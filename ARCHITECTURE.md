# UPS-IMS Architecture & Database Structure

## Overview

This document explains the architecture, database structure, and key concepts of the UPS-IMS application.

---

## Multi-Tenant Architecture

The application uses a **multi-database approach** where three separate databases (UPS, URS, UCS) share the same schema but contain different data. Users can switch between databases via the UI.

### Database Connections

1. **UPS** - United Panel-System (M) Sdn. Bhd.
   - Default connection
   - Company Registration: 772009-A

2. **URS** - United Refrigeration-System (M) Sdn. Bhd.
   - Company Registration: 772011-D

3. **UCS** - United Cold-System (M) Sdn. Bhd.
   - Company Registration: 748674-K

### Database Switching Mechanism

- **Middleware**: `SwitchDatabase` (runs on every request)
- **Session Storage**: Active database stored in session (`active_db`)
- **Connection Reset**: All connections are purged and reconnected on switch
- **User Rehydration**: Authenticated user is reloaded from the new database

**Important**: The `User` model always uses the `ups` connection for authentication, but user data exists in all three databases.

---

## Core Models & Relationships

### 1. User Model
- **Connection**: Always uses `ups` database
- **Features**: Role-based permissions (Spatie)
- **Relationships**:
  - Has many Purchase Orders
  - Has many Delivery Orders
  - Can be assigned as Salesman to Customers

### 2. Customer Model
- **Fields**: Account, Name, Address, Contact Info, Area, Terms, GST, Currency
- **Relationships**:
  - Has many Delivery Orders
  - Belongs to Salesman (User)
- **Snapshots**: Historical data stored in `customer_snapshots` table

### 3. Supplier Model
- **Fields**: Account, Name, Address, Contact Info, Area, Terms, GST, Currency
- **Relationships**:
  - Has many Purchase Orders
  - Has many Items
- **Snapshots**: Historical data stored in `supplier_snapshots` table

### 4. Item Model
- **Fields**: Item Code, Name, Unit of Measure, Quantity, Cost, Prices (Customer/Term/Cash), Stock Alert Level
- **Relationships**:
  - Belongs to Supplier
  - Belongs to Category
  - Belongs to Brand
  - Belongs to Warehouse
  - Belongs to Location
  - Has many Delivery Order Items
  - Has many Purchase Order Items
  - Has many Restock Lists
  - Belongs to many Locations (many-to-many)

### 5. Category Model
- **Fields**: Category Name
- **Relationships**:
  - Has many Brands
  - Has many Items

### 6. Brand Model
- **Fields**: Brand Name
- **Relationships**:
  - Belongs to Category
  - Has many Items

### 7. Warehouse Model
- **Fields**: Warehouse Name
- **Relationships**:
  - Has many Locations
  - Has many Items

### 8. Location Model
- **Fields**: Location Name
- **Relationships**:
  - Belongs to Warehouse
  - Has many Items
  - Belongs to many Items (many-to-many)

### 9. Purchase Order (PO) Model
- **Fields**: PO Number, Reference Number, Date, Status, Final Total Price, Tax, Printed Flag
- **Relationships**:
  - Belongs to Supplier
  - Belongs to User (creator)
  - Has many Purchase Order Items

### 10. Purchase Order Item Model
- **Fields**: Item ID, Quantity, Unit Price, Total Price, Custom Item Name, Description
- **Relationships**:
  - Belongs to Purchase Order
  - Belongs to Item

### 11. Delivery Order (DO) Model
- **Fields**: DO Number, Reference Number, Date, Status, Final Total Price, Printed Flag
- **Relationships**:
  - Belongs to Customer
  - Belongs to User (creator)
  - Has many Delivery Order Items

### 12. Delivery Order Item Model
- **Fields**: Item ID, Quantity, Unit Price, Total Price, Pricing Tier, Custom Item Name, Description
- **Relationships**:
  - Belongs to Delivery Order
  - Belongs to Item

### 13. Quotation Model
- **Fields**: Quotation Number, Date, Status, Final Total Price, Printed Flag
- **Relationships**:
  - Has many Quotation Items

### 14. Stock Movement Model
- **Fields**: Movement Number, Date, Status, Type
- **Relationships**:
  - Has many Stock Movement Items

### 15. Transaction Model
- **Purpose**: Tracks all inventory movements (in/out)
- **Fields**: Item ID, Transaction Type, Quantity, Reference ID, Batch Number
- **Used for**: Transaction logs and reports

### 16. Batch Tracking Model
- **Purpose**: Tracks batch numbers for items
- **Fields**: Batch Number, Item ID, Quantity, Expiry Date

### 17. Restock List Model
- **Purpose**: Items that need restocking
- **Relationships**:
  - Belongs to Item

---

## Chemical Management Models

### IBCChemical Model
- Tracks IBC chemical consumption

### LoadingUnloading Model
- Tracks loading/unloading operations

### IncomingQualityControl Model
- Tracks incoming quality control checks

---

## Permission System

### Roles
- **Admin**: All permissions except "Manage Stock Movement"
- **User**: No default permissions (assigned per user)
- **Salesperson**: No default permissions (assigned per user)

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

---

## Key Features Implementation

### 1. Database Switching
- **Route**: `POST /switch-db`
- **Middleware**: `SwitchDatabase` (runs before other middleware)
- **Session Key**: `active_db`
- **Valid Values**: `ups`, `urs`, `ucs`

### 2. Excel Import/Export
- **Import**: Customers, Suppliers, Items
- **Export**: Items, Transactions
- **Library**: Maatwebsite Excel

