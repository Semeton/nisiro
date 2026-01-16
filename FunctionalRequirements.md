1. Purpose and Scope
   Nisiro is a software product designed to help Nigerian SMEs maintain structured, credible, and intelligible business records. Its primary purpose is to enable accurate bookkeeping, basic financial insight, and conservative tax visibility without acting as a tax authority, tax advisor, or filing system.
   This document defines what the system must do, not how it is implemented. It deliberately avoids technical architecture details and focuses on functional behavior, constraints, and user-visible outcomes.
2. Product Positioning and Boundaries
   2.1 What Nisiro Is
   A bookkeeping and business record system
   A financial clarity and reporting tool
   A tax visibility and awareness engine (non-prescriptive)
   A software product, not a managed service
   2.2 What Nisiro Is Not
   A tax filing system
   A tax advisory or compliance authority
   An accounting outsourcing platform
   A government reporting or enforcement tool
   All functional requirements must respect these boundaries.
3. User Types and Roles
   3.1 Business Owner (Primary User)
   Capabilities:
   Record business transactions
   View reports and summaries
   Manage categories and inventory
   View tax visibility indicators
   Limitations:
   Cannot alter system-level rules
   Cannot override tax logic
   3.2 Business Staff (Optional e.g accountant, legal, operations etc)
   Capabilities:
   Record transactions
   View limited reports
   Limitations:
   No access to configuration
   No access to tax visibility
   3.3 System Administrator (Internal)
   Capabilities:
   Manage rulesets
   Monitor deployments
   Support customers
4. Onboarding and Business Profiling
   4.1 Business Creation
   The system shall allow a user to create a business profile with the following inputs:
   Business name
   Business activity type (product, service, hybrid)
   Registration status (informal, sole proprietor, partnership, limited company)
   Inventory usage
   Cross-border activity indicators
   4.2 Decision-Tree Onboarding
   The system shall guide users through a question-based onboarding flow
   Each question must affect internal classification or defaults
   Users shall not be required to provide tax IDs, CAC numbers, or revenue figures at onboarding
   4.3 Versioned Profiles
   The system shall version business profiles
   Changes to business classification shall apply prospectively from a chosen effective date
   Historical records must remain associated with the profile version active at creation time
5. Bookkeeping and Ledger Management
   5.1 Ledger Principles
   All financial records shall be stored as immutable ledger entries
   Entries shall not be edited or deleted once posted
   Corrections shall be made using reversal or adjustment entries
   5.2 Transaction Recording
   The system shall support the following transaction intents:
   Money received
   Money spent
   Sale recorded
   Stock purchased
   Manual adjustment
   Each transaction shall record:
   Amount
   Date
   Category
   Direction (debit/credit, internal only)
   Optional notes
   5.3 Category System
   The system shall provide default category templates based on business type
   Categories shall have internal classifications (revenue, expense, asset, liability, etc.)
   Users may rename categories but shall not change internal classification after first use
   Categories may be archived but not permanently deleted
6. Inventory Management (Supplementary)
   6.1 Inventory Enablement
   Inventory functionality shall be enabled only for businesses flagged as product or hybrid
   Inventory usage shall be configurable per business profile version
   6.2 Inventory Items
   The system shall allow users to:
   Create inventory items
   Track quantity on hand
   Define unit cost and reorder levels
   6.3 Inventory Movements
   Purchases, sales, and adjustments shall create inventory movement records
   Inventory movements shall automatically generate corresponding ledger entries
   Users shall not manually post accounting entries for inventory movements

7. Profit and Loss Computation
   7.1 Profit Models
   The system shall support multiple profit computation models, including:
   Cash basis
   Accrual-lite (inventory-aware)
   7.2 Automatic Aggregation
   Revenue, costs, expenses, and profits shall be computed automatically per period
   Users shall not manually calculate profit figures
8. Tax Visibility Engine
   8.1 Tax Awareness Only
   The system shall display tax applicability as informational indicators
   The system shall not instruct users to pay or file taxes
   8.2 Applicability States
   Each tax type may have one of the following states:
   Exempt
   Potentially applicable
   Applicable
   8.3 Conservative Logic
   Tax logic shall be rules-driven and versioned
   Ambiguous cases shall default to non-prescriptive states
   All tax displays shall include disclaimers
   8.4 No Filing or Submission
   The system shall not generate tax forms
   The system shall not submit data to any authority
9. Reporting and Analytics
   9.1 Standard Reports
   The system shall provide:
   Profit and loss summary
   Revenue breakdown
   Expense breakdown
   Inventory valuation
   Cash flow overview
   9.2 Report Characteristics
   Reports shall be computed dynamically from ledger data
   Reports shall be filterable by date range
   Reports shall be exportable (PDF, CSV)
   9.3 Tax Visibility Reports
   Tax visibility shall be presented as a separate report
   Reports shall emphasize estimation and uncertainty
10. Data Integrity and Auditability
    10.1 Immutability
    Ledger entries shall be immutable
    Inventory movements shall be immutable
    10.2 Audit Trail
    All changes to business configuration shall be logged
    Ruleset versions shall be traceable
11. Access Control and Security
    11.1 User Access
    The system shall support multiple users per business
    Roles shall restrict access to sensitive features
    11.2 Data Ownership
    Businesses shall retain ownership of their data
    The system shall support data export and exit
12. Deployment and Licensing
    12.1 Software Licensing
    The system shall support private instance deployment
    All instances shall run the same software version
    12.2 Updates
    Updates shall be applied uniformly across deployments
    No client-specific code branches shall exist
13. Non-Functional Constraints (Functional Implications)
    The system must function with intermittent connectivity
    The UI must support low-literacy users
    Defaults must minimize configuration burden
14. Explicit Exclusions
    The system shall not:
    Provide tax advice
    Perform tax filings
    Replace accountants
    Interface directly with tax authorities
15. Success Criteria
    Nisiro is functionally successful if:
    SMEs can record transactions daily without friction
    Reports reflect business reality credibly
    Tax visibility is accurate yet conservative
    The product remains clearly a software system, not a service
