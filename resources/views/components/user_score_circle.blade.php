@php
    // $value: numeric 0-10 (float) or null
    $size = $size ?? 56;
    $stroke = $stroke ?? 6;
    $showDecimal = $showDecimal ?? true; // show e.g. 8.2/10 next to circle
    $label = $label ?? null; // optional label string

    if (is_null($value)) {
        $percent = null;
    } else {
        // ensure float 0-10
        $v = (float) $value;
        $v = max(0, min(10, $v));
        $percent = (int) round($v * 10); // 0-100
        $decimal = round($v, 1);
    }

    $radius = ($size / 2) - $stroke;
    $circ = 2 * pi() * $radius;
    $dash = $percent !== null ? ($circ * ($percent / 100)) : 0;
    $offset = $circ - $dash;
    $textSize = max(10, floor($size / 3.5));
    $fontWeight = $fontWeight ?? 700;

    // unique id per instance (for targeting with JS) - use UUID instead of md5
    $uid = 'usc_' . \Illuminate\Support\Str::uuid()->toString();
@endphp

<div id="{{ $uid }}" class="user-score-circle inline-flex items-center gap-3" style="line-height:1" data-percent="{{ $percent ?? '' }}" data-decimal="{{ $decimal ?? '' }}">
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" aria-hidden="true">
        <defs>
            <linearGradient id="gs-{{ $uid }}" x1="0%" x2="100%">
                <stop offset="0%" stop-color="#1dd1a1" />
                <stop offset="100%" stop-color="#06a" />
            </linearGradient>
        </defs>
        <g transform="translate({{ $size/2 }}, {{ $size/2 }})">
            <circle r="{{ $radius }}" cx="0" cy="0" fill="none" stroke="#17202a" stroke-width="{{ $stroke }}" opacity="0.16"></circle>
            @if($percent !== null)
                {{-- foreground circle starts with full offset (hidden) and will be animated to $offset --}}
                <circle id="{{ $uid }}-fg" class="usc-circle" r="{{ $radius }}" cx="0" cy="0" fill="none" stroke="url(#gs-{{ $uid }})" stroke-width="{{ $stroke }}" stroke-linecap="round"
                    stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ }}" transform="rotate(-90)" />
            @else
                <circle r="{{ $radius }}" cx="0" cy="0" fill="none" stroke="#444" stroke-width="{{ $stroke }}" stroke-dasharray="{{ $circ }}" stroke-dashoffset="0" opacity="0.12"></circle>
            @endif
            <text id="{{ $uid }}-text" x="0" y="0" text-anchor="middle" dominant-baseline="central" fill="#fff" font-weight="{{ $fontWeight }}" font-size="{{ $textSize }}px">{{ $percent !== null ? '0%' : '—' }}</text>
        </g>
    </svg>

    @if($label || $showDecimal)
        <div class="user-score-meta text-left">
            @if($label)
                <div class="text-xs text-white/70 leading-tight">{{ $label }}</div>
            @endif
            @if($showDecimal)
                <div id="{{ $uid }}-decimal" class="text-sm font-semibold leading-tight">
                    @if(isset($decimal))
                        {{-- show placeholder; will be updated as animation runs --}}
                        <span class="usc-decimal">{{ number_format($decimal, 1) }}</span><span class="usc-decimal-suffix text-sm text-white/60">/10</span>
                    @else
                        —
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>

<script>
if (!window.__userScoreAnimLoaded) {
    window.__userScoreAnimLoaded = true;

    (function(){
        const ease = function(t){ return (--t)*t*t+1 }; // cubic ease-out

        function animateCircle(elem){
            const percent = parseInt(elem.getAttribute('data-percent'));
            if (isNaN(percent)) return;

            const fg = elem.querySelector('.usc-circle');
            const txt = elem.querySelector('text');
            const decEl = elem.querySelector('.usc-decimal');
            if (!fg || !txt) return;

            const dasharray = parseFloat(fg.getAttribute('stroke-dasharray')) || 0;
            const targetDash = dasharray * (percent/100);
            const start = performance.now();
            const duration = 1100; // ms

            function frame(now){
                const t = Math.min(1, (now - start)/duration);
                const eased = ease(t);
                const current = Math.max(0, dasharray - (targetDash * eased));
                fg.setAttribute('stroke-dashoffset', current);

                // number animation (0 -> percent)
                const displayPct = Math.round(percent * eased);
                txt.textContent = displayPct + '%';

                // decimal update if present
                if (decEl){
                    const decimalTarget = (percent/10);
                    const decValue = (decimalTarget * eased).toFixed(1);
                    // keep the '/10' suffix outside the span if present
                    decEl.textContent = parseFloat(decValue).toFixed(1);
                    const suffix = elem.querySelector('.usc-decimal-suffix');
                    if (suffix) suffix.textContent = '/10';
                }

                if (t < 1) requestAnimationFrame(frame);
                else {
                    // ensure final state
                    fg.setAttribute('stroke-dashoffset', Math.max(0, dasharray - targetDash));
                    txt.textContent = percent + '%';
                    if (decEl) decEl.textContent = (percent/10).toFixed(1);
                    const suffix = elem.querySelector('.usc-decimal-suffix');
                    if (suffix) suffix.textContent = '/10';
                }
            }

            requestAnimationFrame(frame);
        }

        // Use IntersectionObserver to animate when element becomes visible
        const observer = ('IntersectionObserver' in window) ? new IntersectionObserver((entries, obs)=>{
            entries.forEach(en=>{
                if (en.isIntersecting) {
                    animateCircle(en.target);
                    obs.unobserve(en.target);
                }
            });
        }, { threshold: 0.2 }) : null;

        // init: find all elements and setup
        document.addEventListener('DOMContentLoaded', function(){
            const elems = document.querySelectorAll('.user-score-circle');
            elems.forEach(el=>{
                // if percent missing, do nothing
                if (!el.getAttribute('data-percent')) return;
                if (observer) observer.observe(el);
                else animateCircle(el);
            });
        });

        // Expose helpers so other scripts can trigger animation on demand.
        // Do not overwrite existing globals if present.
        if (!window.__observeUserScoreElement) {
            window.__observeUserScoreElement = function(el){
                try {
                    if (!el) return;
                    if (!el.getAttribute || !el.getAttribute('data-percent')) return;
                    if (observer) observer.observe(el);
                    else animateCircle(el);
                } catch(e) { try { animateCircle(el); } catch(_){} }
            };
        }

        if (!window.__animateUserScoreElement) {
            window.__animateUserScoreElement = function(el){
                try {
                    if (!el) return;
                    if (!el.getAttribute || !el.getAttribute('data-percent')) return;
                    animateCircle(el);
                } catch(e) { /* ignore */ }
            };
        }
    })();
}
</script>
