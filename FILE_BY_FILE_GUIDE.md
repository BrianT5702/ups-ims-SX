# UPS-IMS File-by-File Guide

This document explains what each file does in the UPS-IMS system, organized by directory structure.

---

## 📁 Root Directory Files

### Configuration & Setup Files

**`composer.json`**
- Defines PHP dependencies (Laravel, Livewire, Spatie Permissions, DomPDF, Excel, etc.)
- Specifies PHP version requirement (^8.2)
- Contains autoload configuration and scripts

**`package.json`**
- Defines JavaScript/Node.js dependencies (Vite, Tailwind, Alpine.js, Bootstrap, etc.)
- Contains npm scripts for building assets (`npm run dev`, `npm run build`)

**`artisan`**
- Laravel's command-line interface entry point
- Used to run migrations, seeders, clear cache, etc.

**`vite.config.js`**
- Configuration for Vite build tool
- Defines entry points for CSS and JavaScript
- Configures Laravel Vite plugin

**`tailwind.config.js`**
- Tailwind CSS configuration
- Defines content paths for purging unused styles

**`phpunit.xml`**
- PHPUnit testing configuration
- Defines test environment and database settings

**`Dockerfile`**
- Docker container configuration for deployment
- Sets up PHP 8.2 with Apache, Node.js, and required extensions

**`render.yaml`**
- Render.com deployment configuration
- Defines build and start commands for cloud deployment

### Setup Scripts

**`start.ps1`**
- PowerShell script to start the development server
- Checks for node_modules and builds assets if needed
- Creates storage link if missing
- Starts Laravel development server on port 8000

**`setup.ps1`**
- Automated setup script for Windows
- Installs dependencies, sets up environment, runs migrations

**`create_databases.bat`**
- Batch script to create the three MySQL databases (UPS, URS, UCS)
- Uses MySQL command-line tool from Laragon path
- Creates databases with UTF8MB4 charset

**`create_databases.ps1`**
- PowerShell version of database creation script

**`create_databases.sql`**
- SQL script for creating databases (alternative to batch script)

**`show_accounts.php`**
- PHP script to display all user accounts across all three databases
- Shows login credentials for testing

### Documentation Files

**`README.md`**
- Main project documentation
- Links to setup guides and architecture docs

**`ARCHITECTURE.md`**
- Detailed technical architecture documentation
- Database structure, models, relationships
- Multi-tenant design explanation

**`TECH_STACK.md`**
- Technology stack overview
- Required software and versions
- Setup requirements

**`QUICK_START.md`**
- Quick setup checklist
- Step-by-step installation guide

**`LOGIN_CREDENTIALS.md`**
- Default login credentials for all user accounts
- Available in all three databases

**`SYSTEM_OVERVIEW.md`**
- Comprehensive system overview (created by AI)
- Complete feature documentation

**`WINDOWS_SETUP.md`**
- Windows-specific installation guide
- How to install PHP, Composer, MySQL on Windows

**`DATABASE_SETUP.md`**
- Database setup instructions
- Migration and seeder information

**`INSTALL_PHPMYADMIN.md`**
- Guide to install phpMyAdmin for database management

**`TROUBLESHOOTING_500_ERROR.md`**
- Troubleshooting guide for common 500 errors

---

## 📁 `app/` Directory

### `app/Models/` - Database Models

**`BaseModel.php`**
- Base model class that all other models extend
- Handles dynamic database connection switching
- Uses session `active_db` to determine which database (UPS/URS/UCS) to use
- All models inherit this connection logic

**`User.php`**
- User authentication model
- Always uses `ups` database connection (shared across all databases)
- Uses Spatie HasRoles trait for permissions
- Relationships: PurchaseOrders, DeliveryOrders
- Fields: name, email, username, phone_num, password

**`Customer.php`**
- Customer model (extends BaseModel)
- Fields: account, cust_name, address (4 lines), phone, fax, email, area, term, business/GST registration, currency, salesman_id
- Relationships: DeliveryOrders, Salesman (User)
- Used for customer management and delivery orders

**`Supplier.php`**
- Supplier model (extends BaseModel)
- Fields: account, sup_name, address (4 lines), phone, fax, email, area, term, business/GST registration, currency
- Relationships: PurchaseOrders, Items
- Used for supplier management and purchase orders

