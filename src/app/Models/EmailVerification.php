<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class EmailVerification extends Model
{
    use HasFactory;

    const SEND_MAIL = 0;   // 仮会員登録のメール送信
    const MAIL_VERIFY = 1; //メールアドレス認証
    const REGISTER = 2;    // 本会員登録完了

    const EXPIRATION_HOUR = 1;

    protected $fillable = [
        'email',
        'token',
        'status',
        'expiration_datetime',
    ];

    public static function build($email, $token) {
        $emailVerification = new self([
            'email' => $email,
            'token' => $token,
            'status' => self::SEND_MAIL,
            'expiration_datetime' => Carbon::now()->addHours($EXPIRATION_HOUR),
        ]);
        return $emailVerification;
    }

    public static function findByToken($token) {
        return self::where('token', '=', $token)->first();
    }

    public static function findByEmail($email) {
        return self::where('email', '=', $email)->first();
    }

    public function mailVerify() {
        $this->status = self::MAIL_VERIFY;
    }

    public function isRegister() {
        return $this->status === self::REGISTER;
    }

    public function register() {
        $this->status = self::REGISTER;
    }
}
