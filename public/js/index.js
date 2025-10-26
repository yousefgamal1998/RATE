// RATE - Index Page JavaScript
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
        console.log("🎬 قائمة الأفلام:", data);

        const moviesContainer = document.getElementById("moviesContainer");
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

                <!-- التقييم في الأسفل -->
                <div class="mt-3 text-center">
                    <span class="rating-row inline-flex items-center justify-center">
                        <!-- SVG star to match dashboard styling -->
                        <svg class="w-5 h-5 rating-star" viewBox="0 0 24 24" fill="#FFD700" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.788 1.402 8.168L12 18.896l-7.336 3.868 1.402-8.168L.132 9.21l8.2-1.192L12 .587z" />
                        </svg>
                        <span class="rating-value ml-3 inline-flex items-baseline text-yellow-400 font-semibold">
                            ${
                                ratingDisplay !== "N/A"
                                    ? `<span class="rating-number">${ratingDisplay}</span><span class="rating-suffix">/10</span>`
                                    : "N/A"
                            }
                        </span>
                    </span>
                </div>
      </div>
    </div>
  </a>
`;

            moviesContainer.insertAdjacentHTML("beforeend", cardHTML);
        });
    })
    .catch((error) =>
        console.error("حدث خطأ أثناء الاتصال بـ Laravel:", error)
    );

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
