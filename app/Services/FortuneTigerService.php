<?php

namespace App\Services;
use App\Models\Player;
use App\Models\TigerHistory;
use App\Models\TigerRoundList;
use App\Models\TigerBetList;
use App\Models\PrizeList;
use Illuminate\Support\Facades\Log;

class FortuneTigerService
{
    protected $symbolsPaytable = [
        1 => [
            "identifier" => "WILD", 
            "multiplier" => 50, 
            "probability" => 0.1
        ],
        2 => [
            "identifier" => "Gold bar",
            "multiplier" => 20,
            "probability" => 0.05,
        ],
        3 => [
            "identifier" => "Amulet",
            "multiplier" => 5,
            "probability" => 0.05,
        ],
        4 => [
            "identifier" => "Gold pouch",
            "multiplier" => 2,
            "probability" => 0.12,
        ],
        5 => [
            "identifier" => "Red packet",
            "multiplier" => 1.6,
            "probability" => 0.21,
        ],
        6 => [
            "identifier" => "Fire crackers",
            "multiplier" => 1,
            "probability" => 0.24,
        ],
        7 => [
            "identifier" => "Orange",
            "multiplier" => 0.6,
            "probability" => 0.23,
        ],
    ];

    protected function loadSymbolsPaytable($rtp)
    {
        switch ($rtp) {
            case 1:
                return [
                    // Paytable for RTP = 1
                ];
            case 2:
                return [
                    // Paytable for RTP = 2
                ];
            case 3:
                return [
                        // Paytable for RTP = 3
                ];
            case 4:
                return [
                    // Paytable for RTP = 4
                ];
            case 5:
                return [
                    // Paytable for RTP = 5
                ];
            case 6:
                    return [
                        // Paytable for RTP = 6
            ];
            case 7:
                return [
                    // Paytable for RTP = 7
                ];
            case 8:
                return [
                    // Paytable for RTP = 8
                ];
            case 9:
                return [
                    1 => [
                        "identifier" => "WILD",
                        "multiplier" => 50,
                        "probability" => 0.1
                    ],
                    2 => [
                        "identifier" => "Gold bar",
                        "multiplier" => 20,
                        "probability" => 0.05,
                    ],
                    3 => [
                        "identifier" => "Amulet",
                        "multiplier" => 5,
                        "probability" => 0.05,
                    ],
                    4 => [
                        "identifier" => "Gold pouch",
                        "multiplier" => 2,
                        "probability" => 0.12,
                    ],
                    5 => [
                        "identifier" => "Red packet",
                        "multiplier" => 1.6,
                        "probability" => 0.21,
                    ],
                    6 => [
                        "identifier" => "Fire crackers",
                        "multiplier" => 1,
                        "probability" => 0.24,
                    ],
                    7 => [
                        "identifier" => "Orange",
                        "multiplier" => 0.6,
                        "probability" => 0.23,
                    ],
                ];
            // Add cases for other RTP options
            case 10:
                return [
                    // Paytable for RTP = 10
                ];
            case 11:
                return [
                    // Paytable for RTP = 11
                ];
            case 85:
                return [
                    // Paytable for RTP = 8.5
                ];
            default:
                return [
                    // Default paytable for unknown RTP values
                ];
        }
    }

    protected $freeSpinSymbolsPaytable = [
        0 => ["identifier" => "BLANK", "multiplier" => 0, "probability" => 0.9], // High probability for blank
        1 => ["identifier" => "WILD", "multiplier" => 50, "probability" => 0.1],
        // Assuming one random symbol will be selected for each free spin session
    ];

    protected $winLines = [
        [[1, 0], [1, 1], [1, 2]],
        [[0, 0], [0, 1], [0, 2]],
        [[2, 0], [2, 1], [2, 2]],
        [[0, 0], [1, 1], [2, 2]],
        [[0, 2], [1, 1], [2, 0]],
    ];

    public function spin($betAmount, $token, $player, $betOption)
    {
        $rtp = $player->rtp;
        $reels = $this->generateReels($rtp);
        $result = $this->evaluateSpin($reels, $betAmount, $token, $player, $betOption);

        // Assuming $result contains all the data needed to be stored and matches the structure expected by storeSpinResult
        $store = $this->storeSpinResult($result);
        return $result;
    }

    protected function generateReels($rtp)
    {
        $reels = [];
        for ($col = 0; $col < 3; $col++) {
            for ($row = 0; $row < 3; $row++) {
                $reels[$col][$row] = $this->getRandomSymbol(false, $rtp); // Notice the column is the first index
            }
        }
        return $reels;
    }

    protected function getRandomSymbol($isFreeSpin = false, $rtp)
    {
        // Load symbols paytable based on the RTP value
        $paytable = $this->loadSymbolsPaytable($rtp);
        $random = mt_rand(1, 1000) / 1000;
        $cumulativeProbability = 0.0;
        foreach ($paytable as $symbolId => $symbol) {
            $cumulativeProbability += $symbol["probability"];
            if ($random <= $cumulativeProbability) {
                return $symbolId;
            }
        }
        return null; // Fallback in case probabilities do not sum to 1
    }

