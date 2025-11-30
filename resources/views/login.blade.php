<!DOCTYPE html>
<html lang="en" dir="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Sign in - RATE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'rate-red': '#e50914',
                        'rate-red-hover': '#f40612',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-black text-white min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-black/95 backdrop-blur-sm border-b border-white/10 py-4">
        <nav class="max-w-7xl mx-auto px-8 flex justify-between items-center">
            <a href="{{ route('homepage.only') }}" class="text-3xl font-bold text-rate-red tracking-tight">RATE</a>
            <a href="{{ route('homepage.only') }}" class="text-white/90 hover:text-rate-red transition-colors duration-300 font-medium">
                <span style="font-size:1.3rem;">Back to Home</span> <i class="fas fa-arrow-right"></i></a></nav>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-8">
        <div class="bg-white/5 p-12 rounded-2xl border border-white/10 w-full max-w-lg backdrop-blur-sm">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-2 text-white">Account Sign in</h1>
                <p class="text-white/70">Welcome back! Sign in to continue watching.</p>
            </div>

            <form id="loginForm" method="POST" action="{{ route('login.post') }}">
                @csrf
                @if(config('services.recaptcha.site_key'))
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                @endif
                <div class="mb-6">
                    <label for="email" class="block mb-2 text-white/90 font-medium">Email Address</label>
                          <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required
                              aria-describedby="emailHelp"
                              class="w-full p-4 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:border-rate-red focus:bg-white/15 transition-all duration-300">
                    @error('email')
                        <p dir="auto" role="alert" aria-live="polite" class="text-sm text-yellow-300 mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password" class="block mb-2 text-white/90 font-medium">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password"
                               class="w-full p-4 pr-12 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:border-rate-red focus:bg-white/15 transition-all duration-300"
                        >
                        <button type="button" id="togglePassword" aria-pressed="false" aria-label="Show password"
                                class="absolute inset-y-0 right-2 flex items-center px-2 text-white/70 hover:text-white">
                            <i id="toggleIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p dir="auto" role="alert" aria-live="polite" class="text-sm text-rate-red mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center mb-6">
                    <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }} class="h-4 w-4 text-rate-red focus:ring-rate-red border-white/20 rounded" />
                    <label for="remember" class="ml-3 text-sm text-white/80">Keep me signed in</label>
                </div>

                <button type="submit" class="w-full p-4 bg-rate-red text-white rounded-lg font-semibold hover:bg-rate-red-hover hover:-translate-y-0.5 transition-all duration-300 mb-4 disabled:bg-white/20 disabled:cursor-not-allowed disabled:transform-none">
                    Sign In
                </button>

                {{-- reCAPTCHA friendly notification area (localized) --}}
                @if($errors->has('recaptcha'))
                    <x-recaptcha-alert :error="$errors->first('recaptcha')" />
                @endif

                <!-- password errors are shown directly under the password field -->

                <div class="text-center mb-6">
                    <a href="{{ route('password.request') }}" class="text-rate-red hover:underline font-medium">Forgot your password?</a>
                </div>
            </form>

            @if(session('auth_diagnostic'))
                @php $diag = session('auth_diagnostic'); @endphp
                <div class="mt-6 p-4 bg-white/5 border border-yellow-700 text-yellow-200 rounded">
                    <p class="font-medium">Developer diagnostic (local only):</p>
                    <ul class="text-sm mt-2 space-y-1">
                        <li>User exists: <strong>{{ $diag['user_exists'] ? 'yes' : 'no' }}</strong></li>
                        <li>Password hash present: <strong>{{ $diag['hash_present'] ? 'yes' : 'no' }}</strong></li>
                        <li>Hash check (matches): <strong>{{ $diag['hash_check'] ? 'yes' : 'no' }}</strong></li>
                    </ul>
                </div>
            @endif

            <div class="relative text-center mb-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/20"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white/5 text-white/50">or</span>
                </div>
            </div>

            <div class="space-y-4">
                <a href="{{ route('social.redirect', ['provider' => 'google']) }}"
   onclick="event.preventDefault(); openAuthPopup(this.href);"
   class="flex items-center justify-center gap-3 p-4 border border-white/20 rounded-lg bg-white/5 text-white hover:bg-white/10 hover:border-white/30 transition-all duration-300 font-medium">
    <i class="fab fa-google"></i>
    Continue with Google
