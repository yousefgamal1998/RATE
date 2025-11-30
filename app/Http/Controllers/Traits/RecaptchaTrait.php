<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

trait RecaptchaTrait
{
    /**
     * Verify recaptcha token using Google siteverify and return structured result.
     * Returns ['success' => bool, 'score' => float|null, 'action' => string|null]
     */
    protected function verifyRecaptcha($token, $secret, $ip = null)
    {
        if (! $token) return ['success' => false];

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ])->json();
        } catch (\Exception $e) {
            Log::channel('recaptcha')->warning('reCAPTCHA verification request failed', ['error' => $e->getMessage()]);
            return ['success' => false];
        }

        if (! (isset($response['success']) && $response['success'] == true)) {
            return ['success' => false, 'raw' => $response];
        }

        $result = ['success' => true];
        if (isset($response['score'])) {
            $result['score'] = (float) $response['score'];
        }
        if (isset($response['action'])) {
            $result['action'] = $response['action'];
        }

        return $result;
    }

    /**
     * Increment recaptcha failure counters and send alerts if threshold exceeded.
     * Keeps behavior compatible with existing RegisterController implementation.
     */
    protected function maybeSendRecaptchaAlerts($email, $ip, $score = null, $action = null)
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
