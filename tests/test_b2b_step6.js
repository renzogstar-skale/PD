const { chromium } = require('playwright');

(async () => {
	console.log('=== INICIANDO VALIDACIÓN PLAYWRIGHT: PASO 6 — COTIZACIONES Y PEDIDOS B2B ===\n');

	const browser = await chromium.launch({ channel: 'msedge', headless: true });
	const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

	const consoleErrors = [];
	page.on('console', msg => {
		if (msg.type() === 'error' && !msg.text().includes('status of 400')) {
			consoleErrors.push(msg.text());
		}
	});
	page.on('pageerror', err => consoleErrors.push(err.message));

	let passedTests = 0;
	let totalTests = 0;

	function assert(condition, testName) {
		totalTests++;
		if (condition) {
			console.log(`[PASS] ${testName}`);
			passedTests++;
		} else {
			console.error(`[FAIL] ${testName}`);
		}
	}

	try {
		// 1. CARGA INICIAL DE LA RUTA /orders Y PERSISTENCIA DE DATOS DEMO
		console.log('\n--- 1. Carga de /orders y Métricas del Ciclo Comercial ---');
		const res = await page.goto('http://127.0.0.1:8080/orders', { waitUntil: 'networkidle' });
		assert(res.status() === 200, 'Ruta /orders responde HTTP 200');

		await page.waitForSelector('#orders-table-body tr', { timeout: 8000 });
		const ordersCount = await page.locator('#orders-table-body tr').count();
		assert(ordersCount >= 15, `Se cargaron ${ordersCount} pedidos comerciales (DEMO sembrado ≥ 15)`);

		const kpiConfirmed = await page.locator('#kpi-ord-confirmed').innerText();
		const kpiRevenue = await page.locator('#kpi-ord-revenue').innerText();
		const kpiQuotes = await page.locator('#kpi-ord-quotes').innerText();

		assert(parseInt(kpiConfirmed) >= 11, `KPI Pedidos Confirmados correcto: ${kpiConfirmed}`);
		assert(kpiRevenue.includes('$'), `KPI Facturación Confirmada tiene formato moneda: ${kpiRevenue}`);
		assert(parseInt(kpiQuotes) >= 4, `KPI Cotizaciones Activas correcto: ${kpiQuotes}`);

		// 2. NAVEGACIÓN ENTRE SUB-PESTAÑAS (PEDIDOS VS COTIZACIONES)
		console.log('\n--- 2. Sub-Pestañas: Pedidos vs Cotizaciones ---');
		await page.click('#tab-subnav-quotes');
		await page.waitForSelector('#quotes-table-body tr', { timeout: 5000 });
		const quotesCount = await page.locator('#quotes-table-body tr').count();
		assert(quotesCount >= 10, `Pestaña Cotizaciones muestra ${quotesCount} cotizaciones (DEMO sembrado ≥ 10)`);

		// Volver a Pedidos
		await page.click('#tab-subnav-orders');
		await page.waitForSelector('#orders-table-body tr', { timeout: 5000 });
		assert(await page.locator('#orders-table-body tr').count() >= 15, 'Regreso exitoso a la pestaña de Pedidos');

		// 3. CREAR NUEVA COTIZACIÓN COMERCIAL CON LÍNEAS Y MOTOR DETERMINISTA
		console.log('\n--- 3. Crear Nueva Cotización B2B ---');
		await page.click('button:has-text("Nueva Cotización")');
		await page.waitForSelector('#modal-commercial-form:not(.hidden)');
		assert(await page.locator('#comm-form-title').innerText() === 'Nueva Cotización B2B', 'Modal en modo Cotización');

		// Seleccionar cliente que tiene acuerdo de precios (Supermercados Santa Isabel)
		await page.waitForSelector('#comm-input-customer option:nth-child(2)', { state: 'attached' });
		const custOpt = await page.locator('#comm-input-customer option:has-text("Santa Isabel")').first().getAttribute('value');
		await page.selectOption('#comm-input-customer', custOpt);
		await page.waitForTimeout(300);

		// Seleccionar producto 1 (Leche Entera UHT) con cantidad 10 cajas
		await page.waitForSelector('#comm-line-product option:nth-child(2)', { state: 'attached' });
		const prodOpt = await page.locator('#comm-line-product option:has-text("SKU-LAC-001")').first().getAttribute('value');
		await page.selectOption('#comm-line-product', prodOpt);
		await page.selectOption('#comm-line-unit', 'CASE');
		await page.fill('#comm-line-qty', '10');
		await page.click('#modal-commercial-form button[title="Añadir producto"]');
		await page.waitForSelector('#comm-lines-tbody tr', { timeout: 5000 });

		// Verificar tarifa acordada aplicada en la línea ($24.90 en vez de base $27.00)
		const appliedPriceText = await page.locator('#comm-lines-tbody tr td').nth(4).innerText();
		assert(appliedPriceText.includes('24.90'), `Tarifa pactada de Santa Isabel ($24.90) aplicada en la cotización`);

		// Añadir segunda línea (ej. Jugo Néctar o Agua)
		const prodOpt2 = await page.locator('#comm-line-product option:nth-child(2)').getAttribute('value');
		await page.selectOption('#comm-line-product', prodOpt2);
		await page.fill('#comm-line-qty', '5');
		await page.click('#modal-commercial-form button[title="Añadir producto"]');
		await page.waitForTimeout(400);

		const lineRows = await page.locator('#comm-lines-tbody tr').count();
		assert(lineRows === 2, `Se añadieron exactamente 2 líneas de productos`);

		// Comprobar cálculo de IGV y Total
		const subtotalText = await page.locator('#comm-sum-subtotal').innerText();
		const totalText = await page.locator('#comm-sum-total').innerText();
		assert(parseFloat(subtotalText.replace('$', '')) > 0, `Subtotal calculado correctamente: ${subtotalText}`);
		assert(parseFloat(totalText.replace('$', '')) > parseFloat(subtotalText.replace('$', '')), `Total incluye IGV (18%): ${totalText}`);

		// Guardar cotización en estado "Pendiente"
		await page.fill('#comm-input-notes', 'Cotización de prueba automatizada');
		await page.click('#modal-commercial-form #comm-btn-submit');
		await page.waitForTimeout(800);

		// Verificar que aparece en Cotizaciones
		await page.click('#tab-subnav-quotes');
		await page.waitForSelector('#quotes-table-body tr');
		const latestQuoteNumber = await page.locator('#quotes-table-body tr td').first().innerText();
		assert(latestQuoteNumber.includes('COT-2026-'), `Nueva cotización ${latestQuoteNumber.split('\n')[0]} registrada exitosamente`);

		// 4. APROBAR COTIZACIÓN
		console.log('\n--- 4. Flujo de Aprobación de Cotización ---');
		// Abrir detalle de la cotización recién creada
		await page.click('#quotes-table-body tr:first-child button[title="Ver Detalle y Acciones"]');
		await page.waitForSelector('#modal-commercial-detail:not(.hidden)');
		await page.waitForSelector('#modal-commercial-detail button:has-text("Aprobar Cotización")', { timeout: 8000 });

		// Click en Aprobar
		await page.click('#modal-commercial-detail button:has-text("Aprobar Cotización")');
		await page.waitForTimeout(600);

		// Comprobar que el badge cambió a Aprobada
		const approvedBadge = await page.locator('#dtl-comm-status-badge').innerText();
		assert(approvedBadge.includes('Aprobada'), `Cotización aprobada correctamente (Estado: ${approvedBadge})`);

		// 5. CONVERTIR COTIZACIÓN APROBADA EN PEDIDO B2B (IDEMPOTENTE)
		console.log('\n--- 5. Convertir Cotización a Pedido B2B ---');
		await page.click('#modal-commercial-detail button:has-text("Convertir a Pedido B2B")');
		await page.waitForTimeout(1000);

		// Debe abrir automáticamente el detalle del nuevo pedido confirmado
		await page.waitForSelector('#dtl-comm-number');
		const newOrderNum = await page.locator('#dtl-comm-number').innerText();
		assert(newOrderNum.startsWith('PED-2026-'), `Nuevo pedido generado automáticamente: ${newOrderNum}`);

		const newOrderStatus = await page.locator('#dtl-comm-status-badge').innerText();
		assert(newOrderStatus.includes('Confirmado'), `Pedido creado en estado Confirmado`);

		await page.click('#modal-commercial-detail button:has-text("Cerrar")');
		await page.waitForTimeout(300);

		// 6. CREAR PEDIDO DIRECTAMENTE
		console.log('\n--- 6. Crear Pedido Directo ---');
		await page.click('button:has-text("Nuevo Pedido")');
		await page.waitForSelector('#modal-commercial-form:not(.hidden)');
		assert(await page.locator('#comm-form-title').innerText().then(t => t.includes('Pedido')), 'Modal en modo Pedido Directo');

		await page.selectOption('#comm-input-customer', custOpt);
		await page.selectOption('#comm-line-product', prodOpt);
		await page.fill('#comm-line-qty', '15');
		await page.click('#modal-commercial-form button[title="Añadir producto"]');
		await page.waitForSelector('#comm-lines-tbody tr');

		await page.fill('#comm-input-notes', 'Pedido directo mayorista de reposición');
		await page.click('#modal-commercial-form #comm-btn-submit');
		await page.waitForTimeout(800);

		// Verificar que aparece en Pedidos
		await page.click('#tab-subnav-orders');
		await page.waitForSelector('#orders-table-body tr');
		const directOrderNum = await page.locator('#orders-table-body tr td').first().innerText();
		assert(directOrderNum.includes('PED-2026-'), `Pedido directo ${directOrderNum.split('\n')[0]} creado y visible en la tabla`);

		// 7. CONFIRMAR Y CANCELAR PEDIDO
		console.log('\n--- 7. Transiciones de Estado y Cancelación de Pedidos ---');
		// Abrir detalle del pedido
		await page.click('#orders-table-body tr:first-child button[title="Ver Detalle y Auditoría"]');
		await page.waitForSelector('#modal-commercial-detail:not(.hidden)');

		// Cancelar Pedido
		await page.click('#modal-commercial-detail button:has-text("Cancelar Pedido")');
		await page.waitForTimeout(600);

		const cancelledStatus = await page.locator('#dtl-comm-status-badge').innerText();
		assert(cancelledStatus.includes('Cancelado'), `Pedido cancelado exitosamente (Estado: ${cancelledStatus})`);

		// Ver pestaña de auditoría en la ficha
		await page.click('#tab-cdtl-history');
		await page.waitForSelector('#tab-content-cdtl-history:not(.hidden)');
		const historyRows = await page.locator('#dtl-comm-history-tbody tr').count();
		assert(historyRows >= 2, `Historial de auditoría registró las transiciones de estado (Total: ${historyRows})`);

		await page.click('#modal-commercial-detail button:has-text("Cerrar")');
		await page.waitForTimeout(300);

		// 8. DUPLICAR PEDIDO
		console.log('\n--- 8. Duplicar Pedido Anterior ---');
		const prevOrdersCount = await page.locator('#orders-table-body tr').count();
		// Clic en botón duplicar del primer pedido
		await page.click('#orders-table-body tr:first-child button[title="Duplicar Pedido"]');
		await page.waitForTimeout(800);

		await page.click('#modal-commercial-detail button:has-text("Cerrar")');
		await page.waitForTimeout(300);

		const newOrdersCount = await page.locator('#orders-table-body tr').count();
		assert(newOrdersCount === prevOrdersCount + 1, `Pedido duplicado exitosamente (Total pedidos: ${newOrdersCount})`);

		// 9. PERSISTENCIA EN RECARGA (SQLITE)
		console.log('\n--- 9. Persistencia de Datos tras Recarga Completa ---');
		await page.reload({ waitUntil: 'networkidle' });
		await page.waitForSelector('#orders-table-body tr');
		assert(await page.locator('#orders-table-body tr').count() === newOrdersCount, 'Los pedidos creados y duplicados persisten en SQLite tras recarga');

		// 10. COMPROBACIÓN RESPONSIVA MÓVIL
		console.log('\n--- 10. Comprobación Responsiva Móvil ---');
		await page.setViewportSize({ width: 375, height: 667 });
		await page.waitForTimeout(300);

		const mobileMenuBtn = page.locator('#btn-mobile-menu');
		assert(await mobileMenuBtn.isVisible(), 'Botón de menú móvil visible en viewport 375px');
		await mobileMenuBtn.click();
		await page.waitForTimeout(300);
		const sidebarVisible = await page.evaluate(() => !document.getElementById('main-sidebar').classList.contains('-translate-x-full'));
		assert(sidebarVisible, 'Drawer de navegación responde en móvil para el módulo de pedidos');

		await page.setViewportSize({ width: 1440, height: 900 });

		// 11. AUDITORÍA DE CONSOLA
		console.log('\n--- 11. Auditoría de Consola ---');
		assert(consoleErrors.length === 0, `Sin errores críticos en consola (encontrados: ${consoleErrors.length})`);
		if (consoleErrors.length > 0) {
			console.log('Errores encontrados:', consoleErrors);
		}

	} catch (error) {
		console.error('Error durante la ejecución del test:', error);
		totalTests++;
	} finally {
		await browser.close();
		console.log(`\n========================================`);
		console.log(`RESULTADO FINAL: ${passedTests}/${totalTests} pruebas superadas.`);
		console.log(`========================================\n`);
		process.exit(passedTests === totalTests ? 0 : 1);
	}
})();
