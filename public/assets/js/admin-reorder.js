/**
 * Reordenamiento de filas por AJAX (subir/bajar) — generico para cualquier listado admin.
 *
 * Uso:
 *   <div data-reorder-container data-reorder-csrf="TOKEN">
 *     <tr data-reorder-id="5">
 *       ...
 *       <button class="js-reorder-btn" data-direction="up" data-reorder-url="/modulo/5/ordenar">
 *       <span data-orden-value>3</span>
 *       <button class="js-reorder-btn" data-direction="down" data-reorder-url="/modulo/5/ordenar">
 *
 * El endpoint debe responder JSON: { ok: true, moved: true, orden: { "5": 2, "7": 3 } }
 * Si el vecino no esta en la pagina actual (paginacion), se recarga la pagina.
 * Opcional: celdas [data-row-number] en las filas y data-reorder-start en el contenedor
 * muestran un numero correlativo que se recalcula segun el orden visible.
 */
(function () {
  'use strict';

  function findSiblingRow(row, direction) {
    var sibling = direction === 'up' ? row.previousElementSibling : row.nextElementSibling;
    while (sibling && !sibling.hasAttribute('data-reorder-id')) {
      sibling = direction === 'up' ? sibling.previousElementSibling : sibling.nextElementSibling;
    }
    return sibling;
  }

  function updateOrdenValues(container, ordenMap) {
    Object.keys(ordenMap).forEach(function (id) {
      var row = container.querySelector('[data-reorder-id="' + id + '"]');
      if (!row) return;
      var cell = row.querySelector('[data-orden-value]');
      if (cell) cell.textContent = ordenMap[id];
    });
  }

  function renumberRows(container) {
    var start = parseInt(container.dataset.reorderStart || '1', 10);
    if (isNaN(start)) start = 1;
    var rows = container.querySelectorAll('[data-reorder-id]');
    for (var i = 0; i < rows.length; i++) {
      var cell = rows[i].querySelector('[data-row-number]');
      if (cell) cell.textContent = String(start + i);
    }
  }

  function handleClick(container, event) {
    var btn = event.target.closest('.js-reorder-btn');
    if (!btn || btn.disabled) return;
    event.preventDefault();

    var row = btn.closest('[data-reorder-id]');
    var url = btn.dataset.reorderUrl;
    var direction = btn.dataset.direction;
    var csrf = container.dataset.reorderCsrf || '';
    if (!row || !url || !direction) return;

    var buttons = container.querySelectorAll('.js-reorder-btn');
    buttons.forEach(function (b) { b.disabled = true; });

    var body = new URLSearchParams();
    body.set('direction', direction);
    if (csrf) body.set('_csrf_token', csrf);

    fetch(url, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: body,
      credentials: 'same-origin'
    })
      .then(function (response) {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
      })
      .then(function (data) {
        if (!data.ok) throw new Error(data.error || 'Error');
        if (!data.moved) return;

        var sibling = findSiblingRow(row, direction);
        if (!sibling) {
          // El vecino esta en otra pagina del listado: recargar para reflejar el cambio.
          window.location.reload();
          return;
        }

        if (direction === 'up') {
          row.parentNode.insertBefore(row, sibling);
        } else {
          row.parentNode.insertBefore(sibling, row);
        }

        if (data.orden) updateOrdenValues(container, data.orden);
        renumberRows(container);
      })
      .catch(function (error) {
        console.error('Reorder error:', error);
        window.alert('No se pudo actualizar el orden. Intenta de nuevo.');
      })
      .finally(function () {
        buttons.forEach(function (b) { b.disabled = false; });
      });
  }

  function init() {
    document.querySelectorAll('[data-reorder-container]').forEach(function (container) {
      container.addEventListener('click', function (event) {
        handleClick(container, event);
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
