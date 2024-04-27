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
        Schema::create('tiger_bet_list', function (Blueprint $table) {
            $table->id();
            $table->integer('bet_size');
            $table->integer('bet_multiple');
            $table->integer('basic_bet');
            $table->integer('total_bet');
            $table->string('bet_size_s');
            $table->string('total_bet_s');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiger_bet_list');
    }
};
