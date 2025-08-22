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
function fx_woo_setup() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'fx_woo_setup' );

/**
 * Append layout and column classes based on options.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function fx_woo_body_classes( $classes ) {
    if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
        $layout = fx_get_option( 'woo_layout', 'grid' );
        $classes[] = 'fx-woo-layout-' . sanitize_html_class( $layout );

        $breakpoints = array(
            'mobile'  => fx_get_option( 'woo_columns_mobile', 1 ),
            'tablet'  => fx_get_option( 'woo_columns_tablet', 2 ),
            'desktop' => fx_get_option( 'woo_columns_desktop', 3 ),
        );

        foreach ( $breakpoints as $bp => $count ) {
            $classes[] = 'fx-woo-cols-' . $bp . '-' . absint( $count );
        }
    }
    return $classes;
}
add_filter( 'body_class', 'fx_woo_body_classes' );

/**
 * Enqueue front‑end script for Woo features.
 */
function fx_woo_scripts() {
    if ( class_exists( 'WooCommerce' ) ) {
        $dist_path = get_theme_file_uri( 'dist' );
        wp_enqueue_script( 'fx-woo', $dist_path . '/woo.js', array( 'jquery' ), filemtime( get_theme_file_path( 'dist/woo.js' ) ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'fx_woo_scripts' );

/**
 * Display grid/list layout toggle before shop loop.
 */
function fx_woo_layout_toggle_markup() {
    if ( ! fx_get_option( 'woo_layout_toggle' ) ) {
        return;
    }
    echo '<div class="fx-woo-layout-toggle">';
    echo '<button type="button" data-layout="grid">' . esc_html__( 'Grid', 'fx' ) . '</button>';
    echo '<button type="button" data-layout="list">' . esc_html__( 'List', 'fx' ) . '</button>';
    echo '</div>';
}
add_action( 'woocommerce_before_shop_loop', 'fx_woo_layout_toggle_markup', 30 );