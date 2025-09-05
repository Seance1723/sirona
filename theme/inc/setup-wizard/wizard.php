<?php
/**
 * Theme setup wizard for FortiveaX.
 *
 * Provides a guided setup that installs plugins, imports demo data,
 * and configures basic site options. Steps are exposed via REST
 * endpoints so the wizard can be re-run safely.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Setup Wizard page under the FortiveaX dashboard.
 */
function fx_register_setup_wizard_page() {
    add_submenu_page(
        'fx-dashboard',
        __( 'Setup Wizard', 'fx' ),
        __( 'Setup Wizard', 'fx' ),
        'manage_options',
        'fx-setup',
        'fx_render_setup_wizard_page'
    );
}
add_action( 'admin_menu', 'fx_register_setup_wizard_page', 11 );

/**
 * Render the Setup Wizard page markup.
 */
function fx_render_setup_wizard_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap"><h1>' . esc_html( get_admin_page_title() ) . '</h1><div id="fx-setup-wizard"></div></div>';
}

/**
 * Enqueue assets for the wizard page.
 *
 * @param string $hook Current admin page hook.
 */
function fx_setup_wizard_assets( $hook ) {
    if ( 'fx-dashboard_page_fx-setup' !== $hook ) {
        return;
    }

    $asset_path = get_theme_file_path( 'assets/admin' );
    $asset_url  = get_theme_file_uri( 'assets/admin' );

    if ( file_exists( $asset_path . '/wizard.css' ) ) {
        wp_enqueue_style(
            'fx-wizard',
            $asset_url . '/wizard.css',
            array(),
            filemtime( $asset_path . '/wizard.css' )
        );
    }

    if ( file_exists( $asset_path . '/wizard.js' ) ) {
        wp_enqueue_script(
            'fx-wizard',
            $asset_url . '/wizard.js',
            array( 'wp-element', 'wp-components', 'wp-api-fetch' ),
            filemtime( $asset_path . '/wizard.js' ),
            true
        );

        $data = array(
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'restBase' => esc_url_raw( rest_url( 'fx/v1' ) ),
        );
        wp_localize_script( 'fx-wizard', 'fxWizard', $data );
    }
}
add_action( 'admin_enqueue_scripts', 'fx_setup_wizard_assets' );

/**
 * REST API endpoint for running wizard steps.
 */
function fx_register_wizard_routes() {
    register_rest_route(
        'fx/v1',
        '/wizard/(?P<step>[a-z-]+)',
        array(
            'methods'             => 'POST',
            'callback'            => 'fx_wizard_run_step',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        )
    );
}
add_action( 'rest_api_init', 'fx_register_wizard_routes' );

/**
 * Retrieve list of wizard steps.
 *
 * @return array
 */
function fx_setup_wizard_steps() {
    $steps = array( 'login', 'plugins', 'import', 'setup' );


    /**
     * Filter the setup wizard steps.
     *
     * @param array $steps Steps array.
     */
    return apply_filters( 'fx_setup_wizard_steps', $steps );
}

/**
 * Execute a setup wizard step.
 *
 * @param WP_REST_Request $request Request object.
 *
 * @return WP_REST_Response|WP_Error
 */
