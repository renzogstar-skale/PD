const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  
  const errors = [];
  page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
  
  console.log('Navegando a https://pd-khaki-eight.vercel.app/b2b/schedules...');
  await page.goto('https://pd-khaki-eight.vercel.app/b2b/schedules', { waitUntil: 'networkidle', timeout: 35000 });
  await page.waitForTimeout(2000);
  
  const title = await page.title();
  const banner = await page.$eval('#section-schedules', el => el ? !el.classList.contains('hidden') : false);
  const salesKpi = await page.$eval('#wfm-kpi-sales', el => el.textContent.trim());
  const hoursKpi = await page.$eval('#wfm-kpi-sched-hours', el => el.textContent.trim());
  const covKpi = await page.$eval('#wfm-kpi-coverage', el => el.textContent.trim());
  const costKpi = await page.$eval('#wfm-kpi-cost', el => el.textContent.trim());
  
  console.log('✓ Título de Página:', title);
  console.log('✓ Sección Horarios y Cobertura Visible:', banner);
  console.log('✓ KPI Ventas:', salesKpi);
  console.log('✓ KPI Horas Programadas:', hoursKpi);
  console.log('✓ KPI Cobertura de Personal:', covKpi);
  console.log('✓ KPI Costo Laboral:', costKpi);
  console.log('✓ Errores de Consola:', errors.length === 0 ? 'Ninguno (0)' : errors);

  await page.screenshot({ path: 'C:/Users/renzo/.gemini/antigravity/brain/408bb777-613c-40d9-b931-502afb70d1cb/production_wfm_live.png' });
  console.log('✓ Captura de producción guardada exitosamente en production_wfm_live.png');

  await browser.close();
})();
