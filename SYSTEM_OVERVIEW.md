# UPS-IMS System Overview

## Executive Summary

**UPS-IMS** (United Panel System - Inventory Management System) is a comprehensive multi-tenant inventory management application built with **Laravel 11** and **Livewire 3.5**. It serves three separate companies (UPS, URS, UCS) with shared infrastructure but isolated data through a multi-database architecture.

---

## System Architecture

### Multi-Tenant Design

The system uses a **multi-database approach** where three separate MySQL databases share the same schema but contain completely isolated data:

1. **UPS** - United Panel-System (M) Sdn. Bhd. (Company Registration: 772009-A)
2. **URS** - United Refrigeration-System (M) Sdn. Bhd. (Company Registration: 772011-D)
3. **UCS** - United Cold-System (M) Sdn. Bhd. (Company Registration: 748674-K)

**Key Features:**
- Users can switch between databases via UI (session-based)
- All three databases have identical schema
- User authentication uses the `ups` database, but user records exist in all three
- Database switching happens via `SwitchDatabase` middleware on every request
- Connection is fully reset and rehydrated on switch to prevent stale data

### Technology Stack

**Backend:**
- PHP 8.2+
- Laravel 11.0
- Livewire 3.5 (server-side components)
- MySQL (multi-database)
- Spatie Laravel Permission (RBAC)

**Frontend:**
- Tailwind CSS 3.4
- Bootstrap 5.3
- Alpine.js 3.4
- Chart.js (via Laravel ChartJS)
- Vite 5.0 (build tool)

**Key Packages:**
- `barryvdh/laravel-dompdf` - PDF generation for printing
- `maatwebsite/excel` - Excel import/export
- `icehouse-ventures/laravel-chartjs` - Charts and graphs
- `php-flasher/flasher-toastr-laravel` - Toast notifications
- `pusher/pusher-php-server` - Real-time features

---

## Core Business Entities

### 1. Users & Authentication

**User Model:**
- Always uses `ups` database connection for authentication
- Supports roles: Admin, User, Salesperson
- Has relationships with Purchase Orders and Delivery Orders
- Can be assigned as Salesman to Customers

**Default Accounts:**
- Admin: `admin@example.com` / `admin12345` (all permissions except "Manage Stock Movement")
- User: `user@example.com` / `user12345` (no default permissions)
- Salesperson: `salesman1@example.com` / `salesman12345` (no default permissions)

### 2. Customers

**Fields:**
- Account number, Name, Address (4 lines), Contact info
- Area, Terms, Business/GST registration numbers
- Currency (default: RM)
- Salesman assignment

**Features:**
- Historical snapshots stored in `customer_snapshots` table
- Can have multiple Delivery Orders
- Belongs to a Salesman (User)

### 3. Suppliers

**Fields:**
- Account number, Name, Address (4 lines), Contact info
- Area, Terms, Business/GST registration numbers
- Currency (default: RM)

**Features:**
- Historical snapshots stored in `supplier_snapshots` table
- Can have multiple Purchase Orders
- Can have multiple Items

### 4. Items (Inventory)

**Fields:**
- Item Code, Name, Unit of Measure (UNIT, BOX, KG, ROLL)
- Quantity (qty), Cost, Prices (Customer/Term/Cash)
- Stock Alert Level
- Supplier, Category, Brand
- Warehouse, Location
- Image, Memo

**Relationships:**
- Belongs to: Supplier, Category, Brand, Warehouse, Location
- Has many: Delivery Order Items, Purchase Order Items, Restock Lists
- Many-to-many: Locations

**Pricing Structure:**
- `cost` - Cost price
- `cust_price` - Customer price
- `term_price` - Term price
- `cash_price` - Cash price

### 5. Categories & Brands

**Categories:**
- Predefined list (ACCUMULATOR, ADAP-KOOL, BALL VALVE, etc.)
- Has many Brands and Items

