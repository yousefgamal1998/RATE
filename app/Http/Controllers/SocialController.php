<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Carbon;

class SocialController extends Controller
{
    public function redirect($provider)
    {
        // خزن السائق في متغير لتحسين قراءة الكود ولتجنب تحذيرات IDE/static analyser
        /** @var mixed $driver */
        $driver = Socialite::driver($provider);

        // أطلب صراحة scopes لضمان استرجاع البريد
        if ($provider === 'facebook') {
            // Facebook يتطلب scopes مختلفة عادة
            if (method_exists($driver, 'scopes')) {
                $driver->scopes(['email', 'public_profile']);
            }
            // بعض مزوّدي Facebook يقدّمون fields لتحديد القيم المطلوبة
            if (method_exists($driver, 'fields')) {
                $driver = $driver->fields(['name', 'email', 'picture']);
            }
            // طلب إعادة الموافقة إذا لزم
            if (method_exists($driver, 'with')) {
                $driver = $driver->with(['auth_type' => 'rerequest']);
            }
        } else {
            if (method_exists($driver, 'scopes')) {
                $driver->scopes(['openid', 'profile', 'email']);
            }
        }

        // يمكنك إضافة معطيات إضافية مثل prompt/select_account
        if (method_exists($driver, 'with')) {
            $driver = $driver->with(['prompt' => 'select_account', 'access_type' => 'online']);
        }

        // بعض مزوِّدي OAuth يوفّرون stateless() — نتحقق قبل النداء
        if (method_exists($driver, 'stateless')) {
            $statelessDriver = $driver->stateless();
            return $statelessDriver->redirect();
        }

        return $driver->redirect();
    }

    public function callback(Request $request, $provider)
    {
        /** @var mixed $driver */
        $driver = Socialite::driver($provider);

        try {
            // choose the appropriate driver instance (stateless if available)
            $usedDriver = method_exists($driver, 'stateless') ? $driver->stateless() : $driver;
            $socialUser = $usedDriver->user();
        } catch (\Exception $e) {
            // في حال فشل المصادقة نرسل صفحة تغلق النافذة مع إعلام بالخطأ
            return view('auth.popup-close', ['success' => false, 'message' => 'Authentication failed.']);
        }

        // debug: سجّل المعلومات المستلمة من Google في اللوج
        Log::debug('Social user raw:', (array) $socialUser->user); // يفصل بيانات الـ OAuth الخام
        Log::debug('Social user getEmail: '.$socialUser->getEmail());
        Log::debug('Social user getId: '.$socialUser->getId());

        // بحث عن مستخدم حسب البريد
        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $userData = [
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(16)),
            ];

            try {
                if (Schema::hasColumn('users', 'provider')) {
                    $userData['provider'] = $provider;
                }
                if (Schema::hasColumn('users', 'provider_id')) {
                    $userData['provider_id'] = $socialUser->getId();
                }
                if (Schema::hasColumn('users', 'avatar')) {
                    $userData['avatar'] = $socialUser->getAvatar() ?? null;
                }
            } catch (\Exception $e) {
                // ignore schema check errors during local dev
            }

            $user = User::create($userData);
            // If the OAuth provider verified the email, mark it verified locally
            try {
                $raw = $socialUser->user ?? null;
                if (is_object($raw) && (isset($raw->email_verified) ? $raw->email_verified : (isset($raw->verified_email) ? $raw->verified_email : false))) {
                    $user->email_verified_at = Carbon::now();
                    $user->save();
                }
            } catch (\Exception $e) {
                // non-fatal
            }

            // Send a password reset link so the user can set a password of their choice.
            try {
                Password::broker()->sendResetLink(['email' => $user->email]);
                Log::info('Sent password reset link to social-created user', ['email' => $user->email]);
            } catch (\Exception $e) {
                Log::warning('Failed to send password reset link', ['email' => $user->email, 'error' => $e->getMessage()]);
            }
        } else {
            try {
                $updates = [];
                if (Schema::hasColumn('users', 'provider')) {
                    $updates['provider'] = $provider;
                }
                if (Schema::hasColumn('users', 'provider_id')) {
                    $updates['provider_id'] = $socialUser->getId();
                }
                if (Schema::hasColumn('users', 'avatar')) {
                    $updates['avatar'] = $socialUser->getAvatar() ?? null;
                }
                if (!empty($updates)) {
                    $user->update($updates);
                }
            } catch (\Exception $e) {
                // ignore update errors during local dev
            }
            // If social provider reports verified email, ensure local verification flag is set
            try {
                $raw = $socialUser->user ?? null;
                if (is_object($raw) && (isset($raw->email_verified) ? $raw->email_verified : (isset($raw->verified_email) ? $raw->verified_email : false))) {
                    if (empty($user->email_verified_at)) {
                        $user->email_verified_at = Carbon::now();
                        $user->save();
                    }
                }
            } catch (\Exception $e) {
                // ignore non-fatal
            }
        }

        Auth::login($user, true);

        // Return popup-close view with redirect to dashboard
        return view('auth.popup-close', ['success' => true, 'redirect' => url('/dashboard')]);
    }
}