**`Item.php`**
- Inventory item model (extends BaseModel)
- Fields: item_code, item_name, um (unit of measure), qty, cost, cust_price, term_price, cash_price, stock_alert_level, sup_id, cat_id, brand_id, warehouse_id, location_id, image, memo
- Relationships: Supplier, Category, Brand, Warehouse, Location, DeliveryOrderItems, PurchaseOrderItems, RestockLists, Locations (many-to-many)
- Core inventory management entity

**`Category.php`**
- Item category model (extends BaseModel)
- Fields: cat_name
- Relationships: Brands, Items
- Predefined categories (ACCUMULATOR, ADAP-KOOL, etc.)

**`Brand.php`**
- Item brand model (extends BaseModel)
- Fields: brand_name
- Relationships: Category, Items
- Predefined brands (AC&R, AIRMENDER, etc.)

**`Warehouse.php`**
- Warehouse model (extends BaseModel)
- Fields: warehouse_name
- Relationships: Locations, Items
- Physical warehouse locations

**`Location.php`**
- Storage location model (extends BaseModel)
- Fields: location_name, warehouse_id
- Relationships: Warehouse, Items, Items (many-to-many)
- Specific storage locations within warehouses

**`PurchaseOrder.php`**
- Purchase order model (extends BaseModel, SoftDeletes)
- Fields: ref_num, po_num, sup_id, user_id, date, remark, status, printed, final_total_price, tax_rate, tax_amount, grand_total, supplier_snapshot_id
- Relationships: Supplier, SupplierSnapshot, User, Items (PurchaseOrderItems)
- Status flow: Pending → Approved → Completed

**`PurchaseOrderItem.php`**
- Purchase order line item model (extends BaseModel)
- Fields: po_id, item_id, quantity, unit_price, total_price, custom_item_name, description
- Relationships: PurchaseOrder, Item
- Individual items in a purchase order

**`DeliveryOrder.php`**
- Delivery order model (extends BaseModel, SoftDeletes)
- Fields: ref_num, cust_id, salesman_id, user_id, date, cust_po, do_num, total_amount, remark, customer_snapshot_id, status, printed
- Relationships: Items (DeliveryOrderItems), Customer, CustomerSnapshot, Salesman (User), User
- Status flow: Pending → Completed
- Uses FIFO batch deduction on completion

**`DeliveryOrderItem.php`**
- Delivery order line item model (extends BaseModel)
- Fields: do_id, item_id, quantity, unit_price, total_price, pricing_tier, custom_item_name, description
- Relationships: DeliveryOrder, Item
- Individual items in a delivery order
- Pricing tier determines which price (Customer/Term/Cash) to use

**`Quotation.php`**
- Quotation model (extends BaseModel)
- Fields: quotation_number, date, status, final_total_price, printed
- Relationships: Items (QuotationItems)
- Status: Pending → Accepted/Rejected

**`QuotationItem.php`**
- Quotation line item model (extends BaseModel)
- Fields: quotation_id, item_id, quantity, unit_price, total_price, custom_item_name, description
- Relationships: Quotation, Item

**`StockMovement.php`**
- Stock movement model (extends BaseModel)
- Fields: movement_number, date, status, type
- Relationships: Items (StockMovementItems), User
- Used for internal stock transfers (picking list)

**`StockMovementItem.php`**
- Stock movement line item model (extends BaseModel)
- Fields: stock_movement_id, item_id, quantity, unit_price, total_price
- Relationships: StockMovement, Item

**`Transaction.php`**
- Transaction log model (extends BaseModel)
- Fields: item_id, batch_id, user_id, qty_on_hand, qty_before, qty_after, transaction_qty, transaction_type, source_type, source_doc_num
- Relationships: Batch, Item, User, PurchaseOrder, DeliveryOrder
- Complete audit trail of all inventory movements
- Transaction types: "Stock In", "Stock Out"
- Source types: PO, DO, Batch Adjustment, Initial Stock, etc.

