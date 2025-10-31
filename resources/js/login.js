// RATE - Login Page JavaScript (migrated from public/js/login.js)
// JavaScript functionality for the login page

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("loginForm");
    if (!form) return;

    const submitBtn = document.querySelector('button[type="submit"]');

    // Form submission: perform light client-side checks, then allow normal POST submission
    form.addEventListener("submit", function (e) {
        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;

        if (!email || !password) {
            e.preventDefault();
            alert("Please fill in all fields");
            return;
        }

        // Disable button to prevent duplicate submits; allow the browser to submit the form to the server
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = "Signing In...";
        }
        // No preventDefault here: let the form POST to the server for server-side validation.
    });

    // Form validation on input
    const inputs = form.querySelectorAll("input");
    inputs.forEach((input) => {
        input.addEventListener("blur", function () {
            if (this.value.trim() === "") {
                this.classList.add("border-rate-red");
                this.classList.remove("border-white/20");
            } else {
                this.classList.remove("border-rate-red");
                this.classList.add("border-white/20");
            }
        });
    });

    // Social login buttons
    document.querySelectorAll('a[href^="#"]').forEach((btn) => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            let provider = "Unknown";
            if (this.textContent.includes("Google")) {
                provider = "Google";
            } else if (this.textContent.includes("Facebook")) {
                provider = "Facebook";
            } else if (this.textContent.includes("Twitter")) {
                provider = "Twitter";
            }
            alert(`Sign in with ${provider} - Feature coming soon!`);
        });
    });

    // Google OAuth Popup
    function openAuthPopup(url) {
        var w = 600;
        var h = 700;
        var left = screen.width / 2 - w / 2;
        var top = screen.height / 2 - h / 2;

        var popup = window.open(
            url,
            "oauthPopup",
            `toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=${w},height=${h},top=${top},left=${left}`
        );

        if (!popup) {
            alert("Please allow popups for this site to sign in with Google.");
            return;
        }

        // استمع لرسالة من النافذة الفرعية

        // Listen for a single message from the popup and then remove the listener.
        window.addEventListener(
            "message",
            function onMessage(e) {
                if (!e.data) return;
                if (e.data.success) {
                    if (e.data.redirect) {
                        window.location.href = e.data.redirect;
                    } else {
                        window.location.reload();
                    }
                } else if (e.data && e.data.success === false) {
                    alert("Google sign-in failed or was cancelled.");
                }
            },
            { once: true }
        );

        // خيار: إذا أغلق المستخدم النافذة بدون إنهاء، نتحقق كل نصف ثانية
        var checkPopupClosed = setInterval(function () {
            if (popup.closed) {
                clearInterval(checkPopupClosed);
                // قد نقوم بعمل إعادة تحميل أو شيء آخر إذا أردت
            }
        }, 500);
    }

    // Expose the function to the global scope so inline onclick handlers can call it
    // (login.blade.php uses onclick="openAuthPopup(...)").
    window.openAuthPopup = openAuthPopup;
});
