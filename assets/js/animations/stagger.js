import gsap from 'gsap';

export function initStagger(container) {
  const items = container.querySelectorAll('[data-gsap-target]');
  const stagger = parseFloat(container.dataset.stagger) || 0.1;
  const y = parseFloat(container.dataset.y) || 20;
  const duration = parseFloat(container.dataset.duration) || 0.6;

  gsap.from(items.length ? items : container.children, {
    y,
    opacity: 0,
    stagger,
    duration,
    scrollTrigger: {
      trigger: container,
      start: 'top 80%',
      once: true,
    },
  });
}