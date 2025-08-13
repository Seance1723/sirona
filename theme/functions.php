<?php
/**
 * Theme setup and asset management for FortiveaX.
 */

/**
 * Set up theme supports, menus, and editor styles.
 */
function fortiveax_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
    register_nav_menus(
        array(
            'primary'   => __( 'Primary Menu', 'fortiveax' ),
            'secondary' => __( 'Secondary Menu', 'fortiveax' ),
            'footer'    => __( 'Footer Menu', 'fortiveax' ),
        )
    );
    add_theme_support( 'editor-styles' );
    add_editor_style( 'dist/style.css' );
}
add_action( 'after_setup_theme', 'fortiveax_setup' );

/**
 * Register widget areas.
 */
function fortiveax_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Sidebar', 'fortiveax' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Main sidebar area.', 'fortiveax' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );
}
add_action( 'widgets_init', 'fortiveax_widgets_init' );

/**
 * Enqueue theme assets.
 */
function fortiveax_enqueue_assets() {
    $dist_path = get_template_directory() . '/dist';
    $dist_uri  = get_template_directory_uri() . '/dist';

    if ( file_exists( "$dist_path/style.css" ) ) {
        wp_enqueue_style( 'fortiveax-style', "$dist_uri/style.css", array(), filemtime( "$dist_path/style.css" ) );
    }

    if ( file_exists( "$dist_path/animate.min.css" ) ) {
        wp_enqueue_style( 'animate', "$dist_uri/animate.min.css", array(), filemtime( "$dist_path/animate.min.css" ) );
    }

    if ( file_exists( "$dist_path/gsap.min.js" ) ) {
        wp_enqueue_script( 'gsap', "$dist_uri/gsap.min.js", array(), filemtime( "$dist_path/gsap.min.js" ), true );
    }

    if ( file_exists( "$dist_path/ScrollTrigger.min.js" ) ) {
        wp_enqueue_script( 'gsap-scrolltrigger', "$dist_uri/ScrollTrigger.min.js", array( 'gsap' ), filemtime( "$dist_path/ScrollTrigger.min.js" ), true );
    }

    if ( file_exists( "$dist_path/main.js" ) ) {
        wp_enqueue_script( 'fortiveax-theme', "$dist_uri/main.js", array( 'gsap', 'gsap-scrolltrigger' ), filemtime( "$dist_path/main.js" ), true );

        $theme_options = array(
            'colors' => array(
                'primary'   => get_theme_mod( 'primary_color', '#0d6efd' ),
                'secondary' => get_theme_mod( 'secondary_color', '#6c757d' ),
            ),
            'toggle'   => (bool) get_theme_mod( 'theme_toggle', true ),
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        );
        wp_localize_script( 'fortiveax-theme', 'fortiveaX', $theme_options );
    }
    
}

/**
 * Preload primary font and inline critical CSS.
 */
function fortiveax_preload_assets() {
    $dist_uri = get_template_directory_uri() . '/dist';
    $font     = $dist_uri . '/fonts/Inter-Regular.woff2';
    echo '<link rel="preload" href="' . esc_url( $font ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    if ( fxo( 'inline_critical_css' ) ) {
        $critical = get_template_directory() . '/dist/critical.css';
        if ( file_exists( $critical ) ) {
            echo '<style id="critical-css">' . file_get_contents( $critical ) . '</style>' . "\n";
        }
    }
}

add_action( 'wp_head', 'fortiveax_preload_assets' );

function fortiveax_lazy_images( $attr ) {
    if ( empty( $attr['loading'] ) ) {
        $attr['loading'] = 'lazy';
    }
    return $attr;
}

function fortiveax_lazy_iframes( $html ) {
    if ( strpos( $html, '<iframe' ) !== false && strpos( $html, 'loading=' ) === false ) {
        $html = str_replace( '<iframe', '<iframe loading="lazy"', $html );
    }
    return $html;
}

function fortiveax_disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}

function fortiveax_disable_embeds() {
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );
    remove_action( 'rest_api_init', 'wp_oembed_register_route' );
    add_filter( 'embed_oembed_discover', '__return_false' );
    remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
    add_action( 'wp_footer', function () {
        wp_deregister_script( 'wp-embed' );
    } );
}

