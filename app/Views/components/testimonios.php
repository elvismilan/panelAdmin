<?php
/**
 * Bloque de testimonios: carrusel con un testimonio a la vez.
 * Escritorio en dos columnas (texto + flechas + contador | tarjeta y dots);
 * en el celular se apila (titulo, tarjeta deslizable, dots y contador).
 *
 * Reutilizable en cualquier pagina publica (como en HomeController):
 *
 *   Controlador: 'testimonios' => (new TestimonioModel())->getPublished(),
 *                y en 'pageAssets' => ['js' => [..., Url::to('/assets/public-theme/js/testimonios-carousel.js')]]
 *                (los estilos .testimonios* estan en public-theme/css/style.css, que ya cargan todas las paginas)
 *   Vista:       include dirname(__DIR__) . '/components/testimonios.php';
 *
 * Variables a definir antes de incluir:
 *   $testimonios         list<array{id:int, nombre:string, contexto:string, detalle:string}>  Vacio: no se muestra nada.
 *   $testimoniosTitulo   string  Titulo (h2).                       Default: 'Lo que dicen nuestros clientes'
 *   $testimoniosEyebrow  string  Texto pequeño sobre el titulo.     Default: 'Testimonios'
 *   $testimoniosBajada   string  Texto corto bajo el titulo.        Default: 'Historias reales de quienes confiaron en su proceso.'
 *   $testimoniosId       string  Prefijo de ids (unico por pagina).  Default: 'testimonios'
 *
 * Tarjeta: la primera frase del testimonio sale destacada (la separa este componente,
 * la base guarda el texto entero), comilla en una medalla dorada y, con mas de un
 * testimonio, dos cartas de fondo que asoman detras (pila de cartas).
 *
 * Sin autoplay. Con JS (public-theme/js/testimonios-carousel.js): las tarjetas se
 * apilan en un grid (todas con el alto de la mas larga), la de adelante sale y la
 * siguiente sube, flechas, dots, contador, teclado (← → Inicio Fin), deslizamiento con
 * el dedo y "Leer mas" a partir de 6 lineas. Sin JS: los testimonios uno debajo del otro.
 */
$testimonios = is_array($testimonios ?? null) ? array_values($testimonios) : [];
$testimoniosTitulo = (string) ($testimoniosTitulo ?? 'Lo que dicen nuestros clientes');
$testimoniosEyebrow = (string) ($testimoniosEyebrow ?? 'Testimonios');
$testimoniosBajada = (string) ($testimoniosBajada ?? 'Historias reales de quienes confiaron en su proceso.');
$testimoniosId = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($testimoniosId ?? 'testimonios'))) ?: 'testimonios';

if ($testimonios === []) {
    return;
}

$tTotal = count($testimonios);
$tEsc = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$tPad = static fn (int $n): string => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
$tId = $tEsc($testimoniosId);

/*
 * Separa la frase destacada del resto: hasta el primer . ! ? … (o salto de linea)
 * seguido de espacio o del final, si esta entre 20 y 160 caracteres (asi "Dr." no
 * corta). Sin ese punto y con mas de 160 caracteres, corta en el ultimo espacio
 * antes del tope. Un texto corto de una sola frase queda entero como destacado.
 *
 * @return array{0:string, 1:string} [destacado, resto]
 */