**`BatchTracking.php`**
- Batch tracking model (extends BaseModel)
- Fields: batch_num, po_id, item_id, quantity, received_date, received_by
- Relationships: PurchaseOrder, Item, ReceivedBy (User)
- Tracks inventory by batch numbers with FIFO logic
- Auto-generated batch numbers

**`RestockList.php`**
- Restock list model (extends BaseModel)
- Fields: item_id
- Relationships: Item
- Items that need restocking (qty <= stock_alert_level)

**`CompanyProfile.php`**
- Company profile model (extends BaseModel)
- Fields: company_name, company_no, gst_no, address (4 lines), phone numbers, fax, email
- Stores company information (varies per database: UPS/URS/UCS)
- Used in print previews

**`CustomerSnapshot.php`**
- Customer snapshot model (extends BaseModel)
- Historical snapshot of customer data at time of order creation
- Fields: Same as Customer model
- Relationships: DeliveryOrders
- Preserves customer data even if customer record changes

**`SupplierSnapshot.php`**
- Supplier snapshot model (extends BaseModel)
- Historical snapshot of supplier data at time of order creation
- Fields: Same as Supplier model
- Relationships: PurchaseOrders
- Preserves supplier data even if supplier record changes

**`IBCChemical.php`**
- IBC chemical model (extends BaseModel)
- Fields: do_num, che_code, expiry_date, and other chemical-specific fields
- Tracks IBC chemical consumption
- Used for expiry date tracking

**`LoadingUnloading.php`**
- Loading/unloading model (extends BaseModel)
- Fields: Related to loading/unloading operations
- Tracks loading and unloading of chemicals/materials

**`IncomingQualityControl.php`**
- Incoming quality control model (extends BaseModel)
- Fields: do_num, che_code, expiry_date, and QC-specific fields
- Tracks incoming quality control checks
- Used for expiry date tracking

### `app/Http/Controllers/` - Traditional Controllers

**`Controller.php`**
- Base controller class
- All other controllers extend this

**`ProfileController.php`**
- Handles user profile management
- Methods: edit, update, destroy
- Routes: GET/PATCH/DELETE `/profile`

**`PrintController.php`**
- Handles PDF generation and print previews
- Methods:
  - `previewPO($id)` - Purchase order preview
  - `previewDO($id)` - Delivery order preview
  - `previewQuotation($id)` - Quotation preview
  - `previewStockMovement($id)` - Stock movement preview
  - `markPOPrinted($id)` - Mark PO as printed
  - `markDOPrinted($id)` - Mark DO as printed
  - `markQuotationPrinted($id)` - Mark quotation as printed
- Uses DomPDF for PDF generation

**`Auth/UserController.php`**
- Handles authentication and user management
- Methods:
  - `createLogin()` - Show login form
  - `storeLogin()` - Process login
  - `createRegister()` - Show registration form
  - `storeRegister()` - Process registration
  - `destroy()` - Logout
  - `showImportForm()` - Show Excel import form
  - `importExcel()` - Process Excel import (Items/Customers/Suppliers)
- Handles Excel import with database switching

### `app/Http/Middleware/` - Middleware

**`SwitchDatabase.php`**
- **CRITICAL** middleware for multi-tenant functionality
- Runs on every request (first in middleware stack)
- Switches database connection based on session `active_db`
- Fully resets all connections to prevent stale data
- Rehydrates authenticated user from new database
- Clears Spatie permission cache
- Enables seamless database switching in UI

**`PreventBackHistory.php`**
- Prevents browser back button issues
- Sets cache control headers to prevent caching
- Ensures users can't access cached pages after logout

**`Admin.php`**
- Middleware to check if user has Admin role
- Not currently used (permissions used instead)

