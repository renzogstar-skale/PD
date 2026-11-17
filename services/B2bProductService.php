<?php

namespace Grocy\Services;

use Exception;
use PDO;

/**
 * Servicio de Dominio B2B para Catálogo Comercial y Motor de Precios Mayoristas
 */
class B2bProductService extends BaseService
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
	 * Crea las tablas necesarias para catálogo y reglas comerciales B2B
	 */
	public function EnsureSchema(): void
	{
		$db = $this->Db();

		$db->exec("
			CREATE TABLE IF NOT EXISTS b2b_products (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				sku TEXT NOT NULL UNIQUE,
				name TEXT NOT NULL,
				description TEXT,
				barcode TEXT,
				brand TEXT NOT NULL,
				category TEXT NOT NULL,
				unit_name TEXT NOT NULL DEFAULT 'Unidad',
				case_name TEXT NOT NULL DEFAULT 'Caja',
				conversion_factor INTEGER NOT NULL DEFAULT 12,
				base_price_unit REAL NOT NULL DEFAULT 0.00,
				base_price_case REAL NOT NULL DEFAULT 0.00,
				currency TEXT NOT NULL DEFAULT 'USD',
				active INTEGER NOT NULL DEFAULT 1,
				is_demo INTEGER NOT NULL DEFAULT 0,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				updated_timestamp DATETIME DEFAULT (datetime('now', 'localtime'))
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_prod_sku ON b2b_products(sku);
			CREATE INDEX IF NOT EXISTS idx_b2b_prod_cat ON b2b_products(category);
			CREATE INDEX IF NOT EXISTS idx_b2b_prod_brand ON b2b_products(brand);

			CREATE TABLE IF NOT EXISTS b2b_price_tiers (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				product_id INTEGER NOT NULL,
				min_quantity INTEGER NOT NULL,
				price_per_case REAL NOT NULL,
				discount_percent REAL NULL,
				active INTEGER NOT NULL DEFAULT 1,
				FOREIGN KEY(product_id) REFERENCES b2b_products(id) ON DELETE CASCADE
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_tier_prod ON b2b_price_tiers(product_id);

			CREATE TABLE IF NOT EXISTS b2b_customer_prices (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				product_id INTEGER NOT NULL,
				customer_id INTEGER NOT NULL,
				price_per_case REAL NOT NULL,
				price_per_unit REAL NULL,
				valid_from DATE NULL,
				valid_to DATE NULL,
				active INTEGER NOT NULL DEFAULT 1,
				notes TEXT,
				FOREIGN KEY(product_id) REFERENCES b2b_products(id) ON DELETE CASCADE
			);
			CREATE INDEX IF NOT EXISTS idx_b2b_cp_prod_cust ON b2b_customer_prices(product_id, customer_id);

			CREATE TABLE IF NOT EXISTS b2b_price_history (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				product_id INTEGER NOT NULL,
				field_changed TEXT NOT NULL,
				old_price REAL NOT NULL,
				new_price REAL NOT NULL,
				changed_by TEXT NOT NULL DEFAULT 'Sistema',
				reason TEXT,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				FOREIGN KEY(product_id) REFERENCES b2b_products(id) ON DELETE CASCADE
			);
		");
	}

	/**
	 * Siembra 20 productos DEMO con marcas, conversiones, escalas y precios por cliente
	 */
	public function SeedDemoDataIfEmpty(): void
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$count = (int)$db->query("SELECT COUNT(*) FROM b2b_products WHERE is_demo = 1")->fetchColumn();
		if ($count > 0)
		{
			return; // Ya sembrado
		}

		// Obtener ID del primer cliente (Supermercados Santa Isabel) si existe
		$santaIsabelId = (int)$db->query("SELECT id FROM customers WHERE name LIKE '%Santa Isabel%' LIMIT 1")->fetchColumn();
		$mayoristaCentroId = (int)$db->query("SELECT id FROM customers WHERE name LIKE '%Mayorista del Centro%' LIMIT 1")->fetchColumn();

		$demoProducts = [
			// LÁCTEOS
			[
				'sku' => 'SKU-LAC-001',
				'name' => 'Leche Entera UHT 1 L',
				'description' => 'Leche entera ultrapasteurizada de larga duración enriquecida con vitaminas A y D.',
				'barcode' => '7750123450012',
				'brand' => 'Gloria',
				'category' => 'Lácteos',
				'unit_name' => 'Unidad (1 L)',
				'case_name' => 'Caja x 12 uds',
				'conversion_factor' => 12,
				'base_price_unit' => 2.50,
				'base_price_case' => 27.00,
				'tiers' => [
					['min' => 10, 'price' => 25.50, 'pct' => 5.5]
				],
				'customer_agreements' => [
					['customer_id' => $santaIsabelId, 'price_case' => 24.90, 'price_unit' => 2.20, 'notes' => 'Acuerdo Comercial Cadena Santa Isabel']
				]
			],
			[
				'sku' => 'SKU-LAC-002',
				'name' => 'Leche Evaporada Azul 400 g',
				'description' => 'Leche evaporada tradicional en lata clásica.',
				'barcode' => '7750123450029',
				'brand' => 'Gloria',
				'category' => 'Lácteos',
				'unit_name' => 'Lata (400 g)',
				'case_name' => 'Caja x 24 latas',
				'conversion_factor' => 24,
				'base_price_unit' => 1.80,
				'base_price_case' => 40.00,
				'tiers' => [
					['min' => 10, 'price' => 38.00, 'pct' => 5.0]
				],
				'customer_agreements' => [
					['customer_id' => $mayoristaCentroId, 'price_case' => 37.50, 'price_unit' => 1.65, 'notes' => 'Tarifa Mayorista Centro']
				]
			],
			[
				'sku' => 'SKU-LAC-003',
				'name' => 'Yogurt Fresa 1 kg',
				'description' => 'Yogurt batido sabor fresa con probióticos.',
				'barcode' => '7750123450036',
				'brand' => 'Laive',
				'category' => 'Lácteos',
				'unit_name' => 'Botella (1 kg)',
				'case_name' => 'Caja x 12 uds',
				'conversion_factor' => 12,
				'base_price_unit' => 2.90,
				'base_price_case' => 32.00,
				'tiers' => [
					['min' => 8, 'price' => 30.00, 'pct' => 6.25]
				]
			],
			[
				'sku' => 'SKU-LAC-004',
				'name' => 'Queso Edam Barra 2 kg',
				'description' => 'Queso madurado semiduro tipo Edam para fiambrería.',
				'barcode' => '7750123450043',
				'brand' => 'Laive',
				'category' => 'Lácteos',
				'unit_name' => 'Barra (2 kg)',
				'case_name' => 'Caja x 4 barras',
				'conversion_factor' => 4,
				'base_price_unit' => 22.00,
				'base_price_case' => 82.00,
				'tiers' => [
					['min' => 5, 'price' => 78.00, 'pct' => 4.8]
				]
			],

			// REFRIGERADOS
			[
				'sku' => 'SKU-REF-001',
				'name' => 'Mantequilla con Sal 200 g',
				'description' => 'Mantequilla natural elaborada con crema de leche fresca pasteurizada.',
				'barcode' => '7750123450050',
				'brand' => 'Laive',
				'category' => 'Refrigerados',
				'unit_name' => 'Pastilla (200 g)',
				'case_name' => 'Caja x 24 uds',
				'conversion_factor' => 24,
				'base_price_unit' => 3.10,
				'base_price_case' => 70.00,
				'tiers' => [
					['min' => 6, 'price' => 66.00, 'pct' => 5.7]
				]
			],
			[
				'sku' => 'SKU-REF-002',
				'name' => 'Jamonada Especial 1 kg',
				'description' => 'Embutido cocido porcionado para corte en charcutería.',
				'barcode' => '7750123450067',
				'brand' => 'San Fernando',
				'category' => 'Refrigerados',
				'unit_name' => 'Pieza (1 kg)',
				'case_name' => 'Caja x 10 piezas',
				'conversion_factor' => 10,
				'base_price_unit' => 7.50,
				'base_price_case' => 70.00,
				'tiers' => [
					['min' => 5, 'price' => 66.50, 'pct' => 5.0]
				]
			],
			[
				'sku' => 'SKU-REF-003',
				'name' => 'Salchicha de Pollo 500 g',
				'description' => 'Paquete al vacío con salchichas tipo viena de pollo.',
				'barcode' => '7750123450074',
				'brand' => 'San Fernando',
				'category' => 'Refrigerados',
				'unit_name' => 'Paquete (500 g)',
				'case_name' => 'Caja x 20 paqs',
				'conversion_factor' => 20,
				'base_price_unit' => 3.80,
				'base_price_case' => 72.00,
				'tiers' => [
					['min' => 10, 'price' => 68.00, 'pct' => 5.5]
				]
			],

			// CONGELADOS
			[
				'sku' => 'SKU-CON-001',
				'name' => 'Hamburguesa de Res Clásica x 12',
				'description' => 'Medallones de carne de res seleccionada ultracongelados.',
				'barcode' => '7750123450081',
				'brand' => 'San Fernando',
				'category' => 'Congelados',
				'unit_name' => 'Caja display (12 uds)',
				'case_name' => 'Master x 8 displays',
				'conversion_factor' => 8,
				'base_price_unit' => 11.00,
				'base_price_case' => 82.00,
				'tiers' => [
					['min' => 6, 'price' => 77.00, 'pct' => 6.1]
				]
			],
			[
				'sku' => 'SKU-CON-002',
				'name' => 'Nuggets de Pollo Crocantes 1 kg',
				'description' => 'Bocaditos de pollo empanizados precocidos congelados.',
				'barcode' => '7750123450098',
				'brand' => 'Redondos',
				'category' => 'Congelados',
				'unit_name' => 'Bolsa (1 kg)',
				'case_name' => 'Caja x 6 bolsas',
				'conversion_factor' => 6,
				'base_price_unit' => 9.50,
				'base_price_case' => 54.00,
				'tiers' => [
					['min' => 10, 'price' => 50.00, 'pct' => 7.4]
				]
			],
			[
				'sku' => 'SKU-CON-003',
				'name' => 'Papas Prefritas Corte Recto 2.5 kg',
				'description' => 'Papas procesadas grado A ideales para freidoras industriales.',
				'barcode' => '7750123450104',
				'brand' => 'Lamb Weston',
				'category' => 'Congelados',
				'unit_name' => 'Bolsa (2.5 kg)',
				'case_name' => 'Caja x 4 bolsas',
				'conversion_factor' => 4,
				'base_price_unit' => 12.50,
				'base_price_case' => 47.00,
				'tiers' => [
					['min' => 10, 'price' => 44.00, 'pct' => 6.3]
				]
			],
			[
				'sku' => 'SKU-CON-004',
				'name' => 'Filete de Tilapia Congelada 1 kg',
				'description' => 'Filetes desespinados IQF listos para cocción.',
				'barcode' => '7750123450111',
				'brand' => 'Mar Azul',
				'category' => 'Congelados',
				'unit_name' => 'Bolsa (1 kg)',
				'case_name' => 'Caja x 10 bolsas',
				'conversion_factor' => 10,
				'base_price_unit' => 8.90,
				'base_price_case' => 84.00,
				'tiers' => [
					['min' => 5, 'price' => 79.00, 'pct' => 5.9]
				]
			],

			// CARNES Y AVES
			[
				'sku' => 'SKU-CAR-001',
				'name' => 'Pollo Entero Beneficiado con Menudencia',
				'description' => 'Aves frescas evisceradas calibradas grado comercial.',
				'barcode' => '7750123450128',
				'brand' => 'San Fernando',
				'category' => 'Carnes y aves',
				'unit_name' => 'Unidad (~2.2 kg)',
				'case_name' => 'Jabera x 8 aves',
				'conversion_factor' => 8,
				'base_price_unit' => 8.50,
				'base_price_case' => 65.00,
				'tiers' => [
					['min' => 10, 'price' => 61.00, 'pct' => 6.1]
				]
			],
			[
				'sku' => 'SKU-CAR-002',
				'name' => 'Pechuga Especial con Hueso 2 kg',
				'description' => 'Cortes magros de pechuga congelada para despensa mayorista.',
				'barcode' => '7750123450135',
				'brand' => 'Redondos',
				'category' => 'Carnes y aves',
				'unit_name' => 'Bolsa (2 kg)',
				'case_name' => 'Caja x 6 bolsas',
				'conversion_factor' => 6,
				'base_price_unit' => 16.00,
				'base_price_case' => 92.00,
				'tiers' => [
					['min' => 5, 'price' => 87.00, 'pct' => 5.4]
				]
			],
			[
				'sku' => 'SKU-CAR-003',
				'name' => 'Lomo Fino de Res Porcionado 1 kg',
				'description' => 'Corte premium tierno envasado al vacío.',
				'barcode' => '7750123450142',
				'brand' => 'Carnes del Sur',
				'category' => 'Carnes y aves',
				'unit_name' => 'Corte (1 kg)',
				'case_name' => 'Caja x 10 cortes',
				'conversion_factor' => 10,
				'base_price_unit' => 24.00,
				'base_price_case' => 230.00,
				'tiers' => [
					['min' => 4, 'price' => 215.00, 'pct' => 6.5]
				]
			],

			// BEBIDAS
			[
				'sku' => 'SKU-BEB-001',
				'name' => 'Agua Mineral sin Gas 2.5 L',
				'description' => 'Agua de manantial purificada embotellada sin gas.',
				'barcode' => '7750123450159',
				'brand' => 'San Luis',
				'category' => 'Bebidas',
				'unit_name' => 'Botella (2.5 L)',
				'case_name' => 'Paquete x 6 uds',
				'conversion_factor' => 6,
				'base_price_unit' => 1.40,
				'base_price_case' => 7.80,
				'tiers' => [
					['min' => 15, 'price' => 7.20, 'pct' => 7.6]
				]
			],
			[
				'sku' => 'SKU-BEB-002',
				'name' => 'Bebida Gaseosa Sabor Original 3 L',
				'description' => 'Gaseosa clásica dorada en formato familiar.',
				'barcode' => '7750123450166',
				'brand' => 'Inca Kola',
				'category' => 'Bebidas',
				'unit_name' => 'Botella (3 L)',
				'case_name' => 'Paquete x 4 uds',
				'conversion_factor' => 4,
				'base_price_unit' => 3.80,
				'base_price_case' => 14.50,
				'tiers' => [
					['min' => 20, 'price' => 13.80, 'pct' => 4.8]
				],
				'customer_agreements' => [
					['customer_id' => $santaIsabelId, 'price_case' => 13.50, 'price_unit' => 3.50, 'notes' => 'Acuerdo Comercial Bebidas']
				]
			],
			[
				'sku' => 'SKU-BEB-003',
				'name' => 'Bebida Gaseosa Cola 3 L',
				'description' => 'Gaseosa negra carbonatada líder de mercado.',
				'barcode' => '7750123450173',
				'brand' => 'Coca-Cola',
				'category' => 'Bebidas',
				'unit_name' => 'Botella (3 L)',
				'case_name' => 'Paquete x 4 uds',
				'conversion_factor' => 4,
				'base_price_unit' => 3.90,
				'base_price_case' => 14.90,
				'tiers' => [
					['min' => 20, 'price' => 14.10, 'pct' => 5.3]
				]
			],
			[
				'sku' => 'SKU-BEB-004',
				'name' => 'Jugo de Néctar Naranja 1 L',
				'description' => 'Bebida de jugo cítrico pasteurizado tetra pak.',
				'barcode' => '7750123450180',
				'brand' => 'Frugos',
				'category' => 'Bebidas',
				'unit_name' => 'Tetra Pak (1 L)',
				'case_name' => 'Caja x 12 uds',
				'conversion_factor' => 12,
				'base_price_unit' => 1.90,
				'base_price_case' => 21.50,
				'tiers' => [
					['min' => 10, 'price' => 20.00, 'pct' => 7.0]
				]
			],

			// DESECHABLES
			[
				'sku' => 'SKU-DES-001',
				'name' => 'Contenedor Térmico CT4 con Tapa x 100',
				'description' => 'Envases térmicos de espuma plast para delivery y rotisería.',
				'barcode' => '7750123450197',
				'brand' => 'Pamolsa',
				'category' => 'Desechables',
				'unit_name' => 'Fardo (100 uds)',
				'case_name' => 'Caja x 5 fardos (500 uds)',
				'conversion_factor' => 5,
				'base_price_unit' => 8.00,
				'base_price_case' => 38.00,
				'tiers' => [
					['min' => 10, 'price' => 35.00, 'pct' => 7.8]
				]
			],
			[
				'sku' => 'SKU-DES-002',
				'name' => 'Vaso Plástico Transparente 12 oz x 100',
				'description' => 'Vasos descartables de polipropileno reforzado para bebidas frías.',
				'barcode' => '7750123450203',
				'brand' => 'Pamolsa',
				'category' => 'Desechables',
				'unit_name' => 'Manga (100 uds)',
				'case_name' => 'Caja x 10 mangas (1000 uds)',
				'conversion_factor' => 10,
				'base_price_unit' => 4.50,
				'base_price_case' => 42.00,
				'tiers' => [
					['min' => 15, 'price' => 39.00, 'pct' => 7.1]
				]
			]
		];

		$db->beginTransaction();
		try
		{
			$stmtProd = $db->prepare("
				INSERT INTO b2b_products (
					sku, name, description, barcode, brand, category,
					unit_name, case_name, conversion_factor,
					base_price_unit, base_price_case, currency, active, is_demo
				) VALUES (
					:sku, :name, :description, :barcode, :brand, :category,
					:unit_name, :case_name, :conversion_factor,
					:base_price_unit, :base_price_case, 'USD', 1, 1
				)
			");

			$stmtTier = $db->prepare("
				INSERT INTO b2b_price_tiers (product_id, min_quantity, price_per_case, discount_percent, active)
				VALUES (:product_id, :min_quantity, :price_per_case, :discount_percent, 1)
			");

			$stmtAgr = $db->prepare("
				INSERT INTO b2b_customer_prices (product_id, customer_id, price_per_case, price_per_unit, notes, active)
				VALUES (:product_id, :customer_id, :price_per_case, :price_per_unit, :notes, 1)
			");

			foreach ($demoProducts as $p)
			{
				$stmtProd->execute([
					':sku' => $p['sku'],
					':name' => $p['name'],
					':description' => $p['description'],
					':barcode' => $p['barcode'],
					':brand' => $p['brand'],
					':category' => $p['category'],
					':unit_name' => $p['unit_name'],
					':case_name' => $p['case_name'],
					':conversion_factor' => $p['conversion_factor'],
					':base_price_unit' => $p['base_price_unit'],
					':base_price_case' => $p['base_price_case']
				]);

				$prodId = (int)$db->lastInsertId();

				if (!empty($p['tiers']))
				{
					foreach ($p['tiers'] as $t)
					{
						$stmtTier->execute([
							':product_id' => $prodId,
							':min_quantity' => $t['min'],
							':price_per_case' => $t['price'],
							':discount_percent' => $t['pct'] ?? null
						]);
					}
				}

				if (!empty($p['customer_agreements']))
				{
					foreach ($p['customer_agreements'] as $ca)
					{
						if (!empty($ca['customer_id']))
						{
							$stmtAgr->execute([
								':product_id' => $prodId,
								':customer_id' => $ca['customer_id'],
								':price_per_case' => $ca['price_case'],
								':price_per_unit' => $ca['price_unit'] ?? null,
								':notes' => $ca['notes'] ?? 'Acuerdo Comercial'
							]);
						}
					}
				}
			}

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollBack();
			throw $e;
		}
	}

	/**
	 * Obtiene métricas ejecutivas de catálogo y tarifas
	 */
	public function GetMetrics(): array
	{
		$this->EnsureSchema();
		$this->SeedDemoDataIfEmpty();
		$db = $this->Db();

		$total = (int)$db->query("SELECT COUNT(*) FROM b2b_products")->fetchColumn();
		$active = (int)$db->query("SELECT COUNT(*) FROM b2b_products WHERE active = 1")->fetchColumn();
		$categories = (int)$db->query("SELECT COUNT(DISTINCT category) FROM b2b_products WHERE active = 1")->fetchColumn();
		$brands = (int)$db->query("SELECT COUNT(DISTINCT brand) FROM b2b_products WHERE active = 1")->fetchColumn();
		$agreements = (int)$db->query("SELECT COUNT(*) FROM b2b_customer_prices WHERE active = 1")->fetchColumn();

		return [
			'total_products' => $total,
			'active_products' => $active,
			'total_categories' => $categories,
			'total_brands' => $brands,
			'active_agreements' => $agreements
		];
	}

	/**
	 * Obtiene lista de productos con filtros
	 */
	public function GetProducts(array $filters = []): array
	{
		$this->EnsureSchema();
		$this->SeedDemoDataIfEmpty();
		$db = $this->Db();

		$where = ['1=1'];
		$params = [];

		if (!empty($filters['search']))
		{
			$search = '%' . trim($filters['search']) . '%';
			$where[] = "(p.sku LIKE :s1 OR p.name LIKE :s2 OR p.barcode LIKE :s3)";
			$params[':s1'] = $search;
			$params[':s2'] = $search;
			$params[':s3'] = $search;
		}

		if (!empty($filters['category']))
		{
			$where[] = "LOWER(p.category) = :cat";
			$params[':cat'] = strtolower(trim($filters['category']));
		}

		if (!empty($filters['brand']))
		{
			$where[] = "LOWER(p.brand) = :brand";
			$params[':brand'] = strtolower(trim($filters['brand']));
		}

		if (isset($filters['active']) && $filters['active'] !== '')
		{
			$where[] = "p.active = :active";
			$params[':active'] = (int)$filters['active'];
		}

		$whereSql = implode(' AND ', $where);

		$sql = "
			SELECT 
				p.id, p.sku, p.name, p.description, p.barcode, p.brand, p.category,
				p.unit_name, p.case_name, p.conversion_factor,
				p.base_price_unit, p.base_price_case, p.currency, p.active, p.is_demo,
				(SELECT COUNT(*) FROM b2b_price_tiers t WHERE t.product_id = p.id AND t.active = 1) AS tiers_count,
				(SELECT COUNT(*) FROM b2b_customer_prices cp WHERE cp.product_id = p.id AND cp.active = 1) AS agreements_count
			FROM b2b_products p
			WHERE {$whereSql}
			ORDER BY p.active DESC, p.category ASC, p.name ASC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Obtiene detalle de un producto con sus escalas y acuerdos comerciales
	 */
	public function GetProduct(int $id): ?array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->prepare("SELECT * FROM b2b_products WHERE id = :id");
		$stmt->execute([':id' => $id]);
		$product = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$product)
		{
			return null;
		}

		// Escalas de volumen
		$stmtTiers = $db->prepare("
			SELECT id, min_quantity, price_per_case, discount_percent, active
			FROM b2b_price_tiers
			WHERE product_id = :id AND active = 1
			ORDER BY min_quantity ASC
		");
		$stmtTiers->execute([':id' => $id]);
		$product['tiers'] = $stmtTiers->fetchAll(PDO::FETCH_ASSOC);

		// Acuerdos por cliente
		$stmtCust = $db->prepare("
			SELECT 
				cp.id, cp.customer_id, cp.price_per_case, cp.price_per_unit, cp.valid_from, cp.valid_to, cp.notes,
				c.name AS customer_name, c.tax_id AS customer_tax_id
			FROM b2b_customer_prices cp
			JOIN customers c ON c.id = cp.customer_id
			WHERE cp.product_id = :id AND cp.active = 1
			ORDER BY c.name ASC
		");
		$stmtCust->execute([':id' => $id]);
		$product['customer_agreements'] = $stmtCust->fetchAll(PDO::FETCH_ASSOC);

		// Historial de precios
		$stmtHist = $db->prepare("
			SELECT field_changed, old_price, new_price, changed_by, reason, created_timestamp
			FROM b2b_price_history
			WHERE product_id = :id
			ORDER BY id DESC LIMIT 10
		");
		$stmtHist->execute([':id' => $id]);
		$product['price_history'] = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

		return $product;
	}

	/**
	 * Crea un nuevo producto
	 */
	public function CreateProduct(array $data): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$sku = strtoupper(trim($data['sku'] ?? ''));
		$name = trim($data['name'] ?? '');
		$description = trim($data['description'] ?? '');
		$barcode = trim($data['barcode'] ?? '');
		$brand = trim($data['brand'] ?? '');
		$category = trim($data['category'] ?? '');
		$unitName = trim($data['unit_name'] ?? 'Unidad');
		$caseName = trim($data['case_name'] ?? 'Caja');
		$convFactor = max(1, (int)($data['conversion_factor'] ?? 12));
		$priceUnit = round((float)($data['base_price_unit'] ?? 0), 2);
		$priceCase = round((float)($data['base_price_case'] ?? 0), 2);

		if (empty($sku))
		{
			throw new Exception('El código SKU es obligatorio.');
		}
		if (empty($name))
		{
			throw new Exception('El nombre del producto es obligatorio.');
		}
		if (empty($brand))
		{
			throw new Exception('La marca es obligatoria.');
		}
		if (empty($category))
		{
			throw new Exception('La categoría es obligatoria.');
		}
		if ($priceCase <= 0 && $priceUnit <= 0)
		{
			throw new Exception('Debe especificar un precio base válido.');
		}

		// Validar unicidad de SKU
		$stmtCheck = $db->prepare("SELECT id FROM b2b_products WHERE sku = :sku");
		$stmtCheck->execute([':sku' => $sku]);
		if ($stmtCheck->fetchColumn())
		{
			throw new Exception("El código SKU '{$sku}' ya existe en el catálogo comercial.");
		}

		$stmt = $db->prepare("
			INSERT INTO b2b_products (
				sku, name, description, barcode, brand, category,
				unit_name, case_name, conversion_factor,
				base_price_unit, base_price_case, currency, active, is_demo
			) VALUES (
				:sku, :name, :description, :barcode, :brand, :category,
				:unit_name, :case_name, :conversion_factor,
				:base_price_unit, :base_price_case, 'USD', 1, 0
			)
		");

		$stmt->execute([
			':sku' => $sku,
			':name' => $name,
			':description' => $description,
			':barcode' => $barcode,
			':brand' => $brand,
			':category' => $category,
			':unit_name' => $unitName,
			':case_name' => $caseName,
			':conversion_factor' => $convFactor,
			':base_price_unit' => $priceUnit,
			':base_price_case' => $priceCase
		]);

		$id = (int)$db->lastInsertId();

		try {
			$stmtAudit = $db->prepare("
				INSERT INTO b2b_audit_log (organization_id, user_name, entity_type, entity_id, action, new_values, ip_address, created_at)
				VALUES (1, 'Carlos Mendoza', 'PRODUCT', :eid, 'CREATE', :val, '127.0.0.1', datetime('now', 'localtime'))
			");
			$stmtAudit->execute([
				':eid' => $id,
				':val' => json_encode(['sku' => $sku, 'name' => $name, 'brand' => $brand, 'category' => $category, 'base_price_case' => $priceCase], JSON_UNESCAPED_UNICODE)
			]);
		} catch (\Throwable $e) {}

		return $this->GetProduct($id);
	}

	/**
	 * Actualiza un producto con auditoría de cambios de precio
	 */
	public function UpdateProduct(int $id, array $data, string $user = 'Admin'): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$existing = $this->GetProduct($id);
		if (!$existing)
		{
			throw new Exception('El producto no existe.');
		}

		$sku = strtoupper(trim($data['sku'] ?? $existing['sku']));
		$name = trim($data['name'] ?? $existing['name']);
		$description = trim($data['description'] ?? $existing['description']);
		$barcode = trim($data['barcode'] ?? $existing['barcode']);
		$brand = trim($data['brand'] ?? $existing['brand']);
		$category = trim($data['category'] ?? $existing['category']);
		$unitName = trim($data['unit_name'] ?? $existing['unit_name']);
		$caseName = trim($data['case_name'] ?? $existing['case_name']);
		$convFactor = max(1, (int)($data['conversion_factor'] ?? $existing['conversion_factor']));
		$priceUnit = round((float)($data['base_price_unit'] ?? $existing['base_price_unit']), 2);
		$priceCase = round((float)($data['base_price_case'] ?? $existing['base_price_case']), 2);

		if (empty($sku)) throw new Exception('El código SKU es obligatorio.');
		if (empty($name)) throw new Exception('El nombre del producto es obligatorio.');

		// Validar SKU duplicado
		$stmtCheck = $db->prepare("SELECT id FROM b2b_products WHERE sku = :sku AND id != :id");
		$stmtCheck->execute([':sku' => $sku, ':id' => $id]);
		if ($stmtCheck->fetchColumn())
		{
			throw new Exception("El código SKU '{$sku}' ya está asignado a otro producto.");
		}

		$db->beginTransaction();
		try
		{
			// Auditoría de cambio de precio
			if ($priceCase != (float)$existing['base_price_case'])
			{
				$stmtH = $db->prepare("
					INSERT INTO b2b_price_history (product_id, field_changed, old_price, new_price, changed_by, reason)
					VALUES (:p, 'base_price_case', :old, :new, :user, 'Actualización comercial')
				");
				$stmtH->execute([
					':p' => $id,
					':old' => (float)$existing['base_price_case'],
					':new' => $priceCase,
					':user' => $user
				]);
			}

			$stmt = $db->prepare("
				UPDATE b2b_products
				SET sku = :sku, name = :name, description = :description, barcode = :barcode,
					brand = :brand, category = :category, unit_name = :unit_name, case_name = :case_name,
					conversion_factor = :conversion_factor, base_price_unit = :base_price_unit,
					base_price_case = :base_price_case, updated_timestamp = datetime('now', 'localtime')
				WHERE id = :id
			");

			$stmt->execute([
				':id' => $id,
				':sku' => $sku,
				':name' => $name,
				':description' => $description,
				':barcode' => $barcode,
				':brand' => $brand,
				':category' => $category,
				':unit_name' => $unitName,
				':case_name' => $caseName,
				':conversion_factor' => $convFactor,
				':base_price_unit' => $priceUnit,
				':base_price_case' => $priceCase
			]);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollBack();
			throw $e;
		}

		return $this->GetProduct($id);
	}

	/**
	 * Alterna el estado activo/inactivo de un producto
	 */
	public function ToggleProductActive(int $id, ?bool $newStatus = null): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$existing = $this->GetProduct($id);
		if (!$existing)
		{
			throw new Exception('El producto no existe.');
		}

		$currentActive = (int)$existing['active'];
		$targetActive = ($newStatus !== null) ? ($newStatus ? 1 : 0) : ($currentActive === 1 ? 0 : 1);

		$stmt = $db->prepare("UPDATE b2b_products SET active = :active WHERE id = :id");
		$stmt->execute([':active' => $targetActive, ':id' => $id]);

		return $this->GetProduct($id);
	}

	/**
	 * MOTOR DETERMINISTA DE PRECIOS B2B
	 * Jerarquía estricta:
	 * 1. Precio pactado específico por cliente (Acuerdo Comercial)
	 * 2. Descuento por escala de volumen (Tier Pricing)
	 * 3. Precio base de lista general
	 */
	public function CalculatePrice(int $productId, ?int $customerId, string $unit = 'CASE', int $quantity = 1): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$product = $this->GetProduct($productId);
		if (!$product)
		{
			throw new Exception('Producto no encontrado.');
		}

		$unit = strtoupper($unit);
		if (!in_array($unit, ['CASE', 'UNIT'], true))
		{
			$unit = 'CASE';
		}

		$quantity = max(1, $quantity);
		$convFactor = max(1, (int)$product['conversion_factor']);
		$basePriceUnit = (float)$product['base_price_unit'];
		$basePriceCase = (float)$product['base_price_case'];

		$baseRate = ($unit === 'CASE') ? $basePriceCase : $basePriceUnit;
		$appliedRate = $baseRate;
		$ruleApplied = 'BASE_PRICE';
		$ruleDescription = 'Precio de lista general mayorista';
		$agreementDetail = null;

		// 1. REGLA 1 (Prioridad Máxima): Acuerdo comercial específico por cliente
		if ($customerId && $customerId > 0)
		{
			$stmtAgr = $db->prepare("
				SELECT price_per_case, price_per_unit, notes
				FROM b2b_customer_prices
				WHERE product_id = :prod AND customer_id = :cust AND active = 1
				AND (valid_from IS NULL OR valid_from <= date('now', 'localtime'))
				AND (valid_to IS NULL OR valid_to >= date('now', 'localtime'))
				LIMIT 1
			");
			$stmtAgr->execute([':prod' => $productId, ':cust' => $customerId]);
			$agr = $stmtAgr->fetch(PDO::FETCH_ASSOC);

			if ($agr)
			{
				if ($unit === 'CASE' && (float)$agr['price_per_case'] > 0)
				{
					$appliedRate = (float)$agr['price_per_case'];
					$ruleApplied = 'CUSTOMER_AGREEMENT';
					$ruleDescription = 'Precio pactado según acuerdo comercial del cliente';
					$agreementDetail = $agr['notes'] ?? 'Tarifa exclusiva pactada';
				}
				elseif ($unit === 'UNIT' && (float)$agr['price_per_unit'] > 0)
				{
					$appliedRate = (float)$agr['price_per_unit'];
					$ruleApplied = 'CUSTOMER_AGREEMENT';
					$ruleDescription = 'Precio pactado unitario por cliente';
					$agreementDetail = $agr['notes'] ?? 'Tarifa exclusiva pactada';
				}
			}
		}

		// 2. REGLA 2: Si NO hubo acuerdo de cliente, evaluar escalas de volumen
		if ($ruleApplied === 'BASE_PRICE' && $unit === 'CASE')
		{
			$stmtTier = $db->prepare("
				SELECT price_per_case, discount_percent, min_quantity
				FROM b2b_price_tiers
				WHERE product_id = :prod AND min_quantity <= :qty AND active = 1
				ORDER BY min_quantity DESC LIMIT 1
			");
			$stmtTier->execute([':prod' => $productId, ':qty' => $quantity]);
			$tier = $stmtTier->fetch(PDO::FETCH_ASSOC);

			if ($tier)
			{
				$appliedRate = (float)$tier['price_per_case'];
				$ruleApplied = 'VOLUME_TIER';
				$ruleDescription = "Escala de volumen para pedidos ≥ {$tier['min_quantity']} cajas";
			}
		}

		$appliedRate = round($appliedRate, 2);
		$subtotal = round($quantity * $appliedRate, 2);
		$baseTotal = round($quantity * $baseRate, 2);
		$savings = max(0.00, round($baseTotal - $subtotal, 2));
		$totalUnitsEquivalent = ($unit === 'CASE') ? ($quantity * $convFactor) : $quantity;
		$unitCostEquivalent = round($subtotal / $totalUnitsEquivalent, 4);

		return [
			'product_id' => $productId,
			'product_name' => $product['name'],
			'sku' => $product['sku'],
			'commercial_unit' => $unit,
			'quantity' => $quantity,
			'conversion_factor' => $convFactor,
			'total_units_equivalent' => $totalUnitsEquivalent,
			'base_rate' => number_format($baseRate, 2, '.', ''),
			'applied_rate' => number_format($appliedRate, 2, '.', ''),
			'subtotal' => number_format($subtotal, 2, '.', ''),
			'savings' => number_format($savings, 2, '.', ''),
			'unit_cost_equivalent' => number_format($unitCostEquivalent, 4, '.', ''),
			'rule_applied' => $ruleApplied,
			'rule_description' => $ruleDescription,
			'agreement_detail' => $agreementDetail
		];
	}

	/**
	 * Añade o actualiza una escala de volumen
	 */
	public function AddVolumeTier(int $productId, int $minQty, float $pricePerCase, ?float $discountPct = null): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->prepare("
			INSERT INTO b2b_price_tiers (product_id, min_quantity, price_per_case, discount_percent, active)
			VALUES (:p, :qty, :pr, :disc, 1)
		");
		$stmt->execute([
			':p' => $productId,
			':qty' => $minQty,
			':pr' => round($pricePerCase, 2),
			':disc' => $discountPct !== null ? round($discountPct, 2) : null
		]);

		return $this->GetProduct($productId);
	}

	/**
	 * Añade o actualiza un acuerdo comercial por cliente
	 */
	public function AddCustomerPrice(int $productId, int $customerId, float $priceCase, ?float $priceUnit = null, ?string $notes = null): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmtDel = $db->prepare("DELETE FROM b2b_customer_prices WHERE product_id = :p AND customer_id = :c");
		$stmtDel->execute([':p' => $productId, ':c' => $customerId]);

		$stmt = $db->prepare("
			INSERT INTO b2b_customer_prices (product_id, customer_id, price_per_case, price_per_unit, notes, active)
			VALUES (:p, :c, :pc, :pu, :n, 1)
		");
		$stmt->execute([
			':p' => $productId,
			':c' => $customerId,
			':pc' => round($priceCase, 2),
			':pu' => $priceUnit !== null ? round($priceUnit, 2) : null,
			':n' => $notes
		]);

		return $this->GetProduct($productId);
	}

	/**
	 * Obtiene opciones disponibles para filtros (marcas y categorías)
	 */
	public function GetFilterOptions(): array
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$categories = $db->query("SELECT DISTINCT category FROM b2b_products ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
		$brands = $db->query("SELECT DISTINCT brand FROM b2b_products ORDER BY brand ASC")->fetchAll(PDO::FETCH_COLUMN);
		return [
			'categories' => $categories,
			'brands' => $brands
		];
	}
}
