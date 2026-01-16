# Phase 4: Inventory Module

**Goal**: Implement inventory tracking that feeds into bookkeeping.

## 4.1 Inventory Module Scaffolding

-   [ ] Create `Modules/Inventory`.
-   [ ] Register Service Provider.

## 4.2 Data Models

-   [ ] `StockItem` (Name, SKU, Cost, Price, Reorder Level).
-   [ ] `StockBatch` (For FIFO/LIFO tracking if needed, or simple weighted avg).
-   [ ] `StockMovement` (In, Out, Adjustment).

## 4.3 Integration with Bookkeeping

-   [ ] Event: `StockMovementCreated`.
-   [ ] Listener (in Bookkeeping Module): `PostInventoryJournal`.
-   [ ] This ensures decoupling. Inventory just says "Item moved", Bookkeeping decides "Debit COGS, Credit Asset".

## 4.4 Actions

-   [ ] `PurchaseStockAction`.
-   [ ] `SellStockAction` (Decrements stock).
-   [ ] `AdjustStockAction` (Loss/Theft).

## 4.5 Testing

-   [ ] Test: Stock count accuracy.
-   [ ] Integration Test: Buying stock creates a Ledger Entry.
-   [ ] Integration Test: Selling stock creates Cost of Goods Sold Entry.
