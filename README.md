# Durva Eco Ware - Dynamic PHP & MySQL Web Application

A full-featured, dynamic web application for **Durva Eco Ware** (sustainable disposable tableware and food packaging), built with **PHP 7.4 / 8.x** and **MySQL / MariaDB (XAMPP)**.

Features a clean, modular MVC-style architecture, customer authentication, wholesale quote submission with WhatsApp integration, a customer inquiry dashboard, and an executive Admin Console.

---

## ⚡ Super Easy Setup on Another Device (1-Minute Guide)

Setting this project up on another computer running XAMPP takes less than a minute.

### Step 1: Copy to XAMPP `htdocs`
Copy this project folder into your XAMPP web directory:
- **Windows**: `C:\xampp\htdocs\durva-eco-ware`
- **macOS**: `/Applications/XAMPP/xamppfiles/htdocs/durva-eco-ware`
- **Linux**: `/opt/lampp/htdocs/durva-eco-ware`

### Step 2: Start XAMPP Services
Open the **XAMPP Control Panel** and start:
1. **Apache**
2. **MySQL**

### Step 3: Run the 1-Click Web Installer
Open your browser and navigate to:
```
http://localhost/durva-eco-ware/install.php
```
*(If you renamed the folder, use `http://localhost/<folder-name>/install.php`)*

1. The installer will check your PHP environment.
2. Click **"🚀 Install & Run Migrations"**.
3. It will automatically create the database `durva_eco_ware`, execute all tables, import sample categories, products, default settings, and create the administrator account.

That's it! Your site is live!

---

## 🔑 Default Login Credentials

| Role | Email | Password | Access Area |
|---|---|---|---|
| **Administrator** | `admin@durvaecoware.com` | `AdminPassword@123` | `/admin/index.php` |
| **Sample Customer** | `customer@gmail.com` | `Customer@123` | `/account.php` |

*(You can change the admin email and password anytime in the installer or database)*

---

## 🛠️ Alternative Setup Methods

### Option B: Command-Line Migration
If you prefer the terminal:
```bash
cd C:\xampp\htdocs\durva-eco-ware
php database/migrate.php
```
The script will auto-detect your MySQL port (supports both standard `3306` and alternate `3307`), create the database, run table schemas, and seed initial data.

### Option C: Manual phpMyAdmin Import
1. Open `http://localhost/phpmyadmin/`.
2. Create a new database named `durva_eco_ware` with collation `utf8mb4_unicode_ci`.
3. Click the **Import** tab:
   - Import `database/schema.sql`
   - Import `database/seed.sql`
4. Copy `.env.example` to `.env`.

---

## 🌟 Key Application Features

### 1. Public Storefront & Catalog
- **Dynamic Homepage (`index.php`)**: Features live eco-tableware products, interactive product illustrations, and company highlights.
- **Product Catalog (`products.php`)**: Filter by category (Paper Plates, Paper Bowls, Kraft Food Containers, Meal Trays, Eco Cups) with real-time text search.
- **Product Details (`product-detail.php`)**: View specifications, packaging details, material composition, minimum order quantities (MOQ), and similar items.
- **Wholesale Quote Pipeline**: Integrated quote request forms that submit leads directly to MySQL **AND** generate pre-filled WhatsApp links for instant communication.

### 2. User & Customer Portal
- **Registration (`register.php`)**: Wholesale buyers can create accounts with company and phone details.
- **Login (`login.php`)**: Secure authentication using `password_hash()` (Bcrypt).
- **Customer Dashboard (`account.php`)**: Customers can view submitted quote requests, track follow-up progress (`New` &rarr; `Contacted` &rarr; `In Progress` &rarr; `Closed`), and update their business contact profile.

