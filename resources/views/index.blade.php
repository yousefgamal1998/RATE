<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RATE - Free Movies & TV Shows</title>
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
    <style>
        .hero-bg {
            background: linear-gradient(135deg, rgba(0,0,0,0.8), rgba(0,0,0,0.6));
        }
        .gradient-text {
            background: linear-gradient(45deg, #ffffff, #e50914);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .nav-link::after {
            content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100%;
    height: 2px;
    background: rgba(145, 87, 90, 0.5);
    opacity: 0;
    transition: all 0.5s ease;
        }
        .nav-link:hover::after {
    width: 100%;
    opacity: 1;
}
        
        /* Loading Animation */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }
        
        .loading-screen.fade-out {
            opacity: 0;
            pointer-events: none;
        }
        
        .logo-animation {
            font-size: 4rem;
            font-weight: bold;
            color: #e50914;
            margin-bottom: 2rem;
            animation: logoPulse 2s ease-in-out infinite;
        }
        
        @keyframes logoPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .loading-bar {
            width: 200px;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .loading-progress {
            height: 100%;
            background: linear-gradient(90deg, #e50914, #ff6b6b);
            border-radius: 2px;
            animation: loadingProgress 3s ease-in-out forwards;
        }
        
        @keyframes loadingProgress {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        
        .loading-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            animation: textFade 1s ease-in-out infinite alternate;
        }
        
        @keyframes textFade {
            0% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        /* Page Content Animation */
        .page-content {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease-out;
        }
        
        .page-content.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Hero Section Animation */
            .hero-title {
            opacity: 0;
            transform: translateY(30px);
            animation: slideInUp 1s ease-out 0.5s forwards;
        }
        
        .hero-subtitle {
            opacity: 0;
            transform: translateY(30px);
            animation: slideInUp 1s ease-out 0.8s forwards;
        }
        
        .hero-cta {
            opacity: 0;
            transform: translateY(30px);
            animation: slideInUp 1s ease-out 1.1s forwards;
        }
        
        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Floating Elements */
        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-element:nth-child(2) {
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }
        
        .floating-element:nth-child(3) {
            bottom: 30%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Title: show only on hover (desktop), always visible on touch devices */
.movie-card h3 {
  opacity: 0;
  visibility: hidden;
  transform: translateY(6px);
  transition: opacity 200ms ease, transform 200ms ease;
  pointer-events: none;
}

/* Show title when hovering the card (desktop / hover-capable devices) */
@media (hover: hover) and (pointer: fine) {
  .movie-card:hover h3 {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }
}

/* For touch devices (no hover) keep titles visible so users can read them */
@media (hover: none) {
  .movie-card h3 {
    opacity: 1;
    visibility: visible;
    transform: none;
    pointer-events: auto;
  }
}
                /* Rating star + value alignment (match dashboard) */
                .rating-star { fill: #FFD700 !important; }
                .rating-value { display: inline-flex; align-items: baseline; gap: 0.15rem; }

        /* Ensure user-score animations wait until the page is ready.
           We pause animations by default and add the `start` class via JS
           after the loading screen (#loadingScreen) disappears and the
           page has been fully loaded/painted. This prevents the circle
           from animating prematurely. */
        .user-score {
            /* pause any CSS animation until we explicitly start it */
            -webkit-animation-play-state: paused !important;
            animation-play-state: paused !important;
        }

        .user-score.start {
            -webkit-animation-play-state: running !important;
            animation-play-state: running !important;
        }

                

                
    </style>
</head>
<body class="bg-black text-white overflow-x-hidden">
    <!-- Loading Screen -->
    <div id="loadingScreen" class="loading-screen">
        <div class="logo-animation">RATE</div>
        <div class="loading-bar">
            <div class="loading-progress"></div>
        </div>
        <div class="loading-text">The page is loading</div>
    </div>

    <!-- Page Content -->
    <div id="pageContent" class="page-content">
    <!-- Header -->
        <header class="fixed top-0 w-full z-50 bg-black/95 backdrop-blur-sm border-b border-white/10">
        <nav class="max-w-7xl mx-auto px-8 py-4 flex justify-between items-center">
            <a href="#" class="text-3xl font-bold text-rate-red tracking-tight">RATE</a>
            
            <ul class="hidden md:flex list-none gap-10 mx-8">
                <li><a href="{{ route('login') }}" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-all duration-500 ease-in-out">Free Movies & TV</a></li>
                <li><a href="{{ route('login') }}" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-all duration-500 ease-in-out">Live TV</a></li>
                <li><a href="{{ route('login') }}" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-all duration-500 ease-in-out">Features</a></li>
                <li><a href="{{ route('login') }}" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-all duration-500 ease-in-out">Download</a></li>
            </ul>
            
            <div class="flex items-center gap-4 flex-1 max-w-md">
                <input type="text" class="w-full bg-white/10 border border-white/20 rounded-md px-4 py-3 text-white placeholder-white/60 focus:outline-none focus:border-rate-red focus:bg-white/15 transition-all duration-300 text-sm" placeholder="Search for movies and series...">
                <i class="fas fa-search text-rate-red"></i>
            </div>
            
            <div class="flex gap-4 items-center">
                <a href="{{ route('login') }}" class="px-5 py-3 bg-transparent text-white border border-white/30 rounded-md hover:bg-white/10 hover:border-white/50 transition-all duration-300 text-sm font-medium">Sign In</a>
                <a href="{{ route('signup') }}" class="px-5 py-3 bg-gradient-to-br from-rate-red to-white/20 text-white rounded-md hover:bg-rate-red-hover hover:-translate-y-0.5 transition-all duration-300 text-sm font-medium">Sign Up Free</a>
            </div>
        </nav>
    </header>
    

    <!-- Main Content -->
    <main class="pt-20">
   <section id="movies" class="bg-black text-white py-16 relative overflow-hidden">
  <div class="max-w-7xl mx-auto px-8">
    <h2 class="text-5xl font-bold text-center mb-12 transparent-text">
      🎬 Latest Movies
    </h2>

    <!-- أزرار السلايدر -->
    <button id="prevMovie" class="absolute left-5 top-1/2 transform -translate-y-1/2 bg-white text-black rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-200 transition">
      <i class="fas fa-chevron-left"></i>
    </button>

    <div id="moviesContainer" class="flex gap-6 overflow-x-hidden scroll-smooth">
      <!-- الكروت هتضاف هنا -->
    </div>

    <button id="nextMovie" class="absolute right-5 top-1/2 transform -translate-y-1/2 bg-white text-black rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-200 transition">
      <i class="fas fa-chevron-right"></i>
    </button>
  </div>
</section>

        <!-- Hero Section -->
        <section 
            class="min-h-screen flex items-center justify-center text-center relative"
            style="background-image: linear-gradient(135deg, rgba(0,0,0,0.8), rgba(0,0,0,0.6)), url('{{ asset('image/ceinma.jpg') }}'); background-size: cover; background-position: center;"
        >
            <!-- Floating Elements -->
            <div class="floating-element text-6xl">
                <i class="fas fa-film"></i>
                    </div>
            <div class="floating-element text-4xl">
                <i class="fas fa-play-circle"></i>
                    </div>
            <div class="floating-element text-5xl">
                <i class="fas fa-tv"></i>
            </div>

            <div class="max-w-4xl px-8">
                <h1 class="hero-title text-6xl font-bold mb-6 leading-tight gradient-text">Free Movies to Watch, Anytime Anywhere</h1>
                <p class="hero-subtitle text-xl mb-10 opacity-90 font-normal">The search is over! Let RATE help you find the perfect movie to watch tonight for free.</p>
                <div class="hero-cta flex gap-4 justify-center flex-wrap">
                    <a href="{{ route('login') }}" class="px-8 py-4 bg-gradient-to-br from-rate-red to-white/20 text-white rounded-md hover:bg-rate-red-hover hover:-translate-y-0.5 transition-all duration-300 text-lg font-semibold">Watch Free</a>
                    <a href="{{ route('signup') }}" class="px-8 py-4 bg-transparent text-white border border-white/30 rounded-md hover:bg-white/10 hover:border-white/50 transition-all duration-300 text-lg font-semibold">Sign Up Free</a>
                    <!-- New Console button linking to the add-movie page -->
                    <a href="{{ url('/admin/add-movie') }}" class="px-6 py-3 bg-white/10 text-white border border-white/20 rounded-md hover:bg-white/20 transition-all duration-300 text-lg font-semibold">Console</a>
                </div>
                <!-- User Score circle (same component used on dashboard) -->
                
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-gray-900">
            <div class="max-w-7xl mx-auto px-8">
                <h2 class="text-5xl font-bold text-center mb-12 text-white">Why Choose RATE?</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
                    <div class="bg-white/5 p-10 rounded-xl border border-white/10 transition-all duration-300 text-center hover:-translate-y-1 hover:border-rate-red hover:bg-white/8">
                        <div class="text-5xl text-rate-red mb-6">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-4 text-white">Works Worldwide</h3>
                        <p class="text-white/80 leading-relaxed">No other free streaming service delivers more content to and from more countries worldwide.</p>
                    </div>
                    <div class="bg-white/5 p-10 rounded-xl border border-white/10 transition-all duration-300 text-center hover:-translate-y-1 hover:border-rate-red hover:bg-white/8">
                        <div class="text-5xl text-rate-red mb-6">
                        <i class="fas fa-film"></i>
                    </div>
                        <h3 class="text-xl font-semibold mb-4 text-white">Thousands of Titles</h3>
                        <p class="text-white/80 leading-relaxed">Choose from movies, shows, sports and music documentaries, series, Live TV and more.</p>
                </div>
                    <div class="bg-white/5 p-10 rounded-xl border border-white/10 transition-all duration-300 text-center hover:-translate-y-1 hover:border-rate-red hover:bg-white/8">
                        <div class="text-5xl text-rate-red mb-6">
                            <i class="fas fa-gift"></i>
                    </div>
                        <h3 class="text-xl font-semibold mb-4 text-white">Always 100% Free</h3>
                        <p class="text-white/80 leading-relaxed">Welcome to instant gratification at its best. Watch now without any payment or subscription.</p>
                </div>
                    <div class="bg-white/5 p-10 rounded-xl border border-white/10 transition-all duration-300 text-center hover:-translate-y-1 hover:border-rate-red hover:bg-white/8">
                        <div class="text-5xl text-rate-red mb-6">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                        <h3 class="text-xl font-semibold mb-4 text-white">Device-Friendly</h3>
                        <p class="text-white/80 leading-relaxed">Stream the good stuff from your favorite devices including Apple, Android, Smart TVs and more.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="bg-gradient-to-br from-rate-red to-white/20 py-16 text-center">
            <div class="max-w-7xl mx-auto px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="text-white">
                        <div class="text-5xl font-bold mb-2">50K+</div>
                        <div class="text-lg opacity-90">Free Movies & Shows</div>
                    </div>
                    <div class="text-white">
                        <div class="text-5xl font-bold mb-2">600+</div>
                        <div class="text-lg opacity-90">Live TV Channels</div>
                    </div>
                    <div class="text-white">
                        <div class="text-5xl font-bold mb-2">100%</div>
                        <div class="text-lg opacity-90">Always Free</div>
                    </div>
                    <div class="text-white">
                        <div class="text-5xl font-bold mb-2">24/7</div>
                        <div class="text-lg opacity-90">Available</div>
                    </div>
                </div>
            </div>
        </section>

       
    </main>

    <!-- Footer -->
    <footer class="bg-black py-8">
        <div class="max-w-7xl mx-auto px-8">
            <div class="text-center text-white/60">
                <p class="text-sm">RATE. All rights reserved. | <a href="#privacy" class="text-white-500 hover:text-blue-400 transition-colors duration-300">Privacy & Legal</a> | <a href="#accessibility" class="text-white-500 hover:text-blue-400 transition-colors duration-300">Accessibility</a>. 2025 ©</p>
            </div>
        </div>
    </footer>
    </div>

@php
    // Safely evaluate auth/route values so rendering won't crash when DB is down
    try {
        $isAuthenticated = auth()->check();
    } catch (\Throwable $e) {
        $isAuthenticated = false;
    }

    try {
        $loginUrl = route('login');
    } catch (\Throwable $e) {
        // fallback to a sensible default path if route helper fails
        $loginUrl = url('/login');
    }
@endphp

<script>
  // نمرّر حالة تسجيل الدخول لـ JS بطريقة آمنة
  window.isAuthenticated = @json($isAuthenticated);
  // استخدم اسم المسار المخصص login لضمان مرونة إذا تغيرت الروت
  window.loginUrl = @json($loginUrl);
</script>

<!-- Start user-score delayed animation: define startUserScoreAnimation() and call it on window.load -->
<script>
    (function () {
        'use strict';

        function isElementHidden(el) {
            if (!el) return true;
            var s = window.getComputedStyle(el);
            return s.display === 'none' || s.visibility === 'hidden' || parseFloat(s.opacity) === 0 || (el.offsetWidth === 0 && el.offsetHeight === 0);
        }

        // Expose a global starter function as requested
        window.startUserScoreAnimation = function startUserScoreAnimation() {
            var loader = document.getElementById('loadingScreen');

            function doStart() {
                // animate-in for page content (keeps existing behaviour)
                var page = document.getElementById('pageContent');
                if (page && !page.classList.contains('animate-in')) {
                    page.classList.add('animate-in');
                }

                // add .start to all user-score elements (also support .user-score-circle)
                var scores = document.querySelectorAll('.user-score, .user-score-circle');
                scores.forEach(function (el) {
                    el.classList.add('start');
                });
            }

            if (!loader) {
                // No loader: start immediately (small delay to allow paint)
                setTimeout(doStart, 50);
                return;
            }

            // If loader already hidden, start right away
            if (isElementHidden(loader)) {
                setTimeout(doStart, 50);
                return;
            }

            // Otherwise wait for loader to hide: prefer transitionend, fallback to mutation observer and timeout
            var finished = false;

            function finish() {
                if (finished) return; finished = true;
                setTimeout(doStart, 50);
                cleanup();
            }

            function onTransition(e) {
                if (e.target === loader) finish();
            }

            var obs = new MutationObserver(function () {
                if (isElementHidden(loader)) finish();
            });

            function cleanup() {
                loader.removeEventListener('transitionend', onTransition);
                try { obs.disconnect(); } catch (e) {}
            }

            loader.addEventListener('transitionend', onTransition);
            obs.observe(loader, { attributes: true, attributeFilter: ['class', 'style'] });

            // Fallback: after 2s assume loader gone
            setTimeout(finish, 2000);
        };

        // Call it once the window fully loads: trigger user-score animations immediately
        window.addEventListener('load', function () {
            try {
                // Trigger any user-score circles to animate immediately on page start.
                var elems = document.querySelectorAll('.user-score-circle, .user-score');
                elems.forEach(function(el){
                    if (!el || !el.getAttribute) return;
                    if (!el.getAttribute('data-percent')) return;
                    if (window.__observeUserScoreElement && typeof window.__observeUserScoreElement === 'function') {
                        try { window.__observeUserScoreElement(el); return; } catch(e){}
                    }
                    if (window.__animateUserScoreElement && typeof window.__animateUserScoreElement === 'function') {
                        try { window.__animateUserScoreElement(el); return; } catch(e){}
                    }
                    // fallback: add start class for CSS-driven animations
                    try { el.classList.add('start'); } catch(e){}
                });
            } catch (e) { /* fail silently */ }
        }, { once: true });

    })();
</script>


@vite(['resources/js/index.js'])
</body>
</html>
