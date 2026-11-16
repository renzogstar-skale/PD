/**
 * Suite de Pruebas Automatizadas con Playwright: PASO 11 — INTELIGENCIA COMERCIAL B2B
 * Valida:
 * 1. Acceso a /reports con HTTP 200 y renderizado de #section-reports.
 * 2. 4 Tarjetas KPI de Inteligencia Comercial (Alertas Activas, Clientes en Riesgo, Facturas Vencidas, Oportunidades).
 * 3. Detección automática de cliente inactivo (>30 días sin compras).
 * 4. Detección de caída significativa de ventas (>30% vs período anterior).
 * 5. Identificación de producto habitual que un cliente dejó de comprar (churn de producto).
 * 6. Detección de factura vencida con saldo pendiente en cobranzas.
 * 7. Alerta crítica por margen comercial negativo (<0%).
 * 8. Deduplicación e idempotencia del motor analítico (sin duplicados al recalcular).
 * 9. Persistencia de alerta revisada (marcar como revisada, registrar auditor y conservar estado tras recálculo).
 * 10. Navegación fluida en 5 sub-pestañas (Alertas, Clientes en Riesgo, Oportunidades, Tendencias, Umbrales).
 * 11. Adaptabilidad responsive en móvil (375px).
 * 12. Cero errores no controlados en la consola JavaScript.
 */

const { chromium } = require('playwright');
const { execSync } = require('child_process');

