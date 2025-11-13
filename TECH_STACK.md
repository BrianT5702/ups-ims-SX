# Technology Stack & Setup Requirements

## 📋 Languages & Frameworks Used

### Backend (Server-Side)

#### **PHP 8.2+**
- **Primary Language**: PHP is the main programming language
- **Version Required**: PHP 8.2 or higher
- **Purpose**: Server-side logic, database operations, business logic

#### **Laravel 11.0**
- **Framework**: Laravel is the PHP web framework
- **Version**: Laravel 11.0 (latest)
- **Purpose**: 
  - MVC architecture
  - Routing, controllers, models
  - Database migrations and ORM
  - Authentication and authorization
  - Session management
  - Caching

#### **Livewire 3.5**
- **Component Framework**: Server-side UI components
- **Purpose**: Creates interactive web interfaces without writing JavaScript
- **How it works**: PHP components that update the UI dynamically

### Frontend (Client-Side)

#### **HTML**
- Standard web markup

#### **CSS Frameworks**
- **Tailwind CSS 3.4**: Utility-first CSS framework for styling
- **Bootstrap 5.3**: UI component library (buttons, forms, modals, etc.)
- **SASS**: CSS preprocessor for advanced styling

#### **JavaScript Libraries**
- **Alpine.js 3.4**: Lightweight JavaScript framework for interactivity
- **Axios 1.7**: HTTP client for API requests
- **Chart.js** (via Laravel ChartJS): For charts and graphs
- **Toastr**: For notifications and alerts
- **Laravel Echo**: For real-time features
- **Pusher.js**: WebSocket library for real-time updates

### Build Tools

#### **Vite 5.0**
- **Build Tool**: Modern build tool for frontend assets
- **Purpose**: Bundles and compiles CSS/JavaScript
- **Features**: Hot module replacement for development

#### **Node.js 20.x**
- **Runtime**: Required for running Vite and npm packages
- **Purpose**: Manages JavaScript dependencies and build process

### Database

#### **MySQL**
- **Database System**: MySQL relational database
- **Purpose**: Stores all application data
- **Multi-Database**: Uses 3 separate databases (UPS, URS, UCS)

### PHP Packages (via Composer)

#### **Core Packages**
- `laravel/framework ^11.0` - Laravel framework
- `livewire/livewire ^3.5` - Livewire components
- `spatie/laravel-permission ^6.9` - Role and permission management

#### **Feature Packages**
- `barryvdh/laravel-dompdf ^3.1` - PDF generation (for printing documents)
- `maatwebsite/excel ^3.1` - Excel import/export functionality
- `icehouse-ventures/laravel-chartjs ^4.1` - Chart generation
- `php-flasher/flasher-toastr-laravel ^2.0` - Toast notifications
- `pusher/pusher-php-server ^7.2` - Real-time broadcasting
- `laravel/ui ^4.5` - UI scaffolding

### Development Tools

#### **Testing**
- `phpunit/phpunit ^11.0` - Unit testing framework
- `fakerphp/faker ^1.23` - Fake data generation for testing

#### **Code Quality**
- `laravel/pint ^1.13` - Code formatter
- `laravel/sail ^1.26` - Docker development environment

---

## 🛠️ Setup Requirements

### Required Software

#### 1. **PHP 8.2 or Higher**
- **What it is**: Programming language runtime
- **Why needed**: The application is written in PHP
- **Where to get**: 
  - Windows: Included in Laragon/XAMPP
  - Download: https://windows.php.net/download/
- **Extensions Required**:
  - `pdo_mysql` - Database connection
  - `mbstring` - String handling
  - `zip` - Archive handling
  - `exif` - Image metadata
  - `gd` - Image processing
  - `curl` - HTTP requests
  - `openssl` - Encryption
  - `xml` - XML processing

#### 2. **Composer**
- **What it is**: PHP dependency manager (like npm for JavaScript)
- **Why needed**: Installs PHP packages (Laravel, Livewire, etc.)
- **Where to get**: https://getcomposer.org/download/
- **Windows**: Installer available (Composer-Setup.exe)

#### 3. **Node.js 20.x**
- **What it is**: JavaScript runtime
- **Why needed**: Runs Vite build tool and npm packages
- **Where to get**: https://nodejs.org/
- **Version**: LTS version (20.x recommended)

