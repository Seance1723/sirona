import { ScrollTrigger } from 'gsap/ScrollTrigger';

export function initAnimateCss(el) {
  const animation = el.dataset.animate;
  const allow = window?.allowAnimateCss !== false;
  if (!animation || !allow) return;

  ScrollTrigger.create({
    trigger: el,
    start: 'top 80%',
    once: true,
    onEnter: () => {
      const classes = animation.split(' ').map((cls) => `animate__${cls}`);
      el.classList.add('animate__animated', ...classes);
    },
  });
}