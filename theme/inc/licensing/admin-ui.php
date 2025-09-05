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
add_action( 'admin_menu', 'fx_register_license_page', 11 );

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
                    $status['theme_version']  = wp_get_theme()->get( 'Version' );
                    $status['required_theme'] = get_option( 'fortiveax_required_theme_version', '' );
                    $status['core_requires_theme_update'] = (bool) get_option( 'fortiveax_core_requires_theme_update', 0 );
                    if ( function_exists( 'fx_integrity_get_diff' ) ) {
                        $status['integrity'] = fx_integrity_get_diff();
                    }
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
                    $status['theme_version']  = wp_get_theme()->get( 'Version' );
                    $status['required_theme'] = get_option( 'fortiveax_required_theme_version', '' );
                    $status['core_requires_theme_update'] = (bool) get_option( 'fortiveax_core_requires_theme_update', 0 );
                    if ( function_exists( 'fx_integrity_get_diff' ) ) {
                        $status['integrity'] = fx_integrity_get_diff();
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
                $status['has_core']       = function_exists( 'fx_core_present' ) ? (bool) fx_core_present() : false;
                $status['integrity_fail'] = (bool) get_option( 'fortiveax_integrity_fail', 0 ) || ! $status['has_core'];
                $status['theme_version']  = wp_get_theme()->get( 'Version' );
                $status['required_theme'] = get_option( 'fortiveax_required_theme_version', '' );
                $status['core_requires_theme_update'] = (bool) get_option( 'fortiveax_core_requires_theme_update', 0 );
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
                $status                   = fx_license()->status();
                $status['has_core']       = function_exists( 'fx_core_present' ) ? (bool) fx_core_present() : false;
                $status['integrity_fail'] = (bool) get_option( 'fortiveax_integrity_fail', 0 ) || ! $status['has_core'];
                $status['theme_version']  = wp_get_theme()->get( 'Version' );
                $status['required_theme'] = get_option( 'fortiveax_required_theme_version', '' );
                $status['core_requires_theme_update'] = (bool) get_option( 'fortiveax_core_requires_theme_update', 0 );
                if ( function_exists( 'fx_integrity_get_diff' ) ) {
                    $status['integrity'] = fx_integrity_get_diff();
                }
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
                $payload = <<<'MU'
<?php
/**
 * FortiveaX Core (MU)
 */
if ( ! defined('ABSPATH') ) { exit; }
if ( ! function_exists('fx_core_present') ) {
    function fx_core_present() { return true; }
}
if ( ! function_exists('fx_core_run_checks') ) {
    function fx_core_run_checks() {
        $status = get_option('fortiveax_license_status', array());
        $status['last_check'] = current_time('mysql');
        $active = false;
        $grace  = false;
        $token  = get_option('fortiveax_license_token');
        if ( $token ) {
            if ( function_exists('fx_jwt_verify_rs256') && defined('FX_RSA_PUBLIC') ) {
                $claims = array(
                    'iss' => defined('FX_JWT_ISS') ? FX_JWT_ISS : 'fortiveax',
                    'aud' => defined('FX_JWT_AUD') ? FX_JWT_AUD : wp_parse_url( site_url(), PHP_URL_HOST ),
                );
                $verify = fx_jwt_verify_rs256( $token, FX_RSA_PUBLIC, $claims );
                if ( ! is_wp_error( $verify ) ) {
                    if ( is_array($verify) ) {
                        if ( ! empty( $verify['plan'] ) ) { $status['plan'] = $verify['plan']; }
                        if ( isset( $verify['exp'] ) && is_numeric( $verify['exp'] ) ) { $status['exp'] = intval($verify['exp']); }
                    }
                }
            }
        }
        // Determine active state with 7-day offline grace after exp.
        $now = time();
        if ( ! empty( $status['exp'] ) && is_numeric( $status['exp'] ) ) {
            $exp = intval( $status['exp'] );
            if ( $now <= ( $exp + 7 * DAY_IN_SECONDS ) ) {
                $active = true;
                $grace  = ( $now > $exp );
            } else {
                $active = false;
                $grace  = false;
            }
        } else {
            // If no exp known but token exists, assume active until next check.
            if ( $token ) { $active = true; }
        }
        $status['active'] = (bool) $active;
        $status['grace']  = (bool) $grace;
        update_option('fortiveax_license_status', $status);
    }
}
add_action('init', 'fx_core_run_checks');
MU;

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
                if ( function_exists( 'fx_integrity_get_diff' ) ) {
                    $status['integrity'] = fx_integrity_get_diff();
                }
                return rest_ensure_response( $status );
            },
        )
    );

    // Repair theme files from a local package if provided.
    register_rest_route(
        'fx/v1',
        '/integrity/repair',
        array(
            'methods'             => WP_REST_Server::EDITABLE,
            'permission_callback' => function () { return current_user_can( 'manage_options' ); },
            'callback'            => function () {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                $uploads   = wp_upload_dir();
                $pkg_dir   = trailingslashit( $uploads['basedir'] ) . 'fortiveax';
                $pkg_path  = trailingslashit( $pkg_dir ) . 'fortiveax-theme.zip';
                if ( ! file_exists( $pkg_path ) ) {
                    return new WP_Error( 'package_missing', sprintf( __( 'Upload a theme package to %s and retry.', 'fx' ), esc_html( str_replace( ABSPATH, '/', $pkg_path ) ) ) );
                }

                if ( ! wp_mkdir_p( $pkg_dir ) ) {
                    return new WP_Error( 'fs_error', __( 'Could not prepare temporary directory.', 'fx' ) );
                }
                if ( ! WP_Filesystem() ) {
                    return new WP_Error( 'fs_error', __( 'Filesystem initialization failed.', 'fx' ) );
                }
                global $wp_filesystem;

                $tmp = trailingslashit( $pkg_dir ) . '_tmp_' . wp_generate_password( 6, false );
                if ( ! wp_mkdir_p( $tmp ) ) {
                    return new WP_Error( 'fs_error', __( 'Could not create temp directory.', 'fx' ) );
                }

                $unzipped = unzip_file( $pkg_path, $tmp );
                if ( is_wp_error( $unzipped ) ) {
                    return $unzipped;
                }

                // Find extracted theme folder by looking for style.css containing Theme Name of current theme.
                $stylesheet = wp_get_theme()->get_stylesheet();
                $extracted  = '';
                $rii        = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $tmp, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::SELF_FIRST );
                foreach ( $rii as $file ) {
                    /** @var SplFileInfo $file */
                    if ( $file->isFile() && 'style.css' === $file->getFilename() ) {
                        $candidate = dirname( $file->getPathname() );
                        // A simple heuristic: parent directory name equals stylesheet slug.
                        if ( basename( $candidate ) === $stylesheet ) {
                            $extracted = $candidate;
                            break;
                        }
                    }
                }
                if ( empty( $extracted ) ) {
                    return new WP_Error( 'package_invalid', __( 'Could not locate theme folder inside the package.', 'fx' ) );
                }

                // Copy files into current theme directory.
                $dest = get_template_directory();
                $it   = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $extracted, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::SELF_FIRST );
                foreach ( $it as $fs ) {
                    /** @var SplFileInfo $fs */
                    $rel  = ltrim( str_replace( '\\', '/', substr( $fs->getPathname(), strlen( $extracted ) ) ), '/');
                    $dpth = trailingslashit( $dest ) . $rel;
                    if ( $fs->isDir() ) {
                        $wp_filesystem->mkdir( $dpth );
                    } else {
                        $wp_filesystem->put_contents( $dpth, file_get_contents( $fs->getPathname() ), FS_CHMOD_FILE ); // phpcs:ignore WordPress.WP.AlternativeFunctions
                    }
                }

                // Cleanup temp.
                $wp_filesystem->rmdir( $tmp, true );

                // Refresh integrity state.
                if ( function_exists( 'fx_integrity_scan' ) ) {
                    fx_integrity_scan();
                }

                $status = fx_license()->status();
                if ( function_exists( 'fx_integrity_get_diff' ) ) {
                    $status['integrity'] = fx_integrity_get_diff();
                }
                return rest_ensure_response( $status );
            },
        )
    );
}
add_action( 'rest_api_init', 'fx_register_license_routes' );
