# Phase 1: Setup & Architecture

**Goal**: Establish the engineering foundation, modular structure, and multitenancy architecture.

## 1.1 Project Initialization

- [ ] Install Laravel 11.x (PHP 8.4)
- [ ] Configure `composer.json` for strict typing and standard scripts.
- [ ] specific `.env.example` configuration.
- [ ] Initialize Git repository.

## 1.2 Modular Monolith Structure

- [ ] Create `app/Modules` directory.
- [ ] Define the directory structure for a generic module (Action: Create a generator command or script if helpful, otherwise manual structure for now).
    ```
    app/Modules/
    ├── System/
    ├── Tenancy/
    ├── Identity/
    └── ...
    ```
- [ ] Configure `composer.json` to autoload `app/Modules`.
- [ ] Create a `ModuleServiceProvider` to discover and register module service providers.

## 1.3 Tenancy Setup (Single Database)

- [ ] Design `tenants` table (Unique IDs, Domain/Subdomain, Settings).
- [ ] Implement `Tenant` model in `Modules/System` or `Modules/Tenancy`.
- [ ] Implement `BelongsToTenant` trait with Global Scope for auto-scoping queries.
- [ ] Configure Middleware to identify the current tenant (via Domain or Header).
- [ ] Ensure `UUID`s/`ULID`s are used as primary keys for all tenant-related tables.
- [ ] MySQL optimization: indexing `tenant_id` on all tables.

## 1.4 System Module & Base infrastructure

- [ ] Create `Modules/System` (Core shared traits, interfaces, DTOs).
- [ ] Implement `UUID` traits for models (Crucial for offline sync).
- [ ] Set up Base Repository/Service interfaces.

## 1.5 Testing Infrastructure

- [ ] Configure `phpunit.xml` (or `pest`).
- [ ] Create a `TestCase` that supports multitenancy testing (Tenant creation traits).
- [ ] Verify standard "Can create a tenant" test.
