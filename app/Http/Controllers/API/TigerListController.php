<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TigerHistory;
use App\Models\TigerRoundList;
use App\Models\PrizeList;
use Illuminate\Support\Arr;


class TigerListController extends Controller
{
    public function getList(Request $request) {
        $token = $request->input('token');
        $limit = $request->input('limit', 20); // Default limit to 20 if not provided
        $offset = $request->input('offset', 0); // Default offset to 0 if not provided
        $start_time = $request->input('start_timestamp');
        $end_time = $request->input('end_timestamp');
    
        // Build the query with any provided filters
        $query = TigerHistory::query();
    
        if ($start_time) {
            $query->where('create_timestamp', '>=', $start_time);
        }
    
        if ($end_time) {
            $query->where('create_timestamp', '<=', $end_time);
        }
    
        // Get total counts and sums before applying limit and offset
        $totalrecords = $query->count();
        $betSum = $query->sum('bet_s'); // Assuming 'bet_s' is stored as a numeric type
        $winSum = $query->sum('win_s'); // Assuming 'win_s' is stored as a numeric type
        $totalBet = $query->sum('bet');
        $totalWinLose = $query->sum('win'); // Adjust based on how you calculate win/lose

         // Ensure 'bet_s' and 'win_s' are strings
        $betSumString = (string) $betSum;
        $winSumString = (string) $winSum;

        $query->orderBy('created_at', 'desc');

        $gameList = $query->limit($limit)->offset($offset)->get()->map(function ($item) {
            // Convert create_timestamp to a human-readable format
    
            // Ensure 'free' is a boolean
            $freeBoolean = $item->free == 1;
    
            return [
                'create_time' => "",
                'order_id' => $item->order_id,
                'round_id' => $item->round_id,
                'win_s' => (string) $item->win_s, // Ensure it's a string
                'bet_s' => (string) $item->bet_s, // Ensure it's a string
                'free_times' => $item->free_times,
                'free_mode_type' => $item->free_mode_type,
                'create_timestamp' => $item->create_timestamp,
                'free' => $freeBoolean,
                'normal_round_times' => $item->normal_round_times,
                'free_round_times' => $item->free_round_times,
                'bet' => $item->bet,
                'win' => $item->win
            ];
        });
    
        return response()->json([
            'error_code' => 0,
            'data' => [
                'bet_s' => $betSumString,
                'win_s' => $winSumString,
                'list' => $gameList,
                'id' => 0,
                'count' => $totalrecords,
                'bet' => $totalBet,
                'win' => $totalWinLose,
            ],
            'req' => [
                'token' => $token,
                'start_timestamp' => $start_time,
                'end_timestamp' => $end_time,
                'limit' => $limit,
                'page' => floor($offset / $limit),
                'id' => 0,
                'offset' => $offset,
            ]
        ]);
    }

    public function getDetails(Request $request)
    {
        $token = $request->token; // Assuming you're validating the token for security reasons
        $orderId = $request->order_id;

        // Fetch round data based on order_id
        $roundData = TigerRoundList::where('order_id', $orderId)->get();

        // Check the number of rounds found for the order_id
        if ($roundData->count() > 1) {
            // If more than one round is found, execute alternative logic
            return $this->handleMultipleRounds($roundData, $token, $orderId);
        }

        $listData = $roundData->map(function ($round) {
            // For each round, fetch related prize list entries using the round_id
            $prizeLists = PrizeList::where('round_id', $round->round_id)->get()->map(function ($prizeList) {
                return [
                    'win_pos_list' => json_decode($prizeList->win_pos_list),
                    'index' => $prizeList->index,
                    'level' => $prizeList->level,
                    'item_type' => $prizeList->item_type,
                    'rate' => $prizeList->rate,
                    'win_item_list' => json_decode($prizeList->win_item_list),
                    'multi_time' => $prizeList->multi_time,
                    'win' => $prizeList->win,
                    'level_s' => $prizeList->level_s,
                    'item_type_s' => $prizeList->item_type_s,
                    'rate_s' => "图标倍数" . $prizeList->rate,
                    'multi_time_s' => "",
                    'win_s' => number_format($prizeList->win / 10000, 2, '.', ''),
                ];
            });

            // Calculate the prize list count
            $prize_list_count = $prizeLists->count();

            // If no prize lists are found, set prize_list to null
            $prize_list = $prize_list_count > 0 ? $prizeLists : null;

            return [
                'create_time' => $round->created_at->format('Y/m/d H:i'),
                'order_id' => $round->round_id,
                'round_id' => $round->round_id,
                'bet_s' => $round->bet_s,
                'prize_s' => "",
                'free_mode_type' => 0,
                'player_win_lose_s' => "",
                'balance_s' => "",
                'balance_before_score_s' => "",
                'create_timestamp' => $round->create_timestamp,
                'bet' => $round->bet,
                'prize' => $round->prize,
                'player_win_lose' => $round->player_win_lose,
                'balance' => $round->balance,
                'bet_size' => $round->bet_size,
                'balance_before_score' => $round->balance_before_score,
                'free' => false,
                "free_total_times" => 0,
                "free_remain_times" => 0,
                "free_game_total_win" => 0,
                'basic_bet' => $round->basic_bet,
                'bet_multiple' => $round->bet_multiple,
                'round_list_count' => 1,
                'round_list' => [
                    [
                        'bet_s' => $round->bet_s,
                        'prize_s' => $round->prize_s,
                        'player_win_lose_s' => $round->player_win_lose_s,
                        'balance' => $round->balance_s,
                        'bet_size_s' => number_format($round->bet_size / 10000, 2, '.', ''),
                        'round' => 1,
                        'order_id' => $round->round_id,
                        'round_id' => $round->round_id,
                        'bet' => $round->bet,
                        'prize' => $round->prize,
                        'player_win_lose' => $round->player_win_lose,
                        'balance' => $round->balance,
                        'bet_size' => $round->bet_size,
                        'bet_multiple' => $round->bet_multiple,
                        'basic_bet' => $round->basic_bet,
                        'multi_time' => $round->multi_time,
                        'prize_list_count' => $prize_list_count,
                        'prize_list' => $prize_list,
                        'item_type_list' => json_decode($round->item_type_list),
                    ]
                ],
            ];
        });

        return response()->json([
            'error_code' => 0,
            'data' => [
                'list' => $listData
            ],
            'req' => [
                'token' => $token,
                'order_id' => $orderId
            ]
        ]);
    }

