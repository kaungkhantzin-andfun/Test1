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
        Schema::table('toasks', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['astrologers_id']);
            
            // Add the new foreign key referencing astrologers table
            $table->foreign('astrologers_id')
                  ->references('id')
                  ->on('astrologers')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toasks', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['astrologers_id']);
            
            // Restore the original foreign key referencing users table
            $table->foreign('astrologers_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
