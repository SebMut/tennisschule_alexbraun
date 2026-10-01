(() => {
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.main-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', String(open));
    });
  }
  document.querySelectorAll('[data-modal]').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      const id = el.getAttribute('data-modal');
      document.getElementById(id)?.classList.add('open');
    });
  });
  document.querySelectorAll('.modal-close,.modal').forEach(el => {
    el.addEventListener('click', e => {
      if (el.classList.contains('modal') && e.target !== el) return;
      el.closest('.modal')?.classList.remove('open');
    });
  });

  const loadMaps = () => {
    document.querySelectorAll('[data-map-src]').forEach(frame => {
      if (!frame.src) frame.src = frame.dataset.mapSrc;
      frame.hidden = false;
      frame.previousElementSibling?.remove();
    });
    try { localStorage.setItem('ts_maps_consent','yes'); } catch (_) {}
  };
  try {
    if (localStorage.getItem('ts_maps_consent') === 'yes') loadMaps();
  } catch (_) {}
  document.querySelectorAll('[data-load-map]').forEach(btn => btn.addEventListener('click', loadMaps));
})();