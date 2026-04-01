<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeRefNumNotUniqueInDeliveryOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * This will drop the unique constraint on ref_num so that
     * multiple delivery orders can share the same reference number.
     */
    public function up()
    {
        if (! Schema::hasTable('delivery_orders')) {
            return;
        }

        // Avoid MySQL error 1091 when the index was already removed or never used this name.
        $connection = Schema::getConnection();
        if ($connection->getDriverName() === 'mysql') {
            $dbName = $connection->getDatabaseName();
            $exists = DB::selectOne(
                'SELECT COUNT(*) AS c FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$dbName, 'delivery_orders', 'delivery_orders_ref_num_unique']
            );
            if ((int) ($exists->c ?? 0) === 0) {
                return;
            }
        }

        Schema::table('delivery_orders', function (Blueprint $table) {
            // The index name comes from the original migration:
            // $table->string('ref_num')->unique()->nullable();
            // which creates 'delivery_orders_ref_num_unique'
            $table->dropUnique('delivery_orders_ref_num_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * This will restore the unique constraint on ref_num.
     */
    public function down()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->unique('ref_num');
        });
    }
}