    public function processFreeSpin3($free_index, $betAmount, $token, $betOption, $player, $pre) {
        $winLines = [
            [1, 4, 7], [0, 3, 6], [2, 5, 8], [0, 4, 8], [2, 4, 6]
        ];

        $symbolMultiplier = $this->symbolsPaytable[$free_index]["multiplier"];
        // Prepare the game environment
        $bet = $betAmount * 10000;
        $balance = $player->balance;

        $item_type_lists = [];
        $item_type_appends = [];
        

        // Determine if there should be a fourth round based on free_index and random chance
        //$is4round = ($free_index >= 5) && (rand(1, 100) == 1);
        $is4round = false;

        if ($is4round) {
            // First Round: Generate the initial list with one guaranteed winning line
            $firstRound = $this->generateInitialList($free_index, $winLines);
            $firstAppend = $this->generateFirstAppend($firstRound, $free_index);

            // Second Round: Apply the first append to simulate the second round's item type list
            $secondRound = $this->applyAppend($firstAppend);
            $secondAppend = $this->generateRandomAppend($secondRound, $free_index);

            // Third Round: Apply the second append
            $thirdRound = $this->applyAppend($secondAppend);
            $thirdAppend = $this->generateRandomAppend($thirdRound, $free_index);

            // Fourth Round: Apply the third append
            $fourthRound = $this->applyAppend($thirdAppend);
            $fourthAppend = $this->generateRandomAppend($fourthRound, $free_index); // Last append likely zeroes if no continuation condition

            // Fifth Round: Apply the Fourth append
            $fifthRound = $this->applyAppend($fourthAppend);
            $fifthAppend = $this->generateRandomAppend($fifthRound, $free_index); // Last append likely zeroes if no continuation condition

            // Sixth Round: Apply the Fifth append
            $sixthRound = $this->applyAppend($fifthRound);
            $sixthAppend = $this->generateRandomAppend($sixthRound, $free_index); // Last append likely zeroes if no continuation condition

            // Calculate differences
            $differences[] = $secondRound;
            $differences[] = $this->calculateDifferences($thirdRound, $firstAppend);
            $differences[] = $this->calculateDifferences($fifthRound, $secondAppend);
            $differences[] = $this->calculateDifferences($fifthRound, $sixthAppend);

            $item_type_list_rounds = [$firstRound, $thirdRound, $fifthRound, $fifthRound];
            $item_type_list_appends = $differences;
        } else {
            // First Round: Generate the initial list with one guaranteed winning line
            $firstRound = $this->generateInitialList($free_index, $winLines);
            $firstAppend = $this->generateFirstAppend($firstRound, $free_index);

            // Second Round: Apply the first append to simulate the second round's item type list
            $secondRound = $this->applyAppend($firstAppend);
            $secondAppend = $this->generateRandomAppend($secondRound, $free_index);

            // Third Round: Apply the second append
            $thirdRound = $this->applyAppend($secondAppend);
            $thirdAppend = $this->generateRandomAppend($thirdRound, $free_index);

            // Fourth Round: Apply the third append
            $fourthRound = $this->applyAppend($thirdAppend);
            $fourthAppend = $this->generateRandomAppend($fourthRound, $free_index); // Last append likely zeroes if no continuation condition        

            // Calculate differences
            $differences[] = $secondRound;
            $differences[] = $this->calculateDifferences($thirdRound, $firstAppend);
            $differences[] = $this->calculateDifferences($thirdRound, $fourthAppend);

            $item_type_list_rounds = [$firstRound, $thirdRound, $thirdRound];
            $item_type_list_appends = $differences;
        }

        // Identify and remove duplicate occurrences of the target array
        $targetArray = [0, 0, 0, 0, 0, 0, 0, 0, 0];
        $uniqueDifferences = [];
        $uniqueIndices = [];
        foreach ($differences as $index => $difference) {
            if ($difference != $targetArray || !in_array($difference, $uniqueDifferences)) {
                $uniqueDifferences[] = $difference;
                $uniqueIndices[] = $index;
            } else {
                unset($item_type_list_rounds[$index]);
            }
        }

        // Update $differences and $item_type_list_appends with unique values
        $differences = $uniqueDifferences;
        $item_type_list_appends = $differences;

        $winning_info = $this->calculateWinningLinesAndIndices($item_type_list_rounds, $item_type_list_appends, $winLines, $free_index, $bet, $balance, $token, $betOption, $player, $pre);
        return $winning_info;
    }

    private function calculateDifferences($round, $append) {
        $differences = [];
        foreach ($round as $index => $value) {
            if ($value != $append[$index]) {
                $differences[$index] = $value; // Store the new value that is different from the append
            } else {
                $differences[$index] = 0; // No change at this index
            }
        }
        return $differences;
    }
    
    

    private function generateInitialList($free_index, $winLines) {
        $list = array_fill(0, 9, 0);
        $chosenLine = $winLines[array_rand($winLines)];  // Pick a random winning line
    
        // Ensure at least one free_index is on the winning line
        $mandatoryFreeIndexPosition = $chosenLine[array_rand($chosenLine)];
        $list[$mandatoryFreeIndexPosition] = $free_index;
    
        // Set up the rest of the winning line with either free_index or wildcards
        foreach ($chosenLine as $position) {
            if ($list[$position] === 0) { // Only fill positions that are still zero
                $list[$position] = rand(0, 1) ? $free_index : 1;
            }
        }
    
        // Fill the rest of the slots with random values that are neither free_index nor wildcard
        $this->populateRandom($list, $free_index, $chosenLine);
        return $list;
    }
    
