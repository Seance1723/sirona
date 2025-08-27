(function (wp) {
    const { createElement, render } = wp.element;

    function BuilderApp() {
        return createElement('div', { className: 'fx-hf-builder-app' }, 'Header/Footer Builder');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('fx-hf-builder');
        if (root) {
            render(createElement(BuilderApp), root);
        }
    });
})(window.wp);