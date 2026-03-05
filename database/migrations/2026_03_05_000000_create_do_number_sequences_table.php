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
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}")) {
                continue;
            }

            if (Schema::connection($connection)->hasTable('do_number_sequences')) {
                continue;
            }

            Schema::connection($connection)->create('do_number_sequences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('next_number')->default(1);
                $table->timestamps();
            });

            // Seed with configured start number
            $start = config("do.start.{$connection}", 1);
            \DB::connection($connection)->table('do_number_sequences')->insert([
                'next_number' => $start,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (config("database.connections.{$connection}")) {
                Schema::connection($connection)->dropIfExists('do_number_sequences');
            }
        }
    }
};
