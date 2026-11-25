'use strict';

(function ($) {
    if (!$ || typeof $.fn.ionRangeSlider !== 'function') {
        return;
    }

    var MIN = 0;
    var MAX = 0.99;
    var STEP = 0.01;
    var FALLBACK = 0.50;

    function clamp(value) {
        if (!Number.isFinite(value)) {
            return FALLBACK;
        }

        if (value < MIN) {
            return MIN;
        }

        if (value > MAX) {
            return MAX;
        }

        return value;
    }

    function parseOverlay(rawValue) {
        var parsed = parseFloat(String(rawValue).replace(',', '.'));
        return clamp(parsed);
    }

    function formatOverlay(value) {
        return parseOverlay(value).toFixed(2);
    }

    function updateOutput($output, value) {
        if ($output && $output.length) {
            $output.text(formatOverlay(value));
        }
    }

    $(function () {
        $('[data-overlay-slider="1"]').each(function () {
            var $input = $(this);
            var outputSelector = String($input.data('output') || '');
            var $output = outputSelector !== '' ? $(outputSelector) : $();
            var initial = parseOverlay($input.val());

            $input.val(formatOverlay(initial));
            updateOutput($output, initial);

            $input.ionRangeSlider({
                min: MIN,
                max: MAX,
                from: initial,
                step: STEP,
                grid: true,
                prettify_enabled: false,
                onStart: function (data) {
                    var safe = formatOverlay(data.from);
                    $input.val(safe);
                    updateOutput($output, safe);
                },
                onChange: function (data) {
                    var safe = formatOverlay(data.from);
                    $input.val(safe);
                    updateOutput($output, safe);
                },
                onFinish: function (data) {
                    var safe = formatOverlay(data.from);
                    $input.val(safe);
                    updateOutput($output, safe);
                }
            });
        });

        $('form').on('submit', function () {
            $(this).find('[data-overlay-slider="1"]').each(function () {
                var $input = $(this);
                $input.val(formatOverlay($input.val()));
            });
        });
    });
})(window.jQuery);
