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
        Schema::create('suppliers', function (Blueprint $table) {
            // $table->id();
            // $table->string('sup_name')->unique();
            // $table->string('contact_person');
            // $table->string('address_line1');
            // $table->string('address_line2');
            // $table->string('address_line3')->nullable();
            // $table->string('address_line4')->nullable();
            // $table->string('phone_num1');
            // $table->string('phone_num2')->nullable();
            // $table->string('fax_num')->nullable();
            // $table->string('email');
            // $table->timestamps();

            $table->id();
            $table->string('account')->unique();
            $table->string('sup_name');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('address_line3')->nullable();
            $table->string('address_line4')->nullable();
            $table->string('phone_num')->nullable();
            $table->string('fax_num')->nullable();
            $table->string('email')->nullable();
            $table->string('area')->nullable();
            $table->enum('term', ['C.O.D', 'CASH', 'NET 30 DAY', '30 DAYS', '60 DAYS'])->nullable();
            $table->string('business_registration_no')->nullable();
            $table->string('gst_registration_no')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('suppliers');
        Schema::enableForeignKeyConstraints();
    }
};
