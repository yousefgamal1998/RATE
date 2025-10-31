// RATE - Index Page JavaScript (migrated from public/js/index.js)
// JavaScript functionality for the main landing page

// Loading Animation
window.addEventListener("load", function () {
    const loadingScreen = document.getElementById("loadingScreen");
    const pageContent = document.getElementById("pageContent");

    if (loadingScreen && pageContent) {
        // Hide loading screen and show page content
        setTimeout(() => {
            loadingScreen.classList.add("fade-out");
            pageContent.classList.add("animate-in");

            // Remove loading screen from DOM after animation
            setTimeout(() => {
                loadingScreen.style.display = "none";
            }, 500);
        }, 3000);
    }
});

// Enhanced JavaScript for Plex-style interactions
document.addEventListener("DOMContentLoaded", function () {
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        });
    });

    // Search functionality
    const searchBox = document.querySelector('input[type="text"]');
    if (searchBox) {
        searchBox.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                const searchTerm = this.value;
                if (searchTerm.trim()) {
                    alert("Searching for: " + searchTerm);
                }
            }
        });
    }

    // Header scroll effect
    window.addEventListener("scroll", function () {
        const header = document.querySelector("header");
        if (window.scrollY > 100) {
            header.classList.add("bg-black/98");
            header.classList.remove("bg-black/95");
        } else {
            header.classList.add("bg-black/95");
            header.classList.remove("bg-black/98");
        }
    });

    // Animate stats on scroll
    const observerOptions = {
        threshold: 0.5,
        rootMargin: "0px 0px -100px 0px",
    };

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const statNumbers = entry.target.querySelectorAll(".text-5xl");
                statNumbers.forEach((stat) => {
                    const finalNumber = stat.textContent;
                    const isPercentage = finalNumber.includes("%");
                    const isPlus = finalNumber.includes("+");
                    const isSlash = finalNumber.includes("/");

                    let targetNumber = parseInt(
                        finalNumber.replace(/[^\d]/g, "")
                    );

                    if (isSlash) {
                        stat.textContent = "24/7";
                        return;
                    }

                    let currentNumber = 0;
                    const increment = targetNumber / 50;

                    const timer = setInterval(() => {
                        currentNumber += increment;
                        if (currentNumber >= targetNumber) {
                            currentNumber = targetNumber;
                            clearInterval(timer);
                        }

                        let displayNumber = Math.floor(currentNumber);
                        if (isPercentage) displayNumber += "%";
                        if (isPlus) displayNumber += "+";

                        stat.textContent = displayNumber;
                    }, 30);
                });
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const statsSection = document.querySelector("section.bg-gradient-to-br");
    if (statsSection) {
        observer.observe(statsSection);
    }
});