function fx_wizard_run_step( $request ) {
    $step  = isset( $request['step'] ) ? sanitize_key( $request['step'] ) : '';
    $steps = fx_setup_wizard_steps();

    if ( ! in_array( $step, $steps, true ) ) {
        return new WP_Error( 'invalid_step', __( 'Invalid wizard step.', 'fortiveax' ), array( 'status' => 400 ) );
    }

    switch ( $step ) {
        case 'login':
            $action = isset( $request['action'] ) ? sanitize_key( $request['action'] ) : 'status';

            if ( 'logout' === $action ) {
                delete_option( 'fortiveax_license_token' );
                if ( function_exists( 'fx_core_run_checks' ) ) {
                    fx_core_run_checks();
                }
                $status = get_option( 'fortiveax_license_status', array() );
                return rest_ensure_response( array( 'success' => true, 'status' => $status ) );
            }

            if ( 'recheck' === $action ) {
                if ( function_exists( 'fx_core_run_checks' ) ) {
                    fx_core_run_checks();
                }
                $status = get_option( 'fortiveax_license_status', array() );
                return rest_ensure_response( array( 'success' => true, 'status' => $status ) );
            }

            if ( 'login' === $action ) {
                $email    = isset( $request['email'] ) ? sanitize_email( $request['email'] ) : '';
                $password = isset( $request['password'] ) ? (string) $request['password'] : '';

                $body = array(
                    'email'    => $email,
                    'password' => $password,
                    'site_url' => site_url(),
                    'theme'    => 'fortiveax',
                    'version'  => wp_get_theme()->get( 'Version' ),
                );

                $response = wp_remote_post(
                    'https://licenses.example.com/api/login',
                    array(
                        'body'    => wp_json_encode( $body ),
                        'headers' => array( 'Content-Type' => 'application/json' ),
                        'timeout' => 15,
                    )
                );

                if ( is_wp_error( $response ) ) {
                    return new WP_Error( 'login_request_failed', __( 'Could not connect to license server.', 'fx' ), array( 'status' => 500 ) );
                }

                $data = json_decode( wp_remote_retrieve_body( $response ), true );
                if ( empty( $data['token'] ) ) {
                    return new WP_Error( 'login_invalid', __( 'Invalid email or password.', 'fx' ), array( 'status' => 401 ) );
                }

                $claims = array(
                    'iss' => defined( 'FX_JWT_ISS' ) ? FX_JWT_ISS : 'fortiveax',
                    'aud' => defined( 'FX_JWT_AUD' ) ? FX_JWT_AUD : wp_parse_url( site_url(), PHP_URL_HOST ),
                );

                // Verify only if MU verification helpers exist; otherwise, store token and let MU core verify.
                $verified_claims = array();
                if ( function_exists( 'fx_jwt_verify_rs256' ) && defined( 'FX_RSA_PUBLIC' ) ) {
                    $verify = fx_jwt_verify_rs256( $data['token'], FX_RSA_PUBLIC, $claims );
                    if ( is_wp_error( $verify ) ) {
                        return new WP_Error( 'login_verify', __( 'Token verification failed.', 'fx' ), array( 'status' => 400 ) );
                    }
                    if ( is_array( $verify ) ) {
                        $verified_claims = $verify;
                    }
                }

                update_option( 'fortiveax_license_token', $data['token'] );
                if ( function_exists( 'fx_core_run_checks' ) ) {
                    fx_core_run_checks();
                }
                $status = get_option( 'fortiveax_license_status', array() );
                if ( isset( $verified_claims['plan'] ) ) {
                    $status['plan'] = $verified_claims['plan'];
                } elseif ( isset( $data['plan'] ) ) {
                    $status['plan'] = $data['plan'];
                }
                update_option( 'fortiveax_license_status', $status );
                return rest_ensure_response( array( 'success' => true, 'status' => $status ) );
            }

            $status = get_option( 'fortiveax_license_status', array() );
            return rest_ensure_response( array( 'success' => true, 'status' => $status ) );
        case 'plugins':
            fx_wizard_install_plugins();
            break;
        case 'import':
            if ( function_exists( 'fx_import_demo_data' ) ) {
                fx_import_demo_data();
            }
            break;
        case 'setup':
            fx_wizard_basic_setup();
            update_option( 'fx_wizard_complete', 1 );
            break;
    }

    return rest_ensure_response( array( 'success' => true ) );
}

/**
 * Install and activate required plugins using TGMPA.
 */
function fx_wizard_install_plugins() {
    if ( ! class_exists( 'TGM_Plugin_Activation' ) || ! current_user_can( 'install_plugins' ) ) {
        return;
    }

    $tgmpa   = TGM_Plugin_Activation::$instance;
    $plugins = array_map( 'sanitize_key', array_keys( $tgmpa->plugins ) );

    if ( empty( $plugins ) ) {
        return;
    }

    // Suppress installer output.
    ob_start();
    $tgmpa->bulk_install( $plugins );
    ob_end_clean();

    foreach ( $plugins as $slug ) {
        $file = isset( $tgmpa->plugins[ $slug ]['file_path'] ) ? $tgmpa->plugins[ $slug ]['file_path'] : $slug . '/' . $slug . '.php';
        if ( file_exists( WP_PLUGIN_DIR . '/' . $file ) ) {
            activate_plugin( $file );
        }
    }
}

/**
 * Configure front page, menu locations and widgets.
 */
function fx_wizard_basic_setup() {
    // Set front page to "Home" if it exists.
    $front = get_page_by_title( 'Home' );
    if ( $front ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $front->ID );
    }

    // Assign Primary menu location if menu exists.
    $menu = wp_get_nav_menu_object( 'Primary' );
    if ( $menu ) {
        $locations            = get_theme_mod( 'nav_menu_locations', array() );
        $locations['primary'] = $menu->term_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }
}

/**
 * Redirect to the setup wizard on theme activation.
 */
function fx_setup_wizard_activation_redirect() {
    if ( ! is_admin() ) {
        return;
    }
    update_option( 'fx_setup_wizard_redirect', wp_create_nonce( 'fx_setup_wizard' ) );
}
add_action( 'after_switch_theme', 'fx_setup_wizard_activation_redirect' );

/**
 * Maybe redirect to the setup wizard page.
 */
function fx_setup_wizard_maybe_redirect() {
    if ( ! is_admin() ) {
        return;
    }

    $nonce = get_option( 'fx_setup_wizard_redirect' );
    if ( ! $nonce ) {
        return;
    }

    delete_option( 'fx_setup_wizard_redirect' );

    if ( wp_verify_nonce( $nonce, 'fx_setup_wizard' ) ) {
        wp_safe_redirect( admin_url( 'admin.php?page=fx-setup&step=login&_fw_nonce=' . $nonce ) );
        exit;
    }
}
add_action( 'admin_init', 'fx_setup_wizard_maybe_redirect' );
