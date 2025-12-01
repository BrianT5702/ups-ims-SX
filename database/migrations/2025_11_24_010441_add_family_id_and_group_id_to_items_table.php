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
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('family_id')->nullable()->after('brand_id')->constrained('families')->onDelete('set null');
            $table->foreignId('group_id')->nullable()->after('family_id')->constrained('groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['family_id']);
            $table->dropForeign(['group_id']);
            $table->dropColumn(['family_id', 'group_id']);
        });
    }
};
