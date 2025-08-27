<?php
/**
 * Admin dashboard page for FortiveaX.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the FortiveaX Dashboard page.
 */
function fx_register_dashboard_page() {
    $brand = fx_get_brand_name();
    add_menu_page(
        sprintf( __( '%s Dashboard', 'fx' ), $brand ),
        $brand,
        'manage_options',
        'fx-dashboard',
        'fx_render_dashboard_page'
    );
}
add_action( 'admin_menu', 'fx_register_dashboard_page' );

/**
 * Render the dashboard page.
 */
function fx_render_dashboard_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap"><h1>' . esc_html( get_admin_page_title() ) . '</h1><div id="fx-dashboard"></div></div>';
}

/**
 * Enqueue assets for the dashboard page.
 *
 * @param string $hook Current admin page hook.
 */
function fx_dashboard_assets( $hook ) {
    if ( 'toplevel_page_fx-dashboard' !== $hook ) {
        return;
    }

    $asset_path = get_theme_file_path( 'assets/admin' );
    $asset_url  = get_theme_file_uri( 'assets/admin' );

    if ( file_exists( $asset_path . '/dashboard.css' ) ) {
        wp_enqueue_style(
            'fx-dashboard',
            $asset_url . '/dashboard.css',
            array(),
            filemtime( $asset_path . '/dashboard.css' )
        );
    }

    if ( file_exists( $asset_path . '/dashboard.js' ) ) {
        wp_enqueue_script(
            'fx-dashboard',
            $asset_url . '/dashboard.js',
            array( 'wp-element', 'wp-components', 'wp-api-fetch' ),
            filemtime( $asset_path . '/dashboard.js' ),
            true
        );

        $data = array(
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'restBase' => esc_url_raw( rest_url( 'fx/v1' ) ),
            'links'    => array(
                'themeOptions' => admin_url( 'admin.php?page=fx-options' ),
                'demoImport'   => admin_url( 'admin.php?page=fx-demo-import' ),
                'setupWizard'  => admin_url( 'admin.php?page=fx-setup' ),
                'docs'         => '#',
            ),
            'wizardComplete' => (bool) get_option( 'fx_wizard_complete' ),
        );

        wp_localize_script( 'fx-dashboard', 'fxDashboard', $data );
    }
}
add_action( 'admin_enqueue_scripts', 'fx_dashboard_assets' );

/**
 * Redirect clean admin URLs to their respective pages.
 */
function fx_admin_pretty_url_redirects() {
    if ( ! is_admin() ) {
        return;
    }

    $path = trim( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
    $map  = array(
        'wp-admin/fx-demo-import' => 'fx-demo-import',
        'wp-admin/fx-setup'       => 'fx-setup',
    );

    if ( isset( $map[ $path ] ) ) {
        wp_safe_redirect( admin_url( 'admin.php?page=' . $map[ $path ] ) );
        exit;
    }
}
add_action( 'admin_init', 'fx_admin_pretty_url_redirects' );