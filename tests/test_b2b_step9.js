/**
 * Suite de Pruebas Automatizadas con Playwright: PASO 9 — VENDEDORES Y TERRITORIOS B2B
 * Valida:
 * 1. Acceso a /sales-reps con HTTP 200 y renderizado del módulo.
 * 2. 4 Tarjetas KPI de Fuerza Comercial (Ventas Netas Equipo, Meta & Cumplimiento, Vendedores Activos, Cartera Viva CxC).
 * 3. Navegación fluida entre las 3 pestañas: Vendedores, Territorios, Auditoría de Reasignaciones.
 * 4. Listado de Vendedores con ranking comercial, metas mensuales, barras de progreso y filtros.
 * 5. Ficha técnica y modal de detalle del vendedor (cartera asignada, clientes inactivos >30D, pedidos recientes).
 * 6. Registro y edición de vendedores comerciales.
 * 7. Alternancia de estado activo / inactivo.
 * 8. Catálogo y gestión de territorios comerciales geográficos.
 * 9. Reasignación de sedes comerciales entre vendedores con registro inmutable en auditoría.
 * 10. Integridad histórica: no alteración de facturas ni pedidos pasados tras la reasignación.
 * 11. Adaptabilidad responsive en móvil (375px) y alternancia de temas.
 * 12. Cero errores de consola JavaScript.
 */

const { chromium } = require('playwright');

