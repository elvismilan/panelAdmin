    <?php
    $adminAssetBase = isset($adminAssetBase)
      ? (string) $adminAssetBase
      : \Core\Url::to(rtrim((string) ($_ENV['ADMIN_ASSET_BASE'] ?? ($_ENV['LOGIN_OPTION1_ASSET_BASE'] ?? '/assets/theme-one')), '/'));
    ?>
        </div>
        <!-- footer start-->
        <footer class="footer">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-6 footer-copyright">
              </div>
              <div class="col-md-6">
                <p class="pull-right mb-0">Copyright © <a href="https://elvismilan.com" target="_blank" rel="noopener noreferrer" title="Sitio oficial de Elvis Milan">elvismilan.com</a> 2014-26 <i class="fa fa-laptop font-secondary"></i></p>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>
    <!-- latest jquery-->
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/jquery-3.5.1.min.js"></script>
    <!-- feather icon js-->
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/icons/feather-icon/feather.min.js"></script>
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/icons/feather-icon/feather-icon.js"></script>
    <!-- Sidebar jquery-->
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/config.js"></script>
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/sidebar-menu.js"></script>
    <script>
      (function ($) {
        'use strict';

        function normalizePath(path) {
          if (!path) return '/';
          var clean = path.split('?')[0].split('#')[0].trim();
          if (clean === '') return '/';
          if (clean.length > 1 && clean.endsWith('/')) {
            clean = clean.slice(0, -1);
          }
          return clean;
        }

        function hrefToPath(href) {
          if (!href) return '';
          var value = href.trim();
          if (
            value === '' ||
            value === '#' ||
            value.indexOf('javascript:') === 0 ||
            value.indexOf('mailto:') === 0 ||
            value.indexOf('tel:') === 0
          ) {
            return '';
          }

          if (/^https?:\/\//i.test(value)) {
            try {
              var url = new URL(value, window.location.origin);
              if (url.origin !== window.location.origin) return '';
              return normalizePath(url.pathname);
            } catch (e) {
              return '';
            }
          }

          if (value.indexOf('/') !== 0) {
            value = '/' + value;
          }
          return normalizePath(value);
        }

        function matchesPath(currentPath, linkPath) {
          if (!linkPath) return false;
          return currentPath === linkPath || currentPath.indexOf(linkPath + '/') === 0;
        }

        function markExpanded($anchor) {
          if (!$anchor || !$anchor.length) return;
          $anchor.addClass('active');

          var $arrow = $anchor.find('.according-menu i');
          if ($arrow.length) {
            $arrow.removeClass('fa-angle-right').addClass('fa-angle-down');
          }
        }

        function openParents($link) {
          if (!$link || !$link.length) return;
          $link.addClass('active');

          $link.parents('ul.menu-content, ul.submenu-content').each(function () {
            $(this).css('display', 'block');
          });

          $link.parents('li').each(function () {
            var $li = $(this);
            markExpanded($li.children('a.menu-title'));
            markExpanded($li.children('a.submenu-title'));
          });
        }

        $(function () {
          var currentPath = normalizePath(window.location.pathname);
          var $links = $('.main-navbar ul a[href]');
          var $best = $();
          var bestLen = -1;

          $links.each(function () {
            var $link = $(this);
            var linkPath = hrefToPath($link.attr('href'));
            if (!matchesPath(currentPath, linkPath)) return;
            if (linkPath.length > bestLen) {
              bestLen = linkPath.length;
              $best = $link;
            }
          });

          if ($best.length) {
            openParents($best);
          }

          var glassStorageKey = 'admin_glass_theme';
          var $pageWrapper = $('#pageWrapper');
          var $glassToggle = $('#glass-theme-toggle');

          function setGlassState(enabled) {
            $pageWrapper.toggleClass('glass-theme', enabled);
            $('body').toggleClass('glass-active', enabled);
            $glassToggle.toggleClass('active', enabled);
            $glassToggle.attr('title', enabled ? 'Desactivar glass theme' : 'Activar glass theme');
            $glassToggle.attr('aria-pressed', enabled ? 'true' : 'false');
          }

          if ($pageWrapper.length && $glassToggle.length) {
            var storedState = null;
            try {
              storedState = window.localStorage.getItem(glassStorageKey);
            } catch (e) {
              storedState = null;
            }

            var isEnabled = storedState !== 'off';
            setGlassState(isEnabled);

            $glassToggle.on('click', function (event) {
              event.preventDefault();
              isEnabled = !$pageWrapper.hasClass('glass-theme');
              setGlassState(isEnabled);
              try {
                window.localStorage.setItem(glassStorageKey, isEnabled ? 'on' : 'off');
              } catch (e) {
                // Ignore storage errors (private mode/quota)
              }
            });
          }

          function buildColorHref(colorName) {
            var currentHref = $('#color').attr('href') || '';
            var basePath = currentHref.replace(/color-\d+\.css(?:\?.*)?$/i, '');
            if (basePath) return basePath + colorName + '.css';
            return '<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/' + colorName + '.css';
          }

          function applyStoredThemeColor() {
            var color = window.localStorage.getItem('color');
            var primary = window.localStorage.getItem('primary');
            var secondary = window.localStorage.getItem('secondary');

            if (color) {
              $('#color').attr('href', buildColorHref(color));
            }
            if (primary) {
              document.documentElement.style.setProperty('--theme-deafult', primary);
            }
            if (secondary) {
              document.documentElement.style.setProperty('--theme-secondary', secondary);
            }
          }

          applyStoredThemeColor();

          $(document).on('click', '.theme-color-option', function () {
            var $item = $(this);
            var color = String($item.data('theme-color') || 'color-1');
            var primary = String($item.data('primary') || '#24695c');
            var secondary = String($item.data('secondary') || '#ba895d');

            localStorage.setItem('color', color);
            localStorage.setItem('primary', primary);
            localStorage.setItem('secondary', secondary);
            localStorage.removeItem('dark');

            $('#color').attr('href', buildColorHref(color));
            document.documentElement.style.setProperty('--theme-deafult', primary);
            document.documentElement.style.setProperty('--theme-secondary', secondary);
            $('body').removeClass('dark-only');
            location.reload(true);
          });
        });
      })(jQuery);
    </script>
    <!-- Bootstrap js-->
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/bootstrap/popper.min.js"></script>
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/bootstrap/bootstrap.min.js"></script>
    <!-- Plugins JS start-->
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/datepicker/date-picker/datepicker.js"></script>
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/datepicker/date-picker/datepicker.en.js"></script>
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/datepicker/date-picker/datepicker.custom.js"></script>
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/script.js"></script>
    <style>
      .theme-color-dropdown {
        width: 220px;
        top: 40px !important;
        right: -12px !important;
        transform: translateY(0) !important;
        transition: none !important;
      }

      .theme-color-dropdown:before,
      .theme-color-dropdown:after {
        right: 16px !important;
        left: unset !important;
      }

      .theme-color-dropdown li:last-child {
        padding-bottom: 0;
      }

      .theme-color-dropdown li:hover {
        background: transparent !important;
      }

      .theme-color-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
      }

      .theme-color-option {
        width: 100%;
        height: 30px;
        border: 1px solid #dce3ea;
        border-radius: 6px;
        padding: 0;
        -webkit-appearance: none;
        appearance: none;
        cursor: pointer;
      }

      .theme-color-option:hover,
      .theme-color-option:focus,
      .theme-color-option:active {
        transform: none !important;
        box-shadow: none !important;
        filter: none !important;
        opacity: 1 !important;
        outline: none !important;
      }

      .page-main-header .main-header-right .nav-right .notification-dropdown {
        top: 48px !important;
      }
    </style>
    <!-- login js-->
    <!-- Plugin used-->
    <?php foreach (($pageAssets['js'] ?? []) as $_js): ?>
    <script src="<?= htmlspecialchars((string) $_js, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
  </body>
</html>
