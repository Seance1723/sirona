(function (wp) {
    const { createElement, render, useState, useEffect } = wp.element;
    const { apiFetch } = wp;

    if ( window.fxLicense ) {
        apiFetch.use( apiFetch.createNonceMiddleware( fxLicense.nonce ) );
    }

    function LicenseApp() {
        const [info, setInfo] = useState({});
        const [status, setStatus] = useState('idle');
        const [error, setError] = useState('');
        const [email, setEmail] = useState('');
        const [password, setPassword] = useState('');

        const fetchStatus = () => {
            apiFetch({ path: fxLicense.restBase + '/license' })
                .then((res) => setInfo(res || {}))
                .catch(() => {});
        };

        useEffect(fetchStatus, []);

        const activate = () => {
            setStatus('running');
            setError('');
            apiFetch({
                path: fxLicense.restBase + '/license',
                method: 'POST',
                data: { email, password },
            })
                .then((res) => {
                    setInfo(res || {});
                    setStatus('idle');
                    setEmail('');
                    setPassword('');
                })
                .catch((err) => {
                    setStatus('idle');
                    setError(err.message || 'Error');
                });
        };

        const deactivate = () => {
            setStatus('running');
            setError('');
            apiFetch({ path: fxLicense.restBase + '/license/deactivate', method: 'POST' })
                .then((res) => {
                    setInfo(res || {});
                    setStatus('idle');
                })
                .catch((err) => {
                    setStatus('idle');
                    setError(err.message || 'Error');
                });
        };

        const recheck = () => {
            setStatus('running');
            setError('');
            apiFetch({ path: fxLicense.restBase + '/license/recheck', method: 'POST' })
                .then((res) => {
                    setInfo(res || {});
                    setStatus('idle');
                })
                .catch((err) => {
                    setStatus('idle');
                    setError(err.message || 'Error');
                });
        };

        const showRepair = info.active && (info.integrity_fail || info.has_core === false);
        const showThemeUpdate = !!info.core_requires_theme_update;

        if ( info.active ) {
            return createElement(
                'div',
                { className: 'fx-license-screen' },
                info.plan && createElement('p', null, 'Plan: ' + info.plan),
                (info.exp ? createElement('p', null, 'Expires: ' + new Date(parseInt(info.exp, 10) * 1000).toLocaleString()) : null),
                info.last_check && createElement('p', null, 'Last Check: ' + info.last_check),
                (info.grace ? createElement('p', { className: 'notice inline notice-warning' }, 'License expired. Pro remains active during a 7-day grace period.') : null),
                showRepair && createElement('div', { className: 'notice inline notice-warning' },
                    createElement('p', null, 'Core files are missing or corrupted. Repair to restore verification.'),
                    createElement('button', {
                        className: 'button button-primary',
                        onClick: () => {
                            setStatus('running');
                            setError('');
                            apiFetch({ path: fxLicense.restBase + '/license/repair', method: 'POST' })
                                .then((res) => { setInfo(res || {}); setStatus('idle'); })
                                .catch((err) => { setStatus('idle'); setError(err.message || 'Error'); });
                        },
                        disabled: status === 'running'
                    }, status === 'running' ? 'Repairing...' : 'Repair Core')
                ),
                info.integrity && (info.integrity.changed?.length || info.integrity.missing?.length) ? createElement('div', { className: 'notice inline notice-error' },
                    createElement('p', null, 'Integrity drift detected.'),
                    createElement('ul', { style: { maxHeight: '140px', overflowY: 'auto' } },
                        ...(info.integrity.changed || []).slice(0, 5).map((f) => createElement('li', { key: 'c-'+f }, 'Changed: ' + f )),
                        ...(info.integrity.missing || []).slice(0, 5).map((f) => createElement('li', { key: 'm-'+f }, 'Missing: ' + f ))
                    ),
                    createElement('div', null,
                        createElement('button', {
                            className: 'button',
                            onClick: () => {
                                setStatus('running'); setError('');
                                apiFetch({ path: fxLicense.restBase + '/integrity/repair', method: 'POST' })
                                .then((res) => { setInfo(res || {}); setStatus('idle'); })
                                .catch((err) => { setStatus('idle'); setError(err.message || 'Error'); });
                            },
                            disabled: status === 'running'
                        }, status === 'running' ? 'Repairing…' : 'Repair Theme')
                    )
                ) : null,
                error && createElement('p', { className: 'fx-license-error' }, error),
                showThemeUpdate && createElement('div', { className: 'notice inline notice-warning' },
                    createElement('p', null,
                        'Core update requires a newer theme version',
                        (info.required_theme ? ' (min ' + info.required_theme + ')' : ''), '. ',
                        'Current: ', info.theme_version || 'unknown'
                    ),
                    createElement('button', {
                        className: 'button',
                        onClick: () => { setStatus('running'); setError(''); apiFetch({ path: fxLicense.restBase + '/integrity/repair', method: 'POST' })
                            .then((res) => { setInfo(res || {}); setStatus('idle'); })
                            .catch((err) => { setStatus('idle'); setError(err.message || 'Error'); }); },
                        disabled: status === 'running'
                    }, status === 'running' ? 'Updating…' : 'Update Theme')
                ),
                createElement(
                    'div',
                    { className: 'fx-license-actions' },
                    createElement(
                        'button',
                        { className: 'button', onClick: recheck, disabled: status === 'running' },
                        'Recheck'
                    ),
                    createElement(
                        'button',
                        { className: 'button', onClick: deactivate, disabled: status === 'running' },
                        'Deactivate'
                    )
                ),
                createElement(
                    'p',
                    { className: 'fx-license-links' },
                    createElement('a', { href: fxLicense.links.wizard }, 'Setup Wizard'),
                    ' | ',
                    createElement('a', { href: fxLicense.links.support }, 'Support'),
                    ' | ',
                    createElement('a', { href: fxLicense.links.docs }, 'Privacy & Licensing Docs')
                ),
                createElement('p', { className: 'privacy-note' }, 'We store only token, plan, exp, and last check to verify your license. No personal content is sent.')
            );
        }

        return createElement(
            'div',
            { className: 'fx-license-screen fx-license-form' },
            error && createElement('p', { className: 'fx-license-error' }, error),
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
                    onClick: activate,
                    disabled: status === 'running',
                },
                status === 'running' ? 'Activating...' : 'Activate'
            ),
            createElement(
                'p',
                { className: 'fx-license-links' },
                createElement('a', { href: fxLicense.links.wizard }, 'Setup Wizard'),
                ' | ',
                createElement('a', { href: fxLicense.links.support }, 'Support'),
                ' | ',
                createElement('a', { href: fxLicense.links.docs }, 'Privacy & Licensing Docs')
            ),
            createElement('p', { className: 'privacy-note' }, 'We store only token, plan, exp, and last check to verify your license. No personal content is sent.')
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('fx-license-app');
        if ( root ) {
            render(createElement(LicenseApp), root);
        }
    });
})(window.wp);
