document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById("image");
    const fileName = document.getElementById("fileName");
    const addForm = document.getElementById("addMovieForm");
    const submitBtn = document.getElementById("submitBtn");
    const resetBtn = document.getElementById("resetBtn");
    const status = document.getElementById("status");

    const deleteInput = document.getElementById("deleteMovieId");
    const deleteBtn = document.getElementById("deleteBtn");
    const deleteStatus = document.getElementById("deleteStatus");
    const loadInput = document.getElementById("loadMovieId");
    const loadBtn = document.getElementById("loadBtn");
    const updateBtn = document.getElementById("updateBtn");
    const clearEditBtn = document.getElementById("clearEditBtn");
    const editStatus = document.getElementById("editStatus");

    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "";

    // File input display
    if (fileInput && fileName) {
        fileInput.addEventListener("change", function (e) {
            const f = e.target.files && e.target.files[0];
            if (f) fileName.textContent = f.name;
            else fileName.textContent = "لم يتم اختيار أي ملف";
        });
    }

    // Reset handler
    if (resetBtn) {
        resetBtn.addEventListener("click", function () {
            addForm.reset();
            if (fileName) fileName.textContent = "لم يتم اختيار أي ملف";
            status.textContent = "";
        });
    }

    // Submit (add movie) via fetch to API
    if (addForm) {
        addForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            submitBtn.disabled = true;
            status.textContent = "Saving...";

            try {
                const fd = new FormData(addForm);

                const res = await fetch("/api/movies", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: fd,
                });

                const json = await res.json();

                if (res.ok) {
                    status.textContent = json.message || "Movie saved";
                    // if API returned movie object, show its id and populate delete input for convenience
                    if (json.movie && json.movie.id) {
                        deleteInput.value = json.movie.id;
                        deleteStatus.textContent = "";
                    }
                    addForm.reset();
                    if (fileName) fileName.textContent = "لم يتم اختيار أي ملف";
                } else {
                    status.textContent = json.message || "Failed to save";
                }
            } catch (err) {
                console.error(err);
                status.textContent = "Network or server error";
            } finally {
                submitBtn.disabled = false;
                setTimeout(() => {
                    status.textContent = "";
                }, 4000);
            }
        });
    }

    // Load movie by ID (uses /api/movies index and finds by id)
    let currentMovieId = null;
    async function fetchMovieById(id) {
        try {
            const res = await fetch("/api/movies");
            if (!res.ok) return null;
            const list = await res.json();
            return list.find((m) => Number(m.id) === Number(id)) || null;
        } catch (e) {
            return null;
        }
    }

    if (loadBtn) {
        loadBtn.addEventListener("click", async function () {
            const id = parseInt(loadInput.value, 10);
            editStatus.textContent = "";
            if (!id || id <= 0) {
                editStatus.textContent = "Enter a valid ID to load.";
                return;
            }

            loadBtn.disabled = true;
            editStatus.textContent = "Loading...";

            const movie = await fetchMovieById(id);
            if (!movie) {
                editStatus.textContent = "Movie not found.";
                loadBtn.disabled = false;
                return;
            }

            // Populate form fields (safe defaults)
            document.getElementById("title").value = movie.title || "";
            document.getElementById("description").value =
                movie.description || "";
            // Prefer rating_decimal, fallback to user_score/10
            const r =
                movie.rating_decimal ??
                (movie.user_score ? movie.user_score / 10 : "");
            document.getElementById("user_score").value = r;
            if (movie.visibility)
                document.getElementById("visibility").value = movie.visibility;
            if (
                movie.dashboard_id !== undefined &&
                document.getElementById("dashboard_id")
            )
                document.getElementById("dashboard_id").value =
                    movie.dashboard_id || "";
            if (movie.is_featured)
                document.getElementById("is_featured").checked =
                    !!movie.is_featured;

            currentMovieId = movie.id;
            updateBtn.disabled = false;
            editStatus.textContent = "Movie loaded — you may update it.";
            loadBtn.disabled = false;
        });
    }

    // Clear edit mode
    if (clearEditBtn) {
        clearEditBtn.addEventListener("click", function () {
            currentMovieId = null;
            updateBtn.disabled = true;
            editStatus.textContent = "";
            addForm.reset();
            if (fileName) fileName.textContent = "لم يتم اختيار أي ملف";
        });
    }

    // Update movie
    if (updateBtn) {
        updateBtn.addEventListener("click", async function () {
            if (!currentMovieId) {
                editStatus.textContent = "No movie loaded to update.";
                return;
            }

            // collect updatable fields
            const payload = {
                title: document.getElementById("title").value,
                description: document.getElementById("description").value,
                user_score: document.getElementById("user_score").value,
                visibility: document.getElementById("visibility")
                    ? document.getElementById("visibility").value
                    : undefined,
                is_featured: document.getElementById("is_featured")
                    ? document.getElementById("is_featured").checked
                        ? 1
                        : 0
                    : undefined,
                dashboard_id:
                    document.getElementById("dashboard_id") &&
                    document.getElementById("dashboard_id").value
                        ? parseInt(
                              document.getElementById("dashboard_id").value,
                              10
                          )
                        : null,
            };

            updateBtn.disabled = true;
            editStatus.textContent = "Updating...";

            try {
                const res = await fetch(
                    `/api/movies/${encodeURIComponent(currentMovieId)}`,
                    {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        body: JSON.stringify(payload),
                    }
                );

                const json = await res.json().catch(() => ({}));
                if (res.ok) {
                    editStatus.textContent =
                        json.message || "Movie updated successfully.";
                    // keep edit mode enabled so user may continue
                } else {
                    editStatus.textContent =
                        json.message || `Update failed (${res.status})`;
                }
            } catch (err) {
                console.error(err);
                editStatus.textContent = "Network or server error";
            } finally {
                updateBtn.disabled = false;
                setTimeout(() => {
                    editStatus.textContent = "";
                }, 4000);
            }
        });
    }

    // Delete movie by ID
    if (deleteBtn) {
        deleteBtn.addEventListener("click", async function () {
            const id = parseInt(deleteInput.value, 10);
            deleteStatus.textContent = "";
            if (!id || id <= 0) {
                deleteStatus.textContent = "Please enter a valid movie ID.";
                return;
            }

            const ok = window.confirm(
                `Are you sure you want to permanently delete movie #${id} ?`
            );
            if (!ok) return;

            deleteBtn.disabled = true;
            deleteStatus.textContent = "Deleting...";

            try {
                const res = await fetch(`/api/movies/${id}`, {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                });

                const json = await res.json();

                if (res.ok) {
                    deleteStatus.textContent =
                        json.message || `Movie ${id} deleted`;
                    // clear input when deleted
                    deleteInput.value = "";
                } else {
                    deleteStatus.textContent =
                        json.message || `Failed to delete (${res.status})`;
                }
            } catch (err) {
                console.error(err);
                deleteStatus.textContent = "Network or server error";
            } finally {
                deleteBtn.disabled = false;
                setTimeout(() => {
                    deleteStatus.textContent = "";
                }, 5000);
            }
        });
    }
});
