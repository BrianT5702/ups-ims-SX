<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            try {
                if (! Schema::connection($connection)->hasTable('quotation_items')) {
                    continue;
                }

                if (! Schema::connection($connection)->hasColumn('quotation_items', 'sequence_hidden')) {
                    Schema::connection($connection)->table('quotation_items', function (Blueprint $table) {
                        $table->boolean('sequence_hidden')->default(false)->after('row_index');
                    });
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    public function down(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            try {
                if (! Schema::connection($connection)->hasTable('quotation_items')) {
                    continue;
                }

                if (Schema::connection($connection)->hasColumn('quotation_items', 'sequence_hidden')) {
                    Schema::connection($connection)->table('quotation_items', function (Blueprint $table) {
                        $table->dropColumn('sequence_hidden');
                    });
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }
};