**Brands:**
- Predefined list (AC&R, AIRMENDER, CHINA, EMERSON, etc.)
- Belongs to Category
- Has many Items

### 6. Warehouses & Locations

**Warehouses:**
- Has many Locations
- Has many Items

**Locations:**
- Belongs to Warehouse
- Has many Items
- Many-to-many with Items

### 7. Purchase Orders (PO)

**Fields:**
- PO Number, Reference Number, Date
- Supplier, User (creator)
- Status: Pending → Approved → Completed
- Final Total Price, Tax Rate, Tax Amount, Grand Total
- Printed flag

**Features:**
- Has many Purchase Order Items
- Uses Supplier Snapshot for historical data
- Can receive items (creates batches)
- Transaction logging on receipt

**Workflow:**
1. Create PO with items
2. Approve PO (requires "Approve PO" permission)
3. Receive items (creates Batch Tracking entries)
4. Update item quantities and prices
5. Mark as Completed

### 8. Delivery Orders (DO)

**Fields:**
- DO Number, Reference Number, Date
- Customer, Salesman, User (creator)
- Customer PO number
- Status: Pending → Completed
- Total Amount, Printed flag

**Features:**
- Has many Delivery Order Items
- Uses Customer Snapshot for historical data
- FIFO batch deduction (oldest batches first)
- Transaction logging on completion
- Supports pricing tier per item

**Workflow:**
1. Create DO with items
2. Select pricing tier per item (Customer/Term/Cash)
3. Complete DO (deducts stock via FIFO)
4. Creates transactions for each batch used

### 9. Quotations

**Fields:**
- Quotation Number, Date
- Status: Pending → Accepted/Rejected
- Final Total Price, Printed flag

**Features:**
- Has many Quotation Items
- Can be converted to Delivery Order
- Print preview available

### 10. Stock Movements (Picking List)

**Fields:**
- Movement Number, Date, Status, Type

**Features:**
- Has many Stock Movement Items
- Used for internal stock transfers
- Print preview available

---

## Advanced Features

### Batch Tracking System

**Purpose:** Track inventory by batch numbers with FIFO (First In, First Out) logic

**BatchTracking Model:**
- Batch Number (auto-generated)
- Purchase Order ID (optional)
- Item ID, Quantity
- Received Date, Received By (User)

**Features:**
- Automatic batch creation on PO receipt
- FIFO deduction on DO completion
- Batch quantity adjustments create transactions
- Batch expiry tracking for chemicals

**Batch Number Format:** Auto-generated unique identifiers

### Transaction Logging System

**Purpose:** Complete audit trail of all inventory movements

**Transaction Model:**
- Item ID, Batch ID (optional)
- Quantity on Hand, Before, After
- Transaction Quantity
- Transaction Type: "Stock In" or "Stock Out"
- Source Type: PO, DO, Batch Adjustment, Initial Stock, etc.
- Source Document Number
- User ID, Timestamps

**Transaction Sources:**
- Purchase Order receipt
- Delivery Order completion
- Batch adjustments
- Initial stock entry
- Stock movements

**Features:**
- Complete audit trail
- Tracks user who made the change
- Links to source documents
- Shows quantity before/after for each transaction

### Restock List

**Purpose:** Identify items that need restocking

**Logic:**
- Items where `qty <= stock_alert_level` and `qty >= 0`
- Automatically updated when stock changes
- Can be managed manually

### Chemical Management

**Specialized Models:**
- **IBCChemical** - IBC chemical consumption tracking
- **LoadingUnloading** - Loading/unloading operations
- **IncomingQualityControl (IQC)** - Quality control checks

**Features:**
- Expiry date tracking
- Dashboard shows chemicals expiring in 7 days
- Batch number tracking

### Excel Import/Export

**Import Support:**
- Customers (CustomerImport)
- Suppliers (SupplierImport)
- Items (ItemImport)

