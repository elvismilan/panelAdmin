(function () {
  var SESSION_KEY = 'admin_geo_prompt_shown_v1';

  function documentAllowsGeolocation() {
    if (typeof window !== 'undefined' && window.isSecureContext === false) {
      return false;
    }

    if (typeof document === 'undefined') {
      return true;
    }

    var policy = document.permissionsPolicy || document.featurePolicy || null;
    if (!policy) {
      return true;
    }

    try {
      if (typeof policy.allowsFeature === 'function') {
        return policy.allowsFeature('geolocation');
      }
    } catch (e) {
      return true;
    }

    return true;
  }

  function canUseGeo() {
    return typeof navigator !== 'undefined' && !!navigator.geolocation && documentAllowsGeolocation();
  }

  function markShown() {
    try {
      sessionStorage.setItem(SESSION_KEY, '1');
    } catch (e) {
      // ignore storage errors
    }
  }

  function alreadyShown() {
    try {
      return sessionStorage.getItem(SESSION_KEY) === '1';
    } catch (e) {
      return false;
    }
  }

  function requestGeolocation() {
    navigator.geolocation.getCurrentPosition(
      function () {
        // permission granted and position available
      },
      function () {
        // permission denied or unavailable
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  }

  function askWithConfirm() {
    if (!canUseGeo()) {
      return;
    }

    if (alreadyShown()) {
      return;
    }

    markShown();

    var shouldRequest = window.confirm(
      'Para usar mapas y autocompletar ubicaciones en eventos, necesitamos acceso a tu ubicacion. ¿Deseas habilitarlo ahora?'
    );

    if (!shouldRequest) {
      return;
    }

    requestGeolocation();
  }

  function askForPermission() {
    if (!canUseGeo()) {
      return;
    }

    if (!navigator.permissions || typeof navigator.permissions.query !== 'function') {
      askWithConfirm();
      return;
    }

    navigator.permissions
      .query({ name: 'geolocation' })
      .then(function (result) {
        if (!result || result.state === 'granted' || result.state === 'denied') {
          return;
        }

        askWithConfirm();
      })
      .catch(function () {
        askWithConfirm();
      });
  }

  function init() {
    if (document.visibilityState === 'hidden') {
      return;
    }

    if (document.getElementById('event-location-map')) {
      return;
    }

    askForPermission();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
