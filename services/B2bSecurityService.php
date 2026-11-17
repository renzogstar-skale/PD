<?php

namespace Grocy\Services;

use PDO;
use Exception;

class B2bSecurityService extends BaseService
{
	protected function getDatabaseService(): DatabaseService
	{
		return DatabaseService::GetInstance();
	}

	const ROLE_ADMIN = 'admin';
	const ROLE_SALES_DIRECTOR = 'sales_director';
	const ROLE_SALES_REP = 'sales_rep';
	const ROLE_BILLING = 'billing';
	const ROLE_LOGISTICS = 'logistics';

	// Matriz de permisos por rol
	private static array $rolePermissions = [
		self::ROLE_ADMIN => ['*'],
		self::ROLE_SALES_DIRECTOR => [
			'sales.read', 'sales.view',
			'customers.view', 'customers.create', 'customers.edit', 'customers.toggle',
			'products.view', 'products.create', 'products.edit', 'products.costs',
			'orders.view', 'orders.create', 'orders.edit', 'orders.approve',
			'invoices.view', 'invoices.create', 'invoices.payments', 'invoices.credit_notes',
			'sales_reps.view', 'sales_reps.manage', 'territories.manage',
			'profitability.view', 'profitability.adjust_cost',
			'intelligence.view', 'intelligence.manage',
			'security.api_keys'
		],
		self::ROLE_SALES_REP => [
			'sales.read', 'sales.view',
			'customers.view', 'customers.create', 'customers.edit',
			'products.view',
			'orders.view', 'orders.create',
			'quotes.view', 'quotes.create',
			'invoices.view',
			'sales_reps.self'
		],
		self::ROLE_BILLING => [
			'customers.view',
			'products.view',
			'orders.view',
			'invoices.view', 'invoices.create', 'invoices.payments', 'invoices.credit_notes', 'invoices.cancel',
			'profitability.view'
		],
		self::ROLE_LOGISTICS => [
			'orders.view',
			'invoices.view',
			'products.view'
		]
	];

	// Memoria simple de Rate Limiting por IP/token
	private static array $rateLimitStore = [];

	public function GetRolesMatrix(): array
	{
		return [
			[
				'role' => self::ROLE_ADMIN,
				'name' => 'Administrador Global',
				'description' => 'Acceso sin restricciones a todos los módulos comerciales, configuración fiscal, auditoría y seguridad',
				'permissions' => ['*']
			],
			[
				'role' => self::ROLE_SALES_DIRECTOR,
				'name' => 'Director Comercial',
				'description' => 'Supervisión completa de cartera, pedidos, metas comerciales, rentabilidad y fijación de costos',
				'permissions' => self::$rolePermissions[self::ROLE_SALES_DIRECTOR]
			],
			[
				'role' => self::ROLE_SALES_REP,
				'name' => 'Vendedor / Representante',
				'description' => 'Gestión de clientes, cotizaciones y pedidos dentro de su territorio asignado. Sin acceso a márgenes ni ajustes de costos',
				'permissions' => self::$rolePermissions[self::ROLE_SALES_REP]
			],
			[
				'role' => self::ROLE_BILLING,
				'name' => 'Facturación y Cobranzas',
				'description' => 'Emisión de facturas, conciliación de pagos, notas de crédito y recuperación de cartera',
				'permissions' => self::$rolePermissions[self::ROLE_BILLING]
			],
			[
				'role' => self::ROLE_LOGISTICS,
				'name' => 'Logística y Despachos',
				'description' => 'Consulta de órdenes confirmadas para alistamiento de pedidos y confirmación de entregas',
				'permissions' => self::$rolePermissions[self::ROLE_LOGISTICS]
			]
		];
	}

	public function HasPermission(string $role, string $permission): bool
	{
		$perms = self::$rolePermissions[$role] ?? [];
		if (in_array('*', $perms, true)) {
			return true;
		}

		if (in_array($permission, $perms, true)) {
			return true;
		}

		// Soporte para comodín por módulo (ej. customers.*)
		$parts = explode('.', $permission);
		if (count($parts) === 2) {
			$moduleWildcard = $parts[0] . '.*';
			if (in_array($moduleWildcard, $perms, true)) {
				return true;
			}
		}

		return false;
	}

	public function CreateB2bApiKey(string $description, string $role = self::ROLE_SALES_REP, int $userId = 1, ?int $expiresDays = 365): array
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		
		// Generar token seguro de 48 caracteres con prefijo b2b_
		$rawKey = bin2hex(random_bytes(24));
		$apiKey = 'b2b_' . $rawKey;
		
		$expires = $expiresDays ? date('Y-m-d H:i:s', strtotime("+{$expiresDays} days")) : null;
		$descWithRole = json_encode([
			'label' => trim($description) ?: 'Integración B2B',
			'role' => $role,
			'created_by' => 'Carlos Mendoza'
		], JSON_UNESCAPED_UNICODE);

