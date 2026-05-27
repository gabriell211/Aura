# Aura ERP MPS - Laravel 12 Architecture Baseline

## Purpose
This repository now starts as a real Laravel 12 ERP foundation for Managed Print Services (MPS), based on the full blueprint.

## Stack
- PHP 8.4+
- Laravel 12
- PostgreSQL (recommended)
- Redis (cache/queue)
- Blade + Livewire + Alpine.js

## Implemented Foundation
- Monolithic modular structure under `app/`:
  - Actions, DTOs, Enums, Events, Exceptions, Jobs, Listeners, Models, Policies, Repositories, Services, Traits, Support
- Multi-tenant baseline:
  - `tenant_id` scope trait (`BelongsToTenant`)
  - `created_by` and `updated_by` tracking trait (`TracksUserActions`)
  - `TenantAwareModel` abstract class for business entities
  - Middleware alias `tenant` (`EnsureTenantContext`) in `bootstrap/app.php`
- ERP domain entities scaffolded:
  - Company, Client, ClientUnit, Contract, ContractItem
  - Equipment, MeterRead
  - Ticket, TicketInteraction
  - Invoice, InvoiceItem, Payment
  - StockItem, StockMovement
  - Technician, Visit
- Jobs scaffolded:
  - ProcessMeterReadJob
  - GenerateInvoiceJob
  - SyncEquipmentJob
  - OpenTicketJob
  - SendNotificationJob
- Services scaffolded:
  - MeterReadService
  - BillingService
  - TicketAutomationService

## Database Modeling
Core migrations were added for:
- companies and multi-tenant fields on users
- roles/permissions
- clients/contracts
- equipment/metering
- tickets
- invoicing/payments
- inventory
- technicians/visits
- audit logs and system notifications

## UI
- Route `/` now renders `resources/views/erp/blueprint.blade.php`
- Futuristic purple splash screen theme with blueprint sections mapped to the ERP plan.

## Notes
- If running CLI commands fails due `ext-fileinfo` in your local PHP, enable it in your `php.ini` for full compatibility.

## Next Delivery Steps
1. Expand SLA workflow (assignment, dispatch, and closure transitions with timestamps).
2. Add technician mobile endpoints for visits, checklists, signatures, and attachments.
3. Add automated integration tests for tenant isolation and invoice overage scenarios.

## MVP Status (Current)
- API `v1` enabled in `bootstrap/app.php` and `routes/api.php`.
- Sanctum authentication enabled with:
  - `POST /api/v1/auth/login`
  - `GET /api/v1/auth/me`
  - `POST /api/v1/auth/logout`
- CRUD endpoints implemented for clients, contracts, and equipment.
- Metering endpoint implemented (`POST /api/v1/meter-reads`).
- Printwayy webhooks implemented:
  - `POST /api/v1/printwayy/meter-reads`
  - `POST /api/v1/printwayy/alerts`
- Printwayy API sync implemented in `SyncEquipmentJob` via `PrintwayyIntegrationService` with retries and payload normalization.
- Billing flow implemented:
  - contract usage aggregation by period
  - automatic overage calculations
  - idempotent invoice draft generation by `contract + reference`
  - invoice item regeneration (monthly fee + overage lines)
- Operational automation implemented:
  - `ProcessMeterReadJob` recalculates billing after new reads
  - anomaly detection can auto-open technical tickets
- Dashboard summary endpoint implemented (`GET /api/v1/dashboard/summary`).
