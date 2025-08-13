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
 * Register Setup Wizard page under Appearance.
 */
function fortiveax_register_setup_wizard_page() {
    add_theme_page(
        __( 'Setup Wizard', 'fortiveax' ),
        __( 'Setup Wizard', 'fortiveax' ),
        'manage_options',
        'fortiveax-setup',
        'fortiveax_render_setup_wizard_page'
    );
}
add_action( 'admin_menu', 'fortiveax_register_setup_wizard_page' );

/**
 * Render the Setup Wizard page markup.
 */
function fortiveax_render_setup_wizard_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap"><h1>' . esc_html( get_admin_page_title() ) . '</h1><div id="fortiveax-setup-wizard"></div></div>';
}

/**
 * Enqueue assets for the wizard page.
 *
 * @param string $hook Current admin page hook.
 */
function fortiveax_setup_wizard_assets( $hook ) {
    if ( 'appearance_page_fortiveax-setup' !== $hook ) {
        return;
    }

    $asset_path = get_template_directory() . '/assets/admin';
    $asset_url  = get_template_directory_uri() . '/assets/admin';

    if ( file_exists( $asset_path . '/wizard.css' ) ) {
        wp_enqueue_style(
            'fortiveax-wizard',
            $asset_url . '/wizard.css',
            array(),
            filemtime( $asset_path . '/wizard.css' )
        );
    }

    if ( file_exists( $asset_path . '/wizard.js' ) ) {
        wp_enqueue_script(
            'fortiveax-wizard',
            $asset_url . '/wizard.js',
            array( 'wp-element', 'wp-components', 'wp-api-fetch' ),
            filemtime( $asset_path . '/wizard.js' ),
            true
        );

        $data = array(
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'restBase' => esc_url_raw( rest_url( 'fortiveax/v1' ) ),
        );
        wp_localize_script( 'fortiveax-wizard', 'fortiveaxWizard', $data );
    }
}
add_action( 'admin_enqueue_scripts', 'fortiveax_setup_wizard_assets' );

/**
 * REST API endpoint for running wizard steps.
 */
function fortiveax_register_wizard_routes() {
    register_rest_route(
        'fortiveax/v1',
        '/wizard/(?P<step>[a-z-]+)',
        array(
            'methods'             => 'POST',
            'callback'            => 'fortiveax_wizard_run_step',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        )
    );
}
add_action( 'rest_api_init', 'fortiveax_register_wizard_routes' );

/**
 * Execute a setup wizard step.
 *
 * @param WP_REST_Request $request Request object.
 *
 * @return WP_REST_Response|WP_Error
 */
function fortiveax_wizard_run_step( $request ) {
    $step = $request['step'];

    switch ( $step ) {
        case 'plugins':
            fortiveax_wizard_install_plugins();
            break;
        case 'import':
            if ( function_exists( 'sirona_import_demo_data' ) ) {
                sirona_import_demo_data();
            }
            break;
        case 'setup':
            fortiveax_wizard_basic_setup();
            update_option( 'fortiveax_wizard_complete', 1 );
            break;
        default:
            return new WP_Error( 'invalid_step', __( 'Invalid wizard step.', 'fortiveax' ), array( 'status' => 400 ) );
    }

    return rest_ensure_response( array( 'success' => true ) );
}

/**
 * Install and activate required plugins using TGMPA.
 */
function fortiveax_wizard_install_plugins() {
    if ( ! class_exists( 'TGM_Plugin_Activation' ) ) {
        return;
    }

    $tgmpa   = TGM_Plugin_Activation::$instance;
    $plugins = array_keys( $tgmpa->plugins );

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
function fortiveax_wizard_basic_setup() {
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
function fortiveax_setup_wizard_activation_redirect() {
    if ( ! is_admin() ) {
        return;
    }
    update_option( 'fortiveax_setup_wizard_redirect', wp_create_nonce( 'fortiveax_setup_wizard' ) );
}
add_action( 'after_switch_theme', 'fortiveax_setup_wizard_activation_redirect' );

/**
 * Maybe redirect to the setup wizard page.
 */
function fortiveax_setup_wizard_maybe_redirect() {
    if ( ! is_admin() ) {
        return;
    }

    $nonce = get_option( 'fortiveax_setup_wizard_redirect' );
    if ( ! $nonce ) {
        return;
    }

    delete_option( 'fortiveax_setup_wizard_redirect' );

    if ( wp_verify_nonce( $nonce, 'fortiveax_setup_wizard' ) ) {
        wp_safe_redirect( admin_url( 'themes.php?page=fortiveax-setup&_fw_nonce=' . $nonce ) );
        exit;
    }
}
add_action( 'admin_init', 'fortiveax_setup_wizard_maybe_redirect' );