/**
 * Carrusel de testimonios (components/testimonios.php). Sin librerias, sin autoplay.
 *
 * - Las slides se apilan en un grid (CSS .is-enhanced): todas con el alto de la
 *   mas larga; la activa sube desde la pila y la anterior sale (.is-leaving) sin
 *   animacion con prefers-reduced-motion (lo resuelve el CSS).
 * - Flechas, dots, contador "02 / 05" y teclado sobre la pista (← → dan la
 *   vuelta; Inicio y Fin van a los extremos). Deslizar con el dedo (pointer events).
 * - "Leer mas": si el texto pasa de 6 lineas se corta y aparece el boton
 *   (aria-expanded); al desplegar, el grid recalcula el alto solo.
 * - Las slides ocultas usan visibility:hidden, asi que quedan fuera del orden de
 *   tabulacion y de los lectores de pantalla.
 * - Un mismo script sirve para varios bloques en la pagina ([data-testimonios]).
 */
(function () {
  'use strict';

  var SWIPE_MIN = 40;
  var LEAVE_MS = 560; // un poco mas que la animacion testimonios-rise/leave de style.css (.55s)

  function pad(n) {
    return (n < 10 ? '0' : '') + n;
  }

  function setupReadMore(root) {
    var buttons = Array.prototype.slice.call(root.querySelectorAll('[data-testimonios-more]'));

    function measure() {
      buttons.forEach(function (btn) {
        var text = document.getElementById(btn.getAttribute('aria-controls'));
        if (!text || btn.getAttribute('aria-expanded') === 'true') return;
        text.classList.add('is-clamped');
        // Si con el corte no se esconde nada, el texto entra entero: sin boton.
        var overflows = text.scrollHeight - text.clientHeight > 2;
        if (!overflows) text.classList.remove('is-clamped');
        btn.hidden = !overflows;
      });
    }

    function setExpanded(btn, expand) {
      var text = document.getElementById(btn.getAttribute('aria-controls'));
      if (!text) return;
      btn.setAttribute('aria-expanded', expand ? 'true' : 'false');
      btn.textContent = expand ? 'Leer menos' : 'Leer más';
      text.classList.toggle('is-clamped', !expand);
    }

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setExpanded(btn, btn.getAttribute('aria-expanded') !== 'true');
      });
    });

    // Al salir de una tarjeta desplegada se vuelve a cortar, para que el resto
    // no herede su alto.
    root.addEventListener('testimonios:change', function () {
      buttons.forEach(function (btn) {
        var slide = btn.closest('[data-testimonios-slide]');
        if (slide && !slide.classList.contains('is-active') && btn.getAttribute('aria-expanded') === 'true') {
          setExpanded(btn, false);
        }
      });
    });

    measure();
    var timer = null;
    window.addEventListener('resize', function () {
      window.clearTimeout(timer);
      timer = window.setTimeout(measure, 150);
    });
    // Cuando cargan las fuentes cambia el alto de las lineas.
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(measure);
  }

  function setup(root) {
    var track = root.querySelector('[data-testimonios-track]');
    if (!track) return;
    var slides = Array.prototype.slice.call(track.querySelectorAll('[data-testimonios-slide]'));

    root.classList.add('is-enhanced');
    setupReadMore(root);
    if (slides.length < 2) return;

    var controls = root.querySelector('[data-testimonios-controls]');
    var dotsWrap = root.querySelector('[data-testimonios-dots]');
    var dots = dotsWrap ? Array.prototype.slice.call(dotsWrap.querySelectorAll('[data-testimonios-dot]')) : [];
    var prev = root.querySelector('[data-testimonios-prev]');
    var next = root.querySelector('[data-testimonios-next]');
    var currentEl = root.querySelector('[data-testimonios-current]');
    var status = root.querySelector('[data-testimonios-status]');
    var current = 0;

    if (controls) controls.hidden = false;
    if (dotsWrap) dotsWrap.hidden = false;

    function wrap(i) {
      return (i + slides.length) % slides.length;
    }

    function goTo(index, announce) {
      var target = Math.max(0, Math.min(slides.length - 1, index));
      var previous = current;
      current = target;
      // Pila de cartas: la de adelante sale (hacia atras si se retrocede) mientras sube la siguiente.
      if (previous !== target && slides[previous]) {
        var leaving = slides[previous];
        var entering = slides[target];
        leaving.classList.remove('is-entering');
        leaving.classList.add('is-leaving');
        leaving.classList.toggle('is-back', target < previous);
        entering.classList.remove('is-leaving', 'is-back');
        // Reinicia la animacion si se cambia rapido.
        entering.classList.remove('is-entering');
        void entering.offsetWidth;
        entering.classList.add('is-entering');
        [leaving, entering].forEach(function (el) {
          window.clearTimeout(el._testimoniosTimer);
          el._testimoniosTimer = window.setTimeout(function () {
            el.classList.remove('is-leaving', 'is-back', 'is-entering');
          }, LEAVE_MS);
        });
      }
      slides.forEach(function (slide, i) {
        slide.classList.toggle('is-active', i === target);
      });
      dots.forEach(function (dot, i) {
        if (i === target) {
          dot.setAttribute('aria-current', 'true');
        } else {
          dot.removeAttribute('aria-current');
        }
      });
      if (currentEl) currentEl.textContent = pad(target + 1);
      root.dispatchEvent(new CustomEvent('testimonios:change', { detail: { index: target } }));
      if (announce && status) {
        status.textContent = 'Testimonio ' + (target + 1) + ' de ' + slides.length;
      }
    }

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () { goTo(i, true); });
    });
    if (prev) prev.addEventListener('click', function () { goTo(wrap(current - 1), true); });
    if (next) next.addEventListener('click', function () { goTo(wrap(current + 1), true); });

    track.addEventListener('keydown', function (e) {
      var target;
      switch (e.key) {
        case 'ArrowRight': target = wrap(current + 1); break;
        case 'ArrowLeft': target = wrap(current - 1); break;
        case 'Home': target = 0; break;
        case 'End': target = slides.length - 1; break;
        default: return;
      }
      e.preventDefault();
      goTo(target, true);
    });

    // Deslizar con el dedo (o arrastrar con el mouse): horizontal, minimo 40 px.
    var startX = null;
    var startY = null;
    track.addEventListener('pointerdown', function (e) {
      if (e.pointerType === 'mouse' && e.button !== 0) return;
      startX = e.clientX;
      startY = e.clientY;
    });
    track.addEventListener('pointerup', function (e) {
      if (startX === null) return;
      var dx = e.clientX - startX;
      var dy = e.clientY - startY;
      startX = null;
      if (Math.abs(dx) < SWIPE_MIN || Math.abs(dx) < Math.abs(dy)) return;
      goTo(wrap(current + (dx < 0 ? 1 : -1)), true);
    });
    track.addEventListener('pointercancel', function () { startX = null; });

    goTo(0, false);
  }

  function init() {
    Array.prototype.forEach.call(document.querySelectorAll('[data-testimonios]'), setup);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
