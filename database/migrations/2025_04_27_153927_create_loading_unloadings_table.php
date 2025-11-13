<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loading_unloadings', function (Blueprint $table) {
            $table->id();
            $table->string('tank_id')->nullable();
            $table->string('che_code')->nullable();
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('stop_time')->nullable();
            $table->string('che_before')->nullable();
            $table->string('che_after')->nullable();
            $table->boolean('isFollowDO')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Foreign key
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loading_unloadings');
    }
};