</a>
                <a href="{{ route('social.redirect', ['provider' => 'facebook']) }}"
   onclick="event.preventDefault(); openAuthPopup(this.href);"
   class="flex items-center justify-center gap-3 p-4 border border-white/20 rounded-lg bg-white/5 text-white hover:bg-white/10 hover:border-white/30 transition-all duration-300 font-medium">
                    <i class="fab fa-facebook-f"></i>
                    Continue with Facebook
                </a>
                <a href="#twitter" class="flex items-center justify-center gap-3 p-4 border border-white/20 rounded-lg bg-white/5 text-white hover:bg-white/10 hover:border-white/30 transition-all duration-300 font-medium">
                    <i class="fab fa-twitter"></i>
                    Continue with Twitter
                </a>
            </div>

            <div class="text-center mt-6">
                <p class="text-white/70">Don't have an account? <a href="{{ route('signup') }}" class="text-rate-red hover:underline font-medium">Create your free account</a></p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-black py-8">
        <div class="max-w-7xl mx-auto px-8">
            <div class="text-center text-white/60">
                <p class="text-sm">RATE. All rights reserved. | <a href="#privacy" class="text-blue-500 hover:text-blue-400 transition-colors duration-300">Privacy & Legal</a> | <a href="#accessibility" class="text-blue-500 hover:text-blue-400 transition-colors duration-300">Accessibility</a>. 2025 ©</p>
            </div>
        </div>
    </footer>

    @vite(['resources/js/login.js'])
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
                    // execute on load and before submit to refresh token
                    grecaptcha.execute(siteKey, {action: 'login'}).then(function(token){ setToken(token); });
                    const form = document.getElementById('loginForm');
                    if(form){
                        form.addEventListener('submit', function(e){
                            // ensure token is fresh before submit
                            e.preventDefault();
                            grecaptcha.execute(siteKey, {action: 'login'}).then(function(token){
                                setToken(token);
                                form.submit();
                            }).catch(function(){ form.submit(); });
                        });
                    }
                });
            })();
        </script>
    @endif
    <style>
        /* green outline when password length > 6 */
        .valid-password {
            box-shadow: 0 0 0 3px rgba(34,197,94,0.12);
            border-color: #16a34a !important; /* Tailwind green-600 */
        }
    </style>
    <script>
        (function(){
            const pw = document.getElementById('password');
            if(!pw) return;

            const check = () => {
                const len = pw.value ? pw.value.length : 0;
                if(len > 6) {
                    pw.classList.add('valid-password');
                } else {
                    pw.classList.remove('valid-password');
                }
            };

            pw.addEventListener('input', check, {passive:true});
            document.addEventListener('DOMContentLoaded', check);
            // handle autofill in some browsers
            setTimeout(check, 250);
        })();
    </script>
    <script>
        // Password show/hide toggle
        (function(){
            const pw = document.getElementById('password');
            const btn = document.getElementById('togglePassword');
            const icon = document.getElementById('toggleIcon');
            if(!pw || !btn || !icon) return;

            const setState = (show) => {
                pw.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                icon.classList.toggle('fa-eye', !show);
                icon.classList.toggle('fa-eye-slash', show);
            };

            btn.addEventListener('click', function(e){
                e.preventDefault();
                const show = pw.type !== 'text';
                setState(show);
            });

            // If the browser autofills password as visible text in some edge cases, ensure state sync on load
            document.addEventListener('DOMContentLoaded', function(){
                setState(false);
            });
        })();
    </script>
</body>
</html>