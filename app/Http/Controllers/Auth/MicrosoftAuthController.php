<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MicrosoftAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('microsoft')->redirect();
    }

    public function callback()
    {
        try {
            $microsoftUser = Socialite::driver('microsoft')->user();
            
            $user = User::updateOrCreate(
                ['email' => $microsoftUser->getEmail()],
                [
                    'name' => $microsoftUser->getName() ?? $microsoftUser->getNickname() ?? $microsoftUser->getEmail(),
                    'password' => bcrypt(Str::random(24)),
                    'microsoft_id' => $microsoftUser->getId(),
                ]
            );

            Auth::login($user);

            return redirect('/dashboard');
        } catch (\Exception $e) {
            \Log::error('Microsoft Auth Error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Unable to login using Microsoft. Please try again.');
        }
    }
}
