@extends('')
<!DOCTYPE html>
<html lang="en" dir="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - RATE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center">
    <div class="bg-white/5 p-10 rounded-xl max-w-md w-full border border-white/10">
        <h2 class="text-2xl font-bold mb-2">Reset your password</h2>
        <p class="text-white/70 mb-6">Enter your email and we'll send a secure link to reset your password.</p>

        @if(session('status'))
            <div class="mb-4 p-3 bg-green-800 text-green-200 rounded">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
            @csrf
            @if(config('services.recaptcha.site_key'))
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            @endif
            <div class="mb-4">
                <label class="block text-sm mb-2">Email</label>
                <input type="email" name="email" required class="w-full p-3 bg-white/10 rounded border border-white/20" value="{{ old('email') }}">
                @error('email') <p class="text-sm text-rate-red mt-2">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full p-3 bg-rate-red text-white rounded">Send reset link</button>
        </form>
        {{-- reCAPTCHA friendly notification area (localized) --}}
        @if($errors->has('recaptcha'))
            <x-recaptcha-alert :error="$errors->first('recaptcha')" />
        @endif
    </div>
    @if(config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
        <script>
            (function(){
                const siteKey = '{{ config('services.recaptcha.site_key') }}';
                if(!siteKey) return;
                const setToken = function(token){
                    const el = document.getElementById('g-recaptcha-response');
                    if(el) el.value = token;
                };
                grecaptcha.ready(function(){
                    grecaptcha.execute(siteKey, {action: 'forgot_password'}).then(function(token){ setToken(token); });
                    const form = document.getElementById('forgotForm');
                    if(form){
                        form.addEventListener('submit', function(e){
                            e.preventDefault();
                            grecaptcha.execute(siteKey, {action: 'forgot_password'}).then(function(token){
                                setToken(token);
                                form.submit();
                            }).catch(function(){ form.submit(); });
                        });
                    }
                });
            })();
        </script>
    @endif
</body>
</html>
