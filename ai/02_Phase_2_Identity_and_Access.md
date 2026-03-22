# Phase 2: Identity & Access Control

**Goal**: Implement secure, multitenant authentication and role-based access control.

## 2.1 Identity Module Scaffolding

- [ ] Create `Modules/Identity` structure.
- [ ] Register `IdentityServiceProvider`.

## 2.2 Models & Migrations

- [ ] Create `User` model (UUID).
- [ ] Create `Role` model (Configurable, not hard-coded enums).
- [ ] Create `Permission` model.
- [ ] Create pivot tables (`model_has_roles`, `role_has_permissions`).
- [ ] **Constraint**: All tenantable tables (Users, Roles, Permissions) must include a `tenant_id` column for strict row-level isolation.
- [ ] _Decision_: System-wide "Super-Admin" roles are central; business roles are tenant-scoped.

## 2.3 Authentication Logic

- [ ] Implement Tenant-aware Login Action.
- [ ] Configure `auth` guards for Tenants.

## 2.4 Authorization & Policy (RBAC)

- [ ] Implement a `PermissionService` to handle checking permissions (avoid logic in Models).
- [ ] Seed Default Roles (Owner, Staff, Accountant) via seeder/migration.
- [ ] Create Policies for the User resource.

## 2.5 Testing

- [ ] Unit Test: Role assignment.
- [ ] Unit Test: Permission checking.
- [ ] Feature Test: Tenant A user cannot login to Tenant B.
- [ ] Feature Test: Access control enforcement (Auditor cannot create entries).
