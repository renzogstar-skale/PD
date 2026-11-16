<?php

namespace Grocy\Services;

use Exception;
use PDO;

/**
 * Servicio de Vendedores, Territorios y Metas Comerciales B2B (Paso 9)
 */
class B2bSalesRepService extends BaseService
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
	 * Asegura la creación de las tablas de territorios, vendedores y auditoría de reasignaciones
	 */
	public function EnsureSchema(): void
	{
		$db = $this->Db();

		$db->exec("
			-- 1. Tabla de Territorios Comerciales
			CREATE TABLE IF NOT EXISTS b2b_territories (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				code TEXT NOT NULL UNIQUE,
				name TEXT NOT NULL,
				description TEXT NULL,
				city TEXT NOT NULL DEFAULT 'Lima',
				zone TEXT NOT NULL DEFAULT 'Metropolitana',
				active INTEGER NOT NULL DEFAULT 1,
				is_demo INTEGER NOT NULL DEFAULT 0,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime'))
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_ter_code ON b2b_territories(code);
			CREATE INDEX IF NOT EXISTS idx_b2b_ter_city ON b2b_territories(city);

			-- 2. Tabla de Vendedores (Ejecutivos de Cuenta B2B)
			CREATE TABLE IF NOT EXISTS b2b_sales_reps (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				user_id INTEGER NULL,
				code TEXT NOT NULL UNIQUE,
				name TEXT NOT NULL,
				email TEXT NOT NULL UNIQUE,
				phone TEXT NOT NULL,
				territory_id INTEGER NULL,
				hire_date DATE NOT NULL,
				monthly_quota REAL NOT NULL DEFAULT 20000.00,
				active INTEGER NOT NULL DEFAULT 1,
				is_demo INTEGER NOT NULL DEFAULT 0,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				FOREIGN KEY(territory_id) REFERENCES b2b_territories(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_rep_code ON b2b_sales_reps(code);
			CREATE INDEX IF NOT EXISTS idx_b2b_rep_name ON b2b_sales_reps(name);
			CREATE INDEX IF NOT EXISTS idx_b2b_rep_ter ON b2b_sales_reps(territory_id);

			-- 3. Historial y Auditoría de Reasignaciones de Territorios y Clientes
			CREATE TABLE IF NOT EXISTS b2b_territory_history (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				entity_type TEXT NOT NULL, -- 'ESTABLISHMENT', 'CUSTOMER', 'REP_TERRITORY'
				entity_id INTEGER NOT NULL,
				entity_name TEXT NOT NULL,
				from_rep_id INTEGER NULL,
				from_rep_name TEXT NULL,
				to_rep_id INTEGER NULL,
				to_rep_name TEXT NOT NULL,
				from_territory_id INTEGER NULL,
				from_territory_name TEXT NULL,
				to_territory_id INTEGER NULL,
				to_territory_name TEXT NULL,
				reason TEXT NULL,
				changed_by TEXT NOT NULL DEFAULT 'Dirección Comercial B2B',
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime'))
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_th_ent ON b2b_territory_history(entity_type, entity_id);
		");

		$this->SeedDemoData();
	}

	/**
	 * Siembra inicial idempotente de territorios y vendedores comerciales
	 */
	private function SeedDemoData(): void
	{
		$db = $this->Db();

		$countTer = (int)$db->query("SELECT COUNT(*) FROM b2b_territories")->fetchColumn();
		if ($countTer === 0)
		{
			$stmtTer = $db->prepare("
				INSERT INTO b2b_territories (code, name, description, city, zone, active, is_demo)
				VALUES (:code, :name, :description, :city, :zone, 1, 1)
			");

			$territories = [
				['TER-LIM-CEN', 'Lima Centro Metropolitano', 'Cobertura de hipermercados y almacenes en distritos centrales', 'Lima', 'Centro'],
				['TER-LIM-NOR', 'Lima Norte Comercial', 'Cadenas de supermercados independientes y mayoristas de Los Olivos, SMP e Independencia', 'Lima', 'Norte'],
				['TER-LIM-SUR', 'Lima Sur Cadenas & Retail', 'Supermercados del litoral, autoservicios y distribuidores de Chorrillos a Lurín', 'Lima', 'Sur'],
				['TER-LIM-OES', 'Lima Oeste Corporativo & Puerto', 'Cuentas institucionales del Callao, San Miguel y zonas portuarias', 'Callao', 'Oeste'],
				['TER-LIM-EST', 'Lima Este Distribuidores', 'Mercados concentradores y mayoristas de Santa Anita, Ate y San Juan de Lurigancho', 'Lima', 'Este'],
				['TER-SIE-CEN', 'Sierra Central Mayorista', 'Hub regional de distribución en Huancayo, Concepción y Tarma', 'Huancayo', 'Sierra']
			];

			foreach ($territories as $t)
			{
				$stmtTer->execute([
					':code' => $t[0],
					':name' => $t[1],
					':description' => $t[2],
					':city' => $t[3],
					':zone' => $t[4]
				]);
			}
		}

		$countReps = (int)$db->query("SELECT COUNT(*) FROM b2b_sales_reps")->fetchColumn();
		if ($countReps === 0)
		{
			$stmtRep = $db->prepare("
				INSERT INTO b2b_sales_reps (code, name, email, phone, territory_id, hire_date, monthly_quota, active, is_demo)
				VALUES (:code, :name, :email, :phone, :territory_id, :hire_date, :monthly_quota, 1, 1)
			");

			$reps = [
				['REP-001', 'Carlos Mendoza', 'carlos.mendoza@princedistributors.com', '+51 987 654 321', 4, '2023-03-15', 25000.00],
				['REP-002', 'Andrea Guzmán', 'andrea.guzman@princedistributors.com', '+51 976 543 210', 1, '2023-08-01', 20000.00],
				['REP-003', 'Jorge Morales', 'jorge.morales@princedistributors.com', '+51 965 432 109', 2, '2024-01-10', 22000.00],
				['REP-004', 'Valeria Ramos', 'valeria.ramos@princedistributors.com', '+51 954 321 098', 3, '2024-05-20', 24000.00],
				['REP-005', 'Ana Torres', 'ana.torres@princedistributors.com', '+51 943 210 987', 5, '2024-09-01', 18000.00],
				['REP-006', 'Luis Paredes', 'luis.paredes@princedistributors.com', '+51 932 109 876', 6, '2025-02-15', 15000.00]
			];

			foreach ($reps as $r)
			{
				$stmtRep->execute([
					':code' => $r[0],
					':name' => $r[1],
					':email' => $r[2],
					':phone' => $r[3],
					':territory_id' => $r[4],
					':hire_date' => $r[5],
					':monthly_quota' => $r[6]
				]);
			}
		}

		// Normalizar nombres de territorios y asignar ejecutivos según su territorio asignado
		$db->exec("
			UPDATE customer_establishments SET territory = 'Lima Centro Metropolitano', sales_rep_name = 'Andrea Guzmán' WHERE territory = 'Lima Centro' OR territory = 'Lima Centro Metropolitano';
			UPDATE customer_establishments SET territory = 'Lima Norte Comercial', sales_rep_name = 'Jorge Morales' WHERE territory = 'Lima Norte' OR territory = 'Lima Norte Comercial';
			UPDATE customer_establishments SET territory = 'Lima Sur Cadenas & Retail', sales_rep_name = 'Valeria Ramos' WHERE territory = 'Lima Sur' OR territory = 'Lima Sur Cadenas & Retail';
			UPDATE customer_establishments SET territory = 'Lima Oeste Corporativo & Puerto', sales_rep_name = 'Carlos Mendoza' WHERE territory = 'Lima Oeste' OR territory = 'Callao' OR territory = 'Lima Oeste Corporativo & Puerto';
			UPDATE customer_establishments SET territory = 'Lima Este Distribuidores', sales_rep_name = 'Ana Torres' WHERE territory = 'Lima Este' OR territory = 'Lima Este Distribuidores';
			UPDATE customer_establishments SET territory = 'Sierra Central Mayorista', sales_rep_name = 'Luis Paredes' WHERE territory = 'Sierra Central' OR territory = 'Sierra Central Mayorista';
		");
	}

	/**
	 * Obtiene métricas ejecutivas consolidadas de vendedores y cobertura
	 */
	public function GetExecutiveMetrics(): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		// Total vendedores activos
		$totalReps = (int)$db->query("SELECT COUNT(*) FROM b2b_sales_reps WHERE active = 1")->fetchColumn();
		$totalTerritories = (int)$db->query("SELECT COUNT(*) FROM b2b_territories WHERE active = 1")->fetchColumn();

		// Ventas totales facturadas y meta global del mes (Noviembre 2026)
		$quotaSum = (float)$db->query("SELECT COALESCE(SUM(monthly_quota), 0) FROM b2b_sales_reps WHERE active = 1")->fetchColumn();

		// Facturación válida atribuida en el mes actual
		$invoicedRes = $db->query("
			SELECT COALESCE(SUM(subtotal), 0) AS total_invoiced
			FROM b2b_invoices
			WHERE status IN ('Emitida', 'Parcialmente pagada', 'Pagada')
			  AND issue_date >= '2026-11-01' AND issue_date <= '2026-11-30'
		")->fetch(PDO::FETCH_ASSOC);
		$invoiced = (float)$invoicedRes['total_invoiced'];

		// Notas de crédito en el mes actual
		$creditsRes = $db->query("
			SELECT COALESCE(SUM(subtotal), 0) AS total_credits
			FROM b2b_credit_notes
			WHERE status = 'Emitida'
			  AND date(created_timestamp) >= '2026-11-01' AND date(created_timestamp) <= '2026-11-30'
		")->fetch(PDO::FETCH_ASSOC);
		$credits = (float)$creditsRes['total_credits'];

		$netSales = max(0.0, round($invoiced - $credits, 2));
		$quotaAchievement = $quotaSum > 0 ? round(($netSales / $quotaSum) * 100, 1) : 0;

		// Total órdenes confirmadas en el mes
		$confirmedOrders = (int)$db->query("
			SELECT COUNT(*) FROM b2b_orders
			WHERE status = 'Confirmado'
			  AND date(created_timestamp) >= '2026-11-01' AND date(created_timestamp) <= '2026-11-30'
		")->fetchColumn();

		// Saldo vivo total de cuentas por cobrar
		$totalAr = (float)$db->query("
			SELECT COALESCE(SUM(balance), 0)
			FROM b2b_invoices
			WHERE status IN ('Emitida', 'Parcialmente pagada') AND balance > 0.009
		")->fetchColumn();

		return [
			'total_active_reps' => $totalReps,
			'total_territories' => $totalTerritories,
			'total_monthly_quota' => $quotaSum,
			'total_monthly_quota_formatted' => '$' . number_format($quotaSum, 2),
			'month_net_sales' => $netSales,
			'month_net_sales_formatted' => '$' . number_format($netSales, 2),
			'quota_achievement_percent' => $quotaAchievement,
			'confirmed_orders_count' => $confirmedOrders,
			'total_accounts_receivable' => $totalAr,
			'total_accounts_receivable_formatted' => '$' . number_format($totalAr, 2)
		];
	}

	/**
	 * Obtiene el listado de vendedores con sus KPIs calculados en tiempo real
	 */
	public function GetSalesReps(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$where = ["1 = 1"];
		$params = [];

		if (!empty($filters['search']))
		{
			$where[] = "(r.name LIKE :search OR r.code LIKE :search OR r.email LIKE :search)";
			$params[':search'] = '%' . trim($filters['search']) . '%';
		}
		if (isset($filters['active']) && $filters['active'] !== '')
		{
			$where[] = "r.active = :active";
			$params[':active'] = (int)$filters['active'];
		}
		if (!empty($filters['territory_id']))
		{
			$where[] = "r.territory_id = :tid";
			$params[':tid'] = (int)$filters['territory_id'];
		}

		$whereSql = implode(' AND ', $where);

		$sql = "
			SELECT
				r.*,
				t.name AS territory_name,
				t.code AS territory_code,
				t.city AS territory_city,
				t.zone AS territory_zone
			FROM b2b_sales_reps r
			LEFT JOIN b2b_territories t ON r.territory_id = t.id
			WHERE {$whereSql}
			ORDER BY r.id ASC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		$reps = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Período de cálculo (por defecto MTD Noviembre 2026)
		$from = $filters['from_date'] ?? '2026-11-01';
		$to = $filters['to_date'] ?? '2026-11-30';

		$results = [];
		foreach ($reps as $r)
		{
			$repName = $r['name'];
			$repId = (int)$r['id'];

			// 1. Facturación válida antes de impuestos del vendedor en el período
			$stmtInv = $db->prepare("
				SELECT
					COUNT(DISTINCT id) AS valid_invoices_count,
					COALESCE(SUM(subtotal), 0) AS gross_subtotal,
					COALESCE(SUM(balance), 0) AS open_balance
				FROM b2b_invoices
				WHERE sales_rep = :name
				  AND status IN ('Emitida', 'Parcialmente pagada', 'Pagada')
				  AND issue_date >= :from AND issue_date <= :to
			");
			$stmtInv->execute([':name' => $repName, ':from' => $from, ':to' => $to]);
			$invData = $stmtInv->fetch(PDO::FETCH_ASSOC);

			// 2. Notas de crédito aplicadas
			$stmtCn = $db->prepare("
				SELECT COALESCE(SUM(cn.subtotal), 0) AS cn_subtotal
				FROM b2b_credit_notes cn
				JOIN b2b_invoices i ON cn.invoice_id = i.id
				WHERE i.sales_rep = :name
				  AND cn.status = 'Emitida'
				  AND date(cn.created_timestamp) >= :from AND date(cn.created_timestamp) <= :to
			");
			$stmtCn->execute([':name' => $repName, ':from' => $from, ':to' => $to]);
			$cnSubtotal = (float)$stmtCn->fetchColumn();

			$gross = (float)$invData['gross_subtotal'];
			$netSales = max(0.0, round($gross - $cnSubtotal, 2));
			$invoicesCount = (int)$invData['valid_invoices_count'];

			// 3. Cumplimiento de meta (%)
			$quota = (float)$r['monthly_quota'];
			$achievement = $quota > 0 ? round(($netSales / $quota) * 100, 1) : null;
			$achievementFormatted = $achievement !== null ? $achievement . '%' : 'N/D';

			// 4. Pedidos confirmados en el período
			$stmtOrd = $db->prepare("
				SELECT
					COUNT(*) AS confirmed_count,
					COALESCE(SUM(total), 0) AS confirmed_total
				FROM b2b_orders
				WHERE sales_rep = :name
				  AND status = 'Confirmado'
				  AND date(created_timestamp) >= :from AND date(created_timestamp) <= :to
			");
			$stmtOrd->execute([':name' => $repName, ':from' => $from, ':to' => $to]);
			$ordData = $stmtOrd->fetch(PDO::FETCH_ASSOC);
			$confirmedCount = (int)$ordData['confirmed_count'];

			// 5. Clientes compradores únicos en el período
			$stmtBuyers = $db->prepare("
				SELECT COUNT(DISTINCT customer_id)
				FROM (
					SELECT customer_id FROM b2b_invoices WHERE sales_rep = :name AND status IN ('Emitida', 'Parcialmente pagada', 'Pagada') AND issue_date >= :from AND issue_date <= :to
					UNION
					SELECT customer_id FROM b2b_orders WHERE sales_rep = :name AND status = 'Confirmado' AND date(created_timestamp) >= :from AND date(created_timestamp) <= :to
				)
			");
			$stmtBuyers->execute([':name' => $repName, ':from' => $from, ':to' => $to]);
			$activeBuyers = (int)$stmtBuyers->fetchColumn();

			// 6. Ticket promedio facturado
			$avgTicket = $invoicesCount > 0 ? round($netSales / $invoicesCount, 2) : 0.00;

			// 7. Cartera viva total de cobro
			$stmtAr = $db->prepare("
				SELECT COALESCE(SUM(balance), 0)
				FROM b2b_invoices
				WHERE sales_rep = :name AND status IN ('Emitida', 'Parcialmente pagada') AND balance > 0.009
			");
			$stmtAr->execute([':name' => $repName]);
			$arBalance = (float)$stmtAr->fetchColumn();

			// 8. Clientes y establecimientos asignados actualmente
			$stmtEst = $db->prepare("
				SELECT
					COUNT(*) AS est_count,
					COUNT(DISTINCT customer_id) AS cust_count
				FROM customer_establishments
				WHERE sales_rep_name = :name AND active = 1
			");
			$stmtEst->execute([':name' => $repName]);
			$estData = $stmtEst->fetch(PDO::FETCH_ASSOC);
			$assignedEstCount = (int)$estData['est_count'];
			$assignedCustCount = (int)$estData['cust_count'];

			$results[] = [
				'id' => $repId,
				'code' => $r['code'],
				'name' => $r['name'],
				'email' => $r['email'],
				'phone' => $r['phone'],
				'hire_date' => $r['hire_date'],
				'monthly_quota' => $quota,
				'monthly_quota_formatted' => '$' . number_format($quota, 2),
				'active' => (int)$r['active'],
				'territory' => [
					'id' => (int)$r['territory_id'],
					'code' => $r['territory_code'] ?: 'SIN-TER',
					'name' => $r['territory_name'] ?: 'Sin Territorio Asignado',
					'city' => $r['territory_city'] ?: 'Lima',
					'zone' => $r['territory_zone'] ?: 'Metropolitana'
				],
				'kpis' => [
					'net_sales' => $netSales,
					'net_sales_formatted' => '$' . number_format($netSales, 2),
					'quota_achievement' => $achievement,
					'quota_achievement_formatted' => $achievementFormatted,
					'confirmed_orders' => $confirmedCount,
					'active_buyers' => $activeBuyers,
					'average_ticket' => $avgTicket,
					'average_ticket_formatted' => '$' . number_format($avgTicket, 2),
					'accounts_receivable' => $arBalance,
					'accounts_receivable_formatted' => '$' . number_format($arBalance, 2),
					'assigned_customers' => $assignedCustCount,
					'assigned_establishments' => $assignedEstCount
				]
			];
		}

		// Ordenar por volumen de ventas netas descendente para ranking comercial
		usort($results, function ($a, $b) {
			return $b['kpis']['net_sales'] <=> $a['kpis']['net_sales'];
		});

		foreach ($results as $idx => &$rep)
		{
			$rep['rank'] = $idx + 1;
		}

		return $results;
	}

	/**
	 * Obtiene el detalle exhaustivo de un vendedor (Ficha Individual)
	 */
	public function GetSalesRep(int $id): ?array
	{
		$this->EnsureSchema();
		$reps = $this->GetSalesReps();
		foreach ($reps as $r)
		{
			if ($r['id'] === $id)
			{
				$db = $this->Db();
				$repName = $r['name'];

				// Cartera de establecimientos asignados
				$stmtEst = $db->prepare("
					SELECT
						e.id,
						e.code AS store_code,
						e.name AS establishment_name,
						e.city,
						e.address,
						e.territory,
						c.id AS customer_id,
						c.name AS customer_name,
						c.customer_type,
						c.tax_id
					FROM customer_establishments e
					JOIN customers c ON e.customer_id = c.id
					WHERE e.sales_rep_name = :name AND e.active = 1
					ORDER BY c.name ASC
				");
				$stmtEst->execute([':name' => $repName]);
				$r['assigned_portfolio'] = $stmtEst->fetchAll(PDO::FETCH_ASSOC);

				// Clientes sin compras recientes (> 30 días)
				$stmtInactive = $db->prepare("
					SELECT
						c.id,
						c.name AS customer_name,
						c.tax_id,
						c.customer_type,
						MAX(i.issue_date) AS last_purchase_date,
						julianday('now', 'localtime') - julianday(MAX(i.issue_date)) AS days_since_last
					FROM customers c
					JOIN customer_establishments e ON c.id = e.customer_id
					LEFT JOIN b2b_invoices i ON c.id = i.customer_id AND i.sales_rep = :name AND i.status != 'Anulada'
					WHERE e.sales_rep_name = :name AND c.active = 1
					GROUP BY c.id, c.name, c.tax_id, c.customer_type
					HAVING last_purchase_date IS NULL OR last_purchase_date < date('now', 'localtime', '-30 days')
					ORDER BY days_since_last DESC
				");
				$stmtInactive->execute([':name' => $repName]);
				$r['inactive_customers'] = $stmtInactive->fetchAll(PDO::FETCH_ASSOC);

				// Últimas 5 órdenes registradas por este vendedor
				$stmtRecent = $db->prepare("
					SELECT
						o.id,
						o.order_number,
						o.status,
						o.total,
						o.created_timestamp,
						c.name AS customer_name
					FROM b2b_orders o
					JOIN customers c ON o.customer_id = c.id
					WHERE o.sales_rep = :name
					ORDER BY o.id DESC
					LIMIT 5
				");
				$stmtRecent->execute([':name' => $repName]);
				$r['recent_orders'] = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

				return $r;
			}
		}
		return null;
	}

	/**
	 * Crea un nuevo vendedor comercial
	 */
	public function CreateSalesRep(array $data): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$name = trim($data['name'] ?? '');
		$email = trim($data['email'] ?? '');
		$phone = trim($data['phone'] ?? '');
		$territoryId = !empty($data['territory_id']) ? (int)$data['territory_id'] : null;
		$quota = isset($data['monthly_quota']) ? (float)$data['monthly_quota'] : 20000.00;
		$hireDate = !empty($data['hire_date']) ? trim($data['hire_date']) : date('Y-m-d');

		if (empty($name))
		{
			throw new Exception('El nombre completo del vendedor es obligatorio.');
		}
		if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			throw new Exception('El correo electrónico es inválido o está vacío.');
		}

		// Generar código único correlativo (ej. REP-007)
		$maxId = (int)$db->query("SELECT MAX(id) FROM b2b_sales_reps")->fetchColumn();
		$code = 'REP-' . str_pad((string)($maxId + 1), 3, '0', STR_PAD_LEFT);

		$stmt = $db->prepare("
			INSERT INTO b2b_sales_reps (code, name, email, phone, territory_id, hire_date, monthly_quota, active, is_demo)
			VALUES (:code, :name, :email, :phone, :territory_id, :hire_date, :monthly_quota, 1, 0)
		");
		$stmt->execute([
			':code' => $code,
			':name' => $name,
			':email' => $email,
			':phone' => $phone,
			':territory_id' => $territoryId,
			':hire_date' => $hireDate,
			':monthly_quota' => $quota
		]);

		$newId = (int)$db->lastInsertId();
		return $this->GetSalesRep($newId);
	}

	/**
	 * Actualiza los datos y meta de un vendedor comercial
	 */
	public function UpdateSalesRep(int $id, array $data): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmtCheck = $db->prepare("SELECT * FROM b2b_sales_reps WHERE id = :id");
		$stmtCheck->execute([':id' => $id]);
		$current = $stmtCheck->fetch(PDO::FETCH_ASSOC);
		if (!$current)
		{
			throw new Exception("Vendedor comercial #{$id} no encontrado.");
		}

		$name = trim($data['name'] ?? $current['name']);
		$email = trim($data['email'] ?? $current['email']);
		$phone = trim($data['phone'] ?? $current['phone']);
		$territoryId = isset($data['territory_id']) ? (!empty($data['territory_id']) ? (int)$data['territory_id'] : null) : $current['territory_id'];
		$quota = isset($data['monthly_quota']) ? (float)$data['monthly_quota'] : (float)$current['monthly_quota'];
		$active = isset($data['active']) ? (int)$data['active'] : (int)$current['active'];

		$stmt = $db->prepare("
			UPDATE b2b_sales_reps
			SET name = :name, email = :email, phone = :phone, territory_id = :territory_id, monthly_quota = :quota, active = :active
			WHERE id = :id
		");
		$stmt->execute([
			':name' => $name,
			':email' => $email,
			':phone' => $phone,
			':territory_id' => $territoryId,
			':quota' => $quota,
			':active' => $active,
			':id' => $id
		]);

		return $this->GetSalesRep($id);
	}

	/**
	 * Alterna el estado activo/inactivo de un vendedor
	 */
	public function ToggleSalesRepStatus(int $id): array
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$stmt = $db->prepare("UPDATE b2b_sales_reps SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END WHERE id = :id");
		$stmt->execute([':id' => $id]);
		return $this->GetSalesRep($id);
	}

	/**
	 * Obtiene el listado de territorios comerciales con estadísticas de cobertura
	 */
	public function GetTerritories(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$where = ["1 = 1"];
		$params = [];

		if (!empty($filters['city']))
		{
			$where[] = "t.city = :city";
			$params[':city'] = trim($filters['city']);
		}
		if (!empty($filters['zone']))
		{
			$where[] = "t.zone = :zone";
			$params[':zone'] = trim($filters['zone']);
		}
		if (isset($filters['active']) && $filters['active'] !== '')
		{
			$where[] = "t.active = :active";
			$params[':active'] = (int)$filters['active'];
		}

		$whereSql = implode(' AND ', $where);

		$sql = "
			SELECT
				t.*,
				(SELECT COUNT(*) FROM b2b_sales_reps WHERE territory_id = t.id AND active = 1) AS assigned_reps_count,
				(SELECT COUNT(*) FROM customer_establishments WHERE territory = t.name AND active = 1) AS establishments_count
			FROM b2b_territories t
			WHERE {$whereSql}
			ORDER BY t.id ASC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		$territories = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Obtener vendedores asignados por territorio
		foreach ($territories as &$ter)
		{
			$tid = (int)$ter['id'];
			$stmtReps = $db->prepare("SELECT id, code, name, phone, email FROM b2b_sales_reps WHERE territory_id = :tid AND active = 1");
			$stmtReps->execute([':tid' => $tid]);
			$ter['assigned_reps'] = $stmtReps->fetchAll(PDO::FETCH_ASSOC);
		}

		return $territories;
	}

	/**
	 * Crea un nuevo territorio comercial
	 */
	public function CreateTerritory(array $data): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$name = trim($data['name'] ?? '');
		$city = trim($data['city'] ?? 'Lima');
		$zone = trim($data['zone'] ?? 'Metropolitana');
		$description = trim($data['description'] ?? '');

		if (empty($name))
		{
			throw new Exception('El nombre del territorio comercial es obligatorio.');
		}

		// Generar código de territorio si no se suministra
		$maxId = (int)$db->query("SELECT MAX(id) FROM b2b_territories")->fetchColumn();
		$code = !empty($data['code']) ? strtoupper(trim($data['code'])) : 'TER-' . strtoupper(substr($city, 0, 3)) . '-' . str_pad((string)($maxId + 1), 3, '0', STR_PAD_LEFT);

		$stmt = $db->prepare("
			INSERT INTO b2b_territories (code, name, description, city, zone, active, is_demo)
			VALUES (:code, :name, :description, :city, :zone, 1, 0)
		");
		$stmt->execute([
			':code' => $code,
			':name' => $name,
			':description' => $description,
			':city' => $city,
			':zone' => $zone
		]);

		$newId = (int)$db->lastInsertId();
		$stmtGet = $db->prepare("SELECT * FROM b2b_territories WHERE id = :id");
		$stmtGet->execute([':id' => $newId]);
		return $stmtGet->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Actualiza los datos de un territorio comercial
	 */
	public function UpdateTerritory(int $id, array $data): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmtCheck = $db->prepare("SELECT * FROM b2b_territories WHERE id = :id");
		$stmtCheck->execute([':id' => $id]);
		$current = $stmtCheck->fetch(PDO::FETCH_ASSOC);
		if (!$current)
		{
			throw new Exception("Territorio comercial #{$id} no encontrado.");
		}

		$name = trim($data['name'] ?? $current['name']);
		$city = trim($data['city'] ?? $current['city']);
		$zone = trim($data['zone'] ?? $current['zone']);
		$description = trim($data['description'] ?? $current['description']);
		$active = isset($data['active']) ? (int)$data['active'] : (int)$current['active'];

		$stmt = $db->prepare("
			UPDATE b2b_territories
			SET name = :name, city = :city, zone = :zone, description = :description, active = :active
			WHERE id = :id
		");
		$stmt->execute([
			':name' => $name,
			':city' => $city,
			':zone' => $zone,
			':description' => $description,
			':active' => $active,
			':id' => $id
		]);

		$stmtGet = $db->prepare("SELECT * FROM b2b_territories WHERE id = :id");
		$stmtGet->execute([':id' => $id]);
		return $stmtGet->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Reasigna un establecimiento (o cliente) a un nuevo vendedor o territorio
	 * Conserva estrictamente el historial de auditoría y no altera ventas históricas
	 */
	public function ReassignEstablishment(int $establishmentId, int $newRepId, ?int $newTerritoryId, string $reason): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		// Obtener establecimiento actual
		$stmtEst = $db->prepare("
			SELECT e.*, c.name AS customer_name
			FROM customer_establishments e
			JOIN customers c ON e.customer_id = c.id
			WHERE e.id = :id
		");
		$stmtEst->execute([':id' => $establishmentId]);
		$est = $stmtEst->fetch(PDO::FETCH_ASSOC);
		if (!$est)
		{
			throw new Exception("Establecimiento #{$establishmentId} no encontrado.");
		}

		// Obtener nuevo vendedor
		$stmtRep = $db->prepare("SELECT r.*, t.name AS territory_name FROM b2b_sales_reps r LEFT JOIN b2b_territories t ON r.territory_id = t.id WHERE r.id = :id");
		$stmtRep->execute([':id' => $newRepId]);
		$newRep = $stmtRep->fetch(PDO::FETCH_ASSOC);
		if (!$newRep)
		{
			throw new Exception("El nuevo vendedor seleccionado (#{$newRepId}) no existe.");
		}

		// Resolver nuevo territorio
		$newTerritoryName = $est['territory'];
		if ($newTerritoryId)
		{
			$stmtTer = $db->prepare("SELECT name FROM b2b_territories WHERE id = :id");
			$stmtTer->execute([':id' => $newTerritoryId]);
			$newTerritoryName = $stmtTer->fetchColumn() ?: $est['territory'];
		}
		elseif (!empty($newRep['territory_name']))
		{
			$newTerritoryName = $newRep['territory_name'];
		}

		$fromRepName = $est['sales_rep_name'];
		$toRepName = $newRep['name'];
		$fromTerritoryName = $est['territory'];

		// Actualizar establecimiento
		$stmtUpdate = $db->prepare("
			UPDATE customer_establishments
			SET sales_rep_name = :rep_name, territory = :territory
			WHERE id = :id
		");
		$stmtUpdate->execute([
			':rep_name' => $toRepName,
			':territory' => $newTerritoryName,
			':id' => $establishmentId
		]);

		// Registrar auditoría en b2b_territory_history
		$stmtAudit = $db->prepare("
			INSERT INTO b2b_territory_history (
				entity_type, entity_id, entity_name,
				from_rep_name, to_rep_id, to_rep_name,
				from_territory_name, to_territory_name,
				reason, changed_by
			) VALUES (
				'ESTABLISHMENT', :eid, :ename,
				:from_rep, :to_rep_id, :to_rep,
				:from_ter, :to_ter,
				:reason, 'Supervisión Comercial B2B'
			)
		");
		$stmtAudit->execute([
			':eid' => $establishmentId,
			':ename' => $est['name'] . ' (' . $est['customer_name'] . ')',
			':from_rep' => $fromRepName,
			':to_rep_id' => $newRepId,
			':to_rep' => $toRepName,
			':from_ter' => $fromTerritoryName,
			':to_ter' => $newTerritoryName,
			':reason' => !empty($reason) ? $reason : 'Reestructuración comercial de territorio'
		]);

		return [
			'success' => true,
			'message' => "Establecimiento '{$est['name']}' reasignado exitosamente a {$toRepName} en el territorio {$newTerritoryName}."
		];
	}

	/**
	 * Obtiene el historial de auditoría de reasignaciones comerciales
	 */
	public function GetReassignmentHistory(int $limit = 30): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->prepare("
			SELECT *
			FROM b2b_territory_history
			ORDER BY id DESC
			LIMIT :limit
		");
		$stmt->execute([':limit' => $limit]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}
