const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  
  await page.goto('https://pd-khaki-eight.vercel.app/b2b/sales', { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);

  const navs = [
    { sec: 'sales', text: 'Inicio' },
    { sec: 'customers', text: 'Clientes' },
    { sec: 'products', text: 'Productos' },
    { sec: 'orders', text: 'Pedidos' },
    { sec: 'invoices', text: 'Facturación' },
    { sec: 'sales-reps', text: 'Vendedores' },
    { sec: 'profitability', text: 'Rentabilidad' },
    { sec: 'schedules', text: 'Horarios' }
  ];

  for (const n of navs) {
    const el = await page.$(`a[data-section="${n.sec}"]`);
    if (el) {
      await el.click();
      await page.waitForTimeout(1000);
      const text = await page.evaluate(() => {
        const visibleSection = Array.from(document.querySelectorAll('[id^="section-"]')).find(s => !s.classList.contains('hidden'));
        if (!visibleSection) return 'NO VISIBLE SECTION';
        const tables = Array.from(visibleSection.querySelectorAll('tbody')).map(tb => ({ id: tb.id, trs: tb.querySelectorAll('tr').length }));
        const cards = visibleSection.querySelectorAll('.rounded-xl, .rounded-lg, .card').length;
        return {
          id: visibleSection.id,
          tables,
          cards,
          h1_h2: Array.from(visibleSection.querySelectorAll('h1, h2, h3')).slice(0, 3).map(h => h.innerText.trim())
        };
      });
      console.log(`Nav: ${n.text} (${n.sec}) ->`, text);
    }
  }

  await browser.close();
})();
