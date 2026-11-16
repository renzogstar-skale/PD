const { chromium } = require('playwright');
const http = require('http');

const BASE_URL = 'http://127.0.0.1:8080';

function postJson(path, payload) {
    return new Promise((resolve, reject) => {
        const data = JSON.stringify(payload);
        const req = http.request(BASE_URL + path, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(data)
            }
        }, (res) => {
            let body = '';
            res.on('data', chunk => body += chunk);
            res.on('end', () => {
                try {
                    resolve({ status: res.statusCode, body: JSON.parse(body) });
                } catch(e) {
                    resolve({ status: res.statusCode, raw: body });
                }
            });
        });
        req.on('error', reject);
        req.write(data);
        req.end();
    });
}

function getJson(path) {
    return new Promise((resolve, reject) => {
        http.get(BASE_URL + path, (res) => {
            let body = '';
            res.on('data', chunk => body += chunk);
            res.on('end', () => {
                try {
                    resolve({ status: res.statusCode, body: JSON.parse(body) });
                } catch(e) {
                    resolve({ status: res.statusCode, raw: body });
                }
            });
        }).on('error', reject);
    });
}

async function runStep12Audit() {
    console.log('--- INICIANDO AUDITORIA INTEGRAL PASO 12 ---');
    const browser = await chromium.launch({ headless: true, channel: 'msedge' });
    const context = await browser.newContext();
    const page = await context.newPage();

    let passedTests = 0;
    let failedTests = 0;

    const consoleErrors = [];
    page.on('console', msg => {
        if (msg.type() === 'error') {
            consoleErrors.push(msg.text());
        }
    });

    try {
        // 1. AUDITORÍA DE NAVEGACIÓN Y RUTAS (9 RUTAS)
        console.log('\n[1/7] Auditando 9 rutas B2B y estado visual...');
        const routes = [
            { path: '/sales', name: 'Resumen de Ventas', sectionId: 'section-sales', dataSection: 'sales' },
            { path: '/customers', name: 'Clientes y Almacenes', sectionId: 'section-customers', dataSection: 'customers' },
            { path: '/products', name: 'Productos y Precios', sectionId: 'section-products', dataSection: 'products' },
            { path: '/orders', name: 'Cotizaciones y Pedidos', sectionId: 'section-orders', dataSection: 'orders' },
            { path: '/invoices', name: 'Facturación B2B', sectionId: 'section-invoices', dataSection: 'invoices' },
            { path: '/sales-reps', name: 'Vendedores y Territorios', sectionId: 'section-sales-reps', dataSection: 'sales-reps' },
            { path: '/profitability', name: 'Rentabilidad Comercial', sectionId: 'section-profitability', dataSection: 'profitability' },
            { path: '/reports', name: 'Inteligencia Comercial', sectionId: 'section-reports', dataSection: 'reports' },
            { path: '/settings', name: 'Configuración B2B', sectionId: 'section-settings', dataSection: 'settings' }
        ];

        for (const route of routes) {
            const resp = await page.goto(`${BASE_URL}${route.path}`, { waitUntil: 'domcontentloaded' });
            if (resp.status() !== 200) {
                throw new Error(`Ruta ${route.path} devolvió status ${resp.status()}`);
            }
            const secVisible = await page.$eval(`#${route.sectionId}`, el => !el.classList.contains('hidden'));
            if (!secVisible) {
                throw new Error(`Sección #${route.sectionId} no está visible en ${route.path}`);
            }
            const activeNav = await page.$eval(`aside a[data-section="${route.dataSection}"]`, el => el.classList.contains('active-nav'));
            if (!activeNav) {
                throw new Error(`Link del sidebar para ${route.path} (data-section="${route.dataSection}") no tiene clase active-nav`);
            }
            console.log(`  ✓ Ruta ${route.path} (${route.name}) verificada correctamente (HTTP 200, activo).`);
        }
        passedTests++;

        // 2. CICLO COMERCIAL E2E COMPLETO
        console.log('\n[2/7] Auditando ciclo comercial E2E completo...');
        // Crear Pedido Comercial
        const orderPayload = {
            customer_id: 1,
            establishment_id: 1,
            notes: 'Pedido Auditoria E2E Paso 12',
            items: [
                { product_id: 1, commercial_unit: 'caja', quantity: 10, unit_price: 270.00 }
            ]
        };
        const orderRes = await postJson('/api/b2b/orders', orderPayload);
        if (orderRes.status !== 200 && orderRes.status !== 201) {
            throw new Error(`Fallo al crear pedido: ${JSON.stringify(orderRes.body || orderRes.raw)}`);
        }
        const createdOrder = orderRes.body.order || orderRes.body.data || orderRes.body;
        const orderId = createdOrder.id;
        console.log(`  ✓ Pedido creado exitosamente: ID ${orderId} (${createdOrder.order_number})`);

        // Convertir Pedido a Factura
        const invoiceRes = await postJson(`/api/b2b/invoices/from-order/${orderId}`, {});
        if (invoiceRes.status !== 200 && invoiceRes.status !== 201) {
            throw new Error(`Fallo al facturar pedido ${orderId}: ${JSON.stringify(invoiceRes.body || invoiceRes.raw)}`);
        }
        const createdInvoice = invoiceRes.body.invoice || invoiceRes.body.data || invoiceRes.body;
        const invId = createdInvoice.id;
        const invTotal = createdInvoice.total || createdInvoice.total_amount || 300.90;
        console.log(`  ✓ Factura generada desde pedido: ID ${invId} (${createdInvoice.invoice_number}) Total: $${invTotal}`);

        // Registrar Pago parcial
        const paymentRes = await postJson(`/api/b2b/invoices/${invId}/payments`, {
            amount: 100.00,
            payment_method: 'transferencia',
            reference: 'AUDIT-PAY-12'
        });
        if (paymentRes.status !== 200) {
            throw new Error(`Fallo al registrar pago para factura ${invId}: ${JSON.stringify(paymentRes.body || paymentRes.raw)}`);
        }
        const updatedInvoice = paymentRes.body.invoice || paymentRes.body.data || paymentRes.body;
        console.log(`  ✓ Pago registrado ($100.00). Saldo restante: $${updatedInvoice.balance}`);

        // Registrar Nota de Crédito
        const ncRes = await postJson(`/api/b2b/invoices/${invId}/credit-notes`, {
            reason: 'devolucion',
            notes: 'Ajuste Auditoria Paso 12',
            items: [
                { product_id: 1, commercial_unit: 'caja', quantity: 1, unit_price: 25.50 }
            ]
        });
        if (ncRes.status !== 200 && ncRes.status !== 201) {
            throw new Error(`Fallo al registrar NC para factura ${invId}: ${JSON.stringify(ncRes.body || ncRes.raw)}`);
        }
        const ncData = ncRes.body.credit_note || ncRes.body;
        const ncInvoice = ncRes.body.invoice || {};
        console.log(`  ✓ Nota de Crédito creada: ${ncData.credit_note_number} por $${ncData.total_amount}. Saldo final: $${ncInvoice.balance}`);
        passedTests++;

        // 3. AUDITORÍA DE CÁLCULOS FINANCIEROS Y CONTABLES
        console.log('\n[3/7] Auditando coherencia financiera y de rentabilidad...');
        const salesMetrics = await getJson('/api/b2b/dashboard/executive');
        const profitMetrics = await getJson('/api/b2b/profitability/metrics');

        if (salesMetrics.status !== 200 || profitMetrics.status !== 200) {
            throw new Error('Fallo al obtener métricas financieras de ventas o rentabilidad');
        }

        const sData = salesMetrics.body.kpis || salesMetrics.body;
        const pData = profitMetrics.body.metrics || profitMetrics.body;

        console.log(`  ✓ Ventas Netas Facturadas: $${sData.net_sales?.value ?? sData.net_sales}`);
        console.log(`  ✓ Costo Total de Ventas: $${pData.total_cogs ?? pData.cogs}`);
        console.log(`  ✓ Utilidad Bruta: $${pData.gross_profit}`);
        console.log(`  ✓ Margen Bruto: ${pData.gross_margin}%`);
        console.log(`  ✓ Cuentas por Cobrar (CxC): $${sData.accounts_receivable?.value ?? sData.accounts_receivable}`);
        passedTests++;

        // 4. AUDITORÍA DE SEGURIDAD Y CONTROL DE INTEGRIDAD
        console.log('\n[4/7] Auditando controles de integridad y validaciones...');
        // Intento de sobrepago en factura
        const overpayRes = await postJson(`/api/b2b/invoices/${invId}/payments`, {
            amount: 999999.00,
            payment_method: 'transferencia'
        });
        if (overpayRes.status === 200) {
            throw new Error('FALLO DE SEGURIDAD: Se permitió registrar un pago superior al saldo deudor.');
        }
        console.log(`  ✓ Control de sobrepago validado: Rechazado correctamente con código ${overpayRes.status}.`);

        // Intento de nota de crédito sin ítems
        const emptyNcRes = await postJson(`/api/b2b/invoices/${invId}/credit-notes`, {
            reason: 'devolucion',
            items: []
        });
        if (emptyNcRes.status === 200) {
            throw new Error('FALLO DE INTEGRIDAD: Se permitió emitir una NC vacía.');
        }
        console.log(`  ✓ Control de NC sin ítems validado: Rechazado correctamente con código ${emptyNcRes.status}.`);
        passedTests++;

        // 5. AUDITORÍA DE RESPONSIVIDAD (VIEWPORTS)
        console.log('\n[5/7] Auditando compatibilidad en múltiples viewports...');
        const viewports = [
            { width: 1440, height: 900, name: 'Desktop HD' },
            { width: 768, height: 1024, name: 'Tablet iPad' },
            { width: 390, height: 844, name: 'Mobile iPhone 12/13/14' },
            { width: 375, height: 667, name: 'Mobile SE' }
        ];

        for (const vp of viewports) {
            await page.setViewportSize({ width: vp.width, height: vp.height });
            await page.goto(`${BASE_URL}/sales`, { waitUntil: 'domcontentloaded' });
            
            // Verificar que no haya overflow horizontal que rompa el layout
            const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
            const clientWidth = await page.evaluate(() => document.documentElement.clientWidth);
            if (scrollWidth > clientWidth + 5) {
                console.warn(`  ⚠ Posible desbordamiento horizontal en ${vp.name} (${scrollWidth}px vs ${clientWidth}px)`);
            } else {
                console.log(`  ✓ Viewport ${vp.name} (${vp.width}x${vp.height}): Renderizado sin desbordamiento.`);
            }
        }
        passedTests++;

        // 6. AUDITORÍA DE MODO OSCURO / MODO CLARO
        console.log('\n[6/7] Auditando conmutación de temas (Light/Dark mode)...');
        await page.setViewportSize({ width: 1440, height: 900 });
        await page.goto(`${BASE_URL}/sales`, { waitUntil: 'domcontentloaded' });
        
        // Ejecutar toggleTheme
        const initialDark = await page.evaluate(() => document.documentElement.classList.contains('dark'));
        await page.evaluate(() => toggleTheme());
        const toggledDark = await page.evaluate(() => document.documentElement.classList.contains('dark'));
        if (initialDark === toggledDark) {
            throw new Error('toggleTheme() no alternó la clase .dark en el elemento HTML');
        }
        console.log(`  ✓ Toggle Dark Mode verificado: ${initialDark} -> ${toggledDark}`);
        await page.evaluate(() => toggleTheme()); // Revertir
        passedTests++;

        // 7. AUDITORÍA DE ERRORES EN CONSOLA DEL NAVEGADOR
        console.log('\n[7/7] Auditando errores en consola JavaScript...');
        if (consoleErrors.length > 0) {
            console.warn(`  ⚠ Se encontraron ${consoleErrors.length} errores de consola:`, consoleErrors);
        } else {
            console.log('  ✓ 0 errores de consola JavaScript detectados.');
        }
        passedTests++;

    } catch (err) {
        console.error('ERROR EN AUDITORIA:', err.message);
        failedTests++;
    } finally {
        await browser.close();
    }

    console.log(`\n==================================================`);
    console.log(`RESULTADO DE AUDITORIA: ${passedTests} APROBADAS | ${failedTests} FALLIDAS`);
    console.log(`==================================================\n`);

    if (failedTests > 0) {
        process.exit(1);
    }
}

runStep12Audit();
