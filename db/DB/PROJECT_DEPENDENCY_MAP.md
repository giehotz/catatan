# 🗺️ Project Dependency Map

## Controller → Model Dependencies

| Controller            | Models Used                                                                |
|-----------------------|----------------------------------------------------------------------------|
| Home                  | TransactionModel, WalletModel, DebtModel, ReceivableModel, BudgetModel    |
| Transaction           | TransactionModel, WalletModel, IncomeCategoryModel, ExpenseCategoryModel   |
| Category              | IncomeCategoryModel, ExpenseCategoryModel, TransactionModel                |
| DebtReceivable        | DebtModel, ReceivableModel                                                |
| Profile               | UserModel (Shield)                                                        |
| Report                | TransactionModel, ExpenseCategoryModel, IncomeCategoryModel                |
| Budget                | BudgetModel, ExpenseCategoryModel                                         |
| Recurring             | RecurringTransactionModel, TransactionModel, WalletModel                   |
| Wallet                | WalletModel, TransactionModel                                             |
| Savings               | SavingsGoalModel, SavingsTransactionModel, WalletModel                     |
| Admin                 | UserModel, AuditLogModel                                                  |
| CooperativeAdmin      | KopAnggotaModel, KopPinjamanModel, KopSimpananModel, KopAngsuranModel     |
|                       | KopKasInternalModel, KopSettingModel, KopInvitationModel, AuditLogModel   |
|                       | DebtModel, ReceivableModel                                                |
| CooperativeMember     | KopAnggotaModel, KopPinjamanModel, KopSimpananModel, KopAngsuranModel     |
|                       | KopInvitationModel                                                        |
| CooperativeShu        | KopShuModel, KopAnggotaModel, KopKasInternalModel, AuditLogModel          |

## Model → Database Table Map

| Model                     | Table                    | Primary Key       |
|---------------------------|--------------------------|--------------------|
| UserModel                 | users                    | id (int, auto)     |
| TransactionModel          | transactions             | id (int, auto)     |
| IncomeCategoryModel       | income_categories        | id (int, auto)     |
| ExpenseCategoryModel      | expense_categories       | id (int, auto)     |
| DebtModel                 | debts                    | id (int, auto)     |
| ReceivableModel           | receivables              | id (int, auto)     |
| BudgetModel               | budgets                  | id (int, auto)     |
| WalletModel               | wallets                  | id (int, auto)     |
| SavingsGoalModel          | savings_goals            | id (int, auto)     |
| SavingsTransactionModel   | savings_transactions     | id (int, auto)     |
| RecurringTransactionModel | recurring_transactions   | id (int, auto)     |
| AuditLogModel             | audit_logs               | id (int, auto)     |
| KopAnggotaModel           | kop_anggota              | id (int, auto)     |
| KopInvitationModel        | kop_invitation           | id (int, auto)     |
| KopSimpananModel          | kop_simpanan             | id (int, auto)     |
| KopPinjamanModel          | kop_pinjaman             | id (int, auto)     |
| KopAngsuranModel          | kop_angsuran             | id (int, auto)     |
| KopKasInternalModel       | kop_kas_internal         | id (int, auto)     |
| KopSettingModel           | kop_settings             | key (VARCHAR 100)  |
| KopShuModel               | kop_shu_history          | id (int, auto)     |

## Cross-Module Foreign Keys

```
kop_anggota.user_id          → users.id
kop_pinjaman.anggota_id      → kop_anggota.id
kop_pinjaman.debt_id_fk      → debts.id
kop_pinjaman.receivable_id_fk→ receivables.id
kop_angsuran.pinjaman_id     → kop_pinjaman.id
kop_simpanan.anggota_id      → kop_anggota.id
kop_shu_history.anggota_id   → kop_anggota.id
kop_kas_internal.reference_id→ (polymorphic: pinjaman, angsuran, manual)
debt_payments.debt_id        → debts.id
receivable_payments.receivable_id → receivables.id
transactions.wallet_id       → wallets.id
budgets.expense_category_id  → expense_categories.id
```

## View → Layout Dependencies

| View Directory                | Layout Used                        |
|-------------------------------|-------------------------------------|
| `user/*`                      | `layouts/base.php`                 |
| `user/cooperative/*`          | `layouts/koprasi_base.php`         |
| `admin/dashboard.php`         | `layouts/admin_base.php`           |
| `admin/cooperative/*`         | `layouts/admin_cooperative_base.php`|
| `Shield/*`                    | Self-contained (no layout)         |

## Filter → Route Dependencies

| Filter        | URI Patterns                                             |
|---------------|----------------------------------------------------------|
| `user_auth`   | `/`, `transaction/*`, `category/*`, `debt-receivable/*`  |
|               | `profile/*`, `reports/*`, `budgets/*`, `recurring/*`     |
|               | `wallets/*`, `savings/*`, `cooperative/*`                |
| `admin_auth`  | `admin/*` (except `admin/login`)                         |
