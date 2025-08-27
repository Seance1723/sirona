<?php
/**
 * License admin UI.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the license page under the FortiveaX menu.
 */
function fx_register_license_page() {
    add_submenu_page(
        'fx-dashboard',
        __( 'License', 'fx' ),
        __( 'License', 'fx' ),
        'manage_options',
        'fx-license',
        'fx_render_license_page'
    );
}
add_action( 'admin_menu', 'fx_register_license_page' );

/**
 * Render the license page container.
 */
function fx_render_license_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap"><h1>' . esc_html__( 'License', 'fx' ) . '</h1><div id="fx-license-app"></div></div>';
}

/**
 * Enqueue assets for the license page.
 *
 * @param string $hook Admin hook.
 */
function fx_license_assets( $hook ) {
    if ( 'fortiveax_page_fx-license' !== $hook && 'toplevel_page_fx-license' !== $hook && 'fx-dashboard_page_fx-license' !== $hook ) {
        return;
    }

    $asset_path = get_theme_file_path( 'assets/admin' );
    $asset_url  = get_theme_file_uri( 'assets/admin' );

    if ( file_exists( $asset_path . '/license.css' ) ) {
        wp_enqueue_style(
            'fx-license',
            $asset_url . '/license.css',
            array(),
            filemtime( $asset_path . '/license.css' )
        );
    }

    if ( file_exists( $asset_path . '/license.js' ) ) {
        wp_enqueue_script(
            'fx-license',
            $asset_url . '/license.js',
            array( 'wp-element', 'wp-api-fetch' ),
            filemtime( $asset_path . '/license.js' ),
            true
        );

        $data = array(
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'restBase' => esc_url_raw( rest_url( 'fx/v1' ) ),
            'links'    => array(
                'wizard'  => admin_url( 'admin.php?page=fx-setup' ),
                'support' => '#',
            ),
        );

        wp_localize_script( 'fx-license', 'fxLicense', $data );
    }
}
add_action( 'admin_enqueue_scripts', 'fx_license_assets' );

/**
 * Register REST routes for licensing.
 */
function fx_register_license_routes() {
    register_rest_route(
        'fx/v1',
        '/license',
        array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
                'callback'            => function () {
                    return rest_ensure_response( fx_license()->status() );
                },
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
                'callback'            => function ( $request ) {
                    $creds  = fx_license_sanitize_creds( $request->get_params() );
                    $status = fx_license()->activate( $creds );
                    if ( is_wp_error( $status ) ) {
                        return $status;
                    }
                    return rest_ensure_response( $status );
                },
            ),
        )
    );

    register_rest_route(
        'fx/v1',
        '/license/deactivate',
        array(
            'methods'             => WP_REST_Server::EDITABLE,
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'callback'            => function () {
                $status = fx_license()->deactivate();
                if ( is_wp_error( $status ) ) {
                    return $status;
                }
                return rest_ensure_response( $status );
            },
        )
    );

    register_rest_route(
        'fx/v1',
        '/license/recheck',
        array(
            'methods'             => WP_REST_Server::EDITABLE,
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'callback'            => function () {
                fx_license()->check_remote();
                return rest_ensure_response( fx_license()->status() );
            },
        )
    );
}
add_action( 'rest_api_init', 'fx_register_license_routes' );