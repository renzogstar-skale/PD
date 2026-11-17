<?php

namespace Grocy\Services;

use Exception;
use PDO;

/**
 * Servicio de Dominio B2B para Gestión de Clientes Comerciales y Establecimientos
 */
class B2bCustomerService extends BaseService
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
	 * Asegura la creación de las tablas e índices necesarios para el modelo B2B
	 */
	public function EnsureSchema(): void
	{
		$db = $this->Db();

		// Verificar y agregar columnas adicionales a la tabla 'customers' si no existen
		$customerCols = $db->query("PRAGMA table_info(customers)")->fetchAll(PDO::FETCH_ASSOC);
		$existingCols = array_column($customerCols, 'name');

		if (!in_array('commercial_name', $existingCols, true))
		{
			$db->exec("ALTER TABLE customers ADD COLUMN commercial_name TEXT NULL");
		}
		if (!in_array('is_demo', $existingCols, true))
		{
			$db->exec("ALTER TABLE customers ADD COLUMN is_demo INTEGER NOT NULL DEFAULT 0");
		}
		if (!in_array('city', $existingCols, true))
		{
			$db->exec("ALTER TABLE customers ADD COLUMN city TEXT NULL");
		}
		if (!in_array('state', $existingCols, true))
		{
			$db->exec("ALTER TABLE customers ADD COLUMN state TEXT NULL");
		}

		// Crear tabla de establecimientos si no existe
		$db->exec("
			CREATE TABLE IF NOT EXISTS customer_establishments (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				customer_id INTEGER NOT NULL,
				code TEXT NOT NULL,
				name TEXT NOT NULL,
				address TEXT,
				city TEXT,
				state TEXT,
				purchasing_contact_name TEXT,
				purchasing_contact_phone TEXT,
				purchasing_contact_email TEXT,
				sales_rep_name TEXT,
				territory TEXT,
				active INTEGER NOT NULL DEFAULT 1,
				is_demo INTEGER NOT NULL DEFAULT 0,
				created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				updated_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
				FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE RESTRICT
			);
			CREATE INDEX IF NOT EXISTS idx_cust_est_cust_id ON customer_establishments(customer_id);
			CREATE INDEX IF NOT EXISTS idx_cust_est_code ON customer_establishments(code);
			CREATE INDEX IF NOT EXISTS idx_cust_est_active ON customer_establishments(active);
		");
	}

	/**
	 * Carga inicial de datos de demostración coherentes (idempotente)
	 */
	public function SeedDemoDataIfEmpty(): void
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$count = (int)$db->query("SELECT COUNT(*) FROM customers WHERE is_demo = 1 OR tags LIKE '%DEMO%'")->fetchColumn();
		if ($count > 0)
		{
			return; // Ya existen datos demo sembrados
		}

		$db->beginTransaction();
		try
		{
			// 3 Cadenas comerciales, 2 supermercados independientes, 2 minimarkets
			$demoClients = [
				[
					'name' => 'Supermercados Santa Isabel S.A.',
					'commercial_name' => 'Santa Isabel Express',
					'customer_type' => 'cadena',
					'tax_id' => 'RUC-20100458921',
					'phone' => '+51 1 614-2000',
					'email' => 'compras@santaisabel.com.pe',
					'address' => 'Av. Javier Prado Este 4200, Surco, Lima',
					'city' => 'Lima',
					'state' => 'Lima',
					'is_demo' => 1,
					'establishments' => [
						[
							'code' => 'EST-SI-01',
							'name' => 'Santa Isabel Centro',
							'address' => 'Av. Central 1024',
							'city' => 'Lima',
							'state' => 'Lima',
							'purchasing_contact_name' => 'Roberto Gómez',
							'purchasing_contact_phone' => '+51 984 112 334',
							'purchasing_contact_email' => 'rgomez@santaisabel.com.pe',
							'sales_rep_name' => 'Carlos Mendoza',
							'territory' => 'Lima Centro'
						],
						[
							'code' => 'EST-SI-02',
							'name' => 'Santa Isabel San Isidro',
							'address' => 'Av. Conquistadores 450',
							'city' => 'San Isidro',
							'state' => 'Lima',
							'purchasing_contact_name' => 'Patricia Silva',
							'purchasing_contact_phone' => '+51 971 223 445',
							'purchasing_contact_email' => 'psilva@santaisabel.com.pe',
							'sales_rep_name' => 'Carlos Mendoza',
							'territory' => 'Lima Sur'
						],
						[
							'code' => 'EST-SI-03',
							'name' => 'Santa Isabel Los Olivos',
							'address' => 'Av. Antúnez de Mayolo 890',
							'city' => 'Los Olivos',
							'state' => 'Lima',
							'purchasing_contact_name' => 'Jorge Huamán',
							'purchasing_contact_phone' => '+51 992 334 556',
							'purchasing_contact_email' => 'jhuaman@santaisabel.com.pe',
							'sales_rep_name' => 'Ana Torres',
							'territory' => 'Lima Norte'
						]
					]
				],
				[
					'name' => 'Cadena Mayorista del Centro S.A.C.',
					'commercial_name' => 'Mayorista Central',
					'customer_type' => 'cadena',
					'tax_id' => 'RUC-20455891230',
					'phone' => '+51 64 231-500',
					'email' => 'abastecimiento@mayoristacentral.pe',
					'address' => 'Av. Ferrocarril 850, Huancayo',
					'city' => 'Huancayo',
					'state' => 'Junín',
					'is_demo' => 1,
					'establishments' => [
						[
							'code' => 'EST-MC-01',
							'name' => 'CD Huancayo Central',
							'address' => 'Calle Real 540',
							'city' => 'Huancayo',
							'state' => 'Junín',
							'purchasing_contact_name' => 'Miguel Ramos',
							'purchasing_contact_phone' => '+51 964 556 778',
							'purchasing_contact_email' => 'mramos@mayoristacentral.pe',
							'sales_rep_name' => 'Luis Paredes',
							'territory' => 'Sierra Central'
						],
						[
							'code' => 'EST-MC-02',
							'name' => 'Sucursal El Tambo',
							'address' => 'Av. Huancavelica 1120',
							'city' => 'El Tambo',
							'state' => 'Junín',
							'purchasing_contact_name' => 'Elena Castro',
							'purchasing_contact_phone' => '+51 964 667 889',
							'purchasing_contact_email' => 'ecastro@mayoristacentral.pe',
							'sales_rep_name' => 'Luis Paredes',
							'territory' => 'Sierra Central'
						],
						[
							'code' => 'EST-MC-03',
							'name' => 'Sucursal Tarma',
							'address' => 'Jr. Lima 320',
							'city' => 'Tarma',
							'state' => 'Junín',
							'purchasing_contact_name' => 'David Alania',
							'purchasing_contact_phone' => '+51 964 778 990',
							'purchasing_contact_email' => 'dalania@mayoristacentral.pe',
							'sales_rep_name' => 'Luis Paredes',
							'territory' => 'Sierra Central'
						]
					]
				],
				[
					'name' => 'Almacenes & Retail Express S.A.',
					'commercial_name' => 'Retail Express',
					'customer_type' => 'cadena',
					'tax_id' => 'RUC-20601248795',
					'phone' => '+51 1 445-8890',
					'email' => 'ordenes@retailexpress.com',
					'address' => 'Av. Angamos Oeste 120, Miraflores, Lima',
					'city' => 'Lima',
					'state' => 'Lima',
					'is_demo' => 1,
					'establishments' => [
						[
							'code' => 'EST-RE-01',
							'name' => 'Express Miraflores',
							'address' => 'Av. Larco 780',
							'city' => 'Miraflores',
							'state' => 'Lima',
							'purchasing_contact_name' => 'Daniela Ponce',
							'purchasing_contact_phone' => '+51 955 889 112',
							'purchasing_contact_email' => 'dponce@retailexpress.com',
							'sales_rep_name' => 'Carlos Mendoza',
							'territory' => 'Lima Sur'
						],
						[
							'code' => 'EST-RE-02',
							'name' => 'Express La Molina',
							'address' => 'Av. Javier Prado Este 5600',
							'city' => 'La Molina',
							'state' => 'Lima',
							'purchasing_contact_name' => 'Fernando Ríos',
							'purchasing_contact_phone' => '+51 955 990 223',
							'purchasing_contact_email' => 'frios@retailexpress.com',
							'sales_rep_name' => 'Carlos Mendoza',
							'territory' => 'Lima Este'
						]
					]
				],
				[
					'name' => 'Supermercado El Sol de Chosica E.I.R.L.',
					'commercial_name' => 'Supermercado El Sol',
					'customer_type' => 'supermercado',
					'tax_id' => 'RUC-20389145672',
					'phone' => '+51 1 361-1200',
					'email' => 'administracion@elsolchosica.pe',
					'address' => 'Jr. Trujillo 410, Lurigancho-Chosica',
					'city' => 'Chosica',
					'state' => 'Lima',
					'is_demo' => 1,
					'establishments' => [
						[
							'code' => 'EST-SOL-01',
							'name' => 'Sede Central Chosica',
							'address' => 'Jr. Trujillo 410',
							'city' => 'Chosica',
							'state' => 'Lima',
							'purchasing_contact_name' => 'Walter Rojas',
							'purchasing_contact_phone' => '+51 991 445 667',
							'purchasing_contact_email' => 'wrojas@elsolchosica.pe',
							'sales_rep_name' => 'Ana Torres',
							'territory' => 'Lima Este'
						]
					]
				],
				[
					'name' => 'Comercializadora & Supermercado La Canasta S.A.C.',
					'commercial_name' => 'La Canasta Supermercados',
					'customer_type' => 'supermercado',
					'tax_id' => 'RUC-20512398741',
					'phone' => '+51 1 572-8900',
					'email' => 'compras@lacanasta.com.pe',
					'address' => 'Av. Faucett 1420, Bellavista',
					'city' => 'Callao',
					'state' => 'Callao',
					'is_demo' => 1,
					'establishments' => [
						[
							'code' => 'EST-CAN-01',
							'name' => 'Sede Principal Bellavista',
							'address' => 'Av. Faucett 1420',
							'city' => 'Callao',
							'state' => 'Callao',
							'purchasing_contact_name' => 'Rosa Medina',
							'purchasing_contact_phone' => '+51 988 776 554',
							'purchasing_contact_email' => 'rmedina@lacanasta.com.pe',
							'sales_rep_name' => 'Ana Torres',
							'territory' => 'Callao'
						]
					]
				],
				[
					'name' => 'Inversiones Tambo Market S.A.C.',
					'commercial_name' => 'Tambo Market',
					'customer_type' => 'minimarket',
					'tax_id' => 'RUC-20567812903',
					'phone' => '+51 1 225-3344',
					'email' => 'tiendas@tambomarket.pe',
					'address' => 'Av. Aviación 2890, San Borja',
					'city' => 'Lima',
					'state' => 'Lima',
					'is_demo' => 1,
					'establishments' => [
						[
							'code' => 'EST-TM-01',
							'name' => 'Tambo Market San Borja',
							'address' => 'Av. Aviación 2890',
							'city' => 'San Borja',
							'state' => 'Lima',
							'purchasing_contact_name' => 'César Díaz',
							'purchasing_contact_phone' => '+51 944 112 889',
							'purchasing_contact_email' => 'cdiaz@tambomarket.pe',
							'sales_rep_name' => 'Carlos Mendoza',
							'territory' => 'Lima Centro'
						],
						[
							'code' => 'EST-TM-02',
							'name' => 'Tambo Market Surco',
							'address' => 'Av. Primavera 650',
							'city' => 'Surco',
							'state' => 'Lima',
							'purchasing_contact_name' => 'Carmen Vargas',
							'purchasing_contact_phone' => '+51 944 223 990',
							'purchasing_contact_email' => 'cvargas@tambomarket.pe',
							'sales_rep_name' => 'Carlos Mendoza',
							'territory' => 'Lima Sur'
						]
					]
				],
				[
					'name' => 'Bodega & Minimarket Don Pepe S.C.R.L.',
					'commercial_name' => 'Minimarket Don Pepe',
					'customer_type' => 'minimarket',
					'tax_id' => 'RUC-20234567891',
					'phone' => '+51 1 263-4411',
					'email' => 'donpepe@minimarket.pe',
					'address' => 'Jr. Castilla 520, Magdalena del Mar',
					'city' => 'Lima',
					'state' => 'Lima',
					'is_demo' => 1,
					'establishments' => [
						[
							'code' => 'EST-DP-01',
							'name' => 'Tienda Don Pepe Magdalena',
							'address' => 'Jr. Castilla 520',
							'city' => 'Magdalena del Mar',
							'state' => 'Lima',
							'purchasing_contact_name' => 'José Peñaloza',
							'purchasing_contact_phone' => '+51 981 998 776',
							'purchasing_contact_email' => 'jpenaloza@minimarket.pe',
							'sales_rep_name' => 'Carlos Mendoza',
							'territory' => 'Lima Centro'
						]
					]
				]
			];

			$stmtCust = $db->prepare("
				INSERT INTO customers (name, commercial_name, customer_type, tax_id, phone, email, address, city, state, is_demo, active, tags)
				VALUES (:name, :commercial_name, :customer_type, :tax_id, :phone, :email, :address, :city, :state, 1, 1, 'DEMO,B2B')
			");

			$stmtEst = $db->prepare("
				INSERT INTO customer_establishments (customer_id, code, name, address, city, state, purchasing_contact_name, purchasing_contact_phone, purchasing_contact_email, sales_rep_name, territory, active, is_demo)
				VALUES (:customer_id, :code, :name, :address, :city, :state, :purchasing_contact_name, :purchasing_contact_phone, :purchasing_contact_email, :sales_rep_name, :territory, 1, 1)
			");

			foreach ($demoClients as $client)
			{
				$stmtCust->execute([
					':name' => $client['name'],
					':commercial_name' => $client['commercial_name'],
					':customer_type' => $client['customer_type'],
					':tax_id' => $client['tax_id'],
					':phone' => $client['phone'],
					':email' => $client['email'],
					':address' => $client['address'],
					':city' => $client['city'],
					':state' => $client['state']
				]);

				$customerId = (int)$db->lastInsertId();

				foreach ($client['establishments'] as $est)
				{
					$stmtEst->execute([
						':customer_id' => $customerId,
						':code' => $est['code'],
						':name' => $est['name'],
						':address' => $est['address'],
						':city' => $est['city'],
						':state' => $est['state'],
						':purchasing_contact_name' => $est['purchasing_contact_name'],
						':purchasing_contact_phone' => $est['purchasing_contact_phone'],
						':purchasing_contact_email' => $est['purchasing_contact_email'],
						':sales_rep_name' => $est['sales_rep_name'],
						':territory' => $est['territory']
					]);
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
	 * Obtiene métricas ejecutivas de clientes y establecimientos
	 */
	public function GetMetrics(): array
	{
		$this->EnsureSchema();
		$this->SeedDemoDataIfEmpty();
		$db = $this->Db();

		$activeCust = (int)$db->query("SELECT COUNT(*) FROM customers WHERE active = 1")->fetchColumn();
		$chains = (int)$db->query("SELECT COUNT(*) FROM customers WHERE active = 1 AND LOWER(customer_type) = 'cadena'")->fetchColumn();
		$establishments = (int)$db->query("SELECT COUNT(*) FROM customer_establishments WHERE active = 1")->fetchColumn();
		$inactiveCust = (int)$db->query("SELECT COUNT(*) FROM customers WHERE active = 0")->fetchColumn();

		return [
			'active_customers' => $activeCust,
			'chains' => $chains,
			'total_establishments' => $establishments,
			'inactive_customers' => $inactiveCust
		];
	}

	/**
	 * Consulta clientes comerciales con conteo de establecimientos
	 */
	public function GetCustomers(array $filters = []): array
	{
		$this->EnsureSchema();
		$this->SeedDemoDataIfEmpty();
		$db = $this->Db();

		$where = ['1=1'];
		$params = [];

		if (!empty($filters['search']))
		{
			$search = '%' . trim($filters['search']) . '%';
			$where[] = "(c.name LIKE :s1 OR c.commercial_name LIKE :s2 OR c.tax_id LIKE :s3)";
			$params[':s1'] = $search;
			$params[':s2'] = $search;
			$params[':s3'] = $search;
		}

		if (!empty($filters['type']))
		{
			$where[] = "LOWER(c.customer_type) = :type";
			$params[':type'] = strtolower(trim($filters['type']));
		}

		if (isset($filters['active']) && $filters['active'] !== '')
		{
			$where[] = "c.active = :active";
			$params[':active'] = (int)$filters['active'];
		}

		$whereSql = implode(' AND ', $where);

		$sql = "
			SELECT 
				c.id,
				c.name,
				COALESCE(c.commercial_name, c.name) AS commercial_name,
				COALESCE(c.customer_type, 'otro') AS customer_type,
				COALESCE(c.tax_id, '') AS tax_id,
				c.phone,
				c.email,
				c.address,
				c.city,
				c.state,
				c.active,
				c.is_demo,
				c.created_timestamp,
				(SELECT COUNT(*) FROM customer_establishments e WHERE e.customer_id = c.id AND e.active = 1) AS active_establishments_count,
				(SELECT COUNT(*) FROM customer_establishments e WHERE e.customer_id = c.id) AS total_establishments_count
			FROM customers c
			WHERE {$whereSql}
			ORDER BY c.active DESC, c.id DESC
		";

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Ficha detallada de un cliente con sus establecimientos
	 */
	public function GetCustomer(int $id): ?array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->prepare("
			SELECT 
				c.id,
				c.name,
				COALESCE(c.commercial_name, c.name) AS commercial_name,
				COALESCE(c.customer_type, 'otro') AS customer_type,
				COALESCE(c.tax_id, '') AS tax_id,
				c.phone,
				c.email,
				c.address,
				c.city,
				c.state,
				c.active,
				c.is_demo,
				c.created_timestamp
			FROM customers c
			WHERE c.id = :id
		");
		$stmt->execute([':id' => $id]);
		$customer = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$customer)
		{
			return null;
		}

		// Obtener establecimientos asociados
		$stmtEst = $db->prepare("
			SELECT 
				e.id,
				e.customer_id,
				e.code,
				e.name,
				e.address,
				e.city,
				e.state,
				e.purchasing_contact_name,
				e.purchasing_contact_phone,
				e.purchasing_contact_email,
				e.sales_rep_name,
				e.territory,
				e.active,
				e.is_demo,
				e.created_timestamp
			FROM customer_establishments e
			WHERE e.customer_id = :id
			ORDER BY e.active DESC, e.id ASC
		");
		$stmtEst->execute([':id' => $id]);
		$customer['establishments'] = $stmtEst->fetchAll(PDO::FETCH_ASSOC);

		// Obtener contactos asociados (Paso 13)
		try {
			$stmtCont = $db->prepare("SELECT * FROM b2b_customer_contacts WHERE customer_id = :id ORDER BY is_primary DESC, id ASC");
			$stmtCont->execute([':id' => $id]);
			$customer['contacts'] = $stmtCont->fetchAll(PDO::FETCH_ASSOC);
		} catch (\Throwable $e) { $customer['contacts'] = []; }

		// Obtener direcciones asociadas (Paso 13)
		try {
			$stmtAddr = $db->prepare("SELECT * FROM b2b_customer_addresses WHERE customer_id = :id ORDER BY is_default DESC, id ASC");
			$stmtAddr->execute([':id' => $id]);
			$customer['addresses'] = $stmtAddr->fetchAll(PDO::FETCH_ASSOC);
		} catch (\Throwable $e) { $customer['addresses'] = []; }

		return $customer;
	}

	/**
	 * Crea un nuevo cliente comercial
	 */
	public function CreateCustomer(array $data): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$name = trim($data['name'] ?? '');
		$commercialName = trim($data['commercial_name'] ?? '');
		$taxId = trim($data['tax_id'] ?? '');
		$type = strtolower(trim($data['customer_type'] ?? 'supermercado'));
		$phone = trim($data['phone'] ?? '');
		$email = trim($data['email'] ?? '');
		$address = trim($data['address'] ?? '');
		$city = trim($data['city'] ?? '');
		$state = trim($data['state'] ?? '');

		if (empty($name))
		{
			throw new Exception('La razón social es obligatoria.');
		}
		if (empty($taxId))
		{
			throw new Exception('La identificación fiscal (RUC / Tax ID) es obligatoria.');
		}

		// Validar unicidad de identificación fiscal
		$stmtCheck = $db->prepare("SELECT id FROM customers WHERE tax_id = :tax_id");
		$stmtCheck->execute([':tax_id' => $taxId]);
		if ($stmtCheck->fetchColumn())
		{
			throw new Exception('Ya existe un cliente registrado con esa identificación fiscal.');
		}

		$validTypes = ['cadena', 'supermercado', 'minimarket', 'otro'];
		if (!in_array($type, $validTypes, true))
		{
			$type = 'otro';
		}

		$stmt = $db->prepare("
			INSERT INTO customers (name, commercial_name, customer_type, tax_id, phone, email, address, city, state, active, is_demo)
			VALUES (:name, :commercial_name, :customer_type, :tax_id, :phone, :email, :address, :city, :state, 1, 0)
		");

		$stmt->execute([
			':name' => $name,
			':commercial_name' => !empty($commercialName) ? $commercialName : $name,
			':customer_type' => $type,
			':tax_id' => $taxId,
			':phone' => $phone,
			':email' => $email,
			':address' => $address,
			':city' => $city,
			':state' => $state
		]);

		$id = (int)$db->lastInsertId();

		try {
			$stmtAudit = $db->prepare("
				INSERT INTO b2b_audit_log (organization_id, user_name, entity_type, entity_id, action, new_values, ip_address, created_at)
				VALUES (1, 'Carlos Mendoza', 'CUSTOMER', :eid, 'CREATE', :val, '127.0.0.1', datetime('now', 'localtime'))
			");
			$stmtAudit->execute([
				':eid' => $id,
				':val' => json_encode(['name' => $name, 'tax_id' => $taxId, 'customer_type' => $type], JSON_UNESCAPED_UNICODE)
			]);
		} catch (\Throwable $e) {}

		return $this->GetCustomer($id);
	}

	/**
	 * Actualiza los datos de un cliente comercial
	 */
	public function UpdateCustomer(int $id, array $data): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$existing = $this->GetCustomer($id);
		if (!$existing)
		{
			throw new Exception('El cliente no existe.');
		}

		$name = trim($data['name'] ?? $existing['name']);
		$commercialName = trim($data['commercial_name'] ?? $existing['commercial_name']);
		$taxId = trim($data['tax_id'] ?? $existing['tax_id']);
		$type = strtolower(trim($data['customer_type'] ?? $existing['customer_type']));
		$phone = trim($data['phone'] ?? $existing['phone']);
		$email = trim($data['email'] ?? $existing['email']);
		$address = trim($data['address'] ?? $existing['address']);
		$city = trim($data['city'] ?? $existing['city']);
		$state = trim($data['state'] ?? $existing['state']);

		if (empty($name))
		{
			throw new Exception('La razón social es obligatoria.');
		}
		if (empty($taxId))
		{
			throw new Exception('La identificación fiscal es obligatoria.');
		}

		// Validar unicidad de tax_id en otros clientes
		$stmtCheck = $db->prepare("SELECT id FROM customers WHERE tax_id = :tax_id AND id != :id");
		$stmtCheck->execute([':tax_id' => $taxId, ':id' => $id]);
		if ($stmtCheck->fetchColumn())
		{
			throw new Exception('La identificación fiscal ya está en uso por otro cliente.');
		}

		$stmt = $db->prepare("
			UPDATE customers
			SET name = :name,
				commercial_name = :commercial_name,
				customer_type = :customer_type,
				tax_id = :tax_id,
				phone = :phone,
				email = :email,
				address = :address,
				city = :city,
				state = :state,
				updated_at = datetime('now', 'localtime')
			WHERE id = :id
		");

		$stmt->execute([
			':id' => $id,
			':name' => $name,
			':commercial_name' => $commercialName,
			':customer_type' => $type,
			':tax_id' => $taxId,
			':phone' => $phone,
			':email' => $email,
			':address' => $address,
			':city' => $city,
			':state' => $state
		]);

		try {
			$stmtAudit = $db->prepare("
				INSERT INTO b2b_audit_log (organization_id, user_name, entity_type, entity_id, action, new_values, ip_address, created_at)
				VALUES (1, 'Carlos Mendoza', 'CUSTOMER', :eid, 'UPDATE', :val, '127.0.0.1', datetime('now', 'localtime'))
			");
			$stmtAudit->execute([
				':eid' => $id,
				':val' => json_encode(['name' => $name, 'tax_id' => $taxId, 'customer_type' => $type], JSON_UNESCAPED_UNICODE)
			]);
		} catch (\Throwable $e) {}

		return $this->GetCustomer($id);
	}

	/**
	 * Alterna el estado activo/inactivo de un cliente
	 */
	public function ToggleCustomerActive(int $id, ?bool $newStatus = null): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$existing = $this->GetCustomer($id);
		if (!$existing)
		{
			throw new Exception('El cliente no existe.');
		}

		$currentActive = (int)$existing['active'];
		$targetActive = ($newStatus !== null) ? ($newStatus ? 1 : 0) : ($currentActive === 1 ? 0 : 1);

		// Regla de negocio: Si se desactiva una cadena con establecimientos activos, desactivarlos en cascada de forma controlada
		$db->beginTransaction();
		try
		{
			$stmt = $db->prepare("UPDATE customers SET active = :active WHERE id = :id");
			$stmt->execute([':active' => $targetActive, ':id' => $id]);

			if ($targetActive === 0)
			{
				$stmtEst = $db->prepare("UPDATE customer_establishments SET active = 0 WHERE customer_id = :id");
				$stmtEst->execute([':id' => $id]);
			}

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollBack();
			throw $e;
		}

		return $this->GetCustomer($id);
	}

	/**
	 * Crea un establecimiento asociado a un cliente comercial
	 */
	public function CreateEstablishment(array $data): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$customerId = (int)($data['customer_id'] ?? 0);
		$code = strtoupper(trim($data['code'] ?? ''));
		$name = trim($data['name'] ?? '');
		$address = trim($data['address'] ?? '');
		$city = trim($data['city'] ?? '');
		$state = trim($data['state'] ?? '');
		$contactName = trim($data['purchasing_contact_name'] ?? '');
		$contactPhone = trim($data['purchasing_contact_phone'] ?? '');
		$contactEmail = trim($data['purchasing_contact_email'] ?? '');
		$salesRep = trim($data['sales_rep_name'] ?? '');
		$territory = trim($data['territory'] ?? '');

		if ($customerId <= 0)
		{
			throw new Exception('El cliente comercial asociado es obligatorio.');
		}

		$customer = $this->GetCustomer($customerId);
		if (!$customer)
		{
			throw new Exception('El cliente comercial especificado no existe.');
		}

		if (empty($code))
		{
			throw new Exception('El código de tienda es obligatorio.');
		}
		if (empty($name))
		{
			throw new Exception('El nombre de la tienda es obligatorio.');
		}

		// Validar unicidad del código de establecimiento
		$stmtCheck = $db->prepare("SELECT id FROM customer_establishments WHERE code = :code");
		$stmtCheck->execute([':code' => $code]);
		if ($stmtCheck->fetchColumn())
		{
			throw new Exception("El código de establecimiento '{$code}' ya existe.");
		}

		$stmt = $db->prepare("
			INSERT INTO customer_establishments (
				customer_id, code, name, address, city, state,
				purchasing_contact_name, purchasing_contact_phone, purchasing_contact_email,
				sales_rep_name, territory, active, is_demo
			) VALUES (
				:customer_id, :code, :name, :address, :city, :state,
				:contact_name, :contact_phone, :contact_email,
				:sales_rep, :territory, 1, :is_demo
			)
		");

		$stmt->execute([
			':customer_id' => $customerId,
			':code' => $code,
			':name' => $name,
			':address' => $address,
			':city' => $city,
			':state' => $state,
			':contact_name' => $contactName,
			':contact_phone' => $contactPhone,
			':contact_email' => $contactEmail,
			':sales_rep' => $salesRep,
			':territory' => $territory,
			':is_demo' => (int)($customer['is_demo'] ?? 0)
		]);

		$estId = (int)$db->lastInsertId();
		return $this->GetEstablishment($estId);
	}

	/**
	 * Actualiza un establecimiento
	 */
	public function UpdateEstablishment(int $id, array $data): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$existing = $this->GetEstablishment($id);
		if (!$existing)
		{
			throw new Exception('El establecimiento no existe.');
		}

		$code = strtoupper(trim($data['code'] ?? $existing['code']));
		$name = trim($data['name'] ?? $existing['name']);
		$address = trim($data['address'] ?? $existing['address']);
		$city = trim($data['city'] ?? $existing['city']);
		$state = trim($data['state'] ?? $existing['state']);
		$contactName = trim($data['purchasing_contact_name'] ?? $existing['purchasing_contact_name']);
		$contactPhone = trim($data['purchasing_contact_phone'] ?? $existing['purchasing_contact_phone']);
		$contactEmail = trim($data['purchasing_contact_email'] ?? $existing['purchasing_contact_email']);
		$salesRep = trim($data['sales_rep_name'] ?? $existing['sales_rep_name']);
		$territory = trim($data['territory'] ?? $existing['territory']);

		if (empty($code))
		{
			throw new Exception('El código de tienda es obligatorio.');
		}
		if (empty($name))
		{
			throw new Exception('El nombre de la tienda es obligatorio.');
		}

		// Validar código duplicado
		$stmtCheck = $db->prepare("SELECT id FROM customer_establishments WHERE code = :code AND id != :id");
		$stmtCheck->execute([':code' => $code, ':id' => $id]);
		if ($stmtCheck->fetchColumn())
		{
			throw new Exception("El código de establecimiento '{$code}' ya está asignado a otra sucursal.");
		}

		$stmt = $db->prepare("
			UPDATE customer_establishments
			SET code = :code,
				name = :name,
				address = :address,
				city = :city,
				state = :state,
				purchasing_contact_name = :contact_name,
				purchasing_contact_phone = :contact_phone,
				purchasing_contact_email = :contact_email,
				sales_rep_name = :sales_rep,
				territory = :territory,
				updated_timestamp = datetime('now', 'localtime')
			WHERE id = :id
		");

		$stmt->execute([
			':id' => $id,
			':code' => $code,
			':name' => $name,
			':address' => $address,
			':city' => $city,
			':state' => $state,
			':contact_name' => $contactName,
			':contact_phone' => $contactPhone,
			':contact_email' => $contactEmail,
			':sales_rep' => $salesRep,
			':territory' => $territory
		]);

		return $this->GetEstablishment($id);
	}

	/**
	 * Alterna el estado activo/inactivo de un establecimiento
	 */
	public function ToggleEstablishmentActive(int $id, ?bool $newStatus = null): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$existing = $this->GetEstablishment($id);
		if (!$existing)
		{
			throw new Exception('El establecimiento no existe.');
		}

		$currentActive = (int)$existing['active'];
		$targetActive = ($newStatus !== null) ? ($newStatus ? 1 : 0) : ($currentActive === 1 ? 0 : 1);

		$stmt = $db->prepare("UPDATE customer_establishments SET active = :active WHERE id = :id");
		$stmt->execute([':active' => $targetActive, ':id' => $id]);

		return $this->GetEstablishment($id);
	}

	/**
	 * Obtiene un establecimiento por ID
	 */
	public function GetEstablishment(int $id): ?array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->prepare("
			SELECT 
				e.id,
				e.customer_id,
				e.code,
				e.name,
				e.address,
				e.city,
				e.state,
				e.purchasing_contact_name,
				e.purchasing_contact_phone,
				e.purchasing_contact_email,
				e.sales_rep_name,
				e.territory,
				e.active,
				e.is_demo,
				e.created_timestamp
			FROM customer_establishments e
			WHERE e.id = :id
		");
		$stmt->execute([':id' => $id]);
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		return $res ?: null;
	}
}
