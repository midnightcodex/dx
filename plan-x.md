# Plan X - ERP Architecture Realignment

## Decisions (Feb 4, 2026)
- Auth: JWT
- Frontend: Separate SPA in frontend/
- JWT Library: lcobucci/jwt

## Goal
Realign the codebase to the docs.md contract:
Database <-> Backend APIs <-> Frontend

## Guiding Principles (from docs.md)
- API-first, module boundaries enforced
- Services own business logic, controllers are thin
- No cross-module table access; use services/events
- Inventory changes go only through InventoryPostingService
- Multi-tenant scoping on every model and query

## Workstreams
1. Backend architecture alignment
- Split API routes by module under routes/api/
- Standardize API responses (success/data/message/errors/meta)
- Add FormRequest + Resource classes per module
- Create service layer for Manufacturing, Inventory, Procurement
- Remove duplicate web controllers or segregate them explicitly
- Add organization global scope to prevent data leakage

2. Auth (JWT)
- Select JWT library and implement login/refresh/logout
- Replace Sanctum usage in API routes
- Keep web session auth only if explicitly required

3. Domain enforcement
- Add DB constraints for immutable stock_transactions
- Protect stock_ledger from direct updates
- Implement NumberSeriesService for document IDs
- Fix WorkOrder data mismatches (priority, actual_start_at)

4. Frontend SPA (frontend/)
- Scaffold Vite + React + TypeScript
- API client + auth store + route guards
- Port modules in order: Auth -> Inventory Items -> Work Orders
- Remove Inertia usage after SPA parity

5. Tests + Docs
- Feature tests for each API endpoint
- Seed data for demo flows
- Postman/OpenAPI docs

## Immediate Fixes (pre-sprint)
- WorkOrder priority validation mismatch
- actual_start_at vs actual_start_date update
- Duplicate WorkOrderController split (API vs Web)

## Milestones
- M0: Route modularization + API response standard
- M1: Auth + Inventory + Work Orders APIs stable
- M2: SPA auth + Work Orders UI live
- M3: Procurement + GRN + Inventory ledger flows

## Open Decisions
- None (current scope)
