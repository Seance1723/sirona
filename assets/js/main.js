import { initAnimations } from './animations';
import { initContactForm } from './contact';
import 'animate.css';

console.log('FortiveaX theme loaded');

document.addEventListener('DOMContentLoaded', () => {
  initAnimations();
  initContactForm();
});