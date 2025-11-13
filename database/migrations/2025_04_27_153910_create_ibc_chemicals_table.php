<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ibc_chemicals', function (Blueprint $table) {
            $table->id();
            $table->string('do_num')->nullable();
            $table->string('batch_no')->nullable();
            $table->date('date')->nullable();
            $table->string('che_code')->nullable();
            $table->integer('qty')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Foreign key
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ibc_chemicals');
    }
};
