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
        Schema::create('tiger_history', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->string('order_id');
            $table->string('round_id');
            $table->string('win_s');
            $table->string('bet_s');
            $table->integer('free_times');
            $table->integer('free_mode_type');
            $table->bigInteger('create_timestamp');
            $table->boolean('free');
            $table->integer('normal_round_times');
            $table->integer('free_round_times');
            $table->integer('bet');
            $table->integer('win');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiger_history');
    }
};
