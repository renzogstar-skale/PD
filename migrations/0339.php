<?php

// Migration 0339: PASO 13 — Base de Datos Integral B2B (Organizaciones, Marcas, Categorías, Contactos, Direcciones, Oportunidades, Cuotas, Auditoría y Configuración)

use Grocy\Services\DatabaseService;

$db = DatabaseService::GetInstance()->GetDbConnectionRaw();

// 1. ORGANIZACIONES B2B (Multi-tenant & Entidad Raíz)
$db->exec("
CREATE TABLE IF NOT EXISTS b2b_organizations (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	code TEXT NOT NULL UNIQUE,
	name TEXT NOT NULL,
	legal_name TEXT,
	tax_id TEXT NOT NULL UNIQUE,
	email TEXT,
	phone TEXT,
	address TEXT,
	city TEXT DEFAULT 'Bogotá',
	country TEXT DEFAULT 'Colombia',
	currency TEXT DEFAULT 'USD',
	timezone TEXT DEFAULT 'America/Bogota',
	credit_policy_days INTEGER DEFAULT 30,
	default_tax_rate REAL DEFAULT 18.00,
	min_order_amount REAL DEFAULT 200.00,
	active INTEGER NOT NULL DEFAULT 1,
	settings_json TEXT,
	created_at DATETIME DEFAULT (datetime('now', 'localtime')),
	updated_at DATETIME DEFAULT (datetime('now', 'localtime'))
);
");

// Sembrar Organización Principal si no existe
$orgCount = $db->query("SELECT COUNT(*) FROM b2b_organizations WHERE id = 1")->fetchColumn();
if ($orgCount == 0) {
	$stmt = $db->prepare("
		INSERT INTO b2b_organizations (id, code, name, legal_name, tax_id, email, phone, address, city, country, currency, timezone, credit_policy_days, default_tax_rate, min_order_amount, active)
		VALUES (1, 'ORG-PRINCE', 'Prince Distributors', 'Prince Distributors S.A.S.', 'J-30495821-0', 'contacto@princedistributors.com', '+57 1 800 555 0199', 'Av. Industrial 45-80, Zona Franca', 'Bogotá', 'Colombia', 'USD', 'America/Bogota', 30, 18.00, 200.00, 1)
	");
	$stmt->execute();
}

// 2. AGREGAR organization_id A TABLAS EXISTENTES (Si no existe ya)
$tablesToAlter = [
	'customers',
	'customer_establishments',
	'b2b_products',
	'b2b_quotes',
	'b2b_orders',
	'b2b_invoices',
	'b2b_credit_notes',
	'b2b_sales_reps',
	'b2b_territories',
	'b2b_commercial_alerts'
];

foreach ($tablesToAlter as $tbl) {
	$cols = $db->query("PRAGMA table_info($tbl)")->fetchAll(\PDO::FETCH_ASSOC);
	$hasOrg = false;
	foreach ($cols as $c) {
		if ($c['name'] === 'organization_id') {
			$hasOrg = true;
			break;
		}
	}
	if (!$hasOrg) {
		$db->exec("ALTER TABLE $tbl ADD COLUMN organization_id INTEGER DEFAULT 1");
	}
	// Asegurar que las filas existentes pertenezcan a la Org 1
	$db->exec("UPDATE $tbl SET organization_id = 1 WHERE organization_id IS NULL");
}

// 3. MARCAS Y CATEGORÍAS NORMALIZADAS
$db->exec("
CREATE TABLE IF NOT EXISTS b2b_brands (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	organization_id INTEGER NOT NULL DEFAULT 1,
	name TEXT NOT NULL,
	code TEXT,
	description TEXT,
	active INTEGER NOT NULL DEFAULT 1,
	created_at DATETIME DEFAULT (datetime('now', 'localtime')),
	FOREIGN KEY(organization_id) REFERENCES b2b_organizations(id),
	UNIQUE(organization_id, name)
);

CREATE TABLE IF NOT EXISTS b2b_categories (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	organization_id INTEGER NOT NULL DEFAULT 1,
	name TEXT NOT NULL,
	code TEXT,
	description TEXT,
	active INTEGER NOT NULL DEFAULT 1,
	created_at DATETIME DEFAULT (datetime('now', 'localtime')),
	FOREIGN KEY(organization_id) REFERENCES b2b_organizations(id),
	UNIQUE(organization_id, name)
);
");

// Poblar marcas desde b2b_products
$brands = $db->query("SELECT DISTINCT brand FROM b2b_products WHERE brand IS NOT NULL AND brand != ''")->fetchAll(\PDO::FETCH_COLUMN);
foreach ($brands as $bName) {
	$stmt = $db->prepare("INSERT OR IGNORE INTO b2b_brands (organization_id, name, code, active) VALUES (1, ?, UPPER(REPLACE(?, ' ', '_')), 1)");
	$stmt->execute([$bName, $bName]);
}

// Poblar categorías desde b2b_products
$cats = $db->query("SELECT DISTINCT category FROM b2b_products WHERE category IS NOT NULL AND category != ''")->fetchAll(\PDO::FETCH_COLUMN);
foreach ($cats as $cName) {
	$stmt = $db->prepare("INSERT OR IGNORE INTO b2b_categories (organization_id, name, code, active) VALUES (1, ?, UPPER(REPLACE(?, ' ', '_')), 1)");
	$stmt->execute([$cName, $cName]);
}

// 4. CONTACTOS Y DIRECCIONES COMERCIALES
$db->exec("
CREATE TABLE IF NOT EXISTS b2b_customer_contacts (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	organization_id INTEGER NOT NULL DEFAULT 1,
	customer_id INTEGER NOT NULL,
	establishment_id INTEGER NULL,
	first_name TEXT NOT NULL,
	last_name TEXT,
	job_title TEXT,
	email TEXT,
	phone TEXT,
	is_primary INTEGER NOT NULL DEFAULT 0,
	active INTEGER NOT NULL DEFAULT 1,
	created_at DATETIME DEFAULT (datetime('now', 'localtime')),
	FOREIGN KEY(organization_id) REFERENCES b2b_organizations(id),
	FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE,
	FOREIGN KEY(establishment_id) REFERENCES customer_establishments(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS b2b_customer_addresses (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	organization_id INTEGER NOT NULL DEFAULT 1,
	customer_id INTEGER NOT NULL,
	establishment_id INTEGER NULL,
	address_type TEXT NOT NULL DEFAULT 'delivery', -- delivery, billing, warehouse
	address_line1 TEXT NOT NULL,
	address_line2 TEXT,
	city TEXT NOT NULL,
	state TEXT NOT NULL,
	postal_code TEXT,
	is_default INTEGER NOT NULL DEFAULT 0,
	active INTEGER NOT NULL DEFAULT 1,
	created_at DATETIME DEFAULT (datetime('now', 'localtime')),
	FOREIGN KEY(organization_id) REFERENCES b2b_organizations(id),
	FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE,
	FOREIGN KEY(establishment_id) REFERENCES customer_establishments(id) ON DELETE SET NULL
);
");

// Poblar contactos iniciales desde customer_establishments si está vacía
$contactCount = $db->query("SELECT COUNT(*) FROM b2b_customer_contacts")->fetchColumn();
if ($contactCount == 0) {
	$estabs = $db->query("SELECT id, customer_id, purchasing_contact_name, purchasing_contact_phone, purchasing_contact_email FROM customer_establishments WHERE purchasing_contact_name IS NOT NULL AND purchasing_contact_name != ''")->fetchAll(\PDO::FETCH_ASSOC);
	$insStmt = $db->prepare("INSERT INTO b2b_customer_contacts (organization_id, customer_id, establishment_id, first_name, phone, email, is_primary, active) VALUES (1, ?, ?, ?, ?, ?, 1, 1)");
	foreach ($estabs as $e) {
		$insStmt->execute([$e['customer_id'], $e['id'], $e['purchasing_contact_name'], $e['purchasing_contact_phone'], $e['purchasing_contact_email']]);
	}
}

// Poblar direcciones iniciales desde customer_establishments si está vacía
$addrCount = $db->query("SELECT COUNT(*) FROM b2b_customer_addresses")->fetchColumn();
if ($addrCount == 0) {
	$estabs = $db->query("SELECT id, customer_id, address, city, state FROM customer_establishments WHERE address IS NOT NULL AND address != ''")->fetchAll(\PDO::FETCH_ASSOC);
	$insStmt = $db->prepare("INSERT INTO b2b_customer_addresses (organization_id, customer_id, establishment_id, address_type, address_line1, city, state, is_default, active) VALUES (1, ?, ?, 'delivery', ?, COALESCE(?, 'Bogotá'), COALESCE(?, 'Cundinamarca'), 1, 1)");
	foreach ($estabs as $e) {
		$insStmt->execute([$e['customer_id'], $e['id'], $e['address'], $e['city'], $e['state']]);
	}
}

// 5. OPORTUNIDADES COMERCIALES Y SEGUIMIENTO
$db->exec("
CREATE TABLE IF NOT EXISTS b2b_commercial_opportunities (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	organization_id INTEGER NOT NULL DEFAULT 1,
	customer_id INTEGER NOT NULL,
	product_id INTEGER NOT NULL,
	opportunity_type TEXT NOT NULL, -- churn_recovery, cross_selling, upsell, reactivation
	title TEXT NOT NULL,
	description TEXT,
	potential_value REAL NOT NULL DEFAULT 0.00,
	confidence_score REAL NOT NULL DEFAULT 0.00,
	status TEXT NOT NULL DEFAULT 'IDENTIFICADA', -- IDENTIFICADA, CONTACTADA, COTIZADA, CONVERTIDA, DESCARTADA
	action_plan TEXT,
	sales_rep_id INTEGER NULL,
	notes TEXT,
	created_at DATETIME DEFAULT (datetime('now', 'localtime')),
	updated_at DATETIME DEFAULT (datetime('now', 'localtime')),
	FOREIGN KEY(organization_id) REFERENCES b2b_organizations(id),
	FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE,
	FOREIGN KEY(product_id) REFERENCES b2b_products(id) ON DELETE CASCADE,
	FOREIGN KEY(sales_rep_id) REFERENCES b2b_sales_reps(id) ON DELETE SET NULL
);
");

// 6. METAS Y CUOTAS COMERCIALES HISTÓRICAS
$db->exec("
CREATE TABLE IF NOT EXISTS b2b_sales_quotas (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	organization_id INTEGER NOT NULL DEFAULT 1,
	sales_rep_id INTEGER NOT NULL,
	period_year INTEGER NOT NULL,
	period_month INTEGER NOT NULL,
	target_amount REAL NOT NULL,
	achieved_amount REAL NOT NULL DEFAULT 0.00,
	commission_rate REAL NOT NULL DEFAULT 2.50,
	active INTEGER NOT NULL DEFAULT 1,
	created_at DATETIME DEFAULT (datetime('now', 'localtime')),
	FOREIGN KEY(organization_id) REFERENCES b2b_organizations(id),
	FOREIGN KEY(sales_rep_id) REFERENCES b2b_sales_reps(id) ON DELETE CASCADE,
	UNIQUE(sales_rep_id, period_year, period_month)
);
");

// Poblar cuotas para vendedores existentes
$reps = $db->query("SELECT id, monthly_quota FROM b2b_sales_reps")->fetchAll(\PDO::FETCH_ASSOC);
$insQuota = $db->prepare("INSERT OR IGNORE INTO b2b_sales_quotas (organization_id, sales_rep_id, period_year, period_month, target_amount, achieved_amount, commission_rate) VALUES (1, ?, 2026, 11, ?, 0.00, 2.50)");
foreach ($reps as $r) {
	$insQuota->execute([$r['id'], $r['monthly_quota']]);
}

// 7. AUDITORÍA INMUTABLE DE EVENTOS COMERCIALES Y FINANCIEROS (b2b_audit_log)
$db->exec("
CREATE TABLE IF NOT EXISTS b2b_audit_log (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	organization_id INTEGER NOT NULL DEFAULT 1,
	user_id INTEGER NULL,
	user_name TEXT NOT NULL DEFAULT 'Carlos Mendoza',
	entity_type TEXT NOT NULL, -- CUSTOMER, PRODUCT, ORDER, INVOICE, PAYMENT, CREDIT_NOTE, COST_ADJUSTMENT, SETTING
	entity_id INTEGER NOT NULL,
	action TEXT NOT NULL, -- CREATE, UPDATE, DELETE, STATUS_CHANGE, ADJUST_COST, REGISTER_PAYMENT, ISSUE_CREDIT_NOTE
	old_values TEXT NULL, -- JSON
	new_values TEXT NULL, -- JSON
	ip_address TEXT DEFAULT '127.0.0.1',
	created_at DATETIME DEFAULT (datetime('now', 'localtime')),
	FOREIGN KEY(organization_id) REFERENCES b2b_organizations(id)
);
");

// 8. CONFIGURACIÓN COMERCIAL Y FISCAL B2B
$db->exec("
CREATE TABLE IF NOT EXISTS b2b_settings (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	organization_id INTEGER NOT NULL DEFAULT 1,
	setting_key TEXT NOT NULL,
	setting_value TEXT NOT NULL,
	setting_group TEXT NOT NULL DEFAULT 'general', -- general, credit, moq, tax, security
	description TEXT NULL,
	updated_at DATETIME DEFAULT (datetime('now', 'localtime')),
	FOREIGN KEY(organization_id) REFERENCES b2b_organizations(id),
	UNIQUE(organization_id, setting_key)
);
");

// Sembrar configuraciones estándar si está vacía
$settingsCount = $db->query("SELECT COUNT(*) FROM b2b_settings WHERE organization_id = 1")->fetchColumn();
if ($settingsCount == 0) {
	$defaultSettings = [
		['credit_policy_days', '30', 'credit', 'Días estándar de crédito otorgados a clientes mayoristas'],
		['credit_grace_days', '5', 'credit', 'Días de tolerancia antes de marcar factura en mora'],
		['default_tax_rate', '18.00', 'tax', 'Tasa porcentual estándar de IVA comercial'],
		['min_order_amount', '200.00', 'moq', 'Monto mínimo por pedido mayorista para despacho gratuito'],
		['min_order_boxes', '5', 'moq', 'Cantidad mínima de cajas por orden de compra mayorista'],
		['allow_split_cases', '1', 'general', 'Permitir apertura de cajas para despacho de unidades sueltas'],
		['auto_block_morosos', '1', 'credit', 'Bloquear emisión de nuevos pedidos a clientes con facturas vencidas >15 días'],
		['invoice_prefix', 'FAC-2026', 'general', 'Prefijo de numeración correlativa de facturas'],
		['order_prefix', 'PED-2026', 'general', 'Prefijo correlativo de pedidos comerciales'],
		['quote_prefix', 'COT-2026', 'general', 'Prefijo correlativo de cotizaciones formales'],
		['credit_note_prefix', 'NC-2026', 'general', 'Prefijo correlativo de notas de crédito comerciales']
	];
	$insSet = $db->prepare("INSERT INTO b2b_settings (organization_id, setting_key, setting_value, setting_group, description) VALUES (1, ?, ?, ?, ?)");
	foreach ($defaultSettings as $s) {
		$insSet->execute($s);
	}
}

// 9. ÍNDICES DE ALTO RENDIMIENTO PARA CONSULTAS Y DASHBOARDS
$indices = [
	"CREATE INDEX IF NOT EXISTS idx_b2b_org_code ON b2b_organizations(code)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_org_tax ON b2b_organizations(tax_id)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_brands_org ON b2b_brands(organization_id, active)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_cats_org ON b2b_categories(organization_id, active)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_contacts_cust ON b2b_customer_contacts(customer_id, establishment_id)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_addr_cust ON b2b_customer_addresses(customer_id, establishment_id)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_opp_cust ON b2b_commercial_opportunities(customer_id, status)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_opp_prod ON b2b_commercial_opportunities(product_id)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_quotas_rep ON b2b_sales_quotas(sales_rep_id, period_year, period_month)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_audit_ent ON b2b_audit_log(entity_type, entity_id)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_audit_action ON b2b_audit_log(action, created_at)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_settings_key ON b2b_settings(organization_id, setting_key)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_inv_org_date ON b2b_invoices(organization_id, issue_date, status)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_ord_org_date ON b2b_orders(organization_id, created_timestamp, status)",
	"CREATE INDEX IF NOT EXISTS idx_b2b_prod_org_sku ON b2b_products(organization_id, sku, active)"
];

foreach ($indices as $idxSql) {
	try {
		$db->exec($idxSql);
	} catch (\Throwable $e) {}
}

