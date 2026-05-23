# 🔄 Workflow Map — Catatan Keuangan

## User Registration & Login Flow
```
[Register Page] → Shield::register() → users table → auto-group "user"
[Login Page]    → Shield::login()    → session created → redirect to /
[Admin Login]   → /admin/login      → Admin::login() → redirect to /admin
```

## Transaction Flow (Income/Expense)
```
User opens /transaction
  → Transaction::index()
  → loads: transactions, wallets, categories
  → renders: user/transactions/index.php

User submits form (POST /transaction/create)
  → Transaction::create()
  → validates: amount, type, category, wallet
  → inserts: transactions table
  → updates: wallet balance (increment/decrement)
  → redirect with flash message
```

## Cooperative Member Join Flow
```
User opens /cooperative
  → CooperativeMember::index()
  → checks: is user already a member?
  
  NOT MEMBER:
    → shows invitation code form
    → User enters code (POST /cooperative/join)
    → validates invitation code from kop_invitation
    → creates kop_anggota record
    → marks invitation as used
  
  IS MEMBER:
    → shows cooperative dashboard (simpanan, pinjaman, SHU)
```

## Loan Request Flow (Member → Admin)
```
Member: POST /cooperative/request-loan
  → CooperativeMember::requestLoan()
  → validates: amount, tenor, is member active
  → calculateLoanDetails() → reads kop_settings
  → inserts kop_pinjaman (status: pending)
  → audit log

Admin: GET /admin/cooperative/loans
  → CooperativeAdmin::loans()
  → shows all loan requests

Admin: POST /admin/cooperative/approve-loan/{id}
  → CooperativeAdmin::approveLoan()
  → updates: kop_pinjaman status → approved
  → creates: debt record (for member)
  → creates: receivable record (for cooperative)
  → links: kop_pinjaman.debt_id_fk, receivable_id_fk
  → audit log
```

## Direct Loan Flow (Admin → Member)
```
Admin: GET /admin/cooperative/loans/direct
  → CooperativeAdmin::directLoanForm()
  → loads: active members, financial settings
  → shows: form with live simulation (JS)

Admin: POST /admin/cooperative/loans/direct/store
  → CooperativeAdmin::storeDirectLoan()
  → validates: all fields + bunga_opsi_bayar + metode_bayar_jasa
  → calculateLoanDetails() + override payment methods from form
  → BEGIN TRANSACTION:
    A. Insert kop_pinjaman (status: approved)
    B. Insert debts (member debt)
    C. Insert receivables (cooperative receivable)
    D. Link FKs to kop_pinjaman
    E. Insert kas_internal (pengeluaran - disbursement)
    F. Insert kas_internal (pemasukan - upfront deductions if any)
    G. Audit log
  → COMMIT
```

## Installment Payment & Reconciliation Flow
```
Member: POST /cooperative/pay-installment/{loan_id}
  → CooperativeMember::payInstallment()
  → uploads bukti_bayar (optional)
  → inserts kop_angsuran (status: pending)

Admin: GET /admin/cooperative/installments
  → CooperativeAdmin::installments()
  → shows: all pending/approved/rejected installments

Admin: POST /admin/cooperative/approve-installment/{id}
  → CooperativeAdmin::approveInstallment()
  → updates: kop_angsuran status → approved
  → inserts: kas_internal (pemasukan)
  → inserts: debt_payments (sync member debt)
  → inserts: receivable_payments (sync cooperative receivable)
  → RECONCILIATION: 
    if total_paid >= nominal_total → status = paid (loan fully repaid)
    if total_paid > 0 → status = partial
  → updates: debts.status, receivables.status
  → audit log

Admin: POST /admin/cooperative/reject-installment/{id}
  → validates: catatan_tolak (required)
  → updates: kop_angsuran status → rejected + catatan_tolak
  → audit log
```

## Settings Management Flow
```
Admin: GET /admin/cooperative/settings
  → CooperativeAdmin::settings()
  → reads 12 settings from kop_settings
  → renders: settings form

Admin: POST /admin/cooperative/settings/update
  → CooperativeAdmin::updateSettings()
  → loops through setting keys
  → KopSettingModel::setSetting() → REPLACE INTO kop_settings
  → audit log
```

## Funds Management Flow
```
Admin: GET /admin/cooperative/funds
  → CooperativeAdmin::funds()
  → loads: saldo kas_utama, saldo dana_talangan, total targets
  → loads: riwayat kas_internal

Admin: POST /admin/cooperative/funds/store
  → CooperativeAdmin::storeFund()
  → supports: pemasukan, pengeluaran, transfer_internal
  → validates: sufficient balance for outgoing
  → inserts: kas_internal record(s)
  → audit log
```

## SHU Distribution Flow
```
Admin: GET /admin/cooperative/shu
  → CooperativeShu::adminIndex()
  → calculates: total income, expenses, net profit

Admin: POST /admin/cooperative/shu/distribute
  → CooperativeShu::distribute()
  → calculates per-member share based on contribution
  → inserts kop_shu_history per member
  → audit log
```