    private function handleMultipleRounds($roundData, $token, $orderId)
    {

        foreach ($roundData as $round) {
            // Fetch prize list entries for each round using the round_id
            $prizeLists = PrizeList::where('round_id', $round->round_id)->get()->map(function ($prizeList) {
                $win = $prizeList->win;
                if ($prizeList->multi_time == 10) {
                    $win /= 10; // Divide the win amount by 10 if multi_time is 10
                }
                return [
                    'win_pos_list' => json_decode($prizeList->win_pos_list),
                    'index' => $prizeList->index,
                    'level' => $prizeList->level,
                    'item_type' => $prizeList->item_type,
                    'rate' => $prizeList->rate,
                    'win_item_list' => json_decode($prizeList->win_item_list),
                    'multi_time' => $prizeList->multi_time,
                    'win' => $win, // Use the adjusted win amount
                    'level_s' => $prizeList->level_s,
                    'item_type_s' => $prizeList->item_type_s,
                    'rate_s' => "图标倍数" . $prizeList->rate,
                    'multi_time_s' => "",
                    'win_s' => number_format($win, 2, '.', ''), // Use the adjusted win amount for display
                ];
            });
        }

        $roundList = [];

        foreach ($roundData as $index => $singleData) {
            $singleRound = TigerRoundList::where('round_id', $singleData->round_id)->first();

            // Get the prize list for the current round
            $prizeData = PrizeList::where('round_id', $singleRound->round_id)->get();
            
            // Get the last prize from the prize list
            $lastPrize = $prizeData->last();

            // Get the multi_time from the last prize
            $multiTime = $lastPrize ? $lastPrize->multi_time : 0;

            // Populate round_list for the current round
            $round_list_item = [
                'bet_s' => $singleRound->bet_s,
                'prize_s' => $singleRound->prize_s,
                'player_win_lose_s' => $singleRound->player_win_lose_s,
                'balance_s' => $singleRound->balance_s,
                'bet_size_s' => number_format($singleRound->bet_size / 10000, 2, '.', ''),
                'round' => $index + 1, // Count of the round (1-based index)
                'order_id' => $singleRound->round_id,
                'round_id' => $singleRound->round_id,
                'bet' => $singleRound->bet,
                'prize' => $singleRound->prize,
                'player_win_lose' => $singleRound->player_win_lose,
                'balance' => $singleRound->balance,
                'bet_size' => $round->bet_size,
                'bet_multiple' => $round->bet_multiple,
                'basic_bet' => $round->basic_bet,
                'multi_time' => $multiTime,
                'prize_list_count' => count($prizeLists),
                'prize_list' => $prizeLists,
                'item_type_list' => json_decode($singleRound->item_type_list),
            ];
        
            $roundList[] = $round_list_item;
        }
            // Construct the detailed data for the current round
            $listData[] = [
                'create_time' => $round->created_at->format('Y/m/d H:i'),
                'order_id' => $round->round_id,
                'round_id' => $round->round_id,
                'bet_s' => $round->bet_s,
                'prize_s' => $round->prize_s,
                'free_mode_type' => 0,
                'player_win_lose_s' => $round->player_win_lose_s,
                'balance_s' => $round->balance_s,
                'balance_before_score_s' => $round->balance_before_score_s,
                'create_timestamp' => $round->create_timestamp,
                'bet' => $round->bet,
                'prize' => $round->prize,
                'player_win_lose' => $round->player_win_lose,
                'balance' => $round->balance,
                'balance_before_score' => $round->balance_before_score,
                'free' => false,
                'free_total_times' => 0,
                'free_remain_times' => 0,
                'free_game_total_win' => $round->prize,
                'bet_size' => $round->bet_size,
                'basic_bet' => $round->basic_bet,
                'bet_multiple' => $round->bet_multiple,
                'round_list_count' => count($roundData),
                'round_list' => $roundList,
            ];
        
    
        // Return the data as a JSON response
        return response()->json([
            'error_code' => 0,
            'data' => [
                'list' => $listData
            ],
            'req' => [
                'token' => $token,
                'order_id' => $orderId
            ]
        ]);
    }

    
}