### 3. Executive Admin Console (`/admin`)
- **Dashboard Overview (`/admin/index.php`)**: Real-time KPI metrics for pending inquiries, total products, categories, and customer count.
- **Product CRUD (`/admin/products.php`, `/admin/product-form.php`)**:
  - Add, edit, and delete products.
  - Image upload with size and MIME verification.
  - Set wholesale prices, MOQ, badges, and stock availability.
  - Feature products on the homepage with 1 click.
- **Wholesale Quotes Management (`/admin/inquiries.php`)**:
  - Filter inquiries by status (`New`, `Contacted`, `In Progress`, `Closed`).
  - Add internal team follow-up notes.
  - 1-click **WhatsApp Direct Chat** and **Telephone Dial** buttons.
- **Categories Management (`/admin/categories.php`)**: Manage categories with custom icons and automatic URL slug generation.
- **User Management (`/admin/users.php`)**: View registered customers and promote/demote administrator roles.
- **Site Settings (`/admin/settings.php`)**: Update company phone, email, WhatsApp hotline number, address, and marketing copy without touching any code.

---

## 📁 Clean Code Architecture

```
├── config/
│   ├── app.php               # Dynamic Base URL auto-detection, helper functions, flash alerts
│   ├── database.php          # PDO connection with port 3306/3307 auto-fallback
│   └── auth.php              # Session manager, CSRF protection, role guards
├── database/
│   ├── schema.sql            # MySQL schema with foreign keys and indexes
│   ├── seed.sql              # Initial products, categories, admin user & settings
│   └── migrate.php           # Standalone CLI migration & seeder runner
├── app/
│   ├── Models/
│   │   ├── User.php          # User queries, bcrypt authentication, role updates
│   │   ├── Product.php       # Product CRUD, category filters, and search
│   │   ├── Category.php      # Category management & product counts
│   │   ├── Inquiry.php       # Wholesale quote lead tracking & status updates
│   │   └── Setting.php       # Key-value site configuration
│   ├── Controllers/
│   │   ├── AuthController.php# Login, registration, and logout handling
│   │   └── InquiryController.php # Quote submissions and WhatsApp links
│   └── Helpers/
│       ├── csrf.php          # Anti-CSRF token generator & validator
│       ├── sanitizer.php     # Input sanitization and slugification
│       └── upload.php        # Secure image file uploader
├── assets/
│   ├── css/
│   │   ├── main.css          # Public site stylesheet
│   │   ├── admin.css         # Executive Admin dashboard stylesheet
│   │   └── auth.css          # Split-panel login/signup stylesheet
│   ├── js/
│   │   ├── main.js           # Frontend interactions & mobile navigation
│   │   └── admin.js          # Admin dashboard interactions & confirmation guards
│   └── uploads/              # Dynamic uploaded images with .htaccess execution protection
├── includes/
│   ├── header.php            # Public site header & dynamic navigation
│   ├── footer.php            # Public site footer & live contact settings
│   └── alerts.php            # Flash message alerts component
├── admin/                    # Administrative dashboard routes
├── install.php               # 1-Click web installer wizard
├── index.php                 # Dynamic public homepage
├── products.php              # Product catalog with filters
├── product-detail.php        # Product view & quote modal
├── login.php                 # Authentication login
├── register.php              # Customer registration
├── account.php               # Customer portal
└── .env.example              # Environment variables template
```

---

## 🔒 Security Best Practices Implemented

1. **SQL Injection Protection**: 100% prepared statements with PDO across all queries.
2. **Password Security**: Strong Bcrypt password hashing via `password_hash()` and `password_verify()`.
3. **Cross-Site Request Forgery (CSRF)**: Cryptographic tokens validated on all POST operations.
4. **Cross-Site Scripting (XSS)**: Strict HTML escaping using `e()` / `htmlspecialchars()`.
5. **Secure File Uploads**: Validates actual file MIME types, file size limit (5MB), renames uploaded files with unique hashes, and disables script execution in `/assets/uploads/` via `.htaccess`.
6. **Role-Based Access Control**: Non-admin users are strictly forbidden from accessing `/admin/`.
