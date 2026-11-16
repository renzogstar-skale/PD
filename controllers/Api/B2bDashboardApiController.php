<?php

namespace Grocy\Controllers\Api;

use Exception;
use Grocy\Services\B2bDashboardService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controlador API RESTful para el Tablero Ejecutivo de Ventas B2B (Paso 8)
 */
class B2bDashboardApiController extends BaseApiController
{
	private function DashboardService(): B2bDashboardService
	{
		return B2bDashboardService::GetInstance();
	}

	/**
	 * Retorna las métricas ejecutivas, series de tiempo, distribuciones y rankings
	 */
	public function GetExecutiveDashboard(Request $request, Response $response, array $args): Response
	{
		try
		{
			$queryParams = $request->getQueryParams();
			$filters = [
				'period' => $queryParams['period'] ?? 'MTD',
				'from_date' => $queryParams['from_date'] ?? null,
				'to_date' => $queryParams['to_date'] ?? null,
				'customer_id' => $queryParams['customer_id'] ?? null,
				'establishment_id' => $queryParams['establishment_id'] ?? null,
				'category_id' => $queryParams['category_id'] ?? null,
				'status' => $queryParams['status'] ?? null,
				'granularity' => $queryParams['granularity'] ?? 'day'
			];

			$data = $this->DashboardService()->GetExecutiveDashboard($filters);

			return $this->ApiResponse($response, $data);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	/**
	 * Retorna las opciones disponibles para los filtros globales (clientes, sedes, categorías, estados)
	 */
	public function GetFilterOptions(Request $request, Response $response, array $args): Response
	{
		try
		{
			$options = $this->DashboardService()->GetFilterOptions();

			return $this->ApiResponse($response, [
				'success' => true,
				'filters' => $options
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}
}
