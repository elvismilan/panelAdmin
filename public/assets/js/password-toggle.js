/**
 * password-toggle.js
 * Companion to the theme's .show-hide pattern.
 * Handles any span[data-target] inside .show-hide, swapping fa-eye / fa-eye-slash.
 * No dependencies — plain vanilla JS.
 */

// Suppress the theme's text "show"/"hide" from :before on our custom toggles.
(function () {
    var style = document.createElement('style');
    style.textContent = '.show-hide span[data-target]::before { content: "" !important; }';
    document.head.appendChild(style);
}());

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.show-hide span[data-target]').forEach(function (span) {
        span.addEventListener('click', function () {
            var input = document.getElementById(span.dataset.target);
            var icon  = span.querySelector('i');
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                }
                span.classList.remove('show');
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
                span.classList.add('show');
            }
        });
    });
});
