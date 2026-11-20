<?php

namespace Grocy\Controllers\Api;

use Exception;
use Grocy\Services\B2bAnalyticsService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * API RESTful Unificada para Dashboards Ejecutivos y Analítica B2B
 * Prince Distributors Wholesale B2B Suite
 */
class B2bAnalyticsApiController extends BaseApiController
{
	private function AnalyticsService(): B2bAnalyticsService
	{
		return B2bAnalyticsService::GetInstance();
	}

	public function GetAnalytics(Request $request, Response $response, array $args): Response
	{
		try
		{
			$module = strtolower(trim($args['module'] ?? ''));
			$queryParams = $request->getQueryParams();

			$filters = [
				'period' => $queryParams['period'] ?? 'MTD',
				'from_date' => $queryParams['from_date'] ?? null,
				'to_date' => $queryParams['to_date'] ?? null,
				'territory' => $queryParams['territory'] ?? null,
				'sales_rep' => $queryParams['sales_rep'] ?? null,
				'branch_id' => $queryParams['branch_id'] ?? null,
				'category' => $queryParams['category'] ?? null,
				'status' => $queryParams['status'] ?? null
			];

			$service = $this->AnalyticsService();

			switch ($module)
			{
				case 'customers':
					$data = $service->GetCustomersAnalytics($filters);
					break;

				case 'products':
					$data = $service->GetProductsAnalytics($filters);
					break;

				case 'orders':
					$data = $service->GetOrdersAnalytics($filters);
					break;

				case 'invoices':
					$data = $service->GetInvoicesAnalytics($filters);
					break;

				case 'sales-reps':
				case 'reps':
					$data = $service->GetSalesRepsAnalytics($filters);
					break;

				case 'profitability':
					$data = $service->GetProfitabilityAnalytics($filters);
					break;

				case 'reports':
				case 'intelligence':
					$data = $service->GetReportsAnalytics($filters);
					break;

				case 'schedules':
				case 'wfm':
					$data = $service->GetSchedulesAnalytics($filters);
					break;

				case 'operations':
				case 'tasks':
					$data = $service->GetOperationsAnalytics($filters);
					break;

				case 'employees':
					$data = $service->GetEmployeesAnalytics($filters);
					break;

				case 'attendance':
					$data = $service->GetAttendanceAnalytics($filters);
					break;

				case 'shift-requests':
				case 'requests':
					$data = $service->GetShiftRequestsAnalytics($filters);
					break;

				default:
					return $this->GenericErrorResponse($response, "Módulo de analítica '{$module}' no reconocido", 404);
			}

			return $this->ApiResponse($response, array_merge([
				'success' => true,
				'module' => $module
			], $data));
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 500);
		}
	}
}
