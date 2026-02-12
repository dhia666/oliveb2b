document.addEventListener('click', (event) => {
  const tab = event.target.closest('.oliveb2b-tab');
  if (!tab) {
    return;
  }

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
});
