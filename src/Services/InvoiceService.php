<?php

namespace App\Services;

use App\Repositories\InvoiceRepository;
use App\Models\InvoiceItem;
use App\Models\Invoice;
use Exception;
use DateTime;

class InvoiceService {
    
    public function __construct(
        protected InvoiceRepository $repo = new InvoiceRepository()
    ) {}

    public function saveInvoice(array $data): bool {
        $settings = $this->repo->getSystemSettings();
        $invoice = new Invoice();
        $invoice->invoice_number = $data['header']['invoice_number'];
        $invoice->invoice_date = $data['header']['invoice_date'];
        $invoice->due_date = $data['header']['due_date'];
        $invoice->customer_name = $data['header']['customer_name'];
        $invoice->customer_address = $data['header']['customer_address'];

        foreach ($data['items'] as $itemData) {
            $invoice->items[] = new InvoiceItem(
                $itemData['description'],
                $itemData['is_taxed'],
                $this->sanitizeMoney($itemData['amount'])
            );
        }

        $invoice->syncTotals((float)($settings['current_tax_rate'] ?? 0));
        $this->validateDueDate($invoice->due_date);

        $db = $this->repo->getConnection();
        $db->beginTransaction();
        try {
            $invoiceId = $this->repo->save($invoice);

            foreach ($invoice->items as $item) {
                $this->repo->saveItem($invoiceId, $item);
            }

            $this->incrementCounters($invoice->invoice_number, $data['header']['customer_id']);

            $db->commit();
            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    private function validateDueDate(string $dateString): void {
        $dueDate = new DateTime($dateString);
        $minDate = (new DateTime())->modify('+3 days')->setTime(0, 0, 0);

        if ($dueDate < $minDate) {
            throw new Exception("Validation Error: Due date must be at least 3 days in the future.");
        }
    }

    private function incrementCounters(string $invNum, string $custId): void {
        $this->repo->updateSetting('current_invoice_number', (int)$invNum + 1);

        preg_match('/\d+/', $custId, $matches);
        $nextNum = (int)($matches[0] ?? 0) + 1;
        $nextId = 'CUST-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        $this->repo->updateSetting('current_customer_id', $nextId);
    }

    private function sanitizeMoney($value): float {
        return (float) preg_replace('/[^\d.-]/', '', $value);
    }
}
