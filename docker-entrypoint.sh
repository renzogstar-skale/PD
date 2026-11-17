#!/bin/sh
set -e

# El volumen de data/ llega vacío la primera vez
DATA=/var/www/html/data
mkdir -p "$DATA/viewcache" "$DATA/storage" "$DATA/plugins" "$DATA/logs"
[ -f "$DATA/config.php" ] || cp /var/www/html/config-dist.php "$DATA/config.php"

# Si no existe base de datos en el volumen persistente, sembrar desde demo-seed
if [ ! -f "$DATA/grocy_es.db" ] && [ ! -f "$DATA/grocy.db" ]; then
    if [ -f /var/www/html/demo-seed/grocy_es.db ]; then
        echo "Inicializando base de datos B2B desde demo-seed..."
        cp /var/www/html/demo-seed/grocy_es.db "$DATA/grocy_es.db"
    fi
fi

# Ejecutar migraciones automáticas (incluye migración 0339 para B2B)
php -r "require_once '/var/www/html/packages/autoload.php'; define('GROCY_DATAPATH', '$DATA'); define('GROCY_IS_EMBEDDED_INSTALL', false); require_once '/var/www/html/app.php'; (new \Grocy\Services\DatabaseMigrationService())->MigrateDatabase();" || true

chown -R www-data:www-data "$DATA"

# Railway / Render / Cloud Run / Heroku asigna el puerto en $PORT
PORT="${PORT:-80}"
sed -ri "s/^Listen .*/Listen $PORT/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
