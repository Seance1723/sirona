<?php
/**
 * Theme options page for FortiveaX.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get default options.
 *
 * @return array
 */
function fortiveax_default_options() {
    return array(
        'logo'             => '',
        'logo_dark'        => '',
        'header_sticky'    => 0,
        'footer_text'      => '',
        'social_links'     => '',
        'body_font'        => 'Arial, sans-serif',
        'primary_color'    => '#000000',
        'container_width'  => '1200',
        'show_hero'        => 1,
        'posts_per_page'   => 10,
        'cta_text'         => '',
        'google_analytics' => '',
        'lazy_load'        => 1,
        'meta_description' => '',
        'high_contrast'    => 0,
        'import_export'    => '',
    );
}

/**
 * Helper to retrieve option value.
 *
 * @param string $key      Option key.
 * @param mixed  $fallback Fallback value if option not set.
 *
 * @return mixed
 */
function fxo( $key, $fallback = '' ) {
    $options = get_option( 'fortiveax_options', fortiveax_default_options() );
    return isset( $options[ $key ] ) && '' !== $options[ $key ] ? $options[ $key ] : $fallback;
@@ -48,85 +50,97 @@ function fxo( $key, $fallback = '' ) {
/**
 * Add options page to the Appearance menu.
 */
function fortiveax_add_admin_menu() {
    add_theme_page( 'FortiveaX Options', 'FortiveaX Options', 'manage_options', 'fortiveax-options', 'fortiveax_options_page_html' );
}
add_action( 'admin_menu', 'fortiveax_add_admin_menu' );

/**
 * Register settings, sections, and fields.
 */
function fortiveax_settings_init() {
    register_setting(
        'fortiveax_options',
        'fortiveax_options',
        array(
            'sanitize_callback' => 'fortiveax_sanitize_options',
            'default'           => fortiveax_default_options(),
        )
    );

    $sections = array(
        'branding'      => __( 'Branding', 'fortiveax' ),
        'header'        => __( 'Header', 'fortiveax' ),
        'footer'        => __( 'Footer', 'fortiveax' ),
        'social'        => __( 'Social', 'fortiveax' ),
        'typography'    => __( 'Typography', 'fortiveax' ),
        'colors'        => __( 'Colors', 'fortiveax' ),
        'layout'        => __( 'Layout', 'fortiveax' ),
        'home'          => __( 'Home Sections', 'fortiveax' ),
        'blog'          => __( 'Blog', 'fortiveax' ),
        'cta'           => __( 'CTA', 'fortiveax' ),
        'integrations'  => __( 'Integrations', 'fortiveax' ),
        'performance'   => __( 'Performance', 'fortiveax' ),
        'seo'           => __( 'SEO', 'fortiveax' ),
        'accessibility' => __( 'Accessibility', 'fortiveax' ),
        'import'        => __( 'Import/Export', 'fortiveax' ),
    );

    foreach ( $sections as $id => $title ) {
        add_settings_section( "fortiveax_{$id}_section", $title, '__return_false', "fortiveax_{$id}" );
    }

    // Branding.
    add_settings_field( 'logo', __( 'Logo URL', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_branding', 'fortiveax_branding_section', array(
        'label_for' => 'logo',
        'type'      => 'text',
    ) );
    add_settings_field( 'logo_dark', __( 'Dark Logo URL', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_branding', 'fortiveax_branding_section', array(
        'label_for' => 'logo_dark',
        'type'      => 'text',
    ) );

    // Header.
    add_settings_field( 'header_sticky', __( 'Enable Sticky Header', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_header', 'fortiveax_header_section', array(
        'label_for' => 'header_sticky',
        'type'      => 'checkbox',
    ) );

    // Footer.
    add_settings_field( 'footer_text', __( 'Footer Text', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_footer', 'fortiveax_footer_section', array(
        'label_for' => 'footer_text',
        'type'      => 'text',
    ) );

    // Social.
    add_settings_field( 'social_links', __( 'Social Links', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_social', 'fortiveax_social_section', array(
        'label_for'   => 'social_links',
        'type'        => 'textarea',
        'description' => __( 'One per line: network|URL', 'fortiveax' ),
    ) );

    // Typography.
    add_settings_field( 'body_font', __( 'Body Font', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_typography', 'fortiveax_typography_section', array(
        'label_for' => 'body_font',
        'type'      => 'text',
    ) );

    // Colors.
    add_settings_field( 'primary_color', __( 'Primary Color', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_colors', 'fortiveax_colors_section', array(
        'label_for' => 'primary_color',
        'type'      => 'color',
    ) );

    // Layout.
    add_settings_field( 'container_width', __( 'Container Width', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_layout', 'fortiveax_layout_section', array(
        'label_for' => 'container_width',
        'type'      => 'number',
    ) );

    // Home Sections.
    add_settings_field( 'show_hero', __( 'Show Hero Section', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_home', 'fortiveax_home_section', array(
        'label_for' => 'show_hero',
        'type'      => 'checkbox',
    ) );

    // Blog.
@@ -183,50 +197,53 @@ add_action( 'admin_init', 'fortiveax_settings_init' );
 */
function fortiveax_sanitize_options( $input ) {
    $defaults = fortiveax_default_options();
    $output   = array();
    $input    = (array) $input;

    foreach ( $defaults as $key => $default ) {
        switch ( $key ) {
            case 'header_sticky':
            case 'show_hero':
            case 'lazy_load':
            case 'high_contrast':
                $output[ $key ] = isset( $input[ $key ] ) ? 1 : 0;
                break;
            case 'posts_per_page':
            case 'container_width':
                $output[ $key ] = isset( $input[ $key ] ) ? absint( $input[ $key ] ) : $default;
                break;
            case 'primary_color':
                $color            = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
                $output[ $key ]   = $color ? $color : $default;
                break;
            case 'import_export':
                $output[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( $input[ $key ] ) : $default;
                break;
            case 'social_links':
                $output[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( $input[ $key ] ) : $default;
                break;
            default:
                $output[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : $default;
        }
    }

    return $output;
}

/**
 * Render fields.
 *
 * @param array $args Field arguments.
 */
function fortiveax_field_cb( $args ) {
    $options = get_option( 'fortiveax_options', fortiveax_default_options() );
    $key     = $args['label_for'];
    $type    = isset( $args['type'] ) ? $args['type'] : 'text';
    $value   = isset( $options[ $key ] ) ? $options[ $key ] : '';

    switch ( $type ) {
        case 'checkbox':
            printf( '<input type="checkbox" id="%1$s" name="fortiveax_options[%1$s]" value="1" %2$s />', esc_attr( $key ), checked( $value, 1, false ) );
            break;
        case 'number':
            printf( '<input type="number" id="%1$s" name="fortiveax_options[%1$s]" value="%2$s" class="small-text" />', esc_attr( $key ), esc_attr( $value ) );
@@ -288,26 +305,26 @@ function fortiveax_options_page_html() {

    $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'branding';
    if ( ! array_key_exists( $active_tab, $tabs ) ) {
        $active_tab = 'branding';
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'FortiveaX Options', 'fortiveax' ); ?></h1>
        <h2 class="nav-tab-wrapper">
            <?php
            foreach ( $tabs as $id => $title ) {
                $class = ( $id === $active_tab ) ? ' nav-tab-active' : '';
                printf( '<a href="?page=fortiveax-options&tab=%1$s" class="nav-tab%3$s">%2$s</a>', esc_attr( $id ), esc_html( $title ), esc_attr( $class ) );
            }
            ?>
        </h2>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'fortiveax_options' );
            do_settings_sections( 'fortiveax_' . $active_tab );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}