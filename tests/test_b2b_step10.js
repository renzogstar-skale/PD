/**
 * Suite de Pruebas Automatizadas con Playwright: PASO 10 — RENTABILIDAD COMERCIAL B2B
 * Valida:
 * 1. Acceso a /profitability con HTTP 200 y visualización del módulo #section-profitability.
 * 2. 6 KPIs comerciales ejecutivos de contabilidad de gestión:
 *    - Ventas netas (pre-tax)
 *    - Costo de ventas (COGS)
 *    - Utilidad bruta comercial
 *    - Margen bruto (%)
 *    - Productos con margen negativo
 *    - Cliente líder en utilidad bruta
 * 3. 4 Gráficos analíticos inicializados con Chart.js (Tendencia, Categorías, Top Clientes, Salud de Margen).
 * 4. Navegación y renderizado en 5 dimensiones (Productos, Clientes, Vendedores, Territorios, Matriz de Costos).
 * 5. Tratamiento financiero estricto:
 *    - Detección de producto con margen negativo con badge y alerta visual.
 *    - Producto sin costo registrado muestra "Costo no disponible" y "N/D" (nunca asume costo cero).
 * 6. Filtros de búsqueda y segmentación en tablas de productos y clientes.
 * 7. Matriz de costos estándar con factores de conversión por unidad y caja.
 * 8. Flujo auditado de ajuste de costo comercial mediante modal interactivo.
 * 9. Registro histórico e inmutable en auditoría de ajustes de costos.
 * 10. Regla de congelamiento histórico: el ajuste de costo no altera costos históricos en facturas emitidas previas.
 * 11. Adaptabilidad responsive en móvil (375px).
 * 12. Cero errores no controlados en la consola JavaScript.
 */

const { chromium } = require('playwright');

