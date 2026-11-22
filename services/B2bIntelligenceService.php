<?php

namespace Grocy\Services;

use Exception;
use PDO;

class B2bIntelligenceService
{
	private static ?B2bIntelligenceService $instance = null;
	private ?PDO $db = null;

	public static function GetInstance(): B2bIntelligenceService
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function Db(): PDO
	{
		if ($this->db === null)
		{
			$this->db = DatabaseService::getInstance()->GetDbConnectionRaw();
		}
		return $this->db;
	}

	public function EnsureSchema(): void
	{
		$db = $this->Db();

		// 1. Tabla de umbrales configurables
		$db->exec("
			CREATE TABLE IF NOT EXISTS b2b_intelligence_thresholds (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				threshold_key TEXT UNIQUE NOT NULL,
				threshold_value REAL NOT NULL,
				label TEXT NOT NULL,
				description TEXT,
				unit TEXT DEFAULT '',
				updated_at TEXT DEFAULT CURRENT_TIMESTAMP
			)
		");

		// Seed de umbrales predeterminados si no existen
		$defaultThresholds = [
			['inactive_days_threshold', 30.0, 'Días de Inactividad de Cliente', 'Días transcurridos sin emitir facturas válidas', 'días'],
			['drop_sales_percent_threshold', 30.0, 'Caída Significativa de Ventas', 'Porcentaje de contracción respecto al período anterior', '%'],
			['low_margin_percent_threshold', 15.0, 'Umbral Mínimo de Margen Bruto', 'Márgenes por debajo de este valor se consideran en riesgo', '%'],
			['overdue_days_grace', 0.0, 'Días de Gracia para Facturas Vencidas', 'Días tras fecha de vencimiento antes de alertar', 'días'],
			['quota_risk_threshold_percent', 60.0, 'Cumplimiento Mínimo de Cuota', 'Avance mínimo esperado de cuota para mitad de mes', '%'],
			['pending_order_days_threshold', 5.0, 'Días Máximos Pedido Pendiente', 'Días en estado Pendiente o Borrador sin confirmación', 'días']
		];

		$stmtCheck = $db->prepare("SELECT COUNT(*) FROM b2b_intelligence_thresholds WHERE threshold_key = :key");
		$stmtInsert = $db->prepare("
			INSERT INTO b2b_intelligence_thresholds (threshold_key, threshold_value, label, description, unit)
			VALUES (:key, :val, :label, :desc, :unit)
		");

		foreach ($defaultThresholds as $t)
		{
			$stmtCheck->execute([':key' => $t[0]]);
			if ((int)$stmtCheck->fetchColumn() === 0)
			{
				$stmtInsert->execute([
					':key' => $t[0],
					':val' => $t[1],
					':label' => $t[2],
					':desc' => $t[3],
					':unit' => $t[4]
				]);
			}
		}

		// 2. Tabla de alertas comerciales deduplicadas e inmutables
		$db->exec("
			CREATE TABLE IF NOT EXISTS b2b_commercial_alerts (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				alert_key TEXT UNIQUE NOT NULL,
				alert_type TEXT NOT NULL,
				severity TEXT NOT NULL,
				title TEXT NOT NULL,
				description TEXT NOT NULL,
				evidence_data TEXT,
				suggested_action TEXT NOT NULL,
				entity_type TEXT NOT NULL,
				entity_id INTEGER,
				entity_name TEXT,
				sales_rep_name TEXT,
				territory_name TEXT,
				status TEXT DEFAULT 'ACTIVA',
				threshold_used TEXT,
				reviewed_at TEXT,
				reviewed_by TEXT,
				review_notes TEXT,
				created_at TEXT DEFAULT CURRENT_TIMESTAMP,
				updated_at TEXT DEFAULT CURRENT_TIMESTAMP
			)
		");

		// Índices para optimizar consultas y agregaciones
		$db->exec("CREATE INDEX IF NOT EXISTS idx_alerts_type ON b2b_commercial_alerts (alert_type)");
		$db->exec("CREATE INDEX IF NOT EXISTS idx_alerts_status ON b2b_commercial_alerts (status)");
		$db->exec("CREATE INDEX IF NOT EXISTS idx_alerts_severity ON b2b_commercial_alerts (severity)");
		$db->exec("CREATE INDEX IF NOT EXISTS idx_alerts_entity ON b2b_commercial_alerts (entity_type, entity_id)");
	}

	public function GetThresholds(): array
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$stmt = $db->query("SELECT * FROM b2b_intelligence_thresholds ORDER BY id ASC");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function GetThresholdValue(string $key, float $default = 0.0): float
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$stmt = $db->prepare("SELECT threshold_value FROM b2b_intelligence_thresholds WHERE threshold_key = :k LIMIT 1");
		$stmt->execute([':k' => $key]);
		$val = $stmt->fetchColumn();
		return $val !== false ? (float)$val : $default;
	}

	public function UpdateThresholds(array $thresholds): bool
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$stmt = $db->prepare("
			UPDATE b2b_intelligence_thresholds
			SET threshold_value = :val, updated_at = datetime('now', 'localtime')
			WHERE threshold_key = :k
		");

		foreach ($thresholds as $key => $val)
		{
			$stmt->execute([
				':val' => (float)$val,
				':k' => $key
			]);
		}

		// Recalcular alertas con nuevos umbrales
		$this->RunIntelligenceEngine();
		return true;
	}

	public function RunIntelligenceEngine(): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$inactiveDaysThresh = $this->GetThresholdValue('inactive_days_threshold', 30.0);
		$dropSalesThresh = $this->GetThresholdValue('drop_sales_percent_threshold', 30.0);
		$lowMarginThresh = $this->GetThresholdValue('low_margin_percent_threshold', 15.0);
		$graceDays = $this->GetThresholdValue('overdue_days_grace', 0.0);
		$pendingDaysThresh = $this->GetThresholdValue('pending_order_days_threshold', 5.0);
		$quotaRiskThresh = $this->GetThresholdValue('quota_risk_threshold_percent', 60.0);

		$newAlertsCount = 0;
		$updatedAlertsCount = 0;

		// -------------------------------------------------------------
		// REGLA 1: Clientes Inactivos (30D, 60D, 90D) y Sin Primera Compra
		// -------------------------------------------------------------
		$sqlCustomers = "
			SELECT
				c.id AS customer_id,
				c.name AS customer_name,
				c.customer_type,
				c.tax_id,
				COALESCE(ce.sales_rep_name, 'Sin vendedor') AS sales_rep_name,
				COALESCE(ce.territory, 'Lima Centro Metropolitano') AS territory_name,
				MAX(i.issue_date) AS last_purchase_date,
				COUNT(DISTINCT i.id) AS total_invoices,
				COALESCE(SUM(i.subtotal), 0) AS total_sales
			FROM customers c
			LEFT JOIN customer_establishments ce ON ce.customer_id = c.id
			LEFT JOIN b2b_invoices i ON i.customer_id = c.id AND i.status != 'Anulada'
			WHERE c.active = 1
			GROUP BY c.id, c.name, c.customer_type, c.tax_id, ce.sales_rep_name, ce.territory
		";
		$customers = $db->query($sqlCustomers)->fetchAll(PDO::FETCH_ASSOC);

		foreach ($customers as $c)
		{
			$cid = (int)$c['customer_id'];
			$totalInv = (int)$c['total_invoices'];
			$lastDate = $c['last_purchase_date'];

			if ($totalInv === 0)
			{
				// Cliente nuevo sin primera compra
				$key = "NEW_NO_PURCHASE_{$cid}";
				$sev = 'MEDIUM';
				$title = "Cliente Nuevo sin Primera Compra: {$c['customer_name']}";
				$desc = "Registrado en el sistema comercial pero sin ninguna factura emitida hasta la fecha.";
				$evidence = json_encode([
					'customer_id' => $cid,
					'customer_type' => $c['customer_type'],
					'tax_id' => $c['tax_id'],
					'status' => 'Sin primera compra'
				]);
				$action = "Asignar ejecutivo comercial para presentar portafolio B2B y ofrecer incentivo de primer pedido.";

				$this->UpsertAlert($key, 'NEW_WITHOUT_PURCHASE', $sev, $title, $desc, $evidence, $action, 'CUSTOMER', $cid, $c['customer_name'], $c['sales_rep_name'], $c['territory_name'], "0 compras");
			}
			elseif ($lastDate)
			{
				$daysInactive = (int)(floor((time() - strtotime($lastDate)) / 86400));
				if ($daysInactive >= (int)$inactiveDaysThresh)
				{
					$key = "INACTIVE_CUST_{$cid}";
					$sev = $daysInactive >= 90 ? 'CRITICAL' : ($daysInactive >= 60 ? 'HIGH' : 'MEDIUM');
					$title = "Cliente Inactivo ({$daysInactive} días sin compra): {$c['customer_name']}";
					$desc = "Última compra registrada el {$lastDate}. Acumula {$daysInactive} días sin facturación en la distribuidora.";
					$evidence = json_encode([
						'customer_id' => $cid,
						'last_purchase_date' => $lastDate,
						'days_inactive' => $daysInactive,
						'historical_sales' => round((float)$c['total_sales'], 2),
						'total_invoices' => $totalInv
					]);
					$action = "Contactar al comprador para coordinar llamada de reactivación, verificar inventario en tienda y ofrecer cotización especial.";

					$this->UpsertAlert($key, 'INACTIVE_CUSTOMER', $sev, $title, $desc, $evidence, $action, 'CUSTOMER', $cid, $c['customer_name'], $c['sales_rep_name'], $c['territory_name'], ">= {$inactiveDaysThresh} días");
				}
			}
		}

		// -------------------------------------------------------------
		// REGLA 2: Caída Significativa de Ventas (>30% vs período previo)
		// -------------------------------------------------------------
		// Compara facturación de últimos 30 días vs 31-60 días previos
		$sqlDrop = "
			SELECT
				c.id AS customer_id,
				c.name AS customer_name,
				COALESCE(ce.sales_rep_name, 'Sin vendedor') AS sales_rep_name,
				COALESCE(ce.territory, 'Lima Centro Metropolitano') AS territory_name,
				COALESCE(SUM(CASE WHEN i.issue_date >= date('now', '-30 days') THEN i.subtotal ELSE 0 END), 0) AS current_period_sales,
				COALESCE(SUM(CASE WHEN i.issue_date >= date('now', '-60 days') AND i.issue_date < date('now', '-30 days') THEN i.subtotal ELSE 0 END), 0) AS previous_period_sales
			FROM customers c
			LEFT JOIN customer_establishments ce ON ce.customer_id = c.id
			JOIN b2b_invoices i ON i.customer_id = c.id AND i.status != 'Anulada'
			WHERE i.issue_date >= date('now', '-60 days')
			GROUP BY c.id, c.name, ce.sales_rep_name, ce.territory
			HAVING previous_period_sales > 100.0
		";
		$dropRows = $db->query($sqlDrop)->fetchAll(PDO::FETCH_ASSOC);

		foreach ($dropRows as $dr)
		{
			$prev = (float)$dr['previous_period_sales'];
			$curr = (float)$dr['current_period_sales'];
			if ($prev > 0)
			{
				$dropPct = round((($prev - $curr) / $prev) * 100, 1);
				if ($dropPct >= $dropSalesThresh)
				{
					$cid = (int)$dr['customer_id'];
					$key = "DROP_SALES_{$cid}";
					$sev = $dropPct >= 60 ? 'CRITICAL' : 'HIGH';
					$title = "Caída de Ventas de {$dropPct}%: {$dr['customer_name']}";
					$desc = "Facturación cayó de $" . number_format($prev, 2) . " a $" . number_format($curr, 2) . " (contracción de $" . number_format($prev - $curr, 2) . ").";
					$evidence = json_encode([
						'customer_id' => $cid,
						'previous_period_sales' => $prev,
						'current_period_sales' => $curr,
						'drop_percent' => $dropPct,
						'drop_amount' => round($prev - $curr, 2)
					]);
					$action = "Revisar con {$dr['sales_rep_name']} si el cliente redujo rotación, cambió de proveedor o presentó reclamos de entrega.";

					$this->UpsertAlert($key, 'DROP_SALES', $sev, $title, $desc, $evidence, $action, 'CUSTOMER', $cid, $dr['customer_name'], $dr['sales_rep_name'], $dr['territory_name'], ">= {$dropSalesThresh}%");
				}
			}
		}

		// -------------------------------------------------------------
		// REGLA 3: Facturas Vencidas con Saldo Pendiente
		// -------------------------------------------------------------
		$sqlOverdue = "
			SELECT
				i.id,
				i.invoice_number,
				i.customer_id,
				c.name AS customer_name,
				i.sales_rep,
				i.issue_date,
				i.due_date,
				i.total,
				i.balance,
				CAST(julianday('now') - julianday(i.due_date) AS INTEGER) AS days_overdue
			FROM b2b_invoices i
			JOIN customers c ON i.customer_id = c.id
			WHERE i.status NOT IN ('Pagada', 'Anulada')
				AND i.balance > 0.01
				AND i.due_date < date('now', '-{$graceDays} days')
			ORDER BY days_overdue DESC
		";
		$overdueInvoices = $db->query($sqlOverdue)->fetchAll(PDO::FETCH_ASSOC);

		foreach ($overdueInvoices as $inv)
		{
			$invId = (int)$inv['id'];
			$daysOverdue = max(1, (int)$inv['days_overdue']);
			$bal = (float)$inv['balance'];
			$key = "OVERDUE_INV_{$invId}";
			$sev = $daysOverdue >= 30 ? 'CRITICAL' : ($daysOverdue >= 15 ? 'HIGH' : 'MEDIUM');
			$title = "Factura Vencida {$inv['invoice_number']} ({$daysOverdue} días): {$inv['customer_name']}";
			$desc = "Saldo vencido de $" . number_format($bal, 2) . " con fecha de vencimiento {$inv['due_date']}.";
			$evidence = json_encode([
				'invoice_id' => $invId,
				'invoice_number' => $inv['invoice_number'],
				'customer_id' => (int)$inv['customer_id'],
				'balance' => $bal,
				'due_date' => $inv['due_date'],
				'days_overdue' => $daysOverdue
			]);
			$action = "Gestionar cobro inmediato con departamento de cobranzas antes de autorizar nuevos despachos comerciales.";

			$this->UpsertAlert($key, 'OVERDUE_INVOICE', $sev, $title, $desc, $evidence, $action, 'INVOICE', $invId, $inv['invoice_number'], $inv['sales_rep'], 'Cobranzas', "Vencida > {$graceDays} días");
		}

		// -------------------------------------------------------------
		// REGLA 4: Pedidos Pendientes de Seguimiento (>5 días sin confirmar)
		// -------------------------------------------------------------
		$sqlOrders = "
			SELECT
				o.id,
				o.order_number,
				o.customer_id,
				c.name AS customer_name,
				o.sales_rep,
				o.status,
				o.total,
				o.created_timestamp,
				CAST(julianday('now') - julianday(o.created_timestamp) AS INTEGER) AS days_pending
			FROM b2b_orders o
			JOIN customers c ON o.customer_id = c.id
			WHERE o.status IN ('Pendiente', 'Borrador')
				AND o.created_timestamp <= date('now', '-{$pendingDaysThresh} days')
		";
		$pendingOrders = $db->query($sqlOrders)->fetchAll(PDO::FETCH_ASSOC);

		foreach ($pendingOrders as $ord)
		{
			$ordId = (int)$ord['id'];
			$daysPending = max(1, (int)$ord['days_pending']);
			$key = "PENDING_ORD_{$ordId}";
			$sev = $daysPending >= 10 ? 'HIGH' : 'MEDIUM';
			$title = "Pedido en Espera ({$daysPending} días): {$ord['order_number']}";
			$desc = "Pedido de $" . number_format((float)$ord['total'], 2) . " para {$ord['customer_name']} permanece en estado '{$ord['status']}'.";
			$evidence = json_encode([
				'order_id' => $ordId,
				'order_number' => $ord['order_number'],
				'days_pending' => $daysPending,
				'total' => (float)$ord['total']
			]);
			$action = "Contactar al cliente para validar aprobación o descartar la orden para liberar compromiso de stock.";

			$this->UpsertAlert($key, 'PENDING_ORDER', $sev, $title, $desc, $evidence, $action, 'ORDER', $ordId, $ord['order_number'], $ord['sales_rep'], 'Comercial', "Pendiente > {$pendingDaysThresh} días");
		}

		// -------------------------------------------------------------
		// REGLA 5: Productos o Líneas con Margen Negativo (< 0%)
		// -------------------------------------------------------------
		$sqlNegative = "
			SELECT
				p.id AS product_id,
				p.sku,
				p.name AS product_name,
				p.category,
				p.conversion_factor,
				(
					SELECT unit_cost FROM b2b_product_costs
					WHERE product_id = p.id AND (effective_to IS NULL OR effective_to >= date('now'))
					ORDER BY effective_from DESC LIMIT 1
				) AS current_unit_cost,
				p.base_price_unit,
				SUM(ii.subtotal) AS net_sales,
				SUM(ii.total_cost) AS total_cost
			FROM b2b_products p
			JOIN b2b_invoice_items ii ON ii.product_id = p.id
			JOIN b2b_invoices i ON ii.invoice_id = i.id
			WHERE i.status != 'Anulada'
			GROUP BY p.id, p.sku, p.name, p.category, p.conversion_factor, p.base_price_unit
			HAVING (SUM(ii.subtotal) - SUM(ii.total_cost)) < -0.01
		";
		$negProducts = $db->query($sqlNegative)->fetchAll(PDO::FETCH_ASSOC);

		foreach ($negProducts as $np)
		{
			$pid = (int)$np['product_id'];
			$sales = (float)$np['net_sales'];
			$cost = (float)$np['total_cost'];
			$loss = round($cost - $sales, 2);
			$marginPct = $sales > 0 ? round((($sales - $cost) / $sales) * 100, 1) : -100.0;

			$key = "NEGATIVE_MARGIN_PROD_{$pid}";
			$sev = 'CRITICAL';
			$title = "Margen Negativo ({$marginPct}%): {$np['product_name']} ({$np['sku']})";
			$desc = "Ventas netas de $" . number_format($sales, 2) . " generan pérdida comercial acumulada de $" . number_format($loss, 2) . " debido a costo mayor que precio facturado.";
			$evidence = json_encode([
				'product_id' => $pid,
				'sku' => $np['sku'],
				'net_sales' => $sales,
				'total_cost' => $cost,
				'loss_amount' => $loss,
				'margin_percent' => $marginPct,
				'unit_cost' => $np['current_unit_cost'],
				'base_unit_price' => $np['base_price_unit']
			]);
			$action = "Ajustar precio de venta comercial o renegociar costo de compra mediante módulo de Rentabilidad.";

			$this->UpsertAlert($key, 'NEGATIVE_MARGIN', $sev, $title, $desc, $evidence, $action, 'PRODUCT', $pid, $np['product_name'], 'Dirección Comercial', $np['category'], "Margen < 0%");
		}

		// -------------------------------------------------------------
		// REGLA 6: Meta Comercial en Riesgo (Vendedor con avance bajo)
		// -------------------------------------------------------------
		$dayOfMonth = (int)date('j');
		if ($dayOfMonth >= 10) // Solo después del tercio del mes
		{
			$sqlReps = "
				SELECT
					sr.id,
					sr.name,
					sr.monthly_quota,
					t.name AS territory_name,
					COALESCE(SUM(i.subtotal), 0) AS current_sales
				FROM b2b_sales_reps sr
				LEFT JOIN b2b_territories t ON sr.territory_id = t.id
				LEFT JOIN b2b_invoices i ON i.sales_rep = sr.name
					AND i.status != 'Anulada'
					AND strftime('%Y-%m', i.issue_date) = strftime('%Y-%m', 'now')
				WHERE sr.active = 1 AND sr.monthly_quota > 0
				GROUP BY sr.id, sr.name, sr.monthly_quota, t.name
			";
			$reps = $db->query($sqlReps)->fetchAll(PDO::FETCH_ASSOC);

			foreach ($reps as $r)
			{
				$quota = (float)$r['monthly_quota'];
				$sales = (float)$r['current_sales'];
				$achievePct = round(($sales / $quota) * 100, 1);

				if ($achievePct < $quotaRiskThresh)
				{
					$repId = (int)$r['id'];
					$key = "QUOTA_RISK_REP_{$repId}";
					$sev = $achievePct < 30 ? 'HIGH' : 'MEDIUM';
					$title = "Meta en Riesgo ({$achievePct}% de avance): {$r['name']}";
					$desc = "Ventas facturadas al mes de $" . number_format($sales, 2) . " sobre meta de $" . number_format($quota, 2) . ". Brecha comercial: $" . number_format($quota - $sales, 2) . ".";
					$evidence = json_encode([
						'rep_id' => $repId,
						'rep_name' => $r['name'],
						'monthly_quota' => $quota,
						'current_sales' => $sales,
						'achievement_percent' => $achievePct,
						'gap_amount' => round($quota - $sales, 2)
					]);
					$action = "Revisar pipeline de cotizaciones abiertas y visitas pendientes en su territorio ({$r['territory_name']}).";

					$this->UpsertAlert($key, 'QUOTA_RISK', $sev, $title, $desc, $evidence, $action, 'SALES_REP', $repId, $r['name'], $r['name'], $r['territory_name'], "< {$quotaRiskThresh}%");
				}
			}
		}

		return [
			'success' => true,
			'message' => 'Análisis comercial actualizado con las ventas registradas.'
		];
	}

	private function UpsertAlert(
		string $alertKey,
		string $alertType,
		string $severity,
		string $title,
		string $description,
		string $evidenceData,
		string $suggestedAction,
		string $entityType,
		?int $entityId,
		?string $entityName,
		?string $salesRepName,
		?string $territoryName,
		string $thresholdUsed
	): void {
		$db = $this->Db();

		// Comprobar si ya existe la alerta
		$stmtCheck = $db->prepare("SELECT id, status FROM b2b_commercial_alerts WHERE alert_key = :k LIMIT 1");
		$stmtCheck->execute([':k' => $alertKey]);
		$existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

		if ($existing)
		{
			// Si existe, actualizar datos descriptivos pero CONSERVAR el estado si fue 'REVISADA'
			$stmtUpd = $db->prepare("
				UPDATE b2b_commercial_alerts
				SET severity = :sev,
					title = :title,
					description = :desc,
					evidence_data = :ev,
					suggested_action = :act,
					sales_rep_name = :rep,
					territory_name = :terr,
					threshold_used = :thresh,
					updated_at = datetime('now', 'localtime')
				WHERE alert_key = :k
			");
			$stmtUpd->execute([
				':sev' => $severity,
				':title' => $title,
				':desc' => $description,
				':ev' => $evidenceData,
				':act' => $suggestedAction,
				':rep' => $salesRepName ?: 'General',
				':terr' => $territoryName ?: 'General',
				':thresh' => $thresholdUsed,
				':k' => $alertKey
			]);
		}
		else
		{
			// Insertar nueva alerta activa
			$stmtIns = $db->prepare("
				INSERT INTO b2b_commercial_alerts (
					alert_key, alert_type, severity, title, description,
					evidence_data, suggested_action, entity_type, entity_id,
					entity_name, sales_rep_name, territory_name, status, threshold_used
				) VALUES (
					:k, :type, :sev, :title, :desc,
					:ev, :act, :etype, :eid,
					:ename, :rep, :terr, 'ACTIVA', :thresh
				)
			");
			$stmtIns->execute([
				':k' => $alertKey,
				':type' => $alertType,
				':sev' => $severity,
				':title' => $title,
				':desc' => $description,
				':ev' => $evidenceData,
				':act' => $suggestedAction,
				':etype' => $entityType,
				':eid' => $entityId,
				':ename' => $entityName ?: 'Entidad',
				':rep' => $salesRepName ?: 'General',
				':terr' => $territoryName ?: 'General',
				':thresh' => $thresholdUsed
			]);
		}
	}

	public function GetAlerts(array $filters = []): array
	{
		$this->EnsureSchema();
		$this->RunIntelligenceEngine();
		$db = $this->Db();

		$where = ["1=1"];
		$params = [];

		if (!empty($filters['status']) && $filters['status'] !== 'ALL')
		{
			$where[] = "status = :st";
			$params[':st'] = $filters['status'];
		}
		if (!empty($filters['severity']) && $filters['severity'] !== 'ALL')
		{
			$where[] = "severity = :sev";
			$params[':sev'] = $filters['severity'];
		}
		if (!empty($filters['alert_type']) && $filters['alert_type'] !== 'ALL')
		{
			$where[] = "alert_type = :atype";
			$params[':atype'] = $filters['alert_type'];
		}
		if (!empty($filters['sales_rep']))
		{
			$where[] = "sales_rep_name = :rep";
			$params[':rep'] = $filters['sales_rep'];
		}
		if (!empty($filters['territory']))
		{
			$where[] = "territory_name = :terr";
			$params[':terr'] = $filters['territory'];
		}

		$whereSql = implode(" AND ", $where);
		$sql = "
			SELECT * FROM b2b_commercial_alerts
			WHERE {$whereSql}
			ORDER BY
				CASE severity
					WHEN 'CRITICAL' THEN 1
					WHEN 'HIGH' THEN 2
					WHEN 'MEDIUM' THEN 3
					ELSE 4
				END,
				id DESC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Parsear evidencia JSON para el frontend
		foreach ($rows as &$r)
		{
			$r['evidence'] = !empty($r['evidence_data']) ? json_decode($r['evidence_data'], true) : [];
		}

		return $rows;
	}

	public function MarkAlertReviewed(int $alertId, string $reviewedBy = 'Dirección Comercial', string $notes = ''): bool
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$stmt = $db->prepare("
			UPDATE b2b_commercial_alerts
			SET status = 'REVISADA',
				reviewed_at = datetime('now', 'localtime'),
				reviewed_by = :by,
				review_notes = :notes,
				updated_at = datetime('now', 'localtime')
			WHERE id = :id
		");
		$res = $stmt->execute([
			':by' => trim($reviewedBy) ?: 'Dirección Comercial',
			':notes' => trim($notes) ?: 'Revisado y accionado por el equipo comercial',
			':id' => $alertId
		]);

		try {
			$stmtAudit = $db->prepare("
				INSERT INTO b2b_audit_log (organization_id, user_name, entity_type, entity_id, action, new_values, ip_address, created_at)
				VALUES (1, :by, 'ALERT', :id, 'REVIEW', :val, '127.0.0.1', datetime('now', 'localtime'))
			");
			$stmtAudit->execute([
				':by' => $reviewedBy,
				':id' => $alertId,
				':val' => json_encode(['notes' => $notes], JSON_UNESCAPED_UNICODE)
			]);
		} catch (\Throwable $e) {}

		return $res;
	}

	public function GetExecutiveKPIs(array $filters = []): array
	{
		$this->EnsureSchema();
		$this->RunIntelligenceEngine();
		$db = $this->Db();

		// Alertas activas por severidad
		$stmtAlerts = $db->query("
			SELECT
				COUNT(*) AS total_active,
				COUNT(CASE WHEN severity = 'CRITICAL' THEN 1 END) AS critical_count,
				COUNT(CASE WHEN severity = 'HIGH' THEN 1 END) AS high_count,
				COUNT(CASE WHEN severity = 'MEDIUM' THEN 1 END) AS medium_count,
				COUNT(CASE WHEN alert_type = 'INACTIVE_CUSTOMER' THEN 1 END) AS inactive_customers_count,
				COUNT(CASE WHEN alert_type = 'OVERDUE_INVOICE' THEN 1 END) AS overdue_invoices_count,
				COUNT(CASE WHEN alert_type = 'NEGATIVE_MARGIN' THEN 1 END) AS negative_margin_count
			FROM b2b_commercial_alerts
			WHERE status = 'ACTIVA'
		");
		$alertStats = $stmtAlerts->fetch(PDO::FETCH_ASSOC);

		// Monto de facturas vencidas en riesgo
		$stmtOverdueBal = $db->query("
			SELECT COALESCE(SUM(balance), 0) AS total_overdue_balance
			FROM b2b_invoices
			WHERE status NOT IN ('Pagada', 'Anulada') AND balance > 0.01 AND due_date < date('now')
		");
		$overdueBalance = (float)$stmtOverdueBal->fetchColumn();

		// Oportunidades detectadas totales
		$opps = $this->GetCommercialOpportunities();
		$oppCount = count($opps['abandoned_products'] ?? []) + count($opps['cross_selling'] ?? []) + count($opps['low_margin_customers'] ?? []);

		return [
			'total_active_alerts' => (int)$alertStats['total_active'],
			'critical_alerts' => (int)$alertStats['critical_count'],
			'high_alerts' => (int)$alertStats['high_count'],
			'medium_alerts' => (int)$alertStats['medium_count'],
			'inactive_customers_count' => (int)$alertStats['inactive_customers_count'],
			'overdue_invoices_count' => (int)$alertStats['overdue_invoices_count'],
			'overdue_balance' => $overdueBalance,
			'overdue_balance_formatted' => '$' . number_format($overdueBalance, 2),
			'negative_margin_count' => (int)$alertStats['negative_margin_count'],
			'opportunities_count' => $oppCount
		];
	}

	public function GetInactiveAndAtRiskCustomers(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$sql = "
			SELECT
				c.id AS customer_id,
				c.name AS customer_name,
				c.customer_type,
				c.tax_id,
				COALESCE(ce.sales_rep_name, 'Sin vendedor') AS sales_rep_name,
				COALESCE(ce.territory, 'Lima Centro Metropolitano') AS territory_name,
				MAX(i.issue_date) AS last_purchase_date,
				COUNT(DISTINCT i.id) AS total_invoices,
				COALESCE(SUM(i.subtotal), 0) AS total_sales,
				COALESCE(SUM(CASE WHEN i.issue_date >= date('now', '-30 days') THEN i.subtotal ELSE 0 END), 0) AS sales_last_30d,
				COALESCE(SUM(CASE WHEN i.issue_date >= date('now', '-60 days') AND i.issue_date < date('now', '-30 days') THEN i.subtotal ELSE 0 END), 0) AS sales_prev_30d
			FROM customers c
			LEFT JOIN customer_establishments ce ON ce.customer_id = c.id
			LEFT JOIN b2b_invoices i ON i.customer_id = c.id AND i.status != 'Anulada'
			WHERE c.active = 1
			GROUP BY c.id, c.name, c.customer_type, c.tax_id, ce.sales_rep_name, ce.territory
		";

		$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$results = [];

		foreach ($rows as $r)
		{
			$cid = (int)$r['customer_id'];
			$totalInv = (int)$r['total_invoices'];
			$lastDate = $r['last_purchase_date'];
			$totSales = (float)$r['total_sales'];
			$sLast = (float)$r['sales_last_30d'];
			$sPrev = (float)$r['sales_prev_30d'];

			$daysInactive = $lastDate ? (int)floor((time() - strtotime($lastDate)) / 86400) : 999;
			$avgTicket = $totalInv > 0 ? round($totSales / $totalInv, 2) : 0.0;

			// Clasificación de riesgo
			$riskLevel = 'NORMAL';
			$statusCategory = 'Activo Regular';
			$suggestedAction = 'Mantener ciclo de visitas comercial regular.';

			if ($totalInv === 0)
			{
				$riskLevel = 'MODERATE';
				$statusCategory = 'Nuevo sin Compras';
				$suggestedAction = 'Presentar portafolio inicial y programar primera llamada.';
			}
			elseif ($daysInactive >= 90)
			{
				$riskLevel = 'CRITICAL';
				$statusCategory = 'Inactivo >90 Días';
				$suggestedAction = 'Visita presencial de supervisor comercial para evitar churn definitivo.';
			}
			elseif ($daysInactive >= 60)
			{
				$riskLevel = 'HIGH';
				$statusCategory = 'Inactivo >60 Días';
				$suggestedAction = 'Llamada ejecutiva de reactivación con descuento en compras por volumen.';
			}
			elseif ($daysInactive >= 30)
			{
				$riskLevel = 'MEDIUM';
				$statusCategory = 'Inactivo >30 Días';
				$suggestedAction = 'Envío de catálogo actualizado y verificación de inventario de reposición.';
			}
			elseif ($sPrev > 500.0 && $sLast < ($sPrev * 0.7))
			{
				$riskLevel = 'HIGH';
				$statusCategory = 'Caída de Ventas >30%';
				$dropPct = round((($sPrev - $sLast) / $sPrev) * 100, 1);
				$suggestedAction = "Investigar causa de contracción de {$dropPct}% con comprador de tienda.";
			}

			// Solo incluir si tiene algún nivel de atención o inactividad
			if ($riskLevel !== 'NORMAL')
			{
				$results[] = [
					'customer_id' => $cid,
					'customer_name' => $r['customer_name'],
					'customer_type' => $r['customer_type'] ?: 'Comercial',
					'tax_id' => $r['tax_id'] ?: 'N/A',
					'sales_rep_name' => $r['sales_rep_name'],
					'territory_name' => $r['territory_name'],
					'last_purchase_date' => $lastDate ?: 'Nunca',
					'days_inactive' => $lastDate ? $daysInactive : 'N/A',
					'total_invoices' => $totalInv,
					'total_sales' => $totSales,
					'total_sales_formatted' => '$' . number_format($totSales, 2),
					'avg_ticket_formatted' => '$' . number_format($avgTicket, 2),
					'status_category' => $statusCategory,
					'risk_level' => $riskLevel,
					'suggested_action' => $suggestedAction
				];
			}
		}

		// Ordenar por severidad de riesgo
		usort($results, function($a, $b) {
			$prio = ['CRITICAL' => 1, 'HIGH' => 2, 'MEDIUM' => 3, 'MODERATE' => 4, 'NORMAL' => 5];
			return ($prio[$a['risk_level']] ?? 5) <=> ($prio[$b['risk_level']] ?? 5);
		});

		return $results;
	}

	public function GetCommercialOpportunities(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		// 1. Productos que un cliente compraba y dejó de comprar (Churn de producto)
		$sqlAbandoned = "
			SELECT
				c.id AS customer_id,
				c.name AS customer_name,
				p.id AS product_id,
				p.sku,
				p.name AS product_name,
				p.category,
				COUNT(DISTINCT i.id) AS past_purchases_count,
				MAX(i.issue_date) AS last_product_purchase_date,
				CAST(julianday('now') - julianday(MAX(i.issue_date)) AS INTEGER) AS days_since_product_purchase,
				SUM(ii.quantity) AS past_quantity_cases,
				SUM(ii.subtotal) AS past_spend
			FROM b2b_invoice_items ii
			JOIN b2b_invoices i ON ii.invoice_id = i.id
			JOIN customers c ON i.customer_id = c.id
			JOIN b2b_products p ON ii.product_id = p.id
			WHERE i.status != 'Anulada'
			GROUP BY c.id, c.name, p.id, p.sku, p.name, p.category
			HAVING past_purchases_count >= 1
				AND days_since_product_purchase >= 25
			ORDER BY past_spend DESC
			LIMIT 15
		";
		$abandonedRows = $db->query($sqlAbandoned)->fetchAll(PDO::FETCH_ASSOC);

		$abandonedProducts = [];
		foreach ($abandonedRows as $ar)
		{
			$days = (int)$ar['days_since_product_purchase'];
			$abandonedProducts[] = [
				'customer_id' => (int)$ar['customer_id'],
				'customer_name' => $ar['customer_name'],
				'product_id' => (int)$ar['product_id'],
				'sku' => $ar['sku'],
				'product_name' => $ar['product_name'],
				'category' => $ar['category'],
				'past_purchases_count' => (int)$ar['past_purchases_count'],
				'last_purchase_date' => $ar['last_product_purchase_date'],
				'days_since_last_purchase' => $days,
				'evidence' => "Compró {$ar['past_quantity_cases']} cjs ($" . number_format((float)$ar['past_spend'], 2) . ") en compras previas; han pasado {$days} días sin pedir este SKU.",
				'suggested_action' => "Ofrecer reposición programada de {$ar['product_name']} en la próxima visita."
			];
		}

		// 2. Cross-selling: Productos de alta rotación entre clientes del mismo tipo que aún no compra
		$sqlCross = "
			SELECT
				c.id AS customer_id,
				c.name AS customer_name,
				c.customer_type,
				p.id AS product_id,
				p.sku,
				p.name AS product_name,
				p.category,
				p.base_price_case,
				p.conversion_factor,
				(
					SELECT COUNT(DISTINCT i2.customer_id)
					FROM b2b_invoice_items ii2
					JOIN b2b_invoices i2 ON ii2.invoice_id = i2.id
					JOIN customers c2 ON i2.customer_id = c2.id
					WHERE ii2.product_id = p.id AND c2.customer_type = c.customer_type AND i2.status != 'Anulada'
				) AS peer_customers_count,
				(
					SELECT COUNT(*)
					FROM customers c3
					WHERE c3.customer_type = c.customer_type AND c3.active = 1
				) AS total_segment_customers
			FROM customers c
			CROSS JOIN b2b_products p
			WHERE c.active = 1 AND p.active = 1
				AND p.id NOT IN (
					SELECT DISTINCT ii3.product_id
					FROM b2b_invoice_items ii3
					JOIN b2b_invoices i3 ON ii3.invoice_id = i3.id
					WHERE i3.customer_id = c.id AND i3.status != 'Anulada'
				)
			ORDER BY peer_customers_count DESC
			LIMIT 12
		";
		$crossRows = $db->query($sqlCross)->fetchAll(PDO::FETCH_ASSOC);

		$crossSelling = [];
		foreach ($crossRows as $cr)
		{
			$peers = (int)$cr['peer_customers_count'];
			$totalSeg = max(1, (int)$cr['total_segment_customers']);
			if ($peers >= 2)
			{
				$pct = round(($peers / $totalSeg) * 100, 0);
				$crossSelling[] = [
					'customer_id' => (int)$cr['customer_id'],
					'customer_name' => $cr['customer_name'],
					'customer_type' => $cr['customer_type'],
					'product_id' => (int)$cr['product_id'],
					'sku' => $cr['sku'],
					'product_name' => $cr['product_name'],
					'category' => $cr['category'],
					'evidence' => "El {$pct}% de cuentas del segmento '{$cr['customer_type']}' ({$peers} de {$totalSeg}) compran este SKU activamente.",
					'suggested_action' => "Presentar muestra o cotización de {$cr['product_name']} en caja de {$cr['conversion_factor']} uds."
				];
			}
		}

		// 3. Clientes con margen comercial bajo (<15%)
		$sqlLowMargin = "
			SELECT
				c.id AS customer_id,
				c.name AS customer_name,
				c.customer_type,
				COALESCE(ce.sales_rep_name, 'Sin vendedor') AS sales_rep_name,
				SUM(ii.subtotal) AS net_sales,
				SUM(ii.total_cost) AS total_cogs,
				(SUM(ii.subtotal) - SUM(ii.total_cost)) AS gross_profit,
				ROUND(((SUM(ii.subtotal) - SUM(ii.total_cost)) / SUM(ii.subtotal)) * 100, 1) AS margin_percent
			FROM customers c
			JOIN b2b_invoices i ON i.customer_id = c.id
			JOIN b2b_invoice_items ii ON ii.invoice_id = i.id
			LEFT JOIN customer_establishments ce ON ce.customer_id = c.id
			WHERE i.status != 'Anulada'
				AND ii.total_cost IS NOT NULL
			GROUP BY c.id, c.name, c.customer_type, ce.sales_rep_name
			HAVING SUM(ii.subtotal) > 0 AND margin_percent < 15.0
			ORDER BY margin_percent ASC
		";
		$lowMarginRows = $db->query($sqlLowMargin)->fetchAll(PDO::FETCH_ASSOC);

		$lowMarginCustomers = [];
		foreach ($lowMarginRows as $lm)
		{
			$lowMarginCustomers[] = [
				'customer_id' => (int)$lm['customer_id'],
				'customer_name' => $lm['customer_name'],
				'customer_type' => $lm['customer_type'],
				'sales_rep_name' => $lm['sales_rep_name'],
				'net_sales' => (float)$lm['net_sales'],
				'net_sales_formatted' => '$' . number_format((float)$lm['net_sales'], 2),
				'gross_margin_percent' => (float)$lm['margin_percent'],
				'evidence' => "Margen comercial de solo {$lm['margin_percent']}% (inferior al umbral corporativo de 15.0%).",
				'suggested_action' => "Revisar lista de precios o estructura de descuentos comerciales para recuperar rentabilidad."
			];
		}

		return [
			'abandoned_products' => $abandonedProducts,
			'cross_selling' => $crossSelling,
			'low_margin_customers' => $lowMarginCustomers
		];
	}

	public function GetCategoryTrends(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$sql = "
			SELECT
				COALESCE(p.category, 'General') AS category_name,
				COALESCE(SUM(CASE WHEN i.issue_date >= date('now', '-30 days') THEN ii.subtotal ELSE 0 END), 0) AS sales_current_30d,
				COALESCE(SUM(CASE WHEN i.issue_date >= date('now', '-60 days') AND i.issue_date < date('now', '-30 days') THEN ii.subtotal ELSE 0 END), 0) AS sales_prior_30d,
				COUNT(DISTINCT ii.product_id) AS products_count,
				SUM(CASE WHEN i.issue_date >= date('now', '-30 days') THEN ii.quantity ELSE 0 END) AS cases_sold_current
			FROM b2b_invoice_items ii
			JOIN b2b_invoices i ON ii.invoice_id = i.id
			JOIN b2b_products p ON ii.product_id = p.id
			WHERE i.status != 'Anulada'
			GROUP BY COALESCE(p.category, 'General')
			ORDER BY sales_current_30d DESC
		";

		$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$results = [];

		foreach ($rows as $r)
		{
			$curr = (float)$r['sales_current_30d'];
			$prev = (float)$r['sales_prior_30d'];
			$diff = $curr - $prev;

			$growthPct = 0.0;
			$trend = 'STABLE';
			if ($prev > 0)
			{
				$growthPct = round(($diff / $prev) * 100, 1);
				$trend = $growthPct > 5.0 ? 'GROWTH' : ($growthPct < -5.0 ? 'DECLINE' : 'STABLE');
			}
			elseif ($curr > 0)
			{
				$growthPct = 100.0;
				$trend = 'GROWTH';
			}

			$results[] = [
				'category_name' => $r['category_name'],
				'sales_current' => $curr,
				'sales_current_formatted' => '$' . number_format($curr, 2),
				'sales_prior' => $prev,
				'sales_prior_formatted' => '$' . number_format($prev, 2),
				'difference_amount' => $diff,
				'difference_formatted' => ($diff >= 0 ? '+$' : '-$') . number_format(abs($diff), 2),
				'growth_percent' => $growthPct,
				'trend' => $trend,
				'products_count' => (int)$r['products_count'],
				'cases_sold' => (int)$r['cases_sold_current']
			];
		}

		return $results;
	}
}
