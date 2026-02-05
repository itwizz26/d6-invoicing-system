# Invoice Management Module

A modular, service-oriented PHP invoicing system designed as a component of a larger enterprise ecosystem.

## Architecture & Design Patterns

The system follows a strict Layered Architecture and adheres to PSR-4 Autoloading standards for organized, namespace-driven development.

* **Controller (`App\Controllers`)**: The entry point for HTTP requests. It handles input/output and HTTP status codes.

* **Service (`App\Services`)**: The business logic layer. It enforces domain rules (e.g., the 3-day rule) and manages database transactions.

* **Repository (`App\Repositories`)**: The data access layer. It abstracts SQL complexity and provides a clean interface for data persistence.

* **Models (`App\Models`)**: The data structures (DTOs). They handle self-validation and internal math (re-calculating tax totals to ensure data integrity).

* **Database Connection (`App\Database`)**: A Singleton pattern ensuring a single, efficient PDO connection across the application lifecycle.

## Database Schema & PSR-4

### 1. Schema Definition
The database structure is defined in database/schema.sql. It utilizes:

* **Relational Mapping**: A one-to-many relationship between invoices and invoice_items.

* **Key-Value Configuration**: A `system_settings` table to manage global state like tax rates and document numbering.

* **Data Integrity**: Foreign key constraints with `ON DELETE CASCADE` to maintain referential integrity.

### 2. Autoloading

This project implements the `PSR-4` Autoloading standard via Composer. This eliminates the need for multiple manual require_once calls and ensures that classes are loaded only when needed based on their namespace (`App\`).

```
"autoload": {
    "psr-4": {
        "App\\": "src/"
    }
}
```

## Getting Started

### 1. Prerequisites

* PHP 8.x
* Composer
* MySQL/MariaDB

### 2. Installation

1. Clone the repository (repo-url) to your local environment and change directory into your new repo.
2. Install dependencies.

```
composer install
```

3. Copy `.env.example` into `.env` and update your database credentials accoridngly.

```
cp .env.example .env && nano .env
```

4. Database Initialisation (Seeding)

The system comes with a seeder that creates the necessary schema and populates system-wide settings (Tax rates, Company Info, and Counters).

```
php database/seed.php
```

### 3. Running the server

To run the server, execute this command and open the api endpoint at `/api/invoice.php`. Should see a json repsonse with the System Settings from the seeded data.

```
php -S localhost:8000
```

### Key System Features

* **3-Day Rule**: Server-side enforcement ensuring due dates are at least 3 days in the future.

* **Math Integrity**: The system re-calculates all totals on the server side to prevent frontend data tampering.

* **Auto-incrementing Identifiers**: Automatically manages INV-XXXX and CUST-XXXX sequencing via the system_settings table.

* **Atomic Transactions**: Uses PDO transactions to ensure that an invoice is only saved if all line items are successfully processed.
