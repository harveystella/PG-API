<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Http\API\spinData\fortuneTiger\rtp80;
use Illuminate\Support\Facades\Validator;
use App\Models\TigerBetList;
use App\Http\Controllers\Controller;

class BetController extends Controller
{
    public function getBetInfo(Request $request) {
        $betList = TigerBetList::all()->toArray();
        $addSubCombination = [
            1, 2, 3, 5, 10, 13, 15, 20, 31, 25, 30, 35, 40
        ];
        return response()->json([
            'error_code' => 0,
            'data' => [
                'bet_list' => $betList,
                'default_id' => 10,
                'addSubCombination' => $addSubCombination,
            ],
            'req' => []
        ]);
    }

    public function spinBet(Request $request) {
        // Validate the request parameters
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'token' => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'error_code' => 1,
                'message' => 'Invalid request parameters.',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Retrieve the bet information from the TigerBetList model based on the provided ID
        $betInfo = TigerBetList::find($request->id);

        // Check if the bet information is found
        if (!$betInfo) {
            return response()->json([
                'error_code' => 1,
                'message' => 'Bet information not found for the provided ID. ',
            ], 404);
        }

        // Retrieve the player information from the Player model based on the provided token
        $player = Player::where('token', $request->token)->first();

        // Check if the player is found
        if (!$player) {
            return response()->json([
                'error_code' => 1,
                'message' => 'Player not found for the provided token.',
            ], 404);
        }

        // Deduct the user balance based on the bet size
        $betSize = $betInfo->total_bet; // Take the total bet amount 
        $player->balance -= $betSize; //Deduct the user player balance.

        // Save the updated player balance
        $player->save();

        return response()->json([
            "error_code" => 0,
            "data" => [
                "result" => [
                    "round_list" => [
                        [
                            "item_type_list" => [7, 5, 5, 1, 7, 2, 7, 6, 5],
                            "round_rate" => 0,
                            "round" => 1,
                            "multi_time" => 0,
                            "prize_list" => [
                                [
                                    "win_pos_list" => null,
                                    "index" => 2,
                                    "level" => 3,
                                    "item_type" => 7,
                                    "rate" => 0,
                                    "win_item_list" => [7, 1, 7],
                                    "multi_time" => 0,
                                    "win" => 0,
                                    "level_s" => "3星连珠",
                                    "item_type_s" => "橙子",
                                    "rate_s" => "",
                                    "multi_time_s" => "",
                                    "win_s" => "0.00",
                                ],
                            ],
                            "next_list" => null,
                            "drop_list" => null,
                            "win_pos_list" => null,
                            "balance" => 650000,
                            "free_play" => 0,
                            "win" => 0,
                            "free_mode_type" => 1,
                            "item_type_list_append" => [7, 0, 0, 1, 7, 0, 7, 0, 0],
                            "all_win_item" => 0,
                            "balance_s" => "65.00",
                            "win_s" => "0.00",
                            "round_id" => "3994890055",
                            "player_win_lose_s" => -5000,
                            "total_bet" => 5000,
                        ],
                        [
                            "item_type_list" => [7, 7, 0, 1, 7, 0, 7, 0, 0],
                            "round_rate" => 0,
                            "round" => 2,
                            "multi_time" => 0,
                            "prize_list" => [
                                [
                                    "win_pos_list" => null,
                                    "index" => 2,
                                    "level" => 3,
                                    "item_type" => 7,
                                    "rate" => 0,
                                    "win_item_list" => [7, 1, 7],
                                    "multi_time" => 0,
                                    "win" => 0,
                                    "level_s" => "3星连珠",
                                    "item_type_s" => "橙子",
                                    "rate_s" => "",
                                    "multi_time_s" => "",
                                    "win_s" => "0.00",
                                ],
                            ],
                            "next_list" => null,
                            "drop_list" => null,
                            "win_pos_list" => null,
                            "balance" => 650000,
                            "free_play" => 0,
                            "win" => 0,
                            "free_mode_type" => 1,
                            "item_type_list_append" => [0, 7, 0, 0, 0, 0, 0, 0, 0],
                            "all_win_item" => 0,
                            "balance_s" => "65.00",
                            "win_s" => "0.00",
                            "round_id" => "0118712010",
                            "player_win_lose_s" => 0,
                            "total_bet" => 0,
                        ],
                        [
                            "item_type_list" => [7, 7, 0, 1, 7, 0, 7, 0, 0],
                            "round_rate" => 3,
                            "round" => 3,
                            "multi_time" => 1,
                            "prize_list" => [
                                [
                                    "win_pos_list" => [0, 3, 6],
                                    "index" => 2,
                                    "level" => 3,
                                    "item_type" => 7,
                                    "rate" => 3,
                                    "win_item_list" => [7, 1, 7],
                                    "multi_time" => 0,
                                    "win" => 3000,
                                    "level_s" => "3星连珠",
                                    "item_type_s" => "橙子",
                                    "rate_s" => "图标倍数3",
                                    "multi_time_s" => "",
                                    "win_s" => "0.30",
                                ],
                            ],
                            "next_list" => null,
                            "drop_list" => null,
                            "win_pos_list" => [0, 3, 6],
                            "balance" => 653000,
                            "free_play" => 0,
                            "win" => 3000,
                            "free_mode_type" => 1,
                            "item_type_list_append" => [0, 0, 0, 0, 0, 0, 0, 0, 0],
                            "all_win_item" => 0,
                            "balance_s" => "65.30",
                            "win_s" => "0.30",
                            "round_id" => "6999011530",
                            "player_win_lose_s" => 3000,
                            "total_bet" => 0,
                        ],
                    ],
                    "rate" => 3,
                ],
                "round_id" => "9467247474",
                "order_id" => "9-1708352936-WPV0HUQ6C",
                "balance" => 653000,
                "bet" => 5000,
                "prize" => 3000,
                "player_win_lose" => -2000,
                "is_enter_free_game" => true,
                "chooseItem" => 7,
                "balance_s" => "65.30",
                "bet_s" => "0.50",
                "prize_s" => "0.30",
                "player_win_lose_s" => "-0.20",
                "free_game_total_win_s" => "0.00",
                "dbg" => [
                    "目前的类型是=[普通模式]",
                    "基础投注=[5], 投注倍数=[1], 投注大小=[1000], 必杀盈利率=[2.50]",
                    "下注总额=[0.50], 用户余额=[65.50]",
                    "使用数组=[3], 权重(10/290)",
                    "本轮是否进免费游戏=true, 随机值=0, 权重(10/290)",
                    "采用数组=3, 随机结果上下浮动=[0~0], 随机值=0",
                    "随机无结果, 采用不中奖结果",
                    "玩家本次下注=0.50, 中奖=0.30, 输赢=-0.20",
                    "倍率=3",
                ],
            ],
            "req" => [
                "token" => "4903689A189F4A0087747771EEDA90DE",
                "id" => 1,
                "idempotent" => "1708352935556",
            ],
                ]);
        }

    // Function to generate order ID
    private function generateOrderId() {
        $prefix = '9'; // Prefix for order ID
        $timestamp = time(); // Current timestamp
        $randomString = $this->generateRandomString(9); // Random alphanumeric string
        return "{$prefix}-{$timestamp}-{$randomString}";
    }

    // Function to generate a random alphanumeric string of specified length
    private function generateRandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}
