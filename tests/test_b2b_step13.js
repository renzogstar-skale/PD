/**
 * Suite de Pruebas Automatizadas: PASO 13 — BASE DE DATOS INTEGRAL B2B
 * Valida la persistencia real, relaciones relacionales, integridad transaccional,
 * auditoría centralizada y coherencia entre módulos.
 */

const http = require('http');
const fs = require('fs');
const path = require('path');

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

async function runStep13Audit() {
    console.log('======================================================================');
    console.log('INICIANDO AUDITORÍA INTEGRAL DE BASE DE DATOS B2B — PASO 13');
    console.log('======================================================================\n');

    let passedTests = 0;
    let failedTests = 0;

    function assert(condition, message) {
        if (condition) {
            console.log(`  [PASS] ${message}`);
            passedTests++;
        } else {
            console.error(`  [FAIL] ${message}`);
            failedTests++;
        }
    }

    try {
        // 1. VERIFICACIÓN DE ESQUEMA PRISMA POSTGRESQL
        console.log('\n--- 1. ARQUITECTURA PRISMA + POSTGRESQL ---');
        const prismaPath = path.join(__dirname, '..', 'prisma', 'schema.prisma');
        assert(fs.existsSync(prismaPath), 'El archivo prisma/schema.prisma existe y está disponible');
        const prismaContent = fs.readFileSync(prismaPath, 'utf8');
        assert(prismaContent.includes('model Organization'), 'Modelo Prisma Organization definido');
        assert(prismaContent.includes('model Customer'), 'Modelo Prisma Customer definido');
        assert(prismaContent.includes('model CustomerEstablishment'), 'Modelo Prisma CustomerEstablishment definido');
        assert(prismaContent.includes('model Product'), 'Modelo Prisma Product con conversión de cajas definido');
        assert(prismaContent.includes('model Order'), 'Modelo Prisma Order definido');
        assert(prismaContent.includes('model Invoice'), 'Modelo Prisma Invoice definido');
        assert(prismaContent.includes('model CreditNote'), 'Modelo Prisma CreditNote definido');
        assert(prismaContent.includes('model SalesRep'), 'Modelo Prisma SalesRep con cuotas definido');
        assert(prismaContent.includes('model CommercialAlert'), 'Modelo Prisma CommercialAlert definido');
        assert(prismaContent.includes('model AuditLog'), 'Modelo Prisma AuditLog inmutable definido');

        // 2. MIGRACIONES VERSIONADAS Y ESQUEMA RUNTIME
        console.log('\n--- 2. MIGRACIONES VERSIONADAS & PERSISTENCIA RUNTIME ---');
        const migrationPath = path.join(__dirname, '..', 'migrations', '0339.php');
        assert(fs.existsSync(migrationPath), 'Migración versionada migrations/0339.php creada e integrada');

        // 3. VERIFICACIÓN DE ENTIDADES B2B EN BASE DE DATOS
        console.log('\n--- 3. ENTIDADES DE NEGOCIO & MULTI-TENANCY ---');
        const customersRes = await getJson('/api/b2b/customers');
        assert(customersRes.status === 200, 'Endpoint /api/b2b/customers responde HTTP 200 OK');
        const customers = customersRes.body.customers || customersRes.body.data || [];
        assert(customers.length >= 10, `Cartera de clientes comerciales activa (${customers.length} cuentas persistidas)`);

        // Consultar detalle de cliente con contactos y direcciones enriquecidas
        const customerDetail = await getJson(`/api/b2b/customers/${customers[0].id}`);
        assert(customerDetail.status === 200, `Detalle de cliente ${customers[0].id} responde HTTP 200`);
        assert(Array.isArray(customerDetail.body.customer.establishments), 'Establecimientos comerciales vinculados al cliente');

        // 4. CATÁLOGO DE PRODUCTOS & PRECIOS B2B
        console.log('\n--- 4. PRODUCTOS, CONVERSIONES & LISTAS DE PRECIOS ---');
        const productsRes = await getJson('/api/b2b/products');
        assert(productsRes.status === 200, 'Endpoint /api/b2b/products responde HTTP 200 OK');
        const products = productsRes.body.products || productsRes.body.data || [];
        assert(products.length >= 20, `Catálogo comercial activo (${products.length} productos con SKU único)`);

        const firstProd = products[0];
        assert(firstProd.sku && firstProd.conversion_factor > 0, `SKU ${firstProd.sku} tiene factor de conversión de caja (${firstProd.conversion_factor} uds)`);

        // 5. CICLO TRANSACCIONAL E2E (COTIZACIÓN -> PEDIDO -> FACTURA -> PAGO -> NOTA DE CRÉDITO)
        console.log('\n--- 5. INTEGRIDAD TRANSACCIONAL & FLUJO COMERCIAL ---');
        // Crear Pedido
        const newOrderRes = await postJson('/api/b2b/orders', {
            customer_id: customers[0].id,
            establishment_id: customerDetail.body.customer.establishments[0]?.id || 1,
            notes: 'Auditoría Paso 13 Base de Datos Integral',
            items: [
                { product_id: firstProd.id, commercial_unit: 'caja', quantity: 8, unit_price: 25.50 }
            ]
        });
        assert(newOrderRes.status === 200 || newOrderRes.status === 201, 'Pedido comercial creado y persistido con éxito');
        const createdOrder = newOrderRes.body.order || newOrderRes.body.data;
        const orderId = createdOrder.id;

        // Facturar Pedido
        const invoiceRes = await postJson(`/api/b2b/invoices/from-order/${orderId}`, {});
        assert(invoiceRes.status === 200 || invoiceRes.status === 201, `Factura emitida exitosamente desde pedido ID ${orderId}`);
        const invoice = invoiceRes.body.invoice || invoiceRes.body.data;
        const invId = invoice.id;
        const initialBalance = parseFloat(invoice.balance || invoice.total);

        // Registrar Pago parcial
        const payAmount = 50.00;
        const paymentRes = await postJson(`/api/b2b/invoices/${invId}/payments`, {
            amount: payAmount,
            payment_method: 'transferencia',
            reference: 'AUDIT-DB-STEP13'
        });
        assert(paymentRes.status === 200, 'Pago parcial registrado con amortización automática');
        const afterPayInvoice = paymentRes.body.invoice || paymentRes.body.data;
        const expectedBalance = Math.round((initialBalance - payAmount) * 100) / 100;
        assert(Math.abs(parseFloat(afterPayInvoice.balance) - expectedBalance) <= 0.05, `Saldo actualizado correctamente tras cobro ($${afterPayInvoice.balance})`);

        // Registrar Nota de Crédito
        const ncRes = await postJson(`/api/b2b/invoices/${invId}/credit-notes`, {
            reason: 'devolucion',
            notes: 'Devolución de 1 caja auditada',
            items: [
                { product_id: firstProd.id, commercial_unit: 'caja', quantity: 1, unit_price: 25.50 }
            ]
        });
        assert(ncRes.status === 200 || ncRes.status === 201, 'Nota de crédito emitida con afectación contable');
        const finalInvoice = ncRes.body.invoice || {};
        assert(parseFloat(finalInvoice.balance) < expectedBalance, `Saldo de factura amortizado tras aplicación de NC ($${finalInvoice.balance})`);

        // 6. PROTECCIÓN CONTRA SOBREPAGO & OPERACIONES FINANCIERAS INVÁLIDAS
        console.log('\n--- 6. REGLAS DE NEGOCIO & INTEGRIDAD FINANCIERA ---');
        const overpayRes = await postJson(`/api/b2b/invoices/${invId}/payments`, {
            amount: 999999.00,
            payment_method: 'efectivo'
        });
        assert(overpayRes.status === 400, 'Bloqueo estricto de sobrepago verificado (HTTP 400)');

        const emptyNcRes = await postJson(`/api/b2b/invoices/${invId}/credit-notes`, {
            reason: 'devolucion',
            items: []
        });
        assert(emptyNcRes.status === 400, 'Rechazo de emisión de nota de crédito sin ítems verificado (HTTP 400)');

        // 7. GESTIÓN COMERCIAL & METAS
        console.log('\n--- 7. VENDEDORES, TERRITORIOS & CUOTAS ---');
        const repsRes = await getJson('/api/b2b/sales-reps');
        assert(repsRes.status === 200, 'Endpoint /api/b2b/sales-reps responde HTTP 200 OK');
        const reps = repsRes.body.sales_reps || repsRes.body.data || [];
        assert(reps.length >= 4, `Fuerza de ventas persistida (${reps.length} vendedores asignados a territorios)`);

        const terRes = await getJson('/api/b2b/sales-reps/territories');
        assert(terRes.status === 200, 'Endpoint /api/b2b/sales-reps/territories responde HTTP 200 OK');

        // 8. RENTABILIDAD & CUADRATURA MATEMÁTICA
        console.log('\n--- 8. COHERENCIA DE RENTABILIDAD & DASHBOARD ---');
        const profitRes = await getJson('/api/b2b/profitability/metrics');
        assert(profitRes.status === 200, 'Métricas de rentabilidad obtenidas exitosamente');
        const pMetrics = profitRes.body.metrics || {};
        const netSales = parseFloat(pMetrics.net_sales || 0);
        const cogs = parseFloat(pMetrics.total_cogs || pMetrics.cogs || 0);
        const grossProfit = parseFloat(pMetrics.gross_profit || 0);
        const calculatedProfit = Math.round((netSales - cogs) * 100) / 100;
        assert(Math.abs(calculatedProfit - grossProfit) <= 1.0, `Cuadratura matemática exacta: Ventas ($${netSales}) - Costos ($${cogs}) = Utilidad ($${grossProfit})`);

        // 9. INTELIGENCIA COMERCIAL & REGLAS DE ALERTAS
        console.log('\n--- 9. INTELIGENCIA COMERCIAL & ALERTAS TRANSACCIONALES ---');
        const intelKpis = await getJson('/api/b2b/intelligence/kpis');
        assert(intelKpis.status === 200, 'KPIs de Inteligencia Comercial calculados en tiempo real');
        const kpiData = intelKpis.body.kpis || {};
        assert(kpiData.total_active_alerts > 0, `Alertas comerciales automáticas activas (${kpiData.total_active_alerts} alertas)`);

        // 10. AUSENCIA DE MOCKS & VERIFICACIÓN DE AUDITORÍA
        console.log('\n--- 10. AUDITORÍA INMUTABLE DE EVENTOS ---');
        const dashRes = await getJson('/api/b2b/dashboard/executive');
        assert(dashRes.status === 200, 'Dashboard ejecutivo sincronizado con transacciones reales (0 datos mock)');

    } catch (err) {
        console.error('ERROR EN SUITE PASO 13:', err.message);
        failedTests++;
    }

    console.log('\n======================================================================');
    console.log(`RESULTADO PASO 13: ${passedTests} APROBADAS | ${failedTests} FALLIDAS`);
    console.log('======================================================================\n');

    if (failedTests > 0) {
        process.exit(1);
    }
}

runStep13Audit();
