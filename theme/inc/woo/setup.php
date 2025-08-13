<?php
/**
 * WooCommerce setup and helper functions for Sirona theme.
 *
 * This file registers theme support for WooCommerce and exposes
 * utility hooks based on theme options such as layout toggles
 * and feature flags (quick view, off‑canvas cart, badges).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register WooCommerce theme support.
 */
function sirona_woo_setup() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'sirona_woo_setup' );

/**
 * Append layout and column classes based on options.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function sirona_woo_body_classes( $classes ) {
    if ( is_shop() || is_product_taxonomy() ) {
        $layout = fxo( 'woo_layout', 'grid' );
        $classes[] = 'woo-layout-' . sanitize_html_class( $layout );

        $breakpoints = array(
            'mobile'  => fxo( 'woo_columns_mobile', 1 ),
            'tablet'  => fxo( 'woo_columns_tablet', 2 ),
            'desktop' => fxo( 'woo_columns_desktop', 3 ),
        );

        foreach ( $breakpoints as $bp => $count ) {
            $classes[] = 'woo-cols-' . $bp . '-' . absint( $count );
        }
    }
    return $classes;
}
add_filter( 'body_class', 'sirona_woo_body_classes' );

/**
 * Enqueue front‑end script for Woo features.
 */
function sirona_woo_scripts() {
    if ( class_exists( 'WooCommerce' ) ) {
        $dist_path = get_template_directory_uri() . '/dist';
        wp_enqueue_script( 'sirona-woo', $dist_path . '/woo.js', array( 'jquery' ), filemtime( get_template_directory() . '/dist/woo.js' ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'sirona_woo_scripts' );

/**
 * Display grid/list layout toggle before shop loop.
 */
function sirona_woo_layout_toggle_markup() {
    if ( ! fxo( 'woo_layout_toggle' ) ) {
        return;
    }
    echo '<div class="woo-layout-toggle">';
    echo '<button type="button" data-layout="grid">' . esc_html__( 'Grid', 'fortiveax' ) . '</button>';
    echo '<button type="button" data-layout="list">' . esc_html__( 'List', 'fortiveax' ) . '</button>';
    echo '</div>';
}
add_action( 'woocommerce_before_shop_loop', 'sirona_woo_layout_toggle_markup', 30 );