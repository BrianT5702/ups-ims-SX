<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'cust_po')) {
                $table->dropColumn('cust_po');
            }
            // Ensure default is "Save to Draft"
            $table->string('status')->default('Save to Draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Re-add cust_po as nullable (can't know prior state precisely)
            if (!Schema::hasColumn('quotations', 'cust_po')) {
                $table->string('cust_po')->nullable()->after('date');
            }
            // Revert default to Draft if needed
            $table->string('status')->default('Draft')->change();
        });
    }
};