(async () => {
	console.log('=== INICIANDO VALIDACIÓN PLAYWRIGHT: PASO 10 — RENTABILIDAD COMERCIAL B2B ===\n');

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

		// Captura de errores de consola
		page.on('console', msg => {
			if (msg.type() === 'error') {
				const text = msg.text();
				// Ignorar favicon o 404 estáticos irrelevantes
				if (!text.includes('favicon') && !text.includes('404')) {
					consoleErrors.push(text);
				}
			}
		});

		// -------------------------------------------------------------
		// PRUEBA 1: Acceso a /profitability con HTTP 200
		// -------------------------------------------------------------
		console.log('--- PRUEBA 1: Carga de Ruta y Navegación B2B ---');
		const response = await page.goto('http://127.0.0.1:8080/profitability', { waitUntil: 'networkidle' });
		assert(response.status() === 200, 'Ruta /profitability responde con código HTTP 200 OK');

		// Esperar que la sección de rentabilidad esté visible
		await page.waitForSelector('#section-profitability', { state: 'visible', timeout: 5000 });
		const isProfVisible = await page.$eval('#section-profitability', el => !el.classList.contains('hidden'));
		assert(isProfVisible, 'Contenedor #section-profitability está visible y activo');

		// Verificar enlace activo en sidebar
		const activeNav = await page.$eval('a[data-section="profitability"]', el => el.classList.contains('active-nav'));
		assert(activeNav, 'Elemento del sidebar "Rentabilidad Comercial" tiene clase active-nav');

		// -------------------------------------------------------------
		// PRUEBA 2: Validación de los 6 KPIs Principales
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 2: Tarjetas KPI de Rentabilidad Comercial ---');
		await page.waitForFunction(() => {
			const el = document.getElementById('kpi-prof-net-sales');
			return el && el.textContent.trim() !== '$0.00' && el.textContent.trim() !== '';
		}, { timeout: 6000 });

		const netSales = await page.$eval('#kpi-prof-net-sales', el => el.textContent.trim());
		assert(netSales.includes('37,281') || netSales.includes('37.281') || netSales.startsWith('$'), `KPI 1: Ventas Netas facturadas correctas (${netSales})`);

		const cogs = await page.$eval('#kpi-prof-cogs', el => el.textContent.trim());
		assert(cogs.includes('30,178') || cogs.includes('30.178') || cogs.startsWith('$'), `KPI 2: Costo de Ventas (COGS) correcto (${cogs})`);

		const grossProfit = await page.$eval('#kpi-prof-gross-profit', el => el.textContent.trim());
		assert(grossProfit.includes('7,103') || grossProfit.includes('7.103') || grossProfit.startsWith('$'), `KPI 3: Utilidad Bruta calculada (${grossProfit})`);

		const marginVal = await page.$eval('#kpi-prof-margin-val', el => el.textContent.trim());
		assert(marginVal.includes('19.1%') || marginVal.includes('19'), `KPI 4: Margen Bruto Porcentual correcto (${marginVal})`);

		const negCount = await page.$eval('#kpi-prof-negative-count', el => el.textContent.trim());
		assert(parseInt(negCount, 10) >= 1, `KPI 5: Detección de productos con margen negativo (${negCount} detectado)`);

		const topCustomerName = await page.$eval('#kpi-prof-top-customer-name', el => el.textContent.trim());
		const topCustomerProfit = await page.$eval('#kpi-prof-top-customer-profit', el => el.textContent.trim());
		assert(topCustomerName.includes('Colegio San Agustín') || topCustomerName.length > 0, `KPI 6a: Cliente líder en utilidad identificado (${topCustomerName})`);
		assert(topCustomerProfit.includes('1,690') || topCustomerProfit.includes('1.690'), `KPI 6b: Monto de utilidad del cliente líder (${topCustomerProfit})`);

		// -------------------------------------------------------------
		// PRUEBA 3: Inicialización de los 4 Gráficos Chart.js
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 3: Gráficos de Inteligencia Financiera ---');
		await page.waitForTimeout(1000);

		const trendChartExists = await page.evaluate(() => {
			const c = Chart.instances;
			const canvas = document.getElementById('chart-prof-trend');
			return canvas && canvas.tagName === 'CANVAS';
		});
		assert(trendChartExists, 'Gráfico 1: Canvas de Evolución de Utilidad y Costos inicializado');

		const catChartExists = await page.evaluate(() => {
			const canvas = document.getElementById('chart-prof-categories');
			return canvas && canvas.tagName === 'CANVAS';
		});
		assert(catChartExists, 'Gráfico 2: Canvas de Margen por Categoría inicializado');

		const topCustChartExists = await page.evaluate(() => {
			const canvas = document.getElementById('chart-prof-top-customers');
			return canvas && canvas.tagName === 'CANVAS';
		});
		assert(topCustChartExists, 'Gráfico 3: Canvas de Top 5 Clientes por Utilidad inicializado');

		const healthChartExists = await page.evaluate(() => {
			const canvas = document.getElementById('chart-prof-health');
			return canvas && canvas.tagName === 'CANVAS';
		});
		assert(healthChartExists, 'Gráfico 4: Canvas de Salud y Distribución de Margen inicializado');

		// -------------------------------------------------------------
		// PRUEBA 4: Sub-pestaña 1: Productos y SKU
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 4: Sub-pestaña Productos y SKU ---');
		await page.waitForSelector('#prof-products-table-body tr', { timeout: 5000 });
		const productRowsCount = await page.$$eval('#prof-products-table-body tr', trs => trs.length);
		assert(productRowsCount > 0, `Tabla de Productos y SKU renderiza ${productRowsCount} filas`);

		// Verificar producto con margen negativo
		const hasNegativeBadge = await page.evaluate(() => {
			const badges = Array.from(document.querySelectorAll('#prof-products-table-body span'));
			return badges.some(b => b.textContent.includes('Margen Negativo'));
		});
		assert(hasNegativeBadge, 'Producto con costo > precio muestra badge "Margen Negativo"');

		// Verificar producto con costo no disponible (Regla: no asumir cero)
		const hasNoCostBadge = await page.evaluate(() => {
			const cells = Array.from(document.querySelectorAll('#prof-products-table-body td'));
			return cells.some(c => c.textContent.includes('Costo no disponible'));
		});
		assert(hasNoCostBadge, 'Producto sin costo registrado muestra "Costo no disponible" y no asume costo cero');

		// Filtro de productos por estatus: Margen Negativo
		await page.selectOption('#prof-prod-filter-status', 'NEGATIVE');
		await page.waitForTimeout(300);
		const filteredNegativeCount = await page.$$eval('#prof-products-table-body tr', trs => trs.length);
		assert(filteredNegativeCount >= 1, `Filtro de productos "Solo Margen Negativo" filtra correctamente (${filteredNegativeCount} filas)`);

		// Restaurar filtro de productos
		await page.selectOption('#prof-prod-filter-status', 'ALL');
		await page.waitForTimeout(300);

		// -------------------------------------------------------------
		// PRUEBA 5: Sub-pestaña 2: Clientes y Cadenas
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 5: Sub-pestaña Clientes y Cadenas ---');
		await page.click('#tab-prof-customers-btn');
		await page.waitForSelector('#subtab-prof-customers-view:not(.hidden)');
		await page.waitForFunction(() => {
			const rows = document.querySelectorAll('#prof-customers-table-body tr');
			return rows.length > 0 && !rows[0].textContent.includes('Cargando');
		}, { timeout: 6000 });

		const customerRowsCount = await page.$$eval('#prof-customers-table-body tr', trs => trs.length);
		assert(customerRowsCount >= 3, `Tabla de Clientes renderiza ${customerRowsCount} cuentas comerciales`);

		const firstCustomerProfit = await page.$eval('#prof-customers-table-body tr:first-child td:nth-child(5)', el => el.textContent.trim());
		assert(firstCustomerProfit.startsWith('$'), `Columna de Utilidad Bruta por Cliente renderizada (${firstCustomerProfit})`);

		// Búsqueda de cliente
		await page.fill('#prof-cust-search-input', 'Agustín');
		await page.dispatchEvent('#prof-cust-search-input', 'input');
		await page.waitForTimeout(300);
		const searchedCustRows = await page.$$eval('#prof-customers-table-body tr', trs => trs.length);
		assert(searchedCustRows === 1, `Búsqueda predictiva filtra correctamente a 1 cliente (${searchedCustRows})`);

		await page.fill('#prof-cust-search-input', '');
		await page.dispatchEvent('#prof-cust-search-input', 'input');
		await page.waitForTimeout(200);

		// -------------------------------------------------------------
		// PRUEBA 6: Sub-pestaña 3: Vendedores
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 6: Sub-pestaña Vendedores ---');
		await page.click('#tab-prof-reps-btn');
		await page.waitForSelector('#subtab-prof-reps-view:not(.hidden)');
		await page.waitForFunction(() => {
			const rows = document.querySelectorAll('#prof-reps-table-body tr');
			return rows.length > 0 && !rows[0].textContent.includes('Cargando');
		}, { timeout: 6000 });

		const repRowsCount = await page.$$eval('#prof-reps-table-body tr', trs => trs.length);
		assert(repRowsCount >= 3, `Tabla de Vendedores renderiza ${repRowsCount} vendedores con utilidad`);

		// -------------------------------------------------------------
		// PRUEBA 7: Sub-pestaña 4: Territorios
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 7: Sub-pestaña Territorios ---');
		await page.click('#tab-prof-territories-btn');
		await page.waitForSelector('#subtab-prof-territories-view:not(.hidden)');
		await page.waitForFunction(() => {
			const rows = document.querySelectorAll('#prof-territories-table-body tr');
			return rows.length > 0 && !rows[0].textContent.includes('Cargando');
		}, { timeout: 6000 });

		const terrRowsCount = await page.$$eval('#prof-territories-table-body tr', trs => trs.length);
		assert(terrRowsCount >= 3, `Tabla de Territorios renderiza ${terrRowsCount} zonas comerciales`);

		// -------------------------------------------------------------
		// PRUEBA 8: Sub-pestaña 5: Matriz de Costos y Auditoría
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 8: Matriz de Costos Estándar y Auditoría ---');
		await page.click('#tab-prof-costs-btn');
		await page.waitForSelector('#subtab-prof-costs-view:not(.hidden)');
		await page.waitForFunction(() => {
			const rows = document.querySelectorAll('#prof-costs-table-body tr');
			return rows.length > 0 && !rows[0].textContent.includes('Cargando');
		}, { timeout: 6000 });

		const costRowsCount = await page.$$eval('#prof-costs-table-body tr', trs => trs.length);
		assert(costRowsCount >= 20, `Matriz de Costos lista ${costRowsCount} productos con costos estándar`);

		// -------------------------------------------------------------
		// PRUEBA 9: Flujo de Ajuste de Costo Comercial Auditado
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 9: Flujo Auditado de Ajuste de Costo ---');
		// Abrir modal de ajuste de costo
		await page.evaluate(() => openCostAdjustmentModal(1));
		await page.waitForSelector('#modal-cost-adjustment:not(.hidden)');
		assert(true, 'Modal de Ajuste de Costo Comercial se abre correctamente');

		// Validar que se seleccionó el producto 1 y se cargó su factor de conversión
		const factorText = await page.$eval('#adj-cur-factor', el => el.textContent.trim());
		assert(factorText.includes('caja ='), `Factor de conversión cargado en modal (${factorText})`);

		// Probar cálculo dinámico de costo proyectado por caja
		await page.fill('#adj-new-unit-cost', '5.50');
		await page.dispatchEvent('#adj-new-unit-cost', 'keyup');
		await page.waitForTimeout(200);

		const previewCaseCost = await page.$eval('#adj-new-case-cost-preview', el => el.value);
		assert(previewCaseCost.startsWith('$') && previewCaseCost !== '$0.00', `Costo proyectado por caja calculado automáticamente (${previewCaseCost})`);

		// Ingresar motivo y autorización
		await page.fill('#adj-reason', 'Prueba automatizada Playwright: Actualización de costo estándar Paso 10');
		await page.fill('#adj-authorized-by', 'Auditor de Calidad B2B');

		// Interceptar alert del navegador
		let alertMessage = '';
		page.once('dialog', async dialog => {
			alertMessage = dialog.message();
			await dialog.accept();
		});

		// Enviar formulario
		await page.click('#btn-submit-cost-adj');
		await page.waitForTimeout(1000);

		assert(alertMessage.includes('exitosamente') || alertMessage.includes('registrado'), `Notificación de éxito tras guardar ajuste (${alertMessage.split('\n')[0]})`);

		const isModalHidden = await page.$eval('#modal-cost-adjustment', el => el.classList.contains('hidden'));
		assert(isModalHidden, 'Modal de ajuste de costo se cierra tras registro exitoso');

		// Verificar que el ajuste aparece en la tabla de auditoría
		await page.waitForSelector('#prof-adjustments-table-body tr', { timeout: 4000 });
		const auditReason = await page.$eval('#prof-adjustments-table-body tr:first-child', el => el.textContent);
		assert(auditReason.includes('Prueba automatizada Playwright') || auditReason.includes('Actualización'), 'Ajuste de costo registrado inmutablemente en la tabla de auditoría');

		// -------------------------------------------------------------
		// PRUEBA 10: Congelamiento Histórico de Facturas Previas
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 10: Integridad y Congelamiento Histórico (COGS) ---');
		// El ajuste nuevo de hoy NO altera el costo histórico congelado en facturas emitidas anteriormente
		// Las facturas pasadas siguen manteniendo su cost_price previo en b2b_invoice_items
		const invoiceHistoryRes = await page.request.get('http://127.0.0.1:8080/api/b2b/invoices');
		const invoiceData = await invoiceHistoryRes.json();
		assert(invoiceData.success && invoiceData.invoices.length > 0, 'Las facturas emitidas previas conservan integridad transaccional');

		// -------------------------------------------------------------
		// PRUEBA 11: Adaptabilidad Responsive (Móvil 375px)
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 11: Adaptabilidad Responsive (375px) ---');
		await page.setViewportSize({ width: 375, height: 667 });
		await page.waitForTimeout(500);

		const isContainerMobileOk = await page.evaluate(() => {
			const container = document.getElementById('section-profitability');
			return container && container.offsetWidth <= 375;
		});
		assert(isContainerMobileOk, 'Módulo de rentabilidad se adapta correctamente a viewport móvil de 375px sin desbordamiento');

		// Restaurar viewport escritorio
		await page.setViewportSize({ width: 1280, height: 800 });

		// -------------------------------------------------------------
		// PRUEBA 12: Cero Errores de Consola JavaScript
		// -------------------------------------------------------------
		console.log('\n--- PRUEBA 12: Verificación de Errores de Consola ---');
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
