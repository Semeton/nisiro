# Phase 5: Rules Engine & Reporting

**Goal**: Create dynamic reporting and visibility without hard-coding tax logic.

## 5.1 Rules Engine Module

-   [ ] Create `Modules/RulesEngine`.
-   [ ] Design JSON Schema for Rules (Conditions, Actions, Output).
-   [ ] Implement `RuleEvaluatorService` (Takes Context + Ruleset -> Returns Result).
-   [ ] Usage: "If Revenue > X and State is Y, Tax Visibility = Applicable".

## 5.2 Reporting Module

-   [ ] Create `Modules/Reporting`.
-   [ ] `ReportDefinition` registry.
-   [ ] `DataAggregator`: Fetches raw data from Bookkeeping/Inventory.

## 5.3 Standard Reports

-   [ ] Implement `ProfitAndLossReport` (Livewire Component).
-   [ ] Implement `CashFlowReport`.
-   [ ] Implement `TaxvisibilityReport` (Powered by Rules Engine).

## 5.4 Exporting

-   [ ] PDF Export Service (using Browsershot or DomPDF).
-   [ ] CSV Export Action.

## 5.5 Testing

-   [ ] Unit Test: Rule Engine correctly evaluates JSON logic.
-   [ ] Feature Test: P&L accurately sums ledger entries for a period.