    private function populateRandom(&$list, $free_index, $winLine) {
        $availableSymbols = array_diff(range(1, 7), [$free_index, 1]); // Exclude free_index and wildcard from the fill choices
    
        foreach ($list as $index => &$value) {
            if ($value === 0 && !in_array($index, $winLine)) {
                $value = $availableSymbols[array_rand($availableSymbols)];
            }
        }
    }

    private function generateFirstAppend($list, $free_index) {
        $append = array_fill(0, 9, 0);
        foreach ($list as $index => $value) {
            if (!in_array($value, [$free_index, 1])) {  // Turn non-winning symbols to zeros
                $append[$index] = 0;
            } else {
                $append[$index] = $value;  // Preserve winning symbols
            }
        }
        return $append;
    }

    private function applyAppend($appendList) {
        // This function essentially copies the append list to a new list for the next round
        return $appendList;
    }

    private function generateRandomAppend($list, $free_index) {
        $append = array_fill(0, 9, 0);
        foreach ($list as $index => $value) {
            if ($value == 0) {
                $append[$index] = rand(0, 1) ? $free_index : 0;  // Randomly populate zeros
            } else {
                $append[$index] = $value;  // Preserve existing values
            }
        }
        return $append;
    }

    public function calculateWinningLinesAndIndices($item_type_list_rounds, $item_type_list_appends,  $winLines, $free_index, $bet, $balance, $token, $betOption, $player, $pre) {
        $results = [];
        $winLineKeyCounts = [];
        $totalRounds = count($item_type_list_rounds);  // Total number of rounds

        $balance_s = number_format($balance / 10000, 2, '.', '');
        $roundCounter = 0; // Initialize a counter to track the current round number

        //echo "ITEM TYPE LIST ROUNDS: " . json_encode($item_type_list_rounds) . "\n";
        //echo "Win Lines: " . json_encode($winLines) . "\n";
        //echo "Free Index: " . json_encode($free_index) . "\n";
        //echo "Bet Amount: " . json_encode($bet) . "\n";
        //echo "Balance: " . json_encode($balance) . "\n";
        //echo "Token: " . json_encode($token) . "\n";
        //echo "Bet Option: " . json_encode($betOption) . "\n";

        foreach ($item_type_list_rounds as $roundIndex => $item_type_list) {
            $winningLines = [];
            $uniqueWinningIndices = [];
            $prize_list = [];
            $totalWin = 0; 
            $totalRate = 0;
            $multiTime = 0;
            $totalWin_s = "0.00";
            $winPosList = [];
            $initialBet = $bet;
            $roundCounter++; // Increment for each round processed
            $isLastRound = ($roundCounter == $totalRounds); // Check if this is the last round
            //echo "Round Counter: " . json_encode($roundCounter) . "\n";
            //echo "is Last Round: " . json_encode($isLastRound) . "\n";
            //echo "Item Type List Rounds: " . json_encode($item_type_list) . "\n";
            
    
            foreach ($winLines as $lineIndex => $line) {
                $pos1 = $line[0];
                $pos2 = $line[1];
                $pos3 = $line[2];
    
                $type1 = $item_type_list[$pos1];
                $type2 = $item_type_list[$pos2];
                $type3 = $item_type_list[$pos3];
    
                // Define condition for winning based on item types
                if (($type1 == $type2 || $type1 == 1 || $type2 == 1) &&
                    ($type2 == $type3 || $type2 == 1 || $type3 == 1) &&
                    $type1 != 0 && $type2 != 0 && $type3 != 0) {
    
                    $winLineKey = $lineIndex + 1;
    
                    if (!in_array($winLineKey, $winningLines)) {
                        $winningLines[] = $winLineKey;
                        $uniqueWinningIndices[] = [$pos1, $pos2, $pos3];
                    }
                    
                    if ($isLastRound) {
                        if (!isset($winLineKeyCounts[$winLineKey])) {
                            $winLineKeyCounts[$winLineKey] = 0;
                        }
                        $winLineKeyCounts[$winLineKey]++;
                    }

                    $prize = $this->generatePrizeInfo($pos1, $pos2, $pos3, $item_type_list, $winLineKey, $free_index, $winLineKeyCounts[$winLineKey] ?? 0, $initialBet, $isLastRound, $totalRounds);
                    $prize_list[] = $prize;
                    $totalWin += $prize['win']; // This win amount will be adjusted later if necessary
                    $totalRate += $prize['rate'];
                    // Collect unique positions
                    $uniqueWinPositions[$pos1] = true;
                    $uniqueWinPositions[$pos2] = true;
                    $uniqueWinPositions[$pos3] = true;
                }
            }

            // Determine multi_time for the last round based on prize_list count
            if ($isLastRound) {
                $multiTime = (count($prize_list) >= 5) ? 10 : 1;  // Set to 10 if 5 or more prizes, else 1
                $lastRoundRates = $totalRate * $multiTime; // Multiply the totalRate by multiTime
                

                foreach ($prize_list as &$prize) {
                    $prize['multi_time'] = $multiTime; // Set uniform multi_time for all entries
                    if ($multiTime == 10) {
                        $prize['win'] *= $multiTime;  // Adjust the win amount if multi_time is 10
                    }
                }

                $totalWin *= $multiTime;  // Adjust totalWin if multi_time is 10
                $totalWin_s = number_format($totalWin / 10000, 2, '.', ''); // Correct formatting of the total win string

            } else {
                $multiTime = 0;  // If not the last round, multi_time is 0
            }

            // Set all_win_item based on multi_time condition
            $allWinItem = ($multiTime >= 10) ? $free_index : 0;
    
            // Remove non-matching winnings for the first round
            if ($roundIndex == 0) {
                $prize_list = array_values(array_filter($prize_list, function($prize) use ($free_index) {
                    return in_array($free_index, $prize['win_item_list']);
                }));
                $player_win_lose_s = number_format(-$bet / 10000, 2 , '.', '');
            } else {
                $player_win_lose_s = '';  // For other rounds, keep it empty or as needed
                $lastBet = $isLastRound ? $bet : 0;
            }

            // Convert unique positions array keys to simple array and sort it if it's the last round
            $winPosList = $totalRounds == $isLastRound ? array_keys($uniqueWinPositions) : [];
            sort($winPosList);

            $totalWinNo = count($winningLines);
            
            $playerWinLose_int = $totalWin - $bet;

            $last_winLose_s = number_format($playerWinLose_int / 10000, 2, '.', '');

            $afterBalance = $balance + $totalWin;

            $afterBalance_s = number_format($afterBalance / 10000, 2, '.', '');

            $roundData = 
                [
                    'item_type_list' => $item_type_list,
                    'round_rate' => $isLastRound ? $totalRate : 0, //sum of all the rate inside the prize_list in the last round
                    'round' => $roundCounter,
                    'multi_time' => $multiTime, //the multi_time from the last prize list.
                    'prize_list' => $prize_list,
                    'next_list' => null, 
                    'drop_list' => null,
                    'win_pos_list' =>  $isLastRound ? $winPosList : null,
                    'balance' => $isLastRound ? $afterBalance : $balance,
                    'free_play' => 0,
                    'win' => $isLastRound ? $totalWin : 0, //sum of the win amount in the prize_list
                    'free_mode_type' => 1,
                    'item_type_list_append' => $item_type_list_appends[$roundIndex],
                    'all_win_item' => $allWinItem,
                    'balance_s' => $isLastRound ? $afterBalance_s : $balance_s,
                    'win_s' => $isLastRound ? $totalWin_s : "0.00",
                    'round_id' => $this->generateRoundID(11),
                    'player_win_lose_s' => $isLastRound ? $last_winLose_s : $player_win_lose_s,
                    'total_bet' => $bet
                ];
            $allRoundData[] = $roundData;  // Accumulate data for each round
        }

        $gameResult = [
            "round_list" => $allRoundData,
            "rate" => $lastRoundRates //Sum of all the rate inside the last round's prize_list
        ];

        

        $results = [
            'error_code' => 0,
            'data' => [
                "result" => $gameResult,
                "round_id" => $this->generateRoundID(11),
                "order_id" => $this->generateOrderId(),
                "balance" => $afterBalance, //after balance
                "bet" => $bet, //bet amount
                "prize" => $totalWin, //win amount
                "player_win_lose" => $totalWin - $bet, //player win lose deduct their bet amount
                "is_enter_free_game" => true,
                "chooseItem" => $free_index,
                "balance_s" => $afterBalance_s, //after balance in string format
                "bet_s" => number_format($bet / 10000, 2, '.', ''), // bet amount in string format
                "prize_s" => number_format($totalWin / 10000, 2, '.', ''), // prize amount in string format
                "player_win_lose_s" => number_format(($totalWin - $bet) / 10000, 2, '.', ''), // player win lose in string format
                "free_game_total_win_s" => "0.00", //this is always 0
                "dbg" => []
            ],
            "req" => [
                "token" => $token,
                "id" => $betOption,
                "idempotent" => "1712935039095"
            ]
        ];

        
        //Store History List Data
        $this->storeHistory($results);

        //Store Round List 
        $this->storeRoundList($results, $pre);

        //Store Prize List
        $this->storePrizeList($results, $prize_list);

        $player->balance += $totalWin; // Add win amount to player's balance
        $player->save();
    
        return $results;
    }
    
