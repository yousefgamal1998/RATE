<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // If reCAPTCHA is configured, verify it first and log failures
        $recaptchaSecret = config('services.recaptcha.secret');
        if ($recaptchaSecret) {
            $token = $request->input('g-recaptcha-response');
            $recap = $this->verifyRecaptcha($token, $recaptchaSecret, $request->ip());

            $threshold = config('services.recaptcha.threshold', 0.5);
            $score = $recap['score'] ?? null;
            $action = $recap['action'] ?? null;

            // If verification failed or score below threshold, log and reject
            if (! ($recap['success'] ?? false) || ($score !== null && $score < (float)$threshold)) {
                Log::channel('recaptcha')->warning('reCAPTCHA failed for signup', [
                    'email' => $request->input('email'),
                    'ip' => $request->ip(),
                    'score' => $score,
                    'action' => $action,
                ]);

                // increment counters and maybe alert
                $this->maybeSendRecaptchaAlerts($request->input('email'), $request->ip(), $score, $action);

                return back()->withErrors(['recaptcha' => 'reCAPTCHA verification failed.'])->withInput();
            }
        }

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'phone' => ['nullable','string','max:30'],
            'terms' => ['sometimes','accepted'],
        ]);

        // Create the user (password will be hashed via mutator or explicitly)
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
        ]);

        // Log registration (do not log password)
        Log::info('User registered', ['email' => $user->email, 'id' => $user->id, 'ip' => $request->ip()]);

        // Auto-login and redirect to dashboard
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Verify recaptcha and return details array: ['success' => bool, 'score' => float|null, 'action' => string|null]
     */
    private function verifyRecaptcha($token, $secret, $ip = null)
    {
        if (! $token) return ['success' => false];

        $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secret) . '&response=' . urlencode($token) . ($ip ? '&remoteip=' . urlencode($ip) : ''));
        if (! $response) return ['success' => false];
        $json = json_decode($response, true);
        if (! (isset($json['success']) && $json['success'] == true)) return ['success' => false];

        $result = ['success' => true];
        if (isset($json['score'])) {
            $result['score'] = (float) $json['score'];
        }
        if (isset($json['action'])) {
            $result['action'] = $json['action'];
        }

        return $result;
    }

    // AJAX endpoint to check email availability
    public function checkEmail(Request $request)
    {
        $email = $request->query('email');
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['available' => false, 'message' => 'Invalid email'], 400);
        }

        $exists = \App\Models\User::where('email', $email)->exists();
        return response()->json(['available' => ! $exists]);
    }

    /**
     * Increment recaptcha failure counters and send alerts if threshold exceeded.
     */
    private function maybeSendRecaptchaAlerts($email, $ip, $score = null, $action = null)
    {
        $alerts = config('services.recaptcha.alerts', []);
        $window = (int) ($alerts['window'] ?? 5); // minutes
        $threshold = (int) ($alerts['threshold'] ?? 10);

        $bucket = now()->format('YmdHi');
        $keyTotal = "recaptcha:failures:total:{$bucket}"; // per-minute bucket
        $keyIp = "recaptcha:failures:ip:{$ip}:{$bucket}";

        Cache::increment($keyTotal);
        Cache::increment($keyIp);
        Cache::put($keyTotal, Cache::get($keyTotal), now()->addMinutes($window));
        Cache::put($keyIp, Cache::get($keyIp), now()->addMinutes($window));

        $total = (int) Cache::get($keyTotal, 0);
        $ipCount = (int) Cache::get($keyIp, 0);

        if ($total >= $threshold || $ipCount >= $threshold) {
            // send Slack webhook if configured
            if (! empty($alerts['slack_webhook'])) {
                try {
                    Http::post($alerts['slack_webhook'], [
                        'text' => "reCAPTCHA alert: {$total} failures in the last {$window} minutes (ip={$ip}, ipCount={$ipCount})",
                    ]);
                } catch (\Exception $e) {
                    Log::channel('recaptcha')->warning('Failed to send Slack alert', ['error' => $e->getMessage()]);
                }
            }

            // send email alert if configured
            if (! empty($alerts['email'])) {
                try {
                    Mail::raw("reCAPTCHA alert: {$total} failures in the last {$window} minutes. IP: {$ip} (count={$ipCount}). Email: {$email}. Score={$score}", function ($message) use ($alerts) {
                        $message->to($alerts['email'])->subject('reCAPTCHA alert — RATE');
                    });
                } catch (\Exception $e) {
                    Log::channel('recaptcha')->warning('Failed to send email alert', ['error' => $e->getMessage()]);
                }
            }

            // reset counters to avoid repeated alerts
            Cache::put($keyTotal, 0, now()->addMinutes($window));
            Cache::put($keyIp, 0, now()->addMinutes($window));
        }
    }
}
