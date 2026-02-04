<?php

namespace App\Repositories;

use App\Database\Connection;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use PDO;

class InvoiceRepository {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    public function getSystemSettings(): array {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM system_settings");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function save(Invoice $invoice): int {
        $stmt = $this->db->prepare("INSERT INTO invoices 
            (invoice_number, invoice_date, due_date, customer_name, customer_address, subtotal, tax_total, grand_total) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $invoice->invoice_number,
            $invoice->invoice_date,
            $invoice->due_date,
            $invoice->customer_name,
            $invoice->customer_address,
            $invoice->subtotal,
            $invoice->tax_total,
            $invoice->grand_total
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function saveItem(int $invoiceId, InvoiceItem $item): void {
        $stmt = $this->db->prepare("INSERT INTO invoice_items (invoice_id, description, is_taxed, amount) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $invoiceId,
            $item->description,
            $item->is_taxed ? 1 : 0,
            $item->amount
        ]);
    }

    public function updateSetting(string $key, $value): void {
        $stmt = $this->db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    }

    public function getConnection(): PDO {
        return $this->db;
    }
}
