<?php

namespace App\Http\Controllers\API\spinData\FortuneTiger;

class rtp80
{
    /**
     * @return array
     */
    public static function getSymbols(): array
    {
        return [
            1 => ['identifier' => 'WILD', 'multiplier' => 5, 'probability' => 0.10],
            2 => ['identifier' => 'Gold bar', 'multiplier' => 2, 'probability' => 0.03],
            3 => ['identifier' => 'Amulet', 'multiplier' => 0.5, 'probability' => 0.08],
            4 => ['identifier' => 'Gold pouch', 'multiplier' => 0.2, 'probability' => 0.10],
            5 => ['identifier' => 'Red packet', 'multiplier' => 0.16, 'probability' => 0.17],
            6 => ['identifier' => 'Fire crackers', 'multiplier' => 0.1, 'probability' => 0.24],
            7 => ['identifier' => 'Orange', 'multiplier' => 0.06, 'probability' => 0.28]
        ];
    }
}
