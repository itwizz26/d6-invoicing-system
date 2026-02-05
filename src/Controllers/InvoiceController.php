<?php

namespace App\Controllers;

use App\Repositories\InvoiceRepository;
use App\Services\InvoiceService;
use Exception;

class InvoiceController {
    private $service;
    private $repository;

    public function __construct() {
        $this->repository = new InvoiceRepository();
        $this->service = new InvoiceService($this->repository);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        header('Content-Type: application/json');

        try {
            if ($method === 'GET') {
                $this->getSettings();
            } elseif ($method === 'POST') {
                $this->createInvoice();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method Not Allowed']);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    private function getSettings() {
        $settings = $this->repository->getSystemSettings();
        echo json_encode($settings ?: []);
        exit();
    }

    private function createInvoice() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            throw new Exception("Invalid JSON input.");
        }

        $success = $this->service->saveInvoice($data);
        
        echo json_encode(['status' => 'success', 'success' => $success]);
        exit();
    }
}
