# MVP API - Aura ERP MPS (Laravel 12)

Base URL (local): `/api/v1`

## Headers
- `X-Tenant-Id: <company_id>` (required for `auth/login` and webhook endpoints)
- `Authorization: Bearer <token>` (required for protected ERP endpoints)
- `Content-Type: application/json`
- `X-Printwayy-Token: <token>` (required for Printwayy webhook endpoints when configured)

## Authentication (Sanctum)
- `POST /auth/login`
- `GET /auth/me`
- `POST /auth/logout`

## Core CRUD Endpoints

### Clients
- `GET /clients`
- `POST /clients`
- `GET /clients/{id}`
- `PUT/PATCH /clients/{id}`
- `DELETE /clients/{id}`

### Contracts
- `GET /contracts`
- `POST /contracts`
- `GET /contracts/{id}`
- `PUT/PATCH /contracts/{id}`
- `DELETE /contracts/{id}`

### Equipment
- `GET /equipment`
- `POST /equipment`
- `GET /equipment/{id}`
- `PUT/PATCH /equipment/{id}`
- `DELETE /equipment/{id}`

## Metering and Billing Flow

### Manual meter read
- `POST /meter-reads`

Request body:
```json
{
  "equipment_id": 10,
  "read_at": "2026-05-22 18:05:00",
  "mono_total": 152340,
  "color_total": 28040,
  "source": "manual"
}
```

### Generate invoice for a contract
- `POST /contracts/{contract_id}/invoices/generate`

Request body:
```json
{
  "reference": "202605",
  "emit_ticket_on_anomaly": true
}
```

### Invoice queries
- `GET /invoices`
- `GET /invoices/{id}`

## Printwayy Integration Endpoints

### Meter read webhook
- `POST /printwayy/meter-reads`

Request body:
```json
{
  "serial_number": "SN-0001",
  "mono_total": 200000,
  "color_total": 45000,
  "read_at": "2026-05-22 18:10:00"
}
```

### Alert webhook (auto ticket)
- `POST /printwayy/alerts`

Request body:
```json
{
  "serial_number": "SN-0001",
  "alert_type": "offline",
  "severity": "high",
  "message": "Device offline for 15 minutes"
}
```

### Sync manual (protegido por token)
- `POST /printwayy/sync`

## Dashboard KPI
- `GET /dashboard/summary`

## Implemented business behavior
- Tenant isolation via `tenant_id` scope.
- Token authentication via Laravel Sanctum.
- Meter reads reject decreasing counters.
- Billing generation is idempotent by `contract + billing_reference`.
- Usage is calculated from cumulative counters per period.
- Overages are calculated by contract limits and prices.
- High-consumption anomalies can auto-open technical tickets.
- Printwayy sync job now supports API pull + webhook ingestion.