    private function generatePrizeInfo($pos1, $pos2, $pos3, $item_type_list, $winLineKey, $free_index, $winLineKeyCount, $initialBet, $isLastRound, $totalRounds) {

        // Static variable to hold the count across function calls
        static $cumulativeWinCount = 0; 
        $itemType1 = $item_type_list[$pos1];
        $itemType2 = $item_type_list[$pos2];
        $itemType3 = $item_type_list[$pos3];
        $symbolMultiplier = isset($this->symbolsPaytable[$free_index]) ? $this->symbolsPaytable[$free_index]["multiplier"] : 0;
        $item_type_s = $this->symbolsPaytable[$free_index]["identifier"] ?? 'Unknown Type';
        $rate = $symbolMultiplier * 5;
        $winAmount = $initialBet * $symbolMultiplier * $winLineKeyCount;


        $cumulativeWinCount += ($winLineKeyCount == 1 ? 1 : 0);  // Assuming $winLineKeyCount == 1 means a win condition

        // Adjust multi_time based on the isLastRound and winLineKeyCount conditions
        $multiTime = $isLastRound ? ($cumulativeWinCount >= 5 ? 10 : 1) : 0;

         // Multiplied win amount based on multi_time
        $multipliedWinAmount = $winAmount * 1;

        $specialWin_str = 
        
        $win_str = number_format($multipliedWinAmount / 10000, 2, '.', '');
        $prize_info = [
            'win_pos_list' => $isLastRound ? [$pos1, $pos2, $pos3] : null,
            'index' => $winLineKey,
            'level' => 3,
            'item_type' => $free_index,
            'rate' => $isLastRound ? $rate : 0,
            'win_item_list' => [$itemType1, $itemType2, $itemType3],
            'multi_time' => $multiTime,
            'win' =>  $isLastRound ? $multipliedWinAmount : 0,
            'level_s' => "3星连珠",
            'item_type_s' => $item_type_s,
            'rate_s' => "图标倍数" . $rate,
            'multi_time_s' => "",
            'win_s' => $isLastRound ? $win_str : "",
        ];
    
        return $prize_info;
    }
    
    

