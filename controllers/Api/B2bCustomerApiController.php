<?php

namespace Grocy\Controllers\Api;

use Exception;
use Grocy\Services\B2bCustomerService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * API RESTful para Gestión B2B de Clientes Comerciales y Establecimientos
 */
class B2bCustomerApiController extends BaseApiController
{
	private function CustomerService(): B2bCustomerService
	{
		return B2bCustomerService::GetInstance();
	}

	public function GetCustomers(Request $request, Response $response, array $args): Response
	{
		try
		{
			$queryParams = $request->getQueryParams();
			$filters = [
				'search' => $queryParams['search'] ?? null,
				'type' => $queryParams['type'] ?? null,
				'active' => $queryParams['active'] ?? null
			];

			$service = $this->CustomerService();
			$metrics = $service->GetMetrics();
			$customers = $service->GetCustomers($filters);

			return $this->ApiResponse($response, [
				'success' => true,
				'metrics' => $metrics,
				'customers' => $customers
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetCustomer(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$customer = $this->CustomerService()->GetCustomer($id);

			if (!$customer)
			{
				return $this->GenericErrorResponse($response, 'Cliente no encontrado', 404);
			}

			return $this->ApiResponse($response, [
				'success' => true,
				'customer' => $customer
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function CreateCustomer(Request $request, Response $response, array $args): Response
	{
		try
		{
			$body = (array)$request->getParsedBody();
			$created = $this->CustomerService()->CreateCustomer($body);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Cliente comercial registrado exitosamente',
				'customer' => $created
			], 201);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function UpdateCustomer(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$body = (array)$request->getParsedBody();
			$updated = $this->CustomerService()->UpdateCustomer($id, $body);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Cliente comercial actualizado exitosamente',
				'customer' => $updated
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function ToggleCustomerActive(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$body = (array)$request->getParsedBody();
			$newStatus = isset($body['active']) ? (bool)$body['active'] : null;

			$customer = $this->CustomerService()->ToggleCustomerActive($id, $newStatus);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Estado del cliente comercial actualizado',
				'customer' => $customer
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function CreateEstablishment(Request $request, Response $response, array $args): Response
	{
		try
		{
			$body = (array)$request->getParsedBody();
			$created = $this->CustomerService()->CreateEstablishment($body);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Establecimiento registrado exitosamente',
				'establishment' => $created
			], 201);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function UpdateEstablishment(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$body = (array)$request->getParsedBody();
			$updated = $this->CustomerService()->UpdateEstablishment($id, $body);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Establecimiento actualizado exitosamente',
				'establishment' => $updated
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function ToggleEstablishmentActive(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)($args['id'] ?? 0);
			$body = (array)$request->getParsedBody();
			$newStatus = isset($body['active']) ? (bool)$body['active'] : null;

			$est = $this->CustomerService()->ToggleEstablishmentActive($id, $newStatus);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => 'Estado del establecimiento actualizado',
				'establishment' => $est
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}
}
