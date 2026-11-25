(function ($) {
  'use strict';

  function slugify(value) {
    var text = (value || '').toString().trim().toLowerCase();

    if (typeof text.normalize === 'function') {
      text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    text = text.replace(/[^a-z0-9]+/g, '-');
    text = text.replace(/^-+|-+$/g, '');
    return text;
  }

  function initSlugSync() {
    var titleInput = document.getElementById('titulo');
    var slugInput = document.getElementById('slug');

    // Al editar, el slug queda fijo (data-slug-locked): cambiar la URL de un
    // articulo publicado pierde su posicionamiento. El servidor tambien lo conserva.
    if (!titleInput || !slugInput || slugInput.hasAttribute('data-slug-locked')) {
      return;
    }

    function syncSlug() {
      slugInput.value = slugify(titleInput.value);
    }

    titleInput.addEventListener('input', syncSlug);

    if ((slugInput.value || '').trim() === '') {
      syncSlug();
    }
  }

  function initContentEditor() {
    if (typeof $ !== 'function' || !$.fn || typeof $.fn.summernote !== 'function') {
      return;
    }

    $('.js-blogs-editor').summernote({
      height: 300,
      tabsize: 2
    });
  }

  function initCategoriesSelect() {
    if (typeof $ !== 'function' || !$.fn || typeof $.fn.select2 !== 'function') {
      return;
    }

    var $select = $('.js-blog-categories-select');
    if (!$select.length) {
      return;
    }

    $select.each(function () {
      var $el = $(this);
      var placeholder = $el.attr('data-placeholder') || 'Selecciona categorias';

      $el.select2({
        width: '100%',
        placeholder: placeholder,
        allowClear: true,
        closeOnSelect: false
      });
    });
  }

  function init() {
    initSlugSync();
    initCategoriesSelect();
    initContentEditor();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window.jQuery);
