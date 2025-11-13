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
        if (Schema::hasTable('quotations')) {
            return; // Table already exists
        }
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('ref_num')->unique()->nullable();
            $table->foreignId('cust_id')->constrained('customers');
            $table->foreignId('salesman_id')->constrained('users');
            $table->foreignId('user_id')->constrained('users');
            $table->date('date');
            $table->string('quotation_num')->unique();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->text('remark')->nullable();
            $table->string('status')->default('Save to Draft');
            $table->boolean('printed')->default(false);
            $table->foreignId('customer_snapshot_id')->nullable()->constrained('customer_snapshots');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