$tSplitLead = static function (string $texto): array {
    $min = 20;
    $max = 160;
    $texto = trim($texto);
    if (preg_match_all('/[.!?…]+["”»)]*(?=\s|$)|\n/u', $texto, $m, PREG_OFFSET_CAPTURE)) {
        foreach ($m[0] as [$marca, $byte]) {
            $fin = mb_strlen(substr($texto, 0, $byte + strlen($marca)));
            if ($fin > $max) {
                break;
            }
            if ($fin >= $min) {
                return [rtrim(mb_substr($texto, 0, $fin)), ltrim(mb_substr($texto, $fin))];
            }
        }
    }
    if (mb_strlen($texto) <= $max) {
        return [$texto, ''];
    }
    $corte = mb_strrpos(mb_substr($texto, 0, $max), ' ');
    $corte = $corte !== false && $corte >= $min ? $corte : $max;

    return [rtrim(mb_substr($texto, 0, $corte)), ltrim(mb_substr($texto, $corte))];
};
?>
<section class="section testimonios" id="<?= $tId ?>" aria-labelledby="<?= $tId ?>-title" data-testimonios>
    <div class="container testimonios-layout">
        <div class="testimonios-intro">
            <?php if ($testimoniosEyebrow !== ''): ?>
                <span class="eyebrow"><?= $tEsc($testimoniosEyebrow) ?></span>
            <?php endif; ?>
            <h2 id="<?= $tId ?>-title" class="testimonios-title"><?= $tEsc($testimoniosTitulo) ?></h2>
            <?php if ($testimoniosBajada !== ''): ?>
                <p class="testimonios-bajada"><?= $tEsc($testimoniosBajada) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($tTotal > 1): ?>
            <div class="testimonios-controls" data-testimonios-controls hidden>
                <button type="button" class="testimonios-nav" data-testimonios-prev
                        aria-controls="<?= $tId ?>-track" aria-label="Testimonio anterior">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <p class="testimonios-counter" aria-hidden="true"><span data-testimonios-current>01</span><span class="testimonios-counter-sep">/</span><?= $tPad($tTotal) ?></p>
                <button type="button" class="testimonios-nav" data-testimonios-next
                        aria-controls="<?= $tId ?>-track" aria-label="Testimonio siguiente">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        <?php endif; ?>

        <div class="testimonios-stage">
            <div class="testimonios-track<?= $tTotal > 1 ? ' testimonios-track--stack' : '' ?>" id="<?= $tId ?>-track" data-testimonios-track
                 <?php if ($tTotal > 1): ?>tabindex="0" aria-label="<?= $tEsc($testimoniosTitulo) ?> (usa las flechas del teclado para cambiar)"<?php endif; ?>>
                <?php foreach ($testimonios as $i => $t): ?>
                    <?php
                    $n = $i + 1;
                    $nombre = trim((string) ($t['nombre'] ?? ''));
                    $contexto = trim((string) ($t['contexto'] ?? ''));
                    $inicial = $nombre !== '' ? mb_strtoupper(mb_substr($nombre, 0, 1)) : '“';
                    [$destacado, $resto] = $tSplitLead((string) ($t['detalle'] ?? ''));
                    ?>
                    <div class="testimonios-slide<?= $n === 1 ? ' is-active' : '' ?>" id="<?= $tId ?>-<?= $n ?>" data-testimonios-slide
                         role="group" aria-roledescription="testimonio" aria-label="<?= $n ?> de <?= $tTotal ?>">
                        <figure class="testimonios-card">
                            <span class="testimonios-medal" aria-hidden="true">
                                <svg viewBox="0 0 48 36" focusable="false"><path d="M0 36V22C0 9.6 6.4 2.3 19.2 0l2 5.2C14 7.2 10.6 11.2 10.2 17H19v19H0zm27 0V22C27 9.6 33.4 2.3 46.2 0l1.8 5.2C41 7.2 37.6 11.2 37.2 17H46v19H27z" fill="currentColor"/></svg>
                            </span>
                            <blockquote class="testimonios-text">
                                <p id="<?= $tId ?>-text-<?= $n ?>" data-testimonios-text><span class="testimonios-lead"><?= nl2br($tEsc($destacado), false) ?></span><?php if ($resto !== ''): ?> <span class="testimonios-rest"><?= nl2br($tEsc($resto), false) ?></span><?php endif; ?></p>
                            </blockquote>
                            <button type="button" class="testimonios-more" data-testimonios-more
                                    aria-expanded="false" aria-controls="<?= $tId ?>-text-<?= $n ?>" hidden>Leer más</button>
                            <figcaption class="testimonios-author">
                                <span class="testimonios-monogram" aria-hidden="true"><?= $tEsc($inicial) ?></span>
                                <span class="testimonios-author-text">
                                    <?php if ($nombre !== ''): ?>
                                        <span class="testimonios-name"><?= $tEsc($nombre) ?></span>
                                    <?php endif; ?>
                                    <?php if ($contexto !== ''): ?>
                                        <span class="testimonios-context"><?= $tEsc($contexto) ?></span>
                                    <?php endif; ?>
                                </span>
                            </figcaption>
                        </figure>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($tTotal > 1): ?>
            <div class="testimonios-dots" role="group" aria-label="Elegir testimonio" data-testimonios-dots hidden>
                <?php for ($n = 1; $n <= $tTotal; $n++): ?>
                    <button type="button" class="testimonios-dot" data-testimonios-dot="<?= $n - 1 ?>"
                            aria-controls="<?= $tId ?>-<?= $n ?>"
                            aria-label="Testimonio <?= $n ?> de <?= $tTotal ?>"<?= $n === 1 ? ' aria-current="true"' : '' ?>></button>
                <?php endfor; ?>
            </div>
            <p class="sr-only" aria-live="polite" aria-atomic="true" data-testimonios-status></p>
        <?php endif; ?>
    </div>
</section>