#### 4. **MySQL Database**
- **What it is**: Database server
- **Why needed**: Stores all application data
- **Where to get**: 
  - Windows: Included in Laragon/XAMPP
  - Standalone: https://dev.mysql.com/downloads/
- **Requirement**: MySQL 5.7+ or MariaDB 10.3+

#### 5. **Web Server (Optional for Development)**
- **Apache** or **Nginx** (for production)
- **Development**: Laravel has built-in server (`php artisan serve`)

### Recommended Development Environment

#### **Option 1: Laragon (Easiest - Recommended)**
- **What it is**: All-in-one PHP development environment
- **Includes**: PHP 8.2, Composer, MySQL, Apache, phpMyAdmin
- **Download**: https://laragon.org/download/
- **Why recommended**: Everything pre-configured, one-click setup

#### **Option 2: XAMPP**
- **What it is**: Apache, MySQL, PHP, Perl package
- **Includes**: PHP, MySQL, Apache
- **Download**: https://www.apachefriends.org/
- **Note**: You'll need to install Composer separately

#### **Option 3: Manual Installation**
- Install PHP, Composer, MySQL, Node.js separately
- More control but requires more configuration

---

## 📦 What Gets Installed

### When you run `composer install`:
- Laravel framework
- Livewire components
- Spatie permissions package
- DomPDF for PDF generation
- Excel import/export library
- Chart.js integration
- And 50+ other PHP packages

### When you run `npm install`:
- Vite build tool
- Tailwind CSS
- Alpine.js
- Bootstrap
- Axios
- Laravel Echo
- Pusher.js
- And other frontend dependencies

---

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────┐
│         Frontend (Browser)              │
│  ┌──────────┐  ┌──────────┐           │
│  │ Tailwind │  │ Alpine.js │           │
│  │   CSS    │  │ JavaScript│           │
│  └──────────┘  └──────────┘           │
└─────────────────────────────────────────┘
                  │
                  │ HTTP Requests
                  ▼
┌─────────────────────────────────────────┐
│      Laravel Framework (PHP)            │
│  ┌──────────┐  ┌──────────┐           │
│  │ Livewire │  │  Laravel  │           │
│  │Components│  │   Routes  │           │
│  └──────────┘  └──────────┘           │
└─────────────────────────────────────────┘
                  │
                  │ Database Queries
                  ▼
┌─────────────────────────────────────────┐
│         MySQL Database                  │
│  ┌──────┐  ┌──────┐  ┌──────┐         │
│  │ UPS  │  │ URS  │  │ UCS  │         │
│  └──────┘  └──────┘  └──────┘         │
└─────────────────────────────────────────┘
```

---

## 🔄 Development Workflow

### 1. **Backend Development**
- Write PHP code in `app/` directory
- Use Laravel controllers, models, routes
- Use Livewire for interactive components
- Test with `php artisan serve`

### 2. **Frontend Development**
- Write CSS in `resources/css/`
- Write JavaScript in `resources/js/`
- Use Tailwind CSS classes in Blade templates
- Use Alpine.js for client-side interactivity
- Run `npm run dev` for hot reload

### 3. **Database Changes**
- Create migrations: `php artisan make:migration`
- Run migrations: `php artisan migrate`
- Seed data: `php artisan db:seed`

---

## 📝 Summary

### Languages:
- **PHP 8.2+** (Backend)
- **HTML/CSS/JavaScript** (Frontend)
- **SQL** (Database queries)

### Main Frameworks:
- **Laravel 11** (PHP web framework)
- **Livewire 3.5** (Server-side components)
- **Tailwind CSS** (Styling)
- **Alpine.js** (Client-side interactivity)

### Tools Needed:
- ✅ PHP 8.2+
- ✅ Composer
- ✅ Node.js 20.x
- ✅ MySQL
- ✅ Web Server (optional for development)

### Easiest Setup:
**Install Laragon** - It includes everything you need!

---

**For detailed setup instructions, see:**
- `WINDOWS_SETUP.md` - Install PHP, Composer, MySQL
- `QUICK_START.md` - Quick setup checklist
- `SETUP_GUIDE.md` - Complete setup guide









