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
        'defer_scripts'    => 0,
        'async_noncritical'=> 0,
        'inline_critical_css' => 0,
        'lazy_iframes'     => 1,
        'disable_emojis'   => 0,
        'disable_embeds'   => 0,
        'disable_jquery_migrate' => 0,
        'script_manager'   => '',
        'meta_description' => '',
        'og_image'         => '',
        'twitter_handle'   => '',
        'schema_org'       => 1,
        'schema_breadcrumb'=> 1,
        'schema_article'   => 1,
        'schema_faq'       => 1,
        'schema_service'   => 1,
        'high_contrast'    => 0,
        'import_export'    => '',
        'global_elements'  => '',
        'woo_layout'       => 'grid',
        'woo_products_per_row' => 3,
        'woo_columns_mobile'  => 1,
        'woo_columns_tablet'  => 2,
        'woo_columns_desktop' => 3,
        'woo_layout_toggle'   => 0,
        'woo_badge_sale'      => 1,
        'woo_badge_new'       => 1,
        'woo_badge_featured'  => 1,
        'woo_quick_view'      => 0,
        'woo_offcanvas_cart'  => 0,
        // White Label.
        'wl_brand_name'    => '',
        'wl_logo'          => '',
        'wl_support_url'   => '',
        'wl_support_email' => '',
        'wl_hide_name'     => 0,
        'wl_lock_pass'     => '',
        'wl_import_export' => '',
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
}

/**
 * Add options page to the Appearance menu.
 */
