/**
 * Megamenu de pantalla completa: boton hamburguesa (#menuToggle) que abre/
 * cierra el overlay (#megaMenu). Bloquea el scroll del body mientras esta
 * abierto y se cierra con click en un link, boton, click fuera o Escape.
 */
(function () {
  'use strict';

  function init() {
    var toggle = document.getElementById('menuToggle');
    var menu = document.getElementById('megaMenu');
    if (!toggle || !menu) return;

    function isOpen() {
      return menu.classList.contains('is-open');
    }

    function open() {
      menu.classList.add('is-open');
      menu.setAttribute('aria-hidden', 'false');
      toggle.setAttribute('aria-expanded', 'true');
      document.documentElement.classList.add('mega-menu-open');
      document.body.classList.add('mega-menu-open');
    }

    function close() {
      menu.classList.remove('is-open');
      menu.setAttribute('aria-hidden', 'true');
      toggle.setAttribute('aria-expanded', 'false');
      document.documentElement.classList.remove('mega-menu-open');
      document.body.classList.remove('mega-menu-open');
    }

    toggle.addEventListener('click', function () {
      if (isOpen()) {
        close();
      } else {
        open();
      }
    });

    menu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', close);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && isOpen()) {
        close();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
