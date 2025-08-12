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

    if ( file_exists( "$dist_path/theme.min.js" ) ) {
        wp_enqueue_script( 'fortiveax-theme', "$dist_uri/theme.min.js", array( 'gsap', 'gsap-scrolltrigger' ), filemtime( "$dist_path/theme.min.js" ), true );

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
    if ( file_exists( "$dist_path/navigation.js" ) ) {
        wp_enqueue_script( 'fortiveax-navigation', "$dist_uri/navigation.js", array(), filemtime( "$dist_path/navigation.js" ), true );
    }
}

/**
 * Lazy-load all images by default.
 */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr ) {
    if ( empty( $attr['loading'] ) ) {
        $attr['loading'] = 'lazy';
    }
    return $attr;
} );

add_filter( 'embed_oembed_html', function ( $html ) {
    if ( strpos( $html, '<iframe' ) !== false && strpos( $html, 'loading=' ) === false ) {
        $html = str_replace( '<iframe', '<iframe loading="lazy"', $html );
    }
    return $html;
} );

/**
 * Preload primary font and inline critical CSS.
 */
function fortiveax_preload_assets() {
    $dist_uri = get_template_directory_uri() . '/dist';
    $font     = $dist_uri . '/fonts/Inter-Regular.woff2';
    echo '<link rel="preload" href="' . esc_url( $font ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    $critical = get_template_directory() . '/dist/critical.css';
    if ( file_exists( $critical ) ) {
        echo '<style id="critical-css">' . file_get_contents( $critical ) . '</style>' . "\n";
    }
}

add_action( 'wp_enqueue_scripts', 'fortiveax_enqueue_assets' );
require_once get_template_directory() . '/inc/options.php';
require_once get_template_directory() . '/inc/custom-post-types.php';
require_once get_template_directory() . '/inc/blocks.php';
require_once get_template_directory() . '/inc/seo.php';
require_once get_template_directory() . '/inc/contact-form.php';
require_once get_template_directory() . '/inc/demo-importer.php';
require_once get_template_directory() . '/inc/woocommerce.php';