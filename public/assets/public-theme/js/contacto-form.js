/**
 * Envio del formulario de Contacto via fetch, sin recargar la pagina.
 * Espera un <form id="contactoForm" data-action="..." data-csrf="...">
 * con campos nombre, telefono (opcional), email, mensaje.
 *
 * Validacion 100% del lado servidor (sin atributos required/native): los
 * errores se muestran con un borde rojo por campo + el mensaje en
 * .form-status, sin usar setCustomValidity/reportValidity (evita el
 * tooltip nativo del navegador).
 *
 * El endpoint (POST /contacto/enviar) siempre responde JSON:
 *   { ok: true, message: "..." }
 *   { ok: false, error: "...", errors?: { campo: ["..."] } }
 */
(function () {
  'use strict';

  function stripTags(value) {
    return String(value || '').replace(/<[^>]*>/g, '');
  }

  function setFieldError(form, field) {
    var input = form.querySelector('[name="' + field + '"]');
    if (input) input.classList.add('is-invalid');
  }

  function clearFieldErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(function (el) {
      el.classList.remove('is-invalid');
    });
  }

  function resetRecaptcha() {
    if (window.grecaptcha && typeof window.grecaptcha.reset === 'function') {
      window.grecaptcha.reset();
    }
  }

  // Google Analytics 4: registra la conversion solo si gtag esta cargado (GA_MEASUREMENT_ID).
  function trackLead(formName) {
    if (typeof window.gtag === 'function') {
      window.gtag('event', 'generate_lead', { form_name: formName });
    }
  }

  function init() {
    var form = document.getElementById('contactoForm');
    if (!form) return;

    var status = form.querySelector('.form-status');
    var submitBtn = form.querySelector('button[type="submit"]');
    var action = form.dataset.action;
    var csrf = form.dataset.csrf || '';

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      clearFieldErrors(form);

      var body = new URLSearchParams(new FormData(form));
      body.set('_csrf_token', csrf);

      var originalBtnText = submitBtn ? submitBtn.textContent : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';
      }
      if (status) {
        status.textContent = 'Enviando tu mensaje...';
        status.dataset.state = 'sending';
      }

      fetch(action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: body,
        credentials: 'same-origin',
      })
        .then(function (response) {
          return response.json().then(function (data) {
            return { status: response.status, data: data };
          });
        })
        .then(function (result) {
          var data = result.data;

          if (data.ok) {
            if (status) {
              status.textContent = stripTags(data.message) || '¡Gracias! Te responderemos pronto.';
              status.dataset.state = 'ok';
            }
            form.reset();
            trackLead('contacto');
            return;
          }

          if (data.errors) {
            Object.keys(data.errors).forEach(function (field) {
              setFieldError(form, field);
            });
          }

          if (status) {
            status.textContent = stripTags(data.error) || 'No pudimos enviar tu mensaje. Intenta de nuevo.';
            status.dataset.state = 'error';
          }
        })
        .catch(function () {
          if (status) {
            status.textContent = 'No pudimos conectar con el servidor. Intenta de nuevo en unos minutos.';
            status.dataset.state = 'error';
          }
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
          }
          resetRecaptcha();
        });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
