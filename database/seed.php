<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Connection;

// Set to true if you want to wipe all invoices and start fresh
$resetTransactions = false; 

try {
    $pdo = Connection::getInstance();
    echo "--- Initializing Database Migration & Seeding ---\n";

    // 1. Table Creation Logic
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS invoices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_number VARCHAR(20) NOT NULL,
            invoice_date DATE NOT NULL,
            due_date DATE NOT NULL,
            customer_name VARCHAR(255),
            customer_address TEXT,
            subtotal DECIMAL(10,2),
            tax_total DECIMAL(10,2),
            grand_total DECIMAL(10,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS invoice_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_id INT,
            description TEXT,
            is_taxed BOOLEAN DEFAULT 0,
            amount DECIMAL(10,2),
            FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS system_settings (
            setting_key VARCHAR(50) PRIMARY KEY,
            setting_value TEXT
        );
    ");

    // 2. Transaction Reset (Optional)
    if ($resetTransactions) {
        echo "⚠️ Resetting transactions...\n";
        // Disable foreign keys temporarily to truncate linked tables
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("TRUNCATE TABLE invoice_items;");
        $pdo->exec("TRUNCATE TABLE invoices;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        echo "✅ Invoices and items cleared.\n";
    }

    // 3. Seed Data
    $seeds = [
        'company_name'           => 'InThe-Loop Software Solutions',
        'company_address'        => "123 Innovation Drive\nSilicon Valley, CA 94025",
        'company_phone'          => '555-010-9988',
        'company_fax'            => '555-010-9989',
        'company_website'        => 'www.intheloop.tech',
        'contact_info'           => 'John Doe, 555-010-9988, support@intheloop.tech',
        'current_invoice_number' => '1001',
        'current_customer_id'    => 'CUST-001',
        'current_currency'       => 'R',
        'current_tax_rate'       => '6.250'
    ];

    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) 
                           VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

    foreach ($seeds as $key => $value) {
        $stmt->execute([$key, $value]);
    }

    echo "✅ System settings seeded/updated.\n";
    echo "--- Migration Complete ---\n";

} catch (Exception $e) {
    echo "❌ Migration Error: " . $e->getMessage() . "\n";
}