    public function first_spin_must_win_list($must_win_index, $free_index)
    {
        $winLines = [[1, 4, 7], [0, 3, 6], [2, 5, 8], [0, 4, 8], [2, 4, 6]];

        $winLinesIndexList = $winLines[$must_win_index];
        $arr = [];
        $wild_num = 0;

        for ($i = 0; $i < 9; $i++) {
            $r_num = $this->returnMustWin($i, $winLinesIndexList, $free_index);
            if ($r_num == 1) {
                $wild_num++;
                if ($wild_num > 2) {
                    $r_num = $free_index;
                }
            }
            $arr[] = $r_num;
        }
        return $arr;
    }

    public function returnMustWin($index, $winLinesIndexList, $free_index)
    {
        $num = 0;
        $win_list = [1, $free_index];
        foreach ($winLinesIndexList as $value) {
            if ($index == $value) {
                $r_num = rand(0, 1);
                $num = $win_list[$r_num];
            }
        }
        return $num;
    }

    protected function evaluateSpin($reels, $betAmount, $token, $player, $betOption) {
        $totalWin = 0;
        $winningResults = [];
        $prizeList = [];
        $allWinPosList = [];
        $roundId = $this->generateRoundID(11);
        $orderId = $this->generateOrderId();
        $multi_time = 1; // Default value

        // Additional variables for special conditions
        $allLinesWin = true;
        $wildAndOneOtherSymbol = false;
        $allWilds = true;

        foreach ($this->winLines as $index => $line) {
            $symbolsInLine = array_map(function ($position) use ($reels) {
                return $reels[$position[1]][$position[0]];
            }, $line);

            $winInfo = $this->getWinMultiplier($symbolsInLine);

            if ($winInfo["multiplier"] > 0) {
                // Calculate the win for this line
                $lineWin = $winInfo["multiplier"] * $betAmount;

                // Add to total win
                $totalWin += $lineWin;

                $winPosList = array_map(function ($pos) {
                    return $pos[0] * 3 + $pos[1];
                }, $line);

                // Accumulate winning positions
                $allWinPosList = array_merge($allWinPosList, $winPosList);

                $winItemList = array_map(function ($position) use ($reels) {
                    return $reels[$position[1]][$position[0]]; // Fetches the symbol number
                }, $line);

                $winningResults[] = [
                    "index" => $index + 1,
                    "symbol" => $winInfo["identifier"],
                    "multiplier" => $winInfo["multiplier"],
                    "item_type" => $winInfo["symbolNumber"],
                    "win_pos_list" => $winPosList,
                ];

                $prizeList[] = [
                    "win_pos_list" => $winPosList,
                    "index" => $index + 1,
                    "level" => 3, // Index always = 3
                    "item_type" => $winInfo["symbolNumber"],
                    "rate" => $winInfo["multiplier"] * 5, // Example calculation
                    "win_item_list" => $winItemList,
                    "multi_time" => $multi_time, // Example static value
                    "win" => $lineWin * 10000, // The calculated win amount for this line
                    "level_s" => "3星连珠",
                    "item_type_s" => $winInfo["identifier"],
                    "rate_s" => "图标倍数" . $winInfo["multiplier"] * 5,
                    "multi_time_s" => "",
                    "win_s" => $lineWin,
                ];
            } else {
                $allLinesWin = false;
            }

            // Check for all WILDs or mix of WILD and one other symbol
            $nonWildSymbols = array_filter($symbolsInLine, function ($symbol) {
                return $symbol !== 1;
            });
            if (!empty($nonWildSymbols)) {
                $allWilds = false; // If there's at least one non-WILD, it's not all WILDs
                if (count(array_unique($nonWildSymbols)) == 1) {
                    $wildAndOneOtherSymbol = true;
                }
            }
        }

        // After processing all win lines and determining multi_time...
        if ($multi_time > 1) {
            // Adjust the total win based on multi_time
            $totalWin *= $multi_time;
        }

        // Calculate player's win or lose amount
        $playerWinLose = $totalWin - $betAmount; // Adjust calculation as needed

        $win_s = number_format($totalWin, 2, ".", "");

        // Format the result to a string with 2 decimal places
        $playerWinLose_s = $playerWinLose * 10000; // Multiply by 10000 if your base unit is smaller, like cents

        $playerWinLose_str = number_format($playerWinLose, 2, ".", "");

        // Check if prizeList is empty and set it to null if so
        if (empty($prizeList)) {
            $prizeList = null;
        }

        // Make allWinPosList unique after accumulating all positions
        $allWinPosList = array_values(array_unique($allWinPosList));

        if (count($allWinPosList) == 9) {
            $multi_time = 10; // Apply the 10x multiplier when all reels contribute to a win
            $totalWin *= $multi_time;
            $playerWinLose = $totalWin - $betAmount;
            $playerWinLose_str = number_format($playerWinLose, 2, ".", "");
            $playerWinLose_s = $totalWin - $betAmount;
            $win_s *= $multi_time;

            foreach ($prizeList as &$prize) {
                // Assuming prize['win'] is already in the correct format and just needs multiplication
                $prizeWinAmount = $prize["win"] / 10000; // Convert back if necessary for formatting
                $prize["win_s"] = number_format($prizeWinAmount, 2, ".", ""); // Re-format win_s to make it string instead of integer
                $prize["multi_time"] = $multi_time; // Update multi_time to 10
            }
            unset($prize); // Break the reference with the last element
        }

        //Check if allwinPosList is empty and set it to null if so
        if (empty($allWinPosList)) {
            $allWinPosList = null;
        }

        // Adjust player balance for the win, if any
        if ($totalWin > 0) {
            // Assuming $totalWin is already in the correct unit; adjust if necessary
            $player->balance += $totalWin * 10000; // Add win amount to player's balance
            $player->save(); // Persist the updated balance to the database
        }

        //Player balance in the description
        $balance_s = number_format($player["balance"] / 10000, 2, ".", "");

        return [
            "error_code" => 0,
            "data" => [
                "result" => [
                    "round_list" => [
                        [
                            "item_type_list" => array_merge(...$reels), //get from spin result
                            "round_rate" => $totalWin * 10, //divide by 10 = win amount
                            "round" => 1, //Set as default = 1
                            "multi_time" => $multi_time, //Usually is 1, but if free spin or normal match all results then only is 10
                            //List of Win Results
                            "prize_list" => $prizeList,
                            "next_list" => null,
                            "drop_list" => null,
                            "win_pos_list" => $allWinPosList,
                            "balance" => $player["balance"], //Need to change
                            "free_play" => 0,
                            "win" => $totalWin * 10000,
                            "free_mode_type" => 0,
                            "item_type_list_append" => null, // This is the function to override the free spin results, only used for free spin.
                            "all_win_item" => 0, //Assign a type of symbol that you want to spread all of the columns, for e.g if put 2, it will fill in symbol 2 in all 9 columns.
                            "balance_s" => $balance_s, //Player balance in the description
                            "win_s" => $win_s, //User exact win amount
                            "round_id" => $roundId,
                            "player_win_lose_s" => $playerWinLose_s, //Need to handle.
                            "total_bet" => $betAmount * 10000,
                        ],
                    ],
                    "rate" => $totalWin * 10,
                ],

                "round_id" => $roundId,
                "order_id" => $orderId,
                "balance" => $player["balance"],
                "bet" => $betAmount * 10000,
                "prize" => $totalWin * 10000,
                "player_win_lose" => $playerWinLose_s,
                "is_enter_free_game" => false,
                "chooseItem" => 0, //Choose the item that is winning in free game mode, by default it should be 0 if it's not free mode. If it's free mode then this is the symbol that is going to pay the user, max.symbol is 7.
                "balance_s" => $balance_s, //Player balance in the description
                "bet_s" => $betAmount,
                "prize_s" => $win_s,
                "player_win_lose_s" => $playerWinLose_str,
                "free_game_total_win_s" => "0.00",
                "dbg" => [],
            ],
            "req" => [
                "token" => $token,
                "id" => $betOption,
                "idempotent" => "1708350389492", //handle in the future as signed key
            ],
        ];
    }

