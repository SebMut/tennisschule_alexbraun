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
})();