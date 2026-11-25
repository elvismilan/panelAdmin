/**
 * Lightbox ligero para listados admin — generico y sin dependencias.
 *
 * Uso:
 *   <a href="ruta/imagen-grande.jpg"
 *      class="js-lightbox"
 *      data-lightbox-group="galeria-5"
 *      data-caption="Texto alternativo">
 *     <img src="thumb.jpg" alt="...">
 *   </a>
 *
 * Los anchors con el mismo data-lightbox-group navegan con prev/next,
 * flechas del teclado y Esc para cerrar.
 */
(function () {
  'use strict';

  var CSS = [
    '.adm-lightbox{position:fixed;inset:0;z-index:1080;display:flex;align-items:center;justify-content:center;',
    'background:rgba(13,28,42,.92);opacity:0;transition:opacity .18s ease;}',
    '.adm-lightbox.is-open{opacity:1;}',
    '.adm-lightbox__figure{margin:0;max-width:min(92vw,1100px);max-height:90vh;display:flex;flex-direction:column;align-items:center;gap:.75rem;}',
    '.adm-lightbox__img{max-width:100%;max-height:78vh;object-fit:contain;border-radius:6px;box-shadow:0 18px 50px rgba(0,0,0,.45);}',
    '.adm-lightbox__caption{color:#fff;font-size:.95rem;text-align:center;max-width:80ch;opacity:.9;}',
    '.adm-lightbox__counter{color:rgba(255,255,255,.55);font-size:.78rem;}',
    '.adm-lightbox__btn{position:absolute;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.25);',
    'color:#fff;width:44px;height:44px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;',
    'cursor:pointer;transition:background .15s ease;font-size:18px;}',
    '.adm-lightbox__btn:hover{background:rgba(255,255,255,.22);}',
    '.adm-lightbox__btn:disabled{opacity:.3;cursor:default;}',
    '.adm-lightbox__close{top:18px;right:18px;}',
    '.adm-lightbox__prev{left:18px;top:50%;transform:translateY(-50%);}',
    '.adm-lightbox__next{right:18px;top:50%;transform:translateY(-50%);}',
    '@media (max-width:576px){.adm-lightbox__prev{left:6px;}.adm-lightbox__next{right:6px;}}'
  ].join('');

  var overlay = null;
  var items = [];
  var current = 0;

  function injectStyles() {
    if (document.getElementById('adm-lightbox-css')) return;
    var style = document.createElement('style');
    style.id = 'adm-lightbox-css';
    style.textContent = CSS;
    document.head.appendChild(style);
  }

  function buildOverlay() {
    overlay = document.createElement('div');
    overlay.className = 'adm-lightbox';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.innerHTML =
      '<button type="button" class="adm-lightbox__btn adm-lightbox__close" aria-label="Cerrar"><i class="fa fa-times" aria-hidden="true"></i></button>' +
      '<button type="button" class="adm-lightbox__btn adm-lightbox__prev" aria-label="Anterior"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>' +
      '<figure class="adm-lightbox__figure">' +
      '  <img class="adm-lightbox__img" src="" alt="">' +
      '  <figcaption class="adm-lightbox__caption"></figcaption>' +
      '  <span class="adm-lightbox__counter"></span>' +
      '</figure>' +
      '<button type="button" class="adm-lightbox__btn adm-lightbox__next" aria-label="Siguiente"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>';

    overlay.querySelector('.adm-lightbox__close').addEventListener('click', close);
    overlay.querySelector('.adm-lightbox__prev').addEventListener('click', function () { move(-1); });
    overlay.querySelector('.adm-lightbox__next').addEventListener('click', function () { move(1); });
    overlay.addEventListener('click', function (event) {
      if (event.target === overlay || event.target.classList.contains('adm-lightbox__figure')) close();
    });

    document.body.appendChild(overlay);
  }

  function render() {
    var item = items[current];
    if (!item) return;

    overlay.querySelector('.adm-lightbox__img').src = item.src;
    overlay.querySelector('.adm-lightbox__img').alt = item.caption;
    overlay.querySelector('.adm-lightbox__caption').textContent = item.caption;
    overlay.querySelector('.adm-lightbox__counter').textContent =
      items.length > 1 ? (current + 1) + ' / ' + items.length : '';

    var multiple = items.length > 1;
    overlay.querySelector('.adm-lightbox__prev').style.display = multiple ? '' : 'none';
    overlay.querySelector('.adm-lightbox__next').style.display = multiple ? '' : 'none';
  }

  function move(step) {
    if (items.length < 2) return;
    current = (current + step + items.length) % items.length;
    render();
  }

  function open(group, index) {
    injectStyles();
    if (!overlay) buildOverlay();

    items = group;
    current = index;
    render();

    overlay.classList.add('is-open');
    overlay.style.display = 'flex';
    document.addEventListener('keydown', onKeydown);
    document.body.style.overflow = 'hidden';
  }

  function close() {
    if (!overlay) return;
    overlay.classList.remove('is-open');
    overlay.style.display = 'none';
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
  }

  function onKeydown(event) {
    if (event.key === 'Escape') close();
    if (event.key === 'ArrowLeft') move(-1);
    if (event.key === 'ArrowRight') move(1);
  }

  function collectGroup(anchor) {
    var groupName = anchor.dataset.lightboxGroup || '';
    var anchors = groupName !== ''
      ? Array.prototype.slice.call(document.querySelectorAll('.js-lightbox[data-lightbox-group="' + groupName + '"]'))
      : [anchor];

    return {
      items: anchors.map(function (a) {
        return { src: a.getAttribute('href'), caption: a.dataset.caption || '' };
      }),
      index: Math.max(0, anchors.indexOf(anchor))
    };
  }

  document.addEventListener('click', function (event) {
    var anchor = event.target.closest('.js-lightbox');
    if (!anchor) return;
    event.preventDefault();

    var group = collectGroup(anchor);
    open(group.items, group.index);
  });
})();