    protected function storeHistory($result) {
        $orderId = $result["data"]["order_id"];
        $roundId = $result["data"]["round_id"];
        $win_s = $result["data"]["player_win_lose_s"];
        $bet_s = $result["data"]["bet_s"];
        $prize_s = $result["data"]["prize_s"];
        $balance_s = $result["data"]["balance_s"];
        $free_times = 0; //Free mode multiplier
        $free_mode_type = 0;
        $normal_round_times = count($result['data']['result']['round_list']);
        $free_round_times = 0;
        $bet = $result["data"]["bet"];
        $win = $result["data"]["player_win_lose"];
        $token = $result["req"]["token"];
        $prize = $result["data"]["prize"];
        $balance = $result["data"]["balance"];
        $balanceBeforeScore = $balance - $prize + $bet;
        $betOption = $result["req"]["id"];
        $betOptionRecord = TigerBetList::where("id", $betOption)->first();
        $betSize = $betOptionRecord ? $betOptionRecord->bet_size : null;
        $basicBet = $betOptionRecord ? $betOptionRecord->basic_bet : null;
        $betMultiple = $betOptionRecord ? $betOptionRecord->bet_multiple : null;
        $freeGameTotalWin = 0;
    
        TigerHistory::create([
            "order_id" => $orderId,
            "round_id" => $roundId,
            "win_s" => $win_s,
            "bet_s" => $bet_s,
            "free_times" => $free_times,
            "free_mode_type" => 0,
            "create_timestamp" => round(microtime(true) * 1000),
            "free" => 0,
            "normal_round_times" => $normal_round_times,
            "free_round_times" => $free_round_times,
            "bet" => $bet,
            "win" => $win,
            "token" => $token,
        ]);
    }
    
