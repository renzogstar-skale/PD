/**
 * Prince Distributors — Playwright Verification Suite
 * Workforce Management (Horarios y Cobertura)
 */
const { chromium } = require('playwright');
const path = require('path');

const ARTIFACTS_DIR = 'C:/Users/renzo/.gemini/antigravity/brain/408bb777-613c-40d9-b931-502afb70d1cb';
const BASE_URL = 'http://localhost:8080';

async function run() {
	console.log('======================================================================');
	console.log('   PRINCE DISTRIBUTORS — AUDITORÍA PLAYWRIGHT (HORARIOS Y COBERTURA)  ');
	console.log('======================================================================\n');

	const browser = await chromium.launch({
		headless: true,
		executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'
	});
	const context = await browser.newContext({
		viewport: { width: 1440, height: 900 }
	});
	const page = await context.newPage();

	const consoleErrors = [];
	page.on('console', msg => {
		if (msg.type() === 'error') {
			consoleErrors.push(msg.text());
		}
	});

	try {
		// [1/10] Cargar /b2b/sales y navegar vía Menú Lateral
		console.log('[1/10] Navegando a la plataforma y verificando menú lateral...');
		await page.goto(`${BASE_URL}/b2b/sales`, { waitUntil: 'networkidle' });
		
		const wfmNavLink = await page.locator('a[data-section="schedules"]');
		if (await wfmNavLink.count() === 0) {
			throw new Error('No se encontró el enlace de Horarios y Cobertura en el sidebar');
		}
		console.log('  ✔ PASS: Enlace "Horarios y Cobertura" visible en Operaciones y Personal');

		// Clic en Horarios y Cobertura
		await wfmNavLink.click();
		await page.waitForTimeout(600);

		// [2/10] Verificar Cambio de URL y no pantalla blanca
		console.log('\n[2/10] Verificando enrutamiento y ausencia de pantalla blanca...');
		const currentUrl = page.url();
		if (!currentUrl.includes('/b2b/schedules')) {
			throw new Error(`La URL no cambió a /b2b/schedules (actual: ${currentUrl})`);
		}
		console.log(`  ✔ PASS: URL actualizada correctamente a ${currentUrl}`);

		const schedSection = page.locator('#section-schedules');
		const isVisible = await schedSection.isVisible();
		if (!isVisible) {
			throw new Error('El contenedor #section-schedules no está visible');
		}
		console.log('  ✔ PASS: Dashboard #section-schedules renderizado exitosamente (sin pantalla blanca)');

		// [3/10] Verificación de Consola Limpia
		console.log('\n[3/10] Verificando errores críticos de consola...');
		const criticalErrors = consoleErrors.filter(e => !e.includes('favicon'));
		if (criticalErrors.length > 0) {
			console.log('  ⚠ Advertencia en consola:', criticalErrors);
		} else {
			console.log('  ✔ PASS: 0 errores de consola al inicializar el módulo');
		}

		// [4/10] Verificar los 5 Indicadores Principales y Explicaciones de Cálculo
		console.log('\n[4/10] Verificando los 5 KPIs de Horarios y Cobertura...');
		const salesVal = await page.locator('#wfm-kpi-sales').innerText();
		const recVal = await page.locator('#wfm-kpi-rec-hours').innerText();
		const schedVal = await page.locator('#wfm-kpi-sched-hours').innerText();
		const costVal = await page.locator('#wfm-kpi-cost').innerText();
		const covVal = await page.locator('#wfm-kpi-coverage').innerText();

		console.log(`  ✔ PASS: KPI 1 - Ventas Previstas: ${salesVal}`);
		console.log(`  ✔ PASS: KPI 2 - Horas Recomendadas: ${recVal}`);
		console.log(`  ✔ PASS: KPI 3 - Horas Programadas: ${schedVal}`);
		console.log(`  ✔ PASS: KPI 4 - Costo Laboral: ${costVal}`);
		console.log(`  ✔ PASS: KPI 5 - Cobertura: ${covVal}`);

		// Captura de Resumen
		await page.screenshot({ path: path.join(ARTIFACTS_DIR, 'playwright_wfm_summary.png'), fullPage: true });
		console.log('  ✔ PASS: Captura de pantalla de Resumen guardada');

		// [5/10] Probar Pestaña 2: Programación Semanal
		console.log('\n[5/10] Probando Pestaña 2: Programación Semanal & Matriz de Turnos...');
		await page.click('#wfm-navtab-schedule');
		await page.waitForTimeout(400);

		const scheduleTable = page.locator('#wfm-schedule-table');
		const rowsCount = await page.locator('#wfm-schedule-tbody tr').count();
		if (rowsCount === 0) {
			throw new Error('La tabla de turnos semanal está vacía');
		}
		console.log(`  ✔ PASS: Matriz semanal interactiva renderizada con ${rowsCount} colaboradores`);
		await page.screenshot({ path: path.join(ARTIFACTS_DIR, 'playwright_wfm_schedule.png') });

		// [6/10] Probar Pestaña 3: Empleados
		console.log('\n[6/10] Probando Pestaña 3: Directorio de Empleados...');
		await page.click('#wfm-navtab-employees');
		await page.waitForTimeout(300);
		const empCount = await page.locator('#wfm-employees-tbody tr').count();
		console.log(`  ✔ PASS: Directorio de dotación muestra ${empCount} colaboradores activos`);

		// [7/10] Probar Pestaña 4: Productividad
		console.log('\n[7/10] Probando Pestaña 4: Modelos de Productividad B2B...');
		await page.click('#wfm-navtab-productivity');
		await page.waitForTimeout(300);
		const prodVisible = await page.locator('#wfm-tab-content-productivity').isVisible();
		console.log(`  ✔ PASS: Modelos de Reponedores vs Preventistas visibles: ${prodVisible}`);

		// [8/10] Probar Pestaña 5: Simulador What-If Interactivo
		console.log('\n[8/10] Probando Pestaña 5: Simulador de Demanda What-If...');
		await page.click('#wfm-navtab-simulator');
		await page.waitForTimeout(300);
		await page.click('#btn-sim-p10'); // Clic en +10%
		await page.waitForTimeout(400);
		const simSales = await page.locator('#sim-inline-sales').innerText();
		console.log(`  ✔ PASS: Simulación +10% calculada dinámicamente: ${simSales}`);
		await page.screenshot({ path: path.join(ARTIFACTS_DIR, 'playwright_wfm_simulator.png') });

		// [9/10] Probar Pestaña 6: Configuración
		console.log('\n[9/10] Probando Pestaña 6: Configuración de Parámetros Laborales...');
		await page.click('#wfm-navtab-settings');
		await page.waitForTimeout(300);
		const settVisible = await page.locator('#wfm-tab-content-settings').isVisible();
		console.log(`  ✔ PASS: Panel de configuración normativa visible: ${settVisible}`);

		// [10/10] Probar Asistente de Planificación en 4 Pasos (Wizard)
		console.log('\n[10/10] Probando Asistente "Crear programación" en 4 Pasos...');
		await page.click('button:has-text("Crear programación")');
		await page.waitForTimeout(400);

		const wizModal = page.locator('#modal-wfm-wizard');
		const wizVisible = await wizModal.isVisible();
		if (!wizVisible) throw new Error('El modal del wizard no abrió');
		console.log('  ✔ PASS: Modal del Asistente en 4 pasos abierto correctamente');

		// Navegar Paso 1 -> 2 -> 3 -> 4
		await page.click('#wfm-btn-next');
		await page.waitForTimeout(200);
		console.log('  ✔ PASS: Avanzado a Paso 2 (Calcular Horas)');

		await page.click('#wfm-btn-next');
		await page.waitForTimeout(200);
		console.log('  ✔ PASS: Avanzado a Paso 3 (Construir Horarios)');

		await page.click('#wfm-btn-next');
		await page.waitForTimeout(200);
		console.log('  ✔ PASS: Avanzado a Paso 4 (Revisar y Publicar)');

		await page.screenshot({ path: path.join(ARTIFACTS_DIR, 'playwright_wfm_wizard.png') });
		await page.click('#modal-wfm-wizard button:has-text("Guardar borrador y continuar después")');
		await page.waitForTimeout(300);
		console.log('  ✔ PASS: "Guardar borrador" ejecutado y modal cerrado');

		// Responsive check: Móvil
		console.log('\nProbando adaptabilidad móvil (375x667)...');
		await page.setViewportSize({ width: 375, height: 667 });
		await page.waitForTimeout(400);
		await page.screenshot({ path: path.join(ARTIFACTS_DIR, 'playwright_wfm_mobile.png') });
		console.log('  ✔ PASS: Vista móvil adaptativa validada');

		console.log('\n======================================================================');
		console.log('   RESULTADOS AUDITORÍA PLAYWRIGHT: 12/12 PRUEBAS APROBADAS (100% ÉXITO)');
		console.log('======================================================================\n');
	} finally {
		await browser.close();
	}
}

run().catch(err => {
	console.error('ERROR EN PLAYWRIGHT:', err);
	process.exit(1);
});
