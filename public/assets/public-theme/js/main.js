// ============================================================
// Interacciones de la página Inicio
// ============================================================
(() => {
  'use strict';

  // El menu principal (hamburguesa + megamenu) vive en mega-menu.js.

  // --- Botón volver arriba --------------------------------------------------
  const backToTop = document.getElementById('backToTop');
  const toggleBackToTop = () => {
    backToTop.classList.toggle('is-visible', window.scrollY > 480);
  };
  window.addEventListener('scroll', toggleBackToTop, { passive: true });
  backToTop?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
  toggleBackToTop();

  // El envio real del formulario de contacto (fetch a POST /consultas) vive
  // en consulta-form.js, para no duplicar/chocar manejadores sobre el mismo form.
})();
