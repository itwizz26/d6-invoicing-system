-- Create database
CREATE DATABASE IF NOT EXISTS `invoicing`;
USE invoicing;

-- The products table
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

-- Seed Initial Settings
CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

INSERT INTO system_settings (setting_key, setting_value) VALUES 
('company_name', 'InThe-Loop Software Solutions'),
('company_address', '123 Innovation Drive\nSilicon Valley, CA 94025'),
('company_phone', '555-010-9988'),
('company_fax', '555-010-9989'),
('company_website', 'www.intheloop.tech'),
('contact_info', 'John Doe, 555-010-9988, support@intheloop.tech'),
('current_invoice_number', '1001'),
('current_customer_id', 'CUST-001'),
('current_currency', 'R'),
('current_tax_rate', 6.250);
