# Nisiro Project Guidelines (Ai.md)

This document serves as the authoritative engineering constitution for the Nisiro project. All AI agents and developers must strictly adhere to these rules, principles, and constraints.

## 1. Core Engineering Principles

-   **Modular Monolith**: The application is structured into strictly isolated modules (`app/Modules/`). Modules must not directly access each other's database tables or internal classes. Communication occurs only via public Actions, Services, or Events.
-   **Offline-First & Deterministic**: core systems (Bookkeeping, Inventory) must be designed with offline synchronization in mind. This implies:
    -   **UUIDs Everywhere**: All database primary keys must be UUIDs to allow client-side ID generation.
    -   **Idempotency**: All write actions must be idempotent to handle potentially duplicated sync packets.
-   **Immutability**: The ledger is append-only. Never `UPDATE` or `DELETE` a posted `Entry`. Use reversal entries for corrections.
-   **Configuration Over Code**: Business logic (tax rules, reporting categories) should be improved via data/configuration updates, not code deployments.
-   **TDD with Pest**: Test-Driven Development is mandatory. Write the test first.

## 2. Technology Stack & Tooling

-   **Framework**: Laravel 12.x (latest stable)
-   **Language**: PHP 8.4+ (Strict Typing `declare(strict_types=1);` mandatory)
-   **Frontend**: Livewire + Volt + Flux UI (Free).
-   **Database**: PostgreSQL 15+ (One DB, Multiple Schemas via `stancl/tenancy`).
-   **Testing**: Pest PHP.
-   **AI & DX**: Laravel Boost (MCP).

## 3. Development Rules

### 3.1 Architecture & Structure

-   **Directory Structure**: Features live in `app/Modules/{FeatureName}/`.
-   **No Cross-Schema Queries**: Tenants are isolated in separate schemas. Never join across schemas.
-   **Models**: Keep models "dumb". They represent the data shape.
-   **Logic**: Business logic lives in **Services** (computations) or **Actions** (state mutations), never in Controllers or Models.

### 3.2 Coding Standards

-   **Strict Types**: All PHP files must start with `declare(strict_types=1);`.
-   **Return Types**: All methods must have explicit return types.
-   **Constructor Promotion**: Use PHP 8 constructor property promotion.
-   **Naming**: Be verbose and descriptive. `calculateTaxApplicability` is better than `calcTax`.

### 3.3 Testing Strategy (Pest)

-   **Mandatory**: Every feature must have a corresponding test.
-   **Style**: Use Pest's expectation API (`expect($value)->toBeTrue()`).
-   **Multitenancy**: Tests must explicitly handle tenant context when testing tenant-scoped features.
-   **Optimized**: Use `arch()` tests to enforce architectural boundaries.

### 3.4 Laravel Boost & MCP

-   **Documentation**: Before implementing a feature, use the `search-docs` tool (provided by Boost) to check the latest documentation for Laravel, Livewire, or Pest.
-   **Best Practices**: Follow the `laravel-boost-guidelines` (configured in memory).
-   **Conventions**: Adopt the project's existing conventions. If unsure, check a sibling file.

## 4. Functional Constraints (Non-Negotiable)

-   **No Tax Advice**: The system provides "Tax Visibility" but never "Tax Advice". Use strict disclaimers.
-   **No Automated Filing**: The system never submits data to government authorities.
-   **Low Literacy Support**: UI must be clear, simpler, and forgiving.

## 5. Workflow

1.  **Read Specs**: Consult `TechnicalSpecifications.md` before starting a module.
2.  **Plan**: Break down the task.
3.  **Write Test**: Create a Pest test describing the desired behavior.
4.  **Implement**: Write the code to pass the test.
5.  **Refactor**: Optimize while keeping tests green.
