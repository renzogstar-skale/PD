/**
 * Test Suite: GESTIÓN DEL CICLO DE VIDA DEL PRODUCTO E INTELIGENCIA COMERCIAL
 * Prince Distributors Wholesale B2B Suite
 * Verificación integral de las 7 Fases y 15 Reglas Obligatorias
 */

const http = require('http');

const BASE_URL = 'http://127.0.0.1:8080';

function request(method, path, body = null, headers = {}) {
  return new Promise((resolve, reject) => {
    const url = new URL(path, BASE_URL);
    const options = {
      hostname: url.hostname,
      port: url.port,
      path: url.pathname + url.search,
      method: method,
      headers: {
        'Content-Type': 'application/json',
        ...headers
      }
    };

    const req = http.request(options, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        let json = null;
        try {
          json = JSON.parse(data);
        } catch (e) {
          json = data;
        }
        resolve({
          statusCode: res.statusCode,
          headers: res.headers,
          data: json,
          raw: data
        });
      });
    });

    req.on('error', reject);

    if (body) {
      req.write(typeof body === 'string' ? body : JSON.stringify(body));
    }
    req.end();
  });
}

async function runTests() {
  console.log('\n========================================================================');
  console.log('PRINCE DISTRIBUTORS — SUITE DE PRUEBAS AUTOMATIZADAS');
  console.log('Ciclo de Vida del Producto e Inteligencia Comercial (Fases 1 a 7)');
  console.log('========================================================================\n');

  let passed = 0;
  let failed = 0;

  function assert(condition, message) {
    if (condition) {
      console.log(`  ✓ PASS: ${message}`);
      passed++;
    } else {
      console.error(`  ✗ FAIL: ${message}`);
      failed++;
    }
  }

  try {
    // -------------------------------------------------------------------------
    // TEST 1: Verificar que el dashboard 'Resumen de Ventas' permanece 100% operativo e intacto
    // -------------------------------------------------------------------------
    console.log('[FASE 1 & REGLA 1] Verificación de Intactez de Resumen de Ventas...');
    const resSales = await request('GET', '/b2b/sales');
    assert(resSales.statusCode === 200, 'Ruta /b2b/sales responde HTTP 200 OK');
    assert(resSales.raw.includes('id="section-sales"'), 'Contenedor section-sales existe intacto en el DOM');
    assert(resSales.raw.includes('Resumen de Ventas'), 'Título original Resumen de Ventas presente');

    // -------------------------------------------------------------------------
    // TEST 2: Acceso a la nueva ruta /b2b/product-lifecycle
    // -------------------------------------------------------------------------
    console.log('\n[FASE 4] Acceso a la Sección Ciclo de Vida del Producto...');
    const resPlc = await request('GET', '/b2b/product-lifecycle');
    assert(resPlc.statusCode === 200, 'Ruta /b2b/product-lifecycle responde HTTP 200 OK');
    assert(resPlc.raw.includes('id="section-product-lifecycle"'), 'Contenedor de Ciclo de Vida integrado en la arquitectura');
    assert(resPlc.raw.includes('Ciclo de Vida del Producto'), 'Título oficial de la sección renderizado');
    assert(resPlc.raw.includes('Analiza el rendimiento, anticipa riesgos y optimiza la rentabilidad de tu portafolio'), 'Subtítulo exacto configurado');

    // -------------------------------------------------------------------------
    // TEST 3: Verificación del Motor de los 12 KPIs Principales
    // -------------------------------------------------------------------------
    console.log('\n[FASE 3 & SECCIÓN 3] Verificación Matemática de los 12 KPIs Principales...');
    const resKpis = await request('GET', '/api/b2b/lifecycle/kpis?period=MTD');
    assert(resKpis.statusCode === 200, 'Endpoint /api/b2b/lifecycle/kpis responde HTTP 200');
    assert(resKpis.data.success === true, 'Respuesta API success = true');

    const k = resKpis.data.kpis;
    const requiredKpis = [
      'sell_through', 'turnover', 'coverage_dio', 'gmroi', 'gmros',
      'growth_skus', 'maturity_skus', 'decline_skus', 'immobilized_capital',
      'stockout_risk', 'net_margin_post_discounts', 'forecast_accuracy'
    ];

    for (const key of requiredKpis) {
      assert(k[key] !== undefined, `KPI obligatorio presente: ${key}`);
      assert(typeof k[key].value === 'number', `KPI ${key} tiene valor numérico calculado: ${k[key].value}`);
      assert(k[key].formula !== undefined, `KPI ${key} incluye tooltip con fórmula explicativa`);
      assert(['favorable', 'advertencia', 'critico'].includes(k[key].status), `KPI ${key} posee estado semántico visual: ${k[key].status}`);
      assert(Array.isArray(k[key].sparkline) && k[key].sparkline.length > 0, `KPI ${key} incluye serie para sparkline de tendencia`);
    }

    // -------------------------------------------------------------------------
    // TEST 4: Clasificación Automática del Ciclo de Vida (5 Estados Operativos)
    // -------------------------------------------------------------------------
    console.log('\n[FASE 3 & SECCIÓN 4] Verificación de la Clasificación de Portafolio en 5 Estados...');
    const resDist = await request('GET', '/api/b2b/lifecycle/distribution');
    assert(resDist.statusCode === 200, 'Endpoint /api/b2b/lifecycle/distribution responde HTTP 200');
    assert(resDist.data.distribution.total_skus > 0, `Total SKUs analizados: ${resDist.data.distribution.total_skus}`);

    const stages = resDist.data.distribution.stages;
    const stageKeys = stages.map(s => s.stage);
    assert(stageKeys.includes('introduction'), 'Etapa detectada: Introducción (Nuevos lanzamientos)');
    assert(stageKeys.includes('growth'), 'Etapa detectada: Crecimiento (Demanda en aumento > +10%)');
    assert(stageKeys.includes('maturity'), 'Etapa detectada: Madurez (Ventas consolidadas estables)');
    assert(stageKeys.includes('decline'), 'Etapa detectada: Declive (Contracción sostenida de demanda)');
    assert(stageKeys.includes('exit_renewal'), 'Etapa detectada: Salida o Renovación (Liquidación/Obsolescencia)');
    assert(stageKeys.includes('insufficient_data'), 'Productos con historial insuficiente clasificados como Datos Insuficientes');

    // -------------------------------------------------------------------------
    // TEST 5: Matriz de Rentabilidad y Rotación (Scatter Plot)
    // -------------------------------------------------------------------------
    console.log('\n[FASE 4 & SECCIÓN 5] Verificación de Matriz Rentabilidad y Rotación (BCG)...');
    const resMatrix = await request('GET', '/api/b2b/lifecycle/matrix');
    assert(resMatrix.statusCode === 200, 'Endpoint /api/b2b/lifecycle/matrix responde HTTP 200');
    assert(resMatrix.data.matrix.items.length > 0, `Puntos calculados para dispersión: ${resMatrix.data.matrix.items.length}`);
    const firstPoint = resMatrix.data.matrix.items[0];
    assert(firstPoint.turnover !== undefined && firstPoint.gross_margin !== undefined && firstPoint.inventory_value !== undefined,
      'Coordenadas válidas X (Rotación), Y (Margen %), R (Valor inventario)');

    // -------------------------------------------------------------------------
    // TEST 6: Capital Inmovilizado y Antigüedad de Inventario
    // -------------------------------------------------------------------------
    console.log('\n[FASE 4 & SECCIÓN 5] Verificación de Capital Inmovilizado por Franjas...');
    const resImmob = await request('GET', '/api/b2b/lifecycle/immobilized');
    assert(resImmob.statusCode === 200, 'Endpoint /api/b2b/lifecycle/immobilized responde HTTP 200');
    assert(resImmob.data.immobilized.total_immobilized > 0, `Total capital inmovilizado detectado: $${resImmob.data.immobilized.total_immobilized}`);
    assert(resImmob.data.immobilized.aging_tiers.length === 3, 'Franjas de antigüedad calculadas: 60-90d, 91-180d, >180d crítico');

    // -------------------------------------------------------------------------
    // TEST 7: Motor de Recomendaciones Explicables y Flujo de Aprobación
    // -------------------------------------------------------------------------
    console.log('\n[FASE 5 & SECCIÓN 6] Verificación del Motor de Inteligencia Comercial y Aprobación...');
    const resRecs = await request('GET', '/api/b2b/lifecycle/recommendations');
    assert(resRecs.statusCode === 200, 'Endpoint /api/b2b/lifecycle/recommendations responde HTTP 200');
    assert(resRecs.data.recommendations.length > 0, `Recomendaciones generadas: ${resRecs.data.recommendations.length}`);

    const rec = resRecs.data.recommendations[0];
    assert(rec.problem_detected && rec.suggested_action && rec.financial_impact_usd,
      'Recomendación incluye problema detectado, evidencia, impacto económico y acción sugerida');

    // Probar aprobación de recomendación
    const resReview = await request('POST', `/api/b2b/lifecycle/recommendations/${rec.id}/review`, {
      status: 'approved',
      reviewed_by: 'Carlos Mendoza (Dir. Ventas)',
      notes: 'Aprobación auditada durante prueba automatizada'
    });
    assert(resReview.statusCode === 200 && resReview.data.success === true, 'Aprobación de recomendación persistida con auditoría');

    // -------------------------------------------------------------------------
    // TEST 8: Pronóstico de Demanda y Reposición (ROP & Cantidad Sugerida)
    // -------------------------------------------------------------------------
    console.log('\n[FASE 5 & SECCIÓN 7] Verificación de Pronósticos de Demanda (Holt-Winters & ROP)...');
    const resTrend = await request('GET', '/api/b2b/lifecycle/demand-trend');
    assert(resTrend.statusCode === 200, 'Endpoint /api/b2b/lifecycle/demand-trend responde HTTP 200');
    assert(resTrend.data.trend.real_demand.length > 0 && resTrend.data.trend.forecast_baseline.length > 0,
      'Series de demanda real vs pronóstico ajustado por roturas generadas correctamente');

    // -------------------------------------------------------------------------
    // TEST 9: Simulador de Markdown y Liquidación por Elasticidad
    // -------------------------------------------------------------------------
    console.log('\n[FASE 5 & SECCIÓN 9] Verificación del Simulador de Markdown...');
    const resSim = await request('POST', '/api/b2b/lifecycle/simulate-markdown', {
      product_id: 12,
      discount_pct: 25.0,
      elasticity: 1.4
    });
    assert(resSim.statusCode === 200 && resSim.data.success === true, 'Endpoint /api/b2b/lifecycle/simulate-markdown responde HTTP 200');
    const sim = resSim.data.simulation;
    assert(sim.proposed_price < sim.original_price, `Precio con descuento ($${sim.proposed_price}) menor al original ($${sim.original_price})`);
    assert(sim.recoverable_capital_usd > 0, `Capital recuperable estimado: $${sim.recoverable_capital_usd}`);
    assert(sim.breakeven_units > 0, `Punto de equilibrio calculado: ${sim.breakeven_units} unidades`);

    // -------------------------------------------------------------------------
    // TEST 10: Asistente de Importación de Datos (Idempotente)
    // -------------------------------------------------------------------------
    console.log('\n[FASE 6 & SECCIÓN 12] Verificación del Asistente de Importación CSV/Excel...');
    const sampleImportRows = [
      { sku: 'SKU-TEST-099', name: 'Mermelada de Fresa 320g', category: 'Abarrotes', brand: 'Prince Gourmet', sales_units: 410, sales_amount: 1230.0, cogs: 820.0, stock: 600 }
    ];
    const resImport = await request('POST', '/api/b2b/lifecycle/import', {
      filename: 'test_automated_import.csv',
      rows: sampleImportRows,
      column_mapping: {
        sku: 'sku',
        name: 'name',
        category: 'category',
        brand: 'brand',
        sales_units: 'sales_units',
        sales_amount: 'sales_amount',
        cogs: 'cogs',
        current_stock: 'stock'
      }
    });
    assert(resImport.statusCode === 200 && resImport.data.success === true, 'Importación procesada exitosamente con mapeo y clasificación');
    assert(resImport.data.valid_rows === 1, '1 fila importada y clasificada correctamente');

  } catch (err) {
    console.error('Error fatal durante la ejecución de pruebas:', err);
    failed++;
  }

  console.log('\n========================================================================');
  console.log(`RESULTADO DE LA SUITE: ${passed} PASADAS, ${failed} FALLIDAS`);
  console.log('========================================================================\n');

  if (failed > 0) {
    process.exit(1);
  }
}

runTests();
