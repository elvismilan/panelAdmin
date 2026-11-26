(function () {
'use strict';
var PALETTES = 6;
function store(key, value) {
try {
if (value === null) {
window.localStorage.removeItem(key);
} else {
window.localStorage.setItem(key, value);
}
} catch (e) {
}
}
function stored(key) {
try {
return window.localStorage.getItem(key);
} catch (e) {
return null;
}
}
function each(selector, callback, root) {
Array.prototype.forEach.call((root || document).querySelectorAll(selector), callback);
}
function applyPalette(number) {
var root = document.documentElement;
var valid = /^[1-9]\d*$/.test(String(number)) && Number(number) <= PALETTES;
if (valid && String(number) !== '1') {
root.style.setProperty('--pa-primary', 'var(--pa-palette-' + number + ')');
root.style.setProperty('--pa-secondary', 'var(--pa-palette-' + number + '-secondary)');
} else {
root.style.removeProperty('--pa-primary');
root.style.removeProperty('--pa-secondary');
}
each('[data-pa-palette]', function (button) {
button.setAttribute('aria-pressed', button.getAttribute('data-pa-palette') === String(valid ? number : '1') ? 'true' : 'false');
});
}
var TOGGLES = {
dark: { key: 'pa-dark', bodyClass: 'dark-only' },
glass: { key: 'pa-glass', bodyClass: 'glass-active' }
};
function applyToggle(name, enabled) {
document.body.classList.toggle(TOGGLES[name].bodyClass, enabled);
each('[data-pa-toggle="' + name + '"]', function (button) {
button.setAttribute('aria-pressed', enabled ? 'true' : 'false');
});
}
function followColorStylesheet() {
var link = document.getElementById('color');
if (!link || link.tagName !== 'LINK') {
return;
}
function apply() {
var match = /color-(\d+)\.css/.exec(link.getAttribute('href') || '');
var number = match && Number(match[1]) <= PALETTES ? match[1] : '1';
[['--pa-primary', 'primary', ''], ['--pa-secondary', 'secondary', '-secondary']].forEach(function (token) {
var color = stored(token[1]);
var valid = stored('color') && /^#[0-9a-f]{3,8}$/i.test(color || '');
document.documentElement.style.setProperty(token[0], valid ? color : 'var(--pa-palette-' + number + token[2] + ')');
});
}
apply();
new MutationObserver(apply).observe(link, { attributes: true, attributeFilter: ['href'] });
}
function initPreferences() {
applyPalette(stored('pa-palette') || '1');
followColorStylesheet();
Object.keys(TOGGLES).forEach(function (name) {
applyToggle(name, stored(TOGGLES[name].key) === 'on');
});
document.addEventListener('click', function (event) {
var palette = event.target.closest('[data-pa-palette]');
if (palette) {
event.preventDefault();
var number = palette.getAttribute('data-pa-palette');
store('pa-palette', number === '1' ? null : number);
applyPalette(number);
return;
}
if (event.target.closest('[data-pa-fullscreen]')) {
event.preventDefault();
if (document.fullscreenElement) {
document.exitFullscreen();
} else if (document.documentElement.requestFullscreen) {
document.documentElement.requestFullscreen();
}
return;
}
var toggle = event.target.closest('[data-pa-toggle]');
if (toggle && TOGGLES[toggle.getAttribute('data-pa-toggle')]) {
event.preventDefault();
var name = toggle.getAttribute('data-pa-toggle');
var enabled = !document.body.classList.contains(TOGGLES[name].bodyClass);
store(TOGGLES[name].key, enabled ? 'on' : 'off');
applyToggle(name, enabled);
}
});
}
function initPasswordToggles() {
document.addEventListener('click', function (event) {
var button = event.target.closest('[data-pa-password-toggle]');
if (!button) {
return;
}
var wrapper = button.closest('.pa-password');
var input = wrapper ? wrapper.querySelector('input') : null;
if (!input) {
return;
}
var show = input.type === 'password';
input.type = show ? 'text' : 'password';
button.setAttribute('aria-pressed', show ? 'true' : 'false');
button.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
var icon = button.querySelector('i');
if (icon) {
icon.className = show ? 'fa fa-eye-slash' : 'fa fa-eye';
}
});
}
function initUploads() {
each('[data-pa-upload]', function (wrapper) {
var input = wrapper.querySelector('input[type="file"]');
var preview = wrapper.querySelector('.pa-upload__preview');
var remove = wrapper.querySelector('[data-pa-upload-remove]');
if (!input || !preview) {
return;
}
var original = preview.innerHTML;
input.addEventListener('change', function () {
var file = input.files && input.files[0];
if (!file || !/^image\//.test(file.type)) {
preview.innerHTML = original;
return;
}
var reader = new FileReader();
reader.onload = function () {
var image = document.createElement('img');
image.alt = 'Vista previa';
image.src = String(reader.result);
preview.innerHTML = '';
preview.appendChild(image);
};
reader.readAsDataURL(file);
if (remove) {
remove.checked = false;
}
});
if (remove) {
remove.addEventListener('change', function () {
preview.classList.toggle('opacity-50', remove.checked);
});
}
});
}
function initIconPickers() {
each('[data-pa-icon-picker]', function (picker) {
var input = picker.querySelector('[data-pa-icon-input]');
var preview = picker.querySelector('[data-pa-icon-preview] i');
var filter = picker.querySelector('[data-pa-icon-filter]');
if (!input) {
return;
}
function select(value) {
input.value = value;
if (preview) {
preview.className = value;
}
each('[data-pa-icon]', function (option) {
option.setAttribute('aria-pressed', option.getAttribute('data-pa-icon') === value ? 'true' : 'false');
}, picker);
}
picker.addEventListener('click', function (event) {
var option = event.target.closest('[data-pa-icon]');
if (option) {
event.preventDefault();
select(option.getAttribute('data-pa-icon'));
}
});
input.addEventListener('input', function () {
select(input.value.trim());
});
if (filter) {
filter.addEventListener('input', function () {
var term = filter.value.trim().toLowerCase();
each('[data-pa-icon]', function (option) {
option.classList.toggle('pa-hidden', term !== '' && option.getAttribute('data-pa-icon').indexOf(term) === -1);
}, picker);
});
}
select(input.value.trim());
});
}
function initUserForm() {
var select = document.querySelector('[data-pa-user-person]');
if (!select) {
return;
}
var form = select.form;
var password = form.querySelector('[name="usu_password"]');
var confirm = form.querySelector('[name="confirm_password"]');
var help = form.querySelector('[data-pa-user-mode]');
function update() {
var option = select.options[select.selectedIndex];
var email = option ? (option.getAttribute('data-email') || '').trim() : '';
var invite = email !== '';
[password, confirm].forEach(function (input) {
if (!input) {
return;
}
if (invite) {
input.value = '';
}
input.disabled = invite;
input.required = !invite;
var group = input.closest('[data-pa-password-group]');
if (group) {
group.classList.toggle('pa-hidden', invite);
}
});
if (help) {
help.textContent = invite
? 'La persona tiene correo (' + email + '): se le enviará un enlace para que defina su contraseña.'
: 'La persona no tiene correo: escribe la contraseña del usuario.';
}
}
select.addEventListener('change', update);
update();
}
function initForms() {
document.addEventListener('submit', function (event) {
var message = event.target.getAttribute && event.target.getAttribute('data-pa-confirm');
if (message && !window.confirm(message)) {
event.preventDefault();
}
});
document.addEventListener('change', function (event) {
if (event.target.matches && event.target.matches('[data-pa-autosubmit]') && event.target.form) {
event.target.form.submit();
}
});
}
function initFlashes() {
each('[data-pa-dismiss]', function (alert) {
var delay = parseInt(alert.getAttribute('data-pa-dismiss'), 10);
if (!(delay > 0)) {
return;
}
window.setTimeout(function () {
alert.classList.remove('show');
window.setTimeout(function () {
if (alert.parentNode) {
alert.parentNode.removeChild(alert);
}
}, 350);
}, delay);
});
}
function init() {
if (!preferencesReady) {
initPreferences();
}
initPasswordToggles();
initUploads();
initIconPickers();
initUserForm();
initForms();
initFlashes();
}
var preferencesReady = false;
if (document.body) {
initPreferences();
preferencesReady = true;
}
if (document.readyState === 'loading') {
document.addEventListener('DOMContentLoaded', init);
} else {
init();
}
})();
