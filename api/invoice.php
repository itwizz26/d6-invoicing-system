<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\InvoiceRepository;
use App\Controllers\InvoiceController;
use App\Services\InvoiceService;

$repository = new InvoiceRepository();
$service = new InvoiceService($repository);

$controller = new InvoiceController($repository, $service);
$controller->handleRequest();
