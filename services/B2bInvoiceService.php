<?php

namespace Grocy\Services;

use Exception;
use PDO;

/**
 * Servicio de Facturación Comercial y Cuentas por Cobrar B2B
 * Gestión de Facturas, Pagos, Saldos y Notas de Crédito
 */
class B2bInvoiceService extends BaseService
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
	 * Asegura la existencia de las tablas de facturación, pagos, notas de crédito y auditoría
	 */
	public function EnsureSchema(): void
	{
		$db = $this->Db();

		$db->exec("
			-- Tabla de Facturas Comerciales B2B
			CREATE TABLE IF NOT EXISTS b2b_invoices (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				invoice_number TEXT NOT NULL UNIQUE,
				order_id INTEGER NULL,
				customer_id INTEGER NOT NULL,
				establishment_id INTEGER NULL,
				sales_rep TEXT NOT NULL DEFAULT 'Carlos Mendoza',
				issue_date DATE NOT NULL,
				due_date DATE NOT NULL,
				payment_terms TEXT NOT NULL DEFAULT 'contado', -- contado, credito_15, credito_30, credito_60
				status TEXT NOT NULL DEFAULT 'Emitida', -- Borrador, Emitida, Parcialmente pagada, Pagada, Anulada
				currency TEXT NOT NULL DEFAULT 'USD',
				subtotal REAL NOT NULL DEFAULT 0.00,
				tax_rate REAL NOT NULL DEFAULT 18.00,
				tax_amount REAL NOT NULL DEFAULT 0.00,
				discount_amount REAL NOT NULL DEFAULT 0.00,
				total REAL NOT NULL DEFAULT 0.00,
				paid_amount REAL NOT NULL DEFAULT 0.00,
				credit_amount REAL NOT NULL DEFAULT 0.00,
				balance REAL NOT NULL DEFAULT 0.00,
				notes TEXT NULL,
				created_by TEXT NOT NULL DEFAULT 'Carlos Mendoza',
				is_demo INTEGER NOT NULL DEFAULT 0,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				updated_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				FOREIGN KEY(order_id) REFERENCES b2b_orders(id),
				FOREIGN KEY(customer_id) REFERENCES customers(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_inv_order ON b2b_invoices(order_id);
			CREATE INDEX IF NOT EXISTS idx_b2b_inv_cust ON b2b_invoices(customer_id);
			CREATE INDEX IF NOT EXISTS idx_b2b_inv_status ON b2b_invoices(status);
			CREATE INDEX IF NOT EXISTS idx_b2b_inv_due ON b2b_invoices(due_date);

			-- Líneas de Ítems de la Factura (Snapshot histórico inmutable)
			CREATE TABLE IF NOT EXISTS b2b_invoice_items (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				invoice_id INTEGER NOT NULL,
				product_id INTEGER NOT NULL,
				commercial_unit TEXT NOT NULL DEFAULT 'CASE',
				quantity INTEGER NOT NULL,
				conversion_factor INTEGER NOT NULL DEFAULT 1,
				total_units INTEGER NOT NULL,
				base_price REAL NOT NULL,
				unit_price REAL NOT NULL,
				discount_percent REAL NOT NULL DEFAULT 0.00,
				discount_amount REAL NOT NULL DEFAULT 0.00,
				subtotal REAL NOT NULL,
				credited_quantity INTEGER NOT NULL DEFAULT 0,
				FOREIGN KEY(invoice_id) REFERENCES b2b_invoices(id) ON DELETE CASCADE,
				FOREIGN KEY(product_id) REFERENCES products(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_inv_items_inv ON b2b_invoice_items(invoice_id);

			-- Registro de Pagos / Abonos a Facturas
			CREATE TABLE IF NOT EXISTS b2b_invoice_payments (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				payment_number TEXT NOT NULL UNIQUE,
				invoice_id INTEGER NOT NULL,
				payment_date DATE NOT NULL,
				payment_method TEXT NOT NULL, -- Transferencia bancaria, Cheque, Efectivo, Tarjeta corporativa
				amount REAL NOT NULL,
				reference TEXT NULL,
				notes TEXT NULL,
				recorded_by TEXT NOT NULL DEFAULT 'Carlos Mendoza',
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				FOREIGN KEY(invoice_id) REFERENCES b2b_invoices(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_pay_inv ON b2b_invoice_payments(invoice_id);

			-- Notas de Crédito Comerciales
			CREATE TABLE IF NOT EXISTS b2b_credit_notes (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				credit_note_number TEXT NOT NULL UNIQUE,
				invoice_id INTEGER NOT NULL,
				customer_id INTEGER NOT NULL,
				reason TEXT NOT NULL, -- devolucion, diferencia_precio, descuento, ajuste_comercial
				status TEXT NOT NULL DEFAULT 'Emitida', -- Borrador, Emitida, Anulada
				currency TEXT NOT NULL DEFAULT 'USD',
				subtotal REAL NOT NULL DEFAULT 0.00,
				tax_rate REAL NOT NULL DEFAULT 18.00,
				tax_amount REAL NOT NULL DEFAULT 0.00,
				total REAL NOT NULL DEFAULT 0.00,
				notes TEXT NULL,
				created_by TEXT NOT NULL DEFAULT 'Carlos Mendoza',
				is_demo INTEGER NOT NULL DEFAULT 0,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				updated_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				FOREIGN KEY(invoice_id) REFERENCES b2b_invoices(id),
				FOREIGN KEY(customer_id) REFERENCES customers(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_cn_inv ON b2b_credit_notes(invoice_id);

			-- Líneas de Ítems de Notas de Crédito
			CREATE TABLE IF NOT EXISTS b2b_credit_note_items (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				credit_note_id INTEGER NOT NULL,
				product_id INTEGER NOT NULL,
				commercial_unit TEXT NOT NULL DEFAULT 'CASE',
				quantity INTEGER NOT NULL,
				unit_price REAL NOT NULL,
				subtotal REAL NOT NULL,
				FOREIGN KEY(credit_note_id) REFERENCES b2b_credit_notes(id) ON DELETE CASCADE,
				FOREIGN KEY(product_id) REFERENCES products(id)
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_cn_items_cn ON b2b_credit_note_items(credit_note_id);

			-- Historial y Auditoría de Facturación
			CREATE TABLE IF NOT EXISTS b2b_invoice_history (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				document_type TEXT NOT NULL, -- INVOICE, CREDIT_NOTE, PAYMENT
				document_id INTEGER NOT NULL,
				from_status TEXT NULL,
				to_status TEXT NOT NULL,
				changed_by TEXT NOT NULL DEFAULT 'Carlos Mendoza',
				comment TEXT NULL,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime'))
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_inv_hist_doc ON b2b_invoice_history(document_type, document_id);
		");

		$this->SeedDemoData();
	}

	public function GenerateInvoiceNumber(): string
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$year = date('Y');
		$maxSeq = 0;
		$rows = $db->query("SELECT invoice_number FROM b2b_invoices")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($rows as $num)
		{
			if (preg_match('/FAC-\d{4}-(\d+)/', $num, $matches))
			{
				$seq = (int)$matches[1];
				if ($seq > $maxSeq) $maxSeq = $seq;
			}
		}
		return sprintf("FAC-%s-%04d", $year, $maxSeq + 1);
	}

	public function GenerateCreditNoteNumber(): string
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$year = date('Y');
		$maxSeq = 0;
		$rows = $db->query("SELECT credit_note_number FROM b2b_credit_notes")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($rows as $num)
		{
			if (preg_match('/NC-\d{4}-(\d+)/', $num, $matches))
			{
				$seq = (int)$matches[1];
				if ($seq > $maxSeq) $maxSeq = $seq;
			}
		}
		return sprintf("NC-%s-%04d", $year, $maxSeq + 1);
	}

	public function GeneratePaymentNumber(): string
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$year = date('Y');
		$maxSeq = 0;
		$rows = $db->query("SELECT payment_number FROM b2b_invoice_payments")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($rows as $num)
		{
			if (preg_match('/PAG-\d{4}-(\d+)/', $num, $matches))
			{
				$seq = (int)$matches[1];
				if ($seq > $maxSeq) $maxSeq = $seq;
			}
		}
		return sprintf("PAG-%s-%04d", $year, $maxSeq + 1);
	}

	public function LogInvoiceHistory(string $docType, int $docId, ?string $fromStatus, string $toStatus, string $user, ?string $comment = null): void
	{
		$stmt = $this->Db()->prepare("
			INSERT INTO b2b_invoice_history (document_type, document_id, from_status, to_status, changed_by, comment)
			VALUES (:dt, :did, :fs, :ts, :user, :comm)
		");
		$stmt->execute([
			':dt' => strtoupper($docType),
			':did' => $docId,
			':fs' => $fromStatus,
			':ts' => $toStatus,
			':user' => $user,
			':comm' => $comment
		]);
	}

	/**
	 * Siembra DEMO idempotente de facturas, pagos y notas de crédito conectadas a pedidos
	 */
	public function SeedDemoData(): void
	{
		$db = $this->Db();
		$count = (int)$db->query("SELECT COUNT(*) FROM b2b_invoices")->fetchColumn();
		if ($count >= 8)
		{
			return;
		}

		// Asegurar pedidos de Paso 6
		B2bOrderService::GetInstance()->EnsureSchema();

		// Obtener pedidos confirmados
		$orders = $db->query("
			SELECT o.*
			FROM b2b_orders o
			WHERE o.status = 'Confirmado'
			ORDER BY o.id ASC
			LIMIT 10
		")->fetchAll(PDO::FETCH_ASSOC);

		if (empty($orders))
		{
			return;
		}

		$invoiceConfigs = [
			// 1. Pagada completa con transferencia
			['status' => 'Pagada', 'terms' => 'contado', 'paid_full' => true, 'notes' => 'Facturación de despacho principal'],
			// 2. Parcialmente pagada
			['status' => 'Parcialmente pagada', 'terms' => 'credito_30', 'paid_ratio' => 0.5, 'notes' => 'Primer abono registrado'],
			// 3. Emitida con saldo pendiente
			['status' => 'Emitida', 'terms' => 'credito_30', 'notes' => 'Abastecimiento programado'],
			// 4. Emitida con saldo pendiente
			['status' => 'Emitida', 'terms' => 'credito_15', 'notes' => 'Lote de reposición'],
			// 5. Factura Vencida (due_date en el pasado)
			['status' => 'Emitida', 'terms' => 'credito_15', 'overdue_days' => 8, 'notes' => 'Factura vencida en gestión de cobranza'],
			// 6. Pagada completa
			['status' => 'Pagada', 'terms' => 'contado', 'paid_full' => true, 'notes' => 'Cancelación contraentrega'],
			// 7. Parcialmente pagada con abono
			['status' => 'Parcialmente pagada', 'terms' => 'credito_30', 'paid_ratio' => 0.4, 'notes' => 'Abono 40% inicial'],
			// 8. Emitida con Nota de Crédito parcial asociada
			['status' => 'Emitida', 'terms' => 'credito_30', 'credit_note' => true, 'notes' => 'Factura con ajuste comercial aplicado']
		];

		$idx = 0;
		foreach ($orders as $ord)
		{
			if (!isset($invoiceConfigs[$idx]))
			{
				break;
			}
			$cfg = $invoiceConfigs[$idx++];
			$ordId = (int)$ord['id'];

			// Verificar si ya tiene factura
			$hasInv = (int)$db->query("SELECT COUNT(*) FROM b2b_invoices WHERE order_id = {$ordId}")->fetchColumn();
			if ($hasInv > 0)
			{
				continue;
			}

			$invNum = sprintf("FAC-2026-%04d", $idx);
			$issueDate = date('Y-m-d', strtotime('-' . ($idx * 2) . ' days'));
			
			if (!empty($cfg['overdue_days']))
			{
				$dueDate = date('Y-m-d', strtotime('-' . $cfg['overdue_days'] . ' days'));
			}
			else
			{
				$termsDays = ($cfg['terms'] === 'credito_30') ? 30 : (($cfg['terms'] === 'credito_15') ? 15 : 0);
				$dueDate = date('Y-m-d', strtotime($issueDate . " + {$termsDays} days"));
			}

			$subtotal = round((float)$ord['subtotal'], 2);
			$taxRate = 18.00;
			$taxAmount = round($subtotal * 0.18, 2);
			$discountAmount = round((float)$ord['discount_amount'], 2);
			$total = round($subtotal + $taxAmount, 2);

			$paidAmount = 0.00;
			if (!empty($cfg['paid_full']))
			{
				$paidAmount = $total;
			}
			elseif (!empty($cfg['paid_ratio']))
			{
				$paidAmount = round($total * $cfg['paid_ratio'], 2);
			}

			$creditAmount = 0.00;
			if (!empty($cfg['credit_note']))
			{
				$creditAmount = round(min(150.00, $total * 0.2), 2);
			}

			$balance = max(0.00, round($total - $paidAmount - $creditAmount, 2));
			$status = $cfg['status'];
			if ($balance <= 0.001)
			{
				$status = 'Pagada';
			}
			elseif ($paidAmount > 0.001 && $balance > 0.001)
			{
				$status = 'Parcialmente pagada';
			}

			$stmt = $db->prepare("
				INSERT INTO b2b_invoices (
					invoice_number, order_id, customer_id, establishment_id, sales_rep,
					issue_date, due_date, payment_terms, status, currency,
					subtotal, tax_rate, tax_amount, discount_amount, total,
					paid_amount, credit_amount, balance, notes, created_by, is_demo
				) VALUES (
					:num, :oid, :cid, :eid, :rep,
					:issue, :due, :terms, :st, 'USD',
					:sub, :tr, :tax, :disc, :tot,
					:paid, :cred, :bal, :notes, :user, 1
				)
			");
			$stmt->execute([
				':num' => $invNum,
				':oid' => $ordId,
				':cid' => (int)$ord['customer_id'],
				':eid' => !empty($ord['establishment_id']) ? (int)$ord['establishment_id'] : null,
				':rep' => $ord['sales_rep'] ?? 'Carlos Mendoza',
				':issue' => $issueDate,
				':due' => $dueDate,
				':terms' => $cfg['terms'],
				':st' => $status,
				':sub' => $subtotal,
				':tr' => $taxRate,
				':tax' => $taxAmount,
				':disc' => $discountAmount,
				':tot' => $total,
				':paid' => $paidAmount,
				':cred' => $creditAmount,
				':bal' => $balance,
				':notes' => $cfg['notes'],
				':user' => $ord['sales_rep'] ?? 'Carlos Mendoza'
			]);

			$invId = (int)$db->lastInsertId();

			// Copiar ítems del pedido (snapshot inmutable)
			$items = $db->query("SELECT * FROM b2b_order_items WHERE order_id = {$ordId}")->fetchAll(PDO::FETCH_ASSOC);
			foreach ($items as $it)
			{
				$stmtItem = $db->prepare("
					INSERT INTO b2b_invoice_items (
						invoice_id, product_id, commercial_unit, quantity, conversion_factor, total_units,
						base_price, unit_price, discount_percent, discount_amount, subtotal, credited_quantity
					) VALUES (
						:iid, :pid, :cu, :qty, :cf, :tu,
						:bp, :up, :dp, :da, :sub, 0
					)
				");
				$stmtItem->execute([
					':iid' => $invId,
					':pid' => (int)$it['product_id'],
					':cu' => $it['commercial_unit'],
					':qty' => (int)$it['quantity'],
					':cf' => (int)($it['conversion_factor'] ?? 1),
					':tu' => (int)($it['total_units'] ?? $it['quantity']),
					':bp' => (float)$it['base_price'],
					':up' => (float)$it['unit_price'],
					':dp' => (float)($it['discount_percent'] ?? 0),
					':da' => (float)($it['discount_amount'] ?? 0),
					':sub' => (float)$it['subtotal']
				]);
			}

			$this->LogInvoiceHistory('INVOICE', $invId, null, 'Emitida', 'Carlos Mendoza', 'Emisión inicial de factura desde pedido');

			// Registrar pagos si existen
			if ($paidAmount > 0.001)
			{
				$payNum = sprintf("PAG-2026-%04d", $idx);
				$stmtPay = $db->prepare("
					INSERT INTO b2b_invoice_payments (
						payment_number, invoice_id, payment_date, payment_method,
						amount, reference, notes, recorded_by
					) VALUES (
						:pnum, :iid, :pdate, :pmeth,
						:amt, :ref, :pnotes, 'Carlos Mendoza'
					)
				");
				$stmtPay->execute([
					':pnum' => $payNum,
					':iid' => $invId,
					':pdate' => $issueDate,
					':pmeth' => 'Transferencia bancaria',
					':amt' => $paidAmount,
					':ref' => 'TRX-' . rand(100000, 999999),
					':pnotes' => $paidAmount >= $total ? 'Liquidación total' : 'Abono parcial registrado'
				]);

				$this->LogInvoiceHistory('PAYMENT', $invId, 'Emitida', $status, 'Carlos Mendoza', "Abono de \${$paidAmount} registrado ({$payNum})");
			}

			// Registrar Nota de Crédito si aplica
			if ($creditAmount > 0.001)
			{
				$cnNum = sprintf("NC-2026-%04d", 1);
				$cnSubtotal = round($creditAmount / 1.18, 2);
				$cnTax = round($creditAmount - $cnSubtotal, 2);

				$stmtCn = $db->prepare("
					INSERT INTO b2b_credit_notes (
						credit_note_number, invoice_id, customer_id, reason, status,
						currency, subtotal, tax_rate, tax_amount, total, notes, created_by, is_demo
					) VALUES (
						:cnum, :iid, :cid, 'ajuste_comercial', 'Emitida',
						'USD', :sub, 18.00, :tax, :tot, 'Ajuste comercial acordado', 'Carlos Mendoza', 1
					)
				");
				$stmtCn->execute([
					':cnum' => $cnNum,
					':iid' => $invId,
					':cid' => (int)$ord['customer_id'],
					':sub' => $cnSubtotal,
					':tax' => $cnTax,
					':tot' => $creditAmount
				]);

				$cnId = (int)$db->lastInsertId();

				// Ítem para nota de crédito
				if (!empty($items[0]))
				{
					$stmtCnItem = $db->prepare("
						INSERT INTO b2b_credit_note_items (
							credit_note_id, product_id, commercial_unit, quantity, unit_price, subtotal
						) VALUES (
							:cnid, :pid, :cu, 1, :up, :sub
						)
					");
					$stmtCnItem->execute([
						':cnid' => $cnId,
						':pid' => (int)$items[0]['product_id'],
						':cu' => $items[0]['commercial_unit'],
						':up' => $cnSubtotal,
						':sub' => $cnSubtotal
					]);
				}

				$this->LogInvoiceHistory('CREDIT_NOTE', $invId, $status, $status, 'Carlos Mendoza', "Nota de Crédito {$cnNum} aplicada por \${$creditAmount}");
			}
		}
	}

	/**
	 * Crea una Factura Comercial a partir de un Pedido Confirmado
	 */
	public function CreateInvoiceFromOrder(int $orderId, array $options = [], string $user = 'Carlos Mendoza'): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmtOrd = $db->prepare("SELECT * FROM b2b_orders WHERE id = :id");
		$stmtOrd->execute([':id' => $orderId]);
		$order = $stmtOrd->fetch(PDO::FETCH_ASSOC);

		if (!$order)
		{
			throw new Exception("El pedido comercial #{$orderId} no existe.");
		}

		if ($order['status'] !== 'Confirmado')
		{
			throw new Exception("Solo se pueden facturar pedidos en estado 'Confirmado' (Estado actual: {$order['status']}).");
		}

		// Validar que no tenga ya una factura activa
		$stmtCheck = $db->prepare("
			SELECT invoice_number, status
			FROM b2b_invoices
			WHERE order_id = :oid AND status != 'Anulada'
		");
		$stmtCheck->execute([':oid' => $orderId]);
		$existingInv = $stmtCheck->fetch(PDO::FETCH_ASSOC);

		if ($existingInv)
		{
			throw new Exception("El pedido {$order['order_number']} ya fue facturado mediante el comprobante {$existingInv['invoice_number']} (Estado: {$existingInv['status']}).");
		}

		// Copiar ítems del pedido (snapshot estricto sin recalcular de catálogo)
		$stmtItems = $db->prepare("SELECT * FROM b2b_order_items WHERE order_id = :oid");
		$stmtItems->execute([':oid' => $orderId]);
		$orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

		if (empty($orderItems))
		{
			throw new Exception("El pedido {$order['order_number']} no contiene líneas de producto válidas para facturar.");
		}

		$paymentTerms = trim($options['payment_terms'] ?? 'contado');
		$validTerms = ['contado', 'credito_15', 'credito_30', 'credito_60'];
		if (!in_array($paymentTerms, $validTerms, true))
		{
			$paymentTerms = 'contado';
		}

		$issueDate = !empty($options['issue_date']) ? date('Y-m-d', strtotime($options['issue_date'])) : date('Y-m-d');
		
		if (!empty($options['due_date']))
		{
			$dueDate = date('Y-m-d', strtotime($options['due_date']));
		}
		else
		{
			$daysMap = ['contado' => 0, 'credito_15' => 15, 'credito_30' => 30, 'credito_60' => 60];
			$days = $daysMap[$paymentTerms] ?? 0;
			$dueDate = date('Y-m-d', strtotime($issueDate . " + {$days} days"));
		}

		$notes = trim($options['notes'] ?? $order['notes'] ?? '');
		$status = trim($options['status'] ?? 'Emitida');
		if (!in_array($status, ['Borrador', 'Emitida'], true))
		{
			$status = 'Emitida';
		}

		$subtotal = round((float)$order['subtotal'], 2);
		$taxRate = 18.00;
		$taxAmount = round($subtotal * 0.18, 2);
		$discountAmount = round((float)($order['discount_amount'] ?? 0), 2);
		$total = round($subtotal + $taxAmount, 2);
		$balance = $total;

		$invNum = $this->GenerateInvoiceNumber();

		$db->beginTransaction();
		try
		{
			$stmt = $db->prepare("
				INSERT INTO b2b_invoices (
					invoice_number, order_id, customer_id, establishment_id, sales_rep,
					issue_date, due_date, payment_terms, status, currency,
					subtotal, tax_rate, tax_amount, discount_amount, total,
					paid_amount, credit_amount, balance, notes, created_by, is_demo
				) VALUES (
					:num, :oid, :cid, :eid, :rep,
					:issue, :due, :terms, :st, 'USD',
					:sub, :tr, :tax, :disc, :tot,
					0.00, 0.00, :bal, :notes, :user, 0
				)
			");
			$stmt->execute([
				':num' => $invNum,
				':oid' => $orderId,
				':cid' => (int)$order['customer_id'],
				':eid' => !empty($order['establishment_id']) ? (int)$order['establishment_id'] : null,
				':rep' => $order['sales_rep'] ?: $user,
				':issue' => $issueDate,
				':due' => $dueDate,
				':terms' => $paymentTerms,
				':st' => $status,
				':sub' => $subtotal,
				':tr' => $taxRate,
				':tax' => $taxAmount,
				':disc' => $discountAmount,
				':tot' => $total,
				':bal' => $balance,
				':notes' => $notes,
				':user' => $user
			]);

			$invId = (int)$db->lastInsertId();

			// Insertar ítems
			$stmtItem = $db->prepare("
				INSERT INTO b2b_invoice_items (
					invoice_id, product_id, commercial_unit, quantity, conversion_factor, total_units,
					base_price, unit_price, discount_percent, discount_amount, subtotal, credited_quantity
				) VALUES (
					:iid, :pid, :cu, :qty, :cf, :tu,
					:bp, :up, :dp, :da, :sub, 0
				)
			");

			foreach ($orderItems as $it)
			{
				$stmtItem->execute([
					':iid' => $invId,
					':pid' => (int)$it['product_id'],
					':cu' => $it['commercial_unit'],
					':qty' => (int)$it['quantity'],
					':cf' => (int)($it['conversion_factor'] ?? 1),
					':tu' => (int)($it['total_units'] ?? $it['quantity']),
					':bp' => (float)$it['base_price'],
					':up' => (float)$it['unit_price'],
					':dp' => (float)($it['discount_percent'] ?? 0),
					':da' => (float)($it['discount_amount'] ?? 0),
					':sub' => (float)$it['subtotal']
				]);
			}

			$this->LogInvoiceHistory('INVOICE', $invId, null, $status, $user, "Factura emitida desde pedido {$order['order_number']}");

			$db->commit();
			return $this->GetInvoice($invId);
		}
		catch (Exception $e)
		{
			$db->rollBack();
			throw $e;
		}
	}

	/**
	 * Registra un pago parcial o total a una factura
	 */
	public function RegisterPayment(int $invoiceId, array $data, string $user = 'Carlos Mendoza'): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$inv = $this->GetInvoice($invoiceId);
		if (!$inv)
		{
			throw new Exception("La factura comercial #{$invoiceId} no existe.");
		}

		if ($inv['status'] === 'Anulada')
		{
			throw new Exception("No se pueden registrar pagos en una factura anulada.");
		}

		if ($inv['status'] === 'Borrador')
		{
			throw new Exception("No se pueden registrar pagos en una factura en borrador. Debe ser emitida primero.");
		}

		$amount = round((float)($data['amount'] ?? 0), 2);
		if ($amount <= 0.00)
		{
			throw new Exception("El importe del pago debe ser mayor a 0.");
		}

		$currentBalance = round((float)$inv['balance'], 2);
		if ($amount > ($currentBalance + 0.001))
		{
			throw new Exception(sprintf("El importe del pago ($%.2f) excede el saldo pendiente disponible ($%.2f).", $amount, $currentBalance));
		}

		$payDate = !empty($data['payment_date']) ? date('Y-m-d', strtotime($data['payment_date'])) : date('Y-m-d');
		$payMethod = trim($data['payment_method'] ?? 'Transferencia bancaria');
		$ref = trim($data['reference'] ?? '');
		$notes = trim($data['notes'] ?? '');

		// Protección contra doble clic / reintento duplicado en 30s
		$stmtDup = $db->prepare("
			SELECT id FROM b2b_invoice_payments
			WHERE invoice_id = :iid AND amount = :amt AND payment_date = :pdate AND reference = :ref
			AND created_timestamp >= datetime('now', '-30 seconds')
		");
		$stmtDup->execute([
			':iid' => $invoiceId,
			':amt' => $amount,
			':pdate' => $payDate,
			':ref' => $ref
		]);
		if ($stmtDup->fetch())
		{
			throw new Exception("Se detectó un pago idéntico procesado recientemente. Evite duplicar la transacción.");
		}

		$payNum = $this->GeneratePaymentNumber();

		$db->beginTransaction();
		try
		{
			// 1. Insertar pago
			$stmtPay = $db->prepare("
				INSERT INTO b2b_invoice_payments (
					payment_number, invoice_id, payment_date, payment_method,
					amount, reference, notes, recorded_by
				) VALUES (
					:num, :iid, :pdate, :pmeth,
					:amt, :ref, :notes, :user
				)
			");
			$stmtPay->execute([
				':num' => $payNum,
				':iid' => $invoiceId,
				':pdate' => $payDate,
				':pmeth' => $payMethod,
				':amt' => $amount,
				':ref' => $ref,
				':notes' => $notes,
				':user' => $user
			]);

			// 2. Actualizar totales y estado de factura
			$newPaid = round((float)$inv['paid_amount'] + $amount, 2);
			$newBalance = max(0.00, round((float)$inv['total'] - $newPaid - (float)$inv['credit_amount'], 2));

			$newStatus = ($newBalance <= 0.001) ? 'Pagada' : 'Parcialmente pagada';

			$stmtUpd = $db->prepare("
				UPDATE b2b_invoices
				SET paid_amount = :paid, balance = :bal, status = :st, updated_timestamp = datetime('now', 'localtime')
				WHERE id = :id
			");
			$stmtUpd->execute([
				':paid' => $newPaid,
				':bal' => $newBalance,
				':st' => $newStatus,
				':id' => $invoiceId
			]);

			$this->LogInvoiceHistory('PAYMENT', $invoiceId, $inv['status'], $newStatus, $user, "Pago {$payNum} registrado por \${$amount} ({$payMethod})");

			$db->commit();
			return $this->GetInvoice($invoiceId);
		}
		catch (Exception $e)
		{
			$db->rollBack();
			throw $e;
		}
	}

	/**
	 * Emite una Nota de Crédito B2B vinculada a una factura emitida
	 */
	public function CreateCreditNote(int $invoiceId, array $data, string $user = 'Carlos Mendoza'): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$inv = $this->GetInvoice($invoiceId);
		if (!$inv)
		{
			throw new Exception("La factura comercial #{$invoiceId} no existe.");
		}

		if (!in_array($inv['status'], ['Emitida', 'Parcialmente pagada', 'Pagada'], true))
		{
			throw new Exception("Solo se pueden emitir notas de crédito para facturas válidas (Estado actual: {$inv['status']}).");
		}

		$reason = trim($data['reason'] ?? 'ajuste_comercial');
		$validReasons = ['devolucion', 'diferencia_precio', 'descuento', 'ajuste_comercial'];
		if (!in_array($reason, $validReasons, true))
		{
			$reason = 'ajuste_comercial';
		}

		$notes = trim($data['notes'] ?? '');
		$rawItems = (array)($data['items'] ?? []);

		$cnSubtotal = 0.00;
		$parsedItems = [];

		if (!empty($rawItems))
		{
			// Cálculo detallado por líneas de productos devueltos / ajustados
			foreach ($rawItems as $it)
			{
				$pid = (int)($it['product_id'] ?? 0);
				$qty = (int)($it['quantity'] ?? 0);
				$unitPrice = round((float)($it['unit_price'] ?? 0), 2);
				$commUnit = trim($it['commercial_unit'] ?? 'CASE');

				if ($pid > 0 && $qty > 0 && $unitPrice > 0)
				{
					$lineSub = round($qty * $unitPrice, 2);
					$cnSubtotal = round($cnSubtotal + $lineSub, 2);
					$parsedItems[] = [
						'product_id' => $pid,
						'commercial_unit' => $commUnit,
						'quantity' => $qty,
						'unit_price' => $unitPrice,
						'subtotal' => $lineSub
					];
				}
			}
		}

		// Si no hay líneas detalladas, usar monto directo
		if ($cnSubtotal <= 0.00)
		{
			$directTotal = round((float)($data['total'] ?? $data['amount'] ?? 0), 2);
			if ($directTotal <= 0.00)
			{
				throw new Exception("Debe especificar al menos una línea de producto o un importe válido para la nota de crédito.");
			}
			$cnSubtotal = round($directTotal / 1.18, 2);
			$cnTax = round($directTotal - $cnSubtotal, 2);
			$cnTotal = $directTotal;
		}
		else
		{
			$cnTax = round($cnSubtotal * 0.18, 2);
			$cnTotal = round($cnSubtotal + $cnTax, 2);
		}

		// Validar que no exceda el saldo acreditable disponible (considerando créditos anteriores)
		$availableToCredit = round((float)$inv['total'] - (float)$inv['credit_amount'], 2);
		if ($cnTotal > ($availableToCredit + 0.001))
		{
			throw new Exception(sprintf("El monto de la nota de crédito ($%.2f) excede el saldo acreditable disponible ($%.2f) de la factura.", $cnTotal, $availableToCredit));
		}

		$cnNum = $this->GenerateCreditNoteNumber();

		$db->beginTransaction();
		try
		{
			// 1. Insertar Nota de Crédito
			$stmtCn = $db->prepare("
				INSERT INTO b2b_credit_notes (
					credit_note_number, invoice_id, customer_id, reason, status,
					currency, subtotal, tax_rate, tax_amount, total, notes, created_by, is_demo
				) VALUES (
					:cnum, :iid, :cid, :reason, 'Emitida',
					'USD', :sub, 18.00, :tax, :tot, :notes, :user, 0
				)
			");
			$stmtCn->execute([
				':cnum' => $cnNum,
				':iid' => $invoiceId,
				':cid' => (int)$inv['customer_id'],
				':reason' => $reason,
				':sub' => $cnSubtotal,
				':tax' => $cnTax,
				':tot' => $cnTotal,
				':notes' => $notes,
				':user' => $user
			]);

			$cnId = (int)$db->lastInsertId();

			// 2. Insertar ítems si existen
			if (!empty($parsedItems))
			{
				$stmtCnItem = $db->prepare("
					INSERT INTO b2b_credit_note_items (
						credit_note_id, product_id, commercial_unit, quantity, unit_price, subtotal
					) VALUES (
						:cnid, :pid, :cu, :qty, :up, :sub
					)
				");
				foreach ($parsedItems as $pit)
				{
					$stmtCnItem->execute([
						':cnid' => $cnId,
						':pid' => $pit['product_id'],
						':cu' => $pit['commercial_unit'],
						':qty' => $pit['quantity'],
						':up' => $pit['unit_price'],
						':sub' => $pit['subtotal']
					]);

					// Actualizar credited_quantity en la factura
					$db->prepare("
						UPDATE b2b_invoice_items
						SET credited_quantity = credited_quantity + :qty
						WHERE invoice_id = :iid AND product_id = :pid
					")->execute([
						':qty' => $pit['quantity'],
						':iid' => $invoiceId,
						':pid' => $pit['product_id']
					]);
				}
			}

			// 3. Actualizar factura (credit_amount y balance)
			$newCredit = round((float)$inv['credit_amount'] + $cnTotal, 2);
			$newBalance = max(0.00, round((float)$inv['total'] - (float)$inv['paid_amount'] - $newCredit, 2));

			$newStatus = $inv['status'];
			if ($newBalance <= 0.001)
			{
				$newStatus = 'Pagada';
			}

			$stmtUpd = $db->prepare("
				UPDATE b2b_invoices
				SET credit_amount = :cred, balance = :bal, status = :st, updated_timestamp = datetime('now', 'localtime')
				WHERE id = :id
			");
			$stmtUpd->execute([
				':cred' => $newCredit,
				':bal' => $newBalance,
				':st' => $newStatus,
				':id' => $invoiceId
			]);

			$this->LogInvoiceHistory('CREDIT_NOTE', $invoiceId, $inv['status'], $newStatus, $user, "Nota de crédito {$cnNum} emitida por \${$cnTotal} ({$reason})");

			$db->commit();
			return [
				'credit_note' => $this->GetCreditNote($cnId),
				'invoice' => $this->GetInvoice($invoiceId)
			];
		}
		catch (Exception $e)
		{
			$db->rollBack();
			throw $e;
		}
	}

	/**
	 * Actualiza el estado de una factura con validación de pagos y notas de crédito
	 */
	public function UpdateInvoiceStatus(int $id, string $newStatus, string $user = 'Carlos Mendoza', ?string $comment = null): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$inv = $this->GetInvoice($id);
		if (!$inv)
		{
			throw new Exception("La factura comercial #{$id} no existe.");
		}

		$validStatuses = ['Borrador', 'Emitida', 'Parcialmente pagada', 'Pagada', 'Anulada'];
		if (!in_array($newStatus, $validStatuses, true))
		{
			throw new Exception("Estado '{$newStatus}' no válido.");
		}

		// Validaciones estrictas de anulación
		if ($newStatus === 'Anulada')
		{
			if ((float)$inv['paid_amount'] > 0.001)
			{
				throw new Exception(sprintf("No se puede anular la factura %s porque registra pagos abonados ($%.2f). Debe revertir los pagos primero.", $inv['invoice_number'], $inv['paid_amount']));
			}
			if ((float)$inv['credit_amount'] > 0.001)
			{
				throw new Exception(sprintf("No se puede anular la factura %s porque tiene notas de crédito aplicadas ($%.2f). Debe anular primero las notas de crédito.", $inv['invoice_number'], $inv['credit_amount']));
			}
		}

		$stmt = $db->prepare("
			UPDATE b2b_invoices
			SET status = :st, updated_timestamp = datetime('now', 'localtime')
			WHERE id = :id
		");
		$stmt->execute([':st' => $newStatus, ':id' => $id]);

		$this->LogInvoiceHistory('INVOICE', $id, $inv['status'], $newStatus, $user, $comment ?: "Cambio manual de estado a {$newStatus}");

		return $this->GetInvoice($id);
	}

	/**
	 * Obtiene lista de facturas con filtros, paginación y estado de vencimiento
	 */
	public function GetInvoices(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$where = ["1=1"];
		$params = [];

		if (!empty($filters['search']))
		{
			$term = '%' . trim($filters['search']) . '%';
			$where[] = "(i.invoice_number LIKE :s1 OR c.name LIKE :s2 OR o.order_number LIKE :s3 OR i.sales_rep LIKE :s4)";
			$params[':s1'] = $term;
			$params[':s2'] = $term;
			$params[':s3'] = $term;
			$params[':s4'] = $term;
		}

		if (!empty($filters['status']))
		{
			$where[] = "i.status = :st";
			$params[':st'] = trim($filters['status']);
		}

		if (!empty($filters['customer_id']))
		{
			$where[] = "i.customer_id = :cid";
			$params[':cid'] = (int)$filters['customer_id'];
		}

		if (!empty($filters['overdue_only']))
		{
			$where[] = "i.due_date < date('now', 'localtime') AND i.balance > 0.01 AND i.status != 'Anulada'";
		}

		if (!empty($filters['from_date']))
		{
			$where[] = "i.issue_date >= :from_date";
			$params[':from_date'] = trim($filters['from_date']);
		}

		if (!empty($filters['to_date']))
		{
			$where[] = "i.issue_date <= :to_date";
			$params[':to_date'] = trim($filters['to_date']);
		}

		$whereSql = implode(' AND ', $where);

		$sql = "
			SELECT
				i.*,
				c.name AS customer_name,
				c.customer_type,
				e.name AS establishment_name,
				e.city AS establishment_city,
				o.order_number,
				(SELECT COUNT(*) FROM b2b_invoice_items WHERE invoice_id = i.id) AS items_count,
				(SELECT COUNT(*) FROM b2b_invoice_payments WHERE invoice_id = i.id) AS payments_count,
				(SELECT COUNT(*) FROM b2b_credit_notes WHERE invoice_id = i.id AND status = 'Emitida') AS credit_notes_count,
				CASE WHEN i.due_date < date('now', 'localtime') AND i.balance > 0.01 AND i.status != 'Anulada' THEN 1 ELSE 0 END AS is_overdue
			FROM b2b_invoices i
			JOIN customers c ON i.customer_id = c.id
			LEFT JOIN customer_establishments e ON i.establishment_id = e.id
			LEFT JOIN b2b_orders o ON i.order_id = o.id
			WHERE {$whereSql}
			ORDER BY i.id DESC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Obtiene el detalle completo de una factura con ítems, pagos, notas de crédito e historial
	 */
	public function GetInvoice(int $id): ?array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->prepare("
			SELECT
				i.*,
				c.name AS customer_name,
				c.tax_id AS customer_tax_id,
				c.customer_type,
				c.email AS customer_email,
				c.phone AS customer_phone,
				e.name AS establishment_name,
				e.address AS establishment_address,
				e.city AS establishment_city,
				o.order_number,
				CASE WHEN i.due_date < date('now', 'localtime') AND i.balance > 0.01 AND i.status != 'Anulada' THEN 1 ELSE 0 END AS is_overdue
			FROM b2b_invoices i
			JOIN customers c ON i.customer_id = c.id
			LEFT JOIN customer_establishments e ON i.establishment_id = e.id
			LEFT JOIN b2b_orders o ON i.order_id = o.id
			WHERE i.id = :id
		");
		$stmt->execute([':id' => $id]);
		$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$invoice)
		{
			return null;
		}

		// Ítems de la factura
		$stmtItems = $db->prepare("
			SELECT
				ii.*,
				p.name AS product_name,
				p.sku AS product_sku,
				p.brand AS product_brand
			FROM b2b_invoice_items ii
			JOIN products p ON ii.product_id = p.id
			WHERE ii.invoice_id = :id
			ORDER BY ii.id ASC
		");
		$stmtItems->execute([':id' => $id]);
		$invoice['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

		// Pagos registrados
		$stmtPay = $db->prepare("
			SELECT * FROM b2b_invoice_payments
			WHERE invoice_id = :id
			ORDER BY id DESC
		");
		$stmtPay->execute([':id' => $id]);
		$invoice['payments'] = $stmtPay->fetchAll(PDO::FETCH_ASSOC);

		// Notas de Crédito asociadas
		$stmtCn = $db->prepare("
			SELECT * FROM b2b_credit_notes
			WHERE invoice_id = :id
			ORDER BY id DESC
		");
		$stmtCn->execute([':id' => $id]);
		$invoice['credit_notes'] = $stmtCn->fetchAll(PDO::FETCH_ASSOC);

		// Historial de auditoría
		$stmtHist = $db->prepare("
			SELECT * FROM b2b_invoice_history
			WHERE document_id = :id
			ORDER BY id DESC
		");
		$stmtHist->execute([':id' => $id]);
		$invoice['history'] = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

		return $invoice;
	}

	public function GetCreditNotes(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$sql = "
			SELECT
				cn.*,
				i.invoice_number,
				c.name AS customer_name,
				c.tax_id AS customer_tax_id
			FROM b2b_credit_notes cn
			JOIN b2b_invoices i ON cn.invoice_id = i.id
			JOIN customers c ON cn.customer_id = c.id
			ORDER BY cn.id DESC
		";
		return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function GetCreditNote(int $id): ?array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->prepare("
			SELECT
				cn.*,
				i.invoice_number,
				i.total AS invoice_total,
				c.name AS customer_name,
				c.tax_id AS customer_tax_id
			FROM b2b_credit_notes cn
			JOIN b2b_invoices i ON cn.invoice_id = i.id
			JOIN customers c ON cn.customer_id = c.id
			WHERE cn.id = :id
		");
		$stmt->execute([':id' => $id]);
		$cn = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$cn)
		{
			return null;
		}

		$stmtItems = $db->prepare("
			SELECT
				cni.*,
				p.name AS product_name,
				p.sku AS product_sku
			FROM b2b_credit_note_items cni
			JOIN products p ON cni.product_id = p.id
			WHERE cni.credit_note_id = :id
		");
		$stmtItems->execute([':id' => $id]);
		$cn['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

		return $cn;
	}

	/**
	 * Métricas Financieras y Ventas Netas B2B
	 * Fórmula: Ventas Netas = Ventas facturadas válidas - Notas de crédito emitidas válidas (importes antes de impuestos)
	 */
	public function GetMetrics(): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		// 1. Facturas válidas (excluye Borrador y Anulada)
		$validInvoices = $db->query("
			SELECT
				COUNT(*) AS total_count,
				COALESCE(SUM(subtotal), 0) AS sum_subtotal,
				COALESCE(SUM(total), 0) AS sum_total,
				COALESCE(SUM(paid_amount), 0) AS sum_paid,
				COALESCE(SUM(credit_amount), 0) AS sum_credits,
				COALESCE(SUM(balance), 0) AS sum_balance
			FROM b2b_invoices
			WHERE status IN ('Emitida', 'Parcialmente pagada', 'Pagada')
		")->fetch(PDO::FETCH_ASSOC);

		// 2. Notas de crédito válidas (status = 'Emitida')
		$validCredits = $db->query("
			SELECT
				COUNT(*) AS total_count,
				COALESCE(SUM(subtotal), 0) AS sum_subtotal,
				COALESCE(SUM(total), 0) AS sum_total
			FROM b2b_credit_notes
			WHERE status = 'Emitida'
		")->fetch(PDO::FETCH_ASSOC);

		// 3. Facturas vencidas
		$overdue = $db->query("
			SELECT
				COUNT(*) AS overdue_count,
				COALESCE(SUM(balance), 0) AS overdue_balance
			FROM b2b_invoices
			WHERE due_date < date('now', 'localtime') AND balance > 0.01 AND status != 'Anulada'
		")->fetch(PDO::FETCH_ASSOC);

		// Cálculo de Ventas Netas antes de impuestos
		$grossSubtotal = (float)$validInvoices['sum_subtotal'];
		$creditsSubtotal = (float)$validCredits['sum_subtotal'];
		$netSales = max(0.00, round($grossSubtotal - $creditsSubtotal, 2));

		return [
			'net_sales' => number_format($netSales, 2, '.', ''),
			'gross_invoiced_subtotal' => number_format($grossSubtotal, 2, '.', ''),
			'credit_notes_subtotal' => number_format($creditsSubtotal, 2, '.', ''),
			'total_invoiced_gross' => number_format((float)$validInvoices['sum_total'], 2, '.', ''),
			'total_collected' => number_format((float)$validInvoices['sum_paid'], 2, '.', ''),
			'total_receivables' => number_format((float)$validInvoices['sum_balance'], 2, '.', ''),
			'total_overdue' => number_format((float)$overdue['overdue_balance'], 2, '.', ''),
			'overdue_count' => (int)$overdue['overdue_count'],
			'active_invoices_count' => (int)$validInvoices['total_count'],
			'credit_notes_count' => (int)$validCredits['total_count']
		];
	}

	/**
	 * Genera plantilla HTML descargable / imprimible para la Factura Comercial B2B
	 */
	public function RenderInvoicePrintHtml(int $id): string
	{
		$inv = $this->GetInvoice($id);
		if (!$inv)
		{
			throw new Exception("Factura #{$id} no encontrada.");
		}

		$itemsHtml = '';
		foreach ($inv['items'] as $it)
		{
			$unitLabel = ($it['commercial_unit'] === 'CASE') ? 'Caja' : 'Unidad';
			$itemsHtml .= "
				<tr>
					<td style='padding: 8px; border-bottom: 1px solid #e2e8f0; font-family: monospace;'>{$it['product_sku']}</td>
					<td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>
						<strong>{$it['product_name']}</strong><br>
						<span style='font-size: 11px; color: #64748b;'>{$it['product_brand']}</span>
					</td>
					<td style='padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: center;'>{$unitLabel}</td>
					<td style='padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: center; font-weight: bold;'>{$it['quantity']}</td>
					<td style='padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: right; font-family: monospace;'>$" . number_format($it['unit_price'], 2) . "</td>
					<td style='padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: right; font-family: monospace; font-weight: bold;'>$" . number_format($it['subtotal'], 2) . "</td>
				</tr>
			";
		}

		$statusColor = ($inv['status'] === 'Pagada') ? '#059669' : (($inv['status'] === 'Anulada') ? '#e11d48' : '#2563eb');

		return "<!DOCTYPE html>
		<html lang='es'>
		<head>
			<meta charset='UTF-8'>
			<title>Factura Comercial {$inv['invoice_number']} — Prince Distributors</title>
			<style>
				body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 24px; color: #0f172a; background: #fff; }
				.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 16px; }
				.brand { font-size: 22px; font-weight: bold; letter-spacing: -0.5px; }
				.brand span { color: #2563eb; font-size: 14px; display: block; font-weight: normal; }
				.doc-info { text-align: right; }
				.doc-number { font-size: 20px; font-weight: bold; font-family: monospace; }
				.status-badge { display: inline-block; padding: 3px 10px; font-size: 11px; font-weight: bold; border-radius: 9999px; background: #f1f5f9; color: {$statusColor}; margin-top: 4px; }
				.meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; background: #f8fafc; padding: 14px; border-radius: 8px; font-size: 12px; }
				table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 15px; }
				th { background: #f1f5f9; padding: 10px 8px; text-align: left; font-weight: 600; border-bottom: 2px solid #cbd5e1; }
				.totals { margin-top: 20px; margin-left: auto; width: 320px; font-size: 13px; }
				.totals-row { display: flex; justify-content: space-between; padding: 4px 0; }
				.totals-grand { font-size: 16px; font-weight: bold; border-top: 2px solid #0f172a; padding-top: 6px; margin-top: 6px; }
				.footer { margin-top: 36px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #64748b; text-align: center; }
				@media print {
					body { padding: 0; }
					.no-print { display: none; }
				}
			</style>
		</head>
		<body>
			<div class='no-print' style='margin-bottom: 16px;'>
				<button onclick='window.print()' style='padding: 8px 16px; background: #0f172a; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;'>Imprimir / Guardar en PDF</button>
			</div>
			<div class='header'>
				<div class='brand'>
					PRINCE DISTRIBUTORS
					<span>Wholesale B2B Distribution Suite — RUC 20549182391</span>
				</div>
				<div class='doc-info'>
					<div class='doc-number'>{$inv['invoice_number']}</div>
					<div class='status-badge'>ESTADO: " . strtoupper($inv['status']) . "</div>
					<div style='font-size: 11px; color: #64748b; margin-top: 4px;'>Pedido Ref: " . ($inv['order_number'] ?: 'Directo') . "</div>
				</div>
			</div>

			<div class='meta-grid'>
				<div>
					<strong>CLIENTE COMERCIAL:</strong><br>
					<span style='font-size: 14px; font-weight: bold;'>{$inv['customer_name']}</span><br>
					ID Fiscal / RUC: " . ($inv['customer_tax_id'] ?: 'Sin registrar') . "<br>
					Tipo: " . ucfirst($inv['customer_type'] ?: 'Mayorista') . "<br>
					Establecimiento: " . ($inv['establishment_name'] ?: 'Sede Central') . "<br>
					Dirección: " . ($inv['establishment_address'] ?: 'Oficina comercial') . "
				</div>
				<div>
					<strong>DATOS COMERCIALES & CONDICIONES:</strong><br>
					Fecha de Emisión: {$inv['issue_date']}<br>
					Fecha de Vencimiento: <strong>{$inv['due_date']}</strong><br>
					Condición de Pago: " . strtoupper(str_replace('_', ' ', $inv['payment_terms'])) . "<br>
					Ejecutivo Responsable: {$inv['sales_rep']}<br>
					Moneda: {$inv['currency']} (Dólares Americanos)
				</div>
			</div>

			<table>
				<thead>
					<tr>
						<th>SKU</th>
						<th>Descripción del Producto</th>
						<th style='text-align: center;'>Empaque</th>
						<th style='text-align: center;'>Cantidad</th>
						<th style='text-align: right;'>Precio Unit.</th>
						<th style='text-align: right;'>Subtotal</th>
					</tr>
				</thead>
				<tbody>
					{$itemsHtml}
				</tbody>
			</table>

			<div class='totals'>
				<div class='totals-row'><span>Subtotal Gravado:</span><span style='font-family: monospace;'>$" . number_format($inv['subtotal'], 2) . "</span></div>
				<div class='totals-row'><span>Impuestos (IGV 18%):</span><span style='font-family: monospace;'>$" . number_format($inv['tax_amount'], 2) . "</span></div>
				<div class='totals-row totals-grand'><span>Total Factura:</span><span style='font-family: monospace;'>$" . number_format($inv['total'], 2) . "</span></div>
				<div class='totals-row' style='color: #059669;'><span>Total Pagado / Abonado:</span><span style='font-family: monospace;'>-$" . number_format($inv['paid_amount'], 2) . "</span></div>
				<div class='totals-row' style='color: #7c3aed;'><span>Notas de Crédito Aplicadas:</span><span style='font-family: monospace;'>-$" . number_format($inv['credit_amount'], 2) . "</span></div>
				<div class='totals-row' style='font-weight: bold; border-top: 1px dashed #cbd5e1; margin-top: 4px; padding-top: 4px;'><span>Saldo Pendiente:</span><span style='font-family: monospace; color: #e11d48;'>$" . number_format($inv['balance'], 2) . "</span></div>
			</div>

			<div style='margin-top: 24px; padding: 12px; background: #f8fafc; border-left: 3px solid #0f172a; font-size: 11px;'>
				<strong>Observaciones Comerciales:</strong><br>
				" . ($inv['notes'] ?: 'Facturación comercial estándar. Documento emitido para fines de control comercial interno y distribución mayorista B2B.') . "
			</div>

			<div class='footer'>
				Documento generado electrónicamente por la Plataforma Mayorista B2B Prince Distributors. No reemplaza comprobantes tributarios fiscales oficiales de la jurisdicción local.
			</div>
		</body>
		</html>";
	}
}
