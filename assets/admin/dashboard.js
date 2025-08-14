(function (wp) {
    const { createElement, render, useState, useEffect } = wp.element;
    const { apiFetch } = wp;

    function DashboardApp() {
        const [status, setStatus] = useState(null);
        const [changelog, setChangelog] = useState('');
        const [plugins, setPlugins] = useState([]);

        useEffect(() => {
            apiFetch({ path: fxDashboard.restBase + '/status', headers: { 'X-WP-Nonce': fxDashboard.nonce } }).then(setStatus);
            apiFetch({ path: fxDashboard.restBase + '/changelog', headers: { 'X-WP-Nonce': fxDashboard.nonce } }).then(res => setChangelog(res.changelog));
            apiFetch({ path: fxDashboard.restBase + '/plugins', headers: { 'X-WP-Nonce': fxDashboard.nonce } }).then(res => setPlugins(res.plugins));
        }, []);

        return createElement(
            'div',
            { className: 'fx-dashboard-app' },
            createElement('h2', null, 'System Status'),
            status && createElement(
                'ul',
                null,
                createElement('li', null, 'PHP Version: ' + status.php),
                createElement('li', null, 'WP Version: ' + status.wp),
                createElement('li', null, 'Memory Limit: ' + status.memory),
                createElement('li', null, 'Max Upload: ' + status.upload),
                createElement('li', null, 'Uploads Perms: ' + status.uploads_perm),
                createElement('li', null, 'Theme Perms: ' + status.theme_perm)
            ),
            createElement('h2', null, 'Changelog'),
            createElement('pre', null, changelog || 'No changelog available.'),
            createElement('h2', null, 'Plugins'),
            createElement(
                'ul',
                null,
                plugins.map(plugin => {
                    const statusMap = {
                        active: 'Active',
                        inactive: 'Inactive',
                        missing: 'Not Installed',
                    };
                    const statusText = statusMap[plugin.status] || '';
                    return createElement(
                        'li',
                        { key: plugin.slug },
                        plugin.name,
                        ' - ',
                        statusText,
                        plugin.action && plugin.url
                            ? createElement(
                                  'a',
                                  { href: plugin.url, className: 'button button-primary', style: { marginLeft: '10px' } },
                                  plugin.action.charAt(0).toUpperCase() + plugin.action.slice(1)
                              )
                            : null
                    );
                })
            ),
            createElement('h2', null, 'Quick Links'),
            createElement(
                'ul',
                null,
                createElement('li', null, createElement('a', { href: fxDashboard.links.themeOptions }, 'Theme Options')),
                createElement('li', null, createElement('a', { href: fxDashboard.links.demoImport }, 'Demo Import')),
                createElement(
                    'li',
                    null,
                    createElement(
                        'a',
                        { href: fxDashboard.links.setupWizard },
                        fxDashboard.wizardComplete ? 'Re-run Wizard' : 'Setup Wizard'
                    )
                ),
                createElement('li', null, createElement('a', { href: fxDashboard.links.docs }, 'Documentation'))
            )
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('fx-dashboard');
        if (root) {
            render(createElement(DashboardApp), root);
        }
    });
})(window.wp);