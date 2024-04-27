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
        Schema::create('prize_lists', function (Blueprint $table) {
            $table->id();
            $table->string('round_id');
            $table->text('win_pos_list')->nullable();
            $table->integer('index');
            $table->integer('level');
            $table->integer('item_type');
            $table->integer('rate');
            $table->text('win_item_list');
            $table->integer('multi_time');
            $table->integer('win');
            $table->string('level_s');
            $table->string('item_type_s');
            $table->string('rate_s');
            $table->string('multi_time_s');
            $table->string('win_s');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_lists');
    }
};
