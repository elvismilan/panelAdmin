<?php

declare(strict_types=1);

use App\Models\GaleriaModel;

test('GaleriaModel::isValidDate acepta vacio y fechas AAAA-MM-DD que existen', function (): void {
    foreach ([null, '', '2026-11-26', '2028-02-29'] as $date) {
        assert_true(GaleriaModel::isValidDate($date), var_export($date, true));
    }
});

test('GaleriaModel::isValidDate rechaza formatos y fechas invalidas', function (): void {
    foreach (['abc', '26/11/2026', '2026-2-3', '2026-02-31', '2027-02-29', "2026-11-26\n", ['2026-11-26']] as $date) {
        assert_false(GaleriaModel::isValidDate($date), var_export($date, true));
    }
});
