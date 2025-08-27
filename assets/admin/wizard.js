(function (wp) {
    const { createElement, render, useState, useEffect } = wp.element;
    const { apiFetch } = wp;

    const steps = [
        { key: 'login', label: 'Sign In' },
        { key: 'plugins', label: 'Install Plugins' },
        { key: 'import', label: 'Import Demo' },
        { key: 'setup', label: 'Configure Site' },
    ];

    function LoginStep({ status, licensed, plan, error, onLogin, onLogout, onRecheck }) {
        const [email, setEmail] = useState('');
        const [password, setPassword] = useState('');

        if (licensed) {
            return createElement(
                'div',
                { className: 'fx-wizard-step' },
                createElement('h3', null, 'Sign In'),
                plan && createElement('p', null, 'Plan: ' + plan),
                createElement(
                    'div',
                    { className: 'fx-login-actions' },
                    createElement(
                        'button',
                        { className: 'button', onClick: onRecheck, disabled: status === 'running' },
                        'Recheck'
                    ),
                    createElement(
                        'button',
                        { className: 'button', onClick: onLogout, disabled: status === 'running' },
                        'Logout'
                    )
                )
            );
        }

        return createElement(
            'div',
            { className: 'fx-wizard-step fx-login-form' },
            createElement('h3', null, 'Sign In'),
            error && createElement('p', { className: 'fx-wizard-error' }, error),
            createElement('input', {
                type: 'email',
                placeholder: 'Email',
                value: email,
                onChange: (e) => setEmail(e.target.value),
            }),
            createElement('input', {
                type: 'password',
                placeholder: 'Password',
                value: password,
                onChange: (e) => setPassword(e.target.value),
            }),
            createElement(
                'button',
                {
                    className: 'button button-primary',
                    onClick: () => onLogin(email, password),
                    disabled: status === 'running',
                },
                status === 'running' ? 'Signing In...' : 'Sign In'
            ),
            createElement('p', { className: 'privacy-note' }, 'We respect your privacy.')
        );
    }

    function Step({ step, label, status, onRun, locked }) {
        return createElement(
            'div',
            { className: 'fx-wizard-step' },
            createElement('h3', null, label),
            locked
                ? createElement('span', { className: 'status locked' }, 'Locked')
                : status === 'done'
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
        const [license, setLicense] = useState({ active: false, plan: '' });
        const [error, setError] = useState('');

        function updateStatus() {
            apiFetch({
                path: fxWizard.restBase + '/wizard/login',
                method: 'POST',
                headers: { 'X-WP-Nonce': fxWizard.nonce },
                data: { action: 'status' },
            }).then((res) => {
                if (res && res.status) {
                    setLicense(res.status);
                }
            });
        }

        useEffect(() => {
            updateStatus();
        }, []);

        function handleLogin(email, password) {
            setState({ ...state, login: 'running' });
            setError('');
            apiFetch({
                path: fxWizard.restBase + '/wizard/login',
                method: 'POST',
                headers: { 'X-WP-Nonce': fxWizard.nonce },
                data: { action: 'login', email, password },
            })
                .then((res) => {
                    setState((s) => ({ ...s, login: 'done' }));
                    if (res && res.status) {
                        setLicense(res.status);
                    }
                })
                .catch((err) => {
                    setState((s) => ({ ...s, login: 'error' }));
                    setError(err.message || 'Login failed');
                });
        }

        function handleLogout() {
            setState({ ...state, login: 'running' });
            apiFetch({
                path: fxWizard.restBase + '/wizard/login',
                method: 'POST',
                headers: { 'X-WP-Nonce': fxWizard.nonce },
                data: { action: 'logout' },
            }).then((res) => {
                setState((s) => ({ ...s, login: '' }));
                if (res && res.status) {
                    setLicense(res.status);
                }
            });
        }

        function handleRecheck() {
            apiFetch({
                path: fxWizard.restBase + '/wizard/login',
                method: 'POST',
                headers: { 'X-WP-Nonce': fxWizard.nonce },
                data: { action: 'recheck' },
            }).then((res) => {
                if (res && res.status) {
                    setLicense(res.status);
                }
            });
        }
        function runStep(step) {
            setState({ ...state, [step]: 'running' });
            apiFetch({
                path: fxWizard.restBase + '/wizard/' + step,
                method: 'POST',
                headers: { 'X-WP-Nonce': fxWizard.nonce },
            })
                .then(() => setState((s) => ({ ...s, [step]: 'done' })))
                .catch(() => setState((s) => ({ ...s, [step]: 'error' })));
        }

        const allDone = steps.every((s) => {
            if (s.key === 'login') {
                return license.active;
            }
            if (s.key === 'import' && !license.active) {
                return true;
            }
            return state[s.key] === 'done';
        });

        return createElement(
            'div',
            { className: 'fx-wizard-app' },
            steps.map((s) => {
                if (s.key === 'login') {
                    return createElement(LoginStep, {
                        key: s.key,
                        status: state[s.key],
                        licensed: license.active,
                        plan: license.plan,
                        error,
                        onLogin: handleLogin,
                        onLogout: handleLogout,
                        onRecheck: handleRecheck,
                    });
                }
                const locked = s.key === 'import' && !license.active;
                return createElement(Step, {
                    key: s.key,
                    step: s.key,
                    label: s.label,
                    status: state[s.key],
                    onRun: () => runStep(s.key),
                locked,
                });
            }),
            allDone && createElement('p', { className: 'wizard-complete' }, 'Setup complete.')
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('fx-setup-wizard');
        if (root) {
            render(createElement(WizardApp), root);
        }
    });
})(window.wp);