<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('ref_num')->nullable();
            $table->string('po_num')->unique();
            $table->foreignId('sup_id')->constrained('suppliers');
            $table->foreignId('user_id')->constrained('users');
            $table->date('date');
            $table->text('remark')->nullable();
            $table->decimal('final_total_price', 10, 2)->nullable();
            $table->string('status')->default('Pending Approval');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
}