**Export Support:**
- Items (ItemsExport) - customizable columns
- Transactions (TransactionsExport)

**Item Import Format:**
- Column order: Category, Brand, Item Name, Qty, Cost, Cash Price, Term Price, Customer Price, Stock Alert, Supplier, Unit, Item Code, Warehouse, Location
- Auto-creates UNDEFINED category/brand if missing
- Creates batch tracking entries automatically

### Print/PDF Generation

**Supported Documents:**
- Purchase Orders
- Delivery Orders
- Quotations
- Stock Movements

**Features:**
- Print preview (DomPDF)
- Mark as printed functionality
- Company profile header on all documents
- Professional formatting

### Reports & Dashboard

**Dashboard Features:**
- Timeframe selection (Today, Month, Year)
- Charts showing PO and DO trends
- Inventory statistics:
  - Out of stock items
  - Items below alert level
  - Dead stock (not updated in 1 year)
- Expiring chemicals (7 days)
- Total counts for selected timeframe

**Report Features:**
- Transaction reports
- General reports
- Filterable by date range, item, customer, supplier

---

## Permission System

### Roles

1. **Admin**
   - All permissions except "Manage Stock Movement (Picking List)"
   - Full system access

2. **User**
   - No default permissions
   - Permissions assigned per user

3. **Salesperson**
   - No default permissions
   - Permissions assigned per user

### Permissions List

1. Manage User
2. Manage Brand
3. Manage Category
4. Manage Customer
5. Manage Inventory
6. Manage Location
7. Manage Supplier
8. Manage Restock List
9. View Transaction Log
10. Manage DO (Delivery Orders)
11. Manage PO (Purchase Orders)
12. Approve PO
13. Edit Company Profile
14. View Report
15. Manage Warehouse
16. View Batch List
17. View Consumption Form
18. Manage Stock Movement (Picking List)

**Implementation:**
- Uses Spatie Laravel Permission package
- Permission-based route protection
- Middleware checks on protected routes
- Gate-based authorization in AppServiceProvider

---

## Database Schema Overview

### Main Tables

**Core Entities:**
- `users` - User accounts (shared across databases via ups connection)
- `customers` - Customer information
- `suppliers` - Supplier information
- `items` - Inventory items
- `categories` - Item categories
- `brands` - Item brands
- `warehouses` - Warehouse locations
- `locations` - Storage locations within warehouses

**Order Management:**
- `purchase_orders` - Purchase orders
- `purchase_order_items` - PO line items
- `delivery_orders` - Delivery orders
- `delivery_order_items` - DO line items
- `quotations` - Quotations
- `quotation_items` - Quotation line items
- `stock_movements` - Stock movement records
- `stock_movement_items` - Stock movement line items

**Inventory Tracking:**
- `transactions` - Transaction log (complete audit trail)
- `batch_tracking` - Batch number tracking
- `restock_lists` - Items needing restock

**Company & Configuration:**
- `company_profiles` - Company information (varies per database)
- `ibc_chemicals` - IBC chemical records
- `loading_unloadings` - Loading/unloading records
- `incoming_quality_controls` - IQC records

**Snapshots (Historical Data):**
- `customer_snapshots` - Historical customer data
- `supplier_snapshots` - Historical supplier data

**Permission Tables (Spatie):**
- `permissions` - Permission definitions
- `roles` - Role definitions
- `model_has_permissions` - User permissions
- `model_has_roles` - User roles
- `role_has_permissions` - Role permissions

---

## Key Business Logic

### Stock Management Flow

1. **Stock In (Purchase Order):**
   - Create PO with items
   - Approve PO
   - Receive items → Creates Batch Tracking entries
   - Updates Item quantity
   - Creates Transaction records (Stock In)

2. **Stock Out (Delivery Order):**
   - Create DO with items
   - Select pricing tier per item
   - Complete DO → FIFO batch deduction
   - Updates Item quantity
   - Creates Transaction records (Stock Out) for each batch used

