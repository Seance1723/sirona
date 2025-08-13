(function (wp) {
    const { createElement, render, useState } = wp.element;
    const { apiFetch } = wp;

    const steps = [
        { key: 'plugins', label: 'Install Plugins' },
        { key: 'import', label: 'Import Demo' },
        { key: 'setup', label: 'Configure Site' },
    ];

    function Step({ step, label, status, onRun }) {
        return createElement(
            'div',
            { className: 'fortiveax-wizard-step' },
            createElement('h3', null, label),
            status === 'done'
                ? createElement('span', { className: 'status done' }, 'Done')
                : createElement(
                      'button',
                      {
                          className: 'button button-primary',
                          disabled: status === 'running',
                          onClick: onRun,
                      },
                      status === 'running' ? 'Running...' : 'Run'
                  )
        );
    }

    function WizardApp() {
        const [state, setState] = useState({});

        function runStep(step) {
            setState({ ...state, [step]: 'running' });
            apiFetch({
                path: fortiveaxWizard.restBase + '/wizard/' + step,
                method: 'POST',
                headers: { 'X-WP-Nonce': fortiveaxWizard.nonce },
            })
                .then(() => setState((s) => ({ ...s, [step]: 'done' })))
                .catch(() => setState((s) => ({ ...s, [step]: 'error' })));
        }

        const allDone = steps.every((s) => state[s.key] === 'done');

        return createElement(
            'div',
            { className: 'fortiveax-wizard-app' },
            steps.map((s) =>
                createElement(Step, {
                    key: s.key,
                    step: s.key,
                    label: s.label,
                    status: state[s.key],
                    onRun: () => runStep(s.key),
                })
            ),
            allDone && createElement('p', { className: 'wizard-complete' }, 'Setup complete.')
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('fortiveax-setup-wizard');
        if (root) {
            render(createElement(WizardApp), root);
        }
    });
})(window.wp);