// ✅ جلب بيانات الأفلام من Laravel API (مسار نسبي لا يعتمد على host ثابت)
// كانت: fetch("/api/movies")
fetch("/api/movies?visibility=homepage")
    .then((response) => response.json())
    .then((data) => {
        // Populate movies only if container exists
        const moviesContainer = document.getElementById("moviesContainer");
        if (!moviesContainer) return;
        moviesContainer.innerHTML = "";

        data.forEach((movie) => {
            // Prefer server-provided `image_url` (full URL). Fallback to previous logic if needed.
            let imageSrc = movie.image_url || null;

            if (!imageSrc) {
                const rawImage = movie.image_path || movie.image;
                if (rawImage) {
                    if (/^https?:\/\//i.test(rawImage)) {
                        imageSrc = rawImage;
                    } else {
                        imageSrc = rawImage.startsWith("/")
                            ? rawImage
                            : "/" + rawImage;
                    }
                } else {
                    imageSrc = `https://via.placeholder.com/280x280?text=${encodeURIComponent(
                        movie.title
                    )}`;
                }
            }

            // استخدم id إذا موجود، وإلا fallback إلى '#'
            // إذا المتغير العالمي window.isAuthenticated موجود ومساوي true => افتح صفحة التفاصيل
            // وإلا => اذهب لصفحة تسجيل الدخول
            // Make every card navigate to the login page when clicked.
            // We use window.loginUrl injected via Blade; fallback to '/login'.
            const movieUrl = window.loginUrl || "/login";

            // Format rating to one decimal place (like IMDb: 7.1)
            // Prefer `rating_decimal` (already 0-10 scale). Otherwise, if `rating` is an integer
            // (e.g., 85), normalize to 0-10 by dividing by 10 when appropriate.
            let ratingDisplay = "N/A";
            if (
                movie.rating_decimal !== null &&
                movie.rating_decimal !== undefined &&
                movie.rating_decimal !== ""
            ) {
                const parsed = Number(movie.rating_decimal);
                if (!Number.isNaN(parsed)) {
                    ratingDisplay = parsed.toFixed(1);
                }
            } else if (
                movie.rating !== null &&
                movie.rating !== undefined &&
                movie.rating !== ""
            ) {
                const parsed = Number(movie.rating);
                if (!Number.isNaN(parsed)) {
                    // If stored as 0-100 (e.g., 85) convert to 0-10 by dividing by 10.
                    const normalized = parsed > 10 ? parsed / 10 : parsed;
                    ratingDisplay = normalized.toFixed(1);
                }
            }

            // Convert rating (0-10) to percentage for "User Score" display (0-100)
            let percentDisplay = "N/A";
            let decimalDisplay = null;
            if (ratingDisplay !== "N/A") {
                const parsedRating = Number(ratingDisplay);
                if (!Number.isNaN(parsedRating)) {
                    percentDisplay = Math.round(parsedRating * 10); // e.g. 9.0 -> 90
                    decimalDisplay = parsedRating.toFixed(1);
                }
            }

            // Prepare SVG circle parameters similar to the Blade component
            const uscSize = 44; // matches dashboard include
            const uscStroke = 5;
            const uscRadius = uscSize / 2 - uscStroke;
            const uscCirc = +(2 * Math.PI * uscRadius).toFixed(3);
            const uid = "usc_" + Math.random().toString(36).slice(2, 9);

            const cardHTML = `
  <a href="${movieUrl}" class="movie-link block no-underline" aria-label="Open ${
                movie.title
            }">
    <div class="movie-card bg-transparent text-white rounded-xl overflow-hidden flex-shrink-0 w-[280px] h-[480px] shadow-lg flex flex-col">
      <!-- صورة أعلى البطاقة -->
      <div class="w-full h-[320px] relative overflow-hidden">
        <img
          src="${imageSrc}"
          alt="${movie.title}"
          class="absolute inset-0 w-full h-full object-cover object-top"
          loading="lazy"
        />

        <!-- تراكب تدريجي لتحسين وضوح العنوان -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent pointer-events-none"></div>

        <!-- العنوان داخل الصورة (ظاهر من الأسفل) -->
        <div class="absolute left-4 right-4 bottom-3 z-10">
          <h3 class="text-lg font-bold text-white truncate">${movie.title}</h3>
        </div>
      </div>

      <!-- المحتوى أسفل الصورة (الوصف والتقييم) -->
      <div class="flex-1 flex flex-col justify-between p-4 pt-3">
        <div>
          <p class="text-white/80 text-sm leading-relaxed line-clamp-3">
            ${movie.description}
          </p>
        </div>

                <!-- User Score (animated circular) like dashboard -->
                <div class="mt-5 flex items-center justify-center rating-row">
                    <div id="${uid}" class="user-score-circle inline-flex items-center gap-3" style="line-height:1" data-percent="${
                percentDisplay !== "N/A" ? percentDisplay : ""
            }" data-decimal="${decimalDisplay !== null ? decimalDisplay : ""}">
                        <svg width="${uscSize}" height="${uscSize}" viewBox="0 0 ${uscSize} ${uscSize}" aria-hidden="true">
                            <defs>
                                <linearGradient id="gs-${uid}" x1="0%" x2="100%">
                                    <stop offset="0%" stop-color="#1dd1a1" />
                                    <stop offset="100%" stop-color="#06a" />
                                </linearGradient>
                            </defs>
                            <g transform="translate(${uscSize / 2}, ${
                uscSize / 2
            })">
                                <circle r="${uscRadius}" cx="0" cy="0" fill="none" stroke="#17202a" stroke-width="${uscStroke}" opacity="0.16"></circle>
                                ${
                                    percentDisplay !== "N/A"
                                        ? `<circle id="${uid}-fg" class="usc-circle" r="${uscRadius}" cx="0" cy="0" fill="none" stroke="url(#gs-${uid})" stroke-width="${uscStroke}" stroke-linecap="round" stroke-dasharray="${uscCirc}" stroke-dashoffset="${uscCirc}" transform="rotate(-90)" />`
                                        : `<circle r="${uscRadius}" cx="0" cy="0" fill="none" stroke="#444" stroke-width="${uscStroke}" stroke-dasharray="${uscCirc}" stroke-dashoffset="0" opacity="0.12"></circle>`
                                }
                                <text id="${uid}-text" x="0" y="0" text-anchor="middle" dominant-baseline="central" fill="#fff" font-weight="700" font-size="${Math.max(
                10,
                Math.floor(uscSize / 3.5)
            )}px">${percentDisplay !== "N/A" ? "0%" : "—"}</text>
                            </g>
                        </svg>

                        <div class="user-score-meta text-left">
                            <div class="text-xs text-white/70 leading-tight">User Score</div>
                        </div>
                    </div>
                </div>
      </div>
    </div>
  </a>
`;

            moviesContainer.insertAdjacentHTML("beforeend", cardHTML);
            // Observe / animate the newly inserted User Score element (if helper available)
            try {
                const newEl = document.getElementById(uid);
                if (newEl && window.__observeUserScoreElement) {
                    window.__observeUserScoreElement(newEl);
                }
            } catch (e) {
                // noop
            }
        });
    })
    .catch((error) =>
        console.error("حدث خطأ أثناء الاتصال بـ Laravel:", error)
    );

// User Score animation helper (copied/adapted from Blade component)
if (!window.__userScoreAnimLoaded) {
    window.__userScoreAnimLoaded = true;

    (function () {
        const ease = function (t) {
            return --t * t * t + 1;
        }; // cubic ease-out

        function animateCircle(elem) {
            const percent = parseInt(elem.getAttribute("data-percent"));
            if (isNaN(percent)) return;

            const fg = elem.querySelector(".usc-circle");
            const txt = elem.querySelector("text");
            const decEl =
                elem.querySelector(
                    ".user-score-meta #" + elem.id + "-decimal"
                ) ||
                elem.querySelector("#" + elem.id + "-decimal") ||
                elem.querySelector(".user-score-meta .usc-decimal") ||
                elem.querySelector("#" + elem.id + "-decimal");
            if (!fg || !txt) return;

            const dasharray =
                parseFloat(fg.getAttribute("stroke-dasharray")) || 0;
            const targetDash = dasharray * (percent / 100);
            const start = performance.now();
            const duration = 1100; // ms

            function frame(now) {
                const t = Math.min(1, (now - start) / duration);
                const eased = ease(t);
                const current = Math.max(0, dasharray - targetDash * eased);
                fg.setAttribute("stroke-dashoffset", current);

                // number animation (0 -> percent)
                const displayPct = Math.round(percent * eased);
                txt.textContent = displayPct + "%";

                // decimal update if present
                if (decEl) {
                    const decimalTarget = percent / 10;
                    const decValue = (decimalTarget * eased).toFixed(1);
                    // try to set textContent
                    decEl.textContent = parseFloat(decValue).toFixed(1);
                }

                if (t < 1) requestAnimationFrame(frame);
                else {
                    // ensure final state
                    fg.setAttribute(
                        "stroke-dashoffset",
                        Math.max(0, dasharray - targetDash)
                    );
                    txt.textContent = percent + "%";
                    if (decEl) decEl.textContent = (percent / 10).toFixed(1);

                    // small professional pop + glow on completion
                    try {
                        const parent = elem;
                        parent.classList.add("pop");
                        setTimeout(() => parent.classList.remove("pop"), 360);
                    } catch (err) {
                        // noop
                    }
                }
            }

            requestAnimationFrame(frame);
        }

        // IntersectionObserver will animate when visible
        const observer =
            "IntersectionObserver" in window
                ? new IntersectionObserver(
                      (entries, obs) => {
                          entries.forEach((en) => {
                              if (en.isIntersecting) {
                                  animateCircle(en.target);
                                  obs.unobserve(en.target);
                              }
                          });
                      },
                      { threshold: 0.2 }
                  )
                : null;

        // expose a helper to observe/animate a newly inserted element
        window.__observeUserScoreElement = function (el) {
            if (!el) return;
            if (!el.getAttribute("data-percent")) return;
            if (observer) observer.observe(el);
            else animateCircle(el);
        };

        // automatically observe existing elements on DOMContentLoaded
        document.addEventListener("DOMContentLoaded", function () {
            const elems = document.querySelectorAll(".user-score-circle");
            elems.forEach((el) => {
                if (!el.getAttribute("data-percent")) return;
                if (observer) observer.observe(el);
                else animateCircle(el);
            });
        });
    })();
}

// ✅ تحريك السلايدر يمين ويسار
const container = document.getElementById("moviesContainer");
const nextBtn = document.getElementById("nextMovie");
const prevBtn = document.getElementById("prevMovie");

if (nextBtn && container) {
    nextBtn.addEventListener("click", () => {
        container.scrollBy({ left: 300, behavior: "smooth" });
    });
}

if (prevBtn && container) {
    prevBtn.addEventListener("click", () => {
        container.scrollBy({ left: -300, behavior: "smooth" });
    });
}
