<!DOCTYPE html>
<html lang="en" dir="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - RATE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center">
    <div class="bg-white/5 p-10 rounded-xl max-w-md w-full border border-white/10">
        <h2 class="text-2xl font-bold mb-2">Choose a new password</h2>
        <p class="text-white/70 mb-6">Enter a strong password and confirm it to update your account.</p>

        @if(session('status'))
            <div class="mb-4 p-3 bg-green-800 text-green-200 rounded">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" id="resetForm">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            @if(config('services.recaptcha.site_key'))
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            @endif

            <div class="mb-4">
                <label class="block text-sm mb-2">Email</label>
                <input type="email" name="email" required class="w-full p-3 bg-white/10 rounded border border-white/20" value="{{ $email ?? old('email') }}">
                @error('email') <p class="text-sm text-rate-red mt-2">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-2">New password</label>
                <input type="password" name="password" required class="w-full p-3 bg-white/10 rounded border border-white/20">
                @error('password') <p class="text-sm text-rate-red mt-2">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-2">Confirm password</label>
                <input type="password" name="password_confirmation" required class="w-full p-3 bg-white/10 rounded border border-white/20">
            </div>

            <button type="submit" class="w-full p-3 bg-rate-red text-white rounded">Reset password</button>
        </form>
        @if($errors->has('recaptcha'))
            <x-recaptcha-alert :error="$errors->first('recaptcha')" />
        @endif
    </div>
</body>
</html>
@if(config('services.recaptcha.site_key'))
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    <script>
        (function(){
            const siteKey = '{{ config('services.recaptcha.site_key') }}';
            if(!siteKey) return;
            const form = document.getElementById('resetForm');
            if(!form) return;
            form.addEventListener('submit', function(e){
                e.preventDefault();
                grecaptcha.ready(function(){
                    grecaptcha.execute(siteKey, {action: 'reset_password'}).then(function(token){
                        const el = document.getElementById('g-recaptcha-response');
                        if(el) el.value = token;
                        form.submit();
                    }).catch(function(){ form.submit(); });
                });
            });
        })();
    </script>
@endif
