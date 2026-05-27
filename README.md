# Aura ERP MPS (Laravel 12)

ERP SaaS multi-tenant para outsourcing de impressao (Managed Print Services), com foco em:
- automacao operacional
- leitura de contadores
- faturamento automatico por contrato
- SLA tecnico e chamados
- controle financeiro e rentabilidade

## Stack
- PHP 8.4+
- Laravel 12
- PostgreSQL (producao recomendada)
- Redis (fila/cache)
- Blade + Livewire + Alpine.js

## Multi-tenant
- modelo: single database
- isolamento: `tenant_id`
- trilha de auditoria base: `created_by`, `updated_by`, `deleted_at`
- contexto tenant: header `X-Tenant-Id`

## MVP ja implementado
- API `v1` habilitada
- autenticacao de API com Laravel Sanctum
- CRUD de clientes, contratos e equipamentos
- endpoint de leitura manual de contador
- endpoints de webhook Printwayy (contadores e alertas)
- sincronizacao manual/automatica com Printwayy (`/api/v1/printwayy/sync` + job agendado)
- geracao de fatura idempotente por `contrato + referencia`
- calculo de excedente por franquia
- abertura automatica de chamado por anomalia de consumo
- dashboard resumo operacional
- painel administrativo com Filament (`/admin`)

## Rotas principais
- `GET /` -> blueprint executivo com tema futurista roxo + splash screen
- `POST /api/v1/auth/login`
- `GET /api/v1/auth/me`
- `POST /api/v1/auth/logout`
- `GET/POST/... /api/v1/clients`
- `GET/POST/... /api/v1/contracts`
- `GET/POST/... /api/v1/equipment`
- `POST /api/v1/meter-reads`
- `POST /api/v1/contracts/{contract}/invoices/generate`
- `GET /api/v1/invoices`
- `POST /api/v1/printwayy/meter-reads`
- `POST /api/v1/printwayy/alerts`
- `POST /api/v1/printwayy/sync`
- `GET /api/v1/dashboard/summary`
- `POST /api/v1/billing/infinitepay/webhook` (confirmacao de pagamento do SaaS Aura)
- `GET /admin` (Filament)
- `POST /iniciar-teste` (landing publica para trial de 30 dias)

## Documentacao
- Arquitetura: `docs/ERP_ARCHITECTURE.md`
- API MVP: `docs/API_MVP.md`

## Setup rapido
1. Instalar dependencias:
   - `composer install`
2. Gerar chave:
   - `php artisan key:generate`
3. Ajustar `.env` (DB, Redis, webhook token):
   - `PRINTWAYY_WEBHOOK_TOKEN=...`
   - `PRINTWAYY_API_BASE_URL=...`
   - `PRINTWAYY_API_TOKEN=...`
   - `INFINITEPAY_HANDLE=...`
   - `INFINITEPAY_WEBHOOK_URL=https://seu-dominio.com/api/v1/billing/infinitepay/webhook` (opcional; fallback automatico)
   - `INFINITEPAY_REDIRECT_URL=https://seu-dominio.com/teste-gratis` (opcional)
   - `INFINITEPAY_CHECKOUT_URL_START=https://checkout.infinitepay.io/...` (opcional por plano)
   - `INFINITEPAY_CHECKOUT_URL_PRO=https://checkout.infinitepay.io/...` (opcional por plano)
   - `INFINITEPAY_CHECKOUT_URL_ENTERPRISE=https://checkout.infinitepay.io/...` (opcional por plano)
   - `INFINITEPAY_FALLBACK_CHECKOUT_URL=https://checkout.infinitepay.io/...` (opcional; usado se a criacao dinamica falhar)
4. Rodar migracoes:
   - `php artisan migrate --seed`
5. Subir servidor:
   - `php artisan serve`
6. Acessar:
   - Admin Filament: `http://localhost:8000/admin`
   - Usuario seed default:
     - email: `admin@aura-mps.local`
     - senha: `password`

## Observacoes de ambiente local atual
- Para desenvolvimento com SQLite, confirme no PHP CLI:
  - `pdo_sqlite`
  - `sqlite3`
- Extensoes recomendadas para o projeto:
  - `fileinfo`
  - `intl`
  - `zip`
- Se nao puder alterar o `php.ini` global, use o `tools/php-cli.ini`:
  - exemplo: `php -c tools/php-cli.ini artisan migrate --seed`

## Headers de seguranca
- Middleware global adiciona headers de hardening em todas as respostas web/api:
  - `Content-Security-Policy`
  - `Strict-Transport-Security` (somente HTTPS)
  - `X-Frame-Options`
  - `X-Content-Type-Options`
  - `Referrer-Policy`
  - `Permissions-Policy`
  - `Cross-Origin-Opener-Policy`
  - `Cross-Origin-Resource-Policy`
- Configuracoes em `config/security_headers.php`.

## Trial e cobranca SaaS (Aura Admin)
- O trial de 30 dias gera avisos automatizados em D-7, D-3, D-1 e apos expiracao.
- O link de pagamento e criado via InfinitePay (`/links`) e salvo por empresa (`companies`).
- O webhook da InfinitePay marca o trial como pago e altera status para `active`.
- Execucao manual:
  - `php artisan aura:trial-lifecycle`