**`User.php`**
- Middleware to check if user is authenticated
- Not currently used (Laravel's auth middleware used instead)

### `app/Http/Requests/` - Form Request Validation

**`Auth/LoginRequest.php`**
- Validates login form data
- Handles authentication logic
- Custom validation rules for login

**`ProfileUpdateRequest.php`**
- Validates profile update form data
- Ensures email/username uniqueness
- Password validation rules

### `app/Livewire/` - Livewire Components (Main UI Logic)

**`Dashboard.php`**
- Main dashboard component
- Features:
  - Timeframe selection (Today, Month, Year)
  - Charts showing PO and DO trends (Chart.js)
  - Inventory statistics (out of stock, below alert, dead stock)
  - Expiring chemicals (7 days)
  - Total counts for selected timeframe
- Renders: `livewire.dashboard`

**`ItemList.php`**
- Lists all inventory items
- Features: Search, filter by category/brand/supplier, pagination
- Renders: `livewire.item-list`

**`ItemForm.php`**
- Create/edit item form
- Features:
  - Item details (code, name, prices, quantities)
  - Batch tracking management
  - Stock alert level
  - Image upload
  - Batch quantity adjustments (creates transactions)
  - Initial stock entry
- Renders: `livewire.item-form`

**`CustomerList.php`**
- Lists all customers
- Features: Search, filter, pagination
- Renders: `livewire.customer-list`

**`CustomerForm.php`**
- Create/edit customer form
- Features: Customer details, address, contact info, salesman assignment
- Renders: `livewire.customer-form`

**`SupplierList.php`**
- Lists all suppliers
- Features: Search, filter, pagination
- Renders: `livewire.supplier-list`

**`SupplierForm.php`**
- Create/edit supplier form
- Features: Supplier details, address, contact info
- Renders: `livewire.supplier-form`

**`CategoryList.php`**
- Lists all categories
- Features: Create, edit, delete categories
- Renders: `livewire.category-list`

**`BrandList.php`**
- Lists all brands
- Features: Create, edit, delete brands, filter by category
- Renders: `livewire.brand-list`

**`POList.php`**
- Lists all purchase orders
- Features: Search, filter by supplier, status, date range
- Renders: `livewire.p-o-list`

**`POForm.php`**
- Create/edit purchase order form
- Features:
  - Add items to PO
  - Receive items (creates batches)
  - Update item costs/prices
  - Approve PO (requires permission)
  - Complete PO
  - Transaction logging on receipt
- Renders: `livewire.po-form`

**`DOList.php`**
- Lists all delivery orders
- Features: Search, filter by customer, status, date range
- Renders: `livewire.d-o-list`

**`DOForm.php`**
- Create/edit delivery order form
- Features:
  - Add items to DO
  - Select pricing tier per item
  - Complete DO (FIFO batch deduction)
  - Transaction logging (multiple transactions if spans batches)
  - Customer snapshot creation
- Renders: `livewire.d-o-form`

**`QuotationList.php`**
- Lists all quotations
- Features: Search, filter, status
- Renders: `livewire.quotation-list`

**`QuotationForm.php`**
- Create/edit quotation form
- Features: Add items, calculate totals, convert to DO
- Renders: `livewire.quotation-form`

**`StockMovementList.php`**
- Lists all stock movements
- Features: Search, filter, status
- Renders: `livewire.stock-movement-list`

**`StockMovementForm.php`**
- Create/edit stock movement form
- Features: Internal stock transfers, picking list
- Renders: `livewire.stock-movement-form`

**`TransactionLog.php`**
- Displays transaction log
- Features: Filter by item, date range, transaction type
- Shows complete audit trail
- Renders: `livewire.transaction-log`

**`TransactionReport.php`**
- Transaction reporting
- Features: Advanced filtering, export to Excel
- Renders: `livewire.transaction-report`

**`RestockList.php`**
- Displays items needing restock
- Features: Items where qty <= stock_alert_level
- Renders: `livewire.restock-list`

**`BatchList.php`**
- Lists all batches
- Features: Filter by item, batch number, date
- Renders: `livewire.batch-list`

**`BatchDetails.php`**
- Shows batch details
- Features: Batch information, transactions, expiry dates
- Renders: `livewire.batch-details`

**`UserList.php`**
- Lists all users
- Features: Search, filter by role
- Renders: `livewire.user-list`

**`UserForm.php`**
- Create/edit user form
- Features: User details, role assignment, permission assignment
- Renders: `livewire.user-form`

**`ManageRolesPermissions.php`**
- Role and permission management
- Features: Create/edit roles, assign permissions
- Renders: `livewire.manage-roles-permissions`

**`Profile.php`**
- Company profile management
- Features: Edit company information (name, address, contact)
- Renders: `livewire.profile`

**`LocationMap.php`**
- Location management
- Features: Create/edit locations, assign to warehouses
- Renders: `livewire.location-map`

**`WarehouseLocation.php`**
- Warehouse management
- Features: Create/edit warehouses
- Renders: `livewire.warehouse-location`

**`Report.php`**
- General reporting
- Features: Various reports, filters
- Renders: `livewire.report`

**`ChemicalForm/IBCChemicalForm.php`**
- IBC chemical form
- Features: Track IBC chemical consumption, expiry dates
- Renders: `livewire.chemical-form.i-b-c-chemical-form`

**`ChemicalForm/LoadingUnloadingForm.php`**
- Loading/unloading form
- Features: Track loading and unloading operations
- Renders: `livewire.chemical-form.loading-unloading-form`

**`ChemicalForm/IncomingQualityControlForm.php`**
- Incoming quality control form
- Features: Track IQC checks, expiry dates
- Renders: `livewire.chemical-form.incoming-quality-control-form`

**`ChemicalForm/ConsumptionDashboard.php`**
- Chemical consumption dashboard
- Features: Overview of chemical consumption, expiry tracking
- Renders: `livewire.chemical-form.consumption-dashboard`

### `app/Imports/` - Excel Import Classes

**`ItemImport.php`**
- Imports items from Excel file
- Column mapping: Category, Brand, Item Name, Qty, Cost, Cash Price, Term Price, Customer Price, Stock Alert, Supplier, Unit, Item Code, Warehouse, Location
- Auto-creates UNDEFINED category/brand if missing
- Creates batch tracking entries automatically
- Logs import success/failure

**`CustomerImport.php`**
- Imports customers from Excel file
- Column mapping: Account, Name, Address (4 lines), Phone, Fax, Email, Area, Term, Business/GST Registration, Currency
- Normalizes currency (RM/MYR)
- Maps terms (COD → C.O.D, etc.)
- Assigns default salesman (first Salesperson user)
- Starts from row 6 (skips header)

**`SupplierImport.php`**
- Imports suppliers from Excel file
- Column mapping: Account, Name, Address (4 lines), Phone, Fax, Email, Area, Term, Business/GST Registration, Currency
- Normalizes currency (RM/MYR)
- Maps terms (COD → C.O.D, 30 DAYS, 60 DAYS, etc.)
- Starts from row 6 (skips header)

### `app/Exports/` - Excel Export Classes

**`ItemsExport.php`**
- Exports items to Excel
- Customizable columns
- Formats headings (underscores to spaces, capitalize)
- Maps item data to Excel rows

**`TransactionsExport.php`**
- Exports transactions to Excel
- Customizable columns
- Formats headings
- Maps transaction data to Excel rows

### `app/Providers/` - Service Providers

**`AppServiceProvider.php`**
- Main service provider
- Boot method: Sets up Gate before callback
- Checks for denied permissions in user model
- Handles permission denial logic

### `app/Rules/` - Custom Validation Rules

**`UniqueInCurrentDatabase.php`**
- Custom validation rule for uniqueness
- Checks uniqueness in the currently active database (UPS/URS/UCS)
- Uses session `active_db` to determine which database to check
- Supports ignoring specific ID (for updates)
- Used for account numbers, item codes, etc.

### `app/View/Components/` - Blade Components

**`AppLayout.php`**
- Main application layout component
- Includes sidebar, navigation, main content area
- Used by authenticated pages

**`GuestLayout.php`**
- Guest layout component (login/register pages)
- Minimal layout without sidebar

**`Sidebar.php`**
- Sidebar navigation component
- Menu items based on user permissions
- Database switcher (UPS/URS/UCS)
- User profile dropdown

---

## 📁 `routes/` Directory

**`web.php`**
- Main web routes file
- Defines all application routes
- Route groups:
  - Public routes (login)
  - Authenticated routes with middleware (auth, preventBackHistory, switchdb)
  - Permission-protected routes
- Key routes:
  - `/switch-db` - Database switching
  - `/dashboard` - Dashboard
  - `/users/*` - User management
  - `/items/*` - Item management
  - `/customers/*` - Customer management
  - `/suppliers/*` - Supplier management
  - `/purchase-orders/*` - PO management
  - `/delivery-orders/*` - DO management
  - `/quotations/*` - Quotation management
  - `/stock-movements/*` - Stock movement management
  - `/print/*` - Print previews
  - `/report/*` - Reports
  - `/chemical/*` - Chemical management
  - `/import-excel` - Excel import

**`auth.php`**
- Authentication routes
- Login, register, logout, password confirmation
- Uses UserController methods

**`console.php`**
- Artisan command routes (if any custom commands defined)

---

## 📁 `resources/views/` Directory

### `layouts/` - Layout Templates

**`app.blade.php`**
- Main application layout
- Includes navigation, sidebar, content area
- Loads CSS/JS assets via Vite
- Toastr notifications

**`guest.blade.php`**
- Guest layout (login/register)
- Minimal layout

**`navigation.blade.php`**
- Navigation bar component
- User menu, logout button

### `auth/` - Authentication Views

**`login.blade.php`**
- Login form
- Email/username and password fields

**`register.blade.php`**
- Registration form
- Name, email, username, password fields

**`confirm-password.blade.php`**
- Password confirmation form
- Used for sensitive operations

### `livewire/` - Livewire Component Views

Each Livewire component has a corresponding Blade view:
- `dashboard.blade.php` - Dashboard view
- `item-list.blade.php` - Item list
- `item-form.blade.php` - Item form
- `customer-list.blade.php` - Customer list
- `customer-form.blade.php` - Customer form
- `supplier-list.blade.php` - Supplier list
- `supplier-form.blade.php` - Supplier form
- `p-o-list.blade.php` - Purchase order list
- `po-form.blade.php` - Purchase order form
- `d-o-list.blade.php` - Delivery order list
- `d-o-form.blade.php` - Delivery order form
- `quotation-list.blade.php` - Quotation list
- `quotation-form.blade.php` - Quotation form
- `stock-movement-list.blade.php` - Stock movement list
- `stock-movement-form.blade.php` - Stock movement form
- `transaction-log.blade.php` - Transaction log
- `transaction-report.blade.php` - Transaction report
- `restock-list.blade.php` - Restock list
- `batch-list.blade.php` - Batch list
- `batch-details.blade.php` - Batch details
- `user-list.blade.php` - User list
- `user-form.blade.php` - User form
- `manage-roles-permissions.blade.php` - Role/permission management
- `profile.blade.php` - Company profile
- `location-map.blade.php` - Location management
- `warehouse-location.blade.php` - Warehouse management
- `category-list.blade.php` - Category list
- `brand-list.blade.php` - Brand list
- `report.blade.php` - Reports
- `chemical-form/*.blade.php` - Chemical form views

### `components/` - Reusable Components

**`sidebar.blade.php`**
- Sidebar navigation menu
- Permission-based menu items
- Database switcher

**`modal.blade.php`**
- Reusable modal component
- Used for confirmations, forms in modals

**`stock-alert.blade.php`**
- Stock alert component
- Shows items below alert level

**`application-logo.blade.php`**
- Application logo component

**`auth-session-status.blade.php`**
- Authentication session status display

**`danger-button.blade.php`**
- Danger button component (red, for delete actions)

**`primary-button.blade.php`**
- Primary button component (blue)

**`secondary-button.blade.php`**
- Secondary button component (gray)

**`text-input.blade.php`**
- Text input component
- Includes label and error display

**`input-label.blade.php`**
- Input label component

**`input-error.blade.php`**
- Input error display component

**`dropdown.blade.php`**
- Dropdown menu component

**`dropdown-link.blade.php`**
- Dropdown link item

**`nav-link.blade.php`**
- Navigation link component

**`responsive-nav-link.blade.php`**
- Responsive navigation link (mobile)

### `purchase-orders/`, `delivery-orders/`, `quotations/`, `stock-movements/` - Print Previews

**`purchase-orders/preview.blade.php`**
- Purchase order print preview
- Company header, supplier info, items table, totals
- Formatted for PDF generation

**`delivery-orders/preview.blade.php`**
- Delivery order print preview
- Company header, customer info, items table, totals

**`quotations/preview.blade.php`**
- Quotation print preview
- Company header, customer info, items table, totals

**`stock-movements/preview.blade.php`**
- Stock movement print preview
- Movement details, items table

### `profile/` - Profile Views

**`edit.blade.php`**
- Profile edit page
- Uses Livewire Profile component

**`partials/update-profile-information-form.blade.php`**
- Profile information update form

**`partials/update-password-form.blade.php`**
- Password update form

**`partials/delete-user-form.blade.php`**
- Delete user account form

### `reports/` - Report Views

**`items.blade.php`**
- Items report view

**`transactions.blade.php`**
- Transactions report view

### Other Views

**`dashboard.blade.php`**
- Main dashboard page wrapper
- Loads Dashboard Livewire component

**`import.blade.php`**
- Excel import form
- Select import type (Items/Customers/Suppliers)
- Select database (UPS/URS/UCS)
- File upload

**`welcome.blade.php`**
- Welcome page (if any)

---

## 📁 `database/` Directory

### `migrations/` - Database Migrations

**49 migration files** that create and modify database tables:

**Core Tables:**
- `0001_01_01_000000_create_users_table.php` - Users table
- `0001_01_01_000001_create_cache_table.php` - Cache table
- `2024_03_19_000000_create_customers_table.php` - Customers table
- `2024_03_19_000000_create_suppliers_table.php` - Suppliers table
- `2024_03_19_000001_create_customer_snapshots_table.php` - Customer snapshots
- `2024_03_19_000002_create_supplier_snapshots_table.php` - Supplier snapshots
- `2024_10_03_221221_create_categories_table.php` - Categories table
- `2024_10_03_221240_create_brands_table.php` - Brands table
- `2024_10_04_091625_create_warehouses_table.php` - Warehouses table
- `2024_10_04_100356_create_locations_table.php` - Locations table
- `2024_10_07_085918_create_items_table.php` - Items table
- `2024_10_09_144946_create_restock_lists_table.php` - Restock lists table
- `2024_10_15_213011_create_permission_tables.php` - Spatie permissions tables
- `2024_10_23_102110_create_delivery_order_items_table.php` - DO items
- `2024_10_24_000000_add_more_description_to_delivery_order_items.php` - DO items updates
- `2024_10_25_084205_create_purchase_order_items_table.php` - PO items
- `2024_10_26_000000_add_more_description_to_purchase_order_items.php` - PO items updates
- `2024_12_16_123330_create_company_profiles_table.php` - Company profiles
- `2025_01_01_000001_add_salesman_id_to_customers_table.php` - Salesman assignment
- `2025_01_06_142832_create_batch_tracking_table.php` - Batch tracking
- `2025_01_06_160749_create_transactions_table.php` - Transactions
- `2025_04_27_153910_create_ibc_chemicals_table.php` - IBC chemicals
- `2025_04_27_153927_create_loading_unloadings_table.php` - Loading/unloading
- `2025_04_27_153954_create_incoming_quality_controls_table.php` - IQC
- `2025_05_01_000000_add_memo_to_items_table.php` - Item memo field
- `2025_08_27_010218_create_stock_movements_table.php` - Stock movements
- `2025_08_27_010227_create_stock_movement_items_table.php` - Stock movement items
- `2025_09_03_000001_add_tax_to_purchase_orders.php` - Tax fields
- `2025_09_03_000002_add_status_to_delivery_orders.php` - DO status
- `2025_09_03_000003_backfill_delivery_orders_status.php` - DO status backfill
- `2025_09_04_031838_add_pricing_tier_to_delivery_order_items_table.php` - Pricing tier
- `2025_09_05_034645_add_printed_column_to_purchase_orders_and_delivery_orders.php` - Printed flag
- `2025_09_08_022034_remove_delivery_date_from_delivery_orders_table.php` - Remove delivery date
- `2025_09_08_022132_remove_remark_from_order_items.php` - Remove remark
- `2025_09_09_000001_add_custom_item_name_to_order_items.php` - Custom item name
- `2025_09_18_030711_create_quotations_table.php` - Quotations
- `2025_09_18_030714_create_quotation_items_table.php` - Quotation items
- `2025_09_18_030923_update_quotations_table_remove_cust_po_and_set_status_default.php` - Quotation updates
- `2025_09_18_033848_update_quotations_table_change_printed_to_char.php` - Quotation printed field
- Plus various other migrations for updates and modifications

### `seeders/` - Database Seeders

**`DatabaseSeeder.php`**
- Main database seeder
- Seeds:
  - 3 sample customers
  - 3 sample suppliers
  - All categories (predefined list)
  - All brands (predefined list)
  - Default warehouse and location
  - Admin user (admin@example.com)
  - Regular user (user@example.com)
  - 3 salesperson users
  - All permissions (18 permissions)
  - Roles (Admin, User, Salesperson)
  - Role-permission assignments
  - Company profiles (varies per database: UPS/URS/UCS)
  - Initial purchase order (PO0000000000)
- Idempotent (can run multiple times safely)

---

## 📁 `config/` Directory

**`app.php`**
- Application configuration
- App name, environment, debug mode, timezone, locale

**`database.php`**
- Database configuration
- **CRITICAL**: Defines three database connections (UPS, URS, UCS)
- Connection settings for each database
- SSL options for Aiven Cloud

**`auth.php`**
- Authentication configuration
- Guards, providers, password reset

**`permission.php`**
- Spatie permissions configuration
- Cache settings, table names

**`livewire.php`**
- Livewire configuration
- Component paths, asset URLs

**`cache.php`**
- Cache configuration
- Cache drivers, stores

**`filesystems.php`**
- File storage configuration
- Local, public, S3 storage

**`logging.php`**
- Logging configuration
- Log channels, levels

**`mail.php`**
- Mail configuration
- SMTP settings, mail drivers

**`queue.php`**
- Queue configuration
- Queue connections, workers

**`session.php`**
- Session configuration
- Session driver, lifetime, cookie settings

**`services.php`**
- Third-party service configuration
- Pusher, AWS, etc.

---

## 📁 `public/` Directory

**`index.php`**
- Laravel entry point
- All requests go through this file
- Bootstraps Laravel application

**`favicon.ico`**
- Website favicon

**`robots.txt`**
- Search engine robots file

**`build/`**
- Compiled frontend assets (CSS, JS)
- Generated by `npm run build`

**`storage/`**
- Symbolic link to `storage/app/public`
- Public file storage

**`images/company-logo.png`**
- Company logo image

---

## 📁 `storage/` Directory

**`app/public/`**
- Public file storage
- Item images, uploaded files

**`app/private/`**
- Private file storage

**`framework/cache/`**
- Application cache files

**`framework/sessions/`**
- Session files

**`framework/views/`**
- Compiled Blade templates

**`logs/laravel.log`**
- Application log file
- All errors, warnings, info logs

---

## 📁 `resources/` Directory

### `css/`

**`app.css`**
- Main application stylesheet
- Tailwind directives
- Custom styles

**`global.css`**
- Global CSS styles
- Base styles, utilities

### `js/`

**`app.js`**
- Main JavaScript file
- Alpine.js initialization
- Custom JavaScript

**`bootstrap.js`**
- JavaScript bootstrap file
- Axios configuration
- Laravel Echo setup (for Pusher)

---

## 📁 `tests/` Directory

**`TestCase.php`**
- Base test case class
- All tests extend this

**`Feature/`**
- Integration/feature tests
- Tests full features end-to-end

**`Unit/`**
- Unit tests
- Tests individual classes/methods

---

## Summary

This system has:
- **27 Models** - Database entities
- **30+ Livewire Components** - Interactive UI components
- **4 Controllers** - Traditional controllers
- **4 Middleware** - Request processing
- **3 Import Classes** - Excel import
- **2 Export Classes** - Excel export
- **49 Migrations** - Database schema
- **71+ Views** - Blade templates
- **3 Route Files** - Application routes

The architecture follows Laravel best practices with:
- **MVC pattern** (Models, Views, Controllers)
- **Livewire** for interactive components
- **Multi-tenant** database switching
- **Role-based permissions** (Spatie)
- **Transaction logging** for audit trails
- **Batch tracking** with FIFO logic

Each file has a specific purpose and works together to create a comprehensive inventory management system.






