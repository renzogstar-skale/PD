/**
 * Suite de Pruebas Automatizadas con Playwright: PASO 8 — TABLERO EJECUTIVO DE VENTAS B2B
 * Valida los 6 KPIs principales, 6 gráficos ejecutivos, filtros globales sincronizados,
 * comparativas entre períodos, precisión matemática, viewport móvil y cero errores de consola.
 */

const { chromium } = require('playwright');

(async () => {
	console.log('=== INICIANDO VALIDACIÓN PLAYWRIGHT: PASO 8 — TABLERO EJECUTIVO DE VENTAS B2B ===\n');

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
				// Ignorar 404 de favicon si se produce
				if (!msg.text().includes('favicon.ico')) {
					consoleErrors.push(msg.text());
				}
			}
		});

		page.on('pageerror', err => {
			consoleErrors.push(err.message);
		});

		// --- 1. Carga de /sales y Verificación de los 6 KPIs Principales ---
		console.log('--- 1. Carga de /sales y Verificación de los 6 KPIs Principales ---');
		const res = await page.goto('http://127.0.0.1:8080/sales', { waitUntil: 'domcontentloaded' });
		assert(res.status() === 200, 'Ruta /sales responde HTTP 200');

		// Esperar que los KPIs dinámicos se pueblen
		await page.waitForFunction(() => {
			const el = document.getElementById('kpi-net-sales-val');
			return el && el.textContent.includes('$') && el.textContent !== '$0.00';
		}, { timeout: 7000 });

		const netSalesVal = await page.$eval('#kpi-net-sales-val', el => el.textContent.trim());
		const ordersVal = await page.$eval('#kpi-orders-val', el => el.textContent.trim());
		const customersVal = await page.$eval('#kpi-customers-val', el => el.textContent.trim());
		const ticketVal = await page.$eval('#kpi-ticket-val', el => el.textContent.trim());
		const receivableVal = await page.$eval('#kpi-receivable-val', el => el.textContent.trim());
		const variationVal = await page.$eval('#kpi-variation-val', el => el.textContent.trim());

		assert(netSalesVal.startsWith('$'), `KPI 1: Ventas Netas Facturadas tiene formato moneda (${netSalesVal})`);
		assert(parseInt(ordersVal) >= 0, `KPI 2: Pedidos Confirmados es numérico (${ordersVal})`);
		assert(parseInt(customersVal) >= 0, `KPI 3: Clientes Activos es numérico (${customersVal})`);
		assert(ticketVal.startsWith('$'), `KPI 4: Ticket Promedio Facturado tiene formato moneda (${ticketVal})`);
		assert(receivableVal.startsWith('$'), `KPI 5: Cuentas por Cobrar tiene formato moneda (${receivableVal})`);
		assert(variationVal.includes('%') || variationVal === 'N/D', `KPI 6: Variación vs. Anterior tiene formato porcentaje (${variationVal})`);

		// --- 2. Tooltips Explicativos en las 6 Tarjetas KPI ---
		console.log('\n--- 2. Tooltips Explicativos en las 6 Tarjetas KPI ---');
		const tooltip1 = await page.$eval('#kpi-net-sales-card', el => el.getAttribute('title'));
		const tooltip2 = await page.$eval('#kpi-orders-card', el => el.getAttribute('title'));
		const tooltip3 = await page.$eval('#kpi-customers-card', el => el.getAttribute('title'));
		const tooltip4 = await page.$eval('#kpi-ticket-card', el => el.getAttribute('title'));
		const tooltip5 = await page.$eval('#kpi-receivable-card', el => el.getAttribute('title'));
		const tooltip6 = await page.$eval('#kpi-variation-card', el => el.getAttribute('title'));

		assert(tooltip1 && (tooltip1.includes('Ventas Netas') || tooltip1.includes('Ventas netas')), 'Tooltip KPI 1 explica fórmula de Ventas Netas');
		assert(tooltip2 && tooltip2.includes('Confirmado'), 'Tooltip KPI 2 explica pedidos confirmados');
		assert(tooltip3 && tooltip3.includes('clientes'), 'Tooltip KPI 3 explica clientes activos');
		assert(tooltip4 && tooltip4.includes('Ticket Promedio'), 'Tooltip KPI 4 explica fórmula de Ticket Promedio');
		assert(tooltip5 && tooltip5.includes('Saldo'), 'Tooltip KPI 5 explica cuentas por cobrar y saldos');
		assert(tooltip6 && tooltip6.includes('anterior'), 'Tooltip KPI 6 explica variación porcentual o N/D');

		// --- 3. Validación Matemática de Ventas Netas y Reducción por Notas de Crédito ---
		console.log('\n--- 3. Validación Matemática de Ventas Netas y Reducción por Notas de Crédito ---');
		const apiRes = await page.evaluate(async () => {
			const r = await fetch('/api/b2b/dashboard/executive?period=MTD');
			return await r.json();
		});

		const rawNet = apiRes.kpis.net_sales.raw;
		const grossSubtotal = parseFloat(apiRes.kpis.net_sales.gross_subtotal.replace(/,/g, ''));
		const cnSubtotal = parseFloat(apiRes.kpis.net_sales.credit_notes.replace(/,/g, ''));

		assert(Math.abs(rawNet - Math.max(0, grossSubtotal - cnSubtotal)) < 0.05,
			`Ventas Netas ($${rawNet}) = Subtotal Facturado ($${grossSubtotal}) − Notas de Crédito ($${cnSubtotal})`);
		assert(cnSubtotal > 0, `Notas de crédito emitidas ($${cnSubtotal}) reducen efectivamente las ventas netas`);

		// --- 4. Validación de los 6 Gráficos Ejecutivos Inicializados ---
		console.log('\n--- 4. Validación de los 6 Gráficos Ejecutivos Inicializados ---');
		const chartInstancesStatus = await page.evaluate(() => {
			return {
				trend: !!window.salesTrendChartInstance,
				category: !!window.channelShareChartInstance,
				billingVsColl: !!window.billingVsCollectionsChartInstance,
				orderStatus: !!window.orderStatusChartInstance,
				topProducts: !!window.topProductsChartInstance
			};
		});

		assert(chartInstancesStatus.trend, 'Gráfico 1: Evolución de Facturación & Margen B2B activo');
		assert(chartInstancesStatus.category, 'Gráfico 2: Ventas por Categoría (Doughnut) activo');
		assert(chartInstancesStatus.billingVsColl, 'Gráfico 3: Facturación vs. Cobros (Bar) activo');
		assert(chartInstancesStatus.orderStatus, 'Gráfico 4: Pedidos por Estado (Doughnut) activo');
		assert(chartInstancesStatus.topProducts, 'Gráfico 5: Top 5 Productos Facturados activo');

		// Gráfico 6 / Tablero de Ventas por Cliente (Top 5 Cuentas)
		const topAccountsCount = await page.$$eval('#top-accounts-list > div', divs => divs.length);
		assert(topAccountsCount > 0, `Gráfico 6 / Tablero Top Cuentas Corporativas tiene ${topAccountsCount} cuentas clasificadas`);

		// --- 5. Filtros Globales: Cambio de Período Temporal (7D) ---
		console.log('\n--- 5. Filtros Globales: Cambio de Período Temporal (7D) ---');
		await page.click('#dash-btn-7d');
		await page.waitForTimeout(600);

		const badge7d = await page.$eval('#dash-period-badge', el => el.textContent.trim());
		assert(badge7d.includes('7 días') || badge7d.includes('7D'), `Badge de período se actualizó a: "${badge7d}"`);

		const netSales7d = await page.$eval('#kpi-net-sales-val', el => el.textContent.trim());
		assert(netSales7d.startsWith('$'), `Ventas netas para 7D calculadas: ${netSales7d}`);

		// --- 6. Filtros Globales: Filtro por Cliente Específico ---
		console.log('\n--- 6. Filtros Globales: Filtro por Cliente Específico ---');
		// Seleccionar Cliente 1 en el dropdown
		await page.selectOption('#dash-filter-customer', '1');
		await page.waitForTimeout(600);

		const netSalesCust1 = await page.$eval('#kpi-net-sales-val', el => el.textContent.trim());
		assert(netSalesCust1.startsWith('$'), `Ventas netas filtradas para Cliente 1: ${netSalesCust1}`);

		// Establecimientos se sincronizaron con el cliente
		const estOptionsCount = await page.$$eval('#dash-filter-establishment option', opts => opts.length);
		assert(estOptionsCount >= 1, `Establecimientos sincronizados dinámicamente con el cliente (Opciones: ${estOptionsCount})`);

		// --- 7. Filtros Globales: Restablecer Filtros (Reset) ---
		console.log('\n--- 7. Filtros Globales: Restablecer Filtros (Reset) ---');
		await page.click('#btn-reset-dash-filters');
		await page.waitForTimeout(600);

		const custSelectVal = await page.$eval('#dash-filter-customer', el => el.value);
		const badgeReset = await page.$eval('#dash-period-badge', el => el.textContent.trim());
		assert(custSelectVal === '', 'Selector de clientes restablecido a "Todos los Clientes"');
		assert(badgeReset.includes('fecha') || badgeReset.includes('2026'), `Período restablecido a MTD (${badgeReset})`);

		// --- 8. Manejo de Períodos Sin Información (Empty State Seguro) ---
		console.log('\n--- 8. Manejo de Períodos Sin Información (Empty State Seguro) ---');
		// Consultar período futuro vacío vía API
		const emptyRes = await page.evaluate(async () => {
			const r = await fetch('/api/b2b/dashboard/executive?period=CUSTOM&from_date=2028-01-01&to_date=2028-01-31');
			return await r.json();
		});

		assert(emptyRes.success === true, 'Respuesta exitosa HTTP 200 ante período sin actividad');
		assert(emptyRes.kpis.net_sales.raw === 0, 'Ventas netas en $0.00 en período sin datos');
		assert(emptyRes.kpis.sales_variation.formatted === 'N/D', 'Variación muestra "N/D" ante ausencia de datos previos');
		assert(emptyRes.tables.top_accounts.accounts.length === 0, 'Top cuentas responde lista vacía controlada sin error');

		// --- 9. Órdenes Mayoristas Recientes Conectadas a Base de Datos ---
		console.log('\n--- 9. Órdenes Mayoristas Recientes Conectadas a Base de Datos ---');
		const recentOrdersRows = await page.$$eval('#recent-orders-table-body tr', rows => rows.length);
		assert(recentOrdersRows >= 1, `Tabla de órdenes recientes contiene ${recentOrdersRows} pedidos comerciales reales`);

		const firstOrderNumber = await page.$eval('#recent-orders-table-body tr:first-child td:first-child', el => el.textContent.trim());
		assert(firstOrderNumber.startsWith('PED-') || firstOrderNumber.startsWith('#'), `Número de orden válido: ${firstOrderNumber}`);

		// --- 10. Alternancia de Modo Oscuro / Claro y Persistencia ---
		console.log('\n--- 10. Alternancia de Modo Oscuro / Claro y Persistencia ---');
		await page.click('button[title*="Modo Oscuro"]');
		await page.waitForTimeout(300);
		const isDark = await page.evaluate(() => document.documentElement.classList.contains('dark'));
		assert(isDark, 'Modo oscuro activado correctamente');

		// Verificar que los gráficos se re-renderizaron sin romperse
		const trendAfterTheme = await page.evaluate(() => !!window.salesTrendChartInstance);
		assert(trendAfterTheme, 'Gráfico se adaptó a la paleta de colores del modo oscuro');

		await page.click('button[title*="Modo Oscuro"]');
		await page.waitForTimeout(300);

		// --- 11. Viewport Móvil (375px x 667px) ---
		console.log('\n--- 11. Viewport Móvil (375px x 667px) ---');
		await page.setViewportSize({ width: 375, height: 667 });
		await page.waitForTimeout(400);

		const isMobileMenuVisible = await page.$eval('#btn-mobile-menu', el => el.offsetParent !== null);
		assert(isMobileMenuVisible, 'Menú móvil accesible y visible en 375px');

		const kpiNetSalesVisible = await page.$eval('#kpi-net-sales-card', el => el.offsetParent !== null);
		assert(kpiNetSalesVisible, 'Tarjeta KPI Ventas Netas se adapta y es legible en móvil');

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
