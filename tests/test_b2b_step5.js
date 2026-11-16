const { chromium } = require('playwright');

(async () => {
	console.log('=== INICIANDO VALIDACIÓN PLAYWRIGHT: PASO 5 — PRODUCTOS Y PRECIOS B2B ===\n');

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
		// 1. CARGA INICIAL DE LA RUTA /products Y PERSISTENCIA DE DATOS DEMO
		console.log('\n--- 1. Carga de /products y Métricas del Catálogo ---');
		const res = await page.goto('http://127.0.0.1:8080/products', { waitUntil: 'networkidle' });
		assert(res.status() === 200, 'Ruta /products responde HTTP 200');

		await page.waitForSelector('#prod-table-body tr', { timeout: 8000 });
		const rowsCount = await page.locator('#prod-table-body tr').count();
		assert(rowsCount >= 20, `Se cargaron ${rowsCount} productos comerciales en el catálogo (DEMO sembrado)`);

		const kpiTotal = await page.locator('#kpi-prod-total').innerText();
		const kpiCat = await page.locator('#kpi-prod-categories').innerText();
		const kpiBrands = await page.locator('#kpi-prod-brands').innerText();
		const kpiAgr = await page.locator('#kpi-prod-agreements').innerText();

		assert(parseInt(kpiTotal) >= 20, `KPI Total Productos correcto: ${kpiTotal}`);
		assert(parseInt(kpiCat) >= 6, `KPI Categorías Comerciales (mínimo 6): ${kpiCat}`);
		assert(parseInt(kpiBrands) >= 10, `KPI Marcas Comerciales (mínimo 10): ${kpiBrands}`);
		assert(parseInt(kpiAgr) >= 3, `KPI Acuerdos de Precios iniciales: ${kpiAgr}`);

		// 2. FILTRADO Y BÚSQUEDA
		console.log('\n--- 2. Búsqueda y Filtros de Productos ---');
		// Filtro por Categoría "Lácteos"
		await page.selectOption('#prod-filter-category', 'Lácteos');
		await page.waitForTimeout(300);
		const dairyCount = await page.locator('#prod-table-body tr').count();
		assert(dairyCount >= 4, `Filtro por categoría Lácteos arrojó ${dairyCount} productos`);

		// Reset filtros
		await page.click('button[title="Restablecer filtros"]');
		await page.waitForTimeout(300);
		const resetCount = await page.locator('#prod-table-body tr').count();
		assert(resetCount >= 20, `Restablecer filtros restaura ${resetCount} productos`);

		// Búsqueda por SKU
		await page.fill('#prod-search', 'SKU-LAC-001');
		await page.waitForTimeout(350);
		const searchCount = await page.locator('#prod-table-body tr').count();
		assert(searchCount === 1, `Búsqueda por SKU exacto aisló 1 producto`);
		const foundSku = await page.locator('#prod-table-body tr td').first().innerText();
		assert(foundSku.includes('SKU-LAC-001'), `Producto encontrado tiene SKU-LAC-001`);

		// Limpiar búsqueda
		await page.fill('#prod-search', '');
		await page.waitForTimeout(350);

		// 3. VALIDACIÓN DE UNICIDAD DE SKU AL CREAR PRODUCTO
		console.log('\n--- 3. Registro de Nuevo Producto y Validación de SKU ---');
		await page.click('button:has-text("Nuevo Producto")');
		await page.waitForSelector('#modal-prod-form:not(.hidden)');

		// Intentar duplicar SKU-LAC-001
		await page.fill('#prod-input-sku', 'SKU-LAC-001');
		await page.fill('#prod-input-name', 'Producto Duplicado Test');
		await page.fill('#prod-input-brand', 'Gloria');
		await page.selectOption('#prod-input-category', 'Lácteos');
		await page.fill('#prod-input-price-case', '25.00');

		// Interceptar alert de navegador
		let dialogMsg = '';
		page.once('dialog', async dialog => {
			dialogMsg = dialog.message();
			await dialog.dismiss();
		});

		await page.click('#btn-save-prod');
		await page.waitForTimeout(500);
		assert(dialogMsg.includes('ya existe') || dialogMsg.includes('SKU'), `Rechazo correcto de SKU duplicado: "${dialogMsg}"`);

		// Ahora registrar con SKU único
		const timestamp = Date.now().toString().slice(-4);
		const newSku = `SKU-TST-${timestamp}`;
		const newName = `Queso Parmesano Rallado ${timestamp} 1 kg`;

		await page.fill('#prod-input-sku', newSku);
		await page.fill('#prod-input-name', newName);
		await page.fill('#prod-input-barcode', `7750123${timestamp}`);
		await page.fill('#prod-input-brand', 'Laive');
		await page.selectOption('#prod-input-category', 'Lácteos');
		await page.fill('#prod-input-unit-name', 'Bolsa (1 kg)');
		await page.fill('#prod-input-case-name', 'Caja x 10 bolsas');
		await page.fill('#prod-input-conversion', '10');
		await page.fill('#prod-input-price-case', '120.00');
		await page.fill('#prod-input-price-unit', '13.50');
		await page.fill('#prod-input-desc', 'Queso madurado rallado especial para gastronomía.');

		await page.click('#btn-save-prod');
		await page.waitForTimeout(800);

		// Verificar que se insertó y aparece en la tabla
		await page.fill('#prod-search', newSku);
		await page.waitForTimeout(350);
		const createdCount = await page.locator('#prod-table-body tr').count();
		assert(createdCount === 1, `Nuevo producto ${newSku} creado y visualizado correctamente`);

		// 4. GESTIÓN DE TARIFAS: ESCALAS POR VOLUMEN Y ACUERDOS DE CLIENTES
		console.log('\n--- 4. Configuración de Escalas de Volumen y Acuerdos Comerciales ---');
		// Abrir modal de detalle comercial para el nuevo producto
		await page.click('#prod-table-body tr button[title="Gestionar Tarifas y Escalas"]');
		await page.waitForSelector('#modal-prod-detail:not(.hidden)');
		await page.waitForFunction(sku => document.getElementById('dtl-prod-sku')?.textContent === sku, newSku);

		const detailSku = await page.locator('#dtl-prod-sku').innerText();
		assert(detailSku === newSku, `Modal de tarifas abierto para producto ${newSku}`);

		// Tab 1: Añadir Escala de Volumen (≥ 5 cajas -> $112.00)
		await page.fill('#tier-min-qty', '5');
		await page.fill('#tier-price-case', '112.00');
		await page.fill('#tier-discount-pct', '6.67');
		await page.click('#form-add-tier button[type="submit"]');
		await page.waitForSelector('#dtl-tiers-tbody tr td:has-text("112.00")', { timeout: 8000 });

		const tierRows = await page.locator('#dtl-tiers-tbody tr').count();
		assert(tierRows >= 1, `Escala de volumen añadida y mostrada en tabla (Total: ${tierRows})`);
		const tierPriceText = await page.locator('#dtl-tiers-tbody tr td').nth(1).innerText();
		assert(tierPriceText.includes('112.00'), `Precio de escala verificado en $112.00`);

		// Tab 2: Añadir Acuerdo Comercial por Cliente (Santa Isabel -> $108.00)
		await page.click('#tab-btn-agreements');
		await page.waitForSelector('#tab-content-agreements:not(.hidden)');
		await page.waitForSelector('#agr-customer-select option:nth-child(2)', { state: 'attached', timeout: 5000 });

		// Seleccionar primer cliente disponible
		const customerOptions = await page.locator('#agr-customer-select option').count();
		assert(customerOptions > 1, `Selector de clientes poblado con ${customerOptions - 1} opciones`);

		const selectedCustId = await page.locator('#agr-customer-select option').nth(1).getAttribute('value');
		await page.selectOption('#agr-customer-select', selectedCustId);
		await page.fill('#agr-price-case', '108.00');
		await page.fill('#agr-price-unit', '11.80');
		await page.fill('#agr-notes', 'Tarifa VIP Cadena');
		await page.click('#form-add-agreement button[type="submit"]');
		await page.waitForSelector('#dtl-agreements-tbody tr td:has-text("108.00")', { timeout: 8000 });

		const agrRows = await page.locator('#dtl-agreements-tbody tr').count();
		assert(agrRows >= 1, `Acuerdo de cliente añadido y mostrado en tabla (Total: ${agrRows})`);
		const agrPriceText = await page.locator('#dtl-agreements-tbody tr td').nth(2).innerText();
		assert(agrPriceText.includes('108.00'), `Precio pactado en acuerdo verificado en $108.00`);

		// Cerrar modal de detalle
		await page.click('#modal-prod-detail button:has-text("Cerrar")');
		await page.waitForTimeout(300);

		// 5. MOTOR DETERMINISTA DE PRECIOS (SIMULADOR / COTIZADOR)
		console.log('\n--- 5. Verificación del Motor Determinista de Precios ---');
		await page.click('button:has-text("Simulador de Precios")');
		await page.waitForSelector('#modal-pricing-calc:not(.hidden)');
		await page.waitForSelector(`#calc-product-select option[value]`, { state: 'attached' });

		// Seleccionar el producto que acabamos de crear
		const prodOpt = await page.locator(`#calc-product-select option:has-text("${newSku}")`).getAttribute('value');
		await page.selectOption('#calc-product-select', prodOpt);

		// Caso A: Sin cliente, 1 caja -> Aplica PRECIO DE LISTA GENERAL ($120.00)
		await page.selectOption('#calc-customer-select', '');
		await page.selectOption('#calc-unit-select', 'CASE');
		await page.fill('#calc-quantity-input', '1');
		await page.waitForTimeout(300);

		let ruleBadge = await page.locator('#calc-rule-badge').innerText();
		let appliedRate = await page.locator('#calc-applied-rate').innerText();
		let subtotal = await page.locator('#calc-subtotal').innerText();
		assert(ruleBadge.includes('PRECIO DE LISTA'), `Caso A (1 cja): Regla aplicada = PRECIO DE LISTA`);
		assert(appliedRate.includes('120.00'), `Caso A: Tarifa aplicada = $120.00`);
		assert(subtotal.includes('120.00'), `Caso A: Subtotal = $120.00`);

		// Caso B: Sin cliente, 5 cajas -> Aplica ESCALA DE VOLUMEN ($112.00)
		await page.fill('#calc-quantity-input', '5');
		await page.waitForTimeout(300);

		ruleBadge = await page.locator('#calc-rule-badge').innerText();
		appliedRate = await page.locator('#calc-applied-rate').innerText();
		subtotal = await page.locator('#calc-subtotal').innerText();
		let savings = await page.locator('#calc-savings').innerText();
		assert(ruleBadge.includes('ESCALA DE VOLUMEN'), `Caso B (5 cjas): Regla aplicada = ESCALA DE VOLUMEN`);
		assert(appliedRate.includes('112.00'), `Caso B: Tarifa reducida por escala = $112.00`);
		assert(subtotal.includes('560.00'), `Caso B: Subtotal (5 x 112) = $560.00`);
		assert(savings.includes('40.00'), `Caso B: Ahorro calculado correctamente (5 x 8) = $40.00`);

		// Caso C: Con cliente pactado (selectedCustId), 5 cajas -> Jerarquía estricta: ACUERDO COMERCIAL ($108.00) prevalece
		await page.selectOption('#calc-customer-select', selectedCustId);
		await page.waitForTimeout(300);

		ruleBadge = await page.locator('#calc-rule-badge').innerText();
		appliedRate = await page.locator('#calc-applied-rate').innerText();
		subtotal = await page.locator('#calc-subtotal').innerText();
		savings = await page.locator('#calc-savings').innerText();
		assert(ruleBadge.includes('ACUERDO COMERCIAL'), `Caso C (Cliente pactado): Regla aplicada = ACUERDO COMERCIAL (prevalece sobre escala)`);
		assert(appliedRate.includes('108.00'), `Caso C: Tarifa pactada de cliente = $108.00`);
		assert(subtotal.includes('540.00'), `Caso C: Subtotal con tarifa de cliente = $540.00`);
		assert(savings.includes('60.00'), `Caso C: Ahorro frente a precio base = $60.00`);

		await page.click('#modal-pricing-calc button:has-text("Aceptar")');
		await page.waitForTimeout(300);

		// 6. ACTIVAR / DESACTIVAR PRODUCTO CON CONFIRMACIÓN
		console.log('\n--- 6. Alternar Estado Activo / Inactivo ---');
		await page.fill('#prod-search', newSku);
		await page.waitForTimeout(350);

		// Clic en desactivar
		await page.click('#prod-table-body tr button[title="Desactivar"]');
		await page.waitForSelector('#modal-confirm:not(.hidden)');
		assert(await page.locator('#confirm-title').innerText() === 'Desactivar Producto Comercial', 'Modal de confirmación muestra título de desactivación');

		await page.click('#btn-confirm-action');
		await page.waitForTimeout(500);

		const inactBadge = await page.locator('#prod-table-body tr td').nth(7).innerText();
		assert(inactBadge.includes('Inactivo'), `Producto marcado correctamente como Inactivo`);

		// 7. RESPONSIVIDAD MÓVIL Y ACCESIBILIDAD
		console.log('\n--- 7. Comprobación Responsiva Móvil ---');
		await page.setViewportSize({ width: 375, height: 667 });
		await page.waitForTimeout(300);

		const mobileMenuBtn = page.locator('#btn-mobile-menu');
		assert(await mobileMenuBtn.isVisible(), 'Botón de menú móvil visible en viewport 375px');

		await mobileMenuBtn.click();
		await page.waitForTimeout(300);
		const sidebarVisible = await page.evaluate(() => {
			const s = document.getElementById('main-sidebar');
			return !s.classList.contains('-translate-x-full');
		});
		assert(sidebarVisible, 'Drawer de navegación se desliza correctamente en móvil');

		// Restaurar escritorio
		await page.setViewportSize({ width: 1440, height: 900 });

		// 8. ERRORES DE CONSOLA CRÍTICOS
		console.log('\n--- 8. Auditoría de Consola ---');
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
