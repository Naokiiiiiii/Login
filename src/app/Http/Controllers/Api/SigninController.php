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

    public function storeValidEMail(
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
      return response()->json(['result' => true, 'content' => $emailVerification], 200);
    }

    public function verifyToken(Request $request) {
        $token = $request->token;
        $emailVerification = EmailVerification::findByToken($token);

        // 判定見直す
        $isValid = Carbon::parse($emailVerification->expiration_datetime)->lt(Carbon::now()->format('Y-m-d H:i:s'));

        if (empty($emailVerification) || $emailVerification->isRegister()) {
            return response()->json(['status' => false], 401);
        }

        if (!$isValid) {
            return response()->json(['status' => false], 401);
        }

        try {
            $emailVerification->mailVerify();
            $emailVerification->update();

            return response()->json(['status' => true, 'email' => $emailVerification->email], 200);
        } catch(\Throwable $e) {
            \Log::error($e);
            throw $e;
        }
    }

    public function registerUser(Request $request) {
        $email = $request->email;
        $password = $request->password;
        $emailVerification = EmailVerification::findByEmail($email);

        try {
            $user = User::create([
                'email' => $email,
                'password' => Hash::make($password)
            ]);
            $emailVerification->register();
            $emailVerification->update();

            return response()->json(['status' => true, 'user' => $user], 401);
        } catch(\Throwable $e) {
            \Log::error($e);
            throw $e;
        }
    }
}
