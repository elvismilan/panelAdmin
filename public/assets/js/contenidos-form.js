(function ($) {
  'use strict';

  function initContentEditor() {
    if (typeof $ !== 'function' || !$.fn || typeof $.fn.summernote !== 'function') {
      return;
    }

    $('.js-contenidos-editor').summernote({
      height: 300,
      tabsize: 2
    });
  }

  function init() {
    initContentEditor();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window.jQuery);
