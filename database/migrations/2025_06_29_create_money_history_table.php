<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('money_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('money', 15, 2);
            $table->string('description')->nullable();
            $table->string('type'); // e.g., 'deposit', 'withdrawal', 'transfer', etc.
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('money_history');
    }
};
