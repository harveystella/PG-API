<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Player;
use App\Models\TigerHistory;
use App\Models\TigerRoundList;
use App\Models\PrizeList;
use App\Models\TigerBetList;

class UserController extends Controller
{
    public function gameRequest(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'balance' => 'required|numeric'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'error_code' => 1,
                'message' => 'Invalid balance.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $balance = $request->input('balance');
        $token = $request->input('token');

        // If the token is provided, check if the player exists
        if ($token) {
            $player = Player::where('token', $token)->first();

            if ($player) {
                // Update the player's balance
                $player->balance = $balance * 10000;
                $player->save();

                // Construct the game URL
                $gameUrl = 'http://localhost:7456/?token=' . $token;

                return response()->json([
                    'error_code' => 0,
                    'data' => [
                        'token' => $token,
                        'game_url' => $gameUrl,
                    ],
                ]);
            }
        }

        // If no token provided or the player doesn't exist, generate a new token for a new player
        $newToken = $this->generateToken();
        $newUsername = $this->generateUsername();
        $newNickname = $this->generateNickname();

        // Construct the game URL
        $gameUrl = 'http://localhost:7456/?token=' . $newToken;

        // Create a new player with the generated token
        $newPlayer = new Player();
        $newPlayer->token = $newToken;
        $newPlayer->balance = $balance * 10000;
        $newPlayer->account = $newUsername;
        $newPlayer->nickname = $newNickname;
        $newPlayer->type = 0;
        $newPlayer->mute = 0;
        // You may need to populate other fields here based on your requirements
        $newPlayer->save();

        return response()->json([
            'error_code' => 0,
            'data' => [
                'token' => $newToken,
                'game_url' => $gameUrl,
            ],
        ]);
    }

    private function generateToken()
    {
        // Generate a random string of 32 alphanumeric characters
        $token = \Illuminate\Support\Str::random(32);

        // Convert the token to uppercase
        $token = strtoupper($token);

        // Check if the token already exists in the Player table
        // If it does, recursively call the function to generate a new token
        if (Player::where('token', $token)->exists()) {
            return $this->generateToken(); // Recursive call
        }

        // If the token doesn't exist in the Player table, return it
        return $token;
    }

    private function generateUsername()
    {
        $username = \Illuminate\Support\Str::random(16);
        $prefix = 'player_';
        $usernameWithPrefix = $prefix . $username;

        if (Player::where('account', $usernameWithPrefix)->exists()) {
            return $this->generateUsername(); // Recursive call
        }

        return $usernameWithPrefix;
    }

    private function generateNickname()
    {
        $nickname = \Illuminate\Support\Str::random(16);

        return $nickname;
    }


    public function getUserInfo(Request $request)
    {
         // Validate the request data
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'error_code' => 1,
                'message' => 'No token found.',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Extract the token from the request
        $token = $request->input('token');

        // Retrieve the player information from the database based on the token
        $player = Player::where('token', $token)->first();

        $order = TigerHistory::where('token', $token)->latest()->first();

        if(!$order) {
            return response()->json([
                'error_code' => 0,
                'data' => [
                    'player_info' => [
                        'id' => $player->id,
                        'balance' => $player->balance,
                        'account' => $player->account,
                        'nickname' => $player->nickname,
                        'type' => $player->type,
                        'mute' => $player->mute
                    ],
                    'game_info' => [

                    ],
                    'list' => '',
                ],
                'req' => [
                    'token' => $token
                ]
            ]);
        }

        $lastGame = TigerRoundList::where('order_id', $order->order_id)->first();

        $betOption = TigerBetList::where('bet_size', $lastGame->bet_size)
        ->where('bet_multiple', $lastGame->bet_multiple)
        ->where('basic_bet', $lastGame->basic_bet)
        ->first();

        $roundData = TigerRoundList::where('order_id', $order->order_id)->get();
        $countRound = count($roundData);

        $lastGame2 = TigerRoundList::where('order_id', $order->order_id)->orderByDesc('id')->first();

        $prizeRounds = PrizeList::where('round_id', $lastGame2->round_id)
            ->orderByDesc('id')
            ->get();

        if (!$prizeRounds->isEmpty()) {
            // Initialize an empty array to store all prize lists
            $prizeLists = [];
            // Initialize variables to hold the sums of win, rate, and player_win_lose
            $totalWin = 0;
            $totalRate = 0;

            // Iterate over each prize round and add its information to the prizeLists array
            foreach ($prizeRounds as $prizeRound) {
                // Add the win and rate of the current prize round to the totals
                $totalWin += $prizeRound->win;
                $totalRate += $prizeRound->rate;

                // Add the current prize list information to the array
                $prizeListArray[] = [
                    'win_pos_list' => json_decode($prizeRound->win_pos_list),
                    'index' => $prizeRound->index,
                    'level' => $prizeRound->level,
                    'item_type' => $prizeRound->item_type,
                    'rate' => $prizeRound->rate,
                    'win_item_list' => json_decode($prizeRound->win_item_list),
                    'multi_time' => $prizeRound->multi_time,
                    'win' => $prizeRound->win, // Use the adjusted win amount
                    'level_s' => $prizeRound->level_s,
                    'item_type_s' => $prizeRound->item_type_s,
                    'rate_s' => "图标倍数" . $prizeRound->rate,
                    'multi_time_s' => "",
                    'win_s' => number_format($prizeRound->win, 2, '.', ''), // Use the adjusted win amount for display
                ];
            }

            // Add the prize list array to the main prizeLists array
            $lastRound = [
                'item_type_list' => json_decode($lastGame2->item_type_list),
                'round_rate' => $totalRate,
                'round' => $countRound,
                'multi_time' => $prizeRound->multi_time,
                'prize_list' => $prizeListArray, // Store only the current prize list in an array
                'next_list' => null,
                'drop_list' => null,
                'win_pos_list' => !empty($prizeRound->win_pos_list) ? json_decode($prizeRound->win_pos_list) : null,
                'balance' => $lastGame2->balance,
                'free_play' => 0,
                'win' => $totalWin, // Use the adjusted win amount
                'free_mode_type' => 0,
                'item_type_list_append' => null,
                'all_win_item' => 0, // Note: Adjust if necessary
                'balance_s' => $lastGame2->balance_s,
                'win_s' => number_format($totalWin / 10000, 2, '.', ''), // Use the adjusted win amount for display
                'round_id' => $lastGame2->round_id,
                'player_win_lose_s' => $totalWin,
                'total_bet' => $lastGame2->bet,
            ];
            if ($player) {
                return response()->json([
                    'error_code' => 0,
                    'data' => [
                        'player_info' => [
                            'id' => $player->id,
                            'balance' => $player->balance,
                            'account' => $player->account,
                            'nickname' => $player->nickname,
                            'type' => $player->type,
                            'mute' => $player->mute
                        ],
                        'game_info' => [
                            'id' => $lastGame->id,
                            'last_time_bet' => $lastGame->bet,
                            'last_time_bet_id' => $betOption->id,
                            'last_time_bet_size' => $betOption->bet_size,
                            'last_time_basic_bet' => $betOption->basic_bet,
                            'last_time_bet_multiple' => $betOption->bet_multiple,
                            'free_total_times' => 0,
                            'free_remain_times' => 0,
                            'free_game_total_win' => 0,
                            'total_bet' => 1466695000,
                            'total_bet_times' => 1174,
                            'total_free_times' => 0,
                            'free_mode_type' => 0,
                            'last_win' => 0,
                            'last_multi' => 1,
                        ],
                        'list' => json_decode($lastGame2->item_type_list),
                        'lastRound' => $lastRound,
                    ],
                    'req' => [
                        'token' => $token
                    ]
                ]);
            } else {
                return response()->json([
                    'error_code' => 1,
                    'message' => 'No player found.',
                    'errors' => $validator->errors(),
                ], 400);
            }

        } else {

            // Initialize an empty array to store all prize lists
            $prizeLists = null;
            // Initialize variables to hold the sums of win, rate, and player_win_lose
            $totalWin = 0;
            $totalRate = 0;

            // Add the prize list array to the main prizeLists array
            $lastRound = [
                'item_type_list' => json_decode($lastGame2->item_type_list),
                'round_rate' => 0,
                'round' => 1,
                'multi_time' => 1,
                'prize_list' => null,
                'next_list' => null,
                'drop_list' => null,
                'win_pos_list' => null,
                'balance' => $lastGame2->balance,
                'free_play' => 0,
                'win' => 0, // Use the adjusted win amount
                'free_mode_type' => 0,
                'item_type_list_append' => null,
                'all_win_item' => 0, // Note: Adjust if necessary
                'balance_s' => $lastGame2->balance_s,
                'win_s' => number_format($totalWin / 10000, 2, '.', ''), // Use the adjusted win amount for display
                'round_id' => $lastGame2->round_id,
                'player_win_lose_s' => $totalWin,
                'total_bet' => $lastGame2->bet,
            ];
            if ($player) {
                return response()->json([
                    'error_code' => 0,
                    'data' => [
                        'player_info' => [
                            'id' => $player->id,
                            'balance' => $player->balance,
                            'account' => $player->account,
                            'nickname' => $player->nickname,
                            'type' => $player->type,
                            'mute' => $player->mute
                        ],
                        'game_info' => [
                            'id' => $lastGame->id,
                            'last_time_bet' => $lastGame->bet,
                            'last_time_bet_id' => $betOption->id,
                            'last_time_bet_size' => $betOption->bet_size,
                            'last_time_basic_bet' => $betOption->basic_bet,
                            'last_time_bet_multiple' => $betOption->bet_multiple,
                            'free_total_times' => 0,
                            'free_remain_times' => 0,
                            'free_game_total_win' => 0,
                            'total_bet' => 1466695000,
                            'total_bet_times' => 1174,
                            'total_free_times' => 0,
                            'free_mode_type' => 0,
                            'last_win' => 0,
                            'last_multi' => 1,
                        ],
                        'list' => json_decode($lastGame2->item_type_list),
                        'lastRound' => $lastRound,
                    ],
                    'req' => [
                        'token' => $token
                    ]
                ]);
            } else {
                return response()->json([
                    'error_code' => 1,
                    'message' => 'No player found.',
                    'errors' => $validator->errors(),
                ], 400);
            }
        }
    }
}
