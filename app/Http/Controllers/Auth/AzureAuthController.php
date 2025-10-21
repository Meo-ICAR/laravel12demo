<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AzureAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('microsoft')
            ->scopes(['openid', 'email', 'profile'])
            ->with([
                'response_type' => 'code',
                'prompt' => 'select_account',
            ])
            ->redirect();
    }

    public function callback()
    {
        try {
            $microsoftUser = Socialite::driver('microsoft')->user();

            if (!$microsoftUser->getEmail()) {
                throw new \Exception('No email returned from Microsoft');
            }

            // Get user name with fallback options
            $userName = $microsoftUser->getName() ??
                       $microsoftUser->getNickname() ??
                       $microsoftUser->getEmail();

            $user = User::updateOrCreate(
                ['email' => $microsoftUser->getEmail()],
                [
                    'name' => $userName,
                    'email' => $microsoftUser->getEmail(),
                    'email_verified_at' => now(),
                    'microsoft_id' => $microsoftUser->getId(),
                    'azure_id' => $microsoftUser->getId(),
                    // Set a random password for Microsoft users (they won't use it)
                    'password' => bcrypt(\Illuminate\Support\Str::random(32)),
                ]
            );

            // Log successful authentication
            \Log::info('Microsoft Login Success', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'microsoft_id' => $user->microsoft_id
            ]);

            Auth::login($user, true);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            \Log::error('Microsoft Login Error: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect('/login')
                ->with('error', 'Unable to login using Microsoft. Please try again.');
        }
    }
}
