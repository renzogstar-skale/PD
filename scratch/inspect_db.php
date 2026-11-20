<?php
define('GROCY_DATAPATH', __DIR__ . '/../data');
define('GROCY_IS_EMBEDDED_INSTALL', false);
define('GROCY_USER_ID', 1);

require __DIR__ . '/../app.php';
$db = \Grocy\Services\DatabaseService::GetInstance()->GetDbConnectionRaw();

echo "TABLES:\n";
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND (name LIKE 'b2b%' OR name LIKE 'cust%' OR name LIKE 'branch%') ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);

foreach (['customers', 'customer_establishments', 'b2b_invoices', 'b2b_orders', 'b2b_credit_notes', 'b2b_sales_reps', 'branches'] as $t) {
    echo "\n--- TABLE $t COLUMNS ---\n";
    $cols = $db->query("PRAGMA table_info($t)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "{$c['name']} ({$c['type']})\n";
    }
}
