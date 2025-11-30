@extends('layouts.app')

@section('title', $movie->title . ' — RATE')

@section('content')
    <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-12">
        <section class="grid grid-cols-1 md:grid-cols-3 gap-10 items-start mt-8">
        <div class="md:col-span-1">
            <div class="group overflow-hidden rounded-xl shadow-2xl bg-gradient-to-br from-black/40 to-white/2">
                <img src="{{ $movie->image_url }}" alt="{{ $movie->title }} poster" class="w-full h-auto rounded-lg transform group-hover:scale-105 transition duration-300 object-cover">
            </div>
        </div>

        <div class="md:col-span-2">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-3 leading-tight tracking-tight">{{ $movie->title }}</h1>
            
            @php
                // For professional output: do not render debug text visibly on the page.
                // When in local/debug, log the values to Laravel log and emit an HTML comment
                // so developers can inspect the page source if needed.
                if (app()->environment('local') || config('app.debug')) {
                    \Illuminate\Support\Facades\Log::debug('Movie debug', [
                        'movie_id' => $movie->id ?? null,
                        'tmdb_id' => $movie->tmdb_id ?? null,
                        'tmdbTrailer' => $tmdbTrailer ?? null,
                    ]);

                    // HTML comment (not visible in page) with debug info for quick inspection in source
                    echo '<!-- DEBUG: TMDB ID=' . ($movie->tmdb_id ?? 'null') . ' | TMDB Trailer=' . (!empty($tmdbTrailer) ? $tmdbTrailer : 'null') . ' -->';
                }
            @endphp
            <div class="flex items-center gap-4 text-sm text-white/70 mb-4">
                <span>{{ $movie->year ?? '2019' }}</span>
                @if($movie->duration)<span>&middot; {{ $movie->duration }} min</span>@endif
                <span>&middot; PG-13</span>
                @if($movie->genres && is_array($movie->genres))
                    <span>&middot; {{ implode(', ', $movie->genres) }}</span>
                @endif
            </div>

            <div class="flex items-center gap-4 mb-6">
                @php
                    $displayValue = $movie->rating_decimal ?? (isset($movie->user_score) ? $movie->user_score/10 : null);
                @endphp
                @include('components.user_score_circle', ['value' => $displayValue, 'size' => 72, 'stroke' => 8, 'label' => 'User Score', 'showDecimal' => false])

                @if($movie->year || $movie->duration)
                    <div class="text-sm text-white/70"> 
                        @if($movie->year) <span>{{ $movie->year }}</span> @endif
                        @if($movie->duration) <span>&middot; {{ $movie->duration }} min</span> @endif
                        @if($movie->genres && is_array($movie->genres)) <span>&middot; {{ implode(', ', $movie->genres) }}</span> @endif
                    </div>
                @endif
            </div>

            <p class="text-white/90 leading-relaxed mb-6">{{ $movie->description }}</p>

            

            <div class="mt-4">
                @php
                    // Determine best embed URL (prefer movie.video_url then tmdbTrailer)
                    $source = $movie->video_url ?? $tmdbTrailer ?? null;
                    $embed = null;

                    if ($source) {
                        // If it's a youtube watch URL or youtu.be, extract the key
                        if (preg_match('/(?:v=|\/)([A-Za-z0-9_-]{11})/', $source, $m)) {
                            $key = $m[1];
                            $embed = 'https://www.youtube.com/embed/' . $key . '?rel=0';
                        } elseif (strpos($source, '/embed/') !== false) {
                            // already embed-style, ensure rel=0 is present
                            $embed = preg_replace('/\?.*/', '', $source) . '?rel=0';
                        } else {
                            // fallback: use as-is (may be an external embed URL)
                            $embed = $source;
                        }
                    }

                    // If no embed was discoverable from TMDB or the movie record,
                    // provide a safe, official embeddable trailer as a last-resort
                    // fallback for known titles. This ensures the "Watch trailer"
                    // button will work even when TMDB returns no trailer.
                    //
                    // NOTE: we keep this list intentionally small to avoid
                    // guessing trailers for every movie. Add entries here as
                    // needed. IDs used are YouTube video IDs (embed-friendly).
                    if (!$embed) {
                        $titleKey = strtolower(trim($movie->title ?? ''));
                        $officialFallbacks = [
                            // Avengers: Endgame (official theatrical trailer) - use embed URL so iframe API works
                            'avengers endgame' => 'https://www.youtube.com/embed/TcMBFSGVi1c',
                        ];

                        if (isset($officialFallbacks[$titleKey])) {
                            $embed = $officialFallbacks[$titleKey];
                        }
                    }
                @endphp

                    @if($embed)
                        {{-- Cinematic modal trigger (opens an inline modal with an iframe) --}}
                        <button id="openTrailerBtn" type="button" data-src="{{ $embed }}" class="inline-flex items-center gap-3 px-4 md:px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 rounded-full text-white font-semibold shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M4 2v20l18-10L4 2z" />
                            </svg>
                            <span>Watch Trailer</span>
                        </button>
                    @else
                        <button type="button" disabled class="inline-flex items-center gap-3 px-4 py-3 bg-gray-800 text-gray-400 rounded-full font-semibold cursor-not-allowed border border-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M4 2v20l18-10L4 2z" />
                            </svg>
                            <span>Trailer Unavailable</span>
                        </button>
                    @endif

                    

                {{-- Modal markup (initially hidden) with cinematic dark styling (letterbox bars) --}}
                <div id="trailerModal" aria-hidden="true" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
                    {{-- Backdrop --}}
                    <div id="trailerBackdrop" class="absolute inset-0 bg-black bg-opacity-85"></div>

                    {{-- Exit overlay (appears when user moves pointer/touch away from video) --}}
                    <div id="trailerExitOverlay" class="hidden absolute inset-0 z-60 items-center justify-center pointer-events-none">
                        <div class="bg-black bg-opacity-60 rounded-lg p-4 flex items-center gap-4 pointer-events-auto">
                                <button id="exitTrailerBtn" class="px-4 py-2 bg-white text-black rounded-md font-semibold shadow">Close Trailer</button>
                                <button id="exitTrailerCloseSmall" class="px-3 py-2 bg-transparent text-white rounded-md border border-white/20">Continue watching</button>
                            </div>
                    </div>

                    {{-- Top and bottom letterbox bars to create cinematic look --}}
                    <div class="absolute left-0 right-0 top-0 h-[12vh] bg-gradient-to-b from-black via-black/90 to-transparent pointer-events-none" style="z-index:51;"></div>
                    <div class="absolute left-0 right-0 bottom-0 h-[12vh] bg-gradient-to-t from-black via-black/90 to-transparent pointer-events-none" style="z-index:51;"></div>

                    <div class="relative z-60 w-full max-w-screen-2xl mx-auto">
                        <div class="flex justify-end p-4">
                            <!-- Make the close button fixed, highly visible, and larger so it remains above the video/modal -->
                            <button id="closeTrailerBtn" class="text-white bg-black bg-opacity-60 hover:bg-opacity-70 rounded-full p-3 md:p-4 focus:outline-none shadow-lg" aria-label="Close trailer" style="position:fixed;top:18px;right:18px;z-index:100000;backdrop-filter:blur(6px);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M18 6L6 18"></path>
                                    <path d="M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        {{-- Cinematic container: big, centered, 16:9 with rounded corners and subtle shadow --}}
                        <div class="w-full flex justify-center px-6 pb-8">
                            <div class="w-full" style="max-width:1200px;">
                                <div id="trailerFrameWrap" class="bg-black rounded-xl overflow-hidden shadow-2xl" style="position:relative;padding-top:56.25%;">
                                    <!-- Custom player shell: we'll insert either a YouTube iframe (with JS API) or an HTML5 video element here -->
                                    <div id="playerShell" style="position:absolute;top:0;left:0;width:100%;height:100%;">
                                        <div id="playerContainer" style="position:relative;width:100%;height:100%;background:#000;"></div>

                                        <!-- Center spinner -->
                                        <div id="playerSpinner" class="flex items-center justify-center" style="position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none;">
                                            <svg id="spinnerIcon" width="60" height="60" viewBox="0 0 50 50" style="display:block;">
                                                <circle cx="25" cy="25" r="20" stroke="#ddd" stroke-width="4" fill="none" stroke-linecap="round" stroke-dasharray="31.4 31.4" transform="rotate(-90 25 25)"></circle>
                                            </svg>
                                        </div>

                                        <!-- Custom controls overlay -->
                                        <div id="playerControls" style="position:absolute;left:0;right:0;bottom:0;padding:12px;background:linear-gradient(180deg,transparent,rgba(0,0,0,0.6));display:flex;align-items:center;gap:12px;">
                                            <button id="playPauseBtn" class="text-white bg-transparent p-2 rounded-full focus:outline-none" aria-label="Play/Pause">
                                                <svg id="playIcon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>
                                                <svg id="pauseIcon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="display:none;"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"></path></svg>
                                            </button>

                                            <div style="flex:1;display:flex;align-items:center;gap:8px;">
                                                <div id="progressWrap" style="flex:1;height:8px;background:rgba(255,255,255,0.08);border-radius:4px;position:relative;cursor:pointer;">
                                                    <div id="progressBar" style="position:absolute;left:0;top:0;height:100%;width:0%;background:linear-gradient(90deg,#ff1f5c,#ff5c7a);border-radius:4px;"></div>
                                                </div>
                                                <div id="timeDisplay" style="min-width:70px;color:rgba(255,255,255,0.85);font-size:13px;text-align:right;">0:00 / 0:00</div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Advertisement / fallback overlay matching the provided screenshot --}}
                <div id="adModal" aria-hidden="true" class="fixed inset-0 z-70 hidden items-center justify-center px-4">
                    <div id="adBackdrop" class="absolute inset-0 bg-black"></div>

                    <div class="relative z-80 w-full max-w-screen-2xl mx-auto">
                        <div class="flex justify-end p-4">
                            <!-- Keep ad close button also fixed so it remains visible when the ad modal opens -->
                            <button id="closeAdBtn" class="text-white bg-black bg-opacity-40 hover:bg-opacity-60 rounded-full p-3 md:p-4 focus:outline-none shadow-lg" aria-label="Close ad" style="position:fixed;top:18px;right:18px;z-index:100000;backdrop-filter:blur(6px);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M18 6L6 18"></path>
                                    <path d="M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="w-full mx-auto px-6 pb-8">
                            <div class="bg-black rounded-xl overflow-hidden shadow-2xl" style="min-height:60vh;">
                                {{-- Top area with Plex-like logo on the left (recreated) --}}
                                <div class="flex items-start justify-between px-6 pt-6">
                                    <div class="flex items-center gap-3">
                                        <div style="font-weight:800;font-size:28px;letter-spacing:0.5px;color:#ffffff;">plex<span style="color:#f2c200;margin-left:2px;"></span></div>
                                    </div>
                                    <div></div>
                                </div>

                                {{-- Centered error text --}}
                                <div class="flex items-center justify-center" style="height:calc(60vh - 72px);">
                                    <div class="text-center px-6">
                                        <h2 class="text-white font-semibold" style="font-size:20px;margin-bottom:12px;">There was an error loading the video</h2>
                                        <p class="text-white/70">Please try again later</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Small inline CSS for cinematic transitions (keeps Tailwind usage but adds a couple utilities) --}}
                <style>
                    /* Smooth fade for the modal container */
                    /* Smooth fade for the modal container */
                    #trailerModal { transition: opacity .22s ease, transform .22s ease; }
                    #trailerModal.hidden { opacity: 0; pointer-events: none; }
                    #trailerModal.flex { opacity: 1; pointer-events: auto; }

                    /* Larger close hit area on mobile */
                    #closeTrailerBtn { backdrop-filter: blur(4px); }

                    /* Ad modal styling (full-screen black overlay like the screenshot) */
                    #adModal { transition: opacity .22s ease, transform .22s ease; }
                    #adModal.hidden { opacity: 0; pointer-events: none; }
                    #adModal.flex { opacity: 1; pointer-events: auto; }

                    /* Backdrop should be fully black to match the reference image */
                    #adModal #adBackdrop { background: #000000; opacity: 1; }

                    /* Make the centered ad container match the look: large black area, logo top-left, close top-right */
                    #adModal .bg-black { background: #000000; }
                    #adModal h2 { font-size: 20px; font-weight: 600; }
                    #adModal p { color: rgba(255,255,255,0.75); }

                    /* Slightly increase the close button hit area for parity with screenshot */
                    #closeAdBtn { backdrop-filter: blur(4px); }
                </style>

                {{-- Inline JS to control modal and iframe autoplay/stop (keeps previous behavior) --}}
                @php $autoShow = request()->query('show_ad') || session('show_ad') ? true : false; @endphp
                @push('scripts')
                <script>
                    // Expose server-driven auto-show flag to the player script
                    window.__AUTO_SHOW_AD = {{ $autoShow ? 'true' : 'false' }};

                </script>
                <script>
                    (function(){
                        const openBtn = document.getElementById('openTrailerBtn');
                        const modal = document.getElementById('trailerModal');
                        const backdrop = document.getElementById('trailerBackdrop');
                        const closeBtn = document.getElementById('closeTrailerBtn');
                        const iframe = document.getElementById('trailerFrame');
                        const frameWrap = document.getElementById('trailerFrameWrap');
                        const exitOverlay = document.getElementById('trailerExitOverlay');
                        const exitBtn = document.getElementById('exitTrailerBtn');
                        const keepWatchingBtn = document.getElementById('exitTrailerCloseSmall');

                        let hideOverlayTimer = null;

                        // Helpers to lock body scrolling (supports older browsers)
                        function lockScroll(){
                            document.documentElement.style.overflow = 'hidden';
                            document.body.style.overflow = 'hidden';
                        }
                        function unlockScroll(){
                            document.documentElement.style.overflow = '';
                            document.body.style.overflow = '';
                        }

                        function showExitOverlay(){
                            if(!exitOverlay) return;
                            // make overlay interactive
                            exitOverlay.classList.remove('hidden');
                            exitOverlay.classList.add('flex');
                            clearTimeout(hideOverlayTimer);
                            hideOverlayTimer = setTimeout(hideExitOverlay, 4000);
                        }

                        function hideExitOverlay(){
                            if(!exitOverlay) return;
                            exitOverlay.classList.remove('flex');
                            exitOverlay.classList.add('hidden');
                            clearTimeout(hideOverlayTimer);
                        }

                        function openModal(src){
                            if(!src) return;
                            // ensure autoplay param
                            const sep = src.includes('?') ? '&' : '?';
                            // add modest branding/controls params to improve cinematic feel
                            let final = src + sep + 'autoplay=1&rel=0&controls=1&modestbranding=1';
                            iframe.src = final;
                            // show modal
                            modal.classList.remove('hidden');
                            modal.classList.add('flex');
                            modal.setAttribute('aria-hidden', 'false');
                            lockScroll();
                            hideExitOverlay();
                        }

                        // Graceful close: stop/destroy any active player, clear timers and remove DOM nodes
                        function closeModal(){
                            modal.classList.remove('flex');
                            modal.classList.add('hidden');
                            modal.setAttribute('aria-hidden', 'true');

                            // Stop progress updater
                            stopProgressTimer();

                            // Destroy YouTube player if present
                            try{
                                if(ytPlayer && typeof ytPlayer.destroy === 'function'){
                                    ytPlayer.destroy();
                                } else if(ytPlayer && typeof ytPlayer.stopVideo === 'function'){
                                    ytPlayer.stopVideo();
                                }
                            }catch(e){ /* ignore */ }
                            ytPlayer = null;

                            // Stop and unload native HTML5 video if present
                            try{
                                if(html5Video){
                                    html5Video.pause();
                                    // Remove source to stop network activity
                                    try{ html5Video.removeAttribute('src'); }catch(e){}
                                    try{ html5Video.load(); }catch(e){}
                                }
                            }catch(e){ /* ignore */ }
                            html5Video = null;

                            // Remove iframe node if exists
                            const currentFrame = document.getElementById('trailerFrame');
                            if(currentFrame){
                                try{ currentFrame.src = ''; currentFrame.remove(); }catch(e){}
                            }

                            // Clear container
                            try{ if(playerContainer) playerContainer.innerHTML = ''; }catch(e){}

                            // Clear any mute-check interval
                            try{ if(window.__YT_MUTE_CHECK_INTERVAL){ clearInterval(window.__YT_MUTE_CHECK_INTERVAL); window.__YT_MUTE_CHECK_INTERVAL = null; } }catch(e){}

                            unlockScroll();
                            hideExitOverlay();
                        }

                        // Close modal with a small UX cue if reason given (e.g. muted)
                        function closeModalWithReason(reason){
                            // Optional: could show a tiny toast or animation here. For now, perform a gentle fade and then close.
                            if(reason === 'muted'){
                                // Fade the modal slightly to signal we're closing due to audio off
                                try{ modal.style.transition = 'opacity .25s ease'; modal.style.opacity = '0.85'; }catch(e){}
                                setTimeout(function(){ closeModal(); try{ modal.style.opacity = ''; modal.style.transition = ''; }catch(e){} }, 220);
                            } else {
                                closeModal();
                            }
                        }

                        if(openBtn){
                            openBtn.addEventListener('click', function(e){
                                const src = this.getAttribute('data-src');
                                openModal(src);
                            });
                        }

                        if(closeBtn){
                            closeBtn.addEventListener('click', closeModal);
                        }

                        if(backdrop){
                            backdrop.addEventListener('click', closeModal);
                        }

                        // Exit overlay buttons
                        if(exitBtn){
                            exitBtn.addEventListener('click', function(){
                                closeModal();
                            });
                        }

                        if(keepWatchingBtn){
                            keepWatchingBtn.addEventListener('click', function(){
                                hideExitOverlay();
                            });
                        }

                        // Show overlay when the pointer leaves the video area (desktop)
                        if(frameWrap){
                            frameWrap.addEventListener('mouseleave', function(){
                                // only show when modal is visible
                                if(modal && !modal.classList.contains('hidden')) showExitOverlay();
                            });

                            frameWrap.addEventListener('mouseenter', function(){
                                hideExitOverlay();
                            });
                        }

                        // Touch: show overlay when user taps outside the modal content
                        document.addEventListener('touchstart', function(e){
                            if(modal && !modal.classList.contains('hidden')){
                                if(!modal.contains(e.target)){
                                    showExitOverlay();
                                }
                            }
                        }, {passive:true});

                        // Esc key closes modal
                        document.addEventListener('keydown', function(e){
                            if(e.key === 'Escape'){
                                if(modal && !modal.classList.contains('hidden')) closeModal();
                            }
                        });

                        // --- Ad modal (fallback overlay) controls ---
                        const adModal = document.getElementById('adModal');
                        const adBackdropEl = document.getElementById('adBackdrop');
                        const closeAd = document.getElementById('closeAdBtn');
                        const showAdTestBtn = document.getElementById('showAdTest');

                        function showAdModal(){
                            if(!adModal) return;
                            adModal.classList.remove('hidden');
                            adModal.classList.add('flex');
                            adModal.setAttribute('aria-hidden','false');
                            lockScroll();
                        }

                        function closeAdModal(){
                            if(!adModal) return;
                            adModal.classList.remove('flex');
                            adModal.classList.add('hidden');
                            adModal.setAttribute('aria-hidden','true');
                            unlockScroll();
                        }

                        if(closeAd){
                            closeAd.addEventListener('click', closeAdModal);
                        }

                        if(adBackdropEl){
                            adBackdropEl.addEventListener('click', closeAdModal);
                        }

                        if(showAdTestBtn){
                            showAdTestBtn.addEventListener('click', function(){
                                showAdModal();
                            });
                        }

                        // Close ad using Escape as well
                        document.addEventListener('keydown', function(e){
                            if(e.key === 'Escape'){
                                if(adModal && !adModal.classList.contains('hidden')) closeAdModal();
                            }
                        });

                        // --- Custom player controller (wrapper around iframe or html5 video) ---
                        let ytPlayer = null;
                        let isYouTube = false;
                        let html5Video = null;
                        const playerContainer = document.getElementById('playerContainer');
                        const spinner = document.getElementById('playerSpinner');
                        const playPauseBtn = document.getElementById('playPauseBtn');
                        const playIcon = document.getElementById('playIcon');
                        const pauseIcon = document.getElementById('pauseIcon');
                        const progressBar = document.getElementById('progressBar');
                        const progressWrap = document.getElementById('progressWrap');
                        const timeDisplay = document.getElementById('timeDisplay');

                        // Helper to format seconds to M:SS
                        function formatTime(secs){
                            if(!isFinite(secs) || secs < 0) return '0:00';
                            const s = Math.floor(secs % 60).toString().padStart(2,'0');
                            const m = Math.floor(secs/60);
                            return m + ':' + s;
                        }

                        function showSpinner(){ if(spinner) spinner.style.pointerEvents = 'none', spinner.style.opacity = 1, spinner.style.display = 'flex'; }
                        function hideSpinner(){ if(spinner) spinner.style.opacity = 0, spinner.style.display = 'none'; }

                        // Periodic progress updater
                        let progressTimer = null;
                        function startProgressTimer(){
                            stopProgressTimer();
                            progressTimer = setInterval(updateProgress, 250);
                        }
                        function stopProgressTimer(){ if(progressTimer){ clearInterval(progressTimer); progressTimer = null; } }

                        function updateProgress(){
                            try{
                                let current = 0, duration = 0;
                                if(isYouTube && ytPlayer && typeof ytPlayer.getCurrentTime === 'function'){
                                    current = ytPlayer.getCurrentTime();
                                    duration = ytPlayer.getDuration() || 0;
                                } else if(html5Video){
                                    current = html5Video.currentTime || 0;
                                    duration = html5Video.duration || 0;
                                }
                                const pct = duration > 0 ? Math.min(100, (current/duration*100)) : 0;
                                if(progressBar) progressBar.style.width = pct + '%';
                                if(timeDisplay) timeDisplay.textContent = formatTime(current) + ' / ' + formatTime(duration);
                            }catch(e){ /* ignore */ }
                        }

                        // Build player for the given source (embed URL). If it's YouTube, we'll use the IFrame API.
                        function buildPlayerFor(src){
                            // Clear container
                            playerContainer.innerHTML = '';
                            isYouTube = false;
                            ytPlayer = null;
                            html5Video = null;

                            if(!src) return;

                            // Simple detection for YouTube embed URLs
                            if(src.includes('youtube.com') || src.includes('youtu.be')){
                                isYouTube = true;
                                // Ensure API and minimal params: enablejsapi=1, controls=0 (we provide controls)
                                const sep = src.includes('?') ? '&' : '?';
                                const finalSrc = src + sep + 'enablejsapi=1&controls=0&rel=0&modestbranding=1';

                                const iframe = document.createElement('iframe');
                                iframe.id = 'trailerFrame';
                                iframe.src = finalSrc;
                                iframe.frameBorder = '0';
                                iframe.allow = 'autoplay; encrypted-media; fullscreen';
                                iframe.allowFullscreen = true;
                                iframe.style.position = 'absolute';
                                iframe.style.top = 0;
                                iframe.style.left = 0;
                                iframe.style.width = '100%';
                                iframe.style.height = '100%';
                                playerContainer.appendChild(iframe);

                                // Load YouTube API if not present
                                if(typeof YT === 'undefined' || typeof YT.Player === 'undefined'){
                                    const tag = document.createElement('script');
                                    tag.src = 'https://www.youtube.com/iframe_api';
                                    document.head.appendChild(tag);
                                }

                                // Create player when API ready
                                const tryCreate = function(){
                                    if(typeof YT !== 'undefined' && typeof YT.Player !== 'undefined'){
                                        // clear any existing YT mute check interval to avoid duplicates
                                        try{ if(window.__YT_MUTE_CHECK_INTERVAL){ clearInterval(window.__YT_MUTE_CHECK_INTERVAL); window.__YT_MUTE_CHECK_INTERVAL = null; } }catch(e){}

                                        // Initialize YouTube player and request autoplay when ready.
                                        // Use playerVars to ask the player to autoplay; additionally call playVideo() in onReady
                                        // to ensure playback starts even if the API was just loaded.
                                        ytPlayer = new YT.Player('trailerFrame', {
                                            playerVars: {
                                                autoplay: 1,
                                                rel: 0,
                                                modestbranding: 1,
                                                controls: 0
                                            },
                                            events: {
                                                onReady: function(){
                                                    hideSpinner();
                                                    updateProgress();
                                                    // Attempt to start playback once ready
                                                    try{ if(typeof ytPlayer.playVideo === 'function') ytPlayer.playVideo(); }catch(e){}

                                                    // Poll YouTube player for mute/volume changes (IFrame API doesn't provide a volumechange event)
                                                    try{
                                                        window.__YT_MUTE_CHECK_INTERVAL = setInterval(function(){
                                                            try{
                                                                if(!ytPlayer) return;
                                                                var muted = (typeof ytPlayer.isMuted === 'function' && ytPlayer.isMuted());
                                                                var vol = (typeof ytPlayer.getVolume === 'function' ? ytPlayer.getVolume() : null);
                                                                if(muted || vol === 0){
                                                                    clearInterval(window.__YT_MUTE_CHECK_INTERVAL); window.__YT_MUTE_CHECK_INTERVAL = null;
                                                                    closeModalWithReason('muted');
                                                                }
                                                            }catch(e){}
                                                        }, 300);
                                                    }catch(e){}
                                                },
                                                onStateChange: function(e){
                                                    // 1=playing, 2=paused, 3=buffering, 0=ended
                                                    if(e.data === YT.PlayerState.BUFFERING){ showSpinner(); }
                                                    else if(e.data === YT.PlayerState.PLAYING){ hideSpinner(); playIcon.style.display='none'; pauseIcon.style.display='block'; startProgressTimer(); }
                                                    else if(e.data === YT.PlayerState.PAUSED){ hideSpinner(); playIcon.style.display='block'; pauseIcon.style.display='none'; stopProgressTimer(); }
                                                    else if(e.data === YT.PlayerState.ENDED){ hideSpinner(); playIcon.style.display='block'; pauseIcon.style.display='none'; stopProgressTimer(); }
                                                }
                                            }
                                        });
                                    } else {
                                        // try again shortly
                                        setTimeout(tryCreate, 250);
                                    }
                                };
                                tryCreate();
                            } else {
                                // Fallback: use native HTML5 video tag
                                html5Video = document.createElement('video');
                                html5Video.src = src;
                                html5Video.playsInline = true;
                                html5Video.preload = 'metadata';
                                html5Video.style.width = '100%';
                                html5Video.style.height = '100%';
                                html5Video.style.objectFit = 'cover';
                                html5Video.setAttribute('webkit-playsinline','');
                                playerContainer.appendChild(html5Video);

                                html5Video.addEventListener('waiting', showSpinner);
                                html5Video.addEventListener('playing', function(){ hideSpinner(); playIcon.style.display='none'; pauseIcon.style.display='block'; startProgressTimer(); });
                                html5Video.addEventListener('pause', function(){ hideSpinner(); playIcon.style.display='block'; pauseIcon.style.display='none'; stopProgressTimer(); });
                                html5Video.addEventListener('loadedmetadata', updateProgress);

                                // Close modal gracefully when audio is turned off (muted or volume 0)
                                try{
                                    html5Video.addEventListener('volumechange', function(){
                                        try{
                                            if(html5Video && (html5Video.muted || Number(html5Video.volume) === 0)){
                                                closeModalWithReason('muted');
                                            }
                                        }catch(e){}
                                    });
                                }catch(e){}
                            }
                        }

                        // Play/pause toggle wired to whichever player is active
                        if(playPauseBtn){
                            playPauseBtn.addEventListener('click', function(){
                                try{
                                    if(isYouTube && ytPlayer){
                                        const state = ytPlayer.getPlayerState ? ytPlayer.getPlayerState() : -1;
                                        if(state === YT.PlayerState.PLAYING){ ytPlayer.pauseVideo(); }
                                        else { ytPlayer.playVideo(); }
                                    } else if(html5Video){
                                        if(html5Video.paused){ html5Video.play().catch(()=>{}); }
                                        else { html5Video.pause(); }
                                    }
                                }catch(e){ /* ignore */ }
                            });
                        }

                        // Seek by clicking progress bar
                        if(progressWrap){
                            progressWrap.addEventListener('click', function(e){
                                const rect = progressWrap.getBoundingClientRect();
                                const x = (e.clientX - rect.left) / rect.width;
                                try{
                                    if(isYouTube && ytPlayer && typeof ytPlayer.getDuration === 'function'){
                                        const d = ytPlayer.getDuration() || 0;
                                        ytPlayer.seekTo(d * Math.max(0,Math.min(1,x)), true);
                                    } else if(html5Video && html5Video.duration){
                                        html5Video.currentTime = html5Video.duration * Math.max(0,Math.min(1,x));
                                    }
                                    updateProgress();
                                }catch(e){}
                            });
                        }

                        // When opening the trailer modal, we call buildPlayerFor with the chosen src
                        const originalOpenModal = openModal;
                        openModal = function(src){
                            if(!src) return;
                            // decide build but still allow autoplay param for youtube via iframe src
                            buildPlayerFor(src);
                            // keep previous behavior of setting iframe src for non-YouTube (handled inside buildPlayerFor)
                            // show modal
                            modal.classList.remove('hidden');
                            modal.classList.add('flex');
                            modal.setAttribute('aria-hidden', 'false');
                            lockScroll();
                            hideExitOverlay();
                            showSpinner();
                            // attempt to play once player ready
                            setTimeout(function(){
                                try{
                                    if(isYouTube && ytPlayer && typeof ytPlayer.playVideo === 'function') ytPlayer.playVideo();
                                    if(html5Video) html5Video.play().catch(()=>{});
                                }catch(e){}
                            }, 600);
                        };

                        // Auto-open advertisement modal if server/request requested it
                        try{
                            if (window.__AUTO_SHOW_AD && typeof showAdModal === 'function'){
                                // small delay so layout paints and lockScroll can run cleanly
                                setTimeout(function(){ try{ showAdModal(); }catch(e){} }, 300);
                            }
                        }catch(e){}
                    })();
                </script>
                @endpush

            </div>
        </div>
    </section>

    </div>
@endsection