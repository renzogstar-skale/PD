<?php

namespace Grocy\Controllers\Api;

use Exception;
use Grocy\Services\B2bInvoiceService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * API RESTful para Facturación Comercial, Pagos y Notas de Crédito B2B
 */
class B2bInvoiceApiController extends BaseApiController
{
	private function InvoiceService(): B2bInvoiceService
	{
		return B2bInvoiceService::GetInstance();
	}

	public function GetMetrics(Request $request, Response $response, array $args): Response
	{
		try
		{
			$metrics = $this->InvoiceService()->GetMetrics();
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

	public function GetInvoices(Request $request, Response $response, array $args): Response
	{
		try
		{
			$queryParams = $request->getQueryParams();
			$filters = [
				'search' => $queryParams['search'] ?? null,
				'status' => $queryParams['status'] ?? null,
				'customer_id' => $queryParams['customer_id'] ?? null,
				'overdue_only' => !empty($queryParams['overdue_only']),
				'from_date' => $queryParams['from_date'] ?? null,
				'to_date' => $queryParams['to_date'] ?? null
			];

			$invoices = $this->InvoiceService()->GetInvoices($filters);
			$metrics = $this->InvoiceService()->GetMetrics();

			return $this->ApiResponse($response, [
				'success' => true,
				'invoices' => $invoices,
				'metrics' => $metrics
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetInvoice(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)$args['id'];
			$invoice = $this->InvoiceService()->GetInvoice($id);

			if (!$invoice)
			{
				return $this->GenericErrorResponse($response, "Factura comercial #{$id} no encontrada.", 404);
			}

			return $this->ApiResponse($response, [
				'success' => true,
				'invoice' => $invoice
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function CreateInvoiceFromOrder(Request $request, Response $response, array $args): Response
	{
		try
		{
			$orderId = (int)$args['orderId'];
			$data = (array)($request->getParsedBody() ?? []);
			$user = 'Carlos Mendoza';

			$invoice = $this->InvoiceService()->CreateInvoiceFromOrder($orderId, $data, $user);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => "Factura {$invoice['invoice_number']} emitida exitosamente desde el pedido comercial.",
				'invoice' => $invoice
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function UpdateInvoiceStatus(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)$args['id'];
			$data = (array)($request->getParsedBody() ?? []);
			$status = trim($data['status'] ?? '');
			$comment = trim($data['comment'] ?? '');
			$user = 'Carlos Mendoza';

			if (empty($status))
			{
				return $this->GenericErrorResponse($response, 'El nuevo estado de la factura es obligatorio.', 400);
			}

			$invoice = $this->InvoiceService()->UpdateInvoiceStatus($id, $status, $user, $comment);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => "Factura {$invoice['invoice_number']} actualizada a '{$status}'.",
				'invoice' => $invoice
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function RegisterPayment(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)$args['id'];
			$data = (array)($request->getParsedBody() ?? []);
			$user = 'Carlos Mendoza';

			$invoice = $this->InvoiceService()->RegisterPayment($id, $data, $user);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => "Pago registrado correctamente en la factura {$invoice['invoice_number']}.",
				'invoice' => $invoice
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function CreateCreditNote(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)$args['id'];
			$data = (array)($request->getParsedBody() ?? []);
			$user = 'Carlos Mendoza';

			$result = $this->InvoiceService()->CreateCreditNote($id, $data, $user);

			return $this->ApiResponse($response, [
				'success' => true,
				'message' => "Nota de Crédito {$result['credit_note']['credit_note_number']} emitida exitosamente.",
				'credit_note' => $result['credit_note'],
				'invoice' => $result['invoice']
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetCreditNotes(Request $request, Response $response, array $args): Response
	{
		try
		{
			$queryParams = $request->getQueryParams();
			$creditNotes = $this->InvoiceService()->GetCreditNotes($queryParams);

			return $this->ApiResponse($response, [
				'success' => true,
				'credit_notes' => $creditNotes
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetCreditNote(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)$args['id'];
			$creditNote = $this->InvoiceService()->GetCreditNote($id);

			if (!$creditNote)
			{
				return $this->GenericErrorResponse($response, "Nota de crédito #{$id} no encontrada.", 404);
			}

			return $this->ApiResponse($response, [
				'success' => true,
				'credit_note' => $creditNote
			]);
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function PrintInvoicePdf(Request $request, Response $response, array $args): Response
	{
		try
		{
			$id = (int)$args['id'];
			$html = $this->InvoiceService()->RenderInvoicePrintHtml($id);
			$response->getBody()->write($html);
			return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
		}
		catch (Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 404);
		}
	}
}
