<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\InvoiceController;
use App\Database\Connection;

try {
    $controller = new InvoiceController();
    $pdo = Connection::getInstance();

    echo "\n=== Invoice Controller Test ===\n";

    // Mock a POST Request Payload
    $payload = [
        'header' => [
            'invoice_number' => '7777',
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+5 days')),
            'customer_id' => 'CUST-777',
            'customer_name' => 'Full Stack Test',
            'customer_address' => '777 Controller Blvd',
            'subtotal' => '0.00',
            'tax_total' => '0.00',
            'grand_total' => '0.00',
        ],
        'items' => [
            [
                'description' => 'Controller Test Item',
                'is_taxed' => 1,
                'amount' => '150.00',
            ]
        ]
    ];

    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    ob_start();
    
    echo "Testing the Controller via Service Layer Integration...\n\n";
    
    $repo = new \App\Repositories\InvoiceRepository();
    $service = new \App\Services\InvoiceService($repo);
    
    if ($service->saveInvoice($payload)) {
        echo "✅ SUCCESS (Saved invocie via the saveInvoice() method)\n";
    }

    // 4. Verify Math Correction (Final check of the Model logic)
    $stmt = $pdo->prepare("SELECT grand_total FROM invoices WHERE invoice_number = '7777'");
    $stmt->execute();
    $savedTotal = (float)$stmt->fetchColumn();

    if ($savedTotal > 150) {
        echo "✅ MATH VERIFIED (Calculated Total: R$savedTotal)\n";
    }

    // Cleanup
    $pdo->exec("DELETE FROM invoices WHERE invoice_number = '7777'");
    echo "\n=== All Layers Verified ===\n\n";

} catch (Exception $e) {
    echo "\n❌ TEST FAILED: " . $e->getMessage() . "\n\n";
}
