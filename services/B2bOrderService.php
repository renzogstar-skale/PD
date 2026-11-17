<?php

namespace Grocy\Services;

use Exception;
use PDO;

/**
 * Servicio de Dominio B2B para Cotizaciones y Pedidos Comerciales Mayoristas
 */
class B2bOrderService extends BaseService
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
	 * Crea las tablas necesarias para cotizaciones, pedidos, líneas de detalle e historial
	 */
	public function EnsureSchema(): void
	{
		$db = $this->Db();

		$db->exec("
			-- Tabla de Cotizaciones Comerciales
			CREATE TABLE IF NOT EXISTS b2b_quotes (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				quote_number TEXT NOT NULL UNIQUE,
				customer_id INTEGER NOT NULL,
				establishment_id INTEGER NULL,
				sales_rep TEXT NOT NULL DEFAULT 'Carlos Mendoza',
				status TEXT NOT NULL DEFAULT 'Borrador', -- Borrador, Pendiente, Aprobada, Rechazada, Vencida, Cancelada, Convertida
				valid_until DATE NOT NULL,
				subtotal REAL NOT NULL DEFAULT 0.00,
				tax_rate REAL NOT NULL DEFAULT 18.00,
				tax_amount REAL NOT NULL DEFAULT 0.00,
				discount_amount REAL NOT NULL DEFAULT 0.00,
				total REAL NOT NULL DEFAULT 0.00,
				notes TEXT NULL,
				converted_order_id INTEGER NULL,
				is_demo INTEGER NOT NULL DEFAULT 0,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				updated_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				FOREIGN KEY(customer_id) REFERENCES customers(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_quotes_cust ON b2b_quotes(customer_id);
			CREATE INDEX IF NOT EXISTS idx_b2b_quotes_status ON b2b_quotes(status);

			-- Líneas de detalle de cotizaciones
			CREATE TABLE IF NOT EXISTS b2b_quote_items (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				quote_id INTEGER NOT NULL,
				product_id INTEGER NOT NULL,
				commercial_unit TEXT NOT NULL DEFAULT 'CASE', -- CASE o UNIT
				quantity INTEGER NOT NULL DEFAULT 1,
				conversion_factor INTEGER NOT NULL DEFAULT 12,
				total_units INTEGER NOT NULL DEFAULT 12,
				base_price REAL NOT NULL DEFAULT 0.00,
				unit_price REAL NOT NULL DEFAULT 0.00,
				discount_percent REAL NOT NULL DEFAULT 0.00,
				discount_amount REAL NOT NULL DEFAULT 0.00,
				subtotal REAL NOT NULL DEFAULT 0.00,
				rule_applied TEXT NOT NULL DEFAULT 'BASE_PRICE',
				rule_description TEXT NULL,
				FOREIGN KEY(quote_id) REFERENCES b2b_quotes(id) ON DELETE CASCADE,
				FOREIGN KEY(product_id) REFERENCES b2b_products(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_qitems_quote ON b2b_quote_items(quote_id);

			-- Tabla de Pedidos Comerciales Mayoristas
			CREATE TABLE IF NOT EXISTS b2b_orders (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				order_number TEXT NOT NULL UNIQUE,
				quote_id INTEGER NULL,
				customer_id INTEGER NOT NULL,
				establishment_id INTEGER NULL,
				sales_rep TEXT NOT NULL DEFAULT 'Carlos Mendoza',
				status TEXT NOT NULL DEFAULT 'Borrador', -- Borrador, Confirmado, Cancelado
				subtotal REAL NOT NULL DEFAULT 0.00,
				tax_rate REAL NOT NULL DEFAULT 18.00,
				tax_amount REAL NOT NULL DEFAULT 0.00,
				discount_amount REAL NOT NULL DEFAULT 0.00,
				total REAL NOT NULL DEFAULT 0.00,
				notes TEXT NULL,
				is_demo INTEGER NOT NULL DEFAULT 0,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				updated_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				FOREIGN KEY(customer_id) REFERENCES customers(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_orders_cust ON b2b_orders(customer_id);
			CREATE INDEX IF NOT EXISTS idx_b2b_orders_status ON b2b_orders(status);

			-- Líneas de detalle de pedidos (Snapshot de precios)
			CREATE TABLE IF NOT EXISTS b2b_order_items (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				order_id INTEGER NOT NULL,
				product_id INTEGER NOT NULL,
				commercial_unit TEXT NOT NULL DEFAULT 'CASE',
				quantity INTEGER NOT NULL DEFAULT 1,
				conversion_factor INTEGER NOT NULL DEFAULT 12,
				total_units INTEGER NOT NULL DEFAULT 12,
				base_price REAL NOT NULL DEFAULT 0.00,
				unit_price REAL NOT NULL DEFAULT 0.00,
				discount_percent REAL NOT NULL DEFAULT 0.00,
				discount_amount REAL NOT NULL DEFAULT 0.00,
				subtotal REAL NOT NULL DEFAULT 0.00,
				rule_applied TEXT NOT NULL DEFAULT 'BASE_PRICE',
				rule_description TEXT NULL,
				FOREIGN KEY(order_id) REFERENCES b2b_orders(id) ON DELETE CASCADE,
				FOREIGN KEY(product_id) REFERENCES b2b_products(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_oitems_order ON b2b_order_items(order_id);

			-- Historial de Auditoría y Transiciones de Estado
			CREATE TABLE IF NOT EXISTS b2b_order_history (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				entity_type TEXT NOT NULL, -- 'QUOTE' o 'ORDER'
				entity_id INTEGER NOT NULL,
				from_status TEXT NULL,
				to_status TEXT NOT NULL,
				changed_by TEXT NOT NULL DEFAULT 'Carlos Mendoza',
				comment TEXT NULL,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime'))
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_hist_ent ON b2b_order_history(entity_type, entity_id);
		");
	}

	/**
	 * Genera un código correlativo único para cotizaciones (ej. COT-2026-0001)
	 */
	public function GenerateQuoteNumber(): string
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$year = date('Y');
		$maxId = (int)$db->query("SELECT MAX(id) FROM b2b_quotes")->fetchColumn();
		return sprintf("COT-%s-%04d", $year, $maxId + 1);
	}

	/**
	 * Genera un código correlativo único para pedidos (ej. PED-2026-0001)
	 */
	public function GenerateOrderNumber(): string
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$year = date('Y');
		$maxId = (int)$db->query("SELECT MAX(id) FROM b2b_orders")->fetchColumn();
		return sprintf("PED-%s-%04d", $year, $maxId + 1);
	}

	/**
	 * Siembra 10 cotizaciones y 15 pedidos DEMO de forma idempotente
	 */
	public function SeedDemoDataIfEmpty(): void
	{
		$this->EnsureSchema();
		B2bCustomerService::GetInstance()->SeedDemoDataIfEmpty();
		B2bProductService::GetInstance()->SeedDemoDataIfEmpty();
		$db = $this->Db();

		$quotesCount = (int)$db->query("SELECT COUNT(*) FROM b2b_quotes WHERE is_demo = 1")->fetchColumn();
		$ordersCount = (int)$db->query("SELECT COUNT(*) FROM b2b_orders WHERE is_demo = 1")->fetchColumn();

		if ($quotesCount >= 10 && $ordersCount >= 15)
		{
			return; // Ya sembrado
		}

		// Obtener clientes y productos disponibles
		$customers = $db->query("SELECT id, name FROM customers WHERE active = 1 ORDER BY id ASC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
		$products = $db->query("SELECT id, sku, name, conversion_factor, base_price_case, base_price_unit FROM b2b_products WHERE active = 1 ORDER BY id ASC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);

		if (empty($customers) || empty($products))
		{
			return;
		}

		$salesReps = ['Carlos Mendoza', 'Valeria Ramos', 'Jorge Morales', 'Andrea Guzmán'];

		// 1. SIEMBRA DE 10 COTIZACIONES DEMO
		if ($quotesCount < 10)
		{
			$quoteStatuses = [
				['status' => 'Aprobada', 'days' => 15, 'notes' => 'Aprobada para despacho quincenal'],
				['status' => 'Pendiente', 'days' => 20, 'notes' => 'En revisión por compras centrales'],
				['status' => 'Borrador', 'days' => 30, 'notes' => 'Borrador inicial de campaña verano'],
				['status' => 'Pendiente', 'days' => 10, 'notes' => 'Negociación de volumen de lácteos'],
				['status' => 'Aprobada', 'days' => 12, 'notes' => 'Aprobada sucursal San Isidro'],
				['status' => 'Rechazada', 'days' => -5, 'notes' => 'Desestimada por presupuesto de cliente'],
				['status' => 'Vencida', 'days' => -10, 'notes' => 'Vigencia de 15 días expirada'],
				['status' => 'Borrador', 'days' => 25, 'notes' => 'Propuesta institucional'],
				['status' => 'Aprobada', 'days' => 8, 'notes' => 'Aprobada con tarifa preferencial'],
				['status' => 'Cancelada', 'days' => 5, 'notes' => 'Cancelada por cambio de surtido']
			];

			foreach ($quoteStatuses as $idx => $qs)
			{
				$cust = $customers[$idx % count($customers)];
				$rep = $salesReps[$idx % count($salesReps)];
				$quoteNum = sprintf("COT-2026-%04d", $idx + 1);

				// Obtener establecimiento si existe
				$stmtEst = $db->prepare("SELECT id FROM customer_establishments WHERE customer_id = :cid LIMIT 1");
				$stmtEst->execute([':cid' => $cust['id']]);
				$estId = $stmtEst->fetchColumn() ?: null;

				$validDate = date('Y-m-d', strtotime("+{$qs['days']} days"));

				// 2 a 4 líneas por cotización
				$lineCount = 2 + ($idx % 3);
				$subtotal = 0.00;
				$itemsData = [];

				for ($li = 0; $li < $lineCount; $li++)
				{
					$prod = $products[($idx + $li * 2) % count($products)];
					$qty = 5 + ($li * 5);
					$unit = 'CASE';
					$calc = B2bProductService::GetInstance()->CalculatePrice((int)$prod['id'], (int)$cust['id'], $unit, $qty);
					
					$lineSub = (float)$calc['subtotal'];
					$subtotal += $lineSub;

					$itemsData[] = [
						'product_id' => $prod['id'],
						'commercial_unit' => $unit,
						'quantity' => $qty,
						'conversion_factor' => (int)$prod['conversion_factor'],
						'total_units' => (int)$calc['total_units_equivalent'],
						'base_price' => (float)$calc['base_rate'],
						'unit_price' => (float)$calc['applied_rate'],
						'discount_percent' => (float)$calc['base_rate'] > 0 ? round((((float)$calc['base_rate'] - (float)$calc['applied_rate']) / (float)$calc['base_rate']) * 100, 2) : 0,
						'discount_amount' => (float)$calc['savings'],
						'subtotal' => $lineSub,
						'rule_applied' => $calc['rule_applied'],
						'rule_description' => $calc['rule_description']
					];
				}

				$taxRate = 18.00;
				$taxAmount = round($subtotal * ($taxRate / 100), 2);
				$total = round($subtotal + $taxAmount, 2);

				$stmtQ = $db->prepare("
					INSERT OR IGNORE INTO b2b_quotes (
						quote_number, customer_id, establishment_id, sales_rep, status, valid_until,
						subtotal, tax_rate, tax_amount, discount_amount, total, notes, is_demo
					) VALUES (
						:num, :cid, :eid, :rep, :st, :valid,
						:sub, :tr, :tax, 0.00, :tot, :notes, 1
					)
				");
				$stmtQ->execute([
					':num' => $quoteNum,
					':cid' => $cust['id'],
					':eid' => $estId,
					':rep' => $rep,
					':st' => $qs['status'],
					':valid' => $validDate,
					':sub' => $subtotal,
					':tr' => $taxRate,
					':tax' => $taxAmount,
					':tot' => $total,
					':notes' => $qs['notes']
				]);

				$qid = (int)$db->lastInsertId();
				if ($qid > 0)
				{
					foreach ($itemsData as $item)
					{
						$stmtItem = $db->prepare("
							INSERT INTO b2b_quote_items (
								quote_id, product_id, commercial_unit, quantity, conversion_factor, total_units,
								base_price, unit_price, discount_percent, discount_amount, subtotal, rule_applied, rule_description
							) VALUES (
								:qid, :pid, :cu, :qty, :cf, :tu,
								:bp, :up, :dp, :da, :sub, :ra, :rd
							)
						");
						$stmtItem->execute([
							':qid' => $qid,
							':pid' => $item['product_id'],
							':cu' => $item['commercial_unit'],
							':qty' => $item['quantity'],
							':cf' => $item['conversion_factor'],
							':tu' => $item['total_units'],
							':bp' => $item['base_price'],
							':up' => $item['unit_price'],
							':dp' => $item['discount_percent'],
							':da' => $item['discount_amount'],
							':sub' => $item['subtotal'],
							':ra' => $item['rule_applied'],
							':rd' => $item['rule_description']
						]);
					}

					$this->LogStatusHistory('QUOTE', $qid, null, $qs['status'], $rep, 'Creación de cotización demo');
				}
			}
		}

		// 2. SIEMBRA DE 15 PEDIDOS COMERCIALES DEMO
		if ($ordersCount < 15)
		{
			$orderConfigs = [
				['status' => 'Confirmado', 'notes' => 'Abastecimiento semanal cadena principal'],
				['status' => 'Confirmado', 'notes' => 'Pedido programado ruta norte'],
				['status' => 'Confirmado', 'notes' => 'Despacho prioritario para fin de mes'],
				['status' => 'Borrador', 'notes' => 'Orden en revisión por ejecutivo'],
				['status' => 'Confirmado', 'notes' => 'Reposición de alta rotación embutidos'],
				['status' => 'Cancelado', 'notes' => 'Cancelado por solicitud de gerencia comercial'],
				['status' => 'Confirmado', 'notes' => 'Pedido institucional de bebidas y refrigerados'],
				['status' => 'Confirmado', 'notes' => 'Despacho a sucursal Miraflores'],
				['status' => 'Borrador', 'notes' => 'Pedido borrador preventa'],
				['status' => 'Confirmado', 'notes' => 'Campaña mayorista quincena'],
				['status' => 'Confirmado', 'notes' => 'Abastecimiento almacén central'],
				['status' => 'Confirmado', 'notes' => 'Pedido regular de lácteos y carnes'],
				['status' => 'Cancelado', 'notes' => 'Cancelado por duplicación de orden EDI'],
				['status' => 'Confirmado', 'notes' => 'Pedido programado fin de semana'],
				['status' => 'Confirmado', 'notes' => 'Orden corporativa Santa Isabel']
			];

			foreach ($orderConfigs as $idx => $oc)
			{
				$cust = $customers[$idx % count($customers)];
				$rep = $salesReps[$idx % count($salesReps)];
				$orderNum = sprintf("PED-2026-%04d", $idx + 1);

				$stmtEst = $db->prepare("SELECT id FROM customer_establishments WHERE customer_id = :cid LIMIT 1");
				$stmtEst->execute([':cid' => $cust['id']]);
				$estId = $stmtEst->fetchColumn() ?: null;

				$lineCount = 2 + (($idx + 1) % 3);
				$subtotal = 0.00;
				$itemsData = [];

				for ($li = 0; $li < $lineCount; $li++)
				{
					$prod = $products[($idx + $li * 3) % count($products)];
					$qty = 8 + ($li * 6);
					$unit = 'CASE';
					$calc = B2bProductService::GetInstance()->CalculatePrice((int)$prod['id'], (int)$cust['id'], $unit, $qty);
					
					$lineSub = (float)$calc['subtotal'];
					$subtotal += $lineSub;

					$itemsData[] = [
						'product_id' => $prod['id'],
						'commercial_unit' => $unit,
						'quantity' => $qty,
						'conversion_factor' => (int)$prod['conversion_factor'],
						'total_units' => (int)$calc['total_units_equivalent'],
						'base_price' => (float)$calc['base_rate'],
						'unit_price' => (float)$calc['applied_rate'],
						'discount_percent' => (float)$calc['base_rate'] > 0 ? round((((float)$calc['base_rate'] - (float)$calc['applied_rate']) / (float)$calc['base_rate']) * 100, 2) : 0,
						'discount_amount' => (float)$calc['savings'],
						'subtotal' => $lineSub,
						'rule_applied' => $calc['rule_applied'],
						'rule_description' => $calc['rule_description']
					];
				}

				$taxRate = 18.00;
				$taxAmount = round($subtotal * ($taxRate / 100), 2);
				$total = round($subtotal + $taxAmount, 2);

				$stmtO = $db->prepare("
					INSERT OR IGNORE INTO b2b_orders (
						order_number, quote_id, customer_id, establishment_id, sales_rep, status,
						subtotal, tax_rate, tax_amount, discount_amount, total, notes, is_demo
					) VALUES (
						:num, NULL, :cid, :eid, :rep, :st,
						:sub, :tr, :tax, 0.00, :tot, :notes, 1
					)
				");
				$stmtO->execute([
					':num' => $orderNum,
					':cid' => $cust['id'],
					':eid' => $estId,
					':rep' => $rep,
					':st' => $oc['status'],
					':sub' => $subtotal,
					':tr' => $taxRate,
					':tax' => $taxAmount,
					':tot' => $total,
					':notes' => $oc['notes']
				]);

				$oid = (int)$db->lastInsertId();
				if ($oid > 0)
				{
					foreach ($itemsData as $item)
					{
						$stmtItem = $db->prepare("
							INSERT INTO b2b_order_items (
								order_id, product_id, commercial_unit, quantity, conversion_factor, total_units,
								base_price, unit_price, discount_percent, discount_amount, subtotal, rule_applied, rule_description
							) VALUES (
								:oid, :pid, :cu, :qty, :cf, :tu,
								:bp, :up, :dp, :da, :sub, :ra, :rd
							)
						");
						$stmtItem->execute([
							':oid' => $oid,
							':pid' => $item['product_id'],
							':cu' => $item['commercial_unit'],
							':qty' => $item['quantity'],
							':cf' => $item['conversion_factor'],
							':tu' => $item['total_units'],
							':bp' => $item['base_price'],
							':up' => $item['unit_price'],
							':dp' => $item['discount_percent'],
							':da' => $item['discount_amount'],
							':sub' => $item['subtotal'],
							':ra' => $item['rule_applied'],
							':rd' => $item['rule_description']
						]);
					}

					$this->LogStatusHistory('ORDER', $oid, null, $oc['status'], $rep, 'Creación de pedido comercial demo');
				}
			}
		}
	}

	public function LogStatusHistory(string $entityType, int $entityId, ?string $fromStatus, string $toStatus, string $user, ?string $comment = null): void
	{
		$this->EnsureSchema();
		$stmt = $this->Db()->prepare("
			INSERT INTO b2b_order_history (entity_type, entity_id, from_status, to_status, changed_by, comment)
			VALUES (:et, :eid, :fs, :ts, :user, :comm)
		");
		$stmt->execute([
			':et' => strtoupper($entityType),
			':eid' => $entityId,
			':fs' => $fromStatus,
			':ts' => $toStatus,
			':user' => $user,
			':comm' => $comment
		]);

		try {
			$stmtAudit = $this->Db()->prepare("
				INSERT INTO b2b_audit_log (organization_id, user_name, entity_type, entity_id, action, new_values, ip_address, created_at)
				VALUES (1, :user, :ent, :eid, :act, :val, '127.0.0.1', datetime('now', 'localtime'))
			");
			$stmtAudit->execute([
				':user' => $user,
				':ent' => strtoupper($entityType),
				':eid' => $entityId,
				':act' => 'STATUS_CHANGE',
				':val' => json_encode(['from_status' => $fromStatus, 'to_status' => $toStatus, 'comment' => $comment], JSON_UNESCAPED_UNICODE)
			]);
		} catch (\Throwable $e) {}
	}

	/**
	 * KPIs consolidados del ciclo comercial
	 */
	public function GetMetrics(): array
	{
		$this->EnsureSchema();
		$this->SeedDemoDataIfEmpty();
		$db = $this->Db();

		$totalOrders = (int)$db->query("SELECT COUNT(*) FROM b2b_orders")->fetchColumn();
		$confirmedOrders = (int)$db->query("SELECT COUNT(*) FROM b2b_orders WHERE status = 'Confirmado'")->fetchColumn();
		$totalQuotes = (int)$db->query("SELECT COUNT(*) FROM b2b_quotes")->fetchColumn();
		$activeQuotes = (int)$db->query("SELECT COUNT(*) FROM b2b_quotes WHERE status IN ('Pendiente', 'Aprobada')")->fetchColumn();
		$revenue = (float)$db->query("SELECT SUM(total) FROM b2b_orders WHERE status = 'Confirmado'")->fetchColumn();

		$convertedCount = (int)$db->query("SELECT COUNT(*) FROM b2b_quotes WHERE status = 'Convertida' OR converted_order_id IS NOT NULL")->fetchColumn();
		$conversionRate = $totalQuotes > 0 ? round(($convertedCount / $totalQuotes) * 100, 1) : 0.0;

		return [
			'total_orders' => $totalOrders,
			'confirmed_orders' => $confirmedOrders,
			'total_quotes' => $totalQuotes,
			'active_quotes' => $activeQuotes,
			'confirmed_revenue' => number_format($revenue, 2, '.', ''),
			'conversion_rate' => $conversionRate
		];
	}

	/**
	 * Listar Cotizaciones con filtros
	 */
	public function GetQuotes(array $filters = []): array
	{
		$this->EnsureSchema();
		$this->SeedDemoDataIfEmpty();
		$db = $this->Db();

		$where = ['1=1'];
		$params = [];

		if (!empty($filters['search']))
		{
			$search = '%' . trim($filters['search']) . '%';
			$where[] = "(q.quote_number LIKE :s1 OR c.name LIKE :s2 OR q.sales_rep LIKE :s3)";
			$params[':s1'] = $search;
			$params[':s2'] = $search;
			$params[':s3'] = $search;
		}

		if (!empty($filters['status']))
		{
			$where[] = "q.status = :status";
			$params[':status'] = trim($filters['status']);
		}

		if (!empty($filters['customer_id']))
		{
			$where[] = "q.customer_id = :cid";
			$params[':cid'] = (int)$filters['customer_id'];
		}

		$whereSql = implode(' AND ', $where);

		$sql = "
			SELECT 
				q.*,
				c.name AS customer_name,
				c.tax_id AS customer_tax_id,
				e.name AS establishment_name,
				e.city AS establishment_city,
				(SELECT COUNT(*) FROM b2b_quote_items qi WHERE qi.quote_id = q.id) AS items_count
			FROM b2b_quotes q
			JOIN customers c ON c.id = q.customer_id
			LEFT JOIN customer_establishments e ON e.id = q.establishment_id
			WHERE {$whereSql}
			ORDER BY q.id DESC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Detalle completo de una cotización
	 */
	public function GetQuote(int $id): ?array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->prepare("
			SELECT 
				q.*,
				c.name AS customer_name,
				c.tax_id AS customer_tax_id,
				c.customer_type AS customer_type,
				c.email AS customer_email,
				c.phone AS customer_phone,
				e.name AS establishment_name,
				e.code AS establishment_code,
				e.address AS establishment_address,
				e.city AS establishment_city
			FROM b2b_quotes q
			JOIN customers c ON c.id = q.customer_id
			LEFT JOIN customer_establishments e ON e.id = q.establishment_id
			WHERE q.id = :id
		");
		$stmt->execute([':id' => $id]);
		$quote = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$quote)
		{
			return null;
		}

		// Obtener líneas
		$stmtItems = $db->prepare("
			SELECT 
				qi.*,
				p.sku AS product_sku,
				p.name AS product_name,
				p.brand AS product_brand,
				p.category AS product_category,
				p.unit_name,
				p.case_name
			FROM b2b_quote_items qi
			JOIN b2b_products p ON p.id = qi.product_id
			WHERE qi.quote_id = :id
			ORDER BY qi.id ASC
		");
		$stmtItems->execute([':id' => $id]);
		$quote['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

		// Historial de cambios
		$stmtH = $db->prepare("
			SELECT * FROM b2b_order_history
			WHERE entity_type = 'QUOTE' AND entity_id = :id
			ORDER BY id DESC
		");
		$stmtH->execute([':id' => $id]);
		$quote['history'] = $stmtH->fetchAll(PDO::FETCH_ASSOC);

		return $quote;
	}

	/**
	 * Listar Pedidos con filtros
	 */
	public function GetOrders(array $filters = []): array
	{
		$this->EnsureSchema();
		$this->SeedDemoDataIfEmpty();
		$db = $this->Db();

		$where = ['1=1'];
		$params = [];

		if (!empty($filters['search']))
		{
			$search = '%' . trim($filters['search']) . '%';
			$where[] = "(o.order_number LIKE :s1 OR c.name LIKE :s2 OR o.sales_rep LIKE :s3)";
			$params[':s1'] = $search;
			$params[':s2'] = $search;
			$params[':s3'] = $search;
		}

		if (!empty($filters['status']))
		{
			$where[] = "o.status = :status";
			$params[':status'] = trim($filters['status']);
		}

		if (!empty($filters['customer_id']))
		{
			$where[] = "o.customer_id = :cid";
			$params[':cid'] = (int)$filters['customer_id'];
		}

		$whereSql = implode(' AND ', $where);

		$sql = "
			SELECT 
				o.*,
				c.name AS customer_name,
				c.tax_id AS customer_tax_id,
				e.name AS establishment_name,
				e.city AS establishment_city,
				q.quote_number AS source_quote_number,
				(SELECT COUNT(*) FROM b2b_order_items oi WHERE oi.order_id = o.id) AS items_count
			FROM b2b_orders o
			JOIN customers c ON c.id = o.customer_id
			LEFT JOIN customer_establishments e ON e.id = o.establishment_id
			LEFT JOIN b2b_quotes q ON q.id = o.quote_id
			WHERE {$whereSql}
			ORDER BY o.id DESC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Detalle completo de un pedido con snapshot de precios y auditoría
	 */
	public function GetOrder(int $id): ?array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->prepare("
			SELECT 
				o.*,
				c.name AS customer_name,
				c.tax_id AS customer_tax_id,
				c.customer_type AS customer_type,
				c.email AS customer_email,
				c.phone AS customer_phone,
				e.name AS establishment_name,
				e.code AS establishment_code,
				e.address AS establishment_address,
				e.city AS establishment_city,
				q.quote_number AS source_quote_number
			FROM b2b_orders o
			JOIN customers c ON c.id = o.customer_id
			LEFT JOIN customer_establishments e ON e.id = o.establishment_id
			LEFT JOIN b2b_quotes q ON q.id = o.quote_id
			WHERE o.id = :id
		");
		$stmt->execute([':id' => $id]);
		$order = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$order)
		{
			return null;
		}

		// Obtener líneas
		$stmtItems = $db->prepare("
			SELECT 
				oi.*,
				p.sku AS product_sku,
				p.name AS product_name,
				p.brand AS product_brand,
				p.category AS product_category,
				p.unit_name,
				p.case_name
			FROM b2b_order_items oi
			JOIN b2b_products p ON p.id = oi.product_id
			WHERE oi.order_id = :id
			ORDER BY oi.id ASC
		");
		$stmtItems->execute([':id' => $id]);
		$order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

		// Historial de cambios
		$stmtH = $db->prepare("
			SELECT * FROM b2b_order_history
			WHERE entity_type = 'ORDER' AND entity_id = :id
			ORDER BY id DESC
		");
		$stmtH->execute([':id' => $id]);
		$order['history'] = $stmtH->fetchAll(PDO::FETCH_ASSOC);

		return $order;
	}

	/**
	 * Valida y calcula líneas de cotización o pedido en el backend
	 */
	public function CalculateCommercialLines(int $customerId, array $items): array
	{
		$productService = B2bProductService::GetInstance();
		$calculatedLines = [];
		$subtotal = 0.00;
		$totalSavings = 0.00;

		foreach ($items as $item)
		{
			$productId = (int)($item['product_id'] ?? 0);
			$quantity = max(1, (int)($item['quantity'] ?? 1));
			$unit = strtoupper((string)($item['commercial_unit'] ?? 'CASE'));
			if (!in_array($unit, ['CASE', 'UNIT'], true)) $unit = 'CASE';

			$calc = $productService->CalculatePrice($productId, $customerId, $unit, $quantity);

			$lineSub = (float)$calc['subtotal'];
			$lineSavings = (float)$calc['savings'];
			$subtotal += $lineSub;
			$totalSavings += $lineSavings;

			$calculatedLines[] = [
				'product_id' => $productId,
				'product_sku' => $calc['sku'],
				'product_name' => $calc['product_name'],
				'commercial_unit' => $unit,
				'quantity' => $quantity,
				'conversion_factor' => $calc['conversion_factor'],
				'total_units' => $calc['total_units_equivalent'],
				'base_price' => (float)$calc['base_rate'],
				'unit_price' => (float)$calc['applied_rate'],
				'discount_percent' => (float)$calc['base_rate'] > 0 ? round((((float)$calc['base_rate'] - (float)$calc['applied_rate']) / (float)$calc['base_rate']) * 100, 2) : 0,
				'discount_amount' => $lineSavings,
				'subtotal' => $lineSub,
				'rule_applied' => $calc['rule_applied'],
				'rule_description' => $calc['rule_description']
			];
		}

		$taxRate = 18.00;
		$taxAmount = round($subtotal * ($taxRate / 100), 2);
		$total = round($subtotal + $taxAmount, 2);

		return [
			'items' => $calculatedLines,
			'subtotal' => round($subtotal, 2),
			'tax_rate' => $taxRate,
			'tax_amount' => $taxAmount,
			'discount_amount' => round($totalSavings, 2),
			'total' => $total
		];
	}

	/**
	 * Crea una nueva cotización
	 */
	public function CreateQuote(array $data, string $user = 'Carlos Mendoza'): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$customerId = (int)($data['customer_id'] ?? 0);
		if ($customerId <= 0)
		{
			throw new Exception('El cliente comercial es obligatorio.');
		}

		$items = (array)($data['items'] ?? []);
		if (empty($items))
		{
			throw new Exception('La cotización debe contener al menos un producto.');
		}

		// Validar cliente activo
		$stmtCust = $db->prepare("SELECT id, name, active FROM customers WHERE id = :id");
		$stmtCust->execute([':id' => $customerId]);
		$cust = $stmtCust->fetch(PDO::FETCH_ASSOC);
		if (!$cust || (int)$cust['active'] !== 1)
		{
			throw new Exception('El cliente comercial seleccionado no está activo.');
		}

		$estId = !empty($data['establishment_id']) ? (int)$data['establishment_id'] : null;
		$salesRep = trim($data['sales_rep'] ?? $user) ?: $user;
		$validUntil = !empty($data['valid_until']) ? date('Y-m-d', strtotime($data['valid_until'])) : date('Y-m-d', strtotime('+15 days'));
		$status = trim($data['status'] ?? 'Borrador');
		$validStatuses = ['Borrador', 'Pendiente', 'Aprobada', 'Rechazada', 'Vencida', 'Cancelada'];
		if (!in_array($status, $validStatuses, true))
		{
			$status = 'Borrador';
		}
		$notes = trim($data['notes'] ?? '');

		// Calcular líneas con motor determinista
		$calculated = $this->CalculateCommercialLines($customerId, $items);
		$quoteNum = $this->GenerateQuoteNumber();

		$db->beginTransaction();
		try
		{
			$stmt = $db->prepare("
				INSERT INTO b2b_quotes (
					quote_number, customer_id, establishment_id, sales_rep, status, valid_until,
					subtotal, tax_rate, tax_amount, discount_amount, total, notes, is_demo
				) VALUES (
					:num, :cid, :eid, :rep, :st, :valid,
					:sub, :tr, :tax, :disc, :tot, :notes, 0
				)
			");
			$stmt->execute([
				':num' => $quoteNum,
				':cid' => $customerId,
				':eid' => $estId,
				':rep' => $salesRep,
				':st' => $status,
				':valid' => $validUntil,
				':sub' => $calculated['subtotal'],
				':tr' => $calculated['tax_rate'],
				':tax' => $calculated['tax_amount'],
				':disc' => $calculated['discount_amount'],
				':tot' => $calculated['total'],
				':notes' => $notes
			]);

			$quoteId = (int)$db->lastInsertId();

			// Insertar ítems
			$stmtItem = $db->prepare("
				INSERT INTO b2b_quote_items (
					quote_id, product_id, commercial_unit, quantity, conversion_factor, total_units,
					base_price, unit_price, discount_percent, discount_amount, subtotal, rule_applied, rule_description
				) VALUES (
					:qid, :pid, :cu, :qty, :cf, :tu,
					:bp, :up, :dp, :da, :sub, :ra, :rd
				)
			");

			foreach ($calculated['items'] as $item)
			{
				$stmtItem->execute([
					':qid' => $quoteId,
					':pid' => $item['product_id'],
					':cu' => $item['commercial_unit'],
					':qty' => $item['quantity'],
					':cf' => $item['conversion_factor'],
					':tu' => $item['total_units'],
					':bp' => $item['base_price'],
					':up' => $item['unit_price'],
					':dp' => $item['discount_percent'],
					':da' => $item['discount_amount'],
					':sub' => $item['subtotal'],
					':ra' => $item['rule_applied'],
					':rd' => $item['rule_description']
				]);
			}

			$this->LogStatusHistory('QUOTE', $quoteId, null, $status, $user, 'Registro de nueva cotización comercial');

			$db->commit();
			return $this->GetQuote($quoteId);
		}
		catch (Exception $e)
		{
			$db->rollBack();
			throw $e;
		}
	}

	/**
	 * Actualizar estado de cotización (Aprobar, Rechazar, Cancelar, etc.)
	 */
	public function UpdateQuoteStatus(int $quoteId, string $newStatus, string $user = 'Carlos Mendoza', ?string $comment = null): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$quote = $this->GetQuote($quoteId);
		if (!$quote)
		{
			throw new Exception('Cotización no encontrada.');
		}

		$validStatuses = ['Borrador', 'Pendiente', 'Aprobada', 'Rechazada', 'Vencida', 'Cancelada', 'Convertida'];
		if (!in_array($newStatus, $validStatuses, true))
		{
			throw new Exception('Estado de cotización inválido.');
		}

		$fromStatus = $quote['status'];
		if ($fromStatus === 'Convertida')
		{
			throw new Exception('No se puede alterar una cotización que ya fue convertida a pedido.');
		}

		$stmt = $db->prepare("UPDATE b2b_quotes SET status = :st, updated_timestamp = datetime('now', 'localtime') WHERE id = :id");
		$stmt->execute([':st' => $newStatus, ':id' => $quoteId]);

		$this->LogStatusHistory('QUOTE', $quoteId, $fromStatus, $newStatus, $user, $comment ?: "Cambio de estado a {$newStatus}");

		return $this->GetQuote($quoteId);
	}

	/**
	 * Convierte una cotización aprobada en pedido comercial mayorista (IDEMPOTENTE)
	 */
	public function ConvertQuoteToOrder(int $quoteId, string $user = 'Carlos Mendoza'): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$quote = $this->GetQuote($quoteId);
		if (!$quote)
		{
			throw new Exception('Cotización no encontrada.');
		}

		// Validar que esté aprobada
		if ($quote['status'] !== 'Aprobada')
		{
			throw new Exception('Solo se pueden convertir a pedido cotizaciones en estado Aprobada.');
		}

		// Idempotencia: Verificar si ya fue convertida previamente
		if (!empty($quote['converted_order_id']))
		{
			$existingOrder = $this->GetOrder((int)$quote['converted_order_id']);
			if ($existingOrder)
			{
				return $existingOrder; // Ya convertida, retornar orden existente
			}
		}

		$db->beginTransaction();
		try
		{
			$orderNum = $this->GenerateOrderNumber();

			$stmt = $db->prepare("
				INSERT INTO b2b_orders (
					order_number, quote_id, customer_id, establishment_id, sales_rep, status,
					subtotal, tax_rate, tax_amount, discount_amount, total, notes, is_demo
				) VALUES (
					:num, :qid, :cid, :eid, :rep, 'Confirmado',
					:sub, :tr, :tax, :disc, :tot, :notes, :demo
				)
			");
			$stmt->execute([
				':num' => $orderNum,
				':qid' => $quoteId,
				':cid' => $quote['customer_id'],
				':eid' => $quote['establishment_id'],
				':rep' => $quote['sales_rep'],
				':sub' => $quote['subtotal'],
				':tr' => $quote['tax_rate'],
				':tax' => $quote['tax_amount'],
				':disc' => $quote['discount_amount'],
				':tot' => $quote['total'],
				':notes' => "Generado automáticamente a partir de cotización {$quote['quote_number']}. " . ($quote['notes'] ?? ''),
				':demo' => $quote['is_demo']
			]);

			$orderId = (int)$db->lastInsertId();

			// Copiar líneas con snapshot de precios
			$stmtItem = $db->prepare("
				INSERT INTO b2b_order_items (
					order_id, product_id, commercial_unit, quantity, conversion_factor, total_units,
					base_price, unit_price, discount_percent, discount_amount, subtotal, rule_applied, rule_description
				) VALUES (
					:oid, :pid, :cu, :qty, :cf, :tu,
					:bp, :up, :dp, :da, :sub, :ra, :rd
				)
			");

			foreach ($quote['items'] as $item)
			{
				$stmtItem->execute([
					':oid' => $orderId,
					':pid' => $item['product_id'],
					':cu' => $item['commercial_unit'],
					':qty' => $item['quantity'],
					':cf' => $item['conversion_factor'],
					':tu' => $item['total_units'],
					':bp' => $item['base_price'],
					':up' => $item['unit_price'],
					':dp' => $item['discount_percent'],
					':da' => $item['discount_amount'],
					':sub' => $item['subtotal'],
					':ra' => $item['rule_applied'],
					':rd' => $item['rule_description']
				]);
			}

			// Actualizar estado de la cotización y enlazar
			$stmtUpQuote = $db->prepare("
				UPDATE b2b_quotes
				SET status = 'Convertida', converted_order_id = :oid, updated_timestamp = datetime('now', 'localtime')
				WHERE id = :qid
			");
			$stmtUpQuote->execute([':oid' => $orderId, ':qid' => $quoteId]);

			$this->LogStatusHistory('QUOTE', $quoteId, 'Aprobada', 'Convertida', $user, "Convertida a pedido comercial {$orderNum}");
			$this->LogStatusHistory('ORDER', $orderId, null, 'Confirmado', $user, "Pedido confirmado por conversión de {$quote['quote_number']}");

			$db->commit();
			return $this->GetOrder($orderId);
		}
		catch (Exception $e)
		{
			$db->rollBack();
			throw $e;
		}
	}

	/**
	 * Crea un nuevo pedido directamente
	 */
	public function CreateOrder(array $data, string $user = 'Carlos Mendoza'): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$customerId = (int)($data['customer_id'] ?? 0);
		if ($customerId <= 0)
		{
			throw new Exception('El cliente comercial es obligatorio.');
		}

		$items = (array)($data['items'] ?? []);
		if (empty($items))
		{
			throw new Exception('El pedido debe contener al menos un producto.');
		}

		// Validar cliente activo
		$stmtCust = $db->prepare("SELECT id, name, active FROM customers WHERE id = :id");
		$stmtCust->execute([':id' => $customerId]);
		$cust = $stmtCust->fetch(PDO::FETCH_ASSOC);
		if (!$cust || (int)$cust['active'] !== 1)
		{
			throw new Exception('El cliente comercial seleccionado no está activo.');
		}

		$estId = !empty($data['establishment_id']) ? (int)$data['establishment_id'] : null;
		$salesRep = trim($data['sales_rep'] ?? $user) ?: $user;
		$status = trim($data['status'] ?? 'Confirmado');
		$validStatuses = ['Borrador', 'Confirmado', 'Cancelado'];
		if (!in_array($status, $validStatuses, true))
		{
			$status = 'Confirmado';
		}
		$notes = trim($data['notes'] ?? '');

		// Calcular líneas con motor determinista
		$calculated = $this->CalculateCommercialLines($customerId, $items);
		$orderNum = $this->GenerateOrderNumber();

		$db->beginTransaction();
		try
		{
			$stmt = $db->prepare("
				INSERT INTO b2b_orders (
					order_number, quote_id, customer_id, establishment_id, sales_rep, status,
					subtotal, tax_rate, tax_amount, discount_amount, total, notes, is_demo
				) VALUES (
					:num, NULL, :cid, :eid, :rep, :st,
					:sub, :tr, :tax, :disc, :tot, :notes, 0
				)
			");
			$stmt->execute([
				':num' => $orderNum,
				':cid' => $customerId,
				':eid' => $estId,
				':rep' => $salesRep,
				':st' => $status,
				':sub' => $calculated['subtotal'],
				':tr' => $calculated['tax_rate'],
				':tax' => $calculated['tax_amount'],
				':disc' => $calculated['discount_amount'],
				':tot' => $calculated['total'],
				':notes' => $notes
			]);

			$orderId = (int)$db->lastInsertId();

			// Insertar ítems
			$stmtItem = $db->prepare("
				INSERT INTO b2b_order_items (
					order_id, product_id, commercial_unit, quantity, conversion_factor, total_units,
					base_price, unit_price, discount_percent, discount_amount, subtotal, rule_applied, rule_description
				) VALUES (
					:oid, :pid, :cu, :qty, :cf, :tu,
					:bp, :up, :dp, :da, :sub, :ra, :rd
				)
			");

			foreach ($calculated['items'] as $item)
			{
				$stmtItem->execute([
					':oid' => $orderId,
					':pid' => $item['product_id'],
					':cu' => $item['commercial_unit'],
					':qty' => $item['quantity'],
					':cf' => $item['conversion_factor'],
					':tu' => $item['total_units'],
					':bp' => $item['base_price'],
					':up' => $item['unit_price'],
					':dp' => $item['discount_percent'],
					':da' => $item['discount_amount'],
					':sub' => $item['subtotal'],
					':ra' => $item['rule_applied'],
					':rd' => $item['rule_description']
				]);
			}

			$this->LogStatusHistory('ORDER', $orderId, null, $status, $user, 'Registro directo de pedido comercial');

			$db->commit();
			return $this->GetOrder($orderId);
		}
		catch (Exception $e)
		{
			$db->rollBack();
			throw $e;
		}
	}

	/**
	 * Actualizar estado de pedido (Confirmar o Cancelar)
	 */
	public function UpdateOrderStatus(int $orderId, string $newStatus, string $user = 'Carlos Mendoza', ?string $comment = null): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$order = $this->GetOrder($orderId);
		if (!$order)
		{
			throw new Exception('Pedido comercial no encontrado.');
		}

		$validStatuses = ['Borrador', 'Confirmado', 'Cancelado'];
		if (!in_array($newStatus, $validStatuses, true))
		{
			throw new Exception('Estado de pedido inválido.');
		}

		$fromStatus = $order['status'];
		$stmt = $db->prepare("UPDATE b2b_orders SET status = :st, updated_timestamp = datetime('now', 'localtime') WHERE id = :id");
		$stmt->execute([':st' => $newStatus, ':id' => $orderId]);

		$this->LogStatusHistory('ORDER', $orderId, $fromStatus, $newStatus, $user, $comment ?: "Cambio de estado a {$newStatus}");

		return $this->GetOrder($orderId);
	}

	/**
	 * Duplica un pedido existente creando un nuevo borrador o confirmado
	 */
	public function DuplicateOrder(int $orderId, string $user = 'Carlos Mendoza'): array
	{
		$this->EnsureSchema();
		$order = $this->GetOrder($orderId);
		if (!$order)
		{
			throw new Exception('Pedido original no encontrado para duplicar.');
		}

		$items = [];
		foreach ($order['items'] as $item)
		{
			$items[] = [
				'product_id' => $item['product_id'],
				'commercial_unit' => $item['commercial_unit'],
				'quantity' => $item['quantity']
			];
		}

		$data = [
			'customer_id' => $order['customer_id'],
			'establishment_id' => $order['establishment_id'],
			'sales_rep' => $user,
			'status' => 'Borrador',
			'notes' => "Duplicado de pedido {$order['order_number']}. " . ($order['notes'] ?? ''),
			'items' => $items
		];

		return $this->CreateOrder($data, $user);
	}
}