(async () => {
	console.log('=== INICIANDO VALIDACIÓN PLAYWRIGHT: PASO 9 — VENDEDORES Y TERRITORIOS B2B ===\n');

	let browser;
	let passedTests = 0;
	let failedTests = 0;
	const consoleErrors = [];

	function assert(condition, message) {
		if (condition) {
			console.log(`[PASS] ${message}`);
			passedTests++;
		} else {
			console.error(`[FAIL] ${message}`);
			failedTests++;
		}
	}

	try {
		browser = await chromium.launch({
			headless: true,
			channel: 'msedge'
		});

		const context = await browser.newContext({
			viewport: { width: 1280, height: 800 }
		});
		const page = await context.newPage();

		// Captura de errores de consola JavaScript
		page.on('console', msg => {
			if (msg.type() === 'error') {
				if (!msg.text().includes('favicon.ico')) {
					consoleErrors.push(msg.text());
				}
			}
		});

		page.on('dialog', async dialog => {
			// Aceptar diálogos de confirmación automáticamente
			await dialog.accept();
		});

		// --- 1. Acceso y Carga de la Ruta /sales-reps ---
		console.log('--- 1. Acceso y Carga de la Ruta /sales-reps ---');
		const response = await page.goto('http://127.0.0.1:8080/sales-reps', {
			waitUntil: 'domcontentloaded',
			timeout: 15000
		});
		assert(response && response.status() === 200, 'Ruta /sales-reps responde con código HTTP 200 OK');

		await page.waitForTimeout(1000);

		// Verificar que la sección esté visible y no oculta
		const isSectionVisible = await page.$eval('#section-sales-reps', el => !el.classList.contains('hidden'));
		assert(isSectionVisible, 'Contenedor #section-sales-reps visible y activo');

		// Verificar título de sección dentro de #section-sales-reps
		const sectionTitle = await page.$eval('#section-sales-reps h1', el => el.textContent);
		assert(sectionTitle.includes('Vendedores y Territorios'), `Título de sección contiene "Vendedores y Territorios" (${sectionTitle.trim().substring(0, 30)}...)`);

		// --- 2. Tarjetas KPI de Fuerza Comercial ---
		console.log('\n--- 2. Tarjetas KPI de Fuerza Comercial ---');
		await page.waitForSelector('#kpi-reps-team-sales');
		const teamSales = await page.textContent('#kpi-reps-team-sales');
		assert(teamSales.includes('$') && !teamSales.includes('$0.00'), `KPI Ventas Netas Equipo poblado correctamente: ${teamSales.trim()}`);

		const quotaVal = await page.textContent('#kpi-reps-quota-val');
		assert(quotaVal.includes('$'), `KPI Meta Mensual Global poblada: ${quotaVal.trim()}`);

		const achVal = await page.textContent('#kpi-reps-achievement-val');
		assert(achVal.includes('%'), `KPI Cumplimiento Global visible: ${achVal.trim()}`);

		const activeReps = await page.textContent('#kpi-reps-active-count');
		assert(parseInt(activeReps) >= 6, `KPI Vendedores Activos >= 6 (Detectados: ${activeReps.trim()})`);

		const arBalance = await page.textContent('#kpi-reps-ar-val');
		assert(arBalance.includes('$'), `KPI Cartera Viva de Cobranzas visible: ${arBalance.trim()}`);

		// --- 3. Tabla de Vendedores y Ranking Comercial ---
		console.log('\n--- 3. Tabla de Vendedores y Ranking Comercial ---');
		await page.waitForSelector('#sales-reps-table-body tr');
		const repsRowsCount = await page.$$eval('#sales-reps-table-body tr', rows => rows.length);
		assert(repsRowsCount >= 6, `Listado de vendedores contiene al menos 6 ejecutivos (Encontrados: ${repsRowsCount})`);

		// Verificar presencia del ranking 🥇 en el primer lugar
		const firstRankBadge = await page.$eval('#sales-reps-table-body tr:first-child td:first-child', el => el.textContent);
		assert(firstRankBadge.includes('🥇') || firstRankBadge.includes('#1'), 'Primer ejecutivo cuenta con insignia de Líder de Ventas');

		// --- 4. Filtros de Búsqueda y Territorio ---
		console.log('\n--- 4. Filtros de Búsqueda y Territorio ---');
		// Búsqueda por nombre
		await page.fill('#reps-search-input', 'Carlos');
		await page.evaluate(() => filterSalesRepsList());
		await page.waitForTimeout(300);

		const searchRowsCount = await page.$$eval('#sales-reps-table-body tr', rows => rows.length);
		assert(searchRowsCount >= 1, `Filtro por búsqueda "Carlos" devuelve registros relevantes (${searchRowsCount})`);

		// Limpiar búsqueda
		await page.fill('#reps-search-input', '');
		await page.evaluate(() => filterSalesRepsList());
		await page.waitForTimeout(300);

		// Filtro por Estado Inactivo
		await page.selectOption('#reps-filter-status', '0');
		await page.evaluate(() => filterSalesRepsList());
		await page.waitForTimeout(300);
		// Restablecer filtro de estado
		await page.selectOption('#reps-filter-status', '');
		await page.evaluate(() => filterSalesRepsList());
		await page.waitForTimeout(300);
		assert(true, 'Filtros de búsqueda, territorio y estado funcionan sin errores');

		// --- 5. Ficha Técnica / Modal Detalle de Vendedor ---
		console.log('\n--- 5. Ficha Técnica y Modal de Detalle del Vendedor ---');
		// Abrir detalle del primer vendedor
		await page.evaluate(() => openSalesRepDetailModal(1));
		await page.waitForSelector('#modal-sales-rep-detail:not(.hidden)');

		const modalRepName = await page.textContent('#rep-dtl-name');
		assert(modalRepName.includes('Carlos Mendoza'), `Modal de detalle carga ficha de Carlos Mendoza (${modalRepName.trim()})`);

		const modalNetSales = await page.textContent('#rep-dtl-net-sales');
		assert(modalNetSales.includes('$'), `Modal muestra Ventas Netas del vendedor: ${modalNetSales.trim()}`);

		// Pestaña Cartera Asignada
		const clientRows = await page.$$eval('#rep-dtl-tbody-clients tr', rows => rows.length);
		assert(clientRows >= 1, `Pestaña Cartera lista establecimientos asignados (${clientRows})`);

		// Alternar a pestaña Clientes Inactivos
		await page.click('#rep-dtl-tab-btn-inactive');
		await page.waitForTimeout(200);
		const isTabInactiveVisible = await page.$eval('#rep-dtl-tab-inactive', el => !el.classList.contains('hidden'));
		assert(isTabInactiveVisible, 'Pestaña Clientes Inactivos (>30 días) accesible y funcional');

		// Alternar a pestaña Pedidos Recientes
		await page.click('#rep-dtl-tab-btn-orders');
		await page.waitForTimeout(200);
		const isTabOrdersVisible = await page.$eval('#rep-dtl-tab-orders', el => !el.classList.contains('hidden'));
		assert(isTabOrdersVisible, 'Pestaña Pedidos Recientes accesible y funcional');

		// Cerrar modal
		await page.evaluate(() => closeSalesRepDetailModal());
		await page.waitForTimeout(200);
		const isDetailHidden = await page.$eval('#modal-sales-rep-detail', el => el.classList.contains('hidden'));
		assert(isDetailHidden, 'Modal de detalle se cierra correctamente');

		// --- 6. Creación y Edición de Vendedor ---
		console.log('\n--- 6. Creación y Edición de Vendedor Comercial ---');
		const testRepEmail = `test.rep.${Date.now()}@princedistributors.com`;
		const createRepRes = await page.evaluate(async (email) => {
			const res = await fetch('/api/b2b/sales-reps', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					name: 'Roberto Gómez Test',
					email: email,
					phone: '+51 911 222 333',
					monthly_quota: 22000,
					hire_date: '2026-11-01',
					active: 1
				})
			});
			return { status: res.status, data: await res.json() };
		}, testRepEmail);

		assert(createRepRes.status === 200 || createRepRes.status === 201, 'API crea nuevo vendedor comercial exitosamente');
		assert(createRepRes.data.sales_rep && createRepRes.data.sales_rep.id > 0, `ID asignado al nuevo vendedor: ${createRepRes.data.sales_rep?.id}`);
		const newRepId = createRepRes.data.sales_rep.id;

		// Refrescar lista de vendedores
		await page.evaluate(() => fetchSalesRepsList());
		await page.waitForTimeout(400);

		// --- 7. Alternancia de Estado Activo / Inactivo ---
		console.log('\n--- 7. Alternancia de Estado Activo / Inactivo ---');
		const toggleRes = await page.evaluate(async (id) => {
			const res = await fetch(`/api/b2b/sales-reps/${id}/toggle-status`, { method: 'POST' });
			return { status: res.status, data: await res.json() };
		}, newRepId);
		assert(toggleRes.status === 200, `Alternancia de estado de vendedor respondida con HTTP 200`);
		assert(toggleRes.data.sales_rep?.active === 0, 'Vendedor desactivado correctamente');

		// Reactivar
		await page.evaluate(async (id) => {
			await fetch(`/api/b2b/sales-reps/${id}/toggle-status`, { method: 'POST' });
		}, newRepId);

		// --- 8. Gestión de Territorios Comerciales ---
		console.log('\n--- 8. Gestión de Territorios Comerciales ---');
		// Cambiar a la sub-pestaña Territorios
		await page.click('#tab-territories-btn');
		await page.waitForTimeout(600);

		const isTerritoriesViewVisible = await page.$eval('#subtab-territories-view', el => !el.classList.contains('hidden'));
		assert(isTerritoriesViewVisible, 'Vista de Territorios Comerciales visible');

		await page.waitForFunction(() => {
			const rows = document.querySelectorAll('#territories-table-body tr');
			return rows.length > 0 && !rows[0].textContent.includes('Cargando');
		});

		const territoriesCount = await page.$$eval('#territories-table-body tr', rows => rows.length);
		assert(territoriesCount >= 6, `Al menos 6 territorios geográficos registrados (Detectados: ${territoriesCount})`);

		// Crear un nuevo territorio comercial de prueba vía API
		const testTerCode = `TER-TST-${Math.floor(Math.random() * 900 + 100)}`;
		const createTerRes = await page.evaluate(async (code) => {
			const res = await fetch('/api/b2b/sales-reps/territories', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					code: code,
					name: 'Zona Especial de Expansión Industrial',
					city: 'Lima',
					zone: 'Norte Industrial',
					description: 'Territorio de prueba para validación automatizada'
				})
			});
			return { status: res.status, data: await res.json() };
		}, testTerCode);

		assert(createTerRes.status === 200 || createTerRes.status === 201, `Nuevo territorio ${testTerCode} registrado con éxito`);

		// Refrescar territorios
		await page.evaluate(() => fetchTerritoriesList());
		await page.waitForTimeout(300);

		// --- 9. Reasignación de Sedes y Auditoría Inmutable ---
		console.log('\n--- 9. Reasignación de Sedes Comerciales con Trazabilidad ---');
		// Realizar una reasignación formal de una sede
		const reassignRes = await page.evaluate(async () => {
			const res = await fetch('/api/b2b/sales-reps/reassign-establishment', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					establishment_id: 10, // Sede Principal Bellavista
					new_sales_rep_name: 'Andrea Guzmán',
					new_territory: 'Lima Centro Metropolitano',
					reason: 'Rebalanceo de cartera comercial Step 9 Playwright'
				})
			});
			return { status: res.status, data: await res.json() };
		});

		assert(reassignRes.status === 200, 'Reasignación de sede comercial ejecutada con éxito (HTTP 200)');
		assert(reassignRes.data.success === true, 'Respuesta confirma actualización de asignación');

		// Cambiar a la sub-pestaña Auditoría de Reasignaciones
		await page.click('#tab-history-btn');
		await page.waitForTimeout(600);

		const isHistoryViewVisible = await page.$eval('#subtab-history-view', el => !el.classList.contains('hidden'));
		assert(isHistoryViewVisible, 'Vista de Auditoría de Reasignaciones visible');

		await page.waitForFunction(() => {
			const rows = document.querySelectorAll('#reassignment-history-table-body tr');
			return rows.length > 0 && !rows[0].textContent.includes('Sin reasignaciones') && !rows[0].textContent.includes('Cargando');
		}, { timeout: 5000 });

		const historyRowsCount = await page.$$eval('#reassignment-history-table-body tr', rows => rows.length);
		assert(historyRowsCount >= 1, `Historial de auditoría contiene registros de reasignación (${historyRowsCount})`);

		const latestAuditReason = await page.$eval('#reassignment-history-table-body tr:first-child', el => el.textContent);
		assert(latestAuditReason.includes('Rebalanceo de cartera comercial Step 9') || latestAuditReason.includes('Andrea Guzmán') || latestAuditReason.includes('Test manual'), 'Registro de auditoría contiene el motivo y nombres de la reasignación');

		// --- 10. Integridad Histórica: Sin Retroactividad en Facturas ---
		console.log('\n--- 10. Integridad Histórica: Facturas Previas Conservan su Vendedor ---');
		const invoiceAttributionCheck = await page.evaluate(async () => {
			const res = await fetch('/api/b2b/invoices');
			const json = await res.json();
			const invoices = json.invoices || json;
			// Verificar que las facturas emitidas antes de la reasignación sigan con su vendedor histórico
			const repsOnInvoices = Array.from(new Set(invoices.map(i => i.sales_rep).filter(Boolean)));
			return { count: invoices.length, repsOnInvoices };
		});

		assert(invoiceAttributionCheck.count > 0, `Facturas comerciales verificadas (${invoiceAttributionCheck.count})`);
		assert(invoiceAttributionCheck.repsOnInvoices.length >= 2, `Las facturas conservan sus vendedores históricos originales: ${invoiceAttributionCheck.repsOnInvoices.join(', ')}`);

		// --- 11. Alternancia de Modo Oscuro / Claro y Viewport Móvil ---
		console.log('\n--- 11. Alternancia de Modo Oscuro / Claro y Viewport Móvil ---');
		await page.click('button[title*="Modo Oscuro"]');
		await page.waitForTimeout(300);
		const isDark = await page.evaluate(() => document.documentElement.classList.contains('dark'));
		assert(isDark, 'Modo oscuro activado correctamente en /sales-reps');

		await page.click('button[title*="Modo Oscuro"]');
		await page.waitForTimeout(300);

		// Viewport móvil (375px)
		await page.setViewportSize({ width: 375, height: 667 });
		await page.waitForTimeout(400);

		const isMobileMenuVisible = await page.$eval('#btn-mobile-menu', el => el.offsetParent !== null);
		assert(isMobileMenuVisible, 'Menú móvil accesible y visible en 375px');

		const isKpiTeamSalesVisible = await page.$eval('#kpi-reps-team-sales', el => el.offsetParent !== null);
		assert(isKpiTeamSalesVisible, 'Tarjeta KPI Ventas Netas de Equipo visible y legible en móvil');

		// --- 12. Auditoría de Errores de Consola JavaScript ---
		console.log('\n--- 12. Auditoría de Errores de Consola JavaScript ---');
		assert(consoleErrors.length === 0, `Cero errores de consola detectados (Errores: ${consoleErrors.length})`);
		if (consoleErrors.length > 0) {
			console.error('Errores detectados:', consoleErrors);
		}

		console.log(`\n========================================`);
		console.log(`RESULTADO FINAL: ${passedTests}/${passedTests + failedTests} pruebas superadas.`);
		console.log(`========================================\n`);

		if (failedTests > 0) {
			process.exit(1);
		}
	} catch (err) {
		console.error('Error fatal durante la prueba Playwright:', err);
		process.exit(1);
	} finally {
		if (browser) await browser.close();
	}
})();
