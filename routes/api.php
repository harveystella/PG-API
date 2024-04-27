<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\BetController;
use App\Http\Controllers\API\StimulateController;
use App\Http\Controllers\API\MuteController;
use App\Http\Controllers\API\TigerListController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('v1/hhsc/user/info', [UserController::class, 'getUserInfo']);
Route::post('v1/hhsc/bet/info', [BetController::class, 'getBetInfo']);
Route::post('v1/hhsc/bet/', [StimulateController::class, 'spin']);
Route::post('v1/hhsc/record/list', [TigerListController::class, 'getList']);
Route::post('v1/hhsc/record/detail', [TigerListController::class, 'getDetails']);
//Route::post('v1/hhsc/bet/', [BetController::class, 'spinBet']);
Route::post('v1/hhsc/mute/', [MuteController::class, 'mute']);
Route::post('games/FortuneTiger', [UserController::class, 'gameRequest']);
//Route::get('/simulate-spin', [StimulateController::class, 'spin']);

Route::post('free',[StimulateController::class,'checkReels']);

