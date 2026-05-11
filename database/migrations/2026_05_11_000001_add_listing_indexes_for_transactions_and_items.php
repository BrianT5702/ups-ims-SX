<?php

use Closure;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Speed up transaction log / reports on every company database connection.
     * Same connection list as other multi-DB migrations (e.g. 2026_03_05_132129).
     */
    public function up(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}")) {
                continue;
            }

            $schema = Schema::connection($connection);

            if ($schema->hasTable('transactions')) {
                $this->addIndexUnlessExists($connection, 'transactions', function (Blueprint $table) {
                    $table->index(['item_id', 'created_at'], 'transactions_item_id_created_at_index');
                });
                $this->addIndexUnlessExists($connection, 'transactions', function (Blueprint $table) {
                    $table->index('created_at', 'transactions_created_at_index');
                });
            }

            if ($schema->hasTable('items') && $schema->hasColumn('items', 'group_id')) {
                $this->addIndexUnlessExists($connection, 'items', function (Blueprint $table) {
                    $table->index('group_id', 'items_group_id_index');
                });
            }
        }
    }

    public function down(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}")) {
                continue;
            }

            $schema = Schema::connection($connection);

            if ($schema->hasTable('transactions')) {
                $this->dropIndexIfExists($connection, 'transactions', 'transactions_item_id_created_at_index');
                $this->dropIndexIfExists($connection, 'transactions', 'transactions_created_at_index');
            }

            if ($schema->hasTable('items')) {
                $this->dropIndexIfExists($connection, 'items', 'items_group_id_index');
            }
        }
    }

    private function addIndexUnlessExists(string $connection, string $table, Closure $callback): void
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
