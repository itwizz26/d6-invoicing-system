<?php

namespace App\Models;

class InvoiceItem {
    public $id;
    public $invoice_id;
    public $description;
    public $is_taxed;
    public $amount;

    public function __construct($description, $is_taxed, $amount) {
        $this->description = $description;
        $this->is_taxed = (bool)$is_taxed;
        $this->amount = (float)$amount;
    }
}
