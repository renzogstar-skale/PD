const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  
  async function testSite(url, name) {
    console.log(`\n=================== EVALUATING ${name} (${url}) ===================`);
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await page.goto(`${url}/b2b/sales`, { waitUntil: 'networkidle', timeout: 35000 });
    await page.waitForTimeout(2000);

    const sections = [
      { id: 'sales', name: 'Inicio (Ventas)' },
      { id: 'customers', name: 'Clientes' },
      { id: 'products', name: 'Productos' },
      { id: 'orders', name: 'Pedidos' },
      { id: 'invoices', name: 'Facturación' },
      { id: 'sales-reps', name: 'Vendedores' },
      { id: 'profitability', name: 'Rentabilidad' },
      { id: 'reports', name: 'Reportes' },
      { id: 'schedules', name: 'Horarios y Cobertura' },
      { id: 'employees', name: 'Directorio Empleados' },
      { id: 'attendance', name: 'Asistencia' }
    ];

    for (const sec of sections) {
      try {
        await page.evaluate((s) => {
          if (typeof switchSection === 'function') switchSection(s);
        }, sec.id);
        await page.waitForTimeout(600);

        // Count rows or items in tables
        const stats = await page.evaluate((s) => {
          let rows = 0;
          if (s === 'customers') rows = document.querySelectorAll('#customers-table-body tr')?.length || 0;
          else if (s === 'products') rows = document.querySelectorAll('#products-table-body tr')?.length || 0;
          else if (s === 'orders') rows = document.querySelectorAll('#orders-table-body tr')?.length || 0;
          else if (s === 'invoices') rows = document.querySelectorAll('#invoices-table-body tr')?.length || 0;
          else if (s === 'sales-reps') rows = document.querySelectorAll('#sales-reps-table-body tr')?.length || 0;
          else if (s === 'schedules') rows = document.querySelectorAll('#wfm-schedule-tbody tr')?.length || 0;
          else if (s === 'employees') rows = document.querySelectorAll('#wfm-employees-tbody tr')?.length || 0;
          else if (s === 'attendance') rows = document.querySelectorAll('#wfm-attendance-tbody tr')?.length || 0;
          
          return {
            visible: !document.getElementById(`section-${s}`)?.classList.contains('hidden'),
            rows: rows,
            textSample: document.getElementById(`section-${s}`)?.innerText?.slice(0, 100).replace(/\n/g, ' ')
          };
        }, sec.id);

        console.log(`[${sec.name}] Visible: ${stats.visible} | Rows: ${stats.rows} | Sample: ${stats.textSample?.slice(0, 50)}...`);
      } catch (e) {
        console.log(`[${sec.name}] Error:`, e.message);
      }
    }
    await page.close();
  }

  await testSite('http://localhost:8080', 'LOCALHOST');
  await testSite('https://pd-khaki-eight.vercel.app', 'VERCEL (CURRENT)');

  await browser.close();
})();
