<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Set status to 'Completed' for legacy rows where it's null
        DB::table('delivery_orders')->whereNull('status')->update(['status' => 'Completed']);
    }

    public function down(): void
    {
        // No-op: do not revert data back to null
    }
};


