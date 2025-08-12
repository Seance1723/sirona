import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export function initTicker(el) {
  const start = parseInt(el.dataset.tickerStart || 0, 10);
  const end = parseInt(el.dataset.tickerEnd || 100, 10);
  const duration = parseFloat(el.dataset.tickerDuration || 2);
  const obj = { value: start };

  const tween = gsap.fromTo(
    obj,
    { value: start },
    {
      value: end,
      duration,
      ease: 'none',
      repeat: -1,
      onUpdate: () => {
        el.textContent = Math.floor(obj.value).toString();
      },
    }
  );

  ScrollTrigger.create({
    trigger: el,
    start: 'top 80%',
    onEnter: () => tween.play(),
    onLeaveBack: () => tween.pause(0),
  });
}