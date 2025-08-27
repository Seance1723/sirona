<?php
/**
 * Theme setup and asset management for FortiveaX.
 *
 * @package FortiveaX
 */

if ( ! function_exists( 'fx_core_present' ) ) {
    update_option( 'fortiveax_integrity_fail', 1 );
}

/**
 * Set up theme supports, menus, and editor styles.
 */
function fx_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 100,
			'width'       => 400,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	register_nav_menus(
		array(
			'primary'   => __( 'Primary Menu', 'fx' ),
			'secondary' => __( 'Secondary Menu', 'fx' ),
			'footer'    => __( 'Footer Menu', 'fx' ),
		)
	);
	add_theme_support( 'editor-styles' );
	add_editor_style( get_theme_file_uri( 'dist/style.css' ) );
}
add_action( 'after_setup_theme', 'fx_setup' );

/**
 * Register widget areas.
 */
function fx_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar', 'fx' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Main sidebar area.', 'fx' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'fx_widgets_init' );

/**
 * Enqueue theme assets.
 */
function fx_enqueue_assets() {
	$dist_path = get_theme_file_path( 'dist' );
	$dist_uri  = get_theme_file_uri( 'dist' );

	if ( file_exists( "$dist_path/style.css" ) ) {
		wp_enqueue_style( 'fx-style', "$dist_uri/style.css", array(), filemtime( "$dist_path/style.css" ) );
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
		wp_enqueue_script( 'fx-theme', "$dist_uri/main.js", array( 'gsap', 'gsap-scrolltrigger' ), filemtime( "$dist_path/main.js" ), true );

		$theme_options = array(
			'colors'   => array(
				'primary'   => get_theme_mod( 'primary_color', '#0d6efd' ),
				'secondary' => get_theme_mod( 'secondary_color', '#6c757d' ),
			),
			'toggle'   => (bool) get_theme_mod( 'theme_toggle', true ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script( 'fx-theme', 'fxTheme', $theme_options );
	}
}

/**
 * Preload primary font and inline critical CSS.
 */
function fx_preload_assets() {
	$dist_uri = get_theme_file_uri( 'dist' );
	$font     = $dist_uri . '/fonts/Inter-Regular.woff2';
	echo '<link rel="preload" href="' . esc_url( $font ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	if ( fx_get_option( 'inline_critical_css' ) ) {
		$critical = get_theme_file_path( 'dist/critical.css' );
		if ( file_exists( $critical ) ) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<style id="critical-css">' . file_get_contents( $critical ) . '</style>' . "\n";
		}
	}
}

add_action( 'wp_head', 'fx_preload_assets' );

/**
 * Ensure images use lazy loading.
 *
 * @param array $attr Attributes for the image tag.
 * @return array Modified attributes.
 */
function fx_lazy_images( $attr ) {
	if ( empty( $attr['loading'] ) ) {
		$attr['loading'] = 'lazy';
	}
	return $attr;
}

/**
 * Add lazy-loading to iframes.
 *
 * @param string $html Iframe HTML.
 * @return string Modified HTML.
 */
function fortiveax_lazy_iframes( $html ) {
	if ( strpos( $html, '<iframe' ) !== false && strpos( $html, 'loading=' ) === false ) {
		$html = str_replace( '<iframe', '<iframe loading="lazy"', $html );
	}
	return $html;
}

/**
 * Remove emoji scripts and styles.
 */
function fx_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}

/**
 * Disable WordPress embeds.
 */
function fx_disable_embeds() {
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );
	add_filter( 'embed_oembed_discover', '__return_false' );
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
	add_action(
		'wp_footer',
		function () {
			wp_deregister_script( 'wp-embed' );
		}
	);
}

/**
 * Remove jQuery Migrate on the front end.
 *
 * @param WP_Scripts $scripts Scripts instance.
 */
