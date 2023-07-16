<?php

namespace App\Http\Controllers\Api;

use App\Mail\PreRegister;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use \Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class SigninController extends Controller
{
    const SEND_MAIL = 0;   // 仮会員登録のメール送信
    const MAIL_VERIFY = 1; //メールアドレス認証
    const REGISTER = 2;    // 本会員登録完了

    public function storeValidMail(
      Request $request,
      EmailVerification $emailVerification
    )
    {
      $email = $request->email;
      $token = Hash::make($email);

      try {
        $emailVerification = EmailVerification::firstOrCreate([
          'email' => $email,
        ], [
          'token' => $token,
          'status' => self::SEND_MAIL,
          'expiration_datetime' => Carbon::now()->addHours(1),
        ]);
      } catch(\Throwable $e) {
        \Log::error($e);
        throw $e;
      }

      Mail::to($email)->send(new PreRegister($token));
      return response()->json(['result' => true], 200);
    }

    public function signin(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verify' => false,
        ]);

        return response()->json('User registration completed', Response::HTTP_OK);
    }
}