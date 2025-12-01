# Quotation View - What's Displayed After Clicking a Quotation

## Overview
When you click on a quotation from the quotation list, you are taken to the **View Quotation** page (`quotations.view` route). This page displays all the quotation details in **read-only mode**.

## Sections Displayed

### 1. Header Section
- **Title**: "View Quotation"
- **Navigation**: Back button, Edit button, Preview button

### 2. Customer Information (Read-Only)
- **Customer Name**: Displayed prominently
- **Currency**: Customer's currency (e.g., RM, USD)
- **Address**: 
  - Address Line 1
  - Address Line 2
  - Address Line 3 (if available)
  - Address Line 4 (if available)
- **Contact Information**:
  - Phone number
  - Fax number (if available)
  - Email (if available)

### 3. Quotation Details (Read-Only)
- **Date**: Quotation date (disabled input field)
- **Quotation Number**: Unique quotation identifier (disabled input field)
- **Reference Number**: Optional reference number (disabled input field)
- **Remark**: Any additional notes/remarks (disabled textarea)
- **Salesperson**: Assigned salesperson (disabled dropdown)
- **Created By**: Name of the user who created the quotation

### 4. Items Table (Read-Only)
A table showing all items in the quotation with the following columns:

| Column | Description |
|--------|-------------|
| **#** | Sequential number (1, 2, 3, ...) |
| **Item Code** | Stock/item code |
| **Item Name** | Item description/name (may include custom name if edited) |
| **Qty on Hand** | Current stock quantity available |
| **Order Quantity** | Quantity ordered in this quotation (disabled input) |
| **Unit Price** | Price per unit (disabled dropdown showing pricing tier) |
| **Amount** | Total line amount (Quantity × Unit Price) (disabled input) |

**Additional Item Information:**
- If an item has **additional description** (`more_description`), it appears below the item name as bullet points
- If an item has a **custom name** (edited for this quotation only), it shows the custom name instead of the original item name

### 5. Pricing Information
For each item, the unit price dropdown shows:
- **Custom Price**: Manually entered price
- **Cash Price**: Standard cash price
- **Term Price**: Standard term price
- **Customer Price**: Customer-specific price
- **Cost**: Item cost price
- **Previous Price**: Last quoted price for this customer (if available, with date)

*Note: In view mode, all pricing options are disabled and cannot be changed.*

### 6. Totals Section
- **Total Amount**: Sum of all line items
- Displayed in the customer's currency (e.g., "RM 1,234.56")

### 7. Action Buttons
At the bottom of the page:
- **Back**: Returns to the quotation list
- **Edit**: Navigates to edit mode (if you have permission)
- **Preview**: Opens the print preview page showing how the quotation will look when printed

## Key Features in View Mode

### Read-Only Fields
- All input fields are **disabled**
- No items can be added or removed
- Quantities and prices cannot be modified
- Customer cannot be changed

### Additional Description Display
- If an item has additional description text, it appears below the item name
- Displayed as bullet points
- Only visible in view mode (not editable)

### Custom Item Names
- If an item name was customized for this quotation, the custom name is shown
- Original item name is preserved in the database

## Data Source
The view displays data from:
- **Quotation** model: Main quotation record
- **QuotationItem** model: Individual line items
- **CustomerSnapshot** model: Snapshot of customer data at time of quotation creation
- **Item** model: Item details (code, name, prices, stock)

## Navigation Flow
1. **Quotation List** → Click on any quotation row
2. **View Quotation** → Shows all details in read-only mode
3. **Edit Button** → Navigate to edit mode (if permitted)
4. **Preview Button** → Opens print preview page
5. **Back Button** → Returns to quotation list

## Technical Details
- **Route**: `quotations.view` (defined in `routes/web.php`)
- **Component**: `QuotationForm` Livewire component
- **View Mode Flag**: `$isView = true` when on view route
- **Template**: `resources/views/livewire/quotation-form.blade.php`





