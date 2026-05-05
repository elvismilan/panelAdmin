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
                <p class="pull-right mb-0">Copyright © elvismilan.com 2014-26 <i class="fa fa-laptop font-secondary"></i></p>
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
    <!-- <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/theme-customizer/customizer.js"></script> -->
    <!-- login js-->
    <!-- Plugin used-->
    <?php foreach (($pageAssets['js'] ?? []) as $_js): ?>
    <script src="<?= htmlspecialchars((string) $_js, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
  </body>
</html>
