# 🗺️ Catatan Application Architectural Map

This document presents a comprehensive, production-grade architectural blueprint and dependency map for **Catatan**—a personal finance manager integrated with a cooperative credit union (**Koperasi Simpan Pinjam**) module built on the CodeIgniter 4 framework.

---

## 🏗️ 1. High-Level System Architecture

The application is structured around a classic Model-View-Controller (MVC) architectural pattern, enhanced with **CodeIgniter Shield** for role-based access control and custom filters to separate **Admin/Manager** roles from **Standard Users (Members)**.

```mermaid
graph TD
    %% Styling
    classDef route fill:#F9E79F,stroke:#D4AC0D,stroke-width:2px;
    classDef filter fill:#EDBB99,stroke:#D35400,stroke-width:2px;
    classDef controller fill:#AED6F1,stroke:#2E86C1,stroke-width:2px;
    classDef model fill:#A2D9CE,stroke:#17A589,stroke-width:2px;
    classDef view fill:#D7BDE2,stroke:#884EA1,stroke-width:2px;
    classDef external fill:#E5E7E9,stroke:#7F8C8D,stroke-width:1px;

    %% Elements
    Client([🌐 Web Client])
    Routes[Config/Routes.php]:::route
    AuthFilter[Filters/AdminAuthFilter.php]:::filter
    Shield[Shield Auth Middleware]:::filter

    %% Controllers
    subgraph Controllers [Controllers Layer]
        C_Base[BaseController.php]:::controller
        C_Admin[Admin.php]:::controller
        C_CoopAdmin[CooperativeAdmin.php]:::controller
        C_CoopMember[CooperativeMember.php]:::controller
        C_Tx[Transaction.php]:::controller
        C_Wallet[Wallet.php]:::controller
        C_Savings[Savings.php]:::controller
        C_Debt[DebtReceivable.php]:::controller
    end

    %% Models
    subgraph Models [Models Layer]
        M_User[UserModel.php]:::model
        M_Tx[TransactionModel.php]:::model
        M_Wallet[WalletModel.php]:::model
        M_Goal[SavingsGoalModel.php]:::model
        M_KopAnggota[KopAnggotaModel.php]:::model
        M_KopPinjaman[KopPinjamanModel.php]:::model
        M_KopSimpanan[KopSimpananModel.php]:::model
        M_KopAngsuran[KopAngsuranModel.php]:::model
        M_Debt[DebtModel.php]:::model
        M_Receivable[ReceivableModel.php]:::model
    end

    %% Views
    subgraph Views [Presentation Layer]
        L_Base[Layouts/base.php]:::view
        L_Admin[Layouts/admin_base.php]:::view
        V_Coop[user/cooperative/loans.php]:::view
        V_CoopAdmin[admin/cooperative/members.php]:::view
    end

    %% Database
    subgraph Data [Data Tier]
        DB[(MySQL Database)]
    end

    %% Libraries
    DomPDF([📄 Dompdf]):::external
    PhpSpreadsheet([📊 PhpSpreadsheet]):::external

    %% Connections
    Client -->|HTTP Requests| Routes
    Routes --> Shield
    Shield --> AuthFilter
    AuthFilter --> C_Admin
    AuthFilter --> C_CoopAdmin
    
    %% Base Controller Inheritance
    C_Base <|-- C_Admin
    C_Base <|-- C_CoopAdmin
    C_Base <|-- C_CoopMember
    C_Base <|-- C_Tx
    
    %% Controller to Model relationships
    C_CoopAdmin --> M_KopAnggota
    C_CoopAdmin --> M_KopSimpanan
    C_CoopAdmin --> M_KopPinjaman
    C_CoopMember --> M_KopPinjaman
    C_CoopMember --> M_KopSimpanan
    C_CoopMember --> M_KopAngsuran
    C_Tx --> M_Tx
    C_Tx --> M_Wallet
    C_Debt --> M_Debt
    C_Debt --> M_Receivable
    
    %% Integrations & Outputs
    C_CoopAdmin -->|Generates PDF| DomPDF
    C_CoopAdmin -->|Exports Report| PhpSpreadsheet
    
    %% Sync flows
    M_KopPinjaman -.->|Triggers sync to| M_Debt
    M_KopPinjaman -.->|Triggers sync to| M_Receivable
    
    %% Model to DB
    Models --> DB
    
    %% View Rendering
    C_CoopMember -->|renders| V_Coop
    C_CoopAdmin -->|renders| V_CoopAdmin
    V_Coop --> L_Base
    V_CoopAdmin --> L_Admin
```

---

## 🗄️ 2. Database Schema (Entity Relationships)

The application beautifully marries **Personal Finance Tracking** (Wallets, Budgets, Savings, Debts) with the **Cooperative Credit Union** module.

