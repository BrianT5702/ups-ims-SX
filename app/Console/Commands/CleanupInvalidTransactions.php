<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class CleanupInvalidTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:cleanup 
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--item-id= : Only clean transactions for a specific item ID}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up invalid transaction records (NULL source_doc_num and transaction_qty = 0)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $itemId = $this->option('item-id');
        $force = $this->option('force');

        $this->info('=== Transaction Cleanup Script ===');
        $this->newLine();

        // Build query to find invalid transactions
        $query = Transaction::whereNull('source_doc_num')
            ->where('transaction_qty', 0);

        if ($itemId) {
            $query->where('item_id', $itemId);
            $this->info("Filtering for item_id: {$itemId}");
        }

        // Get statistics before deletion
        $invalidTransactions = $query->get();
        $totalCount = $invalidTransactions->count();

        if ($totalCount === 0) {
            $this->info('✅ No invalid transactions found. Database is clean!');
            return 0;
        }

        // Group by item_id for better reporting
        $groupedByItem = $invalidTransactions->groupBy('item_id');
        
        $this->warn("Found {$totalCount} invalid transaction(s) to clean up:");
        $this->newLine();

        // Display breakdown by item
        $tableData = [];
        foreach ($groupedByItem as $itemId => $transactions) {
            $item = Item::find($itemId);
            $itemCode = $item ? $item->item_code : 'Unknown';
            $itemName = $item ? $item->item_name : 'Unknown';
            
            $tableData[] = [
                'item_id' => $itemId,
                'item_code' => $itemCode,
                'item_name' => $itemName,
                'count' => $transactions->count(),
                'date_range' => $transactions->min('created_at') . ' to ' . $transactions->max('created_at')
            ];
        }

        $this->table(
            ['Item ID', 'Item Code', 'Item Name', 'Count', 'Date Range'],
            $tableData
        );

        $this->newLine();

        // Show sample records
        $this->info('Sample of records to be deleted:');
        $sample = $invalidTransactions->take(5);
        $sampleData = [];
        foreach ($sample as $txn) {
            $item = Item::find($txn->item_id);
            $sampleData[] = [
                'id' => $txn->id,
                'item_id' => $txn->item_id,
                'item_code' => $item ? $item->item_code : 'Unknown',
                'qty_before' => $txn->qty_before,
                'qty_after' => $txn->qty_after,
                'transaction_qty' => $txn->transaction_qty,
                'source_type' => $txn->source_type,
                'created_at' => $txn->created_at->format('Y-m-d H:i:s')
            ];
        }

        $this->table(
            ['ID', 'Item ID', 'Item Code', 'Qty Before', 'Qty After', 'Txn Qty', 'Source Type', 'Created At'],
            $sampleData
        );

        $this->newLine();

        // Safety check: Verify these transactions don't affect stock
        $this->info('🔍 Safety Verification:');
        $this->line('Checking if these transactions affect stock calculations...');
        
        $hasNonZeroQty = $invalidTransactions->contains(function ($txn) {
            return $txn->transaction_qty != 0;
        });

        if ($hasNonZeroQty) {
            $this->error('⚠️  WARNING: Some transactions have non-zero transaction_qty!');
            $this->error('This should not happen. Please review before deleting.');
            return 1;
        }

        $this->info('✅ All transactions have transaction_qty = 0 (safe to delete)');
        $this->info('✅ All transactions have NULL source_doc_num (invalid audit trail)');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No records were deleted');
            $this->info("Would delete {$totalCount} transaction(s)");
            $this->info('Run without --dry-run to perform actual deletion');
            return 0;
        }

        // Confirmation prompt
        if (!$force) {
            if (!$this->confirm("Are you sure you want to delete {$totalCount} invalid transaction(s)?", false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Perform deletion
        $this->info('🗑️  Deleting invalid transactions...');
        
        try {
            DB::beginTransaction();
            
            $deletedCount = $query->delete();
            
            DB::commit();
            
            $this->newLine();
            $this->info("✅ Successfully deleted {$deletedCount} invalid transaction(s)");
            
            // Show summary by item
            $this->newLine();
            $this->info('Summary by item:');
            foreach ($tableData as $row) {
                $this->line("  - Item {$row['item_code']} ({$row['item_name']}): {$row['count']} deleted");
            }
            
            $this->newLine();
            $this->info('✨ Cleanup completed successfully!');
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error during deletion: ' . $e->getMessage());
            $this->error('Transaction rolled back. No changes were made.');
            return 1;
        }
    }
}




