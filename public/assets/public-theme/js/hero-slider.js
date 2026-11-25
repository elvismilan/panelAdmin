/**
 * Rotacion del hero-banner del Home (imagenes + cita), alimentado por el
 * slider del admin. No-op automatico si solo hay una imagen activa.
 *
 * Espera: .hero-banner > .hero-banner-media > .hero-banner-img[.is-active],
 * .hero-banner-card #heroBannerText, .hero-banner-dots > .hero-banner-dot,
 * y window.__heroQuotes (array de strings, mismo orden que las imagenes).
 */
(function () {
  'use strict';

  function init() {
    var banner = document.querySelector('.hero-banner');
    if (!banner) return;

    var images = Array.prototype.slice.call(banner.querySelectorAll('.hero-banner-img'));
    if (images.length < 2) return;

    var dots = Array.prototype.slice.call(banner.querySelectorAll('.hero-banner-dot'));
    var textEl = document.getElementById('heroBannerText');
    var quotes = Array.isArray(window.__heroQuotes) ? window.__heroQuotes : [];
    var intervalMs = 6000;
    var current = 0;
    var timer = null;

    function goTo(index) {
      var next = ((index % images.length) + images.length) % images.length;
      if (next === current) return;

      images[current].classList.remove('is-active');
      if (dots[current]) dots[current].classList.remove('is-active');

      current = next;
      images[current].classList.add('is-active');
      if (dots[current]) dots[current].classList.add('is-active');

      if (textEl && typeof quotes[current] === 'string') {
        textEl.classList.add('is-fading');
        setTimeout(function () {
          textEl.textContent = quotes[current];
          textEl.classList.remove('is-fading');
        }, 250);
      }
    }

    function start() {
      stop();
      timer = setInterval(function () { goTo(current + 1); }, intervalMs);
    }

    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () {
        goTo(i);
        start();
      });
    });

    banner.addEventListener('mouseenter', stop);
    banner.addEventListener('mouseleave', start);

    start();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
