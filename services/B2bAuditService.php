<?php

namespace Grocy\Services;

class B2bAuditService extends BaseService
{
	protected function getDatabaseService(): DatabaseService
	{
		return DatabaseService::GetInstance();
	}

	public function Log(string $entityType, int $entityId, string $action, ?array $oldValues = null, ?array $newValues = null, string $userName = 'Carlos Mendoza', int $orgId = 1): int
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		$stmt = $db->prepare("
			INSERT INTO b2b_audit_log (organization_id, user_name, entity_type, entity_id, action, old_values, new_values, ip_address, created_at)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now', 'localtime'))
		");

		$oldJson = $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
		$newJson = $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;
		$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

		$stmt->execute([$orgId, $userName, strtoupper($entityType), $entityId, strtoupper($action), $oldJson, $newJson, $ip]);
		return (int)$db->lastInsertId();
	}

	public function GetAuditLogs(array $filters = []): array
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		$where = ["organization_id = :org_id"];
		$params = [':org_id' => $filters['organization_id'] ?? 1];

		if (!empty($filters['entity_type'])) {
			$where[] = "entity_type = :entity_type";
			$params[':entity_type'] = strtoupper($filters['entity_type']);
		}

		if (!empty($filters['entity_id'])) {
			$where[] = "entity_id = :entity_id";
			$params[':entity_id'] = (int)$filters['entity_id'];
		}

		if (!empty($filters['action'])) {
			$where[] = "action = :action";
			$params[':action'] = strtoupper($filters['action']);
		}

		$whereSql = implode(' AND ', $where);
		$limit = (int)($filters['limit'] ?? 50);

		$stmt = $db->prepare("SELECT * FROM b2b_audit_log WHERE $whereSql ORDER BY id DESC LIMIT $limit");
		$stmt->execute($params);
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}
}
