<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - RATE</title>
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
            <a href="#" class="text-3xl font-bold text-rate-red tracking-tight">RATE</a>
            
            <ul class="hidden md:flex list-none gap-10 mx-8">
              <li>
                @php $mcuId = \App\Models\Category::where('slug','marvel-cinematic-universe')->value('id'); @endphp
                <a href="#" data-category-id="{{ $mcuId }}" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-colors duration-300">Marvel Cinematic Universe</a>
              </li>
              <li>
                @php $disneyId = \App\Models\Category::whereIn('slug', ['disney-plus-originals','disney-plus'])->value('id'); @endphp
                <a href="#" data-category-id="{{ $disneyId }}" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-colors duration-300">Disney Plus</a>
              </li>
              <li>
                @php $horrorId = \App\Models\Category::where('slug','horror')->value('id'); @endphp
                <a href="#" data-category-id="{{ $horrorId }}" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-colors duration-300">Horror</a>
              </li>
              <li>
                @php $dcId = \App\Models\Category::where('slug','dc-comics')->value('id'); @endphp
                <a href="#" data-category-id="{{ $dcId }}" class="nav-link relative text-white/90 hover:text-white font-medium text-sm transition-colors duration-300">DC</a>
              </li>
            </ul>
            
            <div class="flex items-center gap-4">
                <!-- Group wrapper so we can show the dropdown on hover -->
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

    {{-- Flash status banner removed per request (was showing session('status')) --}}

    <!-- Quick Edit removed from dashboard (moved to Add Movie page) -->


      <!-- Latest Movies Carousel -->
        <section class="py-16 bg-black" data-category-id="{{ \App\Models\Category::where('slug','latest-movies')->value('id') }}">
          <div class="max-w-7xl mx-auto px-6">
            @php $latestId = \App\Models\Category::where('slug','latest-movies')->value('id'); @endphp
            <div class="flex items-center justify-center mb-6">
              <h2 class="text-5xl font-bold flex items-center gap-4">
               🎬 Latest Movies
              
              </h2>
            </div>

            @if(isset($movies) && $movies->count())
            <div class="relative">
              <!-- Left arrow -->
              <button type="button" class="carousel-arrow left arrow-button absolute left-4 top-1/2 -translate-y-1/2 z-20" aria-label="Previous">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>

              <!-- Carousel strip -->
              <div class="carousel overflow-x-auto scrollbar-hidden snap-x snap-mandatory flex gap-6 px-6 py-2">
                @foreach($movies as $movie)
                <article class="movie-card snap-center bg-white/5 rounded-lg overflow-hidden flex-shrink-0 flex flex-col w-[260px] md:w-[300px] lg:w-[320px] cursor-pointer" aria-labelledby="movie-{{ $movie->id }}-title">
                  {{-- full-card clickable overlay (mouse users) --}}
                  <a href="{{ route('movies.show', $movie->id) }}" class="absolute inset-0 z-10" tabindex="-1" aria-hidden="true"></a>
                  <a href="{{ route('movies.show', $movie->id) }}" class="media block w-full bg-gray-800 overflow-hidden flex-shrink-0">
                    <img src="{{ $movie->image_url ?? asset('image/placeholder.png') }}" alt="{{ $movie->title }} poster" class="w-full h-[330px] md:h-[360px] lg:h-[380px] object-cover lazy block">
                    <div class="overlay">
                      @php
                        $rawTitle = trim($movie->title ?? '');
                        $first = $rawTitle !== '' ? mb_strtoupper(mb_substr($rawTitle, 0, 1, 'UTF-8'), 'UTF-8') : '';
                        $rest = $rawTitle !== '' ? mb_substr($rawTitle, 1, mb_strlen($rawTitle, 'UTF-8'), 'UTF-8') : '';
                        $titleCap = $first . $rest;
                      @endphp
                      <h3 id="movie-{{ $movie->id }}-title" class="movie-title text-white text-sm font-medium tracking-tight">{{ $titleCap }}</h3>
                    </div>
                  </a>
                  <div class="p-4 flex-1 flex flex-col justify-between">
                    <p class="text-xs text-gray-300 mt-2 leading-snug">{{ Str::limit($movie->description, 90) }}</p>

          <div class="mt-5 flex items-center justify-center rating-row">
            @php
              $val = $movie->rating_decimal ?? (isset($movie->user_score) ? $movie->user_score/10 : null);
            @endphp
            @include('components.user_score_circle', ['value' => $val, 'size' => 44, 'stroke' => 5, 'label' => 'User Score', 'showDecimal' => false])
          </div>
                  </div>
                </article>
                @endforeach
              </div>

              <!-- Right arrow -->
              <button type="button" class="carousel-arrow right arrow-button absolute right-4 top-1/2 -translate-y-1/2 z-20" aria-label="Next">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
            </div>

            <!-- pagination removed -->

            @else
            <div class="text-center py-12 text-gray-400">
              No movies found. Try seeding the database or add movies from the admin panel.
            </div>
            @endif
          </div>
        </section>

        <!-- End Latest Movies -->

        <!-- Marvel Cinematic Universe Carousel -->
        <section id="category-{{ $marvelCategory->id ?? \App\Models\Category::where('slug','marvel-cinematic-universe')->value('id') }}" class="py-16 bg-black">
          <div class="max-w-7xl mx-auto px-6">
            @php $mcuId = $marvelCategory->id ?? \App\Models\Category::where('slug','marvel-cinematic-universe')->value('id'); @endphp
            <div class="flex items-center justify-center mb-6 flex-col" data-category-id="{{ $marvelCategory->id ?? \App\Models\Category::where('slug','marvel-cinematic-universe')->value('id') }}">
              <h2 class="text-5xl font-bold flex items-center gap-4">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 246.5 55.23" class="h-10 w-auto" aria-hidden="true" role="img"><title>Marvel Studios logo</title><path d="M518.28,486.45h117v-2.05h-117Z" transform="translate(-388.75 -484.39)"/><path d="M518.28,539.59h117v-2.05h-117Z" transform="translate(-388.75 -484.39)"/><path d="M534.52,519.17v5.89c0,5-2.48,7.51-7.51,7.51h-1.27c-5,0-7.39-2.48-7.39-7.39v-5.63h4.5v5.8c0,2.08,1,3.06,3.06,3.06h.92c2.08,0,3.06-1,3.06-3.06v-6.06a4.13,4.13,0,0,0-2-3.87l-5.66-4.1a8,8,0,0,1-3.87-7.51V499c0-5,2.48-7.51,7.51-7.51H527c5,0,7.39,2.48,7.39,7.39v3.46h-4.5v-3.64c0-2.08-1-3.06-3.06-3.06H526c-2.08,0-3.06,1-3.06,3.06v5.08a4.17,4.17,0,0,0,2.08,3.87l5.6,4c2.94,2.14,3.87,4,3.87,7.51" transform="translate(-388.75 -484.39)"/><path d="M541.61,532.22V496.08h-6.06V491.8h16.74v4.27h-6.06v36.14Z" transform="translate(-388.75 -484.39)"/><path d="M570.51,525.06c0,5-2.48,7.51-7.51,7.51h-1.44c-5,0-7.51-2.48-7.51-7.51V491.8h4.62v33.49c0,2.08,1,3.06,3.06,3.06h1.1c2.08,0,3.06-1,3.06-3.06V491.8h4.62Z" transform="translate(-388.75 -484.39)"/><path d="M578.07,528h2.77c3.12,0,4.56-1.56,4.56-4.68V500.69c0-3.12-1.44-4.68-4.56-4.68h-2.77Zm-4.62-36.2h7.62c6.06,0,8.95,2.89,8.95,8.95v22.52c0,6.06-2.89,8.95-8.95,8.95h-7.62Z" transform="translate(-388.75 -484.39)"/><path d="M597.42,532.22H592.8V491.8h4.62Z" transform="translate(-388.75 -484.39)"/><path d="M609.27,495.67h-1.39c-2.08,0-3.06,1-3.06,3.06v26.56c0,2.08,1,3.06,3.06,3.06h1.39c2.08,0,3.06-1,3.06-3.06V498.73c0-2.08-1-3.06-3.06-3.06m.17,36.89h-1.73c-5,0-7.51-2.48-7.51-7.51V499c0-5,2.48-7.51,7.51-7.51h1.73c5,0,7.51,2.48,7.51,7.51v26.1c0,5-2.48,7.51-7.51,7.51" transform="translate(-388.75 -484.39)"/><path d="M635.15,519.17v5.89c0,5-2.48,7.51-7.51,7.51h-1.27c-5,0-7.39-2.48-7.39-7.39v-5.63h4.5v5.8c0,2.08,1,3.06,3.06,3.06h.92c2.08,0,3.06-1,3.06-3.06v-6.06a4.13,4.13,0,0,0-2-3.87l-5.66-4.1a8,8,0,0,1-3.87-7.51V499c0-5,2.48-7.51,7.51-7.51h1.16c5,0,7.39,2.48,7.39,7.39v3.46h-4.51v-3.64c0-2.08-1-3.06-3.06-3.06h-.81c-2.08,0-3.06,1-3.06,3.06v5.08a4.17,4.17,0,0,0,2.08,3.87l5.6,4c2.94,2.14,3.87,4,3.87,7.51" transform="translate(-388.75 -484.39)"/><path d="M388.75,539.61H511.36V484.39H388.75Z" transform="translate(-388.75 -484.39)" style="fill:#e50b14"/><path d="M492.66,499.16h-6.54v9.13h6.54v7.38h-6.54v9.27h6.54v7.27H478.88V495.58l-5.81,36.63-8.5,0s-4.34-25.94-4.34-26h0c.7,4.19-1.93,8.57-4.26,10.3l4.6,15.67H453.4l-3.51-13.38-1.69.25v13.12H434.36l-.85-6.22h-5.67l-.85,6.22H412.91V512.73l-3.27,19.48h-3.88l-3.32-19.48v19.48h-7.3V491.79h9.27l3.25,20.88L411,491.79h9.27V529l5.57-37.17h9.68l5.39,35.88,0-35.88h7.4a11.69,11.69,0,0,1,10.54,6.57l-.83-6.57h7.39l3.34,24.61,3.37-24.61h20.5Z" transform="translate(-388.75 -484.39)" style="fill:#fff"/><path d="M448.24,499h0v13a4,4,0,0,0,1.72-.41c1.7-.83,3.15-2.89,3.15-6.16,0-6.26-4.26-6.43-4.83-6.43" transform="translate(-388.75 -484.39)" style="fill:#e50b14"/><path d="M428.7,519.56h4.08l-2.08-17.28Z" transform="translate(-388.75 -484.39)" style="fill:#e50b14"/><path d="M507.37,532.2H493.93V491.79h7.24v33.14h6.2Z" transform="translate(-388.75 -484.39)" style="fill:#fff"/></svg>
               Marvel Cinematic Universe
              
              </h2>
              <p class="mt-4 text-center text-gray-300 max-w-3xl">
                Discover the official Marvel Cinematic Universe — from Earth’s Mightiest Heroes to cosmic sagas. Browse films by phase, year, or character, read synopses, and see user scores and images. Whether you’re catching up or revisiting favorites, find everything MCU here.
              </p>
            </div>

      @php
        // allow controllers to pass a $mcuMovies collection; otherwise prefer loading
        // MCU movies from an explicit Category record (slug: 'marvel-cinematic-universe'),
        // then fall back to the existing heuristic that scans the provided $movies
        $mcu = $mcuMovies ?? collect();

        // If not provided by controller, prefer loading the latest 10 movies from the
        // Category record so editors can explicitly control MCU membership.
        if ((empty($mcu) || $mcu->count() === 0) ) {
          try {
            $marvelCategory = \App\Models\Category::where('slug', 'marvel-cinematic-universe')->first();
            if ($marvelCategory) {
              $mcu = $marvelCategory->movies()->latest()->take(10)->get();
            }
          } catch (\Exception $e) {
            // ignore and fall back to heuristic below
          }
        }

        // If controller didn't provide MCU list and category lookup did not return results,
        // and we have a $movies collection, build $mcu from $movies by doing a case-insensitive
        // substring check for 'marvel' in common fields.
        if ((empty($mcu) || $mcu->count() === 0) && isset($movies) && $movies->count()) {
          // normalize source to a Collection
          $source = $movies instanceof \Illuminate\Support\Collection ? $movies : collect($movies);

          $mcu = $source->filter(function($movie) {
            // If this movie already has an explicit category_id assigned, skip heuristic matching
            if (isset($movie->category_id) && !empty($movie->category_id)) {
              return false;
            }
            // build a searchable string from several possible fields
            $hay = '';
            $hay .= isset($movie->title) ? ' ' . $movie->title : '';
            $hay .= isset($movie->studio) ? ' ' . $movie->studio : '';
            $hay .= isset($movie->production_company) ? ' ' . $movie->production_company : '';
            $hay .= isset($movie->collection) ? ' ' . $movie->collection : '';
            $hay .= isset($movie->brand) ? ' ' . $movie->brand : '';

            // allow keywords array or string
            if (isset($movie->keywords)) {
              if (is_array($movie->keywords)) {
                $hay .= ' ' . implode(' ', $movie->keywords);
              } else {
                $hay .= ' ' . $movie->keywords;
              }
            }

            return stripos($hay, 'marvel') !== false;
          })->values();
        }
      @endphp

            @if($mcu->count())
            <div class="relative">
              <!-- Left arrow -->
              <button type="button" class="carousel-arrow left arrow-button absolute left-4 top-1/2 -translate-y-1/2 z-20" aria-label="Previous">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>

              <!-- Carousel strip -->
              <div class="carousel overflow-x-auto scrollbar-hidden snap-x snap-mandatory flex gap-6 px-6 py-2">
                @foreach($mcu as $movie)
                <article class="movie-card snap-center bg-white/5 rounded-lg overflow-hidden flex-shrink-0 flex flex-col w-[260px] md:w-[300px] lg:w-[320px] cursor-pointer" aria-labelledby="mcu-movie-{{ $movie->id }}-title">
                  {{-- full-card clickable overlay (mouse users) --}}
                  <a href="{{ route('movies.show', $movie->id) }}" class="absolute inset-0 z-10" tabindex="-1" aria-hidden="true"></a>
                  <a href="{{ route('movies.show', $movie->id) }}" class="media block w-full bg-gray-800 overflow-hidden flex-shrink-0">
                    <img src="{{ $movie->image_url ?? asset('image/placeholder.png') }}" alt="{{ $movie->title }} poster" class="w-full h-[330px] md:h-[360px] lg:h-[380px] object-cover lazy block">
                    <div class="overlay">
                      @php
                        $rawTitle = trim($movie->title ?? '');
                        $first = $rawTitle !== '' ? mb_strtoupper(mb_substr($rawTitle, 0, 1, 'UTF-8'), 'UTF-8') : '';
                        $rest = $rawTitle !== '' ? mb_substr($rawTitle, 1, mb_strlen($rawTitle, 'UTF-8'), 'UTF-8') : '';
                        $titleCap = $first . $rest;
                      @endphp
                      <h3 id="mcu-movie-{{ $movie->id }}-title" class="movie-title text-white text-sm font-medium tracking-tight">{{ $titleCap }}</h3>
                    </div>
                  </a>
                  <div class="p-4 flex-1 flex flex-col justify-between">
                    <p class="text-xs text-gray-300 mt-2 leading-snug">{{ Str::limit($movie->description, 90) }}</p>
                    <div class="mt-5 flex items-center justify-center rating-row">
                      @php
                        $val = $movie->rating_decimal ?? (isset($movie->user_score) ? $movie->user_score/10 : null);
                      @endphp
                      @include('components.user_score_circle', ['value' => $val, 'size' => 44, 'stroke' => 5, 'label' => 'User Score', 'showDecimal' => false])
                    </div>
                  </div>
                </article>
                @endforeach
              </div>

              <!-- Right arrow -->
              <button type="button" class="carousel-arrow right arrow-button absolute right-4 top-1/2 -translate-y-1/2 z-20" aria-label="Next">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
            </div>

            <!-- MCU pagination removed -->

            @else
            {{-- MCU no-movies message removed per request --}}
            @endif
          </div>
        </section>

        <!-- (removed duplicate compact Disney carousel inserted earlier; the canonical Disney+ section appears later) -->

        <!-- DC Comics Carousel -->
        <section id="category-{{ $cat->id ?? \App\Models\Category::where('slug','dc-comics')->value('id') }}" class="py-16 bg-black">
          <div class="max-w-7xl mx-auto px-6">
            @php $dcId = $cat->id ?? \App\Models\Category::where('slug','dc-comics')->value('id'); @endphp
            <div class="flex items-center justify-center mb-6 flex-col" data-category-id="{{ $cat->id ?? \App\Models\Category::where('slug','dc-comics')->value('id') }}">
              <h2 class="text-5xl font-bold flex items-center gap-4">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-10 w-auto" aria-hidden="true" role="img"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L4.5 8 12 4.5 19.5 8 12 9.5z" fill="#fff"/></svg>
               DC Comics
              
              </h2>
              <p class="mt-4 text-center text-gray-300 max-w-3xl">
                Titles from the DC Comics universe — heroes, villains and epic sagas. Browse the DC collection below.
              </p>
              
            </div>

      @php
        $dc = $dcMovies ?? collect();

        // If the controller didn't supply $dcMovies, do not perform an
        // additional category DB lookup here — prefer the controller as
        // the single source of truth. The final fallback below still
        // performs a heuristic filter on $movies when no explicit
        // collection is available.
        // (This keeps view logic simple and avoids surprising results.)

        if ((empty($dc) || $dc->count() === 0) && isset($movies) && $movies->count()) {
          $source = $movies instanceof \Illuminate\Support\Collection ? $movies : collect($movies);

          $dc = $source->filter(function($movie) {
            // Skip movies that already have an explicit category assignment
            if (isset($movie->category_id) && !empty($movie->category_id)) {
              return false;
            }
            $hay = '';
            $hay .= isset($movie->title) ? ' ' . $movie->title : '';
            $hay .= isset($movie->studio) ? ' ' . $movie->studio : '';
            $hay .= isset($movie->production_company) ? ' ' . $movie->production_company : '';
            $hay .= isset($movie->collection) ? ' ' . $movie->collection : '';
            $hay .= isset($movie->brand) ? ' ' . $movie->brand : '';

            if (isset($movie->keywords)) {
              if (is_array($movie->keywords)) {
                $hay .= ' ' . implode(' ', $movie->keywords);
              } else {
                $hay .= ' ' . $movie->keywords;
              }
            }

            return stripos($hay, 'dc') !== false || stripos($hay, 'dc comics') !== false || stripos($hay, 'dc universe') !== false;
          })->values();
        }
      @endphp

            @if($dc->count())
            <div class="relative">
              <!-- Left arrow -->
              <button type="button" class="carousel-arrow left arrow-button absolute left-4 top-1/2 -translate-y-1/2 z-20" aria-label="Previous">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>

              <!-- Carousel strip -->
              <div class="carousel overflow-x-auto scrollbar-hidden snap-x snap-mandatory flex gap-6 px-6 py-2">
                @foreach($dc as $movie)
                <article class="movie-card snap-center bg-white/5 rounded-lg overflow-hidden flex-shrink-0 flex flex-col w-[260px] md:w-[300px] lg:w-[320px] cursor-pointer" aria-labelledby="dc-movie-{{ $movie->id }}-title">
                  <a href="{{ route('movies.show', $movie->id) }}" class="absolute inset-0 z-10" tabindex="-1" aria-hidden="true"></a>
                  <a href="{{ route('movies.show', $movie->id) }}" class="media block w-full bg-gray-800 overflow-hidden flex-shrink-0">
                    <img src="{{ $movie->image_url ?? asset('image/placeholder.png') }}" alt="{{ $movie->title }} poster" class="w-full h-[330px] md:h-[360px] lg:h-[380px] object-cover lazy block">
                    <div class="overlay">
                      @php
                        $rawTitle = trim($movie->title ?? '');
                        $first = $rawTitle !== '' ? mb_strtoupper(mb_substr($rawTitle, 0, 1, 'UTF-8'), 'UTF-8') : '';
                        $rest = $rawTitle !== '' ? mb_substr($rawTitle, 1, mb_strlen($rawTitle, 'UTF-8'), 'UTF-8') : '';
                        $titleCap = $first . $rest;
                      @endphp
                      <h3 id="dc-movie-{{ $movie->id }}-title" class="movie-title text-white text-sm font-medium tracking-tight">{{ $titleCap }}</h3>
                    </div>
                  </a>
                  <div class="p-4 flex-1 flex flex-col justify-between">
                    <p class="text-xs text-gray-300 mt-2 leading-snug">{{ Str::limit($movie->description, 90) }}</p>
                    <div class="mt-5 flex items-center justify-center rating-row">
                      @php
                        $val = $movie->rating_decimal ?? (isset($movie->user_score) ? $movie->user_score/10 : null);
                      @endphp
                      @include('components.user_score_circle', ['value' => $val, 'size' => 44, 'stroke' => 5, 'label' => 'User Score', 'showDecimal' => false])
                    </div>
                  </div>
                </article>
                @endforeach
              </div>

              <!-- Right arrow -->
              <button type="button" class="carousel-arrow right arrow-button absolute right-4 top-1/2 -translate-y-1/2 z-20" aria-label="Next">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
            </div>
            @endif
          </div>
        </section>

        <!-- Disney+ Originals Carousel -->
        <section id="category-{{ \App\Models\Category::whereIn('slug', ['disney-plus-originals','disney-plus'])->value('id') }}" class="py-16 bg-black">
          <div class="max-w-7xl mx-auto px-6">
            @php
              // Resolve either canonical slug or the legacy helper slug so the
              // dashboard works regardless of which slug is present in the DB.
              // Use explicit variable names here to avoid accidental reuse of
              // the generic `$cat` variable used elsewhere in the template.
              $disneyCat = \App\Models\Category::whereIn('slug', ['disney-plus-originals','disney-plus'])->first();
              $disneyId = $disneyCat->id ?? \App\Models\Category::whereIn('slug', ['disney-plus-originals','disney-plus'])->value('id');
            @endphp
            <div class="flex items-center justify-center mb-6 flex-col" data-category-id="{{ $disneyId }}">
              <h2 class="text-5xl font-bold flex items-center gap-4">
                <svg id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 539 294.94" class="h-10 w-auto" aria-hidden="true" role="img">
                  <defs>
                    <style>.cls-1{fill:none;}.cls-2{fill:#316777;}.cls-3{clip-path:url(#clippath);}</style>
                    <clipPath id="clippath"><rect class="cls-1" width="539" height="294.94"/></clipPath>
                  </defs>
                  <g class="cls-3">
                    <path class="cls-2" d="M34.3,209.17c-1.41-1.41-2.45-2.98-2.14-4.85.31-1.83,3.86-3.39,5.64-3.92,17.17-5.06,31.79-5.32,31.79-5.32,0,0-.16,6.16-.16,12.37-.05,11.8.26,23.8.26,23.8,0,0-19.42-5.64-35.39-22.08ZM84.62,115.26c-16.39-4.65-42.54-10.81-71.2-3.13-5.32,1.41-8.93,3.76-11.48,8.2-1.25,2.24-2.4,4.65-1.77,7.1.52,2.09,3.55,2.82,5.9,3.18,2.3.37,7.52.63,8.93-1.04.73-.89.73-3.34-3.08-4.12-1.41-.31-5.27-.42-5.53-1.1,0,0,.16-.47,2.24-1.1.94-.31,7.78-3.08,26.41-2.51,22.19.73,41.55,5.9,62.07,13.94,20.05,7.88,42.6,22.13,55.8,39.15,5.01,6.42,11.22,16.97,12.01,25.89,1.98,21.61-19.11,39.78-61.96,38.32-7.31-.26-16.23-1.98-16.23-1.98l-.89-41.66s18.11-.16,38.21,6.47c1.72.57,3.45,1.67,3.6,3.34.1,1.25-.89,2.35-2.04,3.34-2.09,1.83-4.96,2.61-5.59,3.39-.47.57-.47,1.41.16,1.88,1.51,1.15,5.22,1.93,7.73,1.72,5.06-.47,8.4-3.34,10.96-7.2,2.71-4.07,4.23-14.46-10.18-21.04-18.64-8.56-42.96-6.94-42.96-6.94,0,0-.1-5.64-.37-9.4-.42-6.16-2.14-18.32-7.78-21.77-1.04-.63-2.3-.73-3.03.26-1.1,1.41-2.19,6.84-2.4,8.25-2.14,12.63-1.98,23.91-2.14,24.07-.05.05-.1.16-.21.21-.21.16-13.68,1.72-26.68,7.36-5.17,2.24-12.58,6.26-14.83,11.48-2.92,6.73,2.19,14.09,10.81,22.24,7.31,6.89,18.06,14.15,26,17.91,2.04.94,4.44,2.14,4.44,2.14,0,0,.52,4.8.94,7.62,1.51,10.02,9.14,8.56,11.28,8.14,3.45-.68,4.07-2.98,4.54-5.64.26-1.46.16-3.55.16-3.55,0,0,5.17,1.2,8.67,1.83,10.13,1.88,21.14,2.19,30.64.89,38.11-5.22,47.71-23.13,50.79-27.93,5.27-8.35,16.5-31.53-17.23-65.98-7.73-7.93-29.96-29.6-74.7-42.23Z"/>
                    <path class="cls-2" d="M197.38,151.44c3.97-5.12,11.33-11.48,16.5-14.56,4.02-2.4,7.52-3.08,10.08-1.93,1.57.68,2.77,2.09,2.98,3.92.68,5.79-6,10.6-10.44,13.21-10.13,5.9-22.13,4.59-22.45,4.02-.05-.05,2.45-3.45,3.34-4.65ZM183.13,151.6s-.37-.78-.63-1.36c-1.67-4.12,1.98-8.77,5.32-11.59,6.53-5.53,12.06-5.43,12.06-5.43,0,0-1.31,1.04-2.4,2.04-9.5,8.3-14.36,16.34-14.36,16.34ZM208.97,165.79c8.04-3.13,14.3-7.67,20.57-14.15,3.71-3.76,6.89-9.87,5.74-15.5-.57-2.77-1.88-4.7-4.18-6.73-1.15-.99-3.81-2.4-5.95-2.82-.63-.1-1.31-.16-1.57-.37-.31-.21-.57-.57-.84-.99-1.41-1.77-4.23-3.5-6.21-4.18-6.16-2.14-14.46-.52-20.05,1.83-5.95,2.51-11.43,6.21-15.61,10.7-10.96,11.75-6.16,21.04-5.17,22.76.84,1.46,2.35,3.03,2.71,4.59.21.89,0,2.09.05,3.18.05,2.45,1.15,5.01,3.03,6.63,1.31,1.15,3.24,1.51,5.17-1.41.63-.99,1.72-2.98,1.72-2.98,0,0,2.24,1.31,7.62,1.67,3.92.31,9.4-.89,12.95-2.24Z"/>
                    <path class="cls-2" d="M427.22,218.05c-2.09,1.98-9.03,5.9-16.18,6.68,6.84-13.26,15.35-23.02,20.05-28.19,5.48-6.16,6.21-.57,6.21-.57,1.25,10.86-6.68,19.05-10.08,22.08ZM439.07,182.86c-12.95-8.98-33.77,19.42-42.96,33.51-1.36-7.15,3.5-16.29,3.5-16.29,0,0,7.78-13.47,8.87-16.18,1.04-2.77,1.67-8.4.37-8.98-1.2-.57-3.29,1.25-3.29,1.25-17.49,15.66-19.94,33.04-19.94,33.04-1.67,9.34.26,15.92,3.13,20.52-9.71,20.67-14.46,38.73-13.21,49.38,1.2,8.14,7,13.99,10.54,15.24,3.55,1.46,4.23-2.82,4.23-2.82,2.87-22.08,7.93-36.8,13.94-51.37,18.22,4.18,31.95-13.52,36.8-22.03,2.87-5.06,4.39-11.85,4.39-18.27,0-7.15-2.04-13.94-6.37-17.02Z"/>
                    <path class="cls-2" d="M379.2,228.54c.89-3.29-2.14-6.94-4.85-7.62-2.56-.52-22.08,3.13-24.43,3.76-2.51.68-1.41-1.67-1.41-1.67l3.03-10.23s18.69-.73,21.4-1.15c2.51-.47,2.66-2.24,2.66-2.24,0,0,.31-2.92.21-5.64-.26-2.92-2.61-2.66-2.61-2.66-7.2-.73-17.12.1-17.12.1l2.51-7.83s19.84-.94,27.41-2.71c3.86-.94,1.62-4.7,1.62-4.7-4.7-9.4-10.13-8.87-10.13-8.87-15.61-.52-38.32,3.6-40.77,4.85-2.3,1.04-2.09,2.77.94,6.58,3.24,4.02,8.72,3.65,8.72,3.65l-4.8,9.87c-3.92.26-5.27,1.51-5.27,1.51-2.24,2.4.84,9.5.84,9.5,0,0-6.79,15.19-1.88,23.23,5.27,8.4,11.54,8.04,11.54,8.04,11.85.73,31.37-11.69,32.42-15.77Z"/>
                    <path class="cls-2" d="M310.97,168.2s-2.77-1.31-3.45,1.46c-.68,2.71.37,5.64.37,5.64,6.89,23.33,8.46,37.12,8.46,37.12,0,0,.31,4.07-.47,4.28-.57.42-1.15-.16-1.15-.16-.78.05-8.35-12.63-8.35-12.63l-1.46-2.45c-8.3-13.36-13.36-17.96-13.36-17.96-1.2-1.1-4.18-4.12-7.99.05-3.76,4.18-6.73,17.7-7.88,30.38-.99,12.69-.26,19.89,1.62,22.92,2.04,3.08,5.95,4.12,9.19,1.51,3.45-2.61,2.77-19.31,3.34-27.88.52-8.61,1.36-7.05,1.36-7.05.73-.21,4.7,7.67,4.7,7.67,0,0,10.34,21.09,16.81,24.22,6.58,3.13,11.85-1.88,13.83-6.58,2.04-4.75,5.48-20.41.37-37.74-5.27-17.49-15.92-22.81-15.92-22.81Z"/>
                    <path class="cls-2" d="M203.69,212.83c-.26-5.79-.94-20.52-2.56-27.2-.68-2.87-1.88-5.48-4.7-6.58-.31-.1-.68-.21-.94-.26-1.77-.37-2.77,2.09-3.24,3.29-1.41,3.6-2.4,12.11-2.66,14.77-.84,8.09-1.51,27.2-.1,36.96.57,4.07,1.93,6.73,6.06,7.62,1.67.37,4.75.26,6.06-.57,1.15-.73,1.98-2.14,2.24-3.39.78-3.39.47-8.2.31-11.85-.16-4.18-.26-8.46-.47-12.79Z"/>
                    <path class="cls-2" d="M270.46,180.62c-8.93-4.75-23.6-4.75-32.73-3.6-9.4,1.15-21.98,4.07-27.3,11.8-2.04,2.92-3.24,7.46-2.3,11.12.52,2.04,1.98,3.6,4.12,4.7,3.81,1.98,16.5,2.4,22.13,3.03,5.32.57,15.14,1.51,20.57,3.76,0,0,5.12,1.77,5.06,4.7,0,1.46-1.1,2.61-2.19,3.39-8.04,5.69-19.05,7.31-28.87,7.15-3.34-.05-6.06-.57-9.45-2.04-1.46-.63-3.29-1.77-3.03-3.76.42-3.13,4.02-3.92,6.84-3.65,1.62.16,3.65.84,5.22,1.41,6.32,2.51,9.19,4.59,14.72,3.71,2.09-.31,9.24-1.88,10.18-4.8.26-.73-.1-1.51-.63-2.04-.73-.78-5.06-2.45-6.21-2.87-4.23-1.51-12.22-2.87-16.65-3.03-5.85-.21-13.83.99-17.17,5.79-2.98,4.33-3.65,13.1,11.22,20.52,6.89,3.45,15.3,4.96,23.02,4.12,4.44-.52,17.12-2.51,21.92-12.58,2.61-5.43,2.35-12.69-.47-17.85-1.83-3.39-4.07-7.93-20.67-10.02-2.66-.31-22.19-2.61-23.96-2.87-.99-.16-5.59-.68-3.97-2.45.47-.52,1.83-.89,2.66-1.1,10.23-2.82,23.23-2.45,34.04-3.08,2.4-.16,7.88-.47,10.49-1.31,2.51-.78,4.59-2.56,5.17-5.32.31-1.41-.42-2.09-1.77-2.82Z"/>
                    <path class="cls-2" d="M536.69,193.2h-30.64c-.31-10.6-1.41-21.04-3.18-31.32-.31-1.77-1.83-3.03-3.65-3.03h-8.25c-1.62,0-2.82,1.46-2.56,3.08,1.93,10.23,3.08,20.72,3.45,31.32h-31.16c-1.41,0-2.56,1.15-2.56,2.61v9.4c0,1.41,1.15,2.61,2.56,2.61h31.16c-.37,10.6-1.51,21.04-3.45,31.32-.31,1.57.94,3.08,2.56,3.08h8.25c1.77,0,3.34-1.31,3.65-3.03,1.77-10.28,2.87-20.72,3.18-31.32h30.64c1.41,0,2.56-1.15,2.56-2.61v-9.4c0-1.57-1.15-2.71-2.56-2.71Z"/>
                    <path class="cls-2" d="M114.43,79.56c1.2.84,2.92.21,4.12-1.1,10.49-11.33,22.03-21.19,35.13-30.12C239.66-10.34,365.89-1.77,439.75,72.56c24.27,24.27,37.64,50.38,44.63,70.89.52,1.51,1.93,2.51,3.5,2.51h8.46c1.77,0,3.03-1.72,2.45-3.39-9.19-27.2-26.99-57-52.83-81.28C368.44-11.69,242.85-19.78,155.3,39.83c-14.3,9.66-28.55,22.03-41.03,36.18-1.2,1.36-.78,2.87.16,3.55Z"/>
                  </g>
                </svg>
                Disney+ Originals
              
              </h2>
              <p class="mt-4 text-center text-gray-300 max-w-3xl">
                A curated collection of Disney+ Originals — exclusive films and premieres from Disney's streaming service. Browse titles assigned to the Disney+ Originals category below.
              </p>
            </div>
 

      @php
        // Prefer an explicit $disneyMovies collection passed from a controller
        $disney = $disneyMovies ?? collect();

        // If the controller didn't supply $disneyMovies, do not perform
        // an additional category DB lookup here — prefer the controller as
        // the single source of truth. The final fallback below still
        // performs a heuristic filter on $movies when no explicit
        // collection is available.
        // (This keeps view logic simple and avoids surprising results.)

        // Final fallback: filter the provided $movies collection for 'disney' keywords
        if ((empty($disney) || $disney->count() === 0) && isset($movies) && $movies->count()) {
          $source = $movies instanceof \Illuminate\Support\Collection ? $movies : collect($movies);

          $disney = $source->filter(function($movie) {
            // Prefer explicit category assignments over heuristic matching
            if (isset($movie->category_id) && !empty($movie->category_id)) {
              return false;
            }
            $hay = '';
            $hay .= isset($movie->title) ? ' ' . $movie->title : '';
            $hay .= isset($movie->studio) ? ' ' . $movie->studio : '';
            $hay .= isset($movie->production_company) ? ' ' . $movie->production_company : '';
            $hay .= isset($movie->collection) ? ' ' . $movie->collection : '';
            $hay .= isset($movie->brand) ? ' ' . $movie->brand : '';

            if (isset($movie->keywords)) {
              if (is_array($movie->keywords)) {
                $hay .= ' ' . implode(' ', $movie->keywords);
              } else {
                $hay .= ' ' . $movie->keywords;
              }
            }

            return stripos($hay, 'disney') !== false || stripos($hay, 'disney+') !== false || stripos($hay, 'disney plus') !== false;
          })->values();
        }
      @endphp

            @if($disney->count())
            <div class="relative">
              <!-- Left arrow -->
              <button type="button" class="carousel-arrow left arrow-button absolute left-4 top-1/2 -translate-y-1/2 z-20" aria-label="Previous">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>

              <!-- Carousel strip -->
              <div class="carousel overflow-x-auto scrollbar-hidden snap-x snap-mandatory flex gap-6 px-6 py-2">
                @foreach($disney as $movie)
                <article class="movie-card snap-center bg-white/5 rounded-lg overflow-hidden flex-shrink-0 flex flex-col w-[260px] md:w-[300px] lg:w-[320px] cursor-pointer" aria-labelledby="disney-movie-{{ $movie->id }}-title">
                  <a href="{{ route('movies.show', $movie->id) }}" class="absolute inset-0 z-10" tabindex="-1" aria-hidden="true"></a>
                  <a href="{{ route('movies.show', $movie->id) }}" class="media block w-full bg-gray-800 overflow-hidden flex-shrink-0">
                    <img src="{{ $movie->image_url ?? asset('image/placeholder.png') }}" alt="{{ $movie->title }} poster" class="w-full h-[330px] md:h-[360px] lg:h-[380px] object-cover lazy block">
                    <div class="overlay">
                      @php
                        $rawTitle = trim($movie->title ?? '');
                        $first = $rawTitle !== '' ? mb_strtoupper(mb_substr($rawTitle, 0, 1, 'UTF-8'), 'UTF-8') : '';
                        $rest = $rawTitle !== '' ? mb_substr($rawTitle, 1, mb_strlen($rawTitle, 'UTF-8'), 'UTF-8') : '';
                        $titleCap = $first . $rest;
                      @endphp
                      <h3 id="disney-movie-{{ $movie->id }}-title" class="movie-title text-white text-sm font-medium tracking-tight">{{ $titleCap }}</h3>
                    </div>
                  </a>
                  <div class="p-4 flex-1 flex flex-col justify-between">
                    <p class="text-xs text-gray-300 mt-2 leading-snug">{{ Str::limit($movie->description, 90) }}</p>
                    <div class="mt-5 flex items-center justify-center rating-row">
                      @php
                        $val = $movie->rating_decimal ?? (isset($movie->user_score) ? $movie->user_score/10 : null);
                      @endphp
                      @include('components.user_score_circle', ['value' => $val, 'size' => 44, 'stroke' => 5, 'label' => 'User Score', 'showDecimal' => false])
                    </div>
                  </div>
                </article>
                @endforeach
              </div>

              <!-- Right arrow -->
              <button type="button" class="carousel-arrow right arrow-button absolute right-4 top-1/2 -translate-y-1/2 z-20" aria-label="Next">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
            </div>
            @else
            {{-- Disney no-movies message removed to keep parity with MCU behavior --}}
            @endif
          </div>
        </section>

        <!-- Horror Carousel -->
          <section id="category-{{ $cat->id ?? \App\Models\Category::where('slug','horror')->value('id') }}" class="py-16 bg-black">
              <div class="max-w-7xl mx-auto px-6">
                  <div class="flex items-center justify-center mb-6 flex-col">
                    @php $horrorId = $cat->id ?? \App\Models\Category::where('slug','horror')->value('id'); @endphp
                    <h2 class="text-5xl font-bold flex items-center gap-4" data-category-id="{{ $cat->id ?? \App\Models\Category::where('slug','horror')->value('id') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-10 w-auto" aria-hidden="true" role="img"><path d="M12 2C8.13 2 5 5.13 5 9c0 4.97 7 13 7 13s7-8.03 7-13c0-3.87-3.13-7-7-7z" fill="#9f1f1f"/></svg>
                   Horror
                  
                  </h2>
                  <p class="mt-4 text-center text-gray-300 max-w-3xl">
                    A collection of Horror titles — movies that deliver scares, suspense and thrills. Browse titles assigned to the Horror category below.
                  </p>
                </div>

          @php
            // Prefer an explicit $horrorMovies collection passed from a controller
            $horror = $horrorMovies ?? collect();

            // If the controller didn't supply $horrorMovies, do not perform
            // an additional category DB lookup here — prefer the controller
            // as the single source of truth. The final fallback below will
            // still perform a heuristic filter on $movies when no explicit
            // collection is available.
            // (This keeps view logic simple and avoids surprising results.)

            // Final fallback: filter the provided $movies collection for 'horror' keywords
            if ((empty($horror) || $horror->count() === 0) && isset($movies) && $movies->count()) {
              $source = $movies instanceof \Illuminate\Support\Collection ? $movies : collect($movies);

              $horror = $source->filter(function($movie) {
                // Skip movies that already have an explicit category assignment
                if (isset($movie->category_id) && !empty($movie->category_id)) {
                  return false;
                }
                $hay = '';
                $hay .= isset($movie->title) ? ' ' . $movie->title : '';
                $hay .= isset($movie->studio) ? ' ' . $movie->studio : '';
                $hay .= isset($movie->production_company) ? ' ' . $movie->production_company : '';
                $hay .= isset($movie->collection) ? ' ' . $movie->collection : '';
                $hay .= isset($movie->brand) ? ' ' . $movie->brand : '';

                if (isset($movie->keywords)) {
                  if (is_array($movie->keywords)) {
                    $hay .= ' ' . implode(' ', $movie->keywords);
                  } else {
                    $hay .= ' ' . $movie->keywords;
                  }
                }

                return stripos($hay, 'horror') !== false || stripos($hay, 'scary') !== false || stripos($hay, 'thriller') !== false;
              })->values();
            }
          @endphp

                @if($horror->count())
                <div class="relative">
                  <!-- Left arrow -->
                  <button type="button" class="carousel-arrow left arrow-button absolute left-4 top-1/2 -translate-y-1/2 z-20" aria-label="Previous">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>

                  <!-- Carousel strip -->
                  <div class="carousel overflow-x-auto scrollbar-hidden snap-x snap-mandatory flex gap-6 px-6 py-2">
                    @foreach($horror as $movie)
                    <article class="movie-card snap-center bg-white/5 rounded-lg overflow-hidden flex-shrink-0 flex flex-col w-[260px] md:w-[300px] lg:w-[320px] cursor-pointer" aria-labelledby="horror-movie-{{ $movie->id }}-title">
                      <a href="{{ route('movies.show', $movie->id) }}" class="absolute inset-0 z-10" tabindex="-1" aria-hidden="true"></a>
                      <a href="{{ route('movies.show', $movie->id) }}" class="media block w-full bg-gray-800 overflow-hidden flex-shrink-0">
                        <img src="{{ $movie->image_url ?? asset('image/placeholder.png') }}" alt="{{ $movie->title }} poster" class="w-full h-[330px] md:h-[360px] lg:h-[380px] object-cover lazy block">
                        <div class="overlay">
                          @php
                            $rawTitle = trim($movie->title ?? '');
                            $first = $rawTitle !== '' ? mb_strtoupper(mb_substr($rawTitle, 0, 1, 'UTF-8'), 'UTF-8') : '';
                            $rest = $rawTitle !== '' ? mb_substr($rawTitle, 1, mb_strlen($rawTitle, 'UTF-8'), 'UTF-8') : '';
                            $titleCap = $first . $rest;
                          @endphp
                          <h3 id="horror-movie-{{ $movie->id }}-title" class="movie-title text-white text-sm font-medium tracking-tight">{{ $titleCap }}</h3>
                        </div>
                      </a>
                      <div class="p-4 flex-1 flex flex-col justify-between">
                        <p class="text-xs text-gray-300 mt-2 leading-snug">{{ Str::limit($movie->description, 90) }}</p>
                        <div class="mt-5 flex items-center justify-center rating-row">
                          @php
                            $val = $movie->rating_decimal ?? (isset($movie->user_score) ? $movie->user_score/10 : null);
                          @endphp
                          @include('components.user_score_circle', ['value' => $val, 'size' => 44, 'stroke' => 5, 'label' => 'User Score', 'showDecimal' => false])
                        </div>
                      </div>
                    </article>
                    @endforeach
                  </div>

                  <!-- Right arrow -->
                  <button type="button" class="carousel-arrow right arrow-button absolute right-4 top-1/2 -translate-y-1/2 z-20" aria-label="Next">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                </div>
                @else
                {{-- Horror no-movies message removed to keep parity with other carousels --}}
                @endif
              </div>
            </section>

    <!-- Main Content -->
    <main class="pt-20">
      
        
        
        
        
        
        
        
        
          <ul class="logo-grid">
          <li>
        <svg xmlns="http://www.w3.org/2000/svg" 
         class="" 
         fill="none" 
         role="img" 
         aria-label="Roku">
    <path fill="#fff" fill-opacity="0.5"
      d="M17.906 8.209c0-4.105-4.025-7.501-9.057-7.501H.224v22.361H6.26v-7.5h1.726l5.462 7.5h6.9l-6.325-8.633c2.3-1.415 3.882-3.68 3.882-6.227zm-9.92 3.68H6.119V4.386h1.869c2.156 0 3.737 1.699 3.737 3.68 0 2.123-1.581 3.821-3.737 3.821zM27.25 5.235c-5.175 0-9.2 3.963-9.2 9.058s4.168 9.058 9.2 9.058c5.175 0 9.344-3.963 9.344-9.058-.144-4.953-4.313-9.058-9.344-9.058m0 14.012c-1.582 0-3.02-2.123-3.02-4.812s1.438-4.812 3.02-4.812c1.581 0 3.018 2.123 3.018 4.812 0 2.547-1.437 4.812-3.018 4.812M68.364 5.66v11.322c-.72 1.133-1.582 1.84-3.02 1.84s-2.156-.849-2.156-3.68V5.52H50.825l-6.9 6.793V5.52h-6.037v17.55h6.037v-7.077l7.332 7.076h7.619l-9.2-9.058 7.475-7.5v10.33c0 3.398 2.156 6.653 7.331 6.653 2.444 0 4.744-1.416 6.038-2.69l2.731 2.407h1.15V5.52s-6.037 0-6.037.141z" />
  </svg>
</li>





  
<li>
  <svg xmlns="http://www.w3.org/2000/svg" 
       class="" 
       fill="none" 
       role="img" 
       aria-label="Samsung">
    <g fill="#fff" fill-opacity="0.5" fill-rule="evenodd" clip-path="url(#samsung_svg__a)" clip-rule="evenodd">
      <path d="M61.033 8.366s-1.075-.661-1.736-.923c0 0-2.052-.95-2.465-1.404 0 0-.799-.757-.33-1.631 0 0 .199-.572 1.04-.572 0 0 1.101.062 1.101.964v1.28h3.946l-.007-1.879s.303-3.077-4.648-3.214c0 0-3.898-.255-5.014 1.845 0 0-.44.468-.44 2.099v1.17s-.048 1.356.647 2.237c0 0 .392.572 1.357 1.205 0 0 1.962 1.06 3.14 1.659 0 0 1.191.681 1.033 1.755 0 0-.097 1.108-1.247 1.06 0 0-1.053-.048-1.053-1.136v-1.273h-4.208v1.851s-.117 3.545 5.289 3.545c0 0 5.165.104 5.406-3.64v-1.508c.014.013.192-2.279-1.811-3.49M40.814 1.427l-1.322 8.281h-.31l-1.26-8.205h-6.597l-.33 14.999h3.904l.048-11.303h.31l2.066 11.296h4.11L43.48 5.206h.269l.096 11.296h3.925l-.42-15.075zm-23.242.063L14.97 16.48h4.208l1.535-11.626h.345L22.6 16.48h4.2L24.204 1.49zm88.215 6.67v2.209h1.081v2.375c0 1.163-.992 1.184-.992 1.184-1.205 0-1.163-1.102-1.163-1.102V4.614c0-.846 1.067-.888 1.067-.888 1.026 0 1.033 1.012 1.033 1.012v1.246h3.96c.13-2.437-.358-3.029-.358-3.029C109.43.753 105.718.87 105.718.87c-5.709 0-5.075 4.378-5.075 4.378v7.943c.124 4.103 5.743 3.69 5.839 3.683 2.5-.276 3.258-1.067 3.258-1.067a2.44 2.44 0 0 0 .874-1.232c.2-.42.248-1.742.248-1.742V8.166h-5.075zm-13.125 2.443h-.172l-4.015-9.169h-4.807v15.068h3.912l-.235-9.169h.18l4.159 9.169h4.662V1.427h-3.96zm-18.539 2.14s.055 1.24-1.088 1.24c0 0-1.205.061-1.205-1.205L71.816 1.44h-4.29v11.247s-.44 4.254 5.571 4.254c0 0 5.22.062 5.22-4.06V1.44h-4.194v11.302zM9.391 8.366s-1.074-.66-1.735-.922c0 0-2.052-.95-2.459-1.404 0 0-.798-.757-.33-1.632 0 0 .193-.57 1.04-.57 0 0 1.102.061 1.102.963v1.28h3.952l-.007-1.88s.303-3.076-4.648-3.214c0 0-.372-.027-.923 0 0 0-3.002.159-4.07 1.804a.2.2 0 0 0-.034.041s-.44.468-.44 2.1v1.17s-.049 1.356.647 2.237c0 0 .392.571 1.356 1.204 0 0 1.963 1.06 3.14 1.66 0 0 1.192.68 1.033 1.761 0 0-.096 1.109-1.246 1.06 0 0-1.054-.048-1.054-1.135v-1.28H.522v1.858s-.118 3.545 5.288 3.545c0 0 5.165.103 5.406-3.641v-1.508c0 .007.186-2.285-1.825-3.497"></path>
    </g>
    <defs>
      <clipPath id="samsung_svg__a">
        <path fill="#fff" fill-opacity="0.5" d="M.522.87H110.87v16.148H.522z"></path>
      </clipPath>
    </defs>
  </svg>
</li>






<li>
  <svg xmlns="http://www.w3.org/2000/svg" 
       class="" 
       fill="none" 
       role="img" 
       aria-label="Amazon Fire TV">
    <path fill="#fff" fill-opacity="0.5" fill-rule="evenodd" 
      d="M49.91 27.354c-5.287 3.9-12.955 5.977-19.555 5.977-9.252 0-17.585-3.421-23.889-9.113-.494-.449-.053-1.058.541-.712 6.803 3.96 15.214 6.343 23.903 6.343 5.86 0 12.303-1.216 18.233-3.73.895-.38 1.642.589.768 1.235z" 
      clip-rule="evenodd"/>
    <path fill="#fff" fill-opacity="0.5" fill-rule="evenodd" 
      d="M52.111 24.842c-.674-.868-4.468-.412-6.174-.207-.516.06-.597-.39-.131-.717 3.026-2.127 7.987-1.513 8.562-.799.582.716-.151 5.69-2.988 8.063-.436.365-.852.17-.657-.312.638-1.594 2.066-5.163 1.389-6.028zM30.54 7.983c2.978 0 2.951 2.605 2.878 3.728h-6.293c.114-1.954 1.057-3.728 3.416-3.728zm4.769 5.458c.38 0 .429-1.25.446-1.776.144-3.79-1.857-5.629-5.11-5.629-1.793 0-3.45.648-4.485 1.9-.963 1.146-1.516 2.764-1.516 4.944 0 2.145.487 3.753 1.42 4.883.963 1.143 2.42 1.942 4.959 1.942.96 0 2.826-.204 3.996-.789.431-.158.436-.353.436-.76v-.704c0-.38-.166-.537-.577-.403-1.348.454-2.7.634-3.516.634-1.406 0-2.637-.407-3.314-1.21-.67-.87-.892-1.85-.948-3.032zM4.917 6.426V3.918c0-2.005 2.01-1.75 3.29-1.586.57.11.784.015.784-.431v-.829c0-.414-.166-.56-.729-.711-3.148-.582-5.886.044-5.886 4.042v2.088l-1.842.215C.144 6.74 0 6.862 0 7.254v.66c0 .359.154.502.5.502h1.876v10.387c0 .359.143.502.502.502h1.537c.358 0 .502-.143.502-.502V8.416h3.406c.356 0 .5-.143.5-.502v-.986c0-.359-.163-.502-.5-.502zm8.097.017h-1.508c-.359 0-.502.14-.502.499v11.861c0 .358.143.502.502.502h1.508c.358 0 .502-.143.502-.502V6.942c0-.287-.144-.5-.502-.5zm-.76-5.361c-.885 0-1.562.597-1.562 1.545 0 .96.621 1.542 1.586 1.542s1.588-.594 1.588-1.542c0-1.048-.66-1.545-1.612-1.545m44.08 18.224h-1.608c-.36 0-.558-.144-.701-.502L49.359 7.035c-.144-.358-.052-.592.326-.592h1.723c.346 0 .484.212.597.5l3.53 10.038 3.507-10.039c.158-.358.314-.5.672-.5h1.542c.43 0 .53.223.37.58l-4.67 11.782c-.127.287-.276.502-.622.502m-14.746-3.299c0 2.994 2.063 3.489 3.557 3.489.802 0 1.45-.083 2.095-.22.79-.229.78-.363.78-.72v-.914c0-.359-.173-.47-.509-.393-.604.098-1.004.168-1.603.193-1.657-.115-1.81-.646-1.81-2.261V8.417h3.333c.355 0 .5-.144.5-.502V6.913c0-.36-.164-.502-.5-.502h-3.334V3.32c0-.285-.141-.502-.502-.502h-1.008c-.393 0-.588.144-.624.536l-.32 3.122-1.88.231c-.393.034-.536.23-.536.624v.585c0 .358.214.502.5.502h1.86v7.59zM19.232 8.292l-.185-1.447c-.08-.356-.285-.405-.58-.405l-1.125.003c-.359 0-.502.14-.502.5v11.86c0 .359.143.502.502.502h1.5c.356 0 .5-.143.5-.502V9.817c.98-.719 1.825-1.208 3.75-1.208.47.01 1.135.187 1.135-.317V6.82c0-.329-.197-.511-.828-.511-1.589 0-2.654.402-4.167 1.983" 
      clip-rule="evenodd"/>
  </svg>
</li>












<li><svg xmlns="http://www.w3.org/2000/svg" width="57" height="28" fill="none" role="img" aria-label="Apple TV"><path fill="#fff" fill-opacity="0.5" fill-rule="evenodd" d="M32.735 2.76v4.37h5.482v1.567h-5.482v12.901q0 1.98.64 3.03.637 1.05 2.205 1.05.742.001 1.278-.082.536-.081.907-.206l.206 1.484q-.99.454-2.638.454-1.36 0-2.246-.454a3.54 3.54 0 0 1-1.402-1.257q-.516-.803-.722-1.937a13.6 13.6 0 0 1-.206-2.453V8.697H27.5V7.13h3.257V3.502zm8.657 4.37 4.534 12.283q.577 1.444 1.03 2.72.453 1.28.866 2.515h.082q.412-1.196.886-2.494.475-1.3 1.051-2.741l4.575-12.284h2.061l-7.873 19.785h-1.69L39.33 7.13h2.06zM12.159 2.911C13.918.596 16.36.585 16.36.585s.364 2.178-1.383 4.275c-1.863 2.24-3.982 1.873-3.982 1.873s-.398-1.761 1.165-3.82zm-.94 5.347c.903 0 2.58-1.243 4.765-1.243 3.759 0 5.237 2.675 5.237 2.675s-2.892 1.478-2.892 5.066c0 4.048 3.603 5.443 3.603 5.443s-2.518 7.088-5.92 7.088c-1.563 0-2.777-1.053-4.424-1.053-1.677 0-3.343 1.093-4.426 1.093-3.107 0-7.032-6.725-7.032-12.13C.13 9.88 3.453 7.09 6.568 7.09c2.026 0 3.598 1.169 4.65 1.169z" clip-rule="evenodd"></path></svg></li>




<li><svg xmlns="http://www.w3.org/2000/svg" width="73" height="34" fill="none" role="img" aria-label="iOS"><path fill="#fff" fill-opacity="0.5" fill-rule="evenodd" d="M12.029 2.912c1.758-2.316 4.2-2.327 4.2-2.327s.364 2.178-1.382 4.275c-1.864 2.24-3.982 1.873-3.982 1.873s-.398-1.761 1.164-3.82zm-.941 5.347c.904 0 2.58-1.243 4.765-1.243 3.76 0 5.238 2.675 5.238 2.675s-2.892 1.478-2.892 5.066c0 4.048 3.602 5.443 3.602 5.443s-2.518 7.088-5.92 7.088c-1.562 0-2.777-1.053-4.423-1.053-1.678 0-3.343 1.093-4.427 1.093C3.925 27.328 0 20.603 0 15.198 0 9.88 3.322 7.09 6.437 7.09c2.026 0 3.598 1.169 4.65 1.169z" clip-rule="evenodd"></path><path fill="#fff" fill-opacity="0.5" d="M31.4 8.953c.506 0 .916-.41.916-.916s-.41-.93-.916-.93a.93.93 0 0 0-.93.93c0 .506.41.916.93.916M30.867 27h1.053V12.275h-1.053zm14.11-20.057c-5.4 0-8.764 3.91-8.764 10.186 0 6.289 3.322 10.2 8.764 10.2s8.777-3.911 8.777-10.2c0-6.276-3.35-10.186-8.777-10.186m0 1.012c4.689 0 7.683 3.582 7.683 9.174 0 5.578-2.994 9.187-7.683 9.187-4.704 0-7.67-3.609-7.67-9.187 0-5.592 2.98-9.174 7.67-9.174M57.09 21.9c.191 3.172 3.185 5.428 7.123 5.428 4.088 0 6.877-2.31 6.877-5.523 0-2.639-1.71-4.28-5.783-5.264l-1.791-.438c-3.405-.82-4.8-1.955-4.8-3.91 0-2.365 2.23-4.252 5.346-4.252 3.09 0 5.305 1.737 5.565 3.952h1.08c-.205-2.776-2.898-4.95-6.617-4.95-3.787 0-6.48 2.297-6.48 5.278 0 2.502 1.585 3.95 5.468 4.894l1.9.465c3.473.848 5.004 2.242 5.004 4.307 0 2.502-2.365 4.443-5.7 4.443-3.282 0-5.907-1.873-6.112-4.43z"></path></svg></li>



<li><svg xmlns="http://www.w3.org/2000/svg" width="124" height="24" fill="none" role="img" aria-label="Chromecast"><path fill="#fff" fill-opacity="0.5" d="M31.852 12.304c0-2.582 1.901-4.557 4.595-4.557 2.14 0 3.327 1.216 3.883 2.43l-1.506.685c-.396-.987-1.268-1.595-2.456-1.595-1.426 0-2.773 1.215-2.773 3.114 0 1.823 1.347 3.115 2.773 3.115 1.268 0 2.14-.608 2.615-1.595l1.506.608c-.554 1.215-1.822 2.43-3.961 2.43-2.775-.078-4.676-2.053-4.676-4.635M43.34 8.05l-.08 1.215h.08c.475-.76 1.584-1.443 2.852-1.443 2.297 0 3.407 1.443 3.407 3.569v5.24h-1.664V11.62c0-1.746-.95-2.354-2.218-2.354-1.426 0-2.377 1.292-2.377 2.658v4.71h-1.664V4.177h1.664zm8.002 8.583V8.05h1.584v1.366h.08c.396-.987 1.664-1.594 2.615-1.594.554 0 .872.076 1.268.228l-.634 1.518a3 3 0 0 0-.872-.152c-1.11 0-2.297.911-2.297 2.507v4.71zm14.737-4.33c0 2.582-1.901 4.556-4.595 4.556s-4.595-1.974-4.595-4.556 1.901-4.557 4.595-4.557 4.595 1.975 4.595 4.557m-1.663 0c0-1.975-1.426-3.115-2.932-3.115-1.505 0-2.931 1.14-2.931 3.115s1.425 3.114 2.931 3.114 2.931-1.14 2.931-3.114zm3.01-4.253h1.585v1.215h.08c.475-.836 1.663-1.443 2.773-1.443 1.426 0 2.377.607 2.852 1.67a3.63 3.63 0 0 1 3.09-1.67c2.139 0 3.168 1.443 3.168 3.569v5.24h-1.743V11.62c0-1.746-.713-2.354-1.981-2.354-1.348 0-2.298 1.292-2.298 2.658v4.71H73.29V11.62c0-1.746-.713-2.354-1.98-2.354-1.348 0-2.299 1.292-2.299 2.658v4.71h-1.663V8.052h.08V8.05zm14.738 4.253c0-2.43 1.743-4.557 4.357-4.557 2.615 0 4.357 1.823 4.357 4.557v.303h-7.052c.08 1.823 1.426 2.81 2.852 2.81.95 0 1.981-.38 2.456-1.443l1.505.607c-.554 1.216-1.822 2.355-3.883 2.355-2.77-.075-4.592-2.05-4.592-4.632m4.357-3.038c-1.268 0-2.218.835-2.535 2.05h5.15c-.08-.759-.713-2.05-2.615-2.05m5.309 3.038c0-2.582 1.902-4.557 4.595-4.557 2.14 0 3.328 1.216 3.883 2.43l-1.505.608c-.397-.987-1.268-1.594-2.456-1.594-1.426 0-2.774 1.215-2.774 3.114 0 1.823 1.348 3.114 2.774 3.114 1.267 0 2.14-.607 2.615-1.594l1.505.607c-.554 1.216-1.821 2.43-3.96 2.43-2.775 0-4.677-1.976-4.677-4.558m9.508 1.747c0-1.899 1.822-2.961 3.883-2.961 1.188 0 2.06.303 2.377.531v-.304c0-1.292-1.109-2.051-2.297-2.051-.951 0-1.822.456-2.14 1.215l-1.505-.608c.316-.759 1.347-2.05 3.645-2.05 2.139 0 3.961 1.215 3.961 3.645v5.164h-1.584v-1.215h-.08c-.476.684-1.426 1.443-2.931 1.443-1.823.002-3.329-1.062-3.329-2.809m6.26-1.14s-.713-.53-2.139-.53c-1.744 0-2.456.91-2.456 1.67 0 .911.95 1.367 1.822 1.367 1.426 0 2.773-1.063 2.773-2.507m2.694 1.52 1.505-.608c.476 1.064 1.348 1.595 2.378 1.595s1.743-.456 1.743-1.215c0-.456-.238-.91-1.188-1.139l-1.822-.38c-.792-.227-2.377-.835-2.377-2.353s1.664-2.507 3.486-2.507c1.505 0 2.851.684 3.407 1.974l-1.426.608c-.316-.836-1.188-1.14-2.06-1.14-.95 0-1.743.38-1.743 1.065 0 .53.475.835 1.188.987l1.743.38c1.744.379 2.456 1.443 2.456 2.506 0 1.519-1.426 2.659-3.486 2.659s-3.329-1.217-3.804-2.432m9.508-.456v-4.48h-1.584V8.05h1.584V5.468h1.664v2.583h2.139v1.443h-2.139v4.33c0 .987.396 1.366 1.188 1.366.317 0 .554 0 .792-.151l.554 1.367c-.396.151-.792.228-1.348.228-1.82.075-2.85-.912-2.85-2.659zM24.007 7.368h-8.556c1.584.987 2.693 2.658 2.693 4.633 0 .38-.08.835-.158 1.215v.076c-.08.608-.316 1.139-.634 1.671l-5.387 9.038h.476c6.893 0 12.519-5.317 12.519-12-.001-1.672-.319-3.266-.953-4.633M12.52 17.393c-1.584 0-3.09-.684-4.12-1.671a7.7 7.7 0 0 1-1.03-1.216l-.157-.151-5.31-8.735A11.7 11.7 0 0 0 0 12c0 6.153 4.753 11.166 10.934 11.848l4.279-7.14c-.792.457-1.744.685-2.694.685zM12.52 0C8.4 0 4.755 1.9 2.457 4.86l4.279 6.988c.08.076.08.228.08.303v-.228c0-2.658 2.06-4.937 4.675-5.317h.08c.475-.151.95-.151 1.425-.151h10.538C21.472 2.658 17.273 0 12.519 0zm4.516 12c0 2.431-2.06 4.33-4.517 4.33C9.982 16.33 8 14.356 8 12s2.061-4.33 4.518-4.33c2.456-.075 4.516 1.9 4.516 4.33z"></path></svg></li>



<li><svg xmlns="http://www.w3.org/2000/svg" width="107" height="33" fill="none" role="img" aria-label="Xbox"><path fill="#fff" fill-opacity="0.5" d="M14.865 32.557c-2.462-.233-4.956-1.113-7.102-2.505-1.797-1.165-2.203-1.642-2.203-2.6 0-1.918 2.125-5.28 5.754-9.11 2.064-2.176 4.935-4.726 5.245-4.659.605.134 5.433 4.818 7.241 7.019 2.86 3.49 4.177 6.344 3.508 7.616-.508.968-3.657 2.857-5.97 3.582-1.908.6-4.413.855-6.473.657m67.416-25.09c-5.359 0-9.086 4.006-9.086 9.754s3.727 9.753 9.086 9.753c5.328 0 9.055-4.005 9.055-9.753s-3.727-9.754-9.055-9.754m15.677 7.675 5.436-7.247h2.51l-6.683 8.883 7.315 9.784v.015h-2.51l-6.068-8.103-6.052 8.103h-2.51l7.3-9.784-6.669-8.898h2.495zm-49.818 0 5.436-7.247h2.495l-6.668 8.883 7.3 9.784v.015h-2.51l-6.053-8.103-6.067 8.103h-2.51l7.314-9.784-6.683-8.898h2.51zm17.31-7.247c5.728 0 6.175 3.883 6.175 5.076 0 1.605-.909 2.996-2.28 3.73q.647.276 1.248.78c1.093.902 1.663 2.155 1.648 3.623 0 3.363-2.603 5.458-6.792 5.458h-7.715v-8.745h-3.958a64 64 0 0 1 1.386-1.865h2.572V7.895h7.715zM3.136 25.46C1.647 23.185.892 20.942.525 17.703c-.12-1.07-.078-1.681.274-3.878.438-2.738 2.004-5.903 3.89-7.853.804-.83.875-.851 1.854-.523 1.188.4 2.455 1.268 4.422 3.038l1.15 1.031-.627.767c-2.914 3.554-5.985 8.59-7.141 11.706-.63 1.692-.882 3.395-.612 4.101.182.477.014.3-.598-.632zm26.189.385c.146-.713-.04-2.027-.477-3.352-.943-2.868-4.106-8.21-7.01-11.837l-.914-1.141.99-.904c1.29-1.18 2.188-1.887 3.155-2.484.765-.473 1.854-.89 2.324-.89.288 0 1.306 1.05 2.128 2.197 1.274 1.774 2.21 3.928 2.683 6.168.306 1.448.33 4.546.05 5.987-.232 1.184-.723 2.72-1.2 3.766-.359.78-1.249 2.296-1.64 2.79-.2.248-.2.248-.089-.3m52.955-.843c-4.08 0-6.93-3.195-6.93-7.781S78.2 9.44 82.28 9.44c4.066 0 6.9 3.195 6.9 7.781 0 4.571-2.834 7.781-6.9 7.781m-16.816-.397c1.725 0 4.65-.459 4.65-3.547 0-2.675-2.525-3.24-4.65-3.24h-5.59v6.787zm0-8.668c2.572 0 4.05-1.177 4.05-3.134 0-2.6-2.541-2.966-4.05-2.966h-5.59v6.1zM15.129 4.693c-1.342-.678-3.41-1.402-4.551-1.6a8.6 8.6 0 0 0-1.52-.085c-.943.046-.9 0 .612-.71 1.256-.59 2.306-.936 3.73-1.232 1.6-.336 4.607-.34 6.184-.008 1.7.357 3.704 1.099 4.832 1.788l.334.205-.768-.04c-1.53-.077-3.758.538-6.153 1.696-.722.35-1.348.63-1.395.618-.042-.007-.633-.293-1.305-.632"></path></svg></li>




<li><svg xmlns="http://www.w3.org/2000/svg" width="58" height="57" fill="none" role="img" aria-label="nVidia"><g fill="#fff" fill-opacity="0.5" clip-path="url(#nvidia_svg__a)"><path d="M24.065 40.035v10.619h3.002v-10.62zM.478 40.023v10.63h3.025v-8.076h2.343c.775 0 1.33.194 1.706.593.476.504.67 1.318.67 2.814v4.67h2.93v-5.872c0-4.193-2.67-4.759-5.284-4.759zm28.417.011v10.62h4.864c2.593 0 3.435-.432 4.354-1.396.648-.676 1.064-2.166 1.064-3.795 0-1.49-.355-2.825-.97-3.65-1.113-1.485-2.709-1.773-5.102-1.773h-4.21zm2.975 2.316h1.29c1.873 0 3.08.842 3.08 3.019 0 2.183-1.207 3.019-3.08 3.019h-1.29zm-12.126-2.316-2.503 8.415-2.4-8.414h-3.234l3.423 10.619h4.321l3.451-10.62zm20.829 10.62h2.996v-10.62h-3.002zm8.403-10.614-4.188 10.608h2.958l.665-1.878h4.958l.626 1.878h3.213l-4.221-10.614zm1.95 1.933 1.817 4.97h-3.69zM20.105 15.162v-2.548c.249-.017.498-.034.753-.04 6.974-.22 11.555 5.995 11.555 5.995s-4.941 6.863-10.242 6.863a6.3 6.3 0 0 1-2.061-.332v-7.728c2.714.327 3.263 1.529 4.897 4.249l3.634-3.063s-2.654-3.48-7.118-3.48c-.499 0-.964.04-1.418.084m0-8.426v3.811c.249-.022.498-.033.753-.044 9.7-.327 16.02 7.955 16.02 7.955s-7.262 8.83-14.824 8.83c-.692 0-1.34-.067-1.95-.172v2.354c.521.067 1.064.106 1.624.106 7.04 0 12.13-3.596 17.055-7.85.815.654 4.16 2.244 4.853 2.942-4.686 3.921-15.61 7.085-21.798 7.085-.598 0-1.169-.034-1.733-.09v3.313h26.75V6.736zm0 18.37v2.01c-6.51-1.163-8.315-7.927-8.315-7.927s3.124-3.462 8.315-4.022v2.205h-.012c-2.725-.327-4.852 2.216-4.852 2.216s1.19 4.282 4.864 5.517zm-11.567-6.21S12.4 13.2 20.1 12.613v-2.067c-8.53.687-15.92 7.91-15.92 7.91S8.362 30.555 20.1 31.663V29.47C11.492 28.385 8.54 18.895 8.54 18.895z"></path></g><defs><clipPath id="nvidia_svg__a"><path fill="#fff" fill-opacity="0.5" d="M.478.217h56.724v56.724H.478z"></path></clipPath></defs></svg></li>



<li><svg xmlns="http://www.w3.org/2000/svg" width="71" height="17" fill="none" role="img" aria-label="VIZIO"><g fill="#fff" fill-opacity="0.5" clip-path="url(#vizio_svg__a)"><path d="M57.39 16.152c-2.25 0-4.073-1.795-4.073-4.01V4.084c0-2.215 1.824-4.01 4.073-4.01h8.84c2.25 0 4.074 1.795 4.074 4.01v8.058c0 2.215-1.824 4.01-4.073 4.01zM55.416 4.085v8.058c.003 1.072.886 1.94 1.974 1.943h8.84c1.092-.002 1.972-.871 1.975-1.943V4.084c-.003-1.072-.886-1.94-1.974-1.944h-8.84c-1.092.003-1.972.87-1.975 1.944zm-8.168 12.068V0h2.102v16.153zm-27.254 0V0h2.1v16.153zM16.969 0h-2.1v5.285l-.01.095a5 5 0 0 1-.07.38c-.067.3-.198.668-.34.868l-.016.024-4.188 7.013-.011.023a1.2 1.2 0 0 1-.254.255c-.236.17-.653.396-1.496.422-.842-.026-1.262-.25-1.495-.421a1 1 0 0 1-.23-.224l-.024-.032-.01-.024L2.537 6.65l-.016-.023c-.141-.203-.272-.57-.34-.87a4 4 0 0 1-.069-.379q-.009-.059-.01-.094V0H0v5.375l.003.042c.002.034.03.355.13.798.105.434.266.985.619 1.524l4.11 6.892c.094.182.343.58.857.964.561.424 1.439.795 2.632.837q.066.004.13.003.067 0 .132-.003c1.193-.045 2.07-.413 2.632-.837.513-.385.762-.785.856-.964l4.11-6.892c.354-.54.515-1.09.619-1.524a6 6 0 0 0 .13-.798l.004-.042zm11.496 14.062c-.107-.927.543-2.2.543-2.2.206-.305.492-.62.97-.897l9.293-3.558.613-.234q.208-.1.4-.203c.87-.471 1.517-1.048 1.975-1.64a5.2 5.2 0 0 0 .821-1.546 4.8 4.8 0 0 0 .251-1.488q0-.078-.002-.132c0-.01 0-.026-.003-.034V.073H27.202v2.091H41.23v.042l.003.034v.053c0 .177-.035.74-.37 1.348-.296.53-.612 1.333-1.834 1.796l-.394.15-8.441 3.234-.613.235c-1.072.5-1.843 1.158-2.37 1.835a5.2 5.2 0 0 0-.829 1.553 4.8 4.8 0 0 0-.249 1.618c0 .01 0 .026.003.034v2.056h17.55v-2.09z"></path></g><defs><clipPath id="vizio_svg__a"><path fill="#fff" fill-opacity="0.5" d="M0 0h70.304v16.435H0z"></path></clipPath></defs></svg></li>




<li><svg xmlns="http://www.w3.org/2000/svg" width="47" height="47" fill="none" role="img" aria-label="PlayStation"><g fill="#fff" fill-opacity="0.5" clip-path="url(#playstation_svg__a)"><path d="M7.574 33.978c1.163.438 3.059.294 4.515-.294l3.785-1.312v3.934c-.294 0-.438.144-.727.144-3.784.582-7.718.294-11.65-.875-3.641-1.019-4.223-3.202-2.622-4.37 1.6-1.17 4.078-2.04 4.078-2.04l10.777-3.784v4.37l-7.72 2.767c-1.313.443-1.607 1.175-.438 1.463l.001-.002zm38.152-1.457c-.875 1.163-3.058 1.895-3.058 1.895l-16.456 5.972v-4.37l12.09-4.367c1.312-.438 1.6-1.163.438-1.601s-3.059-.294-4.516.294l-8.01 2.91v-4.515l.438-.145s2.333-.874 5.535-1.163c3.202-.294 7.28 0 10.482 1.313 3.496 1.015 3.934 2.616 3.058 3.779z"></path><path d="M27.813 25.096V14.027c0-1.312-.294-2.477-1.457-2.913-.875-.295-1.457.582-1.457 1.894v27.814l-7.574-2.333V5.29c3.064.582 7.723 1.895 10.344 2.77 6.41 2.183 8.593 4.953 8.593 11.069-.15 5.967-3.79 8.3-8.449 5.967"></path></g><defs><clipPath id="playstation_svg__a"><path fill="#fff" fill-opacity="0.5" d="M0 0h46.115v46.115H0z"></path></clipPath></defs></svg></li>




<li><svg xmlns="http://www.w3.org/2000/svg" width="71" height="31" role="img" aria-label="LG"><path fill="#FFF" fill-opacity="0.5" d="M15.58 31C6.97 31 0 24.068 0 15.503S6.97 0 15.58 0c8.597 0 15.58 6.938 15.58 15.503S24.177 31 15.58 31m-.627-22.3v13.6h4.343v-1.223h-3.091V8.699zm-4.348 3.71a1.863 1.863 0 0 0 1.866-1.858 1.866 1.866 0 0 0-3.732 0c0 1.023.833 1.858 1.866 1.858m5.599-9.88a17 17 0 0 0-.62-.018C8.393 2.512 2.54 8.34 2.54 15.497c0 3.475 1.356 6.733 3.813 9.183a13.03 13.03 0 0 0 9.231 3.795 13 13 0 0 0 9.212-3.795 12.87 12.87 0 0 0 3.826-9.183v-.578h-9.314v1.195h8.072c0 .026 0 .153-.006.186-.41 6.118-5.542 10.963-11.79 10.963a11.76 11.76 0 0 1-8.36-3.449 11.6 11.6 0 0 1-3.474-8.318c0-3.135 1.234-6.1 3.474-8.322a11.78 11.78 0 0 1 8.36-3.444c.136 0 .468 0 .62.008zm26.673 2.688h-4.681v20.18h14.38V21.47h-9.699zm19.997 12.49h3.813v3.528c-.703.267-2.074.532-3.378.532-4.214 0-5.618-2.13-5.618-6.458 0-4.127 1.337-6.56 5.55-6.56 2.342 0 3.68.732 4.782 2.13l2.91-2.664c-1.772-2.528-4.883-3.294-7.791-3.294-6.556 0-10 3.562-10 10.354 0 6.759 3.11 10.421 9.967 10.421 3.143 0 6.22-.799 7.891-1.964v-9.789h-8.126z"></path></svg></li></ul>
        
    </main>



    <br>

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
<br>
<br>
<div class="grid grid-cols-4 text-center place-items-center mb-10">
  <div>
    @php $mcuId = \App\Models\Category::where('slug','marvel-cinematic-universe')->value('id'); @endphp
    <h3 class="text-white font-semibold text-lg mb-4"><a href="#" class="hover:underline" data-category-id="{{ $mcuId }}">Marvel Cinematic Universe</a></h3>
  </div>
  <div>
    @php $disneyId = \App\Models\Category::whereIn('slug', ['disney-plus-originals','disney-plus'])->value('id'); @endphp
    <h3 class="text-white font-semibold text-lg mb-4"><a href="#" class="hover:underline" data-category-id="{{ $disneyId }}">Disney Plus</a></h3>
  </div>
  <div>
    @php $horrorId = \App\Models\Category::where('slug','horror')->value('id'); @endphp
    <h3 class="text-white font-semibold text-lg mb-4"><a href="#" class="hover:underline" data-category-id="{{ $horrorId }}">Horror</a></h3>
  </div>
  <div>
    @php $dcId = \App\Models\Category::where('slug','dc-comics')->value('id'); @endphp
    <h3 class="text-white font-semibold text-lg mb-4"><a href="#" class="hover:underline" data-category-id="{{ $dcId }}">DC</a></h3>
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
        d="M38.786 38.7887H32.5629V29.0431C32.5629 26.7192 32.5214 23.7276 29.3262 23.7276C26.0851 23.7276 25.5892 26.2596 25.5892 28.8739V38.7881H19.3661V18.7472H25.3402V21.486H25.4239C26.0217 20.4637 26.8857 19.6228 27.9237 19.0527C28.9617 18.4826 30.1349 18.2048 31.3183 18.2487C37.6256 18.2487 38.7886 22.3974 38.7886 27.7946L38.786 38.7887ZM12.3444 16.0077C11.6301 16.0078 10.9318 15.7962 10.3379 15.3995C9.74394 15.0027 9.28097 14.4388 9.00751 13.779C8.73406 13.1191 8.66242 12.393 8.80163 11.6925C8.94085 10.9919 9.28468 10.3484 9.78965 9.84324C10.2946 9.3381 10.938 8.99404 11.6385 8.85457C12.339 8.7151 13.0652 8.78648 13.7251 9.0597C14.385 9.33291 14.9491 9.79569 15.3461 10.3895C15.743 10.9833 15.9549 11.6815 15.955 12.3957C15.9551 12.87 15.8618 13.3396 15.6804 13.7778C15.499 14.216 15.233 14.6141 14.8978 14.9495C14.5625 15.2849 14.1644 15.551 13.7263 15.7326C13.2882 15.9141 12.8186 16.0076 12.3444 16.0077ZM15.4559 38.7887H9.22633V18.7472H15.4559V38.7887ZM41.8885 3.00601L6.09923 3.00019C5.28691 2.99103 4.50419 3.31055 3.92305 3.87819C3.34192 4.44583 3.00991 5.22097 3 6.03328V41.971C3.00957 42.7837 3.34136 43.5593 3.92248 44.1276C4.5036 44.6958 5.28651 45.0101 6.09923 45.0015H41.8885C42.7028 45.0117 43.4879 44.6983 44.0713 44.13C44.6547 43.5618 44.9887 42.7853 45 41.971V6.03069C44.9884 5.21677 44.6542 4.44075 44.0707 3.87312C43.4873 3.30549 42.7024 2.99268 41.8885 3.00341"
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

<!-- Copy sync command UI removed per request -->

<script>
  // Smooth-scroll header category links to their sections (offset for fixed header)
  (function(){
    function headerHeight(){
      var header = document.querySelector('header');
      return header ? header.getBoundingClientRect().height : 80;
    }

    document.addEventListener('click', function(e){
      var a = e.target.closest && e.target.closest('a[data-category-id]');
      if(!a) return;
      // prevent default navigation
      e.preventDefault();

      var id = a.getAttribute('data-category-id');
      if(!id) return;

      var target = document.getElementById('category-' + id);
      if(!target) return;

      var offset = headerHeight() + 12; // small margin below header
      var rect = target.getBoundingClientRect();
      var top = window.scrollY + rect.top - offset;

      window.scrollTo({ top: Math.max(0, Math.round(top)), behavior: 'smooth' });
    }, false);
  })();
</script>