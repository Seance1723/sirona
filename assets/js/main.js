import { initAnimations } from './animations';
import { initNavigation } from './navigation';
import { initMegaMenu } from './mega-menu';
import 'animate.css';

console.log('FortiveaX theme loaded');

document.addEventListener('DOMContentLoaded', () => {
  initAnimations();
  initNavigation();
  initMegaMenu();
});