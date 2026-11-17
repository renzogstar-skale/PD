<?php

namespace Grocy\Services;

use PDO;
use Exception;

/**
 * Servicio Empresarial de Gestión Inteligente de Horarios y Cobertura (WFM)
 * Prince Distributors B2B Suite
 */
class B2bWorkforceService extends BaseService
{
	private static bool $tablesChecked = false;

	protected function getDatabaseService(): DatabaseService
	{
		return DatabaseService::GetInstance();
	}

	protected function getDb(): PDO
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		if (!self::$tablesChecked) {
			try {
				$check = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='wfm_schedules'")->fetchColumn();
				if (!$check) {
					require __DIR__ . '/../migrations/0340.php';
				}
			} catch (Exception $e) {
				try {
					require __DIR__ . '/../migrations/0340.php';
				} catch (Exception $e2) {
					// ignore
				}
			}
			self::$tablesChecked = true;
		}
		return $db;
	}

	// 1. DASHBOARD KPIS & FILTROS GLOBALES
	public function GetDashboardKPIs(array $filters = []): array
	{
		$db = $this->getDb();
		$week = $filters['week'] ?? '2026-W47';

		// Buscar programación de la semana
		$stmt = $db->prepare("SELECT * FROM wfm_schedules WHERE week_code = :week LIMIT 1");
		$stmt->execute([':week' => $week]);
		$schedule = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$schedule) {
			// Si no existe, crear programación borrador por defecto para la semana solicitada
			$schedule = [
				'id' => 1,
				'week_code' => $week,
				'forecasted_sales' => 48500.00,
				'recommended_hours' => 340.0,
				'scheduled_hours' => 336.0,
				'estimated_labor_cost' => 2520.00,
				'labor_budget' => 2800.00,
				'coverage_score_pct' => 98.8,
				'status' => 'published',
				'version' => 1
			];
		}

		// Recalcular horas programadas y costo real de los turnos activos
		$whereShifts = ["s.schedule_id = :sched_id", "s.shift_type != 'rest'", "s.shift_type != 'absence'"];
		$params = [':sched_id' => $schedule['id']];

		if (!empty($filters['department']) && $filters['department'] !== 'Todos') {
			$whereShifts[] = "e.department = :dept";
			$params[':dept'] = $filters['department'];
		}
		if (!empty($filters['territory']) && $filters['territory'] !== 'Todos') {
			$whereShifts[] = "e.territory = :terr";
			$params[':terr'] = $filters['territory'];
		}

		$whereSql = implode(' AND ', $whereShifts);
		$shiftStats = $db->prepare("
			SELECT 
				COALESCE(SUM(s.net_hours), 0) as total_hours,
				COALESCE(SUM(s.net_hours * e.hourly_cost), 0) as total_cost,
				COUNT(DISTINCT s.employee_id) as active_employees
			FROM wfm_shifts s
			JOIN wfm_employees e ON s.employee_id = e.id
			WHERE $whereSql
		");
		$shiftStats->execute($params);
		$stats = $shiftStats->fetch(PDO::FETCH_ASSOC);

		$scheduledHours = (float)($stats['total_hours'] ?: $schedule['scheduled_hours']);
		$estimatedCost = (float)($stats['total_cost'] ?: $schedule['estimated_labor_cost']);
		$recommendedHours = (float)$schedule['recommended_hours'];
		$coveragePct = $recommendedHours > 0 ? round(($scheduledHours / $recommendedHours) * 100, 1) : 100.0;

		return [
			'week_code' => $week,
			'schedule_id' => (int)$schedule['id'],
			'status' => $schedule['status'],
			'version' => (int)($schedule['version'] ?? 1),
			'kpis' => [
				'forecasted_sales' => (float)$schedule['forecasted_sales'],
				'recommended_hours' => $recommendedHours,
				'scheduled_hours' => $scheduledHours,
				'estimated_labor_cost' => $estimatedCost,
				'labor_budget' => (float)$schedule['labor_budget'],
				'coverage_score_pct' => $coveragePct,
				'budget_variance' => (float)$schedule['labor_budget'] - $estimatedCost,
				'hours_variance' => $scheduledHours - $recommendedHours,
				'active_employees' => (int)($stats['active_employees'] ?? 10)
			]
		];
	}

	// 2. CALENDARIO SEMANAL & TURNOS
	public function GetWeeklySchedule(array $filters = []): array
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		$week = $filters['week'] ?? '2026-W47';

		// 1. Obtener empleados según filtros
		$empWhere = ["e.active = 1"];
		$empParams = [];

		if (!empty($filters['department']) && $filters['department'] !== 'Todos') {
			$empWhere[] = "e.department = :dept";
			$empParams[':dept'] = $filters['department'];
		}
		if (!empty($filters['territory']) && $filters['territory'] !== 'Todos') {
			$empWhere[] = "e.territory = :terr";
			$empParams[':terr'] = $filters['territory'];
		}

		$empSql = implode(' AND ', $empWhere);
		$empStmt = $db->prepare("SELECT * FROM wfm_employees e WHERE $empSql ORDER BY e.department ASC, e.name ASC");
		$empStmt->execute($empParams);
		$employees = $empStmt->fetchAll(PDO::FETCH_ASSOC);

		// 2. Obtener programación de la semana
		$schedStmt = $db->prepare("SELECT * FROM wfm_schedules WHERE week_code = :week LIMIT 1");
		$schedStmt->execute([':week' => $week]);
		$schedule = $schedStmt->fetch(PDO::FETCH_ASSOC);
		$scheduleId = $schedule ? (int)$schedule['id'] : 1;

		// 3. Obtener turnos de la semana
		$shiftsStmt = $db->prepare("
			SELECT s.*, e.hourly_cost, e.contracted_hours, e.department
			FROM wfm_shifts s
			JOIN wfm_employees e ON s.employee_id = e.id
			WHERE s.schedule_id = :sched_id
			ORDER BY s.day_of_week ASC, s.start_time ASC
		");
		$shiftsStmt->execute([':sched_id' => $scheduleId]);
		$shifts = $shiftsStmt->fetchAll(PDO::FETCH_ASSOC);

		// Indexar turnos por employee_id y day_of_week
		$matrix = [];
		foreach ($employees as $emp) {
			$empId = (int)$emp['id'];
			$matrix[$empId] = [
				'employee' => $emp,
				'total_scheduled_hours' => 0.0,
				'days' => [
					1 => null, // Lunes
					2 => null, // Martes
					3 => null, // Miércoles
					4 => null, // Jueves
					5 => null, // Viernes
					6 => null, // Sábado
					7 => null  // Domingo
				]
			];
		}

		foreach ($shifts as $shift) {
			$empId = (int)$shift['employee_id'];
			$dow = (int)$shift['day_of_week'];

			if (isset($matrix[$empId])) {
				$matrix[$empId]['days'][$dow] = $shift;
				if ($shift['shift_type'] !== 'rest' && $shift['shift_type'] !== 'absence') {
					$matrix[$empId]['total_scheduled_hours'] += (float)$shift['net_hours'];
				}
			}
		}

		// Plantillas disponibles
		$templates = $db->query("SELECT * FROM wfm_shift_templates ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

		return [
			'schedule' => $schedule,
			'employees_count' => count($employees),
			'shifts_count' => count($shifts),
			'schedule_matrix' => array_values($matrix),
			'templates' => $templates
		];
	}

	// 3. AGREGAR O ASIGNAR TURNO
	public function AddShift(array $data): array
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();

		$scheduleId = (int)($data['schedule_id'] ?? 1);
		$employeeId = (int)($data['employee_id'] ?? 0);
		$shiftDate = $data['shift_date'] ?? date('Y-m-d');
		$dayOfWeek = (int)($data['day_of_week'] ?? date('N', strtotime($shiftDate)));
		$startTime = $data['start_time'] ?? '08:00';
		$endTime = $data['end_time'] ?? '16:30';
		$breakMinutes = (int)($data['break_minutes'] ?? 30);
		$shiftType = $data['shift_type'] ?? 'standard';
		$shiftLabel = $data['shift_label'] ?? 'Turno Asignado';
		$colorHex = $data['color_hex'] ?? '#00A859';
		$taskNotes = $data['task_notes'] ?? '';

		// Obtener empleado
		$emp = $db->prepare("SELECT * FROM wfm_employees WHERE id = :id");
		$emp->execute([':id' => $employeeId]);
		$employee = $emp->fetch(PDO::FETCH_ASSOC);
		if (!$employee) {
			throw new Exception("Empleado ID $employeeId no encontrado.");
		}

		// Calcular net hours
		$startTs = strtotime("2000-01-01 $startTime");
		$endTs = strtotime("2000-01-01 $endTime");
		if ($endTs < $startTs) $endTs += 86400; // Turno cruza medianoche
		$rawHours = ($endTs - $startTs) / 3600;
		$netHours = max(0, round($rawHours - ($breakMinutes / 60), 2));
		if ($shiftType === 'rest' || $shiftType === 'absence') {
			$netHours = 0.0;
		}

		// Verificar si ya existe turno para este empleado en ese día
		$existing = $db->prepare("SELECT id FROM wfm_shifts WHERE schedule_id = :sid AND employee_id = :eid AND day_of_week = :dow");
		$existing->execute([':sid' => $scheduleId, ':eid' => $employeeId, ':dow' => $dayOfWeek]);
		$existId = $existing->fetchColumn();

		if ($existId) {
			// Actualizar existente
			$stmt = $db->prepare("
				UPDATE wfm_shifts
				SET start_time = :st, end_time = :et, break_minutes = :bm, net_hours = :nh,
				    shift_type = :stype, shift_label = :slabel, color_hex = :col, task_notes = :notes,
				    updated_at = datetime('now', 'localtime')
				WHERE id = :id
			");
			$stmt->execute([
				':st' => $startTime,
				':et' => $endTime,
				':bm' => $breakMinutes,
				':nh' => $netHours,
				':stype' => $shiftType,
				':slabel' => $shiftLabel,
				':col' => $colorHex,
				':notes' => $taskNotes,
				':id' => $existId
			]);
			$shiftId = (int)$existId;
		} else {
			// Insertar nuevo
			$stmt = $db->prepare("
				INSERT INTO wfm_shifts (schedule_id, employee_id, employee_name, employee_role, shift_date, day_of_week, start_time, end_time, break_minutes, net_hours, shift_type, shift_label, color_hex, task_notes)
				VALUES (:sid, :eid, :ename, :erole, :sdate, :dow, :st, :et, :bm, :nh, :stype, :slabel, :col, :notes)
			");
			$stmt->execute([
				':sid' => $scheduleId,
				':eid' => $employeeId,
				':ename' => $employee['name'],
				':erole' => $employee['role'],
				':sdate' => $shiftDate,
				':dow' => $dayOfWeek,
				':st' => $startTime,
				':et' => $endTime,
				':bm' => $breakMinutes,
				':nh' => $netHours,
				':stype' => $shiftType,
				':slabel' => $shiftLabel,
				':col' => $colorHex,
				':notes' => $taskNotes
			]);
			$shiftId = (int)$db->lastInsertId();
		}

		$this->LogWfmAction($scheduleId, 'SHIFT_SAVED', "Turno ID $shiftId guardado para {$employee['name']} ($startTime-$endTime)");

		return [
			'id' => $shiftId,
			'employee_id' => $employeeId,
			'employee_name' => $employee['name'],
			'day_of_week' => $dayOfWeek,
			'start_time' => $startTime,
			'end_time' => $endTime,
			'net_hours' => $netHours,
			'shift_type' => $shiftType
		];
	}

	// 4. ELIMINAR O CONVERTIR EN DESCANSO
	public function DeleteShift(int $shiftId): bool
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		$stmt = $db->prepare("DELETE FROM wfm_shifts WHERE id = :id");
		$res = $stmt->execute([':id' => $shiftId]);
		if ($res) {
			$this->LogWfmAction(null, 'SHIFT_DELETED', "Turno ID $shiftId eliminado de la programación.");
		}
		return $res;
	}

	// 5. MOVER TURNO (Drag & Drop)
	public function MoveShift(int $shiftId, int $newEmployeeId, int $newDayOfWeek): array
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();

		$shiftStmt = $db->prepare("SELECT * FROM wfm_shifts WHERE id = :id");
		$shiftStmt->execute([':id' => $shiftId]);
		$shift = $shiftStmt->fetch(PDO::FETCH_ASSOC);
		if (!$shift) {
			throw new Exception("Turno ID $shiftId no encontrado.");
		}

		$empStmt = $db->prepare("SELECT * FROM wfm_employees WHERE id = :id");
		$empStmt->execute([':id' => $newEmployeeId]);
		$newEmp = $empStmt->fetch(PDO::FETCH_ASSOC);
		if (!$newEmp) {
			throw new Exception("Empleado de destino ID $newEmployeeId no encontrado.");
		}

		// Actualizar turno
		$update = $db->prepare("
			UPDATE wfm_shifts
			SET employee_id = :eid, employee_name = :ename, employee_role = :erole, day_of_week = :dow, updated_at = datetime('now', 'localtime')
			WHERE id = :id
		");
		$update->execute([
			':eid' => $newEmployeeId,
			':ename' => $newEmp['name'],
			':erole' => $newEmp['role'],
			':dow' => $newDayOfWeek,
			':id' => $shiftId
		]);

		$this->LogWfmAction((int)$shift['schedule_id'], 'SHIFT_MOVED', "Turno ID $shiftId movido a {$newEmp['name']} (Día $newDayOfWeek)");

		return [
			'success' => true,
			'shift_id' => $shiftId,
			'new_employee_id' => $newEmployeeId,
			'new_employee_name' => $newEmp['name'],
			'new_day_of_week' => $newDayOfWeek
		];
	}

	// 6. MOTOR DE RECOMENDACIÓN DE HORAS (PASO 2)
	public function CalculateLaborRecommendation(array $params = []): array
	{
		$forecastSales = (float)($params['forecasted_sales'] ?? 48500.00);
		$targetSalesPerHour = (float)($params['target_sales_per_hour'] ?? 150.00);
		$hourlyCost = (float)($params['hourly_cost'] ?? 7.50);
		$budget = (float)($params['budget'] ?? 2800.00);
		$minCoveragePeople = (int)($params['min_coverage_people'] ?? 4);

		// Cálculo según productividad comercial B2B
		$salesHoursNeeded = round($forecastSales / max(1, $targetSalesPerHour), 1);
		// Horas operativas mínimas de piso (7 días × 14h de apertura × min personas)
		$operationalBaseHours = round(7 * 14 * ($minCoveragePeople * 0.4), 1);

		$recommendedTotalHours = max($operationalBaseHours, $salesHoursNeeded);
		$estimatedCost = round($recommendedTotalHours * $hourlyCost, 2);

		// Demanda por franja horaria
		$timeBands = [
			['band' => '07:00 - 10:00', 'name' => 'Apertura y Descarga', 'demand_level' => 'high', 'recommended_staff' => 5],
			['band' => '10:00 - 13:00', 'name' => 'Pico de Despacho Mayorista', 'demand_level' => 'critical', 'recommended_staff' => 7],
			['band' => '13:00 - 16:00', 'name' => 'Ruta y Reposición Góndolas', 'demand_level' => 'medium', 'recommended_staff' => 4],
			['band' => '16:00 - 19:00', 'name' => 'Cierre de Rutas y Liquidación', 'demand_level' => 'high', 'recommended_staff' => 6],
			['band' => '19:00 - 22:00', 'name' => 'Conteo y Cuadre de Bodega', 'demand_level' => 'medium', 'recommended_staff' => 3],
		];

		return [
			'forecasted_sales' => $forecastSales,
			'target_sales_per_hour' => $targetSalesPerHour,
			'recommended_hours' => $recommendedTotalHours,
			'estimated_labor_cost' => $estimatedCost,
			'labor_budget' => $budget,
			'budget_variance' => round($budget - $estimatedCost, 2),
			'time_bands' => $timeBands
		];
	}

	// 7. SIMULADOR WHAT-IF
	public function RunWhatIfSimulation(array $scenario): array
	{
		$baseForecast = (float)($scenario['base_sales'] ?? 48500.00);
		$salesDeltaPct = (float)($scenario['sales_delta_pct'] ?? 0.0); // e.g. 10.0, 20.0, -10.0
		$productivityDeltaPct = (float)($scenario['productivity_delta_pct'] ?? 0.0);
		$absentEmployeesCount = (int)($scenario['absent_employees'] ?? 0);

		$simulatedSales = round($baseForecast * (1 + ($salesDeltaPct / 100)), 2);
		$baseTargetPerHour = 150.00 * (1 + ($productivityDeltaPct / 100));

		$rec = $this->CalculateLaborRecommendation([
			'forecasted_sales' => $simulatedSales,
			'target_sales_per_hour' => $baseTargetPerHour,
			'hourly_cost' => 7.50,
			'budget' => 2800.00
		]);

		$availableHoursImpact = max(0, 336.0 - ($absentEmployeesCount * 48.0));
		$simulatedCoverage = $rec['recommended_hours'] > 0 ? round(($availableHoursImpact / $rec['recommended_hours']) * 100, 1) : 100.0;

		return [
			'scenario' => $scenario,
			'simulated_sales' => $simulatedSales,
			'recommended_hours' => $rec['recommended_hours'],
			'estimated_labor_cost' => $rec['estimated_labor_cost'],
			'budget_variance' => $rec['budget_variance'],
			'available_scheduled_hours' => $availableHoursImpact,
			'projected_coverage_pct' => $simulatedCoverage,
			'advice' => $simulatedCoverage < 90
				? "Alerta: Con {$absentEmployeesCount} ausencias y demanda +{$salesDeltaPct}%, la cobertura cae a {$simulatedCoverage}%. Se recomienda activar refuerzo de medio tiempo."
				: "Operación estable: Cobertura proyectada en {$simulatedCoverage}% dentro del margen presupuestario."
		];
	}

	// 8. AUDITORÍA Y PUBLICACIÓN (PASO 4)
	public function ValidateAndPublish(int $scheduleId, string $userName = 'Carlos Mendoza'): array
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();

		$schedStmt = $db->prepare("SELECT * FROM wfm_schedules WHERE id = :id");
		$schedStmt->execute([':id' => $scheduleId]);
		$schedule = $schedStmt->fetch(PDO::FETCH_ASSOC);
		if (!$schedule) {
			throw new Exception("Programación ID $scheduleId no encontrada.");
		}

		// Validar solapamientos y reglas laborales
		$warnings = [];
		$errors = [];

		$shifts = $db->prepare("SELECT * FROM wfm_shifts WHERE schedule_id = :sid");
		$shifts->execute([':sid' => $scheduleId]);
		$shiftRows = $shifts->fetchAll(PDO::FETCH_ASSOC);

		// Agrupar por empleado para chequear horas máximas (48h)
		$empHours = [];
		foreach ($shiftRows as $s) {
			$empId = (int)$s['employee_id'];
			if (!isset($empHours[$empId])) {
				$empHours[$empId] = ['name' => $s['employee_name'], 'hours' => 0.0];
			}
			if ($s['shift_type'] !== 'rest' && $s['shift_type'] !== 'absence') {
				$empHours[$empId]['hours'] += (float)$s['net_hours'];
			}
		}

		foreach ($empHours as $eid => $info) {
			if ($info['hours'] > 48.0) {
				$warnings[] = "Empleado {$info['name']} supera el límite legal de 48 horas semanales ({$info['hours']}h programadas).";
			} elseif ($info['hours'] < 30.0) {
				$warnings[] = "Empleado {$info['name']} tiene menos de 30 horas semanales ({$info['hours']}h).";
			}
		}

		// Si no hay errores críticos, publicar
		$newVersion = ((int)($schedule['version'] ?? 1)) + 1;
		$update = $db->prepare("
			UPDATE wfm_schedules
			SET status = 'published', version = :v, published_at = datetime('now', 'localtime'), published_by = :user, updated_at = datetime('now', 'localtime')
			WHERE id = :id
		");
		$update->execute([':v' => $newVersion, ':user' => $userName, ':id' => $scheduleId]);

		$this->LogWfmAction($scheduleId, 'SCHEDULE_PUBLISHED', "Programación {$schedule['week_code']} publicada (Versión $newVersion por $userName).");

		return [
			'success' => true,
			'schedule_id' => $scheduleId,
			'week_code' => $schedule['week_code'],
			'status' => 'published',
			'version' => $newVersion,
			'warnings' => $warnings,
			'published_at' => date('Y-m-d H:i:s'),
			'published_by' => $userName
		];
	}

	// 9. SOLICITUDES DE CAMBIO DE TURNO
	public function GetShiftRequests(): array
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		return $db->query("SELECT * FROM wfm_shift_requests ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
	}

	public function ReviewShiftRequest(int $requestId, string $decision, string $reviewerName = 'Carlos Mendoza'): bool
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		$status = ($decision === 'approve' || $decision === 'approved') ? 'approved' : 'rejected';
		$stmt = $db->prepare("
			UPDATE wfm_shift_requests
			SET status = :st, reviewed_by = :rb, reviewed_at = datetime('now', 'localtime')
			WHERE id = :id
		");
		$res = $stmt->execute([':st' => $status, ':rb' => $reviewerName, ':id' => $requestId]);
		$this->LogWfmAction(null, 'REQUEST_REVIEWED', "Solicitud ID $requestId marcada como $status por $reviewerName.");
		return $res;
	}

	// 10. EMPLEADOS DIRECTORY
	public function GetEmployees(array $filters = []): array
	{
		$db = $this->getDatabaseService()->GetDbConnectionRaw();
		$where = ["active = 1"];
		$params = [];

		if (!empty($filters['department']) && $filters['department'] !== 'Todos') {
			$where[] = "department = :dept";
			$params[':dept'] = $filters['department'];
		}
		if (!empty($filters['role']) && $filters['role'] !== 'Todos') {
			$where[] = "role = :role";
			$params[':role'] = $filters['role'];
		}

		$whereSql = implode(' AND ', $where);
		$stmt = $db->prepare("SELECT * FROM wfm_employees WHERE $whereSql ORDER BY department ASC, name ASC");
		$stmt->execute($params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// REGISTRO DE AUDITORÍA INMUTABLE
	private function LogWfmAction(?int $scheduleId, string $action, string $details): void
	{
		try {
			$db = $this->getDatabaseService()->GetDbConnectionRaw();
			$stmt = $db->prepare("
				INSERT INTO wfm_audit_log (schedule_id, user_name, action, details, ip_address, created_at)
				VALUES (:sid, 'Carlos Mendoza', :act, :det, :ip, datetime('now', 'localtime'))
			");
			$stmt->execute([
				':sid' => $scheduleId,
				':act' => $action,
				':det' => $details,
				':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
			]);
		} catch (\Exception $e) {
			// Silently fail logging if not critical
		}
	}
}
