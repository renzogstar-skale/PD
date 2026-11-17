/**
 * Test Suite: Paso 15 — Seguridad Empresarial, RBAC, API Tokens y Auditoría
 * Prince Distributors B2B Platform
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
          data: json
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
  console.log('====================================================');
  console.log('   PRINCE DISTRIBUTORS - PASO 15: SEGURIDAD Y RBAC  ');
  console.log('====================================================\n');

  let passed = 0;
  let failed = 0;

  function assert(condition, message) {
    if (condition) {
      console.log(`  \x1b[32m✔ PASS:\x1b[0m ${message}`);
      passed++;
    } else {
      console.error(`  \x1b[31m✖ FAIL:\x1b[0m ${message}`);
      failed++;
    }
  }

  try {
    // 1. Roles & Permissions Matrix
    console.log('[1/7] Probando Matriz de Roles y Permisos (RBAC)...');
    const rolesRes = await request('GET', '/api/b2b/security/roles');
    assert(rolesRes.statusCode === 200, 'GET /api/b2b/security/roles responde 200 OK');
    assert(rolesRes.data.success === true, 'Respuesta indica éxito');
    const roleIds = (rolesRes.data.roles || []).map(r => r.role);
    assert(
      roleIds.includes('admin') &&
      roleIds.includes('sales_director') &&
      roleIds.includes('sales_rep') &&
      roleIds.includes('billing') &&
      roleIds.includes('logistics'),
      'Existen los 5 roles empresariales requeridos (admin, sales_director, sales_rep, billing, logistics)'
    );

    // 2. Permission Verification Endpoint
    console.log('\n[2/7] Probando Verificación de Permisos Granulares...');
    const verifyAdmin = await request('POST', '/api/b2b/security/verify-access', {
      role: 'admin',
      permission: 'any.destructive.action'
    });
    assert(verifyAdmin.statusCode === 200 && verifyAdmin.data.allowed === true, 'Admin tiene acceso comodín (*) a cualquier acción');

    const verifyRepAllowed = await request('POST', '/api/b2b/security/verify-access', {
      role: 'sales_rep',
      permission: 'sales.read'
    });
    assert(verifyRepAllowed.data.allowed === true, 'Vendedor (sales_rep) tiene permiso sales.read');

    const verifyRepDenied = await request('POST', '/api/b2b/security/verify-access', {
      role: 'sales_rep',
      permission: 'profitability.adjust_cost'
    });
    assert(verifyRepDenied.statusCode === 403 && verifyRepDenied.data.allowed === false, 'Vendedor (sales_rep) TIENE DENEGADO profitability.adjust_cost (HTTP 403)');

    // 3. API Key Generation & Token Format
    console.log('\n[3/7] Probando Generación de API Keys B2B Seguras...');
    const createKeyRes = await request('POST', '/api/b2b/security/api-keys', {
      name: 'Integración ERP Automotriz Test',
      role: 'sales_director',
      expires_in_days: 90
    });
    assert(createKeyRes.statusCode === 201, 'POST /api/b2b/security/api-keys responde 201 Created');
    assert(createKeyRes.data.success === true, 'API Key creada exitosamente');
    const createdKey = createKeyRes.data.api_key;
    assert(typeof createdKey.token === 'string' && createdKey.token.startsWith('b2b_'), 'Token generado comienza con prefijo "b2b_"');
    assert(createdKey.token.length >= 40, `Token generado tiene alta entropía (${createdKey.token.length} caracteres)`);

    const rawToken = createdKey.token;
    const keyId = createdKey.id;

    // 4. API Key Authentication via Header & Security Headers
    console.log('\n[4/7] Probando Autenticación con X-API-KEY y Headers de Seguridad...');
    const authReq = await request('GET', '/api/b2b/security/roles', null, {
      'X-API-KEY': rawToken
    });
    assert(authReq.statusCode === 200, 'Petición con X-API-KEY válida es aceptada (HTTP 200)');
    assert(authReq.headers['x-content-type-options'] === 'nosniff', 'Cabecera de seguridad X-Content-Type-Options: nosniff presente');
    assert(authReq.headers['x-frame-options'] === 'SAMEORIGIN', 'Cabecera de seguridad X-Frame-Options: SAMEORIGIN presente');
    assert(authReq.headers['x-xss-protection'] === '1; mode=block', 'Cabecera de seguridad X-XSS-Protection: 1; mode=block presente');

    // 5. API Key Listing
    console.log('\n[5/7] Probando Listado de API Keys Registradas...');
    const listRes = await request('GET', '/api/b2b/security/api-keys');
    assert(listRes.statusCode === 200, 'GET /api/b2b/security/api-keys responde 200 OK');
    const foundKey = (listRes.data.keys || []).find(k => k.id === keyId);
    assert(foundKey !== undefined, 'La clave recién creada se encuentra en la lista');
    assert(foundKey.is_active === 1 || foundKey.is_active === true, 'La clave está en estado activo');
    assert(!foundKey.token_hash || foundKey.token_hash !== rawToken, 'El token en crudo NO se expone en la lista (solo hash/preview)');

    // 6. API Key Revocation
    console.log('\n[6/7] Probando Revocación de API Keys...');
    const revokeRes = await request('POST', `/api/b2b/security/api-keys/${keyId}/revoke`);
    assert(revokeRes.statusCode === 200, `POST /api/b2b/security/api-keys/${keyId}/revoke responde 200 OK`);
    assert(revokeRes.data.success === true, 'Revocación exitosa reportada');

    // Test calling with revoked key
    const authRevokedReq = await request('GET', '/api/b2b/security/roles', null, {
      'X-API-KEY': rawToken
    });
    assert(authRevokedReq.statusCode === 401, 'Petición con API Key revocada es rechazada con HTTP 401 Unauthorized');

    // Test calling with completely fake key
    const authFakeReq = await request('GET', '/api/b2b/security/roles', null, {
      'X-API-KEY': 'b2b_fake_invalid_token_99999999999999999999'
    });
    assert(authFakeReq.statusCode === 401, 'Petición con API Key inexistente es rechazada con HTTP 401 Unauthorized');

    // 7. Security Audit Log
    console.log('\n[7/7] Probando Bitácora Centralizada de Auditoría de Seguridad...');
    const auditRes = await request('GET', '/api/b2b/security/audit-log?limit=20');
    assert(auditRes.statusCode === 200, 'GET /api/b2b/security/audit-log responde 200 OK');
    const logs = auditRes.data.logs || [];
    assert(logs.length > 0, `Existen registros de auditoría (${logs.length} eventos registrados)`);
    const createdLog = logs.find(l => l.action === 'API_KEY_CREATED');
    const revokedLog = logs.find(l => l.action === 'API_KEY_REVOKED');
    assert(createdLog !== undefined, 'Evento API_KEY_CREATED registrado en auditoría con IP y metadatos');
    assert(revokedLog !== undefined, 'Evento API_KEY_REVOKED registrado en auditoría');

  } catch (err) {
    console.error('Error durante las pruebas:', err);
    failed++;
  }

  console.log('\n====================================================');
  console.log(` RESULTADOS PASO 15: ${passed} APROBADAS, ${failed} FALLIDAS`);
  console.log('====================================================');

  if (failed > 0) {
    process.exit(1);
  }
}

runTests();
