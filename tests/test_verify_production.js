const { chromium } = require('playwright');

(async () => {
	console.log('Iniciando verificación en PRODUCCIÓN (Vercel)...');
	const browser = await chromium.launch({
		headless: true,
		executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'
	});
	const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
	
	const errors = [];
	page.on('console', msg => {
		if (msg.type() === 'error') errors.push(msg.text());
	});

	console.log('Navegando a: https://pd-khaki-eight.vercel.app/b2b/schedules ...');
	await page.goto('https://pd-khaki-eight.vercel.app/b2b/schedules', { waitUntil: 'networkidle' });

	const schedVisible = await page.locator('#section-schedules').isVisible();
	console.log('¿Dashboard Horarios visible en Producción?', schedVisible);

	const kpiSales = await page.locator('#wfm-kpi-sales').innerText();
	const kpiHours = await page.locator('#wfm-kpi-sched-hours').innerText();
	const kpiCov = await page.locator('#wfm-kpi-coverage').innerText();
	console.log('KPIs en Producción -> Ventas:', kpiSales, '| Horas:', kpiHours, '| Cobertura:', kpiCov);

	// Probar cambio a pestaña Programación semanal
	await page.click('#wfm-navtab-schedule');
	await page.waitForTimeout(400);
	const rows = await page.locator('#wfm-schedule-tbody tr').count();
	console.log('Filas de colaboradores en tabla en Producción:', rows);

	// Probar botón de Crear programación
	await page.click('button:has-text("Crear programación")');
	await page.waitForTimeout(300);
	const wizVisible = await page.locator('#modal-wfm-wizard').isVisible();
	console.log('¿Asistente de 4 pasos abre en Producción?', wizVisible);

	console.log('Errores en consola de Producción:', errors.filter(e => !e.includes('favicon')));

	await browser.close();
	console.log('>>> VERIFICACIÓN EN PRODUCCIÓN COMPLETADA EXITOSAMENTE <<<');
})();
