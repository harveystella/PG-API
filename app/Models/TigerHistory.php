<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TigerHistory extends Model
{
    use HasFactory;

    protected $table = 'tiger_history';

    protected $fillable = [
        'create_time',
        'order_id',
        'round_id',
        'win_s',
        'bet_s',
        'free_times',
        'free_mode_type',
        'create_timestamp',
        'free',
        'normal_round_times',
        'free_round_times',
        'bet',
        'win',
        'token',
    ];

}
