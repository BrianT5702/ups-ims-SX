<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('qty_on_hand');
            $table->integer('qty_before');
            $table->integer('qty_after');
            $table->integer('transaction_qty');
            $table->string('transaction_type');
            $table->string('source_type');
            $table->string('source_doc_num')->nullable;
            $table->foreignId('batch_id')->nullable()->constrained('batch_trackings')->onDelete('set null');
            $table->timestamps();

            // Foreign keys
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
