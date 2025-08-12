import gsap from 'gsap';

export function initCounter(el) {
  const end = parseInt(el.dataset.counterEnd, 10) || 0;
  const start = parseInt(el.dataset.counterStart || 0, 10);
  const duration = parseFloat(el.dataset.counterDuration || 2);
  const obj = { value: start };

  gsap.to(obj, {
    value: end,
    duration,
    scrollTrigger: {
      trigger: el,
      start: 'top 80%',
      once: true,
    },
    onUpdate: () => {
      el.textContent = Math.floor(obj.value).toString();
    },
  });
}