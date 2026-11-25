/**
 * Agrega la clase 'is-scrolled' a .site-header al desplazarse, para que el
 * topbar pase de transparente a glass/blur con una transicion suave.
 */
(function () {
  'use strict';

  function init() {
    var header = document.querySelector('.site-header');
    if (!header) return;

    var threshold = 24;

    function update() {
      header.classList.toggle('is-scrolled', window.scrollY > threshold);
    }

    window.addEventListener('scroll', update, { passive: true });
    update();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
