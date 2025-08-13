function trapFocus(container, closeCallback) {
  const focusable = container.querySelectorAll(
    'a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])'
  );
  const first = focusable[0];
  const last = focusable[focusable.length - 1];
  container.addEventListener('keydown', (e) => {
    if (e.key === 'Tab') {
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    } else if (e.key === 'Escape') {
      closeCallback();
    }
  });
}

export function initNavigation() {
  const menuToggle = document.querySelector('.fx-menu-toggle');
  const mobileMenu = document.getElementById('fx-mobile-menu');
  const closeMenu = mobileMenu?.querySelector('.fx-close-menu');
  const searchToggle = document.querySelector('.fx-search-toggle');
  const searchModal = document.getElementById('fx-search-modal');
  const closeSearch = searchModal?.querySelector('.fx-close-search');

  function openMenu() {
    mobileMenu.classList.add('fx-is-open');
    mobileMenu.removeAttribute('aria-hidden');
    menuToggle.setAttribute('aria-expanded', 'true');
    const firstLink = mobileMenu.querySelector('a, button');
    firstLink?.focus();
    trapFocus(mobileMenu, closeMenuHandler);
  }
  function closeMenuHandler() {
    mobileMenu.classList.remove('fx-is-open');
    mobileMenu.setAttribute('aria-hidden', 'true');
    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.focus();
  }
  menuToggle?.addEventListener('click', () => {
    const expanded = menuToggle.getAttribute('aria-expanded') === 'true';
    if (expanded) {
      closeMenuHandler();
    } else {
      openMenu();
    }
  });
  closeMenu?.addEventListener('click', closeMenuHandler);

  function openSearch() {
    searchModal.classList.add('fx-is-open');
    searchModal.removeAttribute('aria-hidden');
    searchToggle.setAttribute('aria-expanded', 'true');
    const input = searchModal.querySelector('input');
    input?.focus();
    trapFocus(searchModal, closeSearchHandler);
  }
  function closeSearchHandler() {
    searchModal.classList.remove('fx-is-open');
    searchModal.setAttribute('aria-hidden', 'true');
    searchToggle.setAttribute('aria-expanded', 'false');
    searchToggle.focus();
  }
  searchToggle?.addEventListener('click', () => {
    const expanded = searchToggle.getAttribute('aria-expanded') === 'true';
    if (expanded) {
      closeSearchHandler();
    } else {
      openSearch();
    }
  });
  closeSearch?.addEventListener('click', closeSearchHandler);
}