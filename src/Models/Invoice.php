<?php

namespace App\Models;

class Invoice {
    public $id;
    public $invoice_number;
    public $invoice_date;
    public $due_date;
    public $customer_name;
    public $customer_address;
    public $subtotal;
    public $tax_total;
    public $grand_total;
    public $items = [];

    public function syncTotals(float $taxRatePercentage) {
        $this->subtotal = 0;
        $taxableSubtotal = 0;

        foreach ($this->items as $item) {
            $this->subtotal += $item->amount;
            if ($item->is_taxed) {
                $taxableSubtotal += $item->amount;
            }
        }

        $this->tax_total = $taxableSubtotal * ($taxRatePercentage / 100);
        $this->grand_total = $this->subtotal + $this->tax_total;
    }
}
