<?php

namespace Grocy\Controllers\Api;

use Exception;
use Grocy\Services\B2bProductService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * API RESTful para Catálogo Comercial y Motor de Precios Mayoristas B2B
 */
class B2bProductApiController extends BaseApiController
{
	private function ProductService(): B2bProductService
	{
		return B2bProductService::GetInstance();
	}

	public function GetProducts(Request $request, Response $response, array $args): Response
	{
		try
		{
			$queryParams = $request->getQueryParams();
			$filters = [
				'search' => $queryParams['search'] ?? null,
				'category' => $queryParams['category'] ?? null,
				'brand' => $queryParams['brand'] ?? null,
				'active' => $queryParams['active'] ?? null
			];

			$service = $this->ProductService();
			$metrics = $service->GetMetrics();
			$products = $service->GetProducts($filters);
			$options = $service->GetFilterOptions();

			return $this->ApiResponse($response, [
				'success' => true,
				'metrics' => $metrics,
				'products' => $products,
				'filter_options' => $options
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetProduct(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$product = $this->ProductService()->GetProduct($id);

			if (!$product)
			{
				return $this->GenericErrorResponse($response, 'Producto no encontrado', 404);
			}

			return $this->ApiResponse($response, [
				'success' => true,
				'product' => $product
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function CreateProduct(Request $request, Response $response, array $args): Response
	{
		try
		{
			$body = (array)$request->getParsedBody();
			$created = $this->ProductService()->CreateProduct($body);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Producto registrado exitosamente en el catálogo B2B',
				'product' => $created
			], 201);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function UpdateProduct(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$body = (array)$request->getParsedBody();
			$updated = $this->ProductService()->UpdateProduct($id, $body);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Producto comercial actualizado correctamente',
				'product' => $updated
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function ToggleProductActive(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$body = (array)$request->getParsedBody();
			$newStatus = isset($body['active']) ? (bool)$body['active'] : null;

			$product = $this->ProductService()->ToggleProductActive($id, $newStatus);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Estado del producto comercial actualizado',
				'product' => $product
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function CalculatePrice(Request $request, Response $response, array $args): Response
	{
		try
		{
			$body = (array)$request->getParsedBody();
			$productId = (int)($body['product_id'] ?? 0);
			$customerId = !empty($body['customer_id']) ? (int)$body['customer_id'] : null;
			$unit = (string)($body['unit'] ?? 'CASE');
			$quantity = (int)($body['quantity'] ?? 1);

			if ($productId <= 0)
			{
				return $this->GenericErrorResponse($response, 'El parámetro product_id es requerido.', 400);
			}

			$calculation = $this->ProductService()->CalculatePrice($productId, $customerId, $unit, $quantity);

			return $this->ApiResponse($response, [
				'success' => true,
				'calculation' => $calculation
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function AddVolumeTier(Request $request, Response $response, array $args): Response
	{
		try
		{
			$productId = (int)($args['id'] ?? 0);
			$body = (array)$request->getParsedBody();
			$minQty = (int)($body['min_quantity'] ?? 1);
			$priceCase = (float)($body['price_per_case'] ?? 0);
			$discountPct = isset($body['discount_percent']) ? (float)$body['discount_percent'] : null;

			if ($minQty <= 0 || $priceCase <= 0)
			{
				return $this->GenericErrorResponse($response, 'Cantidad mínima y precio por caja deben ser mayores a 0.', 400);
			}

			$product = $this->ProductService()->AddVolumeTier($productId, $minQty, $priceCase, $discountPct);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Escala de volumen configurada exitosamente',
				'product' => $product
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function AddCustomerPrice(Request $request, Response $response, array $args): Response
	{
		try
		{
			$productId = (int)($args['id'] ?? 0);
			$body = (array)$request->getParsedBody();
			$customerId = (int)($body['customer_id'] ?? 0);
			$priceCase = (float)($body['price_per_case'] ?? 0);
			$priceUnit = isset($body['price_per_unit']) && $body['price_per_unit'] !== '' ? (float)$body['price_per_unit'] : null;
			$notes = (string)($body['notes'] ?? '');

			if ($customerId <= 0 || $priceCase <= 0)
			{
				return $this->GenericErrorResponse($response, 'Debe especificar un cliente válido y precio por caja mayor a 0.', 400);
			}

			$product = $this->ProductService()->AddCustomerPrice($productId, $customerId, $priceCase, $priceUnit, $notes);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Acuerdo comercial de cliente configurado exitosamente',
				'product' => $product
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}
}
