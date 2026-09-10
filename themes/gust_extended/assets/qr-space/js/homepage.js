(function () {
  const page = document.querySelector('.qs-page');
  const toggle = document.querySelector('[data-qs-menu]');
  const nav = document.getElementById('qs-nav');
  if (!page || !toggle || !nav) return;

  toggle.addEventListener('click', function () {
    const open = page.classList.toggle('is-nav-open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  nav.querySelectorAll('.qs-nav__item--has-menu > a').forEach(function (link) {
    link.addEventListener('click', function (event) {
      if (window.matchMedia('(max-width: 960px)').matches) {
        event.preventDefault();
        link.parentElement.classList.toggle('is-open');
      }
    });
  });

  document.addEventListener('click', function (event) {
    if (!page.contains(event.target)) return;
    if (!event.target.closest('.qs-header')) {
      page.classList.remove('is-nav-open');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
})();
