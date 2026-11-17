/**
 * Suite de Pruebas Automatizadas: PASO 14 — DOCKERIZACIÓN, VARIABLES DE ENTORNO Y PRODUCCIÓN
 * Valida la preparación para despliegue en la nube (Railway / Docker / Cloud Run),
 * configuraciones de producción, health checks y CI/CD.
 */

const http = require('http');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://127.0.0.1:8080';

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

async function runStep14Audit() {
    console.log('======================================================================');
    console.log('INICIANDO AUDITORÍA PASO 14 — PREPARACIÓN PARA PRODUCCIÓN & DESPLIEGUE');
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
        const rootDir = path.join(__dirname, '..');

        // 1. DOCKERFILE & CONTENERIZACIÓN
        console.log('\n--- 1. CONTENERIZACIÓN DOCKER ---');
        const dockerfilePath = path.join(rootDir, 'Dockerfile');
        assert(fs.existsSync(dockerfilePath), 'Archivo Dockerfile presente en la raíz');
        const dockerContent = fs.readFileSync(dockerfilePath, 'utf8');
        assert(dockerContent.includes('FROM php:8.5-apache'), 'Dockerfile utiliza imagen base PHP 8.5 Apache');
        assert(dockerContent.includes('pdo_sqlite') && dockerContent.includes('pdo_pgsql'), 'Extensiones pdo_sqlite y pdo_pgsql compiladas en Dockerfile');
        assert(dockerContent.includes('GROCY_MODE=production'), 'Modo producción configurado por defecto en Dockerfile');

        // 2. DOCKER ENTRYPOINT SCRIPT
        console.log('\n--- 2. DOCKER ENTRYPOINT & SEMBRADO EN VOLÚMENES ---');
        const entrypointPath = path.join(rootDir, 'docker-entrypoint.sh');
        assert(fs.existsSync(entrypointPath), 'Script docker-entrypoint.sh presente');
        const entryContent = fs.readFileSync(entrypointPath, 'utf8');
        assert(entryContent.includes('demo-seed'), 'Entrypoint copia automáticamente seed inicial si el volumen está vacío');
        assert(entryContent.includes('MigrateDatabase'), 'Entrypoint ejecuta migraciones versionadas (incluyendo 0339) al arrancar');
        assert(entryContent.includes('PORT'), 'Entrypoint soporta asignación dinámica de puerto ($PORT) para Railway/Cloud');

        // 3. DOCKER COMPOSE ORQUESTACIÓN LOCAL / STAGING
        console.log('\n--- 3. ORQUESTACIÓN DOCKER COMPOSE ---');
        const composePath = path.join(rootDir, 'docker-compose.yml');
        assert(fs.existsSync(composePath), 'Archivo docker-compose.yml presente');
        const composeContent = fs.readFileSync(composePath, 'utf8');
        assert(composeContent.includes('b2b_data:'), 'Volumen persistente b2b_data configurado para preservar la base de datos');

        // 4. VARIABLES DE ENTORNO Y CONFIGURACIÓN
        console.log('\n--- 4. VARIABLES DE ENTORNO & CONFIGURACIÓN SEGURA ---');
        const envExamplePath = path.join(rootDir, '.env.example');
        assert(fs.existsSync(envExamplePath), 'Plantilla .env.example documentada presente');
        const envContent = fs.readFileSync(envExamplePath, 'utf8');
        assert(envContent.includes('GROCY_MODE=production'), 'GROCY_MODE=production documentado en .env.example');
        assert(envContent.includes('PORT='), 'Variable PORT documentada para Railway / Render');
        assert(envContent.includes('DATABASE_URL='), 'Variable DATABASE_URL documentada para PostgreSQL/Prisma');

        // 5. CI/CD GITHUB ACTIONS
        console.log('\n--- 5. PIPELINE CI/CD GITHUB ACTIONS ---');
        const ciPath = path.join(rootDir, '.github', 'workflows', 'ci.yml');
        assert(fs.existsSync(ciPath), 'Workflow de CI (.github/workflows/ci.yml) presente');
        const ciContent = fs.readFileSync(ciPath, 'utf8');
        assert(ciContent.includes('test_b2b_step13.js'), 'Pipeline ejecuta suite de base de datos antes de cada release');

        // 6. HEALTHCHECK ENDPOINT EN TIEMPO REAL
        console.log('\n--- 6. HEALTHCHECK ENDPOINT EN TIEMPO REAL ---');
        const healthRes = await getJson('/api/system/health');
        assert(healthRes.status === 200, 'Endpoint /api/system/health responde HTTP 200 OK');
        const healthData = healthRes.body;
        assert(healthData.status === 'healthy', `Estado del sistema: ${healthData.status}`);
        assert(healthData.database === 'connected', `Estado de conexión de base de datos: ${healthData.database}`);
        assert(healthData.platform.includes('Prince Distributors'), `Identidad de plataforma verificada: ${healthData.platform}`);
        assert(healthData.version === '1.0.0', `Versión de producción verificada: ${healthData.version}`);

    } catch (err) {
        console.error('ERROR EN SUITE PASO 14:', err.message);
        failedTests++;
    }

    console.log('\n======================================================================');
    console.log(`RESULTADO PASO 14: ${passedTests} APROBADAS | ${failedTests} FALLIDAS`);
    console.log('======================================================================\n');

    if (failedTests > 0) {
        process.exit(1);
    }
}

runStep14Audit();