function fx_remove_jquery_migrate( $scripts ) {
	if ( ! is_admin() && $scripts->has( 'jquery' ) ) {
		$scripts->remove( 'jquery' );
		$scripts->add( 'jquery', false, array( 'jquery-core' ) );
	}
}

/**
 * Parse script manager rules.
 *
 * @param string $raw Raw rule string.
 * @return array Parsed rules.
 */
function fx_parse_script_manager( $raw ) {
	$rules = array();
	$lines = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
	foreach ( $lines as $line ) {
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( 3 === count( $parts ) ) {
			list( $template, $handle, $mode ) = $parts;
			$rules[ $template ][ $handle ]    = $mode;
		}
	}
	return $rules;
}

/**
 * Modify script tag based on script manager rules.
 *
 * @param string $tag    The HTML script tag.
 * @param string $handle Script handle.
 * @param string $src    Script source.
 * @return string Filtered script tag.
 */
function fx_manage_scripts( $tag, $handle, $src ) {
	unset( $src );
	$raw = fx_get_option( 'script_manager', '' );
	if ( empty( $raw ) ) {
		return $tag;
	}
	$rules    = fx_parse_script_manager( $raw );
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
	if ( 'defer' === $mode && fx_get_option( 'defer_scripts' ) ) {
		return str_replace( '<script', '<script defer', $tag );
	}
	if ( 'async' === $mode && fx_get_option( 'async_noncritical' ) ) {
		return str_replace( '<script', '<script async', $tag );
	}
	return $tag;
}

/**
 * Initialize performance-related features.
 */
function fx_performance_setup() {
	if ( fx_get_option( 'lazy_load' ) ) {
		add_filter( 'wp_get_attachment_image_attributes', 'fx_lazy_images' );
	}
	if ( fx_get_option( 'lazy_iframes' ) ) {
		add_filter( 'embed_oembed_html', 'fortiveax_lazy_iframes' );
	}
	if ( fx_get_option( 'disable_emojis' ) ) {
		fx_disable_emojis();
	}
	if ( fx_get_option( 'disable_embeds' ) ) {
		fx_disable_embeds();
	}
	if ( fx_get_option( 'disable_jquery_migrate' ) ) {
		add_action( 'wp_default_scripts', 'fx_remove_jquery_migrate' );
	}
	if ( fx_get_option( 'defer_scripts' ) || fx_get_option( 'async_noncritical' ) || fx_get_option( 'script_manager' ) ) {
		add_filter( 'script_loader_tag', 'fx_manage_scripts', 10, 3 );
	}
}

add_action( 'init', 'fx_performance_setup' );

add_action( 'wp_enqueue_scripts', 'fx_enqueue_assets' );
require_once get_theme_file_path( 'inc/licensing/license.php' );
require_once get_theme_file_path( 'inc/integrity/features.php' );
add_action( 'init', array( fx_license(), 'schedule_cron' ) );
require_once get_theme_file_path( 'inc/options.php' );
require_once get_theme_file_path( 'inc/custom-post-types.php' );
require_once get_theme_file_path( 'inc/blocks.php' );
require_once get_theme_file_path( 'inc/blocks-pro/index.php' );
require_once get_theme_file_path( 'inc/global-elements/render.php' );
require_once get_theme_file_path( 'inc/seo.php' );
require_once get_theme_file_path( 'inc/contact-form.php' );
require_once get_theme_file_path( 'inc/demo-importer.php' );
require_once get_theme_file_path( 'inc/woocommerce.php' );
require_once get_theme_file_path( 'inc/woo/setup.php' );
require_once get_theme_file_path( 'inc/tgm/register-plugins.php' );
require_once get_theme_file_path( 'inc/admin/rest-dashboard.php' );
require_once get_theme_file_path( 'inc/setup-wizard/wizard.php' );
if ( is_admin() ) {
	require_once get_theme_file_path( 'inc/admin/dashboard.php' );
}
require_once get_theme_file_path( 'inc/admin/branding.php' );
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