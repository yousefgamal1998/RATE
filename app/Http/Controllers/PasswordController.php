<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Controllers\Traits\RecaptchaTrait;

class PasswordController extends Controller
{
    use RecaptchaTrait;
    // Show the form to request a password reset link
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    // Handle sending the reset link email
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // If reCAPTCHA configured, verify token
        $recaptchaSecret = config('services.recaptcha.secret');
        if ($recaptchaSecret) {
            $token = $request->input('g-recaptcha-response');
            $recap = $this->verifyRecaptcha($token, $recaptchaSecret, $request->ip());
            $threshold = config('services.recaptcha.threshold', 0.5);
            $score = $recap['score'] ?? null;

            if (! ($recap['success'] ?? false) || ($score !== null && $score < (float)$threshold)) {
                Log::channel('recaptcha')->warning('reCAPTCHA failed for password reset request', [
                    'email' => $request->input('email'),
                    'ip' => $request->ip(),
                    'score' => $score,
                    'action' => $recap['action'] ?? null,
                ]);

                $this->maybeSendRecaptchaAlerts($request->input('email'), $request->ip(), $score, $recap['action'] ?? null);

                return back()->withErrors(['recaptcha' => 'reCAPTCHA verification failed.'])->withInput();
            }
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    // Show the reset form
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->query('email') ?? old('email'),
        ]);
    }

    // Handle the reset
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return back()->withErrors(['email' => [__($status)]]);
    }
}