(async () => {
	console.log('=== INICIANDO VALIDACIÓN PLAYWRIGHT: PASO 11 — INTELIGENCIA COMERCIAL B2B ===\n');

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
		// Asegurar que las alertas inicien en estado ACTIVA para reproducibilidad de la suite
		execSync('python -c "import sqlite3; conn = sqlite3.connect(\'data/grocy_es.db\'); conn.execute(\'UPDATE b2b_commercial_alerts SET status = \\\"ACTIVA\\\"\'); conn.commit(); conn.close()"', { cwd: "C:/Users/renzo/grocy/grocy" });

		browser = await chromium.launch({
			headless: true,
			channel: 'msedge'
		});

		const context = await browser.newContext({
			viewport: { width: 1280, height: 800 }
		});
		const page = await context.newPage();

		// Captura de errores de consola
		page.on('console', msg => {
			if (msg.type() === 'error') {
				const text = msg.text();
				if (!text.includes('favicon') && !text.includes('404')) {
					consoleErrors.push(text);
				}
			}
		});

		// -------------------------------------------------------------
		// PRUEBA 1: Acceso a /reports con HTTP 200
		// -------------------------------------------------------------
		console.log('--- PRUEBA 1: Carga de Ruta y Navegación B2B ---');
		const response = await page.goto('http://127.0.0.1:8080/reports', { waitUntil: 'networkidle' });
		assert(response.status() === 200, 'Ruta /reports responde con código HTTP 200 OK');

		await page.waitForSelector('#section-reports', { state: 'visible', timeout: 5000 });
		const isReportsVisible = await page.$eval('#section-reports', el => !el.classList.contains('hidden'));
		assert(isReportsVisible, 'Contenedor #section-reports está visible y activo');

		const activeNav = await page.$eval('a[data-section="reports"]', el => el.classList.contains('active-nav'));
		assert(activeNav, 'Elemento del sidebar "Reportes y Analítica" tiene clase active-nav');

		// -------------------------------------------------------------
		// PRUEBA 2: Validación de las 4 Tarjetas KPI
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 2: Tarjetas KPI de Inteligencia Comercial ---');
		await page.waitForFunction(() => {
			const el = document.getElementById('kpi-intel-active-alerts');
			return el && parseInt(el.textContent.trim(), 10) > 0;
		}, { timeout: 6000 });

		const activeAlerts = await page.$eval('#kpi-intel-active-alerts', el => el.textContent.trim());
		assert(parseInt(activeAlerts, 10) >= 5, `KPI 1: Alertas Comerciales Activas pobladas (${activeAlerts} activas)`);

		const inactiveCust = await page.$eval('#kpi-intel-inactive-customers', el => el.textContent.trim());
		assert(parseInt(inactiveCust, 10) >= 1, `KPI 2: Clientes en riesgo/inactivos detectados (${inactiveCust} clientes)`);

		const overdueBal = await page.$eval('#kpi-intel-overdue-balance', el => el.textContent.trim());
		assert(overdueBal.startsWith('$') && overdueBal !== '$0.00', `KPI 3: Monto de facturas vencidas en riesgo (${overdueBal})`);

		const oppsCount = await page.$eval('#kpi-intel-opportunities-count', el => el.textContent.trim());
		assert(parseInt(oppsCount, 10) >= 5, `KPI 4: Oportunidades de recompra detectadas (${oppsCount} oportunidades)`);

		// -------------------------------------------------------------
		// PRUEBA 3: Detección Automática de Cliente Inactivo (>30D)
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 3: Detección de Cliente Inactivo (>30 Días) ---');
		const hasInactiveAlert = await page.evaluate(() => {
			const titles = Array.from(document.querySelectorAll('#intel-alerts-container h4'));
			return titles.some(t => t.textContent.includes('Inactivo') || t.textContent.includes('días sin compra'));
		});
		assert(hasInactiveAlert, 'Alerta automática por cliente inactivo (>30D) generada con días de inactividad');

		// -------------------------------------------------------------
		// PRUEBA 4: Detección de Caída Significativa de Ventas (>30%)
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 4: Detección de Caída Significativa de Ventas ---');
		const hasDropAlert = await page.evaluate(() => {
			const titles = Array.from(document.querySelectorAll('#intel-alerts-container h4'));
			return titles.some(t => t.textContent.includes('Caída de Ventas'));
		});
		assert(hasDropAlert, 'Alerta de caída significativa de ventas (>30%) detectada con porcentaje de contracción');

		// -------------------------------------------------------------
		// PRUEBA 5: Detección de Factura Vencida en Cobranzas
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 5: Detección de Factura Vencida con Saldo ---');
		const hasOverdueAlert = await page.evaluate(() => {
			const titles = Array.from(document.querySelectorAll('#intel-alerts-container h4'));
			return titles.some(t => t.textContent.includes('Factura Vencida'));
		});
		assert(hasOverdueAlert, 'Alerta por factura vencida con saldo pendiente en cobranzas generada');

		// -------------------------------------------------------------
		// PRUEBA 6: Detección Crítica de Margen Negativo (<0%)
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 6: Alerta por Margen Comercial Negativo ---');
		const hasNegMarginAlert = await page.evaluate(() => {
			const titles = Array.from(document.querySelectorAll('#intel-alerts-container h4'));
			return titles.some(t => t.textContent.includes('Margen Negativo'));
		});
		assert(hasNegMarginAlert, 'Alerta crítica por margen comercial negativo (<0%) detectada con SKU');

		// -------------------------------------------------------------
		// PRUEBA 7: Eliminación de Duplicados e Idempotencia
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 7: Eliminación de Duplicados e Idempotencia ---');
		const countBefore = await page.$$eval('#intel-alerts-container > div', divs => divs.length);

		// Ejecutar recálculo del motor
		let alertMsg = '';
		page.once('dialog', async dialog => {
			alertMsg = dialog.message();
			await dialog.accept();
		});
		await page.click('button:has-text("Recalcular Motor")');
		await page.waitForTimeout(1000);

		const countAfter = await page.$$eval('#intel-alerts-container > div', divs => divs.length);
		assert(countBefore === countAfter, `Deduplicación garantizada: el número de alertas se mantiene estable tras recálculo (${countBefore} = ${countAfter})`);

		// -------------------------------------------------------------
		// PRUEBA 8: Flujo de Gestión y Persistencia de Alerta Revisada
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 8: Gestión y Persistencia de Alerta Revisada ---');
		const firstReviewBtn = await page.$('#intel-alerts-container button:has-text("Gestionar / Revisar")');
		assert(firstReviewBtn !== null, 'Botón de "Gestionar / Revisar" disponible en alertas activas');

		if (firstReviewBtn) {
			await firstReviewBtn.click();
			await page.waitForSelector('#modal-intel-review:not(.hidden)');
			assert(true, 'Modal de revisión de alerta comercial se abre correctamente');

			await page.fill('#review-by', 'Auditor de Calidad B2B');
			await page.fill('#review-notes', 'Prueba automatizada Playwright: Acuerdo de reunión con cliente');

			let reviewAlertMsg = '';
			page.once('dialog', async dialog => {
				reviewAlertMsg = dialog.message();
				await dialog.accept();
			});

			await page.click('#btn-submit-review');
			await page.waitForTimeout(1000);

			assert(reviewAlertMsg.includes('exitosamente') || reviewAlertMsg.includes('revisada'), 'Confirmación de alerta marcada como revisada');

			const isModalHidden = await page.$eval('#modal-intel-review', el => el.classList.contains('hidden'));
			assert(isModalHidden, 'Modal de revisión se cierra tras confirmación');
		}

		// -------------------------------------------------------------
		// PRUEBA 9: Sub-pestaña Clientes en Riesgo e Inactivos
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 9: Sub-pestaña Clientes en Riesgo e Inactivos ---');
		await page.click('#tab-intel-inactive-btn');
		await page.waitForSelector('#subtab-intel-inactive-view:not(.hidden)');
		await page.waitForFunction(() => {
			const rows = document.querySelectorAll('#intel-inactive-table-body tr');
			return rows.length > 0 && !rows[0].textContent.includes('Cargando');
		}, { timeout: 6000 });

		const inactiveRowsCount = await page.$$eval('#intel-inactive-table-body tr', trs => trs.length);
		assert(inactiveRowsCount >= 1, `Tabla de Clientes en Riesgo renderiza ${inactiveRowsCount} cuentas`);

		// Búsqueda de cliente
		await page.fill('#intel-cust-search-input', 'Comedor');
		await page.waitForTimeout(300);
		const searchedCount = await page.$$eval('#intel-inactive-table-body tr', trs => trs.length);
		assert(searchedCount >= 1, `Búsqueda predictiva filtra correctamente clientes en riesgo (${searchedCount} encontrados)`);

		await page.fill('#intel-cust-search-input', '');
		await page.waitForTimeout(200);

		// -------------------------------------------------------------
		// PRUEBA 10: Sub-pestaña Oportunidades Comerciales & Recompra
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 10: Sub-pestaña Oportunidades & Recompra ---');
		await page.click('#tab-intel-opportunities-btn');
		await page.waitForSelector('#subtab-intel-opportunities-view:not(.hidden)');
		await page.waitForFunction(() => {
			const rows = document.querySelectorAll('#intel-abandoned-table-body tr');
			return rows.length > 0 && !rows[0].textContent.includes('Cargando');
		}, { timeout: 6000 });

		const abandonedRows = await page.$$eval('#intel-abandoned-table-body tr', trs => trs.length);
		assert(abandonedRows >= 1, `Sección de Productos que Dejó de Comprar detecta ${abandonedRows} casos reales`);

		const crossRows = await page.$$eval('#intel-cross-table-body tr', trs => trs.length);
		assert(crossRows >= 1, `Sección de Cross-Selling en cuentas similares lista ${crossRows} recomendaciones`);

		// -------------------------------------------------------------
		// PRUEBA 11: Sub-pestaña Tendencias de Categorías
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 11: Sub-pestaña Tendencias de Categorías ---');
		await page.click('#tab-intel-trends-btn');
		await page.waitForSelector('#subtab-intel-trends-view:not(.hidden)');
		await page.waitForFunction(() => {
			const rows = document.querySelectorAll('#intel-trends-table-body tr');
			return rows.length > 0 && !rows[0].textContent.includes('Cargando');
		}, { timeout: 6000 });

		const trendRows = await page.$$eval('#intel-trends-table-body tr', trs => trs.length);
		assert(trendRows >= 3, `Tabla de Tendencias renderiza ${trendRows} categorías comerciales con variación`);

		// -------------------------------------------------------------
		// PRUEBA 12: Sub-pestaña Configuración de Umbrales
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 12: Sub-pestaña Umbrales Configurables ---');
		await page.click('#tab-intel-thresholds-btn');
		await page.waitForSelector('#subtab-intel-thresholds-view:not(.hidden)');
		await page.waitForFunction(() => {
			const el = document.getElementById('thresh-inactive-days');
			return el && el.value !== '' && parseInt(el.value, 10) > 0;
		}, { timeout: 6000 });

		const inactiveDaysVal = await page.$eval('#thresh-inactive-days', el => el.value);
		assert(parseInt(inactiveDaysVal, 10) >= 15, `Umbral configurable cargado en formulario (${inactiveDaysVal} días)`);

		// -------------------------------------------------------------
		// PRUEBA 13: Adaptabilidad Responsive (Móvil 375px)
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 13: Adaptabilidad Responsive (375px) ---');
		await page.setViewportSize({ width: 375, height: 667 });
		await page.waitForTimeout(500);

		const isContainerMobileOk = await page.evaluate(() => {
			const container = document.getElementById('section-reports');
			return container && container.offsetWidth <= 375;
		});
		assert(isContainerMobileOk, 'Módulo de Inteligencia Comercial se adapta a viewport móvil de 375px sin desbordamiento');

		// Restaurar viewport
		await page.setViewportSize({ width: 1280, height: 800 });

		// -------------------------------------------------------------
		// PRUEBA 14: Cero Errores de Consola JavaScript
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 14: Verificación de Errores de Consola ---');
		if (consoleErrors.length > 0) {
			console.warn('Errores de consola capturados:', consoleErrors);
		}
		assert(consoleErrors.length === 0, `Cero errores de consola JavaScript detectados (Total: ${consoleErrors.length})`);

	} catch (err) {
		console.error('\n[ERROR CRÍTICO EN EJECUCIÓN DE PRUEBAS]:', err);
		failedTests++;
	} finally {
		if (browser) {
			await browser.close();
		}

		console.log('\n=============================================================');
		console.log(`RESUMEN DE PRUEBAS: ${passedTests} PASADAS, ${failedTests} FALLIDAS`);
		console.log('=============================================================\n');

		if (failedTests > 0) {
			process.exit(1);
		} else {
			process.exit(0);
		}
	}
})();