3. **Stock Adjustment:**
   - Direct batch quantity updates
   - Creates Transaction records
   - Updates Item total quantity

### Pricing Logic

- Items have 4 price fields: cost, cust_price, term_price, cash_price
- Delivery Order Items have `pricing_tier` field
- Pricing tier determines which price to use when creating DO
- Prices can be updated when receiving PO items

### Batch FIFO Logic

- On DO completion, stock is deducted from oldest batches first
- Multiple transactions created if stock spans multiple batches
- Ensures proper batch tracking and expiry management

### Order Status Flow

**Purchase Orders:**
- Pending → Approved → Completed

**Delivery Orders:**
- Pending → Completed

**Quotations:**
- Pending → Accepted/Rejected

---

## Middleware Stack

1. **SwitchDatabase** (runs first)
   - Switches database connection based on session
   - Resets all connections to prevent stale data
   - Rehydrates authenticated user from new database
   - Clears permission cache

2. **Authenticate**
   - Verifies user is logged in

3. **PreventBackHistory**
   - Prevents browser back button issues

4. **Role/Permission Middleware**
   - Checks user permissions (Spatie)
   - Route-level protection

---

## File Structure

```
app/
├── Exports/              # Excel export classes
├── Http/
│   ├── Controllers/     # Traditional controllers (Print, Profile, Auth)
│   └── Middleware/      # Custom middleware
├── Imports/             # Excel import classes
├── Livewire/            # Livewire components (main UI logic)
├── Models/              # Eloquent models
├── Providers/           # Service providers
├── Rules/               # Custom validation rules
└── View/                # View components

database/
├── migrations/          # Database migrations (49 files)
└── seeders/            # Database seeders

resources/
├── css/                # Stylesheets
├── js/                 # JavaScript files
└── views/              # Blade templates (71 files)

routes/
├── web.php             # Web routes
└── auth.php            # Authentication routes
```

---

## Development Workflow

### Local Setup

1. **Prerequisites:**
   - PHP 8.2+
   - Composer
   - Node.js 20.x
   - MySQL

2. **Installation:**
   ```bash
   composer install
   npm install
   ```

3. **Environment:**
   - Copy `.env.example` to `.env`
   - Configure database connections (UPS, URS, UCS)
   - Run `php artisan key:generate`

4. **Database Setup:**
   ```bash
   # Create databases
   php artisan migrate --database=ups
   php artisan migrate --database=urs
   php artisan migrate --database=ucs
   
   # Seed data
   php artisan db:seed --database=ups
   php artisan db:seed --database=urs
   php artisan db:seed --database=ucs
   ```

5. **Storage:**
   ```bash
   php artisan storage:link
   ```

6. **Development:**
   ```bash
   php artisan serve      # Backend server
   npm run dev            # Frontend assets (hot reload)
   ```

### Database Migrations

- 49 migration files
- Run migrations per database: `php artisan migrate --database={ups|urs|ucs}`
- Schema is identical across all three databases

### Adding Features

1. Create migration (if needed)
2. Create model (if needed)
3. Create Livewire component (for UI)
4. Add routes in `web.php`
5. Add permissions (if needed)
6. Test with all three databases

---

## Security Features

### Authentication
- Laravel's built-in authentication
- Password hashing via bcrypt
- Session-based authentication

### Authorization
- Role-based access control (RBAC) via Spatie
- Permission-based route protection
- Middleware checks on protected routes
- Gate-based authorization

