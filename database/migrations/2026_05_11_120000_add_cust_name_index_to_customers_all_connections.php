<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Speed up customer name ordering / filtering in DO (and similar) search.
     */
    public function up(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}") || !Schema::connection($connection)->hasTable('customers')) {
                continue;
            }

            $schema = Schema::connection($connection);
            if (!$schema->hasColumn('customers', 'cust_name')) {
                continue;
            }

            $this->addIndexUnlessExists($connection, 'customers', function (Blueprint $table) {
                $table->index('cust_name', 'customers_cust_name_index');
            });
        }
    }

    public function down(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}") || !Schema::connection($connection)->hasTable('customers')) {
                continue;
            }

            $this->dropIndexIfExists($connection, 'customers', 'customers_cust_name_index');
        }
    }

    private function addIndexUnlessExists(string $connection, string $table, \Closure $callback): void
    {
        try {
            Schema::connection($connection)->table($table, $callback);
        } catch (\Throwable $e) {
            $msg = strtolower($e->getMessage());
            if (str_contains($msg, 'duplicate') || str_contains($msg, 'already exists')) {
                return;
            }
            throw $e;
        }
    }

    private function dropIndexIfExists(string $connection, string $table, string $indexName): void
    {
        try {
            Schema::connection($connection)->table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        } catch (\Throwable $e) {
            $msg = strtolower($e->getMessage());
            if (str_contains($msg, "doesn't exist") || str_contains($msg, 'check that column/key exists') || str_contains($msg, 'unknown key')) {
                return;
            }
            throw $e;
        }
    }
};
