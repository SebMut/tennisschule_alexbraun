(() => {
  const body = document.body;
  const toggle = document.querySelector('.menu-toggle');
  const mobileMenu = document.getElementById('mobileMenu');
  const mobileOverlay = document.getElementById('mobileOverlay');
  const mobileClose = document.querySelector('.mobile-close');

  const setMenu = (open) => {
    if (!mobileMenu || !mobileOverlay || !toggle) return;
    mobileMenu.classList.toggle('open', open);
    mobileOverlay.classList.toggle('open', open);
    mobileMenu.setAttribute('aria-hidden', String(!open));
    toggle.setAttribute('aria-expanded', String(open));
    body.classList.toggle('menu-open', open);
  };

  toggle?.addEventListener('click', () => setMenu(true));
  mobileClose?.addEventListener('click', () => setMenu(false));
  mobileOverlay?.addEventListener('click', () => setMenu(false));
  mobileMenu?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => setMenu(false)));

  document.querySelectorAll('[data-modal]').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      const id = el.getAttribute('data-modal');
      document.getElementById(id)?.classList.add('open');
      body.classList.add('popup-open');
    });
  });

  document.querySelectorAll('.modal-close,.modal').forEach(el => {
    el.addEventListener('click', e => {
      if (el.classList.contains('modal') && e.target !== el) return;
      el.closest('.modal')?.classList.remove('open');
      body.classList.remove('popup-open');
    });
  });

  const trainingModal = document.getElementById('trainingModal');
  const trainingModalContent = document.getElementById('trainingModalContent');
  const trainingClose = trainingModal?.querySelector('.trainings-close');

  const closeTrainingModal = () => {
    if (!trainingModal || !trainingModalContent) return;
    trainingModal.classList.remove('active');
    trainingModal.setAttribute('aria-hidden','true');
    window.setTimeout(() => {
      trainingModalContent.innerHTML = '';
      trainingModalContent.classList.remove('visible');
      if (!document.querySelector('.wp-popup-overlay.open,.modal.open,.mobile-menu.open')) body.classList.remove('popup-open');
    }, 300);
  };

  document.querySelectorAll('.trainings-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      if (!trainingModal || !trainingModalContent) return;
      const formUrl = btn.getAttribute('data-training-url') || '';
      const errorMsg = btn.getAttribute('data-training-error') || 'Die Anmeldung ist aktuell nicht verfügbar.';
      trainingModal.classList.add('active');
      trainingModal.setAttribute('aria-hidden','false');
      body.classList.add('popup-open');
      if (formUrl) {
        const iframe = document.createElement('iframe');
        iframe.src = formUrl;
        iframe.frameBorder = '0';
        iframe.allowFullscreen = true;
        trainingModalContent.replaceChildren(iframe);
      } else {
        const error = document.createElement('div');
        error.className = 'trainings-error';
        error.textContent = errorMsg;
        trainingModalContent.replaceChildren(error);
      }
      window.setTimeout(() => trainingModalContent.classList.add('visible'), 50);
    });
  });
  trainingClose?.addEventListener('click', closeTrainingModal);
  trainingModal?.addEventListener('click', e => { if (e.target === trainingModal) closeTrainingModal(); });

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

  const winterPopup = document.getElementById('winterPopup');
  if (winterPopup) {
    const close = winterPopup.querySelector('.wp-popup-close');
    const storageKey = 'pum-1495-closed-at';
    const eighteenHours = 18 * 60 * 60 * 1000;
    let mayShow = true;

    try {
      const closedAt = Number(localStorage.getItem(storageKey) || 0);
      if (closedAt && Date.now() - closedAt < eighteenHours) mayShow = false;
    } catch (_) {}

    const closePopup = () => {
      winterPopup.classList.remove('open');
      winterPopup.setAttribute('aria-hidden','true');
      body.classList.remove('popup-open');
      try { localStorage.setItem(storageKey, String(Date.now())); } catch (_) {}
    };

    close?.addEventListener('click', closePopup);
    winterPopup.addEventListener('click', e => {
      if (e.target === winterPopup) closePopup();
    });

    if (mayShow) {
      if (close) close.style.visibility = 'hidden';
      window.setTimeout(() => {
        winterPopup.classList.add('open');
        winterPopup.setAttribute('aria-hidden','false');
        body.classList.add('popup-open');
        window.setTimeout(() => { if (close) close.style.visibility = 'visible'; }, 2000);
      }, 1000);
    }
  }

  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    setMenu(false);
    document.querySelectorAll('.modal.open').forEach(modal => modal.classList.remove('open'));
    if (trainingModal?.classList.contains('active')) closeTrainingModal();
    const winterPopup = document.getElementById('winterPopup');
    if (winterPopup?.classList.contains('open')) {
      winterPopup.querySelector('.wp-popup-close')?.click();
    }
    body.classList.remove('popup-open');
  });
})();