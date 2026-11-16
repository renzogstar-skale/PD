<?php

namespace Grocy\Controllers\Api;

use Exception;
use Grocy\Services\B2bSalesRepService;
use Grocy\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controlador API RESTful para Vendedores, Territorios y Metas Comerciales B2B (Paso 9)
 */
class B2bSalesRepApiController extends BaseApiController
{
	private function SalesRepService(): B2bSalesRepService
	{
		return B2bSalesRepService::GetInstance();
	}

	public function GetMetrics(Request $request, Response $response, array $args): Response
	{
		try
		{
			$metrics = $this->SalesRepService()->GetExecutiveMetrics();
			return $this->ApiResponse($response, [
				'success' => true,
				'metrics' => $metrics
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetSalesReps(Request $request, Response $response, array $args): Response
	{
		try
		{
			$queryParams = $request->getQueryParams();
			$filters = [
				'search' => $queryParams['search'] ?? null,
				'active' => $queryParams['active'] ?? null,
				'territory_id' => $queryParams['territory_id'] ?? null,
				'from_date' => $queryParams['from_date'] ?? null,
				'to_date' => $queryParams['to_date'] ?? null
			];

			$reps = $this->SalesRepService()->GetSalesReps($filters);
			$metrics = $this->SalesRepService()->GetExecutiveMetrics();

			return $this->ApiResponse($response, [
				'success' => true,
				'sales_reps' => $reps,
				'metrics' => $metrics
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetSalesRep(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)$args['id'];
			$rep = $this->SalesRepService()->GetSalesRep($id);

			if (!$rep)
			{
				return $this->GenericErrorResponse($response, "Vendedor comercial #{$id} no encontrado.", 404);
			}

			return $this->ApiResponse($response, [
				'success' => true,
				'sales_rep' => $rep
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function CreateSalesRep(Request $request, Response $response, array $args): Response
	{
		try
		{
			$data = $request->getParsedBody();
			$newRep = $this->SalesRepService()->CreateSalesRep($data ?: []);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Vendedor comercial registrado exitosamente.',
				'sales_rep' => $newRep
			], 201);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function UpdateSalesRep(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)$args['id'];
			$data = $request->getParsedBody();
			$updated = $this->SalesRepService()->UpdateSalesRep($id, $data ?: []);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Datos y meta del vendedor comercial actualizados correctamente.',
				'sales_rep' => $updated
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function ToggleSalesRepStatus(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)$args['id'];
			$rep = $this->SalesRepService()->ToggleSalesRepStatus($id);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Estado del vendedor actualizado.',
				'sales_rep' => $rep
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetTerritories(Request $request, Response $response, array $args): Response
	{
		try
		{
			$queryParams = $request->getQueryParams();
			$filters = [
				'city' => $queryParams['city'] ?? null,
				'zone' => $queryParams['zone'] ?? null,
				'active' => $queryParams['active'] ?? null
			];

			$territories = $this->SalesRepService()->GetTerritories($filters);

			return $this->ApiResponse($response, [
				'success' => true,
				'territories' => $territories
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function CreateTerritory(Request $request, Response $response, array $args): Response
	{
		try
		{
			$data = $request->getParsedBody();
			$newTer = $this->SalesRepService()->CreateTerritory($data ?: []);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Territorio comercial creado exitosamente.',
				'territory' => $newTer
			], 201);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function UpdateTerritory(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)$args['id'];
			$data = $request->getParsedBody();
			$updated = $this->SalesRepService()->UpdateTerritory($id, $data ?: []);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Territorio comercial actualizado correctamente.',
				'territory' => $updated
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function ReassignEstablishment(Request $request, Response $response, array $args): Response
	{
		try
		{
			$data = $request->getParsedBody();
			$establishmentId = (int)($data['establishment_id'] ?? 0);
			$newRepId = (int)($data['new_rep_id'] ?? 0);

			$db = DatabaseService::getInstance()->GetDbConnectionRaw();

			if ($newRepId <= 0 && !empty($data['new_sales_rep_name']))
			{
				$stmt = $db->prepare("SELECT id FROM b2b_sales_reps WHERE name = :name");
				$stmt->execute([':name' => trim($data['new_sales_rep_name'])]);
				$newRepId = (int)$stmt->fetchColumn();
			}

			$newTerritoryId = !empty($data['new_territory_id']) ? (int)$data['new_territory_id'] : null;
			if (!$newTerritoryId && !empty($data['new_territory']))
			{
				$stmt = $db->prepare("SELECT id FROM b2b_territories WHERE name = :name");
				$stmt->execute([':name' => trim($data['new_territory'])]);
				$newTerritoryId = (int)$stmt->fetchColumn() ?: null;
			}

			$reason = trim($data['reason'] ?? '');

			if ($establishmentId <= 0 || $newRepId <= 0)
			{
				return $this->GenericErrorResponse($response, 'El establecimiento y el nuevo vendedor son obligatorios.', 400);
			}

			$result = $this->SalesRepService()->ReassignEstablishment($establishmentId, $newRepId, $newTerritoryId, $reason);

			return $this->ApiResponse($response, $result);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetReassignmentHistory(Request $request, Response $response, array $args): Response
	{
		try
		{
			$limit = isset($request->getQueryParams()['limit']) ? (int)$request->getQueryParams()['limit'] : 30;
			$history = $this->SalesRepService()->GetReassignmentHistory($limit);

			return $this->ApiResponse($response, [
				'success' => true,
				'history' => $history
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}
}
