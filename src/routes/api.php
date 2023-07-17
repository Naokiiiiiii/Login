<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SigninController;
use App\Http\Controllers\Api\LoginController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/signin', [SigninController::class, 'signin']); // ユーザー登録
Route::post('/login', [LoginController::class, 'login']); // ログイン

Route::post('/pre_register', [SigninController::class, 'storeValidEMail']); // 仮登録
Route::post('/verify_token', [SigninController::class, 'verifyToken']); //仮登録メールアドレス認証
Route::post('/register_user', [SigninController::class, 'registerUser']); //ユーザー登録


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
