<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrizeList extends Model
{
    use HasFactory;

    protected $table = 'prize_lists';

    protected $fillable = [
        'win_pos_list', 'index', 'level', 'item_type', 'rate',
        'win_item_list', 'multi_time', 'win', 'level_s', 'item_type_s',
        'rate_s', 'multi_time_s', 'win_s', 'round_id', 'order_id'
    ];

    public $timestamps = false; // Disable automatic timestamps
}