### Data Protection
- Sensitive data (passwords) is hashed
- Database credentials in environment variables
- `.env` file is gitignored
- SQL injection protection (Laravel's query builder)
- Prepared statements (via Eloquent)

### Database Security
- SSL support for Aiven Cloud connections
- Separate databases for data isolation

---

## Performance Considerations

### Caching
- Config cache: `php artisan config:cache`
- View cache: `php artisan view:cache`
- Route cache: `php artisan route:cache`
- Permission cache: Managed by Spatie

### Database Optimization
- Indexes on foreign keys
- Connection pooling for multi-database
- Query optimization via Eloquent relationships

### Asset Optimization
- Vite for asset bundling and minification
- CDN support for production assets

---

## Deployment

### Docker Setup
- Base Image: `php:8.2-apache`
- Includes: PHP extensions, Node.js, Composer
- Startup Script: Automatically runs migrations and seeders
- Health Check: `/up` endpoint

### Render.com Configuration
- Environment: Docker
- Build Command: Handled by Dockerfile
- Start Command: `/usr/local/bin/start.sh`
- Health Check: HTTP endpoint

---

## Key Files Reference

**Configuration:**
- `config/database.php` - Database connections (UPS, URS, UCS)
- `config/permission.php` - Spatie permissions config

**Middleware:**
- `app/Http/Middleware/SwitchDatabase.php` - Database switching logic

**Models:**
- `app/Models/BaseModel.php` - Base model with dynamic connection
- `app/Models/User.php` - User model (always uses ups connection)
- `app/Models/Item.php` - Inventory items
- `app/Models/Transaction.php` - Transaction logging
- `app/Models/BatchTracking.php` - Batch tracking

**Livewire Components:**
- `app/Livewire/Dashboard.php` - Main dashboard
- `app/Livewire/ItemForm.php` - Item management
- `app/Livewire/POForm.php` - Purchase order management
- `app/Livewire/DOForm.php` - Delivery order management

**Controllers:**
- `app/Http/Controllers/PrintController.php` - PDF generation
- `app/Http/Controllers/Auth/UserController.php` - User management

**Routes:**
- `routes/web.php` - All web routes with permission middleware

---

## Testing

### Test Structure
```
tests/
├── Feature/      # Integration tests
│   ├── Auth/
│   └── ProfileTest.php
└── Unit/         # Unit tests
```

### Database Testing
- Use separate test databases
- Run migrations before tests
- Clean up after tests

---

## Monitoring & Logging

### Log Files
- Main log: `storage/logs/laravel.log`
- Apache logs: Configured in Dockerfile
- Error tracking: Via Laravel's exception handler

### Health Checks
- Endpoint: `/up`
- Returns 200 if application is healthy

---

## Backup Strategy

### Database Backups
- Each database (UPS, URS, UCS) should be backed up separately
- Daily backups recommended
- Retention: 30 days minimum

### Application Backups
- Code: Git repository
- Uploads: Backup `storage/app/public`
- Logs: Optional (can be regenerated)

---

## Future Enhancements

Potential improvements:
1. API endpoints for mobile app
2. Real-time notifications
3. Advanced reporting with filters
4. Multi-currency support
5. Barcode/QR code scanning
6. Email notifications
7. SMS notifications
8. Advanced analytics dashboard

---

## Support & Maintenance

### Regular Tasks
1. Clear caches after deployments
2. Monitor log files
3. Backup databases regularly
4. Update dependencies monthly
5. Review and rotate passwords
6. Monitor disk space
7. Check application health

---

## Summary

UPS-IMS is a sophisticated multi-tenant inventory management system designed for three related companies. It provides:

- **Complete inventory management** with batch tracking and FIFO logic
- **Multi-tenant architecture** with isolated databases
- **Comprehensive transaction logging** for audit trails
- **Role-based permissions** for security
- **Excel import/export** for data management
- **PDF generation** for document printing
- **Real-time dashboard** with charts and statistics
- **Chemical management** with expiry tracking
- **Professional UI** built with Livewire and Tailwind CSS

The system is production-ready, well-documented, and follows Laravel best practices.

---

**Last Updated:** Based on current codebase structure  
**Framework:** Laravel 11  
**PHP Version:** 8.2+  
**Database:** MySQL (Aiven Cloud for production, local for development)






