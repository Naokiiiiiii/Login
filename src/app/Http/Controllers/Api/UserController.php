<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * ユーザー作成.
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request) {
        $member = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return response()->json($member);
    }

}
