<?php
if (!isset($fmtVal)) {
    $fmtVal = static fn(mixed $v): string =>
        ($n = (float) $v) == floor($n)
            ? number_format($n, 0, '.', '')
            : number_format($n, 2, '.', '');
}
