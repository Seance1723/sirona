import { initAnimations } from './animations';
import { initNavigation } from './navigation';
import { initMegaMenu } from './mega-menu';
import { prefersReducedMotion } from './utils';
import 'animate.css';

document.addEventListener('DOMContentLoaded', () => {
  const loadAnimations = () => initAnimations();
  if (prefersReducedMotion()) {
    loadAnimations();
  } else {
    const elements = document.querySelectorAll('[data-gsap]');
    if (elements.length && 'IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries, obs) => {
        if (entries.some((e) => e.isIntersecting)) {
          loadAnimations();
          obs.disconnect();
        }
      });
      elements.forEach((el) => observer.observe(el));
    } else {
      loadAnimations();
    }
  }
  initNavigation();
  initMegaMenu();
});