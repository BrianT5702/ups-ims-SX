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
        Schema::create('customers', function (Blueprint $table) {
            // $table->id();
            // $table->string('cust_name')->unique();
            // $table->string('contact_person');
            // $table->string('address_line1');
            // $table->string('address_line2');
            // $table->string('address_line3')->nullable();
            // $table->string('address_line4')->nullable();
            // $table->string('phone_num1');
            // $table->string('phone_num2')->nullable();
            // $table->string('fax_num')->nullable();
            // $table->string('email');
            // $table->string('pricing_tier');
            // $table->timestamps();

                $table->id();
                $table->string('account')->unique();
                $table->string('cust_name');
                $table->string('address_line1');
                $table->string('address_line2')->nullable();
                $table->string('address_line3')->nullable();
                $table->string('address_line4')->nullable();
                $table->string('phone_num')->nullable();
                $table->string('fax_num')->nullable();
                $table->string('email')->nullable();
                $table->string('area')->nullable();
                $table->enum('term', ['C.O.D', '30 DAYS', 'CASH'])->nullable();
                $table->string('business_registration_no')->nullable();
                $table->string('gst_registration_no')->nullable();
                $table->string('pricing_tier')->nullable();
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