    protected function storeRoundList($result, $pre) {
        $prize = $result["data"]["prize"];
        $orderId = $result["data"]["order_id"];
        $balance = $result["data"]["balance"];
        $bet = $result["data"]["bet"];
        $balanceBeforeScore = $balance - $prize + $bet;
        $betOption = $result["req"]["id"];
        $betOptionRecord = TigerBetList::where("id", $betOption)->first();
        $betSize = $betOptionRecord ? $betOptionRecord->bet_size : null;
        $basicBet = $betOptionRecord ? $betOptionRecord->basic_bet : null;
        $betMultiple = $betOptionRecord ? $betOptionRecord->bet_multiple : null;
        $freeGameTotalWin = 0;
        $balancebefore = $pre;
    
        foreach ($result['data']['result']['round_list'] as $round) {
            $freeGameTotalWin += $round['win'];
            $bet_s = number_format($round['total_bet'] / 10000, 2, '.', '');
            $playerWinLose = floatval($round['player_win_lose_s']) * 10000;
            $balancebefore_s = number_format($balancebefore / 10000, 2, '.', '');

            TigerRoundList::create([
                "order_id" => $orderId,
                "round_id" => $round['round_id'],
                "bet_s" => $bet_s,
                "prize_s" => $round['win_s'],
                "free_mode_type" => 0,
                "player_win_lose_s" => $round['player_win_lose_s'],
                "balance_s" => $round['balance_s'],
                "balance_before_score_s" => $balancebefore_s, // Assuming this value is available in your data
                "create_timestamp" => round(microtime(true) * 1000),
                "bet" => $round['total_bet'], // Assuming this value is available in your data
                "item_type_list" => json_encode($round['item_type_list']),
                "prize" => $round['win'],
                "player_win_lose" => $playerWinLose, // Assuming this value is available in your data
                "balance" => $round['balance'],
                "balance_before_score" => $balancebefore, // Assuming this value is available in your data
                "free" => false,
                "free_total_times" => 0,
                "free_remain_times" => 0,
                "free_game_total_win" => $freeGameTotalWin, // Add the prize of each round to accumulate the total win
                "bet_size" => $betSize, // Assuming this value is available in your data
                "basic_bet" => $basicBet, // Assuming this value is available in your data
                "bet_multiple" => $betMultiple, // Assuming this value is available in your data
                "round_list_count" =>  $round['round'], // Assuming you have already obtained the count of round_list
            ]);
        }
    }
    
    protected function storePrizeList($results, $prizeData) {
        $orderId = $results['data']['order_id'];
        
        foreach ($results['data']['result']['round_list'] as $round) {
            $round_id = $round['round_id'];
            foreach ($round['prize_list'] as $prize) {
    
                PrizeList::create([
                    "round_id" => $round_id,
                    "order_id" => $orderId,
                    "rate" => $prize["rate"],
                    "win_item_list" => json_encode($prize["win_item_list"]),
                    "win_pos_list" => json_encode($prize["win_pos_list"]),
                    "multi_time" => $prize["multi_time"],
                    "win" => $prize["win"],
                    "index" => $prize["index"],
                    "level" => $prize["level"],
                    "level_s" => $prize["level_s"],
                    "item_type" => $prize["item_type"],
                    "item_type_s" => $prize["item_type_s"],
                    "rate_s" => $prize["rate_s"],
                    "multi_time_s" => $prize["multi_time_s"],
                    "win_s" => $prize["win_s"],
                ]);
            }
        }
    }

