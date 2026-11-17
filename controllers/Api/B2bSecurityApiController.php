<?php

namespace Grocy\Controllers\Api;

use Grocy\Services\B2bSecurityService;
use Grocy\Services\B2bAuditService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class B2bSecurityApiController extends BaseApiController
{
	private ?B2bSecurityService $securityService = null;
	private ?B2bAuditService $auditService = null;

	private function SecurityService(): B2bSecurityService
	{
		if ($this->securityService === null) {
			$this->securityService = new B2bSecurityService();
		}
		return $this->securityService;
	}

	private function AuditService(): B2bAuditService
	{
		if ($this->auditService === null) {
			$this->auditService = new B2bAuditService();
		}
		return $this->auditService;
	}

	protected function JsonResponse(Response $response, array $data, int $status = 200): Response
	{
		$response = $response->withStatus($status)->withHeader('Content-Type', 'application/json');
		$response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
		return $response;
	}

	public function GetRoles(Request $request, Response $response, array $args): Response
	{
		try {
			$roles = $this->SecurityService()->GetRolesMatrix();
			return $this->ApiResponse($response, [
				'success' => true,
				'roles' => $roles
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetApiKeys(Request $request, Response $response, array $args): Response
	{
		try {
			$keys = $this->SecurityService()->ListApiKeys();
			return $this->ApiResponse($response, [
				'success' => true,
				'api_keys' => $keys,
				'keys' => $keys,
				'total' => count($keys)
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function CreateApiKey(Request $request, Response $response, array $args): Response
	{
		try {
			$data = (array)($request->getParsedBody() ?? []);
			$desc = trim($data['description'] ?? $data['name'] ?? 'Integración B2B');
			$role = trim($data['role'] ?? B2bSecurityService::ROLE_SALES_REP);
			$expiresDays = isset($data['expires_days']) ? (int)$data['expires_days'] : (isset($data['expires_in_days']) ? (int)$data['expires_in_days'] : 365);

			$created = $this->SecurityService()->CreateB2bApiKey($desc, $role, 1, $expiresDays);

			return $this->JsonResponse($response, [
				'success' => true,
				'message' => 'Llave de API B2B generada exitosamente. Guarde el token en un lugar seguro.',
				'key' => $created,
				'api_key' => $created
			], 201);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function RevokeApiKey(Request $request, Response $response, array $args): Response
	{
		try {
			$id = (int)$args['id'];
			$success = $this->SecurityService()->RevokeApiKey($id);

			if (!$success) {
				return $this->GenericErrorResponse($response, 'No se pudo revocar la llave especificada.', 404);
			}

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => "Llave de API ID {$id} revocada permanentemente."
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetAuditLog(Request $request, Response $response, array $args): Response
	{
		try {
			$queryParams = $request->getQueryParams();
			$logs = $this->AuditService()->GetAuditLogs($queryParams);

			return $this->ApiResponse($response, [
				'success' => true,
				'audit_logs' => $logs,
				'logs' => $logs,
				'total' => count($logs)
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function VerifyAccess(Request $request, Response $response, array $args): Response
	{
		try {
			$data = (array)($request->getParsedBody() ?? []);
			$role = trim($data['role'] ?? '');
			$permission = trim($data['permission'] ?? '');

			if (empty($role) || empty($permission)) {
				return $this->GenericErrorResponse($response, 'Debe especificar el rol y el permiso a verificar.', 400);
			}

			$allowed = $this->SecurityService()->HasPermission($role, $permission);

			return $this->JsonResponse($response, [
				'success' => true,
				'role' => $role,
				'permission' => $permission,
				'granted' => $allowed,
				'allowed' => $allowed,
				'status' => $allowed ? 'ACCESO_PERMITIDO' : 'ACCESO_DENEGADO'
			], $allowed ? 200 : 403);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}
}