```mermaid
erDiagram
    users ||--o{ kop_invitations : "issues"
    users ||--|| kop_anggota : "registers_as"
    users ||--o{ wallets : "owns"
    users ||--o{ debts : "has_debts"
    users ||--o{ receivables : "has_receivables"
    
    wallets ||--o{ transactions : "contains"
    wallets ||--o{ savings_transactions : "funds"
    
    kop_anggota ||--o{ kop_simpanan : "deposits"
    kop_anggota ||--o{ kop_pinjaman : "borrows"
    
    kop_pinjaman ||--o{ kop_angsuran : "repaid_by"
    kop_pinjaman ||--|| debts : "syncs_to_debt"
    kop_pinjaman ||--|| receivables : "syncs_to_receivable"
    
    transaction_categories ||--o{ transactions : "categorizes"
    savings_goals ||--o{ savings_transactions : "tracks"
    budgets ||--o{ transaction_categories : "limits"

    users {
        int id PK
        string username
        string email
        string password_hash
        datetime created_at
    }

    kop_anggota {
        int id PK
        int user_id FK
        string nomor_anggota UK
        string status_keaktifan "active/inactive/suspended"
        datetime created_at
    }

    kop_simpanan {
        int id PK
        int anggota_id FK
        enum jenis_simpanan "pokok, wajib, sukarela"
        decimal nominal
        datetime tanggal_setor
    }

    kop_pinjaman {
        int id PK
        int anggota_id FK
        decimal nominal_pinjaman
        int tenor_bulan
        decimal bunga_persen
        enum status "pending, approved, rejected, paid"
        int debt_id_fk FK "Null unless approved"
        int receivable_id_fk FK "Null unless approved"
        datetime created_at
    }

    kop_angsuran {
        int id PK
        int pinjaman_id FK
        int angsuran_ke
        decimal nominal_bayar
        datetime tanggal_bayar
    }

    debts {
        int id PK
        int user_id FK
        string title "Cooperative Loan"
        decimal total_amount
        decimal paid_amount
        enum status "unpaid, partial, paid"
    }

    receivables {
        int id PK
        int user_id FK "Cooperative Wallet Owner/Admin"
        string title "Member Loan"
        decimal total_amount
        decimal paid_amount
        enum status "unpaid, partial, paid"
    }
```

---

## 🔄 3. Two-Way Sync Logic: Cooperative & Personal Ledger

A standout design element in this application is the synchronization engine connecting the **Cooperative loan** status to **Personal debts/receivables**.

```mermaid
sequenceDiagram
    autonumber
    actor Member as Member (User)
    actor Admin as Cooperative Admin
    participant PC as CooperativeAdmin Controller
    participant M_Pinjaman as KopPinjamanModel
    participant M_Debt as DebtModel
    participant M_Rec as ReceivableModel

    Member->>M_Pinjaman: Submits loan request (Pending)
    Admin->>PC: Approves Loan request
    PC->>M_Pinjaman: Set status = 'approved'
    
    activate PC
    PC->>M_Debt: Create corresponding Debt row for Member
    Note over M_Debt: Status: unpaid<br/>Amount: principal + interest
    M_Debt-->>PC: Return debt_id
    
    PC->>M_Rec: Create corresponding Receivable row for Cooperative/Admin
    Note over M_Rec: Status: unpaid<br/>Amount: principal + interest
    M_Rec-->>PC: Return receivable_id
    
    PC->>M_Pinjaman: Save debt_id_fk & receivable_id_fk
    deactivate PC
    
    Note over Member, Admin: When Member pays an installment
    Member->>PC: Pays installment of amount X
    PC->>M_Debt: Update debt (paid_amount += X)
    PC->>M_Rec: Update receivable (paid_amount += X)
    PC->>M_Pinjaman: Register installment payment in kop_angsuran
```

---

## 🛠️ 4. Modular Codebase Summary

### 📂 Configuration Layer (`app/Config/`)
- **`Routes.php`**: Manages all SEO-friendly URLs. Contains dedicated routing blocks for `/admin/cooperative/*` and `/user/cooperative/*`.
- **`AuthGroups.php`**: Defines Shield permissions, mapping user groups (e.g., `admin`, `manager`, `member`).

### 📂 Controller Layer (`app/Controllers/`)
- **`CooperativeAdmin.php`**: Core controller for managing members, verifying invitation codes, registering deposits, validating loan requests, and outputting PDF summaries via Dompdf.
- **`CooperativeMember.php`**: Handles client-side cooperative hub dashboard, tracking deposits, filling out loan requests, and listing installment schedules.
- **`Transaction.php` & `Wallet.php`**: Handlers for personal finance accounting.

### 📂 Model Layer (`app/Models/`)
- **`KopAnggotaModel.php`**: Manages membership codes, validation, and user profile relationships.
- **`KopPinjamanModel.php`**: Business rules for interest calculation, amortization schedule generators, and status modifications.
- **`KopSimpananModel.php`**: Categorizes and aggregates Pokok, Wajib, and Sukarela savings pools.

### 📂 View Layer (`app/Views/`)
- **`layouts/base.php`**: Standard premium responsive layout for members, utilizing a dark mode utility via HSL tailored design.
- **`layouts/admin_base.php`**: Specialized dashboard layout for the admin view containing sidebar, audit monitors, and metrics.
- **`user/cooperative/loans.php`**: Interactive simulation forms for members to compute interest and amortization before requesting a loan.

---

### 🎨 Design Highlights
* **HSL Color System**: Sleek glassmorphism and tailormade deep primary hues (`hsl(222.2, 47.4%, 11.2%)` for dark mode base) mapped directly inside the master stylesheets.
* **Micro-Animations**: Hover-triggered translations, dynamic load states, and full screen cinematic overlays for administrative operations.
