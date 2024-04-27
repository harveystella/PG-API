<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TigerRoundList extends Model
{
    use HasFactory;

    protected $table = 'tiger_round_lists';

    protected $fillable = [
        'order_id',
        'round_id',
        'bet_s',
        'prize_s',
        'free_mode_type',
        'player_win_lose_s',
        'balance_s',
        'balance_before_score_s',
        'create_timestamp',
        'bet',
        'prize',
        'player_win_lose',
        'balance',
        'balance_before_score',
        'free',
        'free_total_times',
        'free_remain_times',
        'free_game_total_win',
        'bet_size',
        'basic_bet',
        'bet_multiple',
        'round_list_count',
        'item_type_list'
    ];

    protected $casts = [
        'free' => 'boolean',
    ];
}
