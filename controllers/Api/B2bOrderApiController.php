<?php

namespace Grocy\Controllers\Api;

use Exception;
use Grocy\Services\B2bOrderService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * API RESTful para Cotizaciones y Pedidos Comerciales B2B
 */
class B2bOrderApiController extends BaseApiController
{
	private function OrderService(): B2bOrderService
	{
		return B2bOrderService::GetInstance();
	}

	public function GetMetrics(Request $request, Response $response, array $args): Response
	{
		try
		{
			$metrics = $this->OrderService()->GetMetrics();
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

	public function GetQuotes(Request $request, Response $response, array $args): Response
	{
		try
		{
			$queryParams = $request->getQueryParams();
			$filters = [
				'search' => $queryParams['search'] ?? null,
				'status' => $queryParams['status'] ?? null,
				'customer_id' => $queryParams['customer_id'] ?? null
			];

			$quotes = $this->OrderService()->GetQuotes($filters);
			$metrics = $this->OrderService()->GetMetrics();

			return $this->ApiResponse($response, [
				'success' => true,
				'quotes' => $quotes,
				'metrics' => $metrics
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetQuote(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$quote = $this->OrderService()->GetQuote($id);

			if (!$quote)
			{
				return $this->GenericErrorResponse($response, 'Cotización no encontrada', 404);
			}

			return $this->ApiResponse($response, [
				'success' => true,
				'quote' => $quote
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function CreateQuote(Request $request, Response $response, array $args): Response
	{
		try
		{
			$body = (array)$request->getParsedBody();
			$created = $this->OrderService()->CreateQuote($body);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Cotización comercial registrada exitosamente',
				'quote' => $created
			], 201);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function UpdateQuoteStatus(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$body = (array)$request->getParsedBody();
			$status = (string)($body['status'] ?? '');
			$comment = isset($body['comment']) ? (string)$body['comment'] : null;

			$updated = $this->OrderService()->UpdateQuoteStatus($id, $status, 'Carlos Mendoza', $comment);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => "Estado de cotización actualizado a {$status}",
				'quote' => $updated
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function ConvertQuoteToOrder(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$order = $this->OrderService()->ConvertQuoteToOrder($id);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => "Cotización convertida exitosamente en pedido {$order['order_number']}",
				'order' => $order
			], 201);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetOrders(Request $request, Response $response, array $args): Response
	{
		try
		{
			$queryParams = $request->getQueryParams();
			$filters = [
				'search' => $queryParams['search'] ?? null,
				'status' => $queryParams['status'] ?? null,
				'customer_id' => $queryParams['customer_id'] ?? null
			];

			$orders = $this->OrderService()->GetOrders($filters);
			$metrics = $this->OrderService()->GetMetrics();

			return $this->ApiResponse($response, [
				'success' => true,
				'orders' => $orders,
				'metrics' => $metrics
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetOrder(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$order = $this->OrderService()->GetOrder($id);

			if (!$order)
			{
				return $this->GenericErrorResponse($response, 'Pedido comercial no encontrado', 404);
			}

			return $this->ApiResponse($response, [
				'success' => true,
				'order' => $order
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function CreateOrder(Request $request, Response $response, array $args): Response
	{
		try
		{
			$body = (array)$request->getParsedBody();
			$created = $this->OrderService()->CreateOrder($body);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => "Pedido comercial {$created['order_number']} registrado exitosamente",
				'order' => $created
			], 201);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function UpdateOrderStatus(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$body = (array)$request->getParsedBody();
			$status = (string)($body['status'] ?? '');
			$comment = isset($body['comment']) ? (string)$body['comment'] : null;

			$updated = $this->OrderService()->UpdateOrderStatus($id, $status, 'Carlos Mendoza', $comment);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => "Estado de pedido actualizado a {$status}",
				'order' => $updated
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function DuplicateOrder(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$newOrder = $this->OrderService()->DuplicateOrder($id);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => "Pedido duplicado exitosamente bajo la orden {$newOrder['order_number']}",
				'order' => $newOrder
			], 201);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function CalculateLines(Request $request, Response $response, array $args): Response
	{
		try
		{
			$body = (array)$request->getParsedBody();
			$customerId = (int)($body['customer_id'] ?? 0);
			$items = (array)($body['items'] ?? []);

			if ($customerId <= 0)
			{
				return $this->GenericErrorResponse($response, 'El customer_id es obligatorio para calcular precios.', 400);
			}

			$calculated = $this->OrderService()->CalculateCommercialLines($customerId, $items);

			return $this->ApiResponse($response, [
				'success' => true,
				'calculation' => $calculated
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}
}
