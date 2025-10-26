<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;
use App\Http\Controllers\Traits\RecaptchaTrait;

class AuthController extends Controller
{
    use RecaptchaTrait;
    public function __construct()
    {
        // السماح فقط للضيوف بالوصول إلى login/signup
        // والسماح للمستخدمين المسجلين بتسجيل الخروج فقط
        $this->middleware('guest')->except('logout');
    }

    // عرض صفحة تسجيل الدخول
    public function showLogin()
    {
        return view('login');
    }

    // تنفيذ عملية تسجيل الدخول
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        // If reCAPTCHA secret is configured, verify before attempting login
        $recaptchaSecret = config('services.recaptcha.secret');
        if ($recaptchaSecret) {
            $token = $request->input('g-recaptcha-response');
            $recap = $this->verifyRecaptcha($token, $recaptchaSecret, $request->ip());
            $threshold = config('services.recaptcha.threshold', 0.5);
            $score = $recap['score'] ?? null;

            if (! ($recap['success'] ?? false) || ($score !== null && $score < (float)$threshold)) {
                Log::channel('recaptcha')->warning('reCAPTCHA failed for login', [
                    'email' => $data['email'],
                    'ip' => $request->ip(),
                    'score' => $score,
                    'action' => $recap['action'] ?? null,
                ]);

                // increment counters and possibly alert
                $this->maybeSendRecaptchaAlerts($data['email'], $request->ip(), $score, $recap['action'] ?? null);

                return back()->withErrors(['recaptcha' => 'reCAPTCHA verification failed.'])->withInput();
            }
        }

        // trim inputs to avoid leading/trailing spaces from browsers/autofill
        $email = trim($data['email']);
        $password = is_string($data['password']) ? trim($data['password']) : $data['password'];

        // log attempt (do NOT log the plain password)
        Log::info('Login attempt', ['email' => $email, 'ip' => $request->ip()]);

        // Check user exists
        $user = User::where('email', $email)->first();
        if (! $user) {
            Log::warning('Login failed - user not found', ['email' => $email]);
            return back()->withErrors([
                'email' => 'هذا البريد الإلكتروني غير مسجل في النظام.'
            ])->withInput();
        }

        // Ensure password verification uses Hash::check() only (do NOT call Hash::make or bcrypt)
        $hashPresent = ! empty($user->password);
        $hashCheck = false;
        if ($hashPresent) {
            $hashCheck = Hash::check($password, $user->password);
        }

        // Log diagnostic booleans (never log the plain password)
        Log::info('Password diagnostic', ['email' => $email, 'hash_present' => $hashPresent, 'hash_check' => $hashCheck]);

        // If password doesn't match, return an error (do not reveal which part failed)
        if (! $hashCheck) {
            Log::warning('Login failed - bad credentials', ['email' => $email, 'ip' => $request->ip()]);
            return back()->withErrors([
                'password' => 'بيانات الدخول غير صحيحة.'
            ])->withInput();
        }

        // Password is correct -> log the user in without using Auth::attempt()
        $remember = $request->boolean('remember');
        Auth::login($user, $remember);

        // security: prevent session fixation and regenerate CSRF token
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        Log::info('User logged in via Auth::login', ['email' => $email]);
        $request->session()->flash('status', __('auth.login_success'));
        return redirect()->route('dashboard');
    }

    // تنفيذ عملية تسجيل الخروج
    public function logout(Request $request)
    {
        // Logout and invalidate session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Remove remember me cookie if present. Laravel's remember cookie name starts with 'remember_'
        // Iterate cookies and forget those starting with 'remember_'
        foreach ($request->cookies->keys() as $name) {
            if (stripos($name, 'remember_') === 0) {
                Cookie::queue(Cookie::forget($name));
            }
        }

    // After logout, redirect user to the login page
    return redirect()->route('login');
    }

    // عرض صفحة التسجيل
    public function showSignup()
    {
        return view('signup');
    }

    // تنفيذ عملية إنشاء حساب جديد
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            // password: required, must be a string, minimum 8 characters, and confirmed
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // إنشاء المستخدم — give the plain password to the model so the mutator will hash it
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        // تسجيل الدخول مباشرة بعد التسجيل
        Auth::login($user);
        $request->session()->regenerate();
        // regenerate CSRF token after auth state change
        $request->session()->regenerateToken();

        // بعد التسجيل، توجيه المستخدم إلى لوحة التحكم
        return redirect()->route('dashboard');
    }
}
