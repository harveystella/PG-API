<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\TigerBetList;
use App\Services\FortuneTigerService;

class StimulateController extends Controller
{
    protected $fortuneTigerService;
    
        public function __construct(FortuneTigerService $fortuneTigerService)
        {
            $this->fortuneTigerService = $fortuneTigerService;
        }
    
        public function spin(Request $request)
        {
            // Get the bet amount from the request, default to 1 if not provided
            $betOption = $request->input('id', 1);

            $betInfo = TigerBetList::find($betOption);

            $betAmount = $betInfo['total_bet'] / 10000;

            //Get the user balance
            $token = $request->input('token');

            $player = Player::where('token', $token)->first();

            $pre = $player->balance;

             // Check if the player has enough balance
            if ($player->balance >= $betInfo['total_bet']) {
                // Deduct the bet amount from the player's balance
                $player->balance -= $betInfo['total_bet'];
                $player->save();

                 // Decide between a regular spin and a free spin with a 1% chance
                $isFreeSpin = (rand(1, 100) <= 1); // There is a 1% chance that $isFreeSpin will be true

                if ($isFreeSpin) {
                    $free_index = rand(2, 7);
                    // Process a free spin
                    $spinResult = $this->fortuneTigerService->processFreeSpin3($free_index, $betAmount, $token, $betOption, $player, $pre);
                } else {
                    // Process a regular spin
                    $spinResult = $this->fortuneTigerService->spin($betAmount, $token, $player, $betOption);
                }

                // After spin logic (if any), including balance update based on win/loss could be handled here

                return response()->json($spinResult);
            } else {
                // Handle case where player does not have enough balance
                return response()->json([
                    'error_code'=> 154,
                    'error_msg' => 'Insufficient balance',
                    'data' => [
                        'result' => [
                            'round_list' => [],
                            'rate' => 0,           
                        ],
                        'round_id' => '',
                        'order_id' => '',
                        'balance' => 0,
                        'bet' => 0,
                        'prize' => 0,
                        'player_win_lose' => 0,
                        'is_enter_free_game' => false,
                        'chooseItem' => 0,
                        'balance_s' => '',
                        'bet_s' => '',
                        'prize_s' => '',
                        'player_win_lose_s' => '',
                        'free_game_total_win_s' => '',
                        'dbg' => []
                    ],
                    'req' => [
                        'token' => $token,
                        'id' => $betOption,
                        'idempotent' => '1708350389492'
                    ]
                    ],
                );
            }
        }

        public function checkReels(Request $request) {
            $free_index = rand(2, 7);
            // Get the bet amount from the request, default to 1 if not provided
            $betOption = $request->input('id', 1);
            
            $token = $request->input('token');

            $player = Player::where('token', $token)->first();

            $betInfo = TigerBetList::find($betOption);

            $betAmount = $betInfo['total_bet'] / 10000;

            $pre = $player->balance;
            
            
            $reels = $this->fortuneTigerService->processFreeSpin3($free_index, $betAmount, $token, $betOption, $player, $pre);
            return $reels;
        }

        public function checkReels123() {
            $reels = $this->fortuneTigerService->generateReels();
        }
}
