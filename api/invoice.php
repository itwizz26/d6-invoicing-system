<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\InvoiceController;

$controller = new InvoiceController();
$controller->handleRequest();
