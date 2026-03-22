# Nisiro Implementation Master Plan

This document outlines the step-by-step implementation plan for **Nisiro**, based on the provided Technical Specifications and Functional Requirements.

## Project Phases

The implementation is divided into 6 distinct phases to ensure modularity, testability, and clarity.

### [Phase 1: Setup & Architecture](./01_Phase_1_Setup_and_Architecture.md)

**Goal**: Establish the engineering foundation, module structure, and multitenancy architecture.

- Laravel Installation & Configuration (Laravel Installation already done. Next is validating the key configurations)
- Modular Monolith Directory Structure
- Single Database Tenancy (Scoped via Tenant IDs & UUIDs)
- Base System Module
- Testing Infrastructure

### [Phase 2: Identity & Access Control](./02_Phase_2_Identity_and_Access.md)

**Goal**: Implement secure, multitenant authentication and role-based access control.

- Identity Module Structure
- Users, Roles, and Permissions Models
- Tenant-aware Authentication
- Policy Implementation

### [Phase 3: Bookkeeping Core](./03_Phase_3_Bookkeeping_Core.md)

**Goal**: Build the immutable double-entry ledger system (The Heart of Nisiro).

- Bookkeeping Module Structure
- Ledger, Entry, EntryLine Models
- Transaction Services
- Immutability Guards
- Core Accounting Tests

### [Phase 4: Inventory Management](./04_Phase_4_Inventory_Module.md)

**Goal**: implement inventory tracking that feeds into the bookkeeping system.

- Inventory Module Structure
- Stock Items & Batches
- Movement Recording
- Integration with Bookkeeping (Event-driven)

### [Phase 5: Rules Engine & Reporting](./05_Phase_5_Rules_and_Reporting.md)

**Goal**: Create the flexible configuration-driven logic and reporting layers.

- Rules Engine Module
- JSON/YAML Rule Parsing
- Reporting Module
- Dynamic Report Rendering (P&L, Cashflow)

### [Phase 6: Offline-First & Sync](./06_Phase_6_Offline_Sync.md)

**Goal**: Enable offline capabilities and robust data synchronization.

- Sync Module
- API Endpoints for Sync
- Conflict Resolution Strategy
- UUID & Version Vector Implementation

---

## Guiding Principles (Reminders)

- **TDD is Mandatory**: Write tests before code.
- **Strict Boundaries**: Modules communicate via defined interfaces/events only.
- **Config over Code**: Behavior changes via data.
- **Offline-First**: Design APIs and IDs (UUIDs) to support offline sync from day one.
