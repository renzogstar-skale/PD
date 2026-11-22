<?php

namespace Grocy\Controllers\Api;

use Exception;
use Grocy\Services\B2bIntelligenceService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controlador API RESTful para Inteligencia Comercial B2B (Paso 11)
 * Alertas, Clientes Inactivos, Oportunidades y Recomendaciones Explicables.
 */
class B2bIntelligenceApiController extends BaseApiController
{
	private function IntelligenceService(): B2bIntelligenceService
	{
		return B2bIntelligenceService::GetInstance();
	}

	private function ExtractFilters(Request $request): array
	{
		$q = $request->getQueryParams();
		return [
			'status' => $q['status'] ?? 'ALL',
			'severity' => $q['severity'] ?? 'ALL',
			'alert_type' => $q['alert_type'] ?? 'ALL',
			'sales_rep' => $q['sales_rep'] ?? '',
			'territory' => $q['territory'] ?? ''
		];
	}

	public function GetKpis(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$kpis = $this->IntelligenceService()->GetExecutiveKPIs($filters);
			return $this->ApiResponse($response, [
				'success' => true,
				'kpis' => $kpis
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetAlerts(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$alerts = $this->IntelligenceService()->GetAlerts($filters);
			return $this->ApiResponse($response, [
				'success' => true,
				'count' => count($alerts),
				'alerts' => $alerts
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function ReviewAlert(Request $request, Response $response, array $args): Response
	{
		try
		{
			$data = $request->getParsedBody();
			$alertId = (int)($data['alert_id'] ?? 0);
			$reviewedBy = trim($data['reviewed_by'] ?? 'Dirección Comercial');
			$notes = trim($data['notes'] ?? 'Revisado y accionado por el equipo comercial');

			if ($alertId <= 0)
			{
				return $this->GenericErrorResponse($response, 'El ID de alerta es obligatorio.', 400);
			}

			$success = $this->IntelligenceService()->MarkAlertReviewed($alertId, $reviewedBy, $notes);
			return $this->ApiResponse($response, [
				'success' => $success,
				'message' => 'Alerta comercial marcada como revisada exitosamente.',
				'alert_id' => $alertId
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetInactiveCustomers(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$customers = $this->IntelligenceService()->GetInactiveAndAtRiskCustomers($filters);
			return $this->ApiResponse($response, [
				'success' => true,
				'count' => count($customers),
				'customers' => $customers
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetOpportunities(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$opportunities = $this->IntelligenceService()->GetCommercialOpportunities($filters);
			return $this->ApiResponse($response, [
				'success' => true,
				'opportunities' => $opportunities
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetCategoryTrends(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$trends = $this->IntelligenceService()->GetCategoryTrends($filters);
			return $this->ApiResponse($response, [
				'success' => true,
				'categories' => $trends
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetThresholds(Request $request, Response $response, array $args): Response
	{
		try
		{
			$thresholds = $this->IntelligenceService()->GetThresholds();
			return $this->ApiResponse($response, [
				'success' => true,
				'thresholds' => $thresholds
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function UpdateThresholds(Request $request, Response $response, array $args): Response
	{
		try
		{
			$data = $request->getParsedBody();
			$thresholds = $data['thresholds'] ?? [];
			if (empty($thresholds) || !is_array($thresholds))
			{
				return $this->GenericErrorResponse($response, 'Estructura de umbrales inválida.', 400);
			}

			$success = $this->IntelligenceService()->UpdateThresholds($thresholds);
			return $this->ApiResponse($response, [
				'success' => $success,
				'message' => 'Límites de alerta actualizados y alertas recalculadas.'
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function RunEngine(Request $request, Response $response, array $args): Response
	{
		try
		{
			$result = $this->IntelligenceService()->RunIntelligenceEngine();
			return $this->ApiResponse($response, $result);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}
}