### 3. Print Preview
- **Supported Documents**: PO, DO, Quotation, Stock Movement
- **Library**: DomPDF
- **Route Pattern**: `/print/{document-type}/{id}/preview`

### 4. Reports & Dashboard
- **Charts**: Chart.js (via Laravel ChartJS)
- **Reports**: Transaction reports, general reports
- **Dashboard**: Overview with charts

### 5. Real-time Features
- **Pusher**: Configured for real-time updates
- **Laravel Echo**: For broadcasting events

---

## Middleware Stack

1. **SwitchDatabase** - Runs first, switches database connection
2. **Authenticate** - Verifies user is logged in
3. **PreventBackHistory** - Prevents browser back button issues
4. **Role/Permission Middleware** - Checks user permissions (Spatie)

---

## Frontend Architecture

### Technologies
- **Livewire 3.5**: Server-side components
- **Tailwind CSS**: Styling
- **Alpine.js**: Client-side interactivity
- **Bootstrap 5**: UI components
- **Vite**: Asset bundling
- **Chart.js**: Charts and graphs
- **Toastr**: Notifications

### Asset Structure
```
resources/
├── css/
│   ├── app.css
│   └── global.css
├── js/
│   ├── app.js
│   └── bootstrap.js
└── views/
    ├── layouts/
    ├── components/
    └── livewire/
```

---

## Database Schema Overview

### Main Tables
- `users` - User accounts (shared across databases)
- `customers` - Customer information
- `suppliers` - Supplier information
- `items` - Inventory items
- `categories` - Item categories
- `brands` - Item brands
- `warehouses` - Warehouse locations
- `locations` - Storage locations within warehouses
- `purchase_orders` - Purchase orders
- `purchase_order_items` - PO line items
- `delivery_orders` - Delivery orders
- `delivery_order_items` - DO line items
- `quotations` - Quotations
- `quotation_items` - Quotation line items
- `stock_movements` - Stock movement records
- `stock_movement_items` - Stock movement line items
- `transactions` - Transaction log
- `batch_tracking` - Batch number tracking
- `restock_lists` - Items needing restock
- `company_profiles` - Company information
- `ibc_chemicals` - IBC chemical records
- `loading_unloadings` - Loading/unloading records
- `incoming_quality_controls` - IQC records

### Permission Tables (Spatie)
- `permissions` - Permission definitions
- `roles` - Role definitions
- `model_has_permissions` - User permissions
- `model_has_roles` - User roles
- `role_has_permissions` - Role permissions

### Snapshot Tables
- `customer_snapshots` - Historical customer data
- `supplier_snapshots` - Historical supplier data

---

## Key Business Logic

### 1. Pricing Structure
Items have three pricing tiers:
- `cost` - Cost price
- `cust_price` - Customer price
- `term_price` - Term price
- `cash_price` - Cash price

Delivery Order Items also have a `pricing_tier` field that determines which price to use.

### 2. Stock Management
- Stock is tracked via `qty` field in `items` table
- Transactions are logged in `transactions` table
- Batch tracking is separate from main stock
- Restock list is generated based on `stock_alert_level`

### 3. Order Status Flow
- **Purchase Orders**: Pending → Approved → Completed
- **Delivery Orders**: Pending → Completed
- **Quotations**: Pending → Accepted/Rejected

### 4. Currency Support
- Customers and Suppliers have a `currency` field
- Default currency: RM (Malaysian Ringgit)

---

## File Storage

### Storage Structure
```
storage/
├── app/
│   ├── public/        # Publicly accessible files
│   └── private/       # Private files
├── framework/
│   ├── cache/         # Application cache
│   ├── sessions/      # Session files
│   └── views/         # Compiled views
└── logs/              # Application logs
```

### Public Storage
- Items can have images stored in `storage/app/public`
- Symbolic link: `public/storage` → `storage/app/public`

---

## Security Considerations

### 1. Authentication
- Uses Laravel's built-in authentication
- Password hashing via bcrypt
- Session-based authentication

### 2. Authorization
- Role-based access control (RBAC) via Spatie
- Permission-based route protection
- Middleware checks on protected routes

### 3. Database Security
- SSL support for Aiven Cloud connections
- Prepared statements (via Eloquent)
- SQL injection protection (Laravel's query builder)

### 4. Data Protection
- Sensitive data (passwords) is hashed
- Database credentials in environment variables
- `.env` file is gitignored

---

## Performance Considerations

### 1. Caching
- Config cache: `php artisan config:cache`
- View cache: `php artisan view:cache`
- Route cache: `php artisan route:cache`
- Permission cache: Managed by Spatie

### 2. Database Optimization
- Indexes on foreign keys
- Connection pooling for multi-database
- Query optimization via Eloquent relationships

### 3. Asset Optimization
- Vite for asset bundling and minification
- CDN support for production assets

---

## Deployment Architecture

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

## Development Workflow

### 1. Local Development
```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --database=ups

# Start development server
php artisan serve
npm run dev
```

### 2. Database Changes
```bash
# Create migration
php artisan make:migration create_example_table

# Run migration
php artisan migrate --database=ups

# Rollback migration
php artisan migrate:rollback --database=ups
```

### 3. Adding Features
1. Create migration (if needed)
2. Create model (if needed)
3. Create Livewire component (for UI)
4. Add routes
5. Add permissions (if needed)
6. Test with all three databases

---

## Testing Considerations

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

## Future Considerations

### Potential Enhancements
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

**Last Updated**: Based on current codebase structure
**Framework**: Laravel 11
**PHP Version**: 8.2+
**Database**: MySQL (Aiven Cloud for production)