    protected function storeSpinResult($result)
    {
        //Store as list in the history table
        $orderId = $result["data"]["order_id"];
        $roundId = $result["data"]["round_id"];
        $win_s = $result["data"]["player_win_lose_s"];
        $bet_s = number_format($result["data"]["bet_s"], 2, '.', '');
        $prize_s = $result["data"]["prize_s"];
        $balance_s = $result["data"]["balance_s"];
        $free_times = 0; //Free mode multiplier
        $free_mode_type = 0;
        $normal_round_times = 0;
        $free_round_times = 0;
        $bet = $result["data"]["bet"];
        $win = $result["data"]["player_win_lose"];
        $token = $result["req"]["token"];
        $prize = $result["data"]["prize"];
        $balance = $result["data"]["balance"];
        $balanceBeforeScore = $balance - $prize + $bet;
        $betOption = $result["req"]["id"];
        $betOptionRecord = TigerBetList::where("id", $betOption)->first();
        $betSize = $betOptionRecord ? $betOptionRecord->bet_size : null;
        $basicBet = $betOptionRecord ? $betOptionRecord->basic_bet : null;
        $betMultiple = $betOptionRecord ? $betOptionRecord->bet_multiple : null;

        // Assuming there's always at least one round and you're interested in the first one
        $roundData = $result["data"]["result"]["round_list"][0]; // Access the first round

        $itemTypeListJson = json_encode($roundData["item_type_list"]);

        TigerHistory::create([
            "order_id" => $orderId,
            "round_id" => $roundId,
            "win_s" => $win_s,
            "bet_s" => $bet_s,
            "free_times" => $free_times,
            "free_mode_type" => $free_mode_type,
            "create_timestamp" => round(microtime(true) * 1000),
            "free" => 0,
            "normal_round_times" => $normal_round_times,
            "free_round_times" => $free_round_times,
            "bet" => $bet,
            "win" => $win,
            "token" => $token,
        ]);

        TigerRoundList::create([
            "order_id" => $orderId,
            "round_id" => $roundId,
            "bet_s" => $bet_s,
            "prize_s" => $prize_s,
            "free_mode_type" => $free_mode_type,
            "player_win_lose_s" => $win_s,
            "balance_s" => $balance_s,
            "balance_before_score_s" => $balanceBeforeScore,
            "create_timestamp" => round(microtime(true) * 1000),
            "bet" => $bet,
            "item_type_list" => $itemTypeListJson,
            "prize" => $prize,
            "player_win_lose" => $win, //Player_win_lose_s in the response
            "balance" => $balance,
            "balance_before_score" => $balanceBeforeScore,
            "free" => false,
            "free_total_times" => 0,
            "free_remain_times" => 0,
            "free_game_total_win" => 0,
            "bet_size" => $betSize,
            "basic_bet" => $basicBet,
            "bet_multiple" => $betMultiple,
            "round_list_count" => 1,
        ]);

        if (!empty($roundData["prize_list"])) {
            // Check if 'prize_list' is not empty
            $prizeData = $roundData["prize_list"]; // Now you have the prize_list from the first round

            foreach ($prizeData as $prize) {
                // Process each prize
                PrizeList::create([
                    // Map your prize attributes here
                    "round_id" => $roundId,
                    "order_id" => $orderId,
                    "rate" => $prize["rate"],
                    "win_item_list" => json_encode($prize["win_item_list"]),
                    "win_pos_list" => json_encode($prize["win_pos_list"]),
                    "multi_time" => $prize["multi_time"],
                    "win" => $prize["win"],
                    "index" => $prize["index"],
                    "level" => $prize["level"],
                    "level_s" => $prize["level_s"],
                    "item_type" => $prize["item_type"],
                    "item_type_s" => $prize["item_type_s"],
                    "rate_s" => $prize["rate_s"],
                    "multi_time_s" => $prize["multi_time_s"],
                    "win_s" => $prize["win_s"],
                ]);
            }
        }

        return $result;
    }

    protected function getWinMultiplier($symbolsInLine)
    {
        // Initialize default win info
        $winInfo = [
            "multiplier" => 0,
            "identifier" => null,
            "symbolNumber" => null,
        ];

        // Check if all symbols are WILD or the same non-WILD symbol
        $uniqueSymbols = array_unique($symbolsInLine);
        if (count($uniqueSymbols) === 1) {
            $symbolNumber = reset($uniqueSymbols);
            return [
                "multiplier" =>
                    $this->symbolsPaytable[$symbolNumber]["multiplier"],
                "identifier" =>
                    $this->symbolsPaytable[$symbolNumber]["identifier"],
                "symbolNumber" => $symbolNumber, // Store the symbol number
            ];
        }

        // If there are WILDs, check if the rest of the symbols are the same
        if (in_array(1, $symbolsInLine)) {
            $nonWildSymbols = array_filter($symbolsInLine, function ($symbol) {
                return $symbol !== 1; // Exclude WILD symbols
            });

            if (count(array_unique($nonWildSymbols)) === 1) {
                $symbolNumber = reset($nonWildSymbols);
                return [
                    "multiplier" =>
                        $this->symbolsPaytable[$symbolNumber]["multiplier"],
                    "identifier" =>
                        $this->symbolsPaytable[$symbolNumber]["identifier"],
                    "symbolNumber" => $symbolNumber, // Store the symbol number
                ];
            }
        }

        return $winInfo; // No win
    }

    protected function isWinningLine($symbolsInLine)
    {
        $firstSymbol = $symbolsInLine[0];
        foreach ($symbolsInLine as $symbol) {
            if ($symbol !== $firstSymbol) {
                return false;
            }
        }
        return true;
    }

    // Function to generate order ID
    protected function generateOrderId()
    {
        $prefix = "9"; // Prefix for order ID
        $timestamp = time(); // Current timestamp
        $randomString = $this->generateRandomString(9); // Random alphanumeric string
        return "{$prefix}-{$timestamp}-{$randomString}";
    }

    //Function to generate round Id
    private function generateRandomString($length)
    {
        $characters =
            "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $randomString = "";
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    private function generateRoundID($length) 
    {
        $characters =
        "0123456789";
        $randomString = "";
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    // Helper function to simulate winning line detection
    private function checkForWinningLine($symbols)
    {
        // Provided winning lines
        $winLines = [
            [[1, 0], [1, 1], [1, 2]],
            [[0, 0], [0, 1], [0, 2]],
            [[2, 0], [2, 1], [2, 2]],
            [[0, 0], [1, 1], [2, 2]],
            [[0, 2], [1, 1], [2, 0]],
        ];

        // Check each winning line
        foreach ($winLines as $winLine) {
            $symbolsInLine = [];
            foreach ($winLine as $pos) {
                $symbol = $symbols[$pos[0]][$pos[1]];
                $symbolsInLine[] = $symbol;
            }
            // If all symbols in the line are the same, it's a winning line
            if (count(array_unique($symbolsInLine)) === 1) {
                return $symbolsInLine; // Return winning symbols
            }
        }

        return []; // No winning line detected
    }

}
