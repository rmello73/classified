<?php

namespace App\Http\Controllers;

use App\Models\SocialAccountService;
use Laravel\Socialite\Facades\Socialite;

class SocialLogin extends Controller
{
    public function redirectFacebook()
    {
        //dd(url('login/facebook-callback'));
        return Socialite::driver('facebook')->redirect();
    }

    public function callbackFacebook(SocialAccountService $service)
    {
        try {
            $fb_user = Socialite::driver('facebook')->user();
            //dd($fb_user);

            $user = $service->createOrGetFBUser($fb_user);

            auth()->login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect(route('login'))->with('error', trans('app.error_msg'));
        }
    }

    public function redirectGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle(SocialAccountService $service)
    {
        try {
            $fb_user = Socialite::driver('google')->user();
            //dd($fb_user);

            $user = $service->createOrGetGoogleUser($fb_user);
            auth()->login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            //return $e->getMessage();
            return redirect(route('login'))->with('error', trans('app.error_msg'));
        }
    }
}
