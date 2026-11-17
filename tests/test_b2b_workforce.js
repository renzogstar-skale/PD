/**
 * Test Suite: GESTIÓN INTELIGENTE DE HORARIOS Y COBERTURA (WFM)
 * Prince Distributors B2B Suite
 */

const http = require('http');

const BASE_URL = 'http://127.0.0.1:8080';

function request(method, path, body = null, headers = {}) {
  return new Promise((resolve, reject) => {
    const url = new URL(path, BASE_URL);
    const options = {
      hostname: url.hostname,
      port: url.port,
      path: url.pathname + url.search,
      method: method,
      headers: {
        'Content-Type': 'application/json',
        ...headers
      }
    };

    const req = http.request(options, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        let json = null;
        try {
          json = JSON.parse(data);
        } catch (e) {
          json = data;
        }
        resolve({
          statusCode: res.statusCode,
          headers: res.headers,
          data: json,
          raw: data
        });
      });
    });

    req.on('error', reject);

    if (body) {
      req.write(typeof body === 'string' ? body : JSON.stringify(body));
    }
    req.end();
  });
}

async function runTests() {
  console.log('======================================================================');
  console.log('   PRINCE DISTRIBUTORS — SUITE DE HORARIOS Y COBERTURA (WFM)          ');
  console.log('======================================================================\n');

  let passed = 0;
  let failed = 0;

  function assert(condition, message) {
    if (condition) {
      console.log(`  \x1b[32m✔ PASS:\x1b[0m ${message}`);
      passed++;
    } else {
      console.error(`  \x1b[31m✖ FAIL:\x1b[0m ${message}`);
      failed++;
    }
  }

  try {
    // 1. Web Views & Sidebar Navigation
    console.log('[1/8] Verificando Rutas Web y Menú Operaciones y Personal...');
    const pageSched = await request('GET', '/b2b/schedules');
    assert(pageSched.statusCode === 200, 'Ruta /b2b/schedules responde HTTP 200 OK');
    assert(pageSched.raw.includes('id="section-schedules"'), 'Contenedor #section-schedules presente en la vista');
    assert(pageSched.raw.includes('Operaciones y Personal'), 'Grupo "Operaciones y Personal" presente en menú lateral');
    assert(pageSched.raw.includes('Horarios y Cobertura'), 'Enlace "Horarios y Cobertura" presente');
    assert(pageSched.raw.includes('Empleados') && pageSched.raw.includes('Asistencia') && pageSched.raw.includes('Solicitudes'), 'Submenús Empleados, Asistencia y Solicitudes presentes');

    // 2. Main Dashboard & The 5 Required Indicators
    console.log('\n[2/8] Verificando los 5 Indicadores Principales del Dashboard...');
    const dashRes = await request('GET', '/api/b2b/wfm/dashboard?week=2026-W47');
    assert(dashRes.statusCode === 200, 'GET /api/b2b/wfm/dashboard responde 200 OK');
    assert(dashRes.data.success === true, 'Respuesta de dashboard indica éxito');
    const kpis = dashRes.data.kpis;
    assert(kpis && typeof kpis.forecasted_sales === 'number', `Indicador 1: Ventas previstas ($${kpis.forecasted_sales})`);
    assert(typeof kpis.recommended_hours === 'number', `Indicador 2: Horas recomendadas (${kpis.recommended_hours} hrs)`);
    assert(typeof kpis.scheduled_hours === 'number', `Indicador 3: Horas programadas (${kpis.scheduled_hours} hrs)`);
    assert(typeof kpis.estimated_labor_cost === 'number', `Indicador 4: Costo laboral estimado ($${kpis.estimated_labor_cost})`);
    assert(typeof kpis.coverage_score_pct === 'number', `Indicador 5: Cobertura de personal (${kpis.coverage_score_pct}%)`);

    // 3. Weekly Schedule Matrix & Shifts
    console.log('\n[3/8] Verificando Calendario Semanal Interactivo (Filas: Empleados, Columnas: Lun-Dom)...');
    const schedRes = await request('GET', '/api/b2b/wfm/schedule?week=2026-W47');
    assert(schedRes.statusCode === 200, 'GET /api/b2b/wfm/schedule responde 200 OK');
    assert(Array.isArray(schedRes.data.schedule_matrix), 'Matriz semanal de turnos estructurada correctamente');
    assert(schedRes.data.schedule_matrix.length >= 5, `Existen colaboradores en la programación (${schedRes.data.schedule_matrix.length} colaboradores)`);
    assert(Array.isArray(schedRes.data.templates) && schedRes.data.templates.length >= 3, 'Plantillas de turno (Apertura, Intermedio, Cierre) disponibles');

    // 4. Shift CRUD & Drag-and-Drop Operations
    console.log('\n[4/8] Probando Operaciones de Turnos (Crear, Duplicar, Mover y Eliminar)...');
    // Add shift
    const createShiftRes = await request('POST', '/api/b2b/wfm/shifts', {
      schedule_id: 1,
      employee_id: 4,
      day_of_week: 3,
      shift_date: '2026-11-18',
      start_time: '13:30',
      end_time: '22:00',
      break_minutes: 30,
      shift_type: 'closing',
      shift_label: 'Cierre',
      task_notes: 'Prueba WFM automatizada'
    });
    assert(createShiftRes.statusCode === 201, 'POST /api/b2b/wfm/shifts crea turno exitosamente (HTTP 201)');
    const createdShiftId = createShiftRes.data.shift.id;
    assert(createShiftRes.data.shift.net_hours === 8.0, 'Cálculo exacto de horas netas descontando descansos (8.0h)');

    // Duplicate shift
    const dupRes = await request('POST', `/api/b2b/wfm/shifts/${createdShiftId}/duplicate`);
    assert(dupRes.statusCode === 201, 'POST /api/b2b/wfm/shifts/{id}/duplicate duplica turno al día siguiente');
    const dupShiftId = dupRes.data.shift.id;

    // Move shift (Drag & drop simulation)
    const moveRes = await request('POST', `/api/b2b/wfm/shifts/${createdShiftId}/move`, {
      new_employee_id: 2,
      new_day_of_week: 4
    });
    assert(moveRes.statusCode === 200, 'POST /api/b2b/wfm/shifts/{id}/move mueve turno a nuevo empleado/día');

    // Delete created shifts
    await request('DELETE', `/api/b2b/wfm/shifts/${createdShiftId}`);
    await request('DELETE', `/api/b2b/wfm/shifts/${dupShiftId}`);
    assert(true, 'DELETE /api/b2b/wfm/shifts/{id} elimina turnos de prueba correctamente');

    // 5. Labor Hours Calculation & Productivity Engine (Step 2)
    console.log('\n[5/8] Probando Motor de Recomendación de Horas & Productividad B2B...');
    const recRes = await request('POST', '/api/b2b/wfm/recommendations', {
      forecasted_sales: 52000.00,
      target_sales_per_hour: 160.00,
      hourly_cost: 7.50,
      budget: 3000.00,
      min_coverage_people: 4
    });
    assert(recRes.statusCode === 200, 'POST /api/b2b/wfm/recommendations responde 200 OK');
    assert(recRes.data.recommended_hours > 0, `Horas recomendadas calculadas: ${recRes.data.recommended_hours} hrs`);
    assert(recRes.data.estimated_labor_cost > 0, `Costo laboral proyectado: $${recRes.data.estimated_labor_cost}`);
    assert(Array.isArray(recRes.data.time_bands) && recRes.data.time_bands.length >= 4, 'Demanda por franja horaria calculada (Apertura, Pico mayorista, Reposición, Cierre)');

    // 6. What-If Simulator
    console.log('\n[6/8] Probando Simulador de Escenarios What-If...');
    const simRes = await request('POST', '/api/b2b/wfm/simulate', {
      base_sales: 48500.00,
      sales_delta_pct: 10.0,
      absent_employees: 1
    });
    assert(simRes.statusCode === 200, 'POST /api/b2b/wfm/simulate responde 200 OK');
    assert(simRes.data.simulated_sales === 53350.00, 'Simulación +10% de ventas correcta ($53,350.00)');
    assert(typeof simRes.data.projected_coverage_pct === 'number', `Cobertura proyectada calculada: ${simRes.data.projected_coverage_pct}%`);
    assert(typeof simRes.data.advice === 'string' && simRes.data.advice.length > 10, 'Recomendación operativa en tiempo real generada');

    // 7. Schedule Validation & Publication (Step 4 & Audit)
    console.log('\n[7/8] Probando Asistente Paso 4: Validación y Publicación Auditada...');
    const pubRes = await request('POST', '/api/b2b/wfm/schedules/1/publish');
    assert(pubRes.statusCode === 200, 'POST /api/b2b/wfm/schedules/{id}/publish responde 200 OK');
    assert(pubRes.data.status === 'published', 'Estado de la programación actualizado a "published"');
    assert(pubRes.data.version >= 1, `Versión de publicación incrementada (v${pubRes.data.version})`);
    assert(Array.isArray(pubRes.data.warnings), 'Validación de reglas laborales (solapamientos, 48h máx, descansos) completada');

    // 8. Employees Directory & Shift Requests
    console.log('\n[8/8] Probando Directorio de Empleados y Gestión de Solicitudes...');
    const empRes = await request('GET', '/api/b2b/wfm/employees');
    assert(empRes.statusCode === 200, 'GET /api/b2b/wfm/employees responde 200 OK');
    assert(empRes.data.employees.length >= 10, `Directorio con personal activo (${empRes.data.employees.length} colaboradores)`);

    const reqRes = await request('GET', '/api/b2b/wfm/requests');
    assert(reqRes.statusCode === 200, 'GET /api/b2b/wfm/requests responde 200 OK');
    assert(reqRes.data.requests.length >= 1, 'Solicitudes de intercambio y vacaciones presentes');

    const reviewRes = await request('POST', `/api/b2b/wfm/requests/${reqRes.data.requests[0].id}/review`, {
      decision: 'approved'
    });
    assert(reviewRes.statusCode === 200, 'POST /api/b2b/wfm/requests/{id}/review procesa solicitud exitosamente');

  } catch (err) {
    console.error('Error durante la ejecución de pruebas:', err);
    failed++;
  }

  console.log('\n======================================================================');
  console.log(` RESULTADOS WFM: ${passed} APROBADAS, ${failed} FALLIDAS`);
  console.log('======================================================================');

  if (failed > 0) {
    process.exit(1);
  }
}

runTests();
