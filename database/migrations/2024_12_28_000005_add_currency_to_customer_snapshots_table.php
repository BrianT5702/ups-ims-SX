<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyToCustomerSnapshotsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('customer_snapshots', function (Blueprint $table) {
            $table->string('currency', 3)->default('MYR')->after('pricing_tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('customer_snapshots', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
}
