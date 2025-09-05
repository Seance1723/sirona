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
    $is_license_page = in_array(
        $hook,
        array(
            'fortiveax_page_fx-license',
            'toplevel_page_fx-license',
            'fx-dashboard_page_fx-license',
        ),
        true
    );

    // Also allow enqueuing within Theme Options when the License tab is active.
    $is_options_license_tab = (
        'fx-dashboard_page_fx-options' === $hook &&
        isset( $_GET['tab'] ) && 'license' === sanitize_key( wp_unslash( $_GET['tab'] ) )
    );

    if ( ! $is_license_page && ! $is_options_license_tab ) {
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
                'support' => fx_get_option( 'wl_support_url', '#' ),
                'docs'    => '#',
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
                    $status = fx_license()->status();
                    $status['has_core']       = function_exists( 'fx_core_present' ) ? (bool) fx_core_present() : false;
                    $status['integrity_fail'] = (bool) get_option( 'fortiveax_integrity_fail', 0 ) || ! $status['has_core'];
                    return rest_ensure_response( $status );
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
                    $status['has_core']       = function_exists( 'fx_core_present' ) ? (bool) fx_core_present() : false;
                    $status['integrity_fail'] = (bool) get_option( 'fortiveax_integrity_fail', 0 ) || ! $status['has_core'];
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
                $status['has_core']       = function_exists( 'fx_core_present' ) ? (bool) fx_core_present() : false;
                $status['integrity_fail'] = (bool) get_option( 'fortiveax_integrity_fail', 0 ) || ! $status['has_core'];
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
                $status                      = fx_license()->status();
                $status['has_core']          = function_exists( 'fx_core_present' ) ? (bool) fx_core_present() : false;
                $status['integrity_fail']    = (bool) get_option( 'fortiveax_integrity_fail', 0 ) || ! $status['has_core'];
                return rest_ensure_response( $status );
            },
        )
    );

    // Repair MU plugin/core files.
    register_rest_route(
        'fx/v1',
        '/license/repair',
        array(
            'methods'             => WP_REST_Server::EDITABLE,
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'callback'            => function () {
                // Only allow if license is active but core is missing.
                $status   = fx_license()->status();
                $has_core = function_exists( 'fx_core_present' ) ? (bool) fx_core_present() : false;
                if ( empty( $status['active'] ) ) {
                    return new WP_Error( 'license_inactive', __( 'Activate your license first.', 'fx' ) );
                }

                // Prepare MU plugin path.
                $mu_dir  = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
                $mu_file = trailingslashit( $mu_dir ) . 'fortiveax-core.php';

                if ( ! is_dir( $mu_dir ) ) {
                    if ( ! wp_mkdir_p( $mu_dir ) ) {
                        return new WP_Error( 'fs_error', __( 'Could not create mu-plugins directory.', 'fx' ) );
                    }
                }

                // Initialize WP_Filesystem.
                require_once ABSPATH . 'wp-admin/includes/file.php';
                $creds = request_filesystem_credentials( admin_url() );
                if ( false === $creds ) {
                    // If creds are needed, WP will prompt via screen; for REST, attempt direct init.
                }
                if ( ! WP_Filesystem() ) {
                    return new WP_Error( 'fs_error', __( 'Filesystem initialization failed.', 'fx' ) );
                }

                global $wp_filesystem;

                // Minimal MU plugin payload to restore core presence and run checks.
                $payload = "<?php\n/**\n * FortiveaX Core (MU)\n */\nif ( ! defined('ABSPATH') ) { exit; }\nif ( ! function_exists('fx_core_present') ) {\n    function fx_core_present() { return true; }\n}\nif ( ! function_exists('fx_core_run_checks') ) {\n    function fx_core_run_checks() {\n        $status = get_option('fortiveax_license_status', array());\n        $status['last_check'] = current_time('mysql');\n        $active = false;\n        $grace  = false;\n        $token  = get_option('fortiveax_license_token');\n        if ( $token ) {\n            if ( function_exists('fx_jwt_verify_rs256') && defined('FX_RSA_PUBLIC') ) {\n                $claims = array(\n                    'iss' => defined('FX_JWT_ISS') ? FX_JWT_ISS : 'fortiveax',\n                    'aud' => defined('FX_JWT_AUD') ? FX_JWT_AUD : wp_parse_url( site_url(), PHP_URL_HOST ),\n                );\n                $verify = fx_jwt_verify_rs256( $token, FX_RSA_PUBLIC, $claims );\n                if ( ! is_wp_error( $verify ) ) {\n                    if ( is_array($verify) ) {\n                        if ( ! empty( $verify['plan'] ) ) { $status['plan'] = $verify['plan']; }\n                        if ( isset( $verify['exp'] ) && is_numeric( $verify['exp'] ) ) { $status['exp'] = intval($verify['exp']); }\n                    }\n                }\n            }\n        }\n        // Determine active state with 7-day offline grace after exp.
        $now = time();\n        if ( ! empty( $status['exp'] ) && is_numeric( $status['exp'] ) ) {\n            $exp = intval( $status['exp'] );\n            if ( $now <= ( $exp + 7 * DAY_IN_SECONDS ) ) {\n                $active = true;\n                $grace  = ( $now > $exp );\n            } else {\n                $active = false;\n                $grace  = false;\n            }\n        } else {\n            // If no exp known but token exists, assume active until next check.
            if ( $token ) { $active = true; }\n        }\n        $status['active'] = (bool) $active;\n        $status['grace']  = (bool) $grace;\n        update_option('fortiveax_license_status', $status);\n    }\n}\nadd_action('init', 'fx_core_run_checks');\n";

                if ( ! $wp_filesystem->put_contents( $mu_file, $payload, FS_CHMOD_FILE ) ) {
                    return new WP_Error( 'fs_error', __( 'Could not write core file.', 'fx' ) );
                }

                // Clear integrity flag and load core in current request.
                update_option( 'fortiveax_integrity_fail', 0 );
                if ( ! $has_core && file_exists( $mu_file ) ) {
                    include_once $mu_file;
                }
                if ( function_exists( 'fx_core_run_checks' ) ) {
                    fx_core_run_checks();
                }

                $status                   = fx_license()->status();
                $status['has_core']       = function_exists( 'fx_core_present' ) ? (bool) fx_core_present() : true;
                $status['integrity_fail'] = (bool) get_option( 'fortiveax_integrity_fail', 0 ) || ! $status['has_core'];
                return rest_ensure_response( $status );
            },
        )
    );
}
add_action( 'rest_api_init', 'fx_register_license_routes' );
