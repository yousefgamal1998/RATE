<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','RATE')</title>
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
        
    .movie-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative; /* allow absolute children if needed */
    }
        
        .movie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .arrow-button {
            background: #2a2a2a;
            border: 1px solid #4a4a4a;
            transition: all 0.3s ease;
        }
        
        .arrow-button:hover {
            background: #3a3a3a;
            border-color: #6a6a6a;
        }
        /* Carousel-specific styles */
        .carousel {
            scroll-behavior: smooth;
        }

        /* Hide native scrollbar */
        .scrollbar-hidden::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hidden {
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }

        /* Ensure cards snap to center */
        .snap-center {
            scroll-snap-align: center;
        }

    /* Title overlay inside the poster image (bottom gradient) */
    .movie-card a.media {
      position: relative;
      display: block;
      overflow: hidden;
    }

    /* Gradient panel that holds the title */
    .movie-card .overlay {
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      padding: 0.6rem 0.9rem;
      background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.55) 45%, rgba(0,0,0,0.8) 100%);
      opacity: 0;
      transform: translateY(8px);
      transition: opacity 240ms cubic-bezier(.2,.9,.2,1), transform 240ms cubic-bezier(.2,.9,.2,1);
      pointer-events: none; /* don't block clicks on the anchor */
      display: flex;
      align-items: flex-end;
    }

    .movie-card .overlay .movie-title {
      color: #fff;
      margin: 0;
      font-size: 0.95rem;
      font-weight: 600;
      letter-spacing: -0.01em;
      /* Title casing handled server-side (Blade) so we avoid forcing lowercase in CSS */
      opacity: 1; /* visual opacity handled by .overlay container */
      transform: none;
      pointer-events: auto;
    }

    /* Reveal overlay on hover/focus-within */
    .movie-card:hover .overlay,
    .movie-card:focus-within .overlay {
      opacity: 1;
      transform: translateY(0);
    }

    /* Touch devices: reveal on active (tap) for discoverability */
    @media (hover: none) {
      .movie-card:active .overlay {
        opacity: 1;
        transform: translateY(0);
      }
    }

        @media (max-width: 768px) {
            .carousel-arrow.left, .carousel-arrow.right { display: none; }
        }

        /* Logo grid (centered multi-row) to match screenshot */
        main .logo-grid {
          display: grid;
          grid-template-columns: repeat(4, minmax(80px, 1fr)); /* smaller min column to pack horizontally */
          gap: 0.45rem 0.8rem; /* even tighter row gap and reduced column gap for horizontal packing */
          align-items: center;
          justify-items: center;
          padding: 1rem 0 1.5rem 0; /* reduced vertical padding to pack rows tighter */
          list-style: none;
          margin: 0 auto;
          width: 100%;
          max-width: 920px; /* narrower to keep items compact and centered */
          position: relative;
        }

        /* subtle vignette behind the logos to match the dark gradient in the screenshot */
        main .logo-grid::before {
          content: '';
          position: absolute;
          inset: 0;
          background: radial-gradient(ellipse at center, rgba(255,255,255,0.01) 0%, rgba(0,0,0,0.75) 55%);
          pointer-events: none;
          z-index: 0;
        }

        /* Each logo cell */
        main .logo-grid li {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 100%;
          height: 42px; /* tightened height to reduce vertical spacing between rows */
          padding: 0.04rem; /* reduce padding */
          z-index: 1;
        }

        /* Larger SVGs for clearer logos */
        main .logo-grid svg {
          max-height: 38px; /* slightly smaller for denser pack */
          max-width: 120px; /* reduce max-width so wide logos don't create horizontal gaps */
          height: auto;
          width: auto;
          display: block;
          opacity: 0.72; /* slightly brighter to match image */
          transition: transform 0.18s ease, opacity 0.18s ease;
        }

        /* Hover should be subtle and consistent
           -- disabled for SVG logos: keep them visually static on hover */
        main .logo-grid li:hover svg {
          transform: none !important;
          opacity: 0.72 !important; /* match the default opacity to avoid visual change */
        }

        /* Make inline fills muted white (60%) even if SVGs contain hardcoded fills */
        main .logo-grid svg [fill],
        main .logo-grid svg path,
        main .logo-grid svg g {
          fill: rgba(255,255,255,0.6) !important;
          stroke: none !important;
        }

        /* Responsive fallbacks */
        @media (max-width: 1024px) {
          main .logo-grid {
            grid-template-columns: repeat(3, minmax(90px, 1fr));
            gap: 0.9rem 1.6rem;
            max-width: 760px;
          }
          main .logo-grid svg { max-height: 36px; }
        }

        @media (max-width: 640px) {
          main .logo-grid {
            grid-template-columns: repeat(2, minmax(80px, 1fr));
            gap: 0.7rem 1rem;
            padding: 1rem 0;
          }
          main .logo-grid svg { max-height: 32px; }
        }
        /* Ensure rating star uses gold even if color inheritance is present */
        .rating-star {
            fill: #FFD700 !important;
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen">
    <!-- Header -->
    <header class="fixed top-0 w-full z-50 bg-black/95 backdrop-blur-sm border-b border-white/10">
        <nav class="max-w-7xl mx-auto px-8 py-4 flex justify-between items-center">
            <a href="/" class="text-3xl font-bold text-rate-red tracking-tight">RATE</a>
            
            <ul class="hidden md:flex list-none gap-10 mx-8">
                <li><a href="#movies" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-colors duration-300">Free Movies & TV</a></li>
                <li><a href="#live" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-colors duration-300">Live TV</a></li>
                <li><a href="#features" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-colors duration-300">Features</a></li>
                <li><a href="#download" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-colors duration-300">Download</a></li>
            </ul>
            
            <div class="flex items-center gap-4">
              <div class="relative group">
                <div class="flex items-center gap-2 cursor-default">
                  @if(auth()->check())
                    @php
                      $user = auth()->user();
                      $avatar = $user->avatar ?? null;
                    @endphp
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-800 flex items-center justify-center">
                      @if($avatar)
                        @if(\Illuminate\Support\Str::startsWith($avatar, ['http://', 'https://']))
                          <img src="{{ $avatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                          <img src="{{ asset('storage/' . ltrim($avatar, '/')) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @endif
                      @else
                        <div class="w-8 h-8 bg-rate-red rounded-full flex items-center justify-center">
                          <i class="fas fa-user text-white text-sm"></i>
                        </div>
                      @endif
                    </div>
                    <span class="text-white/90 font-medium">{{ $user->name }}</span>
                  @else
                    <div class="w-8 h-8 bg-rate-red rounded-full flex items-center justify-center">
                      <i class="fas fa-user text-white text-sm"></i>
                    </div>
                  @endif

                  <!-- Compact dropdown arrow (small, circular background) -->
                  <button class="ml-2 w-8 h-8 rounded-full bg-[#111] flex items-center justify-center text-white/70 hover:text-white transition-colors duration-200" aria-label="Open user menu">
                    <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <path d="M1 1l5 5 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </button>
                </div>

                <!-- Hover dropdown (appears on group hover) -->
                <div class="absolute right-0 mt-2 w-40 bg-gray-900 rounded-md shadow-lg ring-1 ring-black/30 opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all duration-200 z-50">
                  <a href="{{ route('about') }}" class="block px-4 py-2 text-sm text-white hover:bg-white/5">About Us</a>

                  <!-- Progressive enhancement: visible button opens modal / triggers AJAX; hidden form exists as fallback for non-JS -->
                  <button type="button" id="signOutBtn" class="w-full text-left px-4 py-2 text-sm text-white hover:bg-white/5">Sign out</button>

                  <!-- Hidden fallback form (for no-JS or if AJAX fails) -->
                  <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="hidden" aria-hidden="true">
                    @csrf
                    <button type="submit">Logout</button>
                  </form>
                </div>
              </div>
            </div>
        </nav>
    </header>

    @if(session('status'))
      <x-flash :message="session('status')" type="success" :autohide="6000" />
    @endif

    <!-- Page content -->
    <main class="pt-20">
        @yield('content')
    </main>


    <footer class="bg-gradient-to-b from-gray-900 via-black to-black text-gray-200">
  <div class="max-w-6xl mx-auto px-6 py-10">
    <div class="flex flex-col md:flex-row items-center md:items-start justify-between gap-8">

      <!-- Logo + language selector (الجزء الأول) -->
      <div class="flex items-center gap-6">
        <!-- logo -->
        <a href="/" class="inline-flex items-center" aria-label="RATE Home">
          <span class="text-rate-red font-extrabold tracking-tight text-4xl md:text-5xl leading-none">RATE</span>
        </a>
        <!-- Language Selector (moved inline beside logo) -->
        <div class="shrink-0">
          <label for="locale-select" class="sr-only">Select a language</label>
          <select
            id="locale-select"
            name="locale"
            aria-label="Select a language"
            class="bg-gray-800 text-gray-100 text-sm px-3 py-2 rounded-md shadow-sm border border-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer"
          >
            <option value="en" selected>🇺🇸 US</option>
            <option value="en-GB">🇬🇧 GB</option>
            <option value="fr">🇫🇷 FR</option>
            <option value="de">🇩🇪 DE</option>
            <option value="it">🇮🇹 IT</option>
            <option value="pt">🇵🇹 PT</option>
            <option value="es">🇪🇸 ES</option>
          </select>
        </div>
      <!-- placeholder للمحتوى اللي هييجي بعدين (روابط الأعمدة) -->
      <div class="w-full md:flex-1">
        <!-- هنا هنضيف أعمدة الـ links بعدين؛ تركت المساحة متاحة عشان يبقى ال layout مرن -->
      </div>

    </div>
  </div>

<div class="container mx-auto px-6">
    <!-- الروابط الرئيسية (محدّثة: أربعة عناوين فقط كما طُلب) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-10">

      <!-- Marvel Cinematic Universe -->
      <div>
        <h3 class="text-white font-semibold text-lg mb-4"><a href="#" class="hover:underline">Marvel Cinematic Universe</a></h3>
        <!-- Links intentionally removed per request -->
      </div>

      <!-- disney plus -->
      <div>
        <h3 class="text-white font-semibold text-lg mb-4"><a href="#" class="hover:underline">disney plus</a></h3>
        <!-- Links intentionally removed per request -->
      </div>

      <!-- DC -->
      <div>
        <h3 class="text-white font-semibold text-lg mb-4"><a href="#" class="hover:underline">DC</a></h3>
        <!-- Links intentionally removed per request -->
      </div>

      <!-- Horror -->
      <div>
        <h3 class="text-white font-semibold text-lg mb-4"><a href="#" class="hover:underline">Horror</a></h3>
        <!-- Links intentionally removed per request -->
      </div>

    </div>

    <!-- الجزء السفلي -->
    <div class="border-t border-gray-700 pt-6 flex flex-col items-center justify-center text-sm text-gray-400 text-center">
      <div class="space-x-4 flex items-center justify-center flex-wrap gap-3">
        <span>© 2025 Plex</span>
        <a href="https://www.plex.tv/about/privacy-legal/" class="hover:text-white">Privacy & Legal</a>
        <a href="https://www.plex.tv/about/privacy-legal/#adchoices" class="hover:text-white">Ad Choices</a>
        <a href="https://www.plex.tv/accessibility-statement/" class="hover:text-white">Accessibility</a>
        <button class="hover:text-white border border-gray-600 rounded px-3 py-1 transition">
          Manage Cookies
        </button>
      </div>
    </div>

    <!-- Social Media Links (aligned under legal links) -->
    <div class="mt-6 flex items-center justify-center gap-5 text-gray-400">
  <!-- Instagram -->
  <a
    href="https://www.instagram.com/plex.tv/"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="Instagram"
        class="hover:text-white transition-colors duration-200"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 48 48"
      fill="currentColor"
          class="w-7 h-7"
    >
      <path
        d="M32.5389 24.0094C32.5389 19.2927 28.7156 15.4695 24 15.4695C19.2839 15.4695 15.4612 19.2927 15.4612 24.0094C15.4612 28.7256 19.2839 32.5494 24 32.5494C28.7156 32.5494 32.5389 28.7256 32.5389 24.0094Z"
      />
      <path
        fill-rule="evenodd"
        clip-rule="evenodd"
        d="M23.1465 3.00615C22.6285 3.00592 22.1485 3.00571 21.7022 3.0064V3C16.928 3.00534 16.0122 3.03736 13.649 3.14411C11.1514 3.25887 9.79533 3.67519 8.89235 4.02746C7.69691 4.49289 6.84302 5.04799 5.94644 5.94468C5.04986 6.84138 4.49377 7.69537 4.02947 8.89096C3.67884 9.79406 3.2615 11.1498 3.1473 13.6477C3.02455 16.3485 3 17.1555 3 23.9971C3 30.8386 3.02455 31.6499 3.1473 34.3507C3.26097 36.8486 3.67884 38.2043 4.02947 39.1064C4.49483 40.3025 5.04986 41.1544 5.94644 42.051C6.84302 42.9477 7.69691 43.5028 8.89235 43.9672C9.79587 44.3179 11.1514 44.7353 13.649 44.8506C16.3494 44.9733 17.1601 45 24.0003 45C30.8399 45 31.6511 44.9733 34.3515 44.8506C36.8491 44.7363 38.2057 44.32 39.1077 43.9677C40.3036 43.5034 41.1548 42.9483 42.0514 42.0516C42.948 41.1554 43.5041 40.3041 43.9684 39.1085C44.319 38.2065 44.7364 36.8508 44.8506 34.3528C44.9733 31.6521 45 30.8408 45 24.0035C45 17.1662 44.9733 16.3549 44.8506 13.6541C44.7369 11.1562 44.319 9.80047 43.9684 8.89844C43.503 7.70284 42.948 6.84885 42.0514 5.95215C41.1554 5.05546 40.3031 4.50036 39.1077 4.036C38.2047 3.68533 36.8491 3.26794 34.3515 3.15372C31.6506 3.03096 30.8399 3.0064 24.0003 3.0064L23.1465 3.00615ZM34.5996 10.3328C34.5996 8.63522 35.9765 7.25993 37.6736 7.25993V7.25886C39.3707 7.25886 40.7476 8.63575 40.7476 10.3328C40.7476 12.0299 39.3707 13.4068 37.6736 13.4068C35.9765 13.4068 34.5996 12.0299 34.5996 10.3328ZM23.9997 10.8517C16.7347 10.8517 10.8445 16.7426 10.8445 24.0085C10.8445 31.2744 16.7347 37.1627 23.9997 37.1627C31.2647 37.1627 37.1528 31.2744 37.1528 24.0085C37.1528 16.7426 31.2647 10.8517 23.9997 10.8517Z"
      />
    </svg>
  </a>

      <!-- TikTok -->
  <a
    href="https://www.tiktok.com/@plex.tv"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="TikTok"
        class="hover:text-white transition-colors duration-200"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 48 48"
      fill="currentColor"
          class="w-7 h-7"
    >
      <path
        d="M37.1557 11.4183C34.8934 9.94323 33.2611 7.58237 32.7518 4.83197C32.6418 4.23788 32.5807 3.62587 32.5807 3H25.3604L25.3481 31.9374C25.2275 35.1784 22.5602 37.7789 19.2907 37.7789C18.2745 37.7789 17.3178 37.5254 16.4751 37.0813C14.5429 36.0651 13.2203 34.04 13.2203 31.7101C13.2203 28.3631 15.9438 25.6396 19.2899 25.6396C19.915 25.6396 20.5139 25.7431 21.0803 25.92V18.5489C20.4936 18.4682 19.8979 18.4193 19.2899 18.4193C11.962 18.4193 6 24.3806 6 31.7101C6 36.2061 8.24595 40.1854 11.6744 42.5911C13.8323 44.1076 16.4588 45 19.2907 45C26.6194 45 32.5807 39.038 32.5807 31.7101V17.0356C35.4125 19.068 38.8825 20.266 42.6271 20.266V13.0449C40.6102 13.0449 38.7318 12.4459 37.1557 11.4183Z"
      />
    </svg>
  </a>

      <!-- X (Twitter) -->
  <a
    href="https://x.com/plex"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="X"
        class="hover:text-white transition-colors duration-200"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 48 48"
      fill="currentColor"
          class="w-7 h-7"
    >
      <path
        d="M27.9957 20.7841L43.6311 3H39.9261L26.3498 18.4417L15.5065 3H3L19.3972 26.3506L3 45H6.70531L21.0422 28.693L32.4935 45H45L27.9948 20.7841H27.9957ZM22.9208 26.5563L21.2594 24.2311L8.04039 5.72933H13.7315L24.3994 20.6609L26.0608 22.9861L39.9278 42.3948H34.2367L22.9208 26.5572V26.5563Z"
      />
    </svg>
  </a>



  <!-- BlueSky -->
  <a
    href="https://bsky.app/profile/plex.tv"
    target="_blank"
    rel="noopener noreferrer nofollow"
    aria-label="Blue Sky"
    class="hover:text-blue-500 transition-colors duration-300"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      fill="currentColor"
      viewBox="0 0 48 48"
      width="28"
      height="28"
    >
      <path
        d="M10.9705 6.72499C16.2461 10.6839 21.9185 18.7137 24 23.0205C26.0844 18.7137 31.7539 10.6867 37.0295 6.72499C40.8331 3.87011 47 1.65924 47 8.69149C47 10.0974 46.195 20.4934 45.7235 22.181C44.079 28.046 38.0961 29.5439 32.7745 28.6382C42.078 30.2224 44.447 35.4664 39.3324 40.7132C29.6264 50.6722 25.3829 38.212 24.2961 35.0207C24.0949 34.4342 24 34.1582 24 34.394C24 34.1611 23.9051 34.4342 23.7067 35.0207C22.6171 38.212 18.3736 50.6722 8.66762 40.7132C3.55588 35.4664 5.922 30.2195 15.2226 28.6382C9.90388 29.5439 3.91812 28.0489 2.2765 22.181C1.805 20.4934 1 10.0945 1 8.69149C1 1.65924 7.16687 3.87011 10.9705 6.72499Z"
      />
    </svg>
  </a>

  <!-- Facebook -->
  <a
    href="https://www.facebook.com/plexapp"
    target="_blank"
    rel="noopener noreferrer nofollow"
    aria-label="Facebook"
    class="hover:text-blue-600 transition-colors duration-300"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      fill="currentColor"
      viewBox="0 0 48 48"
      width="28"
      height="28"
    >
      <path
        d="M7 3C4.79086 3 3 4.79086 3 7V41C3 43.2091 4.79086 45 7 45H18.9876V28.4985H15V21.7779H18.9876V17.7429C18.9876 12.2602 21.2393 9 27.6365 9H32.9623V15.7214H29.6333C27.143 15.7214 26.9783 16.6606 26.9783 18.4134L26.9692 21.7771H33L32.2943 28.4977H26.9692V45H41C43.2091 45 45 43.2091 45 41V7C45 4.79086 43.2091 3 41 3H7Z"
      />
    </svg>
  </a>

  <!-- LinkedIn -->
  <a
    href="https://www.linkedin.com/company/plex-inc"
    target="_blank"
    rel="noopener noreferrer nofollow"
    aria-label="LinkedIn"
    class="hover:text-blue-700 transition-colors duration-300"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      fill="currentColor"
      viewBox="0 0 48 48"
      width="28"
      height="28"
    >
      <path
        d="M38.786 38.7887H32.5629V29.0431C32.5629 26.7192 32.5214 23.7276 29.3262 23.7276C26.0851 23.7276 25.5892 26.2596 25.5892 28.8739V38.7881H19.3661V18.7472H25.3402V21.486H25.4239C26.0217 20.4637 26.8857 19.6228 27.9237 19.0527C28.9617 18.4826 30.1349 18.2048 31.3183 18.2487C37.6256 18.2487 38.7886 22.3974 38.7886 27.7946L38.786 38.7887H38.786Z"
      />
    </svg>
  </a>

  <!-- YouTube -->
  <a
    href="https://www.youtube.com/user/plextvapp"
    target="_blank"
    rel="noopener noreferrer nofollow"
    aria-label="YouTube"
    class="hover:text-red-600 transition-colors duration-300"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 48 48"
      width="28"
      height="28"
      fill="currentColor"
      aria-hidden="true"
      focusable="false"
    >
      <g>
        <path d="M46.5 14.5c-.5-2-2-3.5-4-4-3.5-1-17.5-1-17.5-1s-14 0-17.5 1c-2 .5-3.5 2-4 4-1 3.5-1 10.5-1 10.5s0 7 .9 10.5c.5 2 2 3.5 4 4 3.5 1 17.5 1 17.5 1s14 0 17.5-1c2-.5 3.5-2 4-4 1-3.5 1-10.5 1-10.5s0-7-.9-10.5zM19.5 31.5v-15l13 7.5-13 7.5z"/>
      </g>
    </svg>
  </a>
    </div>
  </div>
</footer>

    <!-- Confirmation modal for sign out (hidden by default) -->
    <div id="signOutModal" class="fixed inset-0 z-60 hidden items-center justify-center" aria-hidden="true">
      <div class="fixed inset-0 bg-black/60" id="signOutModalOverlay" aria-hidden="true"></div>
      <div class="relative max-w-md w-full mx-4">
        <div class="bg-gray-900 rounded-lg shadow-lg border border-white/10 overflow-hidden">
          <div class="p-6 text-center">
            <h3 class="text-2xl font-bold text-white mb-2">Sign out</h3>
            <p class="text-sm text-gray-300 mb-4">Are you sure you want to sign out? You will be returned to the home page.</p>

            <div class="flex items-center justify-center gap-3">
              <button id="cancelSignOut" class="px-4 py-2 rounded bg-transparent border border-gray-700 text-gray-200 hover:bg-white/5">Cancel</button>
              <button id="confirmSignOut" class="px-4 py-2 rounded bg-rate-red text-white font-medium hover:bg-rate-red-hover flex items-center gap-2">
                <span id="confirmSignOutText">Sign out</span>
                <svg id="confirmSpinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  @vite(['resources/js/dashboard.js'])
  {{-- Render any pushed page-specific scripts --}}
  @stack('scripts')
</body>
</html>

<script>
  // Carousel arrow behavior: wire each carousel's left/right arrows to scroll that carousel by one card
  (function(){
    const carousels = document.querySelectorAll('.carousel');
    if (!carousels || !carousels.length) return;

    carousels.forEach((carousel)=>{
      const container = carousel.closest('.relative') || document;
      const left = container.querySelector('.carousel-arrow.left');
      const right = container.querySelector('.carousel-arrow.right');

      function scrollByCard(dir = 1){
        const card = carousel.querySelector('.movie-card');
        if (!card) return;
        const style = getComputedStyle(carousel);
        const gapVal = style.columnGap || style.gap || '24px';
        const gap = parseFloat(gapVal) || 24;
        const cardWidth = card.getBoundingClientRect().width + gap;
        carousel.scrollBy({ left: cardWidth * dir, behavior: 'smooth' });
      }

      left && left.addEventListener('click', ()=> scrollByCard(-1));
      right && right.addEventListener('click', ()=> scrollByCard(1));
    });
  })();

  // Sign out modal + AJAX logout (progressive enhancement)
  (function(){
    const signOutBtn = document.getElementById('signOutBtn');
    const modal = document.getElementById('signOutModal');
    const overlay = document.getElementById('signOutModalOverlay');
    const cancelBtn = document.getElementById('cancelSignOut');
    const confirmBtn = document.getElementById('confirmSignOut');
    const spinner = document.getElementById('confirmSpinner');
    const confirmText = document.getElementById('confirmSignOutText');
    const logoutForm = document.getElementById('logoutForm');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;

    if (!signOutBtn || !modal) return; // nothing to wire

    function openModal(){
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      modal.setAttribute('aria-hidden','false');
      // trap focus could be added later; focus on cancel for keyboard users
      cancelBtn?.focus();
    }

    function closeModal(){
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      modal.setAttribute('aria-hidden','true');
    }

    signOutBtn.addEventListener('click', (e)=>{
      e.preventDefault();
      openModal();
    });

    overlay && overlay.addEventListener('click', closeModal);
    cancelBtn && cancelBtn.addEventListener('click', closeModal);

    confirmBtn && confirmBtn.addEventListener('click', async function(e){
      e.preventDefault();
      // show spinner
      spinner.classList.remove('hidden');
      confirmText && (confirmText.textContent = 'Signing out...');
      confirmBtn.setAttribute('disabled','true');

      // perform AJAX POST to logout route; fall back to form submit if anything goes wrong
      try{
        const endpoint = "{{ route('logout') }}";
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken || '',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({})
        });

        if (res.ok) {
          // on success redirect to home (server may also redirect)
          window.location.href = "{{ url('/') }}";
          return;
        }

        // if server returned non-OK, fallback to form submit
        logoutForm && logoutForm.submit();
      }catch(err){
        // network or other error -> fallback
        logoutForm && logoutForm.submit();
      }
    });
  })();
</script>

<script>
  // Redirect to index when user presses Back on the dashboard page.
  (function(){
    try {
      // ensure a popstate will be emitted when Back is pressed
      history.pushState(null, null, location.href);

      window.addEventListener('popstate', function () {
        // send user to app home page (index)
        window.location.href = "{{ url('/') }}";
      });
    } catch (err) {
      console.warn('Back-redirect script failed:', err);
    }
  })();
</script>
<!-- Duplicate document removed: keep single HTML document above. -->
