# Phase 3: Bookkeeping Core

**Goal**: Build the immutable double-entry ledger system. This is the most critical domain.

## 3.1 Bookkeeping Module Scaffolding

-   [ ] Create `Modules/Bookkeeping`.
-   [ ] Register Service Provider.

## 3.2 Core Domain Models (In Tenant Schema)

-   [ ] `AccountCategory` (Assets, Liabilities, Equity, Revenue, Expense).
-   [ ] `Ledger` (Container for entries).
-   [ ] `Entry` (The transaction header: Date, Description, Reference).
-   [ ] `EntryLine` (Debit/Credit lines: Amount, Account ID, Notes).
-   [ ] **Constraint**: All IDs must be UUIDs.

## 3.3 Business Logic (Services & Actions)

-   [ ] `DoubleEntryService`: Validates that Sum(Debits) == Sum(Credits).
-   [ ] `PostTransactionAction`: Atomic action to create Entry + Lines.
-   [ ] `AccountBalanceService`: Computes balances (optimized via caching later, but pure calculation first).

## 3.4 Immutability & ACID

-   [ ] Database Triggers (optional) or App-Level Guard to prevent UPDATE/DELETE on committed entries.
-   [ ] Wrap all posting actions in Database Transactions.

## 3.5 Categorization

-   [ ] Seed Default Chart of Accounts for common business types (Retail vs Service).
-   [ ] Action to customize categories (Rename only, no changing type after use).

## 3.6 Testing (The most rigorous part)

-   [ ] Logic Test: Debits must equal Credits.
-   [ ] Logic Test: Preventing deletion of posted entries.
-   [ ] Logic Test: Balance calculation accuracy.
-   [ ] Stress Test: Concurrency (Locking mechanisms).
