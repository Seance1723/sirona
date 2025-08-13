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
function fortiveax_register_dashboard_page() {
    $brand = fortiveax_get_brand_name();
    add_menu_page(
        sprintf( __( '%s Dashboard', 'fortiveax' ), $brand ),
        $brand,
        'manage_options',
        'fortiveax-dashboard',
        'fortiveax_render_dashboard_page'
    );
}
add_action( 'admin_menu', 'fortiveax_register_dashboard_page' );

/**
 * Render the dashboard page.
 */
function fortiveax_render_dashboard_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap"><h1>' . esc_html( get_admin_page_title() ) . '</h1><div id="fortiveax-dashboard"></div></div>';
}

/**
 * Enqueue assets for the dashboard page.
 *
 * @param string $hook Current admin page hook.
 */
function fortiveax_dashboard_assets( $hook ) {
    if ( 'toplevel_page_fortiveax-dashboard' !== $hook ) {
        return;
    }

    $asset_path = get_template_directory() . '/assets/admin';
    $asset_url  = get_template_directory_uri() . '/assets/admin';

    if ( file_exists( $asset_path . '/dashboard.css' ) ) {
        wp_enqueue_style(
            'fortiveax-dashboard',
            $asset_url . '/dashboard.css',
            array(),
            filemtime( $asset_path . '/dashboard.css' )
        );
    }

    if ( file_exists( $asset_path . '/dashboard.js' ) ) {
        wp_enqueue_script(
            'fortiveax-dashboard',
            $asset_url . '/dashboard.js',
            array( 'wp-element', 'wp-components', 'wp-api-fetch' ),
            filemtime( $asset_path . '/dashboard.js' ),
            true
        );

        $data = array(
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'restBase' => esc_url_raw( rest_url( 'fortiveax/v1' ) ),
            'links'    => array(
                'themeOptions' => admin_url( 'customize.php' ),
                'demoImport'   => admin_url( 'themes.php?page=one-click-demo-import' ),
                'setupWizard'  => admin_url( 'themes.php?page=fortiveax-setup' ),
                'docs'         => '#',
            ),
            'wizardComplete' => (bool) get_option( 'fortiveax_wizard_complete' ),
        );

        wp_localize_script( 'fortiveax-dashboard', 'fortiveaxDashboard', $data );
    }
}
add_action( 'admin_enqueue_scripts', 'fortiveax_dashboard_assets' );