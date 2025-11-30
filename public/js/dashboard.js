// RATE - Dashboard Page JavaScript
// JavaScript functionality for the dashboard page

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

    // Movie card interactions
    // Note: removed blocking alert() to allow links/navigation without modal dialogs.
    // Clicking the card will follow the anchor link added on the server-side.
    document.querySelectorAll(".movie-card").forEach((card) => {
        // if you want keyboard support for the whole card, we can add tabindex and key handlers later
    });

    // Device logo interactions (updated selector)
    document.querySelectorAll(".device-item").forEach((device) => {
        device.addEventListener("click", function () {
            // prefer aria-label if set, otherwise fallback to visible text
            const deviceName =
                this.getAttribute("aria-label") ||
                (this.textContent || "").trim();
            if (deviceName) {
                // avoid intrusive alert; use console for debugging
                console.info(`Download RATE for ${deviceName}`);
            }
        });
    });

    // Carousel arrow functionality
    const carousel = document.querySelector(".carousel");
    const leftOverlay = document.querySelector(".carousel-arrow.left");
    const rightOverlay = document.querySelector(".carousel-arrow.right");

    function scrollByCard(direction = 1) {
        if (!carousel) return;
        const card = carousel.querySelector(".movie-card");
        if (!card) return;
        const cardWidth =
            card.offsetWidth +
            parseInt(getComputedStyle(carousel).columnGap || 24);
        carousel.scrollBy({ left: direction * cardWidth, behavior: "smooth" });
    }

    if (leftOverlay)
        leftOverlay.addEventListener("click", () => scrollByCard(-1));
    if (rightOverlay)
        rightOverlay.addEventListener("click", () => scrollByCard(1));

    // Show/hide overlay arrows based on scroll position
    function updateArrows() {
        if (!carousel) return;
        const maxScroll = carousel.scrollWidth - carousel.clientWidth;
        if (leftOverlay)
            leftOverlay.style.opacity = carousel.scrollLeft > 10 ? "1" : "0.4";
        if (rightOverlay)
            rightOverlay.style.opacity =
                carousel.scrollLeft < maxScroll - 10 ? "1" : "0.4";
    }

    if (carousel) {
        carousel.addEventListener("scroll", updateArrows);
        updateArrows();
    }

    // Keyboard navigation
    document.addEventListener("keydown", function (e) {
        if (e.key === "ArrowLeft") scrollByCard(-1);
        if (e.key === "ArrowRight") scrollByCard(1);
    });

    // Quick Edit (Load + Update) — supports editing title and visibility from dashboard
    (function () {
        const loadInput = document.getElementById("dashLoadId");
        const loadBtn = document.getElementById("dashLoadBtn");
        const titleInput = document.getElementById("dashTitle");
        const visSelect = document.getElementById("dashVisibility");
        const updateBtn = document.getElementById("dashUpdateBtn");
        const status = document.getElementById("dashEditStatus");

        // Allow loading on dashboard even if the Update button was removed.
        if (!loadBtn) return;

        let currentId = null;

        async function fetchMovieList() {
            try {
                const res = await fetch("/api/movies");
                if (!res.ok) return null;
                return await res.json();
            } catch (e) {
                return null;
            }
        }

        loadBtn.addEventListener("click", async function () {
            const id = parseInt(loadInput.value, 10);
            status.textContent = "";
            if (!id || id <= 0) {
                status.textContent = "Enter a valid ID";
                return;
            }
            loadBtn.disabled = true;
            status.textContent = "Loading...";

            const list = await fetchMovieList();
            if (!list) {
                status.textContent = "Failed to load movies";
                loadBtn.disabled = false;
                return;
            }

            const movie = list.find((m) => Number(m.id) === Number(id));
            if (!movie) {
                status.textContent = "Movie not found";
                loadBtn.disabled = false;
                return;
            }

            titleInput.value = movie.title || "";
            if (visSelect) visSelect.value = movie.visibility || "dashboard";
            currentId = movie.id;
            if (updateBtn) updateBtn.disabled = false;
            status.textContent = "Movie loaded — edit and press Update.";
            loadBtn.disabled = false;
        });
        if (updateBtn) {
            updateBtn.addEventListener("click", async function () {
                if (!currentId) {
                    status.textContent = "Load a movie first.";
                    return;
                }
                updateBtn.disabled = true;
                status.textContent = "Updating...";

                const payload = {
                    title: titleInput.value,
                    visibility: visSelect ? visSelect.value : undefined,
                };

                try {
                    const res = await fetch(
                        `/api/movies/${encodeURIComponent(currentId)}`,
                        {
                            method: "PUT",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify(payload),
                        }
                    );

                    const json = await res.json().catch(() => ({}));
                    if (res.ok) {
                        status.textContent = json.message || "Movie updated";
                    } else {
                        status.textContent =
                            json.message || `Update failed (${res.status})`;
                    }
                } catch (err) {
                    console.error(err);
                    status.textContent = "Network or server error";
                } finally {
                    updateBtn.disabled = false;
                    setTimeout(() => (status.textContent = ""), 3500);
                }
            });
        }
    })();
});
