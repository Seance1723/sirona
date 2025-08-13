<?php
/**
 * REST endpoints for the FortiveaX dashboard.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register dashboard REST routes.
 */
function fx_dashboard_rest_routes() {
    register_rest_route(
        'fx/v1',
        '/status',
        array(
            'methods'             => 'GET',
            'callback'            => 'fx_rest_get_status',
            'permission_callback' => 'fx_dashboard_rest_permission',
        )
    );

    register_rest_route(
        'fx/v1',
        '/changelog',
        array(
            'methods'             => 'GET',
            'callback'            => 'fx_rest_get_changelog',
            'permission_callback' => 'fx_dashboard_rest_permission',
        )
    );

    register_rest_route(
        'fx/v1',
        '/plugins',
        array(
            'methods'             => 'GET',
            'callback'            => 'fx_rest_get_plugins',
            'permission_callback' => 'fx_dashboard_rest_permission',
        )
    );
}
add_action( 'rest_api_init', 'fx_dashboard_rest_routes' );

/**
 * Permission check for dashboard routes.
 *
 * @return bool
 */
function fx_dashboard_rest_permission() {
    return current_user_can( 'manage_options' );
}

/**
 * System status endpoint.
 *
 * @return array
 */
function fx_rest_get_status() {
    $upload_dir = wp_get_upload_dir();

    return array(
        'php'          => phpversion(),
        'wp'           => get_bloginfo( 'version' ),
        'memory'       => ini_get( 'memory_limit' ),
        'upload'       => ini_get( 'upload_max_filesize' ),
        'uploads_perm' => substr( sprintf( '%o', @fileperms( $upload_dir['basedir'] ) ), -4 ),
        'theme_perm'   => substr( sprintf( '%o', @fileperms( get_stylesheet_directory() ) ), -4 ),
    );
}

/**
 * Changelog endpoint.
 *
 * @return array
 */
function fx_rest_get_changelog() {
    $file     = get_theme_file_path( '/CHANGELOG.md' );
    $contents = __( 'Changelog not available.', 'fx' );

    if ( file_exists( $file ) ) {
        $contents = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
    }

    return array(
        'changelog' => $contents,
    );
}

/**
 * Plugins endpoint using TGMPA.
 *
 * @return array
 */
function fx_rest_get_plugins() {
    if ( ! class_exists( 'TGM_Plugin_Activation' ) ) {
        return array( 'plugins' => array() );
    }

    $tgmpa   = TGM_Plugin_Activation::$instance;
    $plugins = array();

    foreach ( $tgmpa->plugins as $slug => $plugin ) {
        $installed = $tgmpa->is_plugin_installed( $slug );
        $active    = $tgmpa->is_plugin_active( $slug );
        $action    = '';
        $url       = '';

        if ( ! $installed ) {
            $action = 'install';
            $url    = wp_nonce_url(
                add_query_arg(
                    array(
                        'plugin'        => urlencode( $slug ),
                        'tgmpa-install' => 'install-plugin',
                    ),
                    $tgmpa->get_tgmpa_url()
                ),
                'tgmpa-install',
                'tgmpa-nonce'
            );
        } elseif ( ! $active && $tgmpa->can_plugin_activate( $slug ) ) {
            $action = 'activate';
            $url    = wp_nonce_url(
                add_query_arg(
                    array(
                        'plugin'         => urlencode( $slug ),
                        'tgmpa-activate' => 'activate-plugin',
                    ),
                    $tgmpa->get_tgmpa_url()
                ),
                'tgmpa-activate',
                'tgmpa-nonce'
            );
        }

        $plugins[] = array(
            'name'      => $plugin['name'],
            'slug'      => $slug,
            'required'  => ! empty( $plugin['required'] ),
            'installed' => $installed,
            'active'    => $active,
            'action'    => $action,
            'url'       => $url,
        );
    }

    return array(
        'plugins' => $plugins,
    );
}