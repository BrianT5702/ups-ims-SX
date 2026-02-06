<?php
/**
 * Standalone Transaction Cleanup Script
 * 
 * This script cleans up invalid transaction records:
 * - source_doc_num IS NULL
 * - transaction_qty = 0
 * 
 * Usage:
 *   php cleanup-transactions.php [--dry-run] [--item-id=16] [--force]
 * 
 * Options:
 *   --dry-run    : Show what would be deleted without actually deleting
 *   --item-id=N  : Only clean transactions for a specific item ID
 *   --force      : Skip confirmation prompt
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

// Parse command line arguments
$options = [
    'dry-run' => false,
    'item-id' => null,
    'force' => false
];

foreach ($argv as $arg) {
    if ($arg === '--dry-run') {
        $options['dry-run'] = true;
    } elseif (strpos($arg, '--item-id=') === 0) {
        $options['item-id'] = (int) substr($arg, 10);
    } elseif ($arg === '--force') {
        $options['force'] = true;
    }
}

echo "=== Transaction Cleanup Script ===\n\n";

// Build query to find invalid transactions
$query = Transaction::whereNull('source_doc_num')
    ->where('transaction_qty', 0);

if ($options['item-id']) {
    $query->where('item_id', $options['item-id']);
    echo "Filtering for item_id: {$options['item-id']}\n";
}

// Get statistics before deletion
$invalidTransactions = $query->get();
$totalCount = $invalidTransactions->count();

if ($totalCount === 0) {
    echo "✅ No invalid transactions found. Database is clean!\n";
    exit(0);
}

// Group by item_id for better reporting
$groupedByItem = $invalidTransactions->groupBy('item_id');

echo "⚠️  Found {$totalCount} invalid transaction(s) to clean up:\n\n";

// Display breakdown by item
echo "Breakdown by item:\n";
echo str_repeat('-', 80) . "\n";
printf("%-10s %-20s %-30s %-10s %-20s\n", "Item ID", "Item Code", "Item Name", "Count", "Date Range");
echo str_repeat('-', 80) . "\n";

$tableData = [];
foreach ($groupedByItem as $itemId => $transactions) {
    $item = Item::find($itemId);
    $itemCode = $item ? $item->item_code : 'Unknown';
    $itemName = $item ? substr($item->item_name, 0, 28) : 'Unknown';
    
    $dateRange = $transactions->min('created_at') . ' to ' . $transactions->max('created_at');
    printf("%-10s %-20s %-30s %-10s %-20s\n", 
        $itemId, 
        substr($itemCode, 0, 18), 
        substr($itemName, 0, 28),
        $transactions->count(),
        substr($dateRange, 0, 18)
    );
    
    $tableData[] = [
        'item_id' => $itemId,
        'item_code' => $itemCode,
        'item_name' => $itemName,
        'count' => $transactions->count(),
        'date_range' => $dateRange
    ];
}
echo str_repeat('-', 80) . "\n\n";

// Show sample records
echo "Sample of records to be deleted (first 5):\n";
echo str_repeat('-', 100) . "\n";
printf("%-8s %-10s %-20s %-12s %-12s %-12s %-20s %-20s\n", 
    "ID", "Item ID", "Item Code", "Qty Before", "Qty After", "Txn Qty", "Source Type", "Created At");
echo str_repeat('-', 100) . "\n";

$sample = $invalidTransactions->take(5);
foreach ($sample as $txn) {
    $item = Item::find($txn->item_id);
    printf("%-8s %-10s %-20s %-12s %-12s %-12s %-20s %-20s\n",
        $txn->id,
        $txn->item_id,
        substr($item ? $item->item_code : 'Unknown', 0, 18),
        $txn->qty_before,
        $txn->qty_after,
        $txn->transaction_qty,
        substr($txn->source_type, 0, 18),
        $txn->created_at->format('Y-m-d H:i:s')
    );
}
echo str_repeat('-', 100) . "\n\n";

// Safety check: Verify these transactions don't affect stock
echo "🔍 Safety Verification:\n";
echo "Checking if these transactions affect stock calculations...\n";

$hasNonZeroQty = $invalidTransactions->contains(function ($txn) {
    return $txn->transaction_qty != 0;
});

if ($hasNonZeroQty) {
    echo "❌ ERROR: Some transactions have non-zero transaction_qty!\n";
    echo "This should not happen. Please review before deleting.\n";
    exit(1);
}

echo "✅ All transactions have transaction_qty = 0 (safe to delete)\n";
echo "✅ All transactions have NULL source_doc_num (invalid audit trail)\n\n";

if ($options['dry-run']) {
    echo "🔍 DRY RUN MODE - No records were deleted\n";
    echo "Would delete {$totalCount} transaction(s)\n";
    echo "Run without --dry-run to perform actual deletion\n";
    exit(0);
}

// Confirmation prompt
if (!$options['force']) {
    echo "⚠️  WARNING: This will permanently delete {$totalCount} transaction(s)\n";
    echo "Type 'yes' to continue, or anything else to cancel: ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) !== 'yes') {
        echo "Operation cancelled.\n";
        exit(0);
    }
}

// Perform deletion
echo "\n🗑️  Deleting invalid transactions...\n";

try {
    DB::beginTransaction();
    
    $deletedCount = $query->delete();
    
    DB::commit();
    
    echo "\n✅ Successfully deleted {$deletedCount} invalid transaction(s)\n\n";
    
    // Show summary by item
    echo "Summary by item:\n";
    foreach ($tableData as $row) {
        echo "  - Item {$row['item_code']} ({$row['item_name']}): {$row['count']} deleted\n";
    }
    
    echo "\n✨ Cleanup completed successfully!\n";
    
    exit(0);
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error during deletion: " . $e->getMessage() . "\n";
    echo "Transaction rolled back. No changes were made.\n";
    exit(1);
}




