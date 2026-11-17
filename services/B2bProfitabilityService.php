<?php

namespace Grocy\Services;

use Exception;
use PDO;

/**
 * Servicio de Contabilidad de Gestión y Rentabilidad Comercial B2B (Paso 10)
 * Control de costos de adquisición, COGS, utilidad bruta, margen comercial
 * y análisis multidimensional por producto, cliente, vendedor y territorio.
 */
class B2bProfitabilityService extends BaseService
{
	private static ?self $instance = null;

	public static function GetInstance(): self
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function Db(): PDO
	{
		return DatabaseService::GetInstance()->GetDbConnectionRaw();
	}

	/**
	 * Asegura la creación y migración del esquema de costos y rentabilidad comercial
	 */
	public function EnsureSchema(): void
	{
		// Asegurar que existan facturas y órdenes primero
		B2bInvoiceService::GetInstance()->EnsureSchema();

		$db = $this->Db();

		// 1. Tabla de costos de adquisición estándar e histórico de vigencia
		$db->exec("
			CREATE TABLE IF NOT EXISTS b2b_product_costs (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				product_id INTEGER NOT NULL,
				unit_cost REAL NOT NULL,
				effective_from DATE NOT NULL,
				effective_to DATE NULL,
				currency TEXT NOT NULL DEFAULT 'USD',
				source TEXT NULL,
				notes TEXT NULL,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				FOREIGN KEY(product_id) REFERENCES b2b_products(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_pc_prod ON b2b_product_costs(product_id, effective_from);

			-- 2. Registro de auditoría para ajustes de costo autorizados
			CREATE TABLE IF NOT EXISTS b2b_cost_adjustments (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				product_id INTEGER NOT NULL,
				old_unit_cost REAL NULL,
				new_unit_cost REAL NOT NULL,
				reason TEXT NOT NULL,
				authorized_by TEXT NOT NULL,
				effective_date DATE NOT NULL,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				FOREIGN KEY(product_id) REFERENCES b2b_products(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_ca_prod ON b2b_cost_adjustments(product_id);
		");

		// 3. Añadir columnas de costo congelado en ítems de facturas si no existen
		$invCols = $db->query("PRAGMA table_info(b2b_invoice_items)")->fetchAll(PDO::FETCH_ASSOC);
		$colNames = array_column($invCols, 'name');

		if (!in_array('unit_cost', $colNames, true))
		{
			$db->exec("ALTER TABLE b2b_invoice_items ADD COLUMN unit_cost REAL NULL");
		}
		if (!in_array('cost_price', $colNames, true))
		{
			$db->exec("ALTER TABLE b2b_invoice_items ADD COLUMN cost_price REAL NULL");
		}
		if (!in_array('total_cost', $colNames, true))
		{
			$db->exec("ALTER TABLE b2b_invoice_items ADD COLUMN total_cost REAL NULL");
		}

		// 4. Añadir columnas de costo en ítems de notas de crédito si no existen
		$cnCols = $db->query("PRAGMA table_info(b2b_credit_note_items)")->fetchAll(PDO::FETCH_ASSOC);
		$cnColNames = array_column($cnCols, 'name');

		if (!in_array('unit_cost', $cnColNames, true))
		{
			$db->exec("ALTER TABLE b2b_credit_note_items ADD COLUMN unit_cost REAL NULL");
		}
		if (!in_array('cost_price', $cnColNames, true))
		{
			$db->exec("ALTER TABLE b2b_credit_note_items ADD COLUMN cost_price REAL NULL");
		}
		if (!in_array('total_cost', $cnColNames, true))
		{
			$db->exec("ALTER TABLE b2b_credit_note_items ADD COLUMN total_cost REAL NULL");
		}

		$this->SeedDemoCosts();
		$this->BackfillHistoricalLineCosts();
	}

	/**
	 * Siembra inicial idempotente de la matriz de costos comerciales estándar
	 */
	private function SeedDemoCosts(): void
	{
		$db = $this->Db();
		$count = (int)$db->query("SELECT COUNT(*) FROM b2b_product_costs")->fetchColumn();
		if ($count > 0)
		{
			return;
		}

		// Matriz de costo base unitario por producto
		// Nota: Producto 15 (Agua Mineral) tiene costo unitario superior al precio venta para generar margen negativo intencional
		// Producto 20 se omite intencionalmente para validar la regla estricta de "Costo no disponible"
		$costs = [
			1  => ['cost' => 1.85, 'source' => 'Contrato Lácteo Anual - Gloria'],
			2  => ['cost' => 1.35, 'source' => 'Distribución Mayorista Directa'],
			3  => ['cost' => 2.10, 'source' => 'Planta Laive Huachipa'],
			4  => ['cost' => 15.50, 'source' => 'Importación Quesos Madurados'],
			5  => ['cost' => 2.20, 'source' => 'Línea Refrigerada Pasteurizada'],
			6  => ['cost' => 5.20, 'source' => 'Embutidos San Fernando'],
			7  => ['cost' => 2.60, 'source' => 'Contrato Avícola Mayorista'],
			8  => ['cost' => 7.80, 'source' => 'Línea Congelados San Fernando'],
			9  => ['cost' => 6.80, 'source' => 'Cárnicos Procesados CIF'],
			10 => ['cost' => 9.00, 'source' => 'Importación Papas Congeladas'],
			11 => ['cost' => 6.20, 'source' => 'Piscigranja & Acuicultura'],
			12 => ['cost' => 6.00, 'source' => 'Camal Mayorista Avícola'],
			13 => ['cost' => 11.50, 'source' => 'Cortes Especiales Avícolas'],
			14 => ['cost' => 17.50, 'source' => 'Frigorífico Bovino San Pedro'],
			15 => ['cost' => 1.55, 'source' => 'Promoción Liquidación Quiebre de Stock'], // Venta caja $7.80 vs Costo caja $9.30 (Margen Negativo)
			16 => ['cost' => 2.80, 'source' => 'Embotelladora Mayorista'],
			17 => ['cost' => 2.85, 'source' => 'Distribuidora Bebidas Lima'],
			18 => ['cost' => 1.35, 'source' => 'Procesadora Cítricos Selva'],
			19 => ['cost' => 5.80, 'source' => 'Fábrica Envases Desechables'],
			// 20 queda sin costo (Costo no disponible)
			21 => ['cost' => 9.80, 'source' => 'Lácteos Artesanales del Valle'],
			22 => ['cost' => 9.80, 'source' => 'Lácteos Artesanales del Valle'],
			23 => ['cost' => 9.80, 'source' => 'Lácteos Artesanales del Valle'],
			24 => ['cost' => 9.80, 'source' => 'Lácteos Artesanales del Valle'],
			25 => ['cost' => 9.80, 'source' => 'Lácteos Artesanales del Valle'],
			26 => ['cost' => 9.80, 'source' => 'Lácteos Artesanales del Valle'],
			27 => ['cost' => 9.80, 'source' => 'Lácteos Artesanales del Valle'],
			28 => ['cost' => 9.80, 'source' => 'Lácteos Artesanales del Valle']
		];

		$stmt = $db->prepare("
			INSERT INTO b2b_product_costs (product_id, unit_cost, effective_from, effective_to, currency, source, notes)
			VALUES (:pid, :cost, '2026-01-01', NULL, 'USD', :source, 'Costo de adquisición estándar inicial')
		");

		foreach ($costs as $pid => $c)
		{
			$stmt->execute([
				':pid' => $pid,
				':cost' => $c['cost'],
				':source' => $c['source']
			]);
		}
	}

	/**
	 * Congela el costo histórico aplicable en ítems de facturas y notas de crédito ya emitidas
	 */
	private function BackfillHistoricalLineCosts(): void
	{
		$db = $this->Db();

		// Actualizar líneas de factura sin costo congelado
		$items = $db->query("
			SELECT ii.id, ii.product_id, ii.commercial_unit, ii.quantity, ii.conversion_factor, i.issue_date
			FROM b2b_invoice_items ii
			JOIN b2b_invoices i ON ii.invoice_id = i.id
			WHERE ii.cost_price IS NULL
		")->fetchAll(PDO::FETCH_ASSOC);

		if (!empty($items))
		{
			$stmtCost = $db->prepare("
				SELECT unit_cost
				FROM b2b_product_costs
				WHERE product_id = :pid
				  AND effective_from <= :idate
				  AND (effective_to IS NULL OR effective_to >= :idate)
				ORDER BY effective_from DESC
				LIMIT 1
			");

			$stmtUpdate = $db->prepare("
				UPDATE b2b_invoice_items
				SET unit_cost = :uc, cost_price = :cp, total_cost = :tc
				WHERE id = :id
			");

			foreach ($items as $item)
			{
				$stmtCost->execute([':pid' => $item['product_id'], ':idate' => $item['issue_date']]);
				$unitCost = $stmtCost->fetchColumn();

				if ($unitCost !== false && $unitCost !== null)
				{
					$uCost = (float)$unitCost;
					$factor = max(1, (int)$item['conversion_factor']);
					$costPrice = ($item['commercial_unit'] === 'CASE') ? round($uCost * $factor, 2) : $uCost;
					$totalCost = round($costPrice * (int)$item['quantity'], 2);

					$stmtUpdate->execute([
						':uc' => $uCost,
						':cp' => $costPrice,
						':tc' => $totalCost,
						':id' => $item['id']
					]);
				}
			}
		}

		// Actualizar líneas de notas de crédito sin costo
		$cnItems = $db->query("
			SELECT cni.id, cni.product_id, cni.commercial_unit, cni.quantity, cn.created_timestamp, p.conversion_factor
			FROM b2b_credit_note_items cni
			JOIN b2b_credit_notes cn ON cni.credit_note_id = cn.id
			LEFT JOIN b2b_products p ON cni.product_id = p.id
			WHERE cni.cost_price IS NULL
		")->fetchAll(PDO::FETCH_ASSOC);

		if (!empty($cnItems))
		{
			$stmtCost = $db->prepare("
				SELECT unit_cost
				FROM b2b_product_costs
				WHERE product_id = :pid
				ORDER BY id DESC
				LIMIT 1
			");

			$stmtUpdateCn = $db->prepare("
				UPDATE b2b_credit_note_items
				SET unit_cost = :uc, cost_price = :cp, total_cost = :tc
				WHERE id = :id
			");

			foreach ($cnItems as $cni)
			{
				$stmtCost->execute([':pid' => $cni['product_id']]);
				$unitCost = $stmtCost->fetchColumn();

				if ($unitCost !== false && $unitCost !== null)
				{
					$uCost = (float)$unitCost;
					$factor = max(1, (int)($cni['conversion_factor'] ?? 1));
					$costPrice = ($cni['commercial_unit'] === 'CASE') ? round($uCost * $factor, 2) : $uCost;
					$totalCost = round($costPrice * (int)$cni['quantity'], 2);

					$stmtUpdateCn->execute([
						':uc' => $uCost,
						':cp' => $costPrice,
						':tc' => $totalCost,
						':id' => $cni['id']
					]);
				}
			}
		}
	}

	/**
	 * Resuelve las fechas de inicio y fin para los períodos predefinidos
	 */
	public function ResolvePeriodDates(string $period, ?string $from = null, ?string $to = null): array
	{
		$period = strtoupper($period);
		switch ($period)
		{
			case '7D':
				return ['from' => '2026-11-10', 'to' => '2026-11-16', 'label' => 'Últimos 7 días (10 Nov - 16 Nov)'];
			case 'QTD':
				return ['from' => '2026-10-01', 'to' => '2026-11-30', 'label' => 'Trimestre Comercial (Q4 2026)'];
			case 'YTD':
				return ['from' => '2026-01-01', 'to' => '2026-11-30', 'label' => 'Año a la Fecha (YTD 2026)'];
			case 'CUSTOM':
				return [
					'from' => $from ?: '2026-11-01',
					'to' => $to ?: '2026-11-30',
					'label' => "Rango: " . ($from ?: '2026-11-01') . " al " . ($to ?: '2026-11-30')
				];
			case 'MTD':
			default:
				return ['from' => '2026-11-01', 'to' => '2026-11-30', 'label' => 'Mes a la Fecha (Noviembre 2026)'];
		}
	}

	/**
	 * Construye condiciones WHERE y parámetros a partir de filtros comerciales
	 */
	private function BuildFiltersSql(array $filters, string $invAlias = 'i', string $itemAlias = 'ii'): array
	{
		$where = ["{$invAlias}.status IN ('Emitida', 'Parcialmente pagada', 'Pagada')"];
		$params = [];

		$periodInfo = $this->ResolvePeriodDates($filters['period'] ?? 'MTD', $filters['from_date'] ?? null, $filters['to_date'] ?? null);
		$where[] = "{$invAlias}.issue_date >= :from AND {$invAlias}.issue_date <= :to";
		$params[':from'] = $periodInfo['from'];
		$params[':to'] = $periodInfo['to'];

		if (!empty($filters['customer_id']))
		{
			$where[] = "{$invAlias}.customer_id = :cid";
			$params[':cid'] = (int)$filters['customer_id'];
		}
		if (!empty($filters['establishment_id']))
		{
			$where[] = "{$invAlias}.establishment_id = :eid";
			$params[':eid'] = (int)$filters['establishment_id'];
		}
		if (!empty($filters['sales_rep']))
		{
			$where[] = "{$invAlias}.sales_rep = :rep";
			$params[':rep'] = trim($filters['sales_rep']);
		}
		if (!empty($filters['territory']))
		{
			$where[] = "EXISTS (SELECT 1 FROM customer_establishments ce WHERE ce.id = {$invAlias}.establishment_id AND ce.territory = :terr)";
			$params[':terr'] = trim($filters['territory']);
		}
		if (!empty($filters['product_id']))
		{
			$where[] = "{$itemAlias}.product_id = :pid";
			$params[':pid'] = (int)$filters['product_id'];
		}
		if (!empty($filters['category']))
		{
			$where[] = "EXISTS (SELECT 1 FROM b2b_products bp WHERE bp.id = {$itemAlias}.product_id AND bp.category = :cat)";
			$params[':cat'] = trim($filters['category']);
		}

		return [
			'sql' => implode(' AND ', $where),
			'params' => $params,
			'period_info' => $periodInfo
		];
	}

	/**
	 * Obtiene los 6 indicadores KPI principales de rentabilidad comercial
	 */
	public function GetExecutiveKPIs(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$filterData = $this->BuildFiltersSql($filters);
		$whereSql = $filterData['sql'];
		$params = $filterData['params'];

		// 1. Facturación bruta e ítems facturados
		$stmtInv = $db->prepare("
			SELECT
				COALESCE(SUM(ii.subtotal), 0) AS gross_sales,
				COALESCE(SUM(CASE WHEN ii.total_cost IS NOT NULL THEN ii.total_cost ELSE 0 END), 0) AS gross_cogs,
				COUNT(CASE WHEN ii.total_cost IS NULL THEN 1 END) AS missing_cost_lines,
				COUNT(DISTINCT i.id) AS invoices_count
			FROM b2b_invoice_items ii
			JOIN b2b_invoices i ON ii.invoice_id = i.id
			WHERE {$whereSql}
		");
		$stmtInv->execute($params);
		$invData = $stmtInv->fetch(PDO::FETCH_ASSOC);

		$invoicedSales = (float)$invData['gross_sales'];
		$invoicedCogs = (float)$invData['gross_cogs'];
		$missingCostLines = (int)$invData['missing_cost_lines'];

		// 2. Notas de crédito en el período correspondiente
		$cnWhere = ["cn.status = 'Emitida' AND date(cn.created_timestamp) >= :from AND date(cn.created_timestamp) <= :to"];
		$cnParams = [':from' => $filterData['period_info']['from'], ':to' => $filterData['period_info']['to']];

		if (!empty($filters['customer_id']))
		{
			$cnWhere[] = "cn.customer_id = :cid";
			$cnParams[':cid'] = (int)$filters['customer_id'];
		}

		$cnWhereSql = implode(' AND ', $cnWhere);
		$stmtCn = $db->prepare("
			SELECT
				COALESCE(SUM(cni.subtotal), 0) AS credited_sales,
				COALESCE(SUM(CASE WHEN cni.total_cost IS NOT NULL THEN cni.total_cost ELSE 0 END), 0) AS credited_cogs
			FROM b2b_credit_note_items cni
			JOIN b2b_credit_notes cn ON cni.credit_note_id = cn.id
			WHERE {$cnWhereSql}
		");
		$stmtCn->execute($cnParams);
		$cnData = $stmtCn->fetch(PDO::FETCH_ASSOC);

		$creditedSales = (float)$cnData['credited_sales'];
		$creditedCogs = (float)$cnData['credited_cogs'];

		// 3. Fórmulas exactas de Rentabilidad Comercial
		$netSales = max(0.0, round($invoicedSales - $creditedSales, 2));
		$cogs = max(0.0, round($invoicedCogs - $creditedCogs, 2));
		$grossProfit = round($netSales - $cogs, 2);
		$grossMarginPct = $netSales > 0.001 ? round(($grossProfit / $netSales) * 100, 1) : null;

		// 4. Productos con margen comercial negativo en el período
		$stmtLoss = $db->prepare("
			SELECT COUNT(*) FROM (
				SELECT
					ii.product_id,
					COALESCE(SUM(ii.subtotal), 0) AS p_sales,
					COALESCE(SUM(ii.total_cost), 0) AS p_cogs
				FROM b2b_invoice_items ii
				JOIN b2b_invoices i ON ii.invoice_id = i.id
				WHERE {$whereSql} AND ii.total_cost IS NOT NULL
				GROUP BY ii.product_id
				HAVING (p_sales - p_cogs) < -0.01
			)
		");
		$stmtLoss->execute($params);
		$negativeMarginCount = (int)$stmtLoss->fetchColumn();

		// 5. Cliente líder en contribución bruta
		$stmtTopCust = $db->prepare("
			SELECT
				c.id,
				c.name,
				COALESCE(SUM(ii.subtotal), 0) AS c_sales,
				COALESCE(SUM(ii.total_cost), 0) AS c_cogs,
				(COALESCE(SUM(ii.subtotal), 0) - COALESCE(SUM(ii.total_cost), 0)) AS c_profit
			FROM b2b_invoice_items ii
			JOIN b2b_invoices i ON ii.invoice_id = i.id
			JOIN customers c ON i.customer_id = c.id
			WHERE {$whereSql}
			GROUP BY c.id, c.name
			ORDER BY c_profit DESC
			LIMIT 1
		");
		$stmtTopCust->execute($params);
		$topCust = $stmtTopCust->fetch(PDO::FETCH_ASSOC);

		$topCustomerName = $topCust ? $topCust['name'] : 'N/D';
		$topCustomerProfit = $topCust ? round((float)$topCust['c_profit'], 2) : 0.00;
		$topCustomerMargin = ($topCust && (float)$topCust['c_sales'] > 0) ? round(((float)$topCust['c_profit'] / (float)$topCust['c_sales']) * 100, 1) : 0.0;

		return [
			'period' => $filters['period'] ?? 'MTD',
			'period_label' => $filterData['period_info']['label'],
			'date_from' => $filterData['period_info']['from'],
			'date_to' => $filterData['period_info']['to'],
			'net_sales' => $netSales,
			'net_sales_formatted' => '$' . number_format($netSales, 2),
			'cogs' => $cogs,
			'cogs_formatted' => '$' . number_format($cogs, 2),
			'gross_profit' => $grossProfit,
			'gross_profit_formatted' => '$' . number_format($grossProfit, 2),
			'gross_margin_percent' => $grossMarginPct,
			'gross_margin_formatted' => $grossMarginPct !== null ? $grossMarginPct . '%' : 'N/D',
			'negative_margin_products_count' => $negativeMarginCount,
			'top_customer' => [
				'id' => $topCust ? (int)$topCust['id'] : null,
				'name' => $topCustomerName,
				'profit' => $topCustomerProfit,
				'profit_formatted' => '$' . number_format($topCustomerProfit, 2),
				'margin_percent' => $topCustomerMargin
			],
			'has_missing_costs' => $missingCostLines > 0,
			'missing_cost_lines_count' => $missingCostLines
		];
	}

	/**
	 * Análisis de Rentabilidad por Producto y SKU
	 */
	public function GetProfitabilityByProduct(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$filterData = $this->BuildFiltersSql($filters);
		$whereSql = $filterData['sql'];
		$params = $filterData['params'];

		$sql = "
			SELECT
				p.id AS product_id,
				p.sku,
				p.name,
				p.category,
				p.brand,
				p.conversion_factor,
				p.base_price_unit,
				p.base_price_case,
				COALESCE(sales.invoiced_sales, 0) AS invoiced_sales,
				COALESCE(sales.cases_sold, 0) AS cases_sold,
				COALESCE(sales.total_units_sold, 0) AS total_units_sold,
				sales.total_cogs AS total_cogs,
				COALESCE(sales.lines_count, 0) AS lines_count,
				COALESCE(sales.missing_cost_count, 0) AS missing_cost_count,
				(
					SELECT unit_cost FROM b2b_product_costs
					WHERE product_id = p.id AND (effective_to IS NULL OR effective_to >= date('now'))
					ORDER BY effective_from DESC LIMIT 1
				) AS current_unit_cost
			FROM b2b_products p
			LEFT JOIN (
				SELECT
					ii.product_id,
					SUM(ii.subtotal) AS invoiced_sales,
					SUM(CASE WHEN ii.commercial_unit = 'CASE' THEN ii.quantity ELSE 0 END) AS cases_sold,
					SUM(ii.total_units) AS total_units_sold,
					SUM(ii.total_cost) AS total_cogs,
					COUNT(ii.id) AS lines_count,
					COUNT(CASE WHEN ii.total_cost IS NULL THEN 1 END) AS missing_cost_count
				FROM b2b_invoice_items ii
				JOIN b2b_invoices i ON ii.invoice_id = i.id
				WHERE {$whereSql}
				GROUP BY ii.product_id
			) sales ON p.id = sales.product_id
			WHERE p.active = 1
			ORDER BY (COALESCE(sales.invoiced_sales, 0) - COALESCE(sales.total_cogs, 0)) DESC, p.name ASC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$results = [];
		foreach ($rows as $r)
		{
			$sales = (float)$r['invoiced_sales'];
			$unitCost = $r['current_unit_cost'] !== null ? (float)$r['current_unit_cost'] : null;
			$caseCost = ($unitCost !== null) ? round($unitCost * max(1, (int)$r['conversion_factor']), 2) : null;
			$missingCosts = (int)$r['missing_cost_count'] > 0 || $unitCost === null || ($sales > 0 && $r['total_cogs'] === null);

			$cogs = $missingCosts ? null : (float)$r['total_cogs'];
			$profit = ($cogs !== null && $sales > 0) ? round($sales - $cogs, 2) : null;
			$margin = ($profit !== null && $sales > 0) ? round(($profit / $sales) * 100, 1) : null;

			// Clasificación de estado de rentabilidad
			$status = 'HEALTHY';
			if ($unitCost === null || $missingCosts)
			{
				$status = 'NO_COST';
			}
			elseif ($profit !== null && $profit < -0.01)
			{
				$status = 'NEGATIVE';
			}
			elseif ($margin !== null && $margin < 10.0)
			{
				$status = 'LOW';
			}
			elseif ($margin !== null && $margin < 20.0)
			{
				$status = 'MODERATE';
			}
			elseif ($sales == 0)
			{
				$status = 'NO_SALES';
			}

			$results[] = [
				'product_id' => (int)$r['product_id'],
				'sku' => $r['sku'],
				'name' => $r['name'],
				'category' => $r['category'],
				'brand' => $r['brand'],
				'conversion_factor' => (int)$r['conversion_factor'],
				'cases_sold' => (int)$r['cases_sold'],
				'units_sold' => (int)$r['total_units_sold'],
				'current_unit_cost' => $unitCost,
				'current_unit_cost_formatted' => $unitCost !== null ? '$' . number_format($unitCost, 2) : 'Costo no disponible',
				'current_case_cost' => $caseCost,
				'current_case_cost_formatted' => $caseCost !== null ? '$' . number_format($caseCost, 2) : 'Costo no disponible',
				'net_sales' => $sales,
				'net_sales_formatted' => '$' . number_format($sales, 2),
				'cogs' => $cogs,
				'cogs_formatted' => $cogs !== null ? '$' . number_format($cogs, 2) : 'Costo no disponible',
				'gross_profit' => $profit,
				'gross_profit_formatted' => $profit !== null ? '$' . number_format($profit, 2) : 'N/D',
				'gross_margin_percent' => $margin,
				'gross_margin_formatted' => $margin !== null ? $margin . '%' : 'N/D',
				'status' => $status,
				'is_negative' => $status === 'NEGATIVE'
			];
		}

		return $results;
	}

	/**
	 * Análisis de Rentabilidad por Cliente y Cadena Comercial
	 */
	public function GetProfitabilityByCustomer(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$filterData = $this->BuildFiltersSql($filters);
		$whereSql = $filterData['sql'];
		$params = $filterData['params'];

		$sql = "
			SELECT
				c.id AS customer_id,
				c.name AS customer_name,
				c.customer_type,
				c.tax_id,
				COUNT(DISTINCT i.id) AS invoices_count,
				COUNT(DISTINCT i.establishment_id) AS establishments_count,
				COALESCE(SUM(ii.subtotal), 0) AS total_sales,
				SUM(ii.total_cost) AS total_cogs,
				COUNT(CASE WHEN ii.total_cost IS NULL THEN 1 END) AS missing_cost_count
			FROM b2b_invoice_items ii
			JOIN b2b_invoices i ON ii.invoice_id = i.id
			JOIN customers c ON i.customer_id = c.id
			WHERE {$whereSql}
			GROUP BY c.id, c.name, c.customer_type, c.tax_id
			ORDER BY (COALESCE(SUM(ii.subtotal), 0) - COALESCE(SUM(ii.total_cost), 0)) DESC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$results = [];
		foreach ($rows as $idx => $r)
		{
			$sales = (float)$r['total_sales'];
			$missing = (int)$r['missing_cost_count'] > 0 || $r['total_cogs'] === null;
			$cogs = $missing ? null : (float)$r['total_cogs'];
			$profit = ($cogs !== null) ? round($sales - $cogs, 2) : null;
			$margin = ($profit !== null && $sales > 0) ? round(($profit / $sales) * 100, 1) : null;

			$results[] = [
				'rank' => $idx + 1,
				'customer_id' => (int)$r['customer_id'],
				'name' => $r['customer_name'],
				'customer_type' => $r['customer_type'] ?: 'comercial',
				'tax_id' => $r['tax_id'],
				'invoices_count' => (int)$r['invoices_count'],
				'establishments_count' => max(1, (int)$r['establishments_count']),
				'net_sales' => $sales,
				'net_sales_formatted' => '$' . number_format($sales, 2),
				'cogs' => $cogs,
				'cogs_formatted' => $cogs !== null ? '$' . number_format($cogs, 2) : 'Costo no disponible',
				'gross_profit' => $profit,
				'gross_profit_formatted' => $profit !== null ? '$' . number_format($profit, 2) : 'Costo no disponible',
				'gross_margin_percent' => $margin,
				'gross_margin_formatted' => $margin !== null ? $margin . '%' : 'N/D',
				'is_healthy' => $margin !== null && $margin >= 20.0
			];
		}

		return $results;
	}

	/**
	 * Análisis de Rentabilidad por Vendedor Comercial
	 */
	public function GetProfitabilityBySalesRep(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$filterData = $this->BuildFiltersSql($filters);
		$whereSql = $filterData['sql'];
		$params = $filterData['params'];

		$sql = "
			SELECT
				i.sales_rep AS rep_name,
				COUNT(DISTINCT i.id) AS invoices_count,
				COUNT(DISTINCT i.customer_id) AS customers_count,
				COALESCE(SUM(ii.subtotal), 0) AS total_sales,
				SUM(ii.total_cost) AS total_cogs
			FROM b2b_invoice_items ii
			JOIN b2b_invoices i ON ii.invoice_id = i.id
			WHERE {$whereSql} AND i.sales_rep IS NOT NULL AND i.sales_rep != ''
			GROUP BY i.sales_rep
			ORDER BY (COALESCE(SUM(ii.subtotal), 0) - COALESCE(SUM(ii.total_cost), 0)) DESC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$results = [];
		foreach ($rows as $r)
		{
			$sales = (float)$r['total_sales'];
			$cogs = $r['total_cogs'] !== null ? (float)$r['total_cogs'] : 0.0;
			$profit = round($sales - $cogs, 2);
			$margin = $sales > 0 ? round(($profit / $sales) * 100, 1) : 0.0;

			// Buscar territorio asignado al vendedor
			$stmtTerr = $db->prepare("
				SELECT t.name FROM b2b_sales_reps sr
				LEFT JOIN b2b_territories t ON sr.territory_id = t.id
				WHERE sr.name = :name
				LIMIT 1
			");
			$stmtTerr->execute([':name' => $r['rep_name']]);
			$terrName = $stmtTerr->fetchColumn() ?: 'Territorio General';

			$results[] = [
				'rep_name' => $r['rep_name'],
				'territory' => $terrName,
				'invoices_count' => (int)$r['invoices_count'],
				'customers_count' => (int)$r['customers_count'],
				'net_sales' => $sales,
				'net_sales_formatted' => '$' . number_format($sales, 2),
				'cogs' => $cogs,
				'cogs_formatted' => '$' . number_format($cogs, 2),
				'gross_profit' => $profit,
				'gross_profit_formatted' => '$' . number_format($profit, 2),
				'gross_margin_percent' => $margin,
				'gross_margin_formatted' => $margin . '%'
			];
		}

		return $results;
	}

	/**
	 * Análisis de Rentabilidad por Territorio Comercial
	 */
	public function GetProfitabilityByTerritory(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$filterData = $this->BuildFiltersSql($filters);
		$whereSql = $filterData['sql'];
		$params = $filterData['params'];

		$sql = "
			SELECT
				t.id AS territory_id,
				COALESCE(t.code, 'TER-LIM-CEN') AS territory_code,
				COALESCE(t.name, ce.territory, 'Lima Centro Metropolitano') AS territory_name,
				COALESCE(t.city, 'Lima') AS city,
				COALESCE(t.zone, 'Centro') AS zone,
				COUNT(DISTINCT i.id) AS invoices_count,
				COUNT(DISTINCT i.customer_id) AS customers_count,
				COALESCE(SUM(ii.subtotal), 0) AS total_sales,
				SUM(ii.total_cost) AS total_cogs
			FROM b2b_invoice_items ii
			JOIN b2b_invoices i ON ii.invoice_id = i.id
			LEFT JOIN customer_establishments ce ON i.establishment_id = ce.id
			LEFT JOIN b2b_sales_reps sr ON i.sales_rep = sr.name
			LEFT JOIN b2b_territories t ON (t.name = ce.territory OR t.id = sr.territory_id)
			WHERE {$whereSql}
			GROUP BY t.id, t.code, t.name, t.city, t.zone
			ORDER BY (COALESCE(SUM(ii.subtotal), 0) - COALESCE(SUM(ii.total_cost), 0)) DESC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$results = [];
		foreach ($rows as $r)
		{
			$sales = (float)$r['total_sales'];
			$cogs = $r['total_cogs'] !== null ? (float)$r['total_cogs'] : 0.0;
			$profit = round($sales - $cogs, 2);
			$margin = $sales > 0 ? round(($profit / $sales) * 100, 1) : 0.0;

			$results[] = [
				'code' => $r['territory_code'],
				'territory_code' => $r['territory_code'],
				'name' => $r['territory_name'],
				'territory_name' => $r['territory_name'],
				'city' => $r['city'],
				'zone' => $r['zone'],
				'invoices_count' => (int)$r['invoices_count'],
				'customers_count' => (int)$r['customers_count'],
				'net_sales' => $sales,
				'net_sales_formatted' => '$' . number_format($sales, 2),
				'cogs' => $cogs,
				'cogs_formatted' => '$' . number_format($cogs, 2),
				'gross_profit' => $profit,
				'gross_profit_formatted' => '$' . number_format($profit, 2),
				'gross_margin_percent' => $margin,
				'gross_margin_formatted' => $margin . '%'
			];
		}

		return $results;
	}

	/**
	 * Análisis de Rentabilidad por Categoría de Producto
	 */
	public function GetProfitabilityByCategory(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$filterData = $this->BuildFiltersSql($filters);
		$whereSql = $filterData['sql'];
		$params = $filterData['params'];

		$sql = "
			SELECT
				p.category,
				COUNT(DISTINCT ii.product_id) AS products_count,
				COALESCE(SUM(ii.subtotal), 0) AS total_sales,
				SUM(ii.total_cost) AS total_cogs
			FROM b2b_invoice_items ii
			JOIN b2b_invoices i ON ii.invoice_id = i.id
			JOIN b2b_products p ON ii.product_id = p.id
			WHERE {$whereSql}
			GROUP BY p.category
			ORDER BY (COALESCE(SUM(ii.subtotal), 0) - COALESCE(SUM(ii.total_cost), 0)) DESC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$totalProfit = 0.0;
		foreach ($rows as $r)
		{
			$sales = (float)$r['total_sales'];
			$cogs = $r['total_cogs'] !== null ? (float)$r['total_cogs'] : 0.0;
			$totalProfit += ($sales - $cogs);
		}

		$results = [];
		foreach ($rows as $r)
		{
			$sales = (float)$r['total_sales'];
			$cogs = $r['total_cogs'] !== null ? (float)$r['total_cogs'] : 0.0;
			$profit = round($sales - $cogs, 2);
			$margin = $sales > 0 ? round(($profit / $sales) * 100, 1) : 0.0;
			$share = $totalProfit > 0 ? round(($profit / $totalProfit) * 100, 1) : 0.0;

			$results[] = [
				'category' => $r['category'] ?: 'General',
				'products_count' => (int)$r['products_count'],
				'net_sales' => $sales,
				'net_sales_formatted' => '$' . number_format($sales, 2),
				'cogs' => $cogs,
				'cogs_formatted' => '$' . number_format($cogs, 2),
				'gross_profit' => $profit,
				'gross_profit_formatted' => '$' . number_format($profit, 2),
				'gross_margin_percent' => $margin,
				'gross_margin_formatted' => $margin . '%',
				'profit_share_percent' => $share
			];
		}

		return $results;
	}

	/**
	 * Evolución temporal de ingresos, costos y utilidad bruta para gráficos
	 */
	public function GetProfitabilityTrend(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$filterData = $this->BuildFiltersSql($filters);
		$whereSql = $filterData['sql'];
		$params = $filterData['params'];

		$sql = "
			SELECT
				i.issue_date,
				COALESCE(SUM(ii.subtotal), 0) AS daily_sales,
				COALESCE(SUM(ii.total_cost), 0) AS daily_cogs
			FROM b2b_invoice_items ii
			JOIN b2b_invoices i ON ii.invoice_id = i.id
			WHERE {$whereSql}
			GROUP BY i.issue_date
			ORDER BY i.issue_date ASC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$labels = [];
		$salesData = [];
		$cogsData = [];
		$profitData = [];
		$marginData = [];

		foreach ($rows as $r)
		{
			$date = $r['issue_date'];
			$sales = (float)$r['daily_sales'];
			$cogs = (float)$r['daily_cogs'];
			$profit = round($sales - $cogs, 2);
			$margin = $sales > 0 ? round(($profit / $sales) * 100, 1) : 0.0;

			$labels[] = substr($date, 5); // MM-DD
			$salesData[] = $sales;
			$cogsData[] = $cogs;
			$profitData[] = $profit;
			$marginData[] = $margin;
		}

		return [
			'labels' => $labels,
			'sales' => $salesData,
			'cogs' => $cogsData,
			'profit' => $profitData,
			'margins' => $marginData
		];
	}

	/**
	 * Matriz de costos estándar por producto y su historial de ajustes
	 */
	public function GetProductCostsCatalog(): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$sql = "
			SELECT
				p.id AS product_id,
				p.sku,
				p.name,
				p.category,
				p.brand,
				p.conversion_factor,
				p.base_price_unit,
				p.base_price_case,
				pc.id AS cost_id,
				pc.unit_cost,
				pc.effective_from,
				pc.effective_to,
				pc.source,
				pc.notes
			FROM b2b_products p
			LEFT JOIN b2b_product_costs pc ON p.id = pc.product_id AND (pc.effective_to IS NULL OR pc.effective_to >= date('now'))
			ORDER BY p.id ASC
		";

		$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

		$results = [];
		foreach ($rows as $r)
		{
			$factor = max(1, (int)$r['conversion_factor']);
			$unitCost = $r['unit_cost'] !== null ? (float)$r['unit_cost'] : null;
			$caseCost = ($unitCost !== null) ? round($unitCost * $factor, 2) : null;
			$unitPrice = (float)$r['base_price_unit'];
			$casePrice = (float)$r['base_price_case'];

			$marginUnit = ($unitCost !== null && $unitPrice > 0) ? round((($unitPrice - $unitCost) / $unitPrice) * 100, 1) : null;
			$marginCase = ($caseCost !== null && $casePrice > 0) ? round((($casePrice - $caseCost) / $casePrice) * 100, 1) : null;

			$results[] = [
				'product_id' => (int)$r['product_id'],
				'sku' => $r['sku'],
				'name' => $r['name'],
				'category' => $r['category'],
				'brand' => $r['brand'],
				'conversion_factor' => $factor,
				'base_price_unit' => $unitPrice,
				'base_price_case' => $casePrice,
				'unit_cost' => $unitCost,
				'unit_cost_formatted' => $unitCost !== null ? '$' . number_format($unitCost, 2) : 'Costo no disponible',
				'case_cost' => $caseCost,
				'case_cost_formatted' => $caseCost !== null ? '$' . number_format($caseCost, 2) : 'Costo no disponible',
				'margin_unit_percent' => $marginUnit,
				'margin_case_percent' => $marginCase,
				'effective_from' => $r['effective_from'],
				'source' => $r['source'] ?: 'Estándar Mayorista',
				'has_cost' => $unitCost !== null
			];
		}

		return $results;
	}

	/**
	 * Ajuste de costo autorizado y auditado para un producto
	 * Cumple la regla contable: no altera márgenes de facturas históricas ya emitidas.
	 */
	public function AdjustProductCost(int $productId, float $newUnitCost, string $reason, string $authorizedBy, ?string $effectiveDate = null): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$effectiveDate = $effectiveDate ?: date('Y-m-d');
		$reason = trim($reason);
		$authorizedBy = trim($authorizedBy) ?: 'Auditoría de Costos';

		$stmtProd = $db->prepare("SELECT name, sku FROM b2b_products WHERE id = :pid");
		$stmtProd->execute([':pid' => $productId]);
		$prodInfo = $stmtProd->fetch(PDO::FETCH_ASSOC);
		$prodName = $prodInfo ? $prodInfo['name'] : 'Producto #' . $productId;

		if ($newUnitCost < 0)
		{
			throw new Exception('El costo de adquisición no puede ser negativo.');
		}

		// Obtener costo actual
		$stmtCur = $db->prepare("
			SELECT id, unit_cost FROM b2b_product_costs
			WHERE product_id = :pid AND (effective_to IS NULL OR effective_to >= :edate)
			ORDER BY effective_from DESC LIMIT 1
		");
		$stmtCur->execute([':pid' => $productId, ':edate' => $effectiveDate]);
		$curCost = $stmtCur->fetch(PDO::FETCH_ASSOC);

		$oldUnitCost = $curCost ? (float)$curCost['unit_cost'] : null;

		// 1. Cerrar vigencia del costo anterior
		if ($curCost)
		{
			$stmtClose = $db->prepare("
				UPDATE b2b_product_costs
				SET effective_to = date(:edate, '-1 day')
				WHERE id = :cid
			");
			$stmtClose->execute([':edate' => $effectiveDate, ':cid' => $curCost['id']]);
		}

		// 2. Insertar nuevo costo activo
		$stmtNew = $db->prepare("
			INSERT INTO b2b_product_costs (product_id, unit_cost, effective_from, effective_to, currency, source, notes)
			VALUES (:pid, :cost, :efrom, NULL, 'USD', 'Ajuste Autorizado', :notes)
		");
		$stmtNew->execute([
			':pid' => $productId,
			':cost' => $newUnitCost,
			':efrom' => $effectiveDate,
			':notes' => $reason
		]);

		// 3. Registrar en bitácora de auditoría inmutable
		$stmtAudit = $db->prepare("
			INSERT INTO b2b_cost_adjustments (product_id, old_unit_cost, new_unit_cost, reason, authorized_by, effective_date)
			VALUES (:pid, :old, :new, :reason, :auth, :edate)
		");
		$stmtAudit->execute([
			':pid' => $productId,
			':old' => $oldUnitCost,
			':new' => $newUnitCost,
			':reason' => $reason,
			':auth' => $authorizedBy,
			':edate' => $effectiveDate
		]);

		try {
			$stmtAuditLog = $db->prepare("
				INSERT INTO b2b_audit_log (organization_id, user_name, entity_type, entity_id, action, old_values, new_values, ip_address, created_at)
				VALUES (1, :user, 'COST_ADJUSTMENT', :eid, 'ADJUST_COST', :old, :new, '127.0.0.1', datetime('now', 'localtime'))
			");
			$stmtAuditLog->execute([
				':user' => $authorizedBy,
				':eid' => $productId,
				':old' => json_encode(['unit_cost' => $oldUnitCost], JSON_UNESCAPED_UNICODE),
				':new' => json_encode(['unit_cost' => $newUnitCost, 'reason' => $reason, 'effective_date' => $effectiveDate], JSON_UNESCAPED_UNICODE)
			]);
		} catch (\Throwable $e) {}

		return [
			'success' => true,
			'message' => 'Ajuste de costo comercial registrado exitosamente en auditoría. Los márgenes históricos de facturas pasadas se mantienen inalterados.',
			'product_id' => $productId,
			'product_name' => $prodName,
			'old_unit_cost' => $oldUnitCost,
			'new_unit_cost' => $newUnitCost,
			'effective_date' => $effectiveDate
		];
	}

	/**
	 * Obtiene el historial de auditoría de ajustes de costo
	 */
	public function GetCostAdjustmentsHistory(): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$sql = "
			SELECT
				ca.*,
				p.sku,
				p.name AS product_name
			FROM b2b_cost_adjustments ca
			JOIN b2b_products p ON ca.product_id = p.id
			ORDER BY ca.id DESC
			LIMIT 50
		";

		return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}
}
