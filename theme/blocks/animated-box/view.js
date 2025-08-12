document.addEventListener('DOMContentLoaded', function () {
    if ( typeof gsap === 'undefined' ) {
        return;
    }
    document.querySelectorAll('.wp-block-fortiveax-animated-box[data-animation]').forEach(function(el){
        const type = el.dataset.animation;
        if (type === 'fadeIn') {
            gsap.from(el, { opacity: 0, duration: 1 });
        } else if (type === 'slideUp') {
            gsap.from(el, { y: 50, opacity: 0, duration: 1 });
        }
    });
});