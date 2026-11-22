<?php

namespace Grocy\Controllers\Api;

use Grocy\Services\B2bWorkforceService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class B2bWorkforceApiController extends BaseApiController
{
	private ?B2bWorkforceService $wfmService = null;

	private function WfmService(): B2bWorkforceService
	{
		if ($this->wfmService === null) {
			$this->wfmService = new B2bWorkforceService();
		}
		return $this->wfmService;
	}

	protected function JsonResponse(Response $response, array $data, int $status = 200): Response
	{
		$response = $response->withStatus($status)->withHeader('Content-Type', 'application/json');
		$response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
		return $response;
	}

	public function GetDashboard(Request $request, Response $response, array $args): Response
	{
		try {
			$params = $request->getQueryParams();
			$data = $this->WfmService()->GetDashboardKPIs($params);
			return $this->JsonResponse($response, [
				'success' => true,
				...$data
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetSchedule(Request $request, Response $response, array $args): Response
	{
		try {
			$params = $request->getQueryParams();
			$data = $this->WfmService()->GetWeeklySchedule($params);
			return $this->JsonResponse($response, [
				'success' => true,
				...$data
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetDailySchedule(Request $request, Response $response, array $args): Response
	{
		try {
			$params = $request->getQueryParams();
			$data = $this->WfmService()->GetDailySchedule($params);
			return $this->JsonResponse($response, [
				'success' => true,
				...$data
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetMonthlySchedule(Request $request, Response $response, array $args): Response
	{
		try {
			$params = $request->getQueryParams();
			$data = $this->WfmService()->GetMonthlySchedule($params);
			return $this->JsonResponse($response, [
				'success' => true,
				...$data
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetActiveShift(Request $request, Response $response, array $args): Response
	{
		try {
			$params = $request->getQueryParams();
			$empId = (int)($params['employee_id'] ?? 2);
			$data = $this->WfmService()->GetActiveShiftForEmployee($empId);
			return $this->JsonResponse($response, [
				'success' => true,
				'shift' => $data
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function SaveShift(Request $request, Response $response, array $args): Response
	{
		try {
			$body = (array)($request->getParsedBody() ?? []);
			$shift = $this->WfmService()->AddShift($body);
			return $this->JsonResponse($response, [
				'success' => true,
				'message' => 'Turno guardado correctamente.',
				'shift' => $shift
			], 201);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function DeleteShift(Request $request, Response $response, array $args): Response
	{
		try {
			$id = (int)$args['id'];
			$res = $this->WfmService()->DeleteShift($id);
			return $this->JsonResponse($response, [
				'success' => $res,
				'message' => 'Turno eliminado correctamente.'
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function MoveShift(Request $request, Response $response, array $args): Response
	{
		try {
			$id = (int)$args['id'];
			$body = (array)($request->getParsedBody() ?? []);
			$newEmpId = (int)($body['new_employee_id'] ?? 0);
			$newDow = (int)($body['new_day_of_week'] ?? 1);

			$res = $this->WfmService()->MoveShift($id, $newEmpId, $newDow);
			return $this->JsonResponse($response, [
				'success' => true,
				'message' => 'Turno movido exitosamente.',
				...$res
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function DuplicateShift(Request $request, Response $response, array $args): Response
	{
		try {
			$id = (int)$args['id'];
			$db = \Grocy\Services\DatabaseService::GetInstance()->GetDbConnectionRaw();
			$stmt = $db->prepare("SELECT * FROM wfm_shifts WHERE id = :id");
			$stmt->execute([':id' => $id]);
			$source = $stmt->fetch(\PDO::FETCH_ASSOC);

			if (!$source) {
				return $this->GenericErrorResponse($response, "Turno no encontrado", 404);
			}

			$targetDow = min(7, ((int)$source['day_of_week']) + 1);
			$newShift = $this->WfmService()->AddShift([
				'schedule_id' => $source['schedule_id'],
				'employee_id' => $source['employee_id'],
				'day_of_week' => $targetDow,
				'shift_date' => date('Y-m-d', strtotime($source['shift_date'] . ' +1 day')),
				'start_time' => $source['start_time'],
				'end_time' => $source['end_time'],
				'break_minutes' => $source['break_minutes'],
				'shift_type' => $source['shift_type'],
				'shift_label' => $source['shift_label'],
				'color_hex' => $source['color_hex']
			]);

			return $this->JsonResponse($response, [
				'success' => true,
				'message' => 'Turno duplicado al siguiente día.',
				'shift' => $newShift
			], 201);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetRecommendations(Request $request, Response $response, array $args): Response
	{
		try {
			$body = (array)($request->getParsedBody() ?? []);
			$data = $this->WfmService()->CalculateLaborRecommendation($body);
			return $this->JsonResponse($response, [
				'success' => true,
				...$data
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function RunSimulation(Request $request, Response $response, array $args): Response
	{
		try {
			$body = (array)($request->getParsedBody() ?? []);
			$res = $this->WfmService()->RunWhatIfSimulation($body);
			return $this->JsonResponse($response, [
				'success' => true,
				...$res
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function PublishSchedule(Request $request, Response $response, array $args): Response
	{
		try {
			$id = (int)$args['id'];
			$res = $this->WfmService()->ValidateAndPublish($id);
			return $this->JsonResponse($response, [
				'success' => true,
				'message' => 'Programación semanal publicada y notificada a los colaboradores.',
				...$res
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetEmployees(Request $request, Response $response, array $args): Response
	{
		try {
			$params = $request->getQueryParams();
			$employees = $this->WfmService()->GetEmployees($params);
			return $this->JsonResponse($response, [
				'success' => true,
				'employees' => $employees,
				'total' => count($employees)
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function GetRequests(Request $request, Response $response, array $args): Response
	{
		try {
			$reqs = $this->WfmService()->GetShiftRequests();
			return $this->JsonResponse($response, [
				'success' => true,
				'requests' => $reqs,
				'total' => count($reqs)
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function ReviewRequest(Request $request, Response $response, array $args): Response
	{
		try {
			$id = (int)$args['id'];
			$body = (array)($request->getParsedBody() ?? []);
			$decision = $body['decision'] ?? 'approved';
			$res = $this->WfmService()->ReviewShiftRequest($id, $decision);
			return $this->JsonResponse($response, [
				'success' => $res,
				'message' => "Solicitud #$id procesada con decisión: $decision"
			]);
		} catch (Exception $ex) {
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}
}
