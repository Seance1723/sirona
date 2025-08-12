<?php
/**
 * Register required and recommended plugins for FortiveaX theme.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once get_theme_file_path( 'inc/tgm/class-tgm-plugin-activation.php' );

add_action( 'tgmpa_register', 'fortiveax_register_plugins' );

/**
 * Register plugins using TGM Plugin Activation.
 */
function fortiveax_register_plugins() {
    $plugins = array(
        array(
            'name'     => __( 'Contact Form 7', 'fortiveax' ),
            'slug'     => 'contact-form-7',
            'required' => true,
        ),
        array(
            'name'     => __( 'One Click Demo Import', 'fortiveax' ),
            'slug'     => 'one-click-demo-import',
            'required' => true,
        ),
        array(
            'name'     => __( 'Kirki', 'fortiveax' ),
            'slug'     => 'kirki',
            'required' => true,
        ),
        array(
            'name'     => __( 'Yoast SEO', 'fortiveax' ),
            'slug'     => 'wordpress-seo',
            'required' => false,
        ),
        array(
            'name'     => __( 'WP Super Cache', 'fortiveax' ),
            'slug'     => 'wp-super-cache',
            'required' => false,
        ),
        array(
            'name'     => __( 'WooCommerce', 'fortiveax' ),
            'slug'     => 'woocommerce',
            'required' => false,
        ),
        // Example of bundling a plugin with the theme.
        // array(
        //     'name'     => __( 'Bundled Plugin', 'fortiveax' ),
        //     'slug'     => 'bundled-plugin',
        //     'source'   => get_theme_file_path( 'inc/plugins/bundled-plugin.zip' ),
        //     'required' => false,
        // ),
    );

    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    $required_plugins = array_filter(
        $plugins,
        function ( $plugin ) {
            return ! empty( $plugin['required'] );
        }
    );

    $active_required = array_filter(
        $required_plugins,
        function ( $plugin ) {
            $file = isset( $plugin['file_path'] ) ? $plugin['file_path'] : $plugin['slug'] . '/' . $plugin['slug'] . '.php';
            return file_exists( WP_PLUGIN_DIR . '/' . $file ) && is_plugin_active( $file );
        }
    );

    $config = array(
        'id'           => 'fortiveax',
        'default_path' => get_theme_file_path( 'inc/plugins/' ),
        'menu'         => 'tgmpa-install-plugins',
        'parent_slug'  => 'themes.php',
        'capability'   => 'edit_theme_options',
        'has_notices'  => count( $active_required ) !== count( $required_plugins ),
        'dismissable'  => true,
        'is_automatic' => false,
        'message'      => '',
        'strings'      => array(
            'page_title'                      => __( 'Install Required Plugins', 'fortiveax' ),
            'menu_title'                      => __( 'Install Plugins', 'fortiveax' ),
            'installing'                      => __( 'Installing Plugin: %s', 'fortiveax' ),
            'updating'                        => __( 'Updating Plugin: %s', 'fortiveax' ),
            'oops'                            => __( 'Something went wrong with the plugin API.', 'fortiveax' ),
            'notice_can_install_required'     => _n_noop(
                'FortiveaX requires the following plugin: %1$s.',
                'FortiveaX requires the following plugins: %1$s.',
                'fortiveax'
            ),
            'notice_can_install_recommended'  => _n_noop(
                'FortiveaX recommends the following plugin: %1$s.',
                'FortiveaX recommends the following plugins: %1$s.',
                'fortiveax'
            ),
            'notice_ask_to_update'            => _n_noop(
                'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
                'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
                'fortiveax'
            ),
            'notice_ask_to_update_maybe'      => _n_noop(
                'There is an update available for: %1$s.',
                'There are updates available for the following plugins: %1$s.',
                'fortiveax'
            ),
            'notice_can_activate_required'    => _n_noop(
                'The following required plugin is currently inactive: %1$s.',
                'The following required plugins are currently inactive: %1$s.',
                'fortiveax'
            ),
            'notice_can_activate_recommended' => _n_noop(
                'The following recommended plugin is currently inactive: %1$s.',
                'The following recommended plugins are currently inactive: %1$s.',
                'fortiveax'
            ),
            'install_link'                    => _n_noop(
                'Begin installing plugin',
                'Begin installing plugins',
                'fortiveax'
            ),
            'update_link'                     => _n_noop(
                'Begin updating plugin',
                'Begin updating plugins',
                'fortiveax'
            ),
            'activate_link'                   => _n_noop(
                'Begin activating plugin',
                'Begin activating plugins',
                'fortiveax'
            ),
            'return'                          => __( 'Return to Required Plugins Installer', 'fortiveax' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'fortiveax' ),
            'activated_successfully'          => __( 'The following plugin was activated successfully:', 'fortiveax' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'fortiveax' ),
            'dismiss'                         => __( 'Dismiss this notice', 'fortiveax' ),
            'nag_type'                        => 'updated',
        ),
    );

    tgmpa( $plugins, $config );
}