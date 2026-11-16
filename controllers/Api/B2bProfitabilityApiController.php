<?php

namespace Grocy\Controllers\Api;

use Exception;
use Grocy\Services\B2bProfitabilityService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controlador API RESTful para Contabilidad de Gestión y Rentabilidad Comercial B2B (Paso 10)
 */
class B2bProfitabilityApiController extends BaseApiController
{
	private function ProfitabilityService(): B2bProfitabilityService
	{
		return B2bProfitabilityService::GetInstance();
	}

	private function ExtractFilters(Request $request): array
	{
		$q = $request->getQueryParams();
		return [
			'period' => $q['period'] ?? 'MTD',
			'from_date' => $q['from_date'] ?? null,
			'to_date' => $q['to_date'] ?? null,
			'customer_id' => !empty($q['customer_id']) && $q['customer_id'] !== 'ALL' ? (int)$q['customer_id'] : null,
			'establishment_id' => !empty($q['establishment_id']) && $q['establishment_id'] !== 'ALL' ? (int)$q['establishment_id'] : null,
			'sales_rep' => !empty($q['sales_rep']) && $q['sales_rep'] !== 'ALL' ? trim($q['sales_rep']) : null,
			'territory' => !empty($q['territory']) && $q['territory'] !== 'ALL' ? trim($q['territory']) : null,
			'product_id' => !empty($q['product_id']) && $q['product_id'] !== 'ALL' ? (int)$q['product_id'] : null,
			'category' => !empty($q['category']) && $q['category'] !== 'ALL' ? trim($q['category']) : null
		];
	}

	public function GetMetrics(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$kpis = $this->ProfitabilityService()->GetExecutiveKPIs($filters);

			return $this->ApiResponse($response, [
				'success' => true,
				'metrics' => $kpis
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetProductsProfitability(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$products = $this->ProfitabilityService()->GetProfitabilityByProduct($filters);

			return $this->ApiResponse($response, [
				'success' => true,
				'products' => $products
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetCustomersProfitability(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$customers = $this->ProfitabilityService()->GetProfitabilityByCustomer($filters);

			return $this->ApiResponse($response, [
				'success' => true,
				'customers' => $customers
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetSalesRepsProfitability(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$reps = $this->ProfitabilityService()->GetProfitabilityBySalesRep($filters);

			return $this->ApiResponse($response, [
				'success' => true,
				'sales_reps' => $reps
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetTerritoriesProfitability(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$territories = $this->ProfitabilityService()->GetProfitabilityByTerritory($filters);

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

	public function GetCategoriesProfitability(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$categories = $this->ProfitabilityService()->GetProfitabilityByCategory($filters);

			return $this->ApiResponse($response, [
				'success' => true,
				'categories' => $categories
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetProfitabilityTrend(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$trend = $this->ProfitabilityService()->GetProfitabilityTrend($filters);

			return $this->ApiResponse($response, [
				'success' => true,
				'trend' => $trend
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetCostsCatalog(Request $request, Response $response, array $args): Response
	{
		try
		{
			$catalog = $this->ProfitabilityService()->GetProductCostsCatalog();
			$adjustments = $this->ProfitabilityService()->GetCostAdjustmentsHistory();

			return $this->ApiResponse($response, [
				'success' => true,
				'costs' => $catalog,
				'adjustments' => $adjustments
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function AdjustCost(Request $request, Response $response, array $args): Response
	{
		try
		{
			$data = $request->getParsedBody();
			$productId = (int)($data['product_id'] ?? 0);
			$newUnitCost = (float)($data['new_unit_cost'] ?? -1);
			$reason = trim($data['reason'] ?? '');
			$authorizedBy = trim($data['authorized_by'] ?? 'Auditoría Comercial B2B');
			$effectiveDate = !empty($data['effective_date']) ? trim($data['effective_date']) : date('Y-m-d');

			if ($productId <= 0)
			{
				return $this->GenericErrorResponse($response, 'El producto es obligatorio.', 400);
			}
			if ($newUnitCost < 0)
			{
				return $this->GenericErrorResponse($response, 'El costo unitario debe ser mayor o igual a 0.00.', 400);
			}
			if (empty($reason))
			{
				return $this->GenericErrorResponse($response, 'El motivo del ajuste de costo es obligatorio para auditoría.', 400);
			}

			$result = $this->ProfitabilityService()->AdjustProductCost(
				$productId,
				$newUnitCost,
				$reason,
				$authorizedBy,
				$effectiveDate
			);

			return $this->ApiResponse($response, $result);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}
}
