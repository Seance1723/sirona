import gsap from 'gsap';

export function initParallax(el) {
  const y = parseFloat(el.dataset.parallaxY) || 100;
  gsap.to(el, {
    y,
    ease: 'none',
    scrollTrigger: {
      trigger: el,
      start: 'top bottom',
      scrub: true,
    },
  });
}