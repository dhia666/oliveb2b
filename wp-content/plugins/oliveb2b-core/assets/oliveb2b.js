document.addEventListener('click', (event) => {
  const tab = event.target.closest('.oliveb2b-tab');
  if (tab) {
    const container = tab.closest('.oliveb2b-results');
    if (!container) {
      return;
    }

    const target = tab.dataset.target;
    container.querySelectorAll('.oliveb2b-tab').forEach((btn) => {
      btn.classList.toggle('is-active', btn === tab);
    });
    container.querySelectorAll('.oliveb2b-results-panel').forEach((panel) => {
      panel.classList.toggle('is-active', panel.dataset.panel === target);
    });
  }

  const geolocateButton = event.target.closest('.oliveb2b-geolocate');
  if (!geolocateButton) {
    return;
  }

  const form = geolocateButton.closest('form');
  if (!form || !navigator.geolocation) {
    geolocateButton.textContent = 'Location unavailable';
    return;
  }

  geolocateButton.disabled = true;
  geolocateButton.textContent = 'Locating...';

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const latInput = form.querySelector('input[name="user_lat"]');
      const lngInput = form.querySelector('input[name="user_lng"]');
      if (latInput && lngInput) {
        latInput.value = position.coords.latitude.toFixed(6);
        lngInput.value = position.coords.longitude.toFixed(6);
      }
      geolocateButton.textContent = 'Location ready';
      geolocateButton.disabled = false;
    },
    () => {
      geolocateButton.textContent = 'Location denied';
      geolocateButton.disabled = false;
    },
    { enableHighAccuracy: true, timeout: 10000 }
  );
});
