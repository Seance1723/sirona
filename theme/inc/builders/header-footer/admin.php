<?php
/**
 * Admin UI for header/footer builder.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register builder page under the FortiveaX dashboard.
 */
function fx_hf_register_builder_page() {
    add_submenu_page(
        'fx-dashboard',
        __( 'Header/Footer Builder', 'fx' ),
        __( 'Header/Footer Builder', 'fx' ),
        'manage_options',
        'fx-hf-builder',
        'fx_hf_builder_page'
    );
}
add_action( 'admin_menu', 'fx_hf_register_builder_page' );

/**
 * Render the builder page.
 */
function fx_hf_builder_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap"><h1>' . esc_html__( 'Header/Footer Builder', 'fx' ) . '</h1><div id="fx-hf-builder"></div></div>';
}

/**
 * Enqueue builder assets.
 *
 * @param string $hook Current admin page hook.
 */
function fx_hf_builder_assets( $hook ) {
    if ( 'fx-dashboard_page_fx-hf-builder' !== $hook ) {
        return;
    }

    $asset_path = get_theme_file_path( 'assets/admin' );
    $asset_url  = get_theme_file_uri( 'assets/admin' );

    if ( file_exists( $asset_path . '/hf-builder.css' ) ) {
        wp_enqueue_style(
            'fx-hf-builder',
            $asset_url . '/hf-builder.css',
            array(),
            filemtime( $asset_path . '/hf-builder.css' )
        );
    }

    if ( file_exists( $asset_path . '/hf-builder.js' ) ) {
        wp_enqueue_script(
            'fx-hf-builder',
            $asset_url . '/hf-builder.js',
            array( 'wp-element', 'wp-components' ),
            filemtime( $asset_path . '/hf-builder.js' ),
            true
        );

        $data = array(
            'header'   => fx_hf_get_layouts( 'header' ),
            'footer'   => fx_hf_get_layouts( 'footer' ),
            'presets'  => fx_hf_default_presets(),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'restBase' => esc_url_raw( rest_url( 'fx/v1' ) ),
        );
        wp_localize_script( 'fx-hf-builder', 'fxBuilderData', $data );
    }
}
add_action( 'admin_enqueue_scripts', 'fx_hf_builder_assets' );