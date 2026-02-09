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
        // Create stealth_settings table in UPS database (where users are stored)
        Schema::connection('ups')->create('stealth_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->timestamps();
            
            $table->foreign('changed_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('ups')->dropIfExists('stealth_settings');
    }
};
