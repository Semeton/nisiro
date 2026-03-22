This document defines the technical specification for Nisiro, a modular, multitenant, offline-capable bookkeeping and business record system for Nigerian SMEs. It is deliberately written to support agentic development, meaning:
Clear boundaries between modules
Deterministic behavior and contracts
High testability and low coupling
Configurability over hard‑coding
This specification translates the Functional Requirements Document (FRD) into engineering‑ready architecture, patterns, and constraints.
Nisiro is not a tax software. It is a record‑keeping and computation‑exposure platform designed to accept tax logic via configuration and external specifications without refactoring core systems.

2. Non‑Negotiable Engineering Principles
   These principles override convenience, speed, or personal preference.
   Simplicity over cleverness
   Every abstraction must justify itself in reduced cognitive load.
   Configuration over code
   System behavior should change via data, not deployments.
   Strong boundaries
   Modules do not reach into each other’s internals.
   Single responsibility everywhere
   Controllers, services, actions, and jobs do one thing.
   Deterministic computation
   Given the same inputs, the system must produce the same outputs.
   Offline‑first by design
   Online is an optimization, not an assumption.
   ACID compliance for all persisted state
   Test‑Driven Development is mandatory

3. Technology Stack
   3.1 Core Stack
   PHP 8.4
   Laravel (latest stable)
   Laravel Livewire (UI interaction layer)
   MySQL 8.0+
   Redis (optional, non‑critical paths only)
   3.2 Multitenancy
   Single Database (Row-level Isolation)
   Tenant isolation via unique ID (tenant_id) scoping
   One database, shared tables
   Rationale:
   Simpler onboarding and standalone installations
   Ease of maintenance and cloud-native scaling
   Unified schema for easier updates

4. High‑Level System Architecture
   4.1 Architectural Style
   Modular Monolith
   Domain‑driven module boundaries
   No shared global state between modules
   app/
   └── Modules/
   ├── Tenancy/
   ├── Identity/
   ├── Bookkeeping/
   ├── Inventory/
   ├── Reporting/
   ├── RulesEngine/
   ├── Sync/
   └── System/

Each module is independently understandable and testable.

5. Multitenancy Design
   5.1 Tenant Lifecycle
   Tenant creation triggers:
   Record creation in `tenants` table
   Base configuration seeding
   Default roles and permissions
   Tenant context resolution:
   Subdomain
   Custom domain
   Explicit header (for offline sync)
   5.2 Cross‑Tenant Isolation Rules
   Strict Row-Level Scoping via `tenant_id`
   No cross-tenant data access without explicit system-level permission
   Shared tables include:
   tenants
   system_users
   system_audit_logs

6. Identity, Roles, and Access Control
   6.1 Role Hierarchy
   Roles are configurable, not hard‑coded.
   Default roles:
   system_admin (platform‑wide)
   super_admin (tenant owner)
   admin
   operations_manager
   accountant
   legal
   auditor (read‑only)
   6.2 Permission Model
   Role → Permissions
   Permission → Action + Resource
   No role checks inside business logic. Only permissions.

7. Module Specification
   7.1 Common Module Structure
   Every module must follow this layout:
   Modules/Feature/
   ├── Http/
   │ ├── Controllers/
   │ ├── Requests/
   ├── Actions/
   ├── Services/
   ├── Repositories/
   ├── Models/
   ├── Policies/
   ├── Events/
   ├── Listeners/
   ├── Reports/
   ├── Traits/
   ├── Tests/
   └── module.php

Single‑action controllers only.

8. Bookkeeping Module (Core Domain)
   8.1 Core Concepts
   Ledger
   Entry
   Line Item
   Account Category
   Credit / Debit polarity
   No accounting assumptions are hard‑coded.
   8.2 Data Model (Simplified)
   ledgers
   entries
   entry_lines
   account_categories
   All computations occur in pure services, never in models.

9. Inventory Module
   9.1 Design Principles
   Inventory is supplementary to bookkeeping
   Inventory movements emit bookkeeping events
   Inventory can be disabled per tenant
   9.2 Stock Movement Model
   stock_items
   stock_batches
   stock_movements
   No valuation logic is enforced.

10. Reporting Module
    10.1 Report Definition
    Reports are data‑driven, not code‑driven.
    report_definitions (JSON)
    report_parameters
    report_renderers
    10.2 Output Formats
    On‑screen (Livewire)
    PDF
    CSV

11. Rules Engine Module (Pre‑Tax)
    11.1 Purpose
    Evaluate business data
    Classify outcomes
    Expose results
    No enforcement.
    11.2 Rule Format
    JSON or YAML
    Versioned
    Hot‑reloadable
    Rules operate on normalized computation outputs, not raw data.

12. Offline‑First & Sync Module
    12.1 Offline Strategy
    Local SQLite store (client side)
    UUID‑based entity IDs
    Append‑only mutation logs
    12.2 Sync Guarantees
    Idempotent writes
    Conflict resolution via:
    timestamps
    version vectors
    No destructive merges.

13. System Control Module
    13.1 Capabilities
    Tenant management
    Feature toggles
    System‑wide configurations
    Emergency tenant isolation
    All controls are auditable.

14. Testing Strategy
    14.1 Mandatory TDD
    Write tests first
    Red → Green → Refactor
    14.2 Test Types
    Unit tests (Actions, Services)
    Module integration tests
    Sync conflict tests
    No brittle UI tests initially.

15. Configurability
    15.1 Configuration Layers
    System
    Tenant
    Module
    Configurations are stored in database, not env files.

16. Extensibility for Tax Specification
    Tax logic will integrate via:
    Rules Engine extensions
    Report definitions
    Configuration schemas
    No core refactor should be required.

17. What Is Explicitly Out of Scope (For Now)
    Payment processing
    Filing submissions
    Government integrations
    Automated tax remittance

18. Final Engineering Mandate
    If a feature:
    Breaks modularity
    Requires cross‑module state mutation
    Introduces hidden coupling
    It must be rejected or redesigned.
    This document is the technical constitution of Nisiro.
