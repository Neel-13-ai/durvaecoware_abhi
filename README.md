# Durva Eco Ware - Dynamic PHP & MongoDB Web Application

A full-featured, dynamic web application for **Durva Eco Ware** (sustainable disposable tableware and food packaging), built with **PHP 7.4 / 8.x** and **MongoDB (Atlas / Local)**.

Features a clean, modular MVC-style architecture, customer authentication, wholesale quote submission with WhatsApp integration, a customer inquiry dashboard, and an executive Admin Console.

---

## ⚡ Super Easy Setup (1-Minute Guide)

### Step 1: Copy to Web Directory
Copy this project folder into your web directory (e.g. `C:\xampp\htdocs\durva-eco-ware`).

### Step 2: Enable MongoDB Extension in PHP (Windows / XAMPP)
1. Download the matching `php_mongodb.dll` for your PHP version (Thread Safe x64) from [PECL MongoDB](https://pecl.php.net/package/mongodb).
2. Put `php_mongodb.dll` into your PHP extensions directory: `C:\xampp\php\ext\`.
3. Open `C:\xampp\php\php.ini` and add:
   ```ini
   extension=mongodb
   ```
4. Restart Apache in the XAMPP Control Panel.

### Step 3: Run the 1-Click Web Installer
Open your browser and navigate to:
```
http://localhost/durva-eco-ware/install.php
```
1. Paste your MongoDB connection string (e.g. `mongodb+srv://username:password@cluster0.abcde.mongodb.net/?retryWrites=true&w=majority` or `mongodb://127.0.0.1:27017`).
2. Click **"🚀 Connect & Initialize MongoDB Collections"**.
3. It will automatically create the database `durva_eco_ware`, build unique indexes, import sample categories, products, default settings, and create the administrator account.

---

## 🔑 Default Login Credentials

| Role | Email | Password | Access Area |
|---|---|---|---|
| **Administrator** | `admin@durvaecoware.com` | `AdminPassword@123` | `/admin/index.php` |
| **Sample Customer** | `customer@gmail.com` | `Customer@123` | `/account.php` |

*(You can customize admin credentials during installation or in the admin portal)*

---

## 🛠️ Environment Configuration (`.env`)

```env
APP_NAME="Durva Eco Ware"
APP_ENV=development
APP_DEBUG=true

# MongoDB Configuration
DB_CONNECTION=mongodb
MONGODB_URI="mongodb+srv://<username>:<password>@cluster0.xxxxx.mongodb.net/?retryWrites=true&w=majority"
MONGODB_DATABASE=durva_eco_ware

SESSION_LIFETIME=86400
```
