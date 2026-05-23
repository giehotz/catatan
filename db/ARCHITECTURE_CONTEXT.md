# 🏛️ Architecture Context — Catatan Keuangan

## System Architecture Diagram

```
┌──────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                              │
│  Browser (Mobile-First Responsive)                               │
│  TailwindCSS v4 Dark Theme + Vanilla JS                         │
└───────────────────────┬──────────────────────────────────────────┘
                        │ HTTP Request
┌───────────────────────▼──────────────────────────────────────────┐
│                    ROUTING & MIDDLEWARE                           │
│  Routes.php → Controller mapping                                 │
│  Filters.php → UserAuthFilter / AdminAuthFilter                 │
│  Shield → Session-based auth (register, login, logout)          │
└───────────────────────┬──────────────────────────────────────────┘
                        │
┌───────────────────────▼──────────────────────────────────────────┐
│                    CONTROLLER LAYER (15 Controllers)             │
│                                                                  │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────────┐   │
│  │  Personal    │ │  Admin      │ │  Cooperative            │   │
│  │  Finance     │ │  Panel      │ │  Module                 │   │
│  ├─────────────┤ ├─────────────┤ ├─────────────────────────┤   │
│  │ Home        │ │ Admin       │ │ CooperativeAdmin (50KB) │   │
│  │ Transaction │ │             │ │ CooperativeMember       │   │
│  │ Category    │ │             │ │ CooperativeShu          │   │
│  │ DebtRecv    │ │             │ │                         │   │
│  │ Budget      │ │             │ │                         │   │
│  │ Recurring   │ │             │ │                         │   │
│  │ Wallet      │ │             │ │                         │   │
│  │ Savings     │ │             │ │                         │   │
│  │ Report      │ │             │ │                         │   │
│  │ Profile     │ │             │ │                         │   │
│  └─────────────┘ └─────────────┘ └─────────────────────────┘   │
└───────────────────────┬──────────────────────────────────────────┘
                        │
┌───────────────────────▼──────────────────────────────────────────┐
│                    MODEL LAYER (21 Models)                        │
│                                                                  │
│  Core Financial:        Cooperative (Kop*):                      │
│  ─────────────          ────────────────────                     │
│  TransactionModel       KopAnggotaModel                          │
│  DebtModel              KopPinjamanModel (calc engine)           │
│  ReceivableModel        KopSimpananModel                         │
│  WalletModel            KopAngsuranModel                         │
│  BudgetModel            KopKasInternalModel                      │
│  SavingsGoalModel       KopSettingModel (key-value)              │
│  RecurringTxnModel      KopShuModel                              │
│  CategoryModels (2)     KopInvitationModel                       │
│                                                                  │
│  Cross-Cutting:                                                  │
│  ─────────────                                                   │
│  AuditLogModel (static)                                          │
│  UserModel (Shield extension)                                    │
└───────────────────────┬──────────────────────────────────────────┘
                        │
┌───────────────────────▼──────────────────────────────────────────┐
│                    DATABASE LAYER (MySQL)                         │
│                                                                  │
│  20+ tables, 18 migration files                                  │
│  Key patterns:                                                   │
│   - Auto-increment integer PK (most tables)                      │
│   - VARCHAR PK for kop_settings (key-value store)                │
│   - Polymorphic reference (kop_kas_internal.reference_type)      │
│   - Dual-sync: kop_pinjaman → debts + receivables               │
│   - Installment reconciliation: kop_angsuran → debt/recv payments│
└──────────────────────────────────────────────────────────────────┘
```

## Module Boundaries

### Personal Finance (Independent)
- Transactions, Categories, Wallets, Budgets, Savings, Recurring, Reports
- Each module is self-contained
- Shared only via `user_id` ownership

### Cooperative Module (Interconnected)
- **Admin Panel** (`CooperativeAdmin`): manages members, loans, savings, installments, funds, settings
- **Member Portal** (`CooperativeMember`): join, deposit, loans, bills
- **SHU** (`CooperativeShu`): profit distribution
- **Cross-links to core**: `kop_pinjaman` ↔ `debts` + `receivables`
- **Settings-driven**: All financial rules configurable via `kop_settings`

### Admin Module
- User management, role assignment, impersonation
- Audit log viewer
- Access gate for cooperative management

## Data Flow Patterns

### Pattern 1: Simple CRUD (Most modules)
```
Request → Filter → Controller.method() → Model.insert/update/delete() → Redirect
```

### Pattern 2: Multi-table Transaction (Loans, Installments)
```
Request → Filter → Controller.method()
  → DB::transStart()
  → Model1.insert() (primary record)
  → Model2.insert() (linked debt/receivable)
  → Model3.insert() (kas_internal entry)
  → AuditLogModel::log()
  → DB::transComplete()
  → Redirect
```

### Pattern 3: Settings-driven Calculation
```
Controller → KopPinjamanModel::calculateLoanDetails($nominal, $tenor)
  → KopSettingModel::getSetting('key1')
  → KopSettingModel::getSetting('key2')
  → ... (reads 7 settings)
  → returns calculation array
Controller → override with form values → store
```

### Pattern 4: Approval Workflow
```
Member submits → status: pending
Admin reviews  → approve (with reconciliation) OR reject (with reason)
```

## File Size Indicators (Complexity Gauge)

| File                       | Size    | Complexity |
|----------------------------|---------|------------|
| CooperativeAdmin.php       | 50.8 KB | ⚠️ Very High |
| CooperativeMember.php      | 27.3 KB | ⚠️ High     |
| direct_loan.php (view)     | 27.6 KB | ⚠️ High     |
| Transaction.php            | 18.9 KB | 🔶 Medium   |
| installments.php (view)    | 20.7 KB | 🔶 Medium   |
| CooperativeShu.php         | 12.2 KB | 🟢 Normal   |
| All other controllers      | < 15 KB | 🟢 Normal   |
