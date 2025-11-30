<!DOCTYPE html>
<html lang="en" dir="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account - RATE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .spinner { border: 3px solid rgba(255,255,255,0.1); border-top: 3px solid #fff; border-radius: 50%; width: 18px; height: 18px; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center">
    <div class="bg-white/5 p-10 rounded-xl max-w-md w-full border border-white/10">
        <h2 class="text-2xl font-bold mb-2">Create your account</h2>
        <p class="text-white/70 mb-6">Sign up to start rating movies.</p>

        <form id="registerForm" method="POST" action="{{ route('signup.post') }}">
            @csrf
            @if(config('services.recaptcha.site_key'))
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            @endif
            <div class="mb-4">
                <label class="block text-sm mb-2">Full name</label>
                <input type="text" name="name" id="name" required class="w-full p-3 bg-white/10 rounded border border-white/20" value="{{ old('name') }}">
                @error('name') <p class="text-sm text-rate-red mt-2">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-2">Email</label>
                <input type="email" name="email" id="email" required class="w-full p-3 bg-white/10 rounded border border-white/20" value="{{ old('email') }}">
                <p id="emailFeedback" class="text-sm text-yellow-300 mt-2 hidden"></p>
                @error('email') <p class="text-sm text-rate-red mt-2">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-2">Password</label>
                <input type="password" name="password" id="password" required class="w-full p-3 bg-white/10 rounded border border-white/20">
                <p id="pwFeedback" class="text-sm text-yellow-300 mt-2 hidden"></p>
                @error('password') <p class="text-sm text-rate-red mt-2">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-2">Confirm password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full p-3 bg-white/10 rounded border border-white/20">
            </div>

            <div class="flex items-center gap-2 mb-4">
                <input type="checkbox" id="terms" name="terms" class="h-4 w-4">
                <label for="terms" class="text-sm">I agree to the <a href="#" class="text-rate-red">terms</a></label>
            </div>

            <button id="submitBtn" type="submit" class="w-full p-3 bg-rate-red text-white rounded flex items-center justify-center gap-3">
                <span id="btnText">Create account</span>
                <span id="btnSpinner" class="spinner hidden" aria-hidden="true"></span>
            </button>
            @if($errors->has('recaptcha'))
                <x-recaptcha-alert :error="$errors->first('recaptcha')" />
            @endif
        </form>
    </div>

    <script>
        const recaptchaSiteKey = {!! json_encode(config('services.recaptcha.site_key')) !!};
        (function(){
            const form = document.getElementById('registerForm');
            const email = document.getElementById('email');
            const pw = document.getElementById('password');
            const pwc = document.getElementById('password_confirmation');
            const submitBtn = document.getElementById('submitBtn');
            const btnSpinner = document.getElementById('btnSpinner');
            const btnText = document.getElementById('btnText');
            const emailFeedback = document.getElementById('emailFeedback');
            const pwFeedback = document.getElementById('pwFeedback');

            function showSpinner(on){
                btnSpinner.classList.toggle('hidden', !on);
                btnText.style.opacity = on ? '0.6' : '1';
            }

            // basic client-side password checks
            function validatePassword(){
                const val = pw.value || '';
                if(val.length < 8){
                    pwFeedback.textContent = 'Password is too short (min 8 chars)';
                    pwFeedback.classList.remove('hidden');
                    return false;
                }
                if(val !== pwc.value){
                    pwFeedback.textContent = 'Passwords do not match';
                    pwFeedback.classList.remove('hidden');
                    return false;
                }
                pwFeedback.classList.add('hidden');
                return true;
            }

            // Debounced email availability check
            let emailTimer = null;
            email.addEventListener('input', function(){
                emailFeedback.classList.add('hidden');
                if (emailTimer) clearTimeout(emailTimer);
                emailTimer = setTimeout(async function(){
                    const val = email.value.trim();
                    if (!val || !/^\S+@\S+\.\S+$/.test(val)) {
                        emailFeedback.textContent = 'Enter a valid email address';
                        emailFeedback.classList.remove('hidden');
                        return;
                    }
                    emailFeedback.textContent = 'Checking...';
                    emailFeedback.classList.remove('hidden');
                    try {
                        const res = await fetch('{{ route('api.check-email') }}?email=' + encodeURIComponent(val));
                        const data = await res.json();
                        if (res.ok && data.available) {
                            emailFeedback.textContent = 'Email is available';
                            emailFeedback.classList.remove('text-yellow-300');
                            emailFeedback.classList.remove('text-rate-red');
                            emailFeedback.classList.add('text-green-400');
                        } else if (res.ok) {
                            emailFeedback.textContent = 'This email is already registered';
                            emailFeedback.classList.remove('text-green-400');
                            emailFeedback.classList.add('text-rate-red');
                        } else {
                            emailFeedback.textContent = data.message || 'Error checking email';
                            emailFeedback.classList.remove('text-green-400');
                            emailFeedback.classList.add('text-yellow-300');
                        }
                    } catch (err) {
                        emailFeedback.textContent = 'Could not check email availability';
                        emailFeedback.classList.remove('text-green-400');
                        emailFeedback.classList.add('text-yellow-300');
                    }
                }, 500);
            });

            pw.addEventListener('input', validatePassword);
            pwc.addEventListener('input', validatePassword);

            form.addEventListener('submit', function(e){
                if(!validatePassword()){
                    e.preventDefault();
                    return;
                }
                e.preventDefault();
                // If reCAPTCHA is configured, execute and get token first
                if (recaptchaSiteKey && window.grecaptcha) {
                    showSpinner(true);
                    grecaptcha.ready(function(){
                        grecaptcha.execute(recaptchaSiteKey, {action: 'signup'}).then(function(token){
                            document.getElementById('g-recaptcha-response').value = token;
                            form.submit();
                        }).catch(function(){
                            showSpinner(false);
                            alert('reCAPTCHA failed, please try again.');
                        });
                    });
                    return;
                }
                showSpinner(true);
                form.submit();
            });
        })();
    </script>
    @if(config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @endif
</body>
</html>
