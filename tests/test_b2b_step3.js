const { chromium } = require('playwright');

(async () => {
	console.log('=== INICIANDO VALIDACIÓN PLAYWRIGHT: PASO 3 — B2B SUITE ===\n');

	const browser = await chromium.launch({ channel: 'msedge', headless: true });
	const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

	const consoleErrors = [];
	page.on('console', msg => {
		if (msg.type() === 'error') {
			consoleErrors.push(msg.text());
		}
	});
	page.on('pageerror', err => consoleErrors.push(err.message));
	page.on('response', resp => {
		if (resp.status() >= 400 && !resp.url().includes('favicon')) {
			console.log('HTTP 4xx/5xx Resource URL:', resp.status(), resp.url());
		}
	});

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
		// 1. VERIFICACIÓN DE RUTAS DIRECTAS (HTTP 200)
		console.log('\n--- 1. Verificación de Carga de Rutas ---');
		const routesToTest = [
			'/b2b',
			'/b2b/sales',
			'/b2b/customers',
			'/b2b/products',
			'/b2b/orders',
			'/b2b/sales-reps',
			'/b2b/profitability',
			'/b2b/reports',
			'/b2b/settings',
			'/sales-reps',
			'/profitability'
		];

		for (const route of routesToTest) {
			const res = await page.goto(`http://127.0.0.1:8080${route}`, { waitUntil: 'domcontentloaded' });
			assert(res.status() === 200, `Ruta ${route} responde HTTP 200`);
		}

		// 2. VERIFICACIÓN DE NAVEGACIÓN CLIENT-SIDE Y SECCIÓN ACTIVA
		console.log('\n--- 2. Verificación de Navegación SPA y Estado Activo ---');
		await page.goto('http://127.0.0.1:8080/b2b', { waitUntil: 'networkidle' });
		await page.waitForTimeout(400);

		// Verificar que Sales esté activo inicialmente
		let activeLink = await page.locator('#b2b-sidebar-nav [data-section="sales"]');
		let activeClass = await activeLink.getAttribute('class');
		assert(activeClass.includes('text-brand-700'), 'Sección inicial "sales" está activa');

		// Click en Clientes y Almacenes sin recargar página
		await page.click('#b2b-sidebar-nav [data-section="customers"]');
		await page.waitForTimeout(200);

		let url = page.url();
		assert(url.includes('/b2b/customers'), `URL actualizada a /b2b/customers sin recarga: ${url}`);

		let breadcrumb = await page.locator('#breadcrumb-current').innerText();
		assert(breadcrumb === 'Clientes y Almacenes', `Breadcrumb actualizado a "Clientes y Almacenes": ${breadcrumb}`);

		let placeholderTitle = await page.locator('#placeholder-title').innerText();
		assert(placeholderTitle === 'Clientes y Almacenes', `Título de módulo en desarrollo correcto: ${placeholderTitle}`);

		let badgeDev = await page.locator('#section-placeholder span[data-i18n="badge_in_development"]').isVisible();
		assert(badgeDev, 'Badge "Módulo en desarrollo" visible');

		// Click en Productos y Precios
		await page.click('#b2b-sidebar-nav [data-section="products"]');
		await page.waitForTimeout(200);
		url = page.url();
		assert(url.includes('/b2b/products'), `URL actualizada a /b2b/products: ${url}`);
		placeholderTitle = await page.locator('#placeholder-title').innerText();
		assert(placeholderTitle === 'Productos y Precios', `Título correcto: ${placeholderTitle}`);

		// Volver a Resumen de Ventas
		await page.click('#b2b-sidebar-nav [data-section="sales"]');
		await page.waitForTimeout(300);
		let salesVisible = await page.locator('#section-sales').isVisible();
		let placeholderHidden = await page.locator('#section-placeholder').isHidden();
		assert(salesVisible && placeholderHidden, 'Resumen de Ventas vuelve a mostrarse completamente');

		// 3. SIDEBAR COLAPSABLE Y TOOLTIPS
		console.log('\n--- 3. Verificación de Sidebar Colapsable ---');
		assert(!await page.evaluate(() => document.body.classList.contains('sidebar-collapsed')), 'Sidebar inicialmente expandido');

		await page.click('#btn-toggle-collapse');
		await page.waitForTimeout(250);
		const isCollapsed = await page.evaluate(() => document.body.classList.contains('sidebar-collapsed'));
		assert(isCollapsed, 'Sidebar colapsado correctamente (.sidebar-collapsed en body)');

		// Hover sobre item para tooltip
		await page.hover('#b2b-sidebar-nav [data-section="customers"]');
		const tooltipText = await page.locator('#b2b-sidebar-nav [data-section="customers"] .nav-tooltip').textContent();
		assert(tooltipText.includes('Clientes y Almacenes'), `Tooltip presente en item colapsado: "${tooltipText.trim()}"`);

		// Expandir de nuevo
		await page.click('#btn-toggle-collapse');
		await page.waitForTimeout(250);
		assert(!await page.evaluate(() => document.body.classList.contains('sidebar-collapsed')), 'Sidebar restaurado a expandido');

		// 4. MODO CLARO / OSCURO
		console.log('\n--- 4. Verificación de Modo Claro / Oscuro ---');
		const initialTheme = await page.evaluate(() => document.documentElement.classList.contains('dark'));
		await page.click('button[title*="tema"]');
		await page.waitForTimeout(200);
		const toggledTheme = await page.evaluate(() => document.documentElement.classList.contains('dark'));
		assert(toggledTheme !== initialTheme, 'Tema alternado con éxito (toggle light/dark)');

		// Revertir tema
		await page.click('button[title*="tema"]');
		await page.waitForTimeout(200);

		// 5. SELECTOR DE IDIOMA ES / EN
		console.log('\n--- 5. Verificación de Idioma ES / EN ---');
		await page.click('#lang-btn-en');
		await page.waitForTimeout(300);

		let salesTextEn = await page.locator('#b2b-sidebar-nav [data-section="sales"] .nav-text').innerText();
		let newOrderEn = await page.locator('header button span[data-i18n="btn_new_order"]').innerText();
		assert(salesTextEn === 'Sales Summary', `Texto cambiado a inglés en nav: "${salesTextEn}"`);
		assert(newOrderEn === 'New B2B Order', `Texto cambiado a inglés en botón: "${newOrderEn}"`);

		// Cambiar de vuelta a español
		await page.click('#lang-btn-es');
		await page.waitForTimeout(300);
		let salesTextEs = await page.locator('#b2b-sidebar-nav [data-section="sales"] .nav-text').innerText();
		assert(salesTextEs === 'Resumen de Ventas', `Texto restaurado a español: "${salesTextEs}"`);

		// 6. MENÚ MÓVIL (VIEWPORT 375x667)
		console.log('\n--- 6. Verificación de Menú Móvil Responsivo ---');
		await page.setViewportSize({ width: 375, height: 667 });
		await page.waitForTimeout(300);

		const mobileBtnVisible = await page.locator('#btn-mobile-menu').isVisible();
		assert(mobileBtnVisible, 'Botón hamburguesa móvil visible en viewport 375px');

		// Abrir menú móvil
		await page.click('#btn-mobile-menu');
		await page.waitForTimeout(300);
		let sidebarHasClass = await page.evaluate(() => {
			return !document.getElementById('main-sidebar').classList.contains('-translate-x-full');
		});
		assert(sidebarHasClass, 'Sidebar abierto off-canvas en móvil');

		// Cerrar haciendo clic en el botón cerrar
		await page.click('#btn-close-mobile-sidebar');
		await page.waitForTimeout(300);
		let sidebarClosed = await page.evaluate(() => {
			return document.getElementById('main-sidebar').classList.contains('-translate-x-full');
		});
		assert(sidebarClosed, 'Sidebar cerrado correctamente con botón cerrar');

		// 7. ERRORES DE CONSOLA
		console.log('\n--- 7. Verificación de Errores de Consola ---');
		const filteredErrors = consoleErrors.filter(err => !err.includes('favicon'));
		assert(filteredErrors.length === 0, `Cero errores críticos de consola JS (${filteredErrors.length} detectados)`);
		if (filteredErrors.length > 0) {
			console.error('Errores de consola encontrados:', filteredErrors);
		}

	} catch (err) {
		console.error('Excepción durante la prueba:', err);
		assert(false, 'Flujo de prueba completado sin excepciones');
	} finally {
		await browser.close();
		console.log(`\n=== RESUMEN PLAYWRIGHT: ${passedTests}/${totalTests} PRUEBAS EXITOSAS ===`);
		process.exit(passedTests === totalTests ? 0 : 1);
	}
})();
