<?php
define('GROCY_DATAPATH', __DIR__ . '/../data');
require __DIR__ . '/../app.php';
$db = \Grocy\Services\DatabaseService::GetInstance()->GetDbConnectionRaw();

$tablesToInspect = [
    'b2b_products',
    'b2b_brands',
    'b2b_categories',
    'b2b_product_costs',
    'b2b_price_history',
    'b2b_invoice_items',
    'b2b_order_items',
    'b2b_credit_note_items',
    'b2b_intelligence_thresholds',
    'stock',
    'branches',
    'suppliers'
];

foreach ($tablesToInspect as $t) {
    echo "=== TABLE: $t ===\n";
    try {
        $cols = $db->query("PRAGMA table_info($t)")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "  {$c['name']} ({$c['type']})\n";
        }
        $count = $db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "  [Row count: $count]\n";
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}
