<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        // Obtener información de usuario desde provider
        $userProvider = Socialite::driver($provider)->user();

        //Validar que el usuario exista
        $user = User::where([
            ['email', '=', $userProvider->email],
            ['social_id', '=', $userProvider->id]
        ])->first();

        if (!$user) {

            $insert = User::create([
                'name' => $userProvider->name,
                'email' => $userProvider->email,
                'password' => bcrypt(
                    substr(
                        str_shuffle(
                            str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)
                        ), 0, 5)
                ),
                'social_id' => $userProvider->id
            ]);

            $user = $insert;
        }

        // Hacer login de usuario
        Auth::login($user);

        return redirect('/');
    }

    public function sign_out()
    {
        Auth::logout();
        return redirect('/');
    }
}