function fortiveax_add_admin_menu() {
    $brand = fortiveax_get_brand_name();
    $title = sprintf( __( '%s Options', 'fortiveax' ), $brand );
    add_theme_page( $title, $title, 'manage_options', 'fortiveax-options', 'fortiveax_options_page_html' );
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
        'white_label'   => __( 'White Label', 'fortiveax' ),
        'woocommerce'   => __( 'WooCommerce', 'fortiveax' ),
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
    add_settings_field( 'posts_per_page', __( 'Posts Per Page', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_blog', 'fortiveax_blog_section', array(
        'label_for' => 'posts_per_page',
        'type'      => 'number',
    ) );

    // WooCommerce.
    add_settings_field( 'woo_layout', __( 'Product Layout', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_woocommerce', 'fortiveax_woocommerce_section', array(
        'label_for' => 'woo_layout',
        'type'      => 'select',
        'options'   => array(
            'grid' => __( 'Grid', 'fortiveax' ),
            'list' => __( 'List', 'fortiveax' ),
        ),
    ) );
    add_settings_field( 'woo_products_per_row', __( 'Products Per Row', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_woocommerce', 'fortiveax_woocommerce_section', array(
        'label_for' => 'woo_products_per_row',
        'type'      => 'number',
    ) );
    add_settings_field( 'woo_columns_mobile', __( 'Columns – Mobile', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_woocommerce', 'fortiveax_woocommerce_section', array(
        'label_for' => 'woo_columns_mobile',
        'type'      => 'number',
    ) );
    add_settings_field( 'woo_columns_tablet', __( 'Columns – Tablet', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_woocommerce', 'fortiveax_woocommerce_section', array(
        'label_for' => 'woo_columns_tablet',
        'type'      => 'number',
    ) );
    add_settings_field( 'woo_columns_desktop', __( 'Columns – Desktop', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_woocommerce', 'fortiveax_woocommerce_section', array(
        'label_for' => 'woo_columns_desktop',
        'type'      => 'number',
    ) );
    add_settings_field( 'woo_layout_toggle', __( 'Enable Grid/List Toggle', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_woocommerce', 'fortiveax_woocommerce_section', array(
        'label_for' => 'woo_layout_toggle',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'woo_badge_sale', __( 'Show Sale Badge', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_woocommerce', 'fortiveax_woocommerce_section', array(
        'label_for' => 'woo_badge_sale',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'woo_badge_new', __( 'Show New Badge', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_woocommerce', 'fortiveax_woocommerce_section', array(
        'label_for' => 'woo_badge_new',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'woo_badge_featured', __( 'Show Featured Badge', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_woocommerce', 'fortiveax_woocommerce_section', array(
        'label_for' => 'woo_badge_featured',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'woo_quick_view', __( 'Enable Quick View', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_woocommerce', 'fortiveax_woocommerce_section', array(
        'label_for' => 'woo_quick_view',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'woo_offcanvas_cart', __( 'Enable Off-canvas Cart', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_woocommerce', 'fortiveax_woocommerce_section', array(
        'label_for' => 'woo_offcanvas_cart',
        'type'      => 'checkbox',
    ) );

    // CTA.
    add_settings_field( 'cta_text', __( 'CTA Text', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_cta', 'fortiveax_cta_section', array(
        'label_for' => 'cta_text',
        'type'      => 'text',
    ) );

    // Integrations.
    add_settings_field( 'google_analytics', __( 'Google Analytics ID', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_integrations', 'fortiveax_integrations_section', array(
        'label_for' => 'google_analytics',
        'type'      => 'text',
    ) );

    // Performance.
    add_settings_field( 'lazy_load', __( 'Enable Lazy Load', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_performance', 'fortiveax_performance_section', array(
        'label_for' => 'lazy_load',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'defer_scripts', __( 'Defer Scripts', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_performance', 'fortiveax_performance_section', array(
        'label_for' => 'defer_scripts',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'async_noncritical', __( 'Async Non-critical Scripts', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_performance', 'fortiveax_performance_section', array(
        'label_for' => 'async_noncritical',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'inline_critical_css', __( 'Inline Critical CSS', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_performance', 'fortiveax_performance_section', array(
        'label_for' => 'inline_critical_css',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'lazy_iframes', __( 'Lazy-load Iframes', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_performance', 'fortiveax_performance_section', array(
        'label_for' => 'lazy_iframes',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'disable_emojis', __( 'Disable Emojis', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_performance', 'fortiveax_performance_section', array(
        'label_for' => 'disable_emojis',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'disable_embeds', __( 'Disable Embeds', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_performance', 'fortiveax_performance_section', array(
        'label_for' => 'disable_embeds',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'disable_jquery_migrate', __( 'Disable jQuery Migrate', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_performance', 'fortiveax_performance_section', array(
        'label_for' => 'disable_jquery_migrate',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'script_manager', __( 'Script Manager', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_performance', 'fortiveax_performance_section', array(
        'label_for'   => 'script_manager',
        'type'        => 'textarea',
        'description' => __( 'One per line: template|handle|mode (disable|defer|async)', 'fortiveax' ),
    ) );

    // SEO.
    add_settings_field( 'meta_description', __( 'Meta Description', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_seo', 'fortiveax_seo_section', array(
        'label_for' => 'meta_description',
        'type'      => 'text',
    ) );
    add_settings_field( 'og_image', __( 'Default OG Image URL', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_seo', 'fortiveax_seo_section', array(
        'label_for' => 'og_image',
        'type'      => 'text',
    ) );
    add_settings_field( 'twitter_handle', __( 'Twitter Handle', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_seo', 'fortiveax_seo_section', array(
        'label_for' => 'twitter_handle',
        'type'      => 'text',
    ) );
    add_settings_field( 'schema_org', __( 'Enable Organization Schema', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_seo', 'fortiveax_seo_section', array(
        'label_for' => 'schema_org',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'schema_breadcrumb', __( 'Enable Breadcrumb Schema', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_seo', 'fortiveax_seo_section', array(
        'label_for' => 'schema_breadcrumb',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'schema_article', __( 'Enable Article Schema', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_seo', 'fortiveax_seo_section', array(
        'label_for' => 'schema_article',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'schema_faq', __( 'Enable FAQ Schema', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_seo', 'fortiveax_seo_section', array(
        'label_for' => 'schema_faq',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'schema_service', __( 'Enable Service Schema', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_seo', 'fortiveax_seo_section', array(
        'label_for' => 'schema_service',
        'type'      => 'checkbox',
    ) );

    // Accessibility.
    add_settings_field( 'high_contrast', __( 'Enable High Contrast', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_accessibility', 'fortiveax_accessibility_section', array(
        'label_for' => 'high_contrast',
        'type'      => 'checkbox',
    ) );

    // White Label.
    add_settings_field( 'wl_brand_name', __( 'Admin Brand Name', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_white_label', 'fortiveax_white_label_section', array(
        'label_for' => 'wl_brand_name',
        'type'      => 'text',
    ) );
    add_settings_field( 'wl_logo', __( 'Admin Logo URL', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_white_label', 'fortiveax_white_label_section', array(
        'label_for' => 'wl_logo',
        'type'      => 'text',
    ) );
    add_settings_field( 'wl_support_url', __( 'Support URL', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_white_label', 'fortiveax_white_label_section', array(
        'label_for' => 'wl_support_url',
        'type'      => 'text',
    ) );
    add_settings_field( 'wl_support_email', __( 'Support Email', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_white_label', 'fortiveax_white_label_section', array(
        'label_for' => 'wl_support_email',
        'type'      => 'text',
    ) );
    add_settings_field( 'wl_hide_name', __( 'Hide "FortiveaX" Name', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_white_label', 'fortiveax_white_label_section', array(
        'label_for' => 'wl_hide_name',
        'type'      => 'checkbox',
    ) );
    add_settings_field( 'wl_lock_pass', __( 'Lock Passphrase', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_white_label', 'fortiveax_white_label_section', array(
        'label_for' => 'wl_lock_pass',
        'type'      => 'text',
    ) );
    add_settings_field( 'wl_import_export', __( 'Export/Import JSON', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_white_label', 'fortiveax_white_label_section', array(
        'label_for'   => 'wl_import_export',
        'type'        => 'textarea',
        'description' => __( 'Copy to export or paste JSON to import.', 'fortiveax' ),
    ) );

    // Import/Export.
    add_settings_field( 'import_export', __( 'Import/Export Data', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_import', 'fortiveax_import_section', array(
        'label_for'   => 'import_export',
        'type'        => 'textarea',
        'description' => __( 'Paste export data here or copy current settings.', 'fortiveax' ),
    ) );
    add_settings_field( 'global_elements', __( 'Global Elements JSON', 'fortiveax' ), 'fortiveax_field_cb', 'fortiveax_import', 'fortiveax_import_section', array(
        'label_for'   => 'global_elements',
        'type'        => 'textarea',
        'description' => __( 'Paste JSON here to import or copy to export.', 'fortiveax' ),
    ) );
}
add_action( 'admin_init', 'fortiveax_settings_init' );

/**
 * Sanitize options before saving.
 *
 * @param array $input Raw input values.
 *
 * @return array
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
            case 'defer_scripts':
            case 'async_noncritical':
            case 'inline_critical_css':
            case 'lazy_iframes':
            case 'disable_emojis':
            case 'disable_embeds':
            case 'disable_jquery_migrate':
            case 'schema_org':
            case 'schema_breadcrumb':
            case 'schema_article':
            case 'schema_faq':
            case 'schema_service':
            case 'high_contrast':
            case 'wl_hide_name':
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
            case 'og_image':
            case 'wl_support_url':
            case 'wl_logo':
                $output[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( $input[ $key ] ) : $default;
                break;
            case 'twitter_handle':
                $output[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : $default;
                break;
            case 'wl_support_email':
                $output[ $key ] = isset( $input[ $key ] ) ? sanitize_email( $input[ $key ] ) : $default;
                break;
            case 'import_export':
                $output[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( $input[ $key ] ) : $default;
                break;
            case 'global_elements':
                if ( ! empty( $input[ $key ] ) ) {
                    fx_global_elements_import( $input[ $key ] );
                }
                $output[ $key ] = '';
                break;
            case 'wl_import_export':
                if ( ! empty( $input[ $key ] ) ) {
                    fortiveax_whitelabel_import( $input[ $key ] );
                }
                $output[ $key ] = '';
                break;
            case 'social_links':
            case 'script_manager':
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
            printf(
                '<input type="checkbox" id="%1$s" name="fortiveax_options[%1$s]" value="1" %2$s />',
                esc_attr( $key ),
                checked( $value, 1, false )
            );
            break;
        case 'number':
            printf(
                '<input type="number" id="%1$s" name="fortiveax_options[%1$s]" value="%2$s" class="small-text" />',
                esc_attr( $key ),
                esc_attr( $value )
            );
            break;
        case 'select':
            echo '<select id="' . esc_attr( $key ) . '" name="fortiveax_options[' . esc_attr( $key ) . ']">';
            foreach ( $args['options'] as $option_value => $option_label ) {
                printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $option_value ), selected( $value, $option_value, false ), esc_html( $option_label ) );
            }
            echo '</select>';
            break;
        case 'color':
            printf(
                '<input type="text" class="fortiveax-color-picker" id="%1$s" name="fortiveax_options[%1$s]" value="%2$s" />',
                esc_attr( $key ),
                esc_attr( $value )
            );
            break;
        case 'textarea':
            $display_value = ( 'global_elements' === $key ) ? fx_global_elements_export() : ( 'wl_import_export' === $key ? fortiveax_whitelabel_export() : $value );
            printf(
                '<textarea id="%1$s" name="fortiveax_options[%1$s]" rows="5" cols="50">%2$s</textarea>',
                esc_attr( $key ),
                esc_textarea( $display_value )
            );
            break;
        default:
            printf(
                '<input type="text" id="%1$s" name="fortiveax_options[%1$s]" value="%2$s" class="regular-text" />',
                esc_attr( $key ),
                esc_attr( $value )
            );
    }

    if ( ! empty( $args['description'] ) ) {
        printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
    }
}

/**
 * Export white label settings as JSON.
 *
 * @return string
 */
function fortiveax_whitelabel_export() {
    $keys    = array( 'wl_brand_name', 'wl_logo', 'wl_support_url', 'wl_support_email', 'wl_hide_name', 'wl_lock_pass' );
    $options = get_option( 'fortiveax_options', fortiveax_default_options() );
    $data    = array();
    foreach ( $keys as $key ) {
        $data[ $key ] = isset( $options[ $key ] ) ? $options[ $key ] : '';
    }
    return wp_json_encode( $data );
}

/**
 * Import white label settings from JSON.
 *
 * @param string $json JSON string.
 */
function fortiveax_whitelabel_import( $json ) {
    $data = json_decode( $json, true );
    if ( ! is_array( $data ) ) {
        return;
    }
    $options = get_option( 'fortiveax_options', fortiveax_default_options() );
    $keys    = array( 'wl_brand_name', 'wl_logo', 'wl_support_url', 'wl_support_email', 'wl_hide_name', 'wl_lock_pass' );
    foreach ( $keys as $key ) {
        if ( isset( $data[ $key ] ) ) {
            $options[ $key ] = $data[ $key ];
        }
    }
    update_option( 'fortiveax_options', $options );
}


/**
 * Enqueue admin scripts for color picker.
 *
 * @param string $hook Current admin page.
 */
function fortiveax_admin_enqueue( $hook ) {
    if ( 'appearance_page_fortiveax-options' !== $hook ) {
        return;
    }

    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
}
add_action( 'admin_enqueue_scripts', 'fortiveax_admin_enqueue' );

/**
 * Render the options page.
 */
function fortiveax_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $tabs = array(
        'branding'      => __( 'Branding', 'fortiveax' ),
        'header'        => __( 'Header', 'fortiveax' ),
        'footer'        => __( 'Footer', 'fortiveax' ),
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
        'woocommerce'   => __( 'WooCommerce', 'fortiveax' ),
    );

    $pass        = fxo( 'wl_lock_pass' );
    $wl_unlocked = empty( $pass );
    if ( ! $wl_unlocked && isset( $_GET['wl_pass'] ) ) {
        $wl_unlocked = hash_equals( $pass, sanitize_text_field( wp_unslash( $_GET['wl_pass'] ) ) );
    }
    if ( $wl_unlocked ) {
        $tabs['white_label'] = __( 'White Label', 'fortiveax' );
    }

    $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'branding';
    if ( ! array_key_exists( $active_tab, $tabs ) ) {
        $active_tab = 'branding';
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( sprintf( __( '%s Options', 'fortiveax' ), fortiveax_get_brand_name() ) ); ?></h1>
        <h2 class="nav-tab-wrapper">
            <?php
            foreach ( $tabs as $id => $title ) {
                $class = ( $id === $active_tab ) ? ' nav-tab-active' : '';
                $url   = '?page=fortiveax-options&tab=' . $id;
                if ( ! empty( $_GET['wl_pass'] ) ) {
                    $url .= '&wl_pass=' . urlencode( sanitize_text_field( wp_unslash( $_GET['wl_pass'] ) ) );
                }
                printf( '<a href="%1$s" class="nav-tab%3$s">%2$s</a>', esc_attr( $url ), esc_html( $title ), esc_attr( $class ) );
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