		$stmt = $db->prepare("
			INSERT INTO api_keys (api_key, user_id, expires, key_type, description, row_created_timestamp)
			VALUES (:key, :uid, :exp, 'b2b_integration', :desc, datetime('now', 'localtime'))
		");

		$stmt->execute([
			':key' => $apiKey,
			':uid' => $userId,
			':exp' => $expires,
			':desc' => $descWithRole
		]);

		$id = (int)$db->lastInsertId();

		// Registrar evento de seguridad en auditoría
		$this->LogSecurityEvent('API_KEY_CREATED', 'Carlos Mendoza', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', true, "Generada llave B2B ID $id ($description) con rol $role");

		return [
			'id' => $id,
			'api_key' => $apiKey,
			'token' => $apiKey,
			'role' => $role,
			'description' => $description,
			'expires' => $expires,
			'created_at' => date('Y-m-d H:i:s')
		];
	}

	public function ValidateApiKey(string $apiKey): ?array
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		$stmt = $db->prepare("
			SELECT ak.*, u.username, u.first_name, u.last_name
			FROM api_keys ak
			LEFT JOIN users u ON ak.user_id = u.id
			WHERE ak.api_key = :key
		");
		$stmt->execute([':key' => $apiKey]);
		$key = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$key) {
			return null;
		}

		// Verificar expiración
		if (!empty($key['expires']) && strtotime($key['expires']) < time()) {
			return null;
		}

		// Actualizar last_used
		$db->prepare("UPDATE api_keys SET last_used = datetime('now', 'localtime') WHERE id = :id")->execute([':id' => $key['id']]);

		// Parsear rol de descripción si existe
		$role = self::ROLE_ADMIN;
		if (!empty($key['description'])) {
			$meta = json_decode($key['description'], true);
			if (is_array($meta) && !empty($meta['role'])) {
				$role = $meta['role'];
			}
		}

		return [
			'id' => (int)$key['id'],
			'user_id' => (int)$key['user_id'],
			'username' => $key['username'] ?? 'api_user',
			'role' => $role,
			'key_type' => $key['key_type']
		];
	}

	public function ListApiKeys(): array
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		$stmt = $db->query("
			SELECT ak.id, ak.api_key, ak.user_id, ak.expires, ak.last_used, ak.row_created_timestamp, ak.key_type, ak.description,
			       u.username, u.first_name, u.last_name
			FROM api_keys ak
			LEFT JOIN users u ON ak.user_id = u.id
			ORDER BY ak.id DESC
		");
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$result = [];
		foreach ($rows as $r) {
			$role = 'admin';
			$label = $r['description'];
			if (!empty($r['description']) && str_starts_with($r['description'], '{')) {
				$meta = json_decode($r['description'], true);
				if (is_array($meta)) {
					$role = $meta['role'] ?? 'admin';
					$label = $meta['label'] ?? $r['description'];
				}
			}

			// Ocultar la mitad del token por seguridad
			$maskedKey = substr($r['api_key'], 0, 8) . '...' . substr($r['api_key'], -6);

			$result[] = [
				'id' => (int)$r['id'],
				'masked_key' => $maskedKey,
				'key_prefix' => substr($r['api_key'], 0, 8),
				'label' => $label,
				'role' => $role,
				'user_name' => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: ($r['username'] ?? 'Admin'),
				'expires' => $r['expires'],
				'last_used' => $r['last_used'],
				'created_at' => $r['row_created_timestamp'],
				'is_expired' => !empty($r['expires']) && strtotime($r['expires']) < time(),
				'is_active' => (!empty($r['expires']) && strtotime($r['expires']) < time()) ? 0 : 1
			];
		}

		return $result;
	}

	public function RevokeApiKey(int $keyId): bool
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		$stmt = $db->prepare("DELETE FROM api_keys WHERE id = :id");
		$res = $stmt->execute([':id' => $keyId]);

		if ($res) {
			$this->LogSecurityEvent('API_KEY_REVOKED', 'Carlos Mendoza', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', true, "Llave API ID $keyId revocada exitosamente");
		}

		return $res;
	}

	public function CheckRateLimit(string $identifier, int $maxHits = 120, int $windowSeconds = 60): bool
	{
		$now = time();
		if (!isset(self::$rateLimitStore[$identifier])) {
			self::$rateLimitStore[$identifier] = [];
		}

		// Filtrar hits fuera de la ventana de tiempo
		self::$rateLimitStore[$identifier] = array_filter(
			self::$rateLimitStore[$identifier],
			fn($timestamp) => ($now - $timestamp) < $windowSeconds
		);

		if (count(self::$rateLimitStore[$identifier]) >= $maxHits) {
			return false; // Rate limit exceeded
		}

		self::$rateLimitStore[$identifier][] = $now;
		return true;
	}

	public function LogSecurityEvent(string $event, string $user, string $ip, bool $success, ?string $details = null): void
	{
		try {
			$db = $this->getDatabaseService()->GetDbConnectionRaw();
			$stmt = $db->prepare("
				INSERT INTO b2b_audit_log (organization_id, user_name, entity_type, entity_id, action, new_values, ip_address, created_at)
				VALUES (1, :user, 'SECURITY', 0, :event, :val, :ip, datetime('now', 'localtime'))
			");
			$stmt->execute([
				':user' => $user,
				':event' => $event,
				':val' => json_encode(['success' => $success, 'details' => $details], JSON_UNESCAPED_UNICODE),
				':ip' => $ip
			]);
		} catch (\Throwable $e) {}
	}
}
