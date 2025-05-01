<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('email_verified_at');
            $table->string('phone')->unique()->after('name');
        });

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->string('phone')->primary();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->unique()->after('name');
            $table->timestamp('email_verified_at')->nullable();
            $table->dropColumn('phone');
        });

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->string('email')->primary();
        });
    }
};
