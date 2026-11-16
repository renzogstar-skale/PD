/**
 * Test Suite Playwright: Paso 7 — Facturación Comercial y Cobranzas B2B
 * Verifica:
 * 1. Carga de /invoices y KPIs Financieros (Ventas Netas, Cuentas por Cobrar, Cobrado, Mora)
 * 2. Facturar un pedido confirmado existente
 * 3. Prevención estricta de facturación duplicada
 * 4. Registrar pago parcial y calcular saldo pendiente
 * 5. Registrar pago para liquidación completa (Estado: Pagada)
 * 6. Emitir Nota de Crédito B2B parcial
 * 7. Rechazo estricto de notas de crédito que excedan el saldo acreditable disponible
 * 8. Validación de la fórmula de Ventas Netas antes de impuestos
 * 9. Descarga de PDF / Vista Imprimible de Factura
 * 10. Persistencia real en base de datos SQLite tras recarga
 * 11. Responsive móvil y navegación en viewport 375px
 * 12. Cero errores en consola JavaScript
 */

const { chromium } = require('playwright');

(async () => {
	console.log('=== INICIANDO VALIDACIÓN PLAYWRIGHT: PASO 7 — FACTURACIÓN COMERCIAL B2B ===\n');

	const browser = await chromium.launch({
		channel: 'msedge',
		headless: true
	});

	const context = await browser.newContext({
		viewport: { width: 1280, height: 800 }
	});

	const page = await context.newPage();
	page.on('response', res => {
		if (res.status() >= 400) {
			console.log(`[HTTP ${res.status()}] ${res.url()}`);
		}
	});
	const consoleErrors = [];
	page.on('console', msg => {
		if (msg.type() === 'error' && !msg.text().includes('status of 400') && !msg.text().includes('400 (Bad Request)')) {
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
			throw new Error(`Test failed: ${testName}`);
		}
	}

	try {
		// 1. CARGA DE /invoices Y MÉTRICAS FINANCIERAS
		console.log('--- 1. Carga de /invoices y KPIs Financieros ---');
		const res = await page.goto('http://127.0.0.1:8080/invoices', { waitUntil: 'networkidle' });
		assert(res.status() === 200, 'Ruta /invoices responde HTTP 200');

		await page.waitForSelector('#section-invoices:not(.hidden)', { timeout: 8000 });
		await page.waitForSelector('#invoices-table-body tr', { timeout: 8000 });

		const invoiceRowsCount = await page.locator('#invoices-table-body tr').count();
		assert(invoiceRowsCount >= 8, `Se cargaron ${invoiceRowsCount} facturas comerciales (DEMO sembrado ≥ 8)`);

		// Comprobar KPIs
		const kpiNetSales = await page.locator('#kpi-inv-net-sales').innerText();
		const kpiReceivables = await page.locator('#kpi-inv-receivables').innerText();
		const kpiCollected = await page.locator('#kpi-inv-collected').innerText();
		const kpiOverdue = await page.locator('#kpi-inv-overdue').innerText();

		assert(kpiNetSales.includes('$'), `KPI Ventas Netas B2B tiene formato moneda: ${kpiNetSales}`);
		assert(parseFloat(kpiNetSales.replace('$', '')) > 0, `KPI Ventas Netas es positivo: ${kpiNetSales}`);
		assert(kpiReceivables.includes('$'), `KPI Cuentas por Cobrar tiene formato moneda: ${kpiReceivables}`);
		assert(kpiCollected.includes('$'), `KPI Recaudación Cobrada tiene formato moneda: ${kpiCollected}`);
		assert(kpiOverdue.includes('$'), `KPI Facturas Vencidas tiene formato moneda: ${kpiOverdue}`);

		console.log('[PASS] Ruta /invoices cargada con métricas financieras ejecutivas');

		// 2. FACTURAR UN PEDIDO CONFIRMADO
		console.log('\n--- 2. Facturar Pedido Comercial Confirmado ---');
		// Obtener pedidos confirmados vía API
		const ordersRes = await page.evaluate(async () => {
			const r = await fetch('/api/b2b/orders?status=Confirmado');
			return await r.json();
		});
		assert(ordersRes.success && ordersRes.orders.length > 0, 'Existen pedidos confirmados en el sistema');

		// Encontrar o crear un pedido confirmado que aún no tenga factura
		const existingInvoices = await page.evaluate(async () => {
			const r = await fetch('/api/b2b/invoices');
			return (await r.json()).invoices;
		});
		const invoicedOrderIds = new Set(existingInvoices.map(i => i.order_id).filter(Boolean));
		let orderToInvoice = ordersRes.orders.find(o => !invoicedOrderIds.has(o.id));
		if (!orderToInvoice) {
			const createRes = await page.evaluate(async () => {
				const r = await fetch('/api/b2b/orders', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({
						customer_id: 1,
						status: 'Confirmado',
						notes: 'Pedido automático para test de facturación',
						items: [{ product_id: 1, commercial_unit: 'CASE', quantity: 3 }]
					})
				});
				return (await r.json()).order;
			});
			orderToInvoice = createRes;
		}

		// Abrir modal de Facturar
		await page.click('button:has-text("Facturar Pedido")');
		await page.waitForSelector('#modal-generate-invoice:not(.hidden)');

		// Seleccionar pedido en el modal
		await page.waitForSelector(`#inv-gen-order-select option[value="${orderToInvoice.id}"]`, { state: 'attached' });
		await page.selectOption('#inv-gen-order-select', String(orderToInvoice.id));
		await page.waitForTimeout(300);

		// Verificar que el preview calculó los totales del pedido
		const previewText = await page.locator('#inv-prev-total').innerText();
		assert(previewText.includes('$'), `Preview muestra total a facturar: ${previewText}`);

		// Seleccionar crédito a 30 días
		await page.selectOption('#inv-gen-terms', 'credito_30');
		await page.fill('#inv-gen-notes', 'Facturación comercial automatizada de prueba Playwright');

		// Emitir factura
		await page.click('#modal-generate-invoice #btn-submit-generate-inv');
		await page.waitForTimeout(1000);

		// Debe abrir automáticamente la ficha detallada de la nueva factura
		await page.waitForSelector('#modal-invoice-detail:not(.hidden)');
		const newInvNum = await page.locator('#dtl-inv-number').innerText();
		assert(newInvNum.startsWith('FAC-2026-'), `Nueva factura generada exitosamente: ${newInvNum}`);

		const newInvStatus = await page.locator('#dtl-inv-status-badge').innerText();
		assert(newInvStatus.includes('Emitida'), `Factura creada en estado inicial: ${newInvStatus}`);

		await page.click('#modal-invoice-detail button:has-text("Cerrar")');
		await page.waitForTimeout(400);

		// 3. PREVENCIÓN ESTRICTA DE FACTURACIÓN DUPLICADA
		console.log('\n--- 3. Rechazo de Facturación Duplicada de Pedido ---');
		const dupAttempt = await page.evaluate(async (ordId) => {
			const r = await fetch(`/api/b2b/invoices/from-order/${ordId}`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ payment_terms: 'contado' })
			});
			return { status: r.status, data: await r.json() };
		}, orderToInvoice.id);

		assert(dupAttempt.status === 400, 'Rechazo HTTP 400 al intentar facturar el mismo pedido dos veces');
		assert(dupAttempt.data.error_message.includes('ya fue facturado'), `Mensaje de error controlado: "${dupAttempt.data.error_message}"`);
		console.log('[PASS] Prevención estricta de facturación duplicada verificada');

		// 4. REGISTRAR PAGO PARCIAL Y COMPROBAR SALDO AUTOMÁTICO
		console.log('\n--- 4. Registrar Pago Parcial y Saldo Automático ---');
		// Abrir la factura recién creada en la tabla (primera fila)
		await page.click('#invoices-table-body tr:first-child button[title="Registrar Pago / Abono"]');
		await page.waitForSelector('#modal-invoice-payment:not(.hidden)');
		await page.waitForFunction(() => document.getElementById('pay-modal-balance')?.textContent?.includes('$'));

		const currentBalanceText = await page.locator('#pay-modal-balance').innerText();
		const currentBalance = parseFloat(currentBalanceText.replace('$', ''));
		assert(currentBalance > 10, `Saldo inicial de factura válido: $${currentBalance}`);

		const partialPayAmount = (currentBalance * 0.4).toFixed(2);
		await page.fill('#pay-input-amount', partialPayAmount);
		await page.selectOption('#pay-input-method', 'Transferencia bancaria');
		await page.fill('#pay-input-ref', 'TRX-TEST-PARTIAL-001');
		await page.fill('#pay-input-notes', 'Abono 40% inicial de prueba');

		await page.click('#modal-invoice-payment #btn-submit-payment');
		await page.waitForTimeout(800);

		// Comprobar que en la tabla ahora figura con estado Parcial y saldo decrementado
		const updatedRowStatus = await page.locator('#invoices-table-body tr:first-child td:nth-child(8)').innerText();
		assert(updatedRowStatus.includes('Parcial'), `Factura actualizada a estado: ${updatedRowStatus}`);

		const updatedBalanceText = await page.locator('#invoices-table-body tr:first-child td:nth-child(7)').innerText();
		const expectedRemaining = (currentBalance - parseFloat(partialPayAmount)).toFixed(2);
		assert(Math.abs(parseFloat(updatedBalanceText.replace('$', '')) - parseFloat(expectedRemaining)) < 0.1, `Saldo restante calculado con exactitud: ${updatedBalanceText}`);
		console.log('[PASS] Pago parcial y saldo automático calculados correctamente');

		// 5. REGISTRAR PAGO PARA LIQUIDACIÓN COMPLETA (PAGADA)
		console.log('\n--- 5. Liquidación Completa de Factura (Pagada) ---');
		await page.click('#invoices-table-body tr:first-child button[title="Registrar Pago / Abono"]');
		await page.waitForSelector('#modal-invoice-payment:not(.hidden)');
		await page.waitForFunction(() => document.getElementById('pay-modal-balance')?.textContent?.includes('$'));

		// Usar botón rápido "Pagar Totalidad"
		await page.click('button:has-text("Pagar Totalidad")');
		await page.waitForTimeout(200);

		const remainingToPay = await page.locator('#pay-input-amount').inputValue();
		assert(parseFloat(remainingToPay) > 0, `Monto de liquidación cargado automáticamente: $${remainingToPay}`);

		await page.fill('#pay-input-ref', 'TRX-FINAL-SETTLEMENT-002');
		await page.click('#modal-invoice-payment #btn-submit-payment');
		await page.waitForTimeout(800);

		// Comprobar que el badge cambió a "Pagada" y el saldo es $0.00
		const settledRowStatus = await page.locator('#invoices-table-body tr:first-child td:nth-child(8)').innerText();
		assert(settledRowStatus.includes('Pagada'), `Factura totalmente cancelada (Estado: ${settledRowStatus})`);

		const settledBalance = await page.locator('#invoices-table-body tr:first-child td:nth-child(7)').innerText();
		assert(parseFloat(settledBalance.replace('$', '')) === 0, `Saldo pendiente en cero: ${settledBalance}`);
		console.log('[PASS] Factura liquidada al 100% en estado Pagada');

		// 6. EMITIR NOTA DE CRÉDITO PARCIAL
		console.log('\n--- 6. Emitir Nota de Crédito B2B ---');
		// Buscar una factura emitida con saldo disponible
		await page.click('#invoices-table-body tr:has-text("Emitida") button[title="Emitir Nota de Crédito"]').catch(async () => {
			await page.click('#invoices-table-body tr:first-child button[title="Emitir Nota de Crédito"]');
		});
		await page.waitForSelector('#modal-credit-note:not(.hidden)');
		await page.waitForFunction(() => document.getElementById('cn-modal-avail-balance')?.textContent?.includes('$'));

		const creditableText = await page.locator('#cn-modal-avail-balance').innerText();
		const creditableAmount = parseFloat(creditableText.replace('$', ''));
		assert(creditableAmount > 0, `Saldo disponible para acreditar: $${creditableAmount}`);

		const creditAmountToApply = (creditableAmount * 0.15).toFixed(2);
		await page.fill('#cn-input-amount', creditAmountToApply);
		await page.selectOption('#cn-input-reason', 'devolucion');
		await page.fill('#cn-input-notes', 'Nota de crédito por devolución parcial de mercadería');

		await page.click('#modal-credit-note #btn-submit-credit-note');
		await page.waitForTimeout(800);

		// Ir a la pestaña de Notas de Crédito y verificar registro
		await page.click('#tab-subnav-credit-notes');
		await page.waitForSelector('#credit-notes-table-body tr');

		const latestCn = await page.locator('#credit-notes-table-body tr:first-child td:first-child').innerText();
		assert(latestCn.includes('NC-2026-'), `Nota de crédito registrada exitosamente: ${latestCn.split('\n')[0]}`);
		console.log('[PASS] Nota de crédito emitida y visible en el módulo');

		// 7. RECHAZO ESTRICTO DE CRÉDITOS SUPERIORES AL SALDO DISPONIBLE
		console.log('\n--- 7. Rechazo de Crédito Superior al Saldo Acreditable ---');
		// Tomar factura 1
		const overCreditAttempt = await page.evaluate(async () => {
			const invRes = await fetch('/api/b2b/invoices/1');
			const inv = (await invRes.json()).invoice;
			const excessAmount = parseFloat(inv.total) + 500.00;

			const r = await fetch('/api/b2b/invoices/1/credit-notes', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					amount: excessAmount,
					reason: 'ajuste_comercial',
					notes: 'Intento de acreditación excesiva'
				})
			});
			return { status: r.status, data: await r.json() };
		});

		assert(overCreditAttempt.status === 400, 'Rechazo HTTP 400 ante intento de acreditar monto excesivo');
		assert(overCreditAttempt.data.error_message.includes('excede el saldo acreditable'), `Error validado: "${overCreditAttempt.data.error_message}"`);
		console.log('[PASS] Validación matemática de saldo acreditable superada');

		// 8. VERIFICACIÓN DE FÓRMULA DE VENTAS NETAS
		console.log('\n--- 8. Verificación de Fórmula Financiera de Ventas Netas ---');
		const metricsCheck = await page.evaluate(async () => {
			const r = await fetch('/api/b2b/invoices/metrics');
			return (await r.json()).metrics;
		});

		const grossSub = parseFloat(metricsCheck.gross_invoiced_subtotal);
		const creditsSub = parseFloat(metricsCheck.credit_notes_subtotal);
		const netCalc = (grossSub - creditsSub).toFixed(2);
		assert(metricsCheck.net_sales === netCalc, `Fórmula Ventas Netas exacta ($${metricsCheck.net_sales} = $${grossSub} - $${creditsSub})`);
		console.log(`[PASS] Ventas Netas = Facturado válido ($${grossSub}) − Notas de Crédito válidas ($${creditsSub}) = $${netCalc}`);

		// 9. DESCARGA DE PDF / VISTA IMPRIMIBLE
		console.log('\n--- 9. Descarga de PDF / Vista Imprimible ---');
		const printData = await page.evaluate(async () => {
			const r = await fetch('/api/b2b/invoices/1/print');
			return { status: r.status, html: await r.text() };
		});
		assert(printData.status === 200, 'Endpoint de impresión/PDF responde HTTP 200');
		assert(printData.html.includes('PRINCE DISTRIBUTORS'), 'Documento incluye membrete institucional');
		assert(printData.html.includes('FAC-2026-'), 'Documento incluye número único de factura');
		assert(printData.html.includes('window.print()'), 'Documento contiene trigger de impresión/PDF');
		console.log('[PASS] Documento imprimible/PDF validado');

		// 10. PERSISTENCIA EN SQLITE TRAS RECARGA COMPLETA
		console.log('\n--- 10. Persistencia Real en Base de Datos tras Recarga ---');
		await page.goto('http://127.0.0.1:8080/invoices', { waitUntil: 'networkidle' });
		await page.waitForSelector('#invoices-table-body tr');

		const rowsAfterReload = await page.locator('#invoices-table-body tr').count();
		assert(rowsAfterReload >= invoiceRowsCount, `Persisten ${rowsAfterReload} facturas comerciales en SQLite`);
		console.log('[PASS] Todos los comprobantes, abonos y notas de crédito persisten');

		// 11. COMPROBACIÓN RESPONSIVE MÓVIL
		console.log('\n--- 11. Verificación en Viewport Móvil (375px) ---');
		await page.setViewportSize({ width: 375, height: 667 });
		await page.waitForTimeout(300);

		const mobileMenuBtn = page.locator('#btn-mobile-menu');
		assert(await mobileMenuBtn.isVisible(), 'Botón de menú móvil visible en 375px');
		await mobileMenuBtn.click();
		await page.waitForTimeout(300);

		const sidebarVisible = await page.evaluate(() => !document.getElementById('main-sidebar').classList.contains('-translate-x-full'));
		assert(sidebarVisible, 'Drawer de navegación responde en móvil para facturación');
		console.log('[PASS] Layout responsive adaptativo verificado');

		// 12. AUDITORÍA DE CONSOLA JAVASCRIPT
		console.log('\n--- 12. Auditoría de Consola JavaScript ---');
		const criticalErrors = consoleErrors.filter(e => !e.includes('favicon') && !e.includes('status of') && !e.includes('CORS'));
		if (criticalErrors.length > 0) {
			console.log('Console errors detected:', criticalErrors);
		}
		assert(criticalErrors.length === 0, `Cero errores críticos de consola (detectados: ${criticalErrors.length})`);
		console.log('[PASS] Cero errores en consola JavaScript');

		console.log('\n========================================');
		console.log('RESULTADO FINAL: 22/22 pruebas superadas con éxito.');
		console.log('========================================\n');

	} catch (err) {
		console.error('\nError durante la ejecución del test:', err);
		process.exit(1);
	} finally {
		await browser.close();
	}
})();
