<?php

declare(strict_types=1);

use App\Models\TestimonioModel;

/**
 * Renderiza components/testimonios.php con las variables dadas.
 *
 * @param array<string, mixed> $vars
 */
function render_testimonios_component(array $vars): string
{
    extract($vars);
    ob_start();
    include dirname(__DIR__, 2) . '/app/Views/components/testimonios.php';

    return (string) ob_get_clean();
}

test('TestimonioModel::cleanPublished recorta, tipa y descarta textos vacios', function (): void {
    $items = TestimonioModel::cleanPublished([
        ['id' => '4', 'nombre' => '  Carla ', 'contexto' => ' Servicio A ', 'detalle' => ' Gracias. '],
        ['id' => 5, 'nombre' => 'Vacio', 'detalle' => '   '],
        ['id' => 6, 'detalle' => 'Sin nombre'],
    ]);
    assert_same([
        ['id' => 4, 'nombre' => 'Carla', 'contexto' => 'Servicio A', 'detalle' => 'Gracias.'],
        ['id' => 6, 'nombre' => '', 'contexto' => '', 'detalle' => 'Sin nombre'],
    ], $items);
    assert_same([], TestimonioModel::cleanPublished([]));
});

test('Componente testimonios: sin testimonios no imprime nada', function (): void {
    assert_same('', trim(render_testimonios_component(['testimonios' => []])));
});

test('Componente testimonios: con uno, sin dots ni flechas', function (): void {
    $html = render_testimonios_component(['testimonios' => [['id' => 1, 'nombre' => 'Ana', 'detalle' => 'Bien']]]);
    assert_true(str_contains($html, 'data-testimonios'));
    assert_false(str_contains($html, 'testimonios-dot"'));
    assert_false(str_contains($html, 'data-testimonios-prev'));
    assert_false(str_contains($html, 'data-testimonios-controls'));
    assert_false(str_contains($html, 'tabindex="0"'));
});

test('Componente testimonios: escapa el texto, respeta saltos y arma dots accesibles', function (): void {
    $html = render_testimonios_component([
        'testimonios' => [
            ['id' => 1, 'nombre' => 'Ana', 'detalle' => "Linea 1\n<script>alert(1)</script>"],
            ['id' => 2, 'nombre' => 'Luis', 'detalle' => 'Dos'],
            ['id' => 3, 'nombre' => 'Eva', 'detalle' => 'Tres'],
        ],
        'testimoniosId' => 'Otro Bloque!',
    ]);
    assert_false(str_contains($html, '<script>'));
    assert_true(str_contains($html, 'Linea 1<br>'));
    assert_true(str_contains($html, '&lt;script&gt;'));
    assert_same(3, substr_count($html, 'class="testimonios-dot"'));
    assert_same(1, substr_count($html, 'aria-current="true"'));
    assert_true(str_contains($html, 'aria-label="Testimonio 3 de 3"'));
    assert_true(str_contains($html, 'id="otrobloque-track"'));
    assert_true(str_contains($html, 'aria-live="polite"'));
});

test('Componente testimonios: monograma, contexto, bajada, contador y Leer mas', function (): void {
    $html = render_testimonios_component([
        'testimonios' => [
            ['id' => 1, 'nombre' => 'álvaro', 'contexto' => 'Servicio B', 'detalle' => 'Uno'],
            ['id' => 2, 'nombre' => '', 'contexto' => '', 'detalle' => 'Dos'],
        ],
    ]);
    assert_true(str_contains($html, 'class="testimonios-monogram" aria-hidden="true">Á<'));
    assert_true(str_contains($html, '<span class="testimonios-context">Servicio B</span>'));
    assert_same(1, substr_count($html, 'testimonios-context"'));
    assert_true(str_contains($html, 'Historias reales de quienes confiaron en su proceso.'));
    assert_true(str_contains($html, '<span data-testimonios-current>01</span><span class="testimonios-counter-sep">/</span>02'));
    assert_same(2, substr_count($html, 'aria-expanded="false" aria-controls="testimonios-text-'));
    assert_true(str_contains($html, 'id="testimonios-text-2"'));
    assert_same(1, substr_count($html, 'testimonios-slide is-active'));
});

test('Componente testimonios: la primera frase sale destacada y el resto aparte', function (): void {
    $html = render_testimonios_component(['testimonios' => [
        ['id' => 1, 'nombre' => 'Ana', 'detalle' => 'Me cambió la forma de verme. Hoy duermo mejor y entiendo mis miedos.'],
        ['id' => 2, 'nombre' => 'Luis', 'detalle' => 'Una sola frase corta sin punto final'],
        ['id' => 3, 'nombre' => 'Eva', 'detalle' => str_repeat('palabra ', 40) . 'fin sin punto'],
    ]]);
    assert_true(str_contains($html, '<span class="testimonios-lead">Me cambió la forma de verme.</span> <span class="testimonios-rest">Hoy duermo mejor y entiendo mis miedos.</span>'));
    // Una sola frase: todo es destacado, sin resto.
    assert_true(str_contains($html, '<span class="testimonios-lead">Una sola frase corta sin punto final</span></p>'));
    // Sin punto y largo: corta en un espacio antes de los 160 caracteres.
    assert_same(1, preg_match('/<span class="testimonios-lead">(.{1,160}?)<\/span> <span class="testimonios-rest">/u', $html, $m));
    assert_true(mb_strlen($m[1]) <= 160);
    // Un solo blockquote por testimonio (accesible) y la pila solo con mas de uno.
    assert_same(3, substr_count($html, '<blockquote'));
    assert_true(str_contains($html, 'testimonios-track--stack'));
    assert_false(str_contains(render_testimonios_component(['testimonios' => [['id' => 1, 'nombre' => 'Ana', 'detalle' => 'Bien']]]), 'testimonios-track--stack'));
});
