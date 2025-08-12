import { initAnimations } from './animations';
import { initNavigation } from './navigation';
import 'animate.css';

console.log('FortiveaX theme loaded');

document.addEventListener('DOMContentLoaded', () => {
  initAnimations();
  initNavigation();
});