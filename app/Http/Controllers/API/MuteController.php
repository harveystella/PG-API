<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Http\Controllers\Controller;

class MuteController extends Controller
{
    public function mute(Request $request) {
        $mute = $request->input('mute');
        $token = $request->input('token');

        $player = Player::where('token', $token)->first();

        $player->mute = $mute;
        $player->save();

        $response = [
            "error_code" => 0,
            "data" => [
                "player_info" => [
                    "id" => $player->id,
                    "balance" => $player->balance,
                    "account" => $player->account,
                    "nickname" => $player->nickname,
                    "type" => $player->type,
                    "mute" => $player->mute,
                ],
            ],
            "req" => [
                "token" => $token,
                "mute" => $player->mute,
            ],
        ];
    
        return response()->json($response);
    }
}
