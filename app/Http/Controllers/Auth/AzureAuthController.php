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
            
            $user = User::updateOrCreate(
                ['email' => $microsoftUser->getEmail()],
                [
                    'name' => $microsoftUser->getName() ?? $microsoftUser->getNickname() ?? $microsoftUser->getEmail(),
                    'email' => $microsoftUser->getEmail(),
                    'email_verified_at' => now(),
                    'microsoft_id' => $microsoftUser->getId(),
                    'azure_id' => $microsoftUser->getId(), // Keep both for backward compatibility
                ]
            );

            Auth::login($user, true);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            \Log::error('Microsoft Login Error: ' . $e->getMessage());
            return redirect('/login')
                ->with('error', 'Unable to login using Microsoft. Please try again. ' . $e->getMessage());
        }
    }
}
