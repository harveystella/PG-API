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
        Schema::create('tiger_round_lists', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->string('round_id');
            $table->string('bet_s');
            $table->string('prize_s');
            $table->integer('free_mode_type');
            $table->string('player_win_lose_s');
            $table->string('balance_s');
            $table->string('balance_before_score_s');
            $table->bigInteger('create_timestamp');
            $table->integer('bet');
            $table->integer('prize');
            $table->integer('player_win_lose');
            $table->integer('balance');
            $table->integer('balance_before_score');
            $table->boolean('free');
            $table->integer('free_total_times');
            $table->integer('free_remain_times');
            $table->bigInteger('free_game_total_win');
            $table->integer('bet_size');
            $table->integer('basic_bet');
            $table->integer('bet_multiple');
            $table->integer('round_list_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiger_round_lists');
    }
};