function fortiveax_remove_jquery_migrate( $scripts ) {
    if ( ! is_admin() && $scripts->has( 'jquery' ) ) {
        $scripts->remove( 'jquery' );
        $scripts->add( 'jquery', false, array( 'jquery-core' ) );
    }
}

function fortiveax_parse_script_manager( $raw ) {
    $rules = array();
    $lines = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
    foreach ( $lines as $line ) {
        $parts = array_map( 'trim', explode( '|', $line ) );
        if ( 3 === count( $parts ) ) {
            list( $template, $handle, $mode ) = $parts;
            $rules[ $template ][ $handle ] = $mode;
        }
    }
    return $rules;
}

function fortiveax_manage_scripts( $tag, $handle, $src ) {
    $raw = fxo( 'script_manager', '' );
    if ( empty( $raw ) ) {
        return $tag;
    }
    $rules    = fortiveax_parse_script_manager( $raw );
    $template = get_page_template_slug();
    $template = $template ? $template : 'default';
    $mode     = '';
    if ( isset( $rules[ $template ][ $handle ] ) ) {
        $mode = $rules[ $template ][ $handle ];
    } elseif ( isset( $rules['*'][ $handle ] ) ) {
        $mode = $rules['*'][ $handle ];
    }
    if ( 'disable' === $mode ) {
        return '';
    }
    if ( 'defer' === $mode && fxo( 'defer_scripts' ) ) {
        return str_replace( '<script', '<script defer', $tag );
    }
    if ( 'async' === $mode && fxo( 'async_noncritical' ) ) {
        return str_replace( '<script', '<script async', $tag );
    }
    return $tag;
}

function fortiveax_performance_setup() {
    if ( fxo( 'lazy_load' ) ) {
        add_filter( 'wp_get_attachment_image_attributes', 'fortiveax_lazy_images' );
    }
    if ( fxo( 'lazy_iframes' ) ) {
        add_filter( 'embed_oembed_html', 'fortiveax_lazy_iframes' );
    }
    if ( fxo( 'disable_emojis' ) ) {
        fortiveax_disable_emojis();
    }
    if ( fxo( 'disable_embeds' ) ) {
        fortiveax_disable_embeds();
    }
    if ( fxo( 'disable_jquery_migrate' ) ) {
        add_action( 'wp_default_scripts', 'fortiveax_remove_jquery_migrate' );
    }
    if ( fxo( 'defer_scripts' ) || fxo( 'async_noncritical' ) || fxo( 'script_manager' ) ) {
        add_filter( 'script_loader_tag', 'fortiveax_manage_scripts', 10, 3 );
    }
}

add_action( 'init', 'fortiveax_performance_setup' );

add_action( 'wp_enqueue_scripts', 'fortiveax_enqueue_assets' );
require_once get_template_directory() . '/inc/options.php';
require_once get_template_directory() . '/inc/custom-post-types.php';
require_once get_template_directory() . '/inc/blocks.php';
require_once get_template_directory() . '/inc/global-elements/render.php';
require_once get_template_directory() . '/inc/seo.php';
require_once get_template_directory() . '/inc/contact-form.php';
require_once get_template_directory() . '/inc/demo-importer.php';
require_once get_template_directory() . '/inc/woocommerce.php';
require_once get_theme_file_path( 'inc/tgm/register-plugins.php' );
require_once get_theme_file_path( 'inc/admin/rest-dashboard.php' );
if ( is_admin() ) {
    require_once get_theme_file_path( 'inc/admin/dashboard.php' );
}
require_once get_theme_file_path( 'inc/builders/header-footer/storage.php' );
require_once get_theme_file_path( 'inc/builders/header-footer/render.php' );
require_once get_theme_file_path( 'inc/meta/layout-assign.php' );
if ( is_admin() ) {
    require_once get_theme_file_path( 'inc/builders/header-footer/admin.php' );
}
require_once get_theme_file_path( 'inc/mega-menu/walker.php' );
if ( is_admin() ) {
    require_once get_theme_file_path( 'inc/mega-menu/meta.php' );
}