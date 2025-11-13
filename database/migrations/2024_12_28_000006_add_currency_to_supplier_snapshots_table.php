<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyToSupplierSnapshotsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('supplier_snapshots', function (Blueprint $table) {
            $table->string('currency', 3)->default('MYR')->after('gst_registration_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('supplier_snapshots', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
}
