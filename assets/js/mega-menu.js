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

function closeAll(items) {
  items.forEach((item) => {
    const link = item.querySelector('a');
    const panel = item.querySelector('.fx-mega');
    link.setAttribute('aria-expanded', 'false');
    panel.classList.remove('is-open');
    panel.setAttribute('aria-hidden', 'true');
  });
}

export function initMegaMenu() {
  const items = document.querySelectorAll('.primary-navigation .menu-item-has-mega');
  if (!items.length) return;
  items.forEach((item) => {
    const link = item.querySelector('a');
    const panel = item.querySelector('.fx-mega');
    if (!link || !panel) return;
    link.setAttribute('aria-haspopup', 'true');
    link.setAttribute('aria-expanded', 'false');
    panel.setAttribute('aria-hidden', 'true');
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const expanded = link.getAttribute('aria-expanded') === 'true';
      closeAll(items);
      if (!expanded) {
        link.setAttribute('aria-expanded', 'true');
        panel.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        const firstLink = panel.querySelector('a, button');
        firstLink?.focus();
        trapFocus(panel, () => {
          closeAll(items);
          link.focus();
        });
      }
    });
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeAll(items);
    }
  });
}