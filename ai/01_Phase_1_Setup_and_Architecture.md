# Phase 1: Setup & Architecture

**Goal**: Establish the engineering foundation, modular structure, and multitenancy architecture.

## 1.1 Project Initialization

-   [ ] Install Laravel 11.x (PHP 8.4)
-   [ ] Configure `composer.json` for strict typing and standard scripts.
-   [ ] specific `.env.example` configuration.
-   [ ] Initialize Git repository.

## 1.2 Modular Monolith Structure

-   [ ] Create `app/Modules` directory.
-   [ ] Define the directory structure for a generic module (Action: Create a generator command or script if helpful, otherwise manual structure for now).
    ```
    app/Modules/
    ├── System/
    ├── Tenancy/
    ├── Identity/
    └── ...
    ```
-   [ ] Configure `composer.json` to autoload `app/Modules`.
-   [ ] Create a `ModuleServiceProvider` to discover and register module service providers.

## 1.3 Tenancy Setup

-   [ ] Install `stancl/tenancy`.
-   [ ] Publish configuration.
-   [ ] Configure Tenant Model (`Tenant` inside `Modules/Tenancy`).
-   [ ] Configure Bootstrappers (Database Separation - Postgres Schemas).
-   [ ] Set up Domains/Central Routes vs Tenant Routes.
-   [ ] specific `pgschema` configuration for PostgreSQL.

## 1.4 System Module & Base infrastructure

-   [ ] Create `Modules/System` (Core shared traits, interfaces, DTOs).
-   [ ] Implement `UUID` traits for models (Crucial for offline sync).
-   [ ] Set up Base Repository/Service interfaces.

## 1.5 Testing Infrastructure

-   [ ] Configure `phpunit.xml` (or `pest`).
-   [ ] Create a `TestCase` that supports multitenancy testing (Tenant creation traits).
-   [ ] Verify standard "Can create a tenant" test.
