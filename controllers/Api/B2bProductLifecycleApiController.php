<?php

namespace Grocy\Controllers\Api;

use Exception;
use Grocy\Services\B2bProductLifecycleService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controlador API RESTful para Gestión del Ciclo de Vida del Producto e Inteligencia Comercial
 * Prince Distributors Wholesale B2B Suite
 */
class B2bProductLifecycleApiController extends BaseApiController
{
	private function LifecycleService(): B2bProductLifecycleService
	{
		return B2bProductLifecycleService::GetInstance();
	}

	private function ExtractFilters(Request $request): array
	{
		$q = $request->getQueryParams();
		return [
			'period' => $q['period'] ?? 'MTD',
			'category' => $q['category'] ?? 'ALL',
			'stage' => $q['stage'] ?? 'ALL',
			'branch' => $q['branch'] ?? 'ALL',
			'search' => $q['search'] ?? '',
			'status' => $q['status'] ?? 'ALL',
			'priority' => $q['priority'] ?? 'ALL'
		];
	}

	public function GetKpis(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$kpis = $this->LifecycleService()->GetLifecycleKPIs($filters);
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

	public function GetProducts(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$products = $this->LifecycleService()->GetProductsLifecycle($filters);
			return $this->ApiResponse($response, [
				'success' => true,
				'count' => count($products),
				'products' => $products
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetDistribution(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$data = $this->LifecycleService()->GetPortfolioDistribution($filters);
			return $this->ApiResponse($response, [
				'success' => true,
				'distribution' => $data
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetCurves(Request $request, Response $response, array $args): Response
	{
		try
		{
			$data = $this->LifecycleService()->GetLifecycleCurveData();
			return $this->ApiResponse($response, [
				'success' => true,
				'curves' => $data
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetMatrix(Request $request, Response $response, array $args): Response
	{
		try
		{
			$data = $this->LifecycleService()->GetProfitabilityRotationMatrix();
			return $this->ApiResponse($response, [
				'success' => true,
				'matrix' => $data
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetImmobilized(Request $request, Response $response, array $args): Response
	{
		try
		{
			$data = $this->LifecycleService()->GetImmobilizedCapitalBreakdown();
			return $this->ApiResponse($response, [
				'success' => true,
				'immobilized' => $data
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetDemandTrend(Request $request, Response $response, array $args): Response
	{
		try
		{
			$data = $this->LifecycleService()->GetDemandTrendComparison();
			return $this->ApiResponse($response, [
				'success' => true,
				'trend' => $data
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetRecommendations(Request $request, Response $response, array $args): Response
	{
		try
		{
			$filters = $this->ExtractFilters($request);
			$data = $this->LifecycleService()->GetRecommendations($filters);
			return $this->ApiResponse($response, [
				'success' => true,
				'count' => count($data),
				'recommendations' => $data
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function ReviewRecommendation(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$body = $request->getParsedBody() ?? [];
			$status = trim($body['status'] ?? 'approved');
			$by = trim($body['reviewed_by'] ?? 'Carlos Mendoza (Dir. Ventas)');
			$notes = trim($body['notes'] ?? 'Aprobado según políticas comerciales de Prince Distributors');

			$result = $this->LifecycleService()->ReviewRecommendation($id, $status, $by, $notes);
			return $this->ApiResponse($response, $result);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function SimulateMarkdown(Request $request, Response $response, array $args): Response
	{
		try
		{
			$body = $request->getParsedBody() ?? [];
			$productId = (int)($body['product_id'] ?? 1);
			$discountPct = (float)($body['discount_pct'] ?? 15.0);
			$elasticity = (float)($body['elasticity'] ?? 1.4);

			$result = $this->LifecycleService()->SimulateMarkdown($productId, $discountPct, $elasticity);
			return $this->ApiResponse($response, [
				'success' => true,
				'simulation' => $result
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function SaveMarkdownScenario(Request $request, Response $response, array $args): Response
	{
		try
		{
			$body = $request->getParsedBody() ?? [];
			$result = $this->LifecycleService()->SaveMarkdownScenario($body);
			return $this->ApiResponse($response, $result);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function ImportData(Request $request, Response $response, array $args): Response
	{
		try
		{
			$body = $request->getParsedBody() ?? [];
			$filename = trim($body['filename'] ?? 'import_portafolio_' . date('Ymd_His') . '.csv');
			$rows = $body['rows'] ?? [];
			$mapping = $body['column_mapping'] ?? [
				'sku' => 'sku',
				'name' => 'name',
				'category' => 'category',
				'brand' => 'brand',
				'sales_units' => 'sales_units',
				'sales_amount' => 'sales_amount',
				'cogs' => 'cogs',
				'current_stock' => 'stock'
			];

			if (empty($rows))
			{
				return $this->GenericErrorResponse($response, 'No se proporcionaron filas para importar.', 400);
			}

			$result = $this->LifecycleService()->ProcessDataImport($filename, $rows, $mapping);
			return $this->ApiResponse($response, $result);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetImportHistory(Request $request, Response $response, array $args): Response
	{
		try
		{
			$history = $this->LifecycleService()->GetImportHistory();
			return $this->ApiResponse($response, [
				'success' => true,
				'history' => $history
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}
}
