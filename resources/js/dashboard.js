// Source for dashboard.js — Quick Edit support (load+update)
// This file is a small source copy; the app serves the compiled/public file at /js/dashboard.js

document.addEventListener("DOMContentLoaded", function () {
    // Quick Edit (small helpers)
    const loadInput = document.getElementById("dashLoadId");
    const loadBtn = document.getElementById("dashLoadBtn");
    const titleInput = document.getElementById("dashTitle");
    const visSelect = document.getElementById("dashVisibility");
    const updateBtn = document.getElementById("dashUpdateBtn");
    const status = document.getElementById("dashEditStatus");

    // allow the dashboard quick-load to work even if the Update button
    // was removed (update is handled on the Add Movie page). Stop only
    // when the load button is missing.
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
    // If the update button still exists (older layout), wire it up; otherwise
    // updating is expected to be done via the Add Movie page.
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
});
