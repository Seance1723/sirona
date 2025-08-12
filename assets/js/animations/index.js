import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { initStagger } from './stagger';
import { initParallax } from './parallax';
import { initCounter } from './counter';
import { initTicker } from './ticker';
import { initAnimateCss } from './animateCss';
import { prefersReducedMotion } from '../utils';

gsap.registerPlugin(ScrollTrigger);

export function initAnimations() {
  if (prefersReducedMotion()) {
    document.querySelectorAll('[data-gsap]').forEach((el) => {
      if (el.dataset.counterEnd) {
        el.textContent = el.dataset.counterEnd;
      }
    });
    return;
  }

  document.querySelectorAll('[data-gsap]').forEach((el) => {
    switch (el.dataset.gsap) {
      case 'stagger':
        initStagger(el);
        break;
      case 'parallax':
        initParallax(el);
        break;
      case 'counter':
        initCounter(el);
        break;
      case 'ticker':
        initTicker(el);
        break;
      default:
        break;
    }
    initAnimateCss(el);
  });
}