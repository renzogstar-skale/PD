const { chromium } = require('playwright');

(async () => {
	console.log('=== INICIANDO VALIDACIÓN PLAYWRIGHT: PASO 4 — CLIENTES Y ALMACENES ===\n');

	const browser = await chromium.launch({ channel: 'msedge', headless: true });
	const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

	const consoleErrors = [];
	page.on('console', msg => {
		if (msg.type() === 'error') {
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
		// 1. CARGA INICIAL DE LA RUTA /customers Y PERSISTENCIA DE DATOS DEMO
		console.log('\n--- 1. Carga de /customers y Métricas Iniciales ---');
		const res = await page.goto('http://127.0.0.1:8080/customers', { waitUntil: 'networkidle' });
		assert(res.status() === 200, 'Ruta /customers responde HTTP 200');

		await page.waitForSelector('#cust-table-body tr', { timeout: 8000 });
		const rowsCount = await page.locator('#cust-table-body tr').count();
		assert(rowsCount >= 7, `Se cargaron ${rowsCount} clientes comerciales (datos demo sembrados)`);

		const activeKpi = await page.locator('#kpi-cust-active').innerText();
		const estKpi = await page.locator('#kpi-cust-est').innerText();
		assert(parseInt(activeKpi) >= 7, `KPI Clientes Activos correcto: ${activeKpi}`);
		assert(parseInt(estKpi) >= 13, `KPI Establecimientos Totales correcto: ${estKpi}`);

		// 2. CREAR UN NUEVO CLIENTE COMERCIAL (CADENA)
		console.log('\n--- 2. Crear Cliente Comercial ---');
		await page.click('button:has-text("Nuevo Cliente")');
		await page.waitForSelector('#modal-cust-form:not(.hidden)');

		const timestamp = Date.now().toString().slice(-4);
		const testClientName = `Inversiones Retail Pacífico ${timestamp} S.A.`;
		const testCommercialName = `Pacífico Express ${timestamp}`;
		const testTaxId = `RUC-2099887${timestamp}`;

		await page.fill('#cust-field-name', testClientName);
		await page.fill('#cust-field-commercial', testCommercialName);
		await page.fill('#cust-field-taxid', testTaxId);
		await page.selectOption('#cust-field-type', 'cadena');
		await page.fill('#cust-field-phone', '+51 1 700-8000');
		await page.fill('#cust-field-email', `contacto@pacifico${timestamp}.pe`);
		await page.fill('#cust-field-address', 'Av. La Marina 2100');
		await page.fill('#cust-field-city', 'San Miguel');
		await page.fill('#cust-field-state', 'Lima');

		await page.click('#btn-save-cust');
		await page.waitForTimeout(600);

		const createdRow = await page.locator(`#cust-table-body tr:has-text("${testClientName}")`);
		assert(await createdRow.isVisible(), `Cliente "${testClientName}" creado y visible en la tabla`);

		// 3. EDITAR DATOS DEL CLIENTE
		console.log('\n--- 3. Editar Datos del Cliente ---');
		await createdRow.locator('button[title="Editar Cliente"]').click();
		await page.waitForSelector('#modal-cust-form:not(.hidden)');

		const updatedCommercialName = `Pacífico Hypermarket Pro ${timestamp}`;
		await page.fill('#cust-field-commercial', updatedCommercialName);
		await page.click('#btn-save-cust');
		await page.waitForTimeout(600);

		const updatedText = await createdRow.locator('td:first-child').innerText();
		assert(updatedText.includes(updatedCommercialName), `Nombre comercial actualizado a "${updatedCommercialName}"`);

		// 4. REGISTRAR TRES (3) ESTABLECIMIENTOS PARA LA MISMA CADENA
		console.log('\n--- 4. Registrar Tres (3) Establecimientos para la Cadena ---');
		// Abrir ficha detallada del cliente recién creado
		await createdRow.locator('button[title="Ver Ficha y Sucursales"]').click();
		await page.waitForSelector('#modal-cust-detail:not(.hidden)');

		const estList = [
			{ code: `EST-PAC-01-${timestamp}`, name: 'Pacífico Sede San Miguel', address: 'Av. La Marina 2100', city: 'San Miguel', contact: 'Carlos Ramos' },
			{ code: `EST-PAC-02-${timestamp}`, name: 'Pacífico Sede Jesús María', address: 'Av. Brasil 1400', city: 'Jesús María', contact: 'Diana Vidal' },
			{ code: `EST-PAC-03-${timestamp}`, name: 'Pacífico Sede Pueblo Libre', address: 'Av. Sucre 800', city: 'Pueblo Libre', contact: 'Jorge Méndez' }
		];

		for (const est of estList) {
			await page.click('button:has-text("Agregar Establecimiento")');
			await page.waitForSelector('#modal-est-form:not(.hidden)');

			await page.fill('#est-field-code', est.code);
			await page.fill('#est-field-name', est.name);
			await page.fill('#est-field-address', est.address);
			await page.fill('#est-field-city', est.city);
			await page.fill('#est-field-state', 'Lima');
			await page.fill('#est-field-contact', est.contact);
			await page.fill('#est-field-phone', '+51 987 654 321');
			await page.fill('#est-field-email', 'compras@sucursal.pe');
			await page.fill('#est-field-salesrep', 'Carlos Mendoza');
			await page.fill('#est-field-territory', 'Lima Oeste');

			await page.click('#btn-save-est');
			await page.waitForTimeout(500);
		}

		// Comprobar que los 3 establecimientos figuran en la ficha
		const estRowCount = await page.locator('#detail-est-table-body tr').count();
		assert(estRowCount === 3, `Se registraron exactamente los 3 establecimientos en la cadena (${estRowCount} en lista)`);

		// 5. DESACTIVAR UN ESTABLECIMIENTO
		console.log('\n--- 5. Desactivar Establecimiento con Confirmación ---');
		const estRowToDeactivate = page.locator(`#detail-est-table-body tr:has-text("${estList[1].code}")`);
		await estRowToDeactivate.locator('button[title="Desactivar"]').click();
		await page.waitForSelector('#modal-confirm:not(.hidden)');

		await page.click('#btn-confirm-action');
		await page.waitForTimeout(500);

		const deactivatedBadge = await estRowToDeactivate.locator('span:has-text("Inactivo")');
		assert(await deactivatedBadge.isVisible(), `Establecimiento ${estList[1].code} marcado como Inactivo`);

		// Cerrar ficha
		await page.click('button:has-text("Cerrar Ficha")');
		await page.waitForTimeout(300);

		// 6. BUSCAR Y FILTRAR CLIENTES
		console.log('\n--- 6. Búsqueda Reactiva y Filtros ---');
		// Buscar por Tax ID
		await page.fill('#cust-search', testTaxId);
		await page.waitForTimeout(400);

		let filteredCount = await page.locator('#cust-table-body tr').count();
		assert(filteredCount === 1, `Búsqueda por Tax ID devuelve exactamente 1 cliente coincidente (${filteredCount})`);

		// Limpiar búsqueda y filtrar por tipo "cadena"
		await page.fill('#cust-search', '');
		await page.selectOption('#cust-filter-type', 'cadena');
		await page.waitForTimeout(400);

		const allChains = await page.locator('#cust-table-body tr').count();
		assert(allChains >= 4, `Filtro por tipo "cadena" arroja ${allChains} registros`);

		// Filtrar por supermercado
		await page.selectOption('#cust-filter-type', 'supermercado');
		await page.waitForTimeout(400);
		const allSupers = await page.locator('#cust-table-body tr').count();
		assert(allSupers >= 2, `Filtro por tipo "supermercado" arroja ${allSupers} registros`);

		// Resetear filtros
		await page.click('button:has-text("Limpiar")');
		await page.waitForTimeout(400);

		// 7. VERIFICACIÓN DE PERSISTENCIA REAL TRAS RECARGA
		console.log('\n--- 7. Persistencia Real en Base de Datos (Reload) ---');
		await page.reload({ waitUntil: 'networkidle' });
		await page.waitForSelector('#cust-table-body tr');

		const persistedRow = page.locator(`#cust-table-body tr:has-text("${testClientName}")`);
		assert(await persistedRow.isVisible(), `Cliente "${testClientName}" persiste en SQLite tras recarga`);

		// Abrir ficha y verificar que persisten sus 3 establecimientos (2 activos, 1 inactivo)
		await persistedRow.locator('button[title="Ver Ficha y Sucursales"]').click();
		await page.waitForSelector('#modal-cust-detail:not(.hidden)');

		const persistedEstCount = await page.locator('#detail-est-table-body tr').count();
		assert(persistedEstCount === 3, `Persisten los 3 establecimientos asociados en base de datos`);

		await page.click('button:has-text("Cerrar Ficha")');

		// 8. PRUEBA EN DISPOSITIVOS MÓVILES (VIEWPORT 375x667)
		console.log('\n--- 8. Verificación de Responsive Móvil ---');
		await page.setViewportSize({ width: 375, height: 667 });
		await page.waitForTimeout(300);

		const tableResponsiveWrapper = await page.locator('#section-customers .overflow-x-auto').isVisible();
		assert(tableResponsiveWrapper, 'Contenedor con desplazamiento horizontal activo en móvil');

		const newCustBtnMobile = await page.locator('button:has-text("Nuevo Cliente")').first().isVisible();
		assert(newCustBtnMobile, 'Botón Nuevo Cliente accesible en viewport móvil');

		// 9. VERIFICACIÓN DE CONSOLA
		console.log('\n--- 9. Verificación de Errores de Consola ---');
		const filteredErrors = consoleErrors.filter(err => !err.includes('favicon'));
		assert(filteredErrors.length === 0, `Cero errores críticos de consola JS (${filteredErrors.length} detectados)`);

	} catch (err) {
		console.error('Excepción durante la prueba:', err);
		assert(false, 'Flujo de prueba completado sin excepciones');
	} finally {
		await browser.close();
		console.log(`\n=== RESUMEN PLAYWRIGHT: ${passedTests}/${totalTests} PRUEBAS EXITOSAS ===`);
		process.exit(passedTests === totalTests ? 0 : 1);
	}
})();
