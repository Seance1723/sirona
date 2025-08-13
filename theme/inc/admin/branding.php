<?php
/**
 * Admin branding utilities for FortiveaX.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get the branded name for admin displays.
 *
 * @return string
 */
function fx_get_brand_name() {
    $name = fx_get_option( 'wl_brand_name', 'FortiveaX' );
    if ( fx_get_option( 'wl_hide_name' ) && $name ) {
        return $name;
    }
    return 'FortiveaX';
}

/**
 * Get the branded logo URL.
 *
 * @return string
 */
function fx_get_brand_logo() {
    return fx_get_option( 'wl_logo' );
}

/**
 * Customize admin footer text.
 *
 * @param string $text Existing footer text.
 * @return string
 */
function fx_admin_footer_text( $text ) {
    $brand        = fx_get_brand_name();
    $support_url  = fx_get_option( 'wl_support_url' );
    $support_mail = fx_get_option( 'wl_support_email' );
    $support      = '';

    if ( $support_url ) {
        $support = '<a href="' . esc_url( $support_url ) . '" target="_blank">' . esc_html__( 'Support', 'fx' ) . '</a>';
    } elseif ( $support_mail ) {
        $support = '<a href="mailto:' . esc_attr( $support_mail ) . '">' . esc_html__( 'Support', 'fx' ) . '</a>';
    }

    $text = sprintf( esc_html__( 'Thank you for using %s.', 'fx' ), esc_html( $brand ) );
    if ( $support ) {
        $text .= ' ' . $support;
    }
    return $text;
}
add_filter( 'admin_footer_text', 'fx_admin_footer_text' );

/**
 * Add a branded dashboard widget.
 */
function fx_branding_dashboard_widget() {
    wp_add_dashboard_widget( 'fx_brand_widget', fx_get_brand_name(), 'fx_branding_dashboard_widget_output' );
}
add_action( 'wp_dashboard_setup', 'fx_branding_dashboard_widget' );

/**
 * Output for the branded dashboard widget.
 */
function fx_branding_dashboard_widget_output() {
    $logo        = fx_get_brand_logo();
    $support_url = fx_get_option( 'wl_support_url' );
    $support_mail = fx_get_option( 'wl_support_email' );

    if ( $logo ) {
        echo '<p><img src="' . esc_url( $logo ) . '" style="max-width:100%;height:auto" alt="" /></p>';
    }

    if ( $support_url ) {
        echo '<p><a href="' . esc_url( $support_url ) . '" target="_blank">' . esc_html__( 'Support', 'fx' ) . '</a></p>';
    } elseif ( $support_mail ) {
        echo '<p><a href="mailto:' . esc_attr( $support_mail ) . '">' . esc_html__( 'Support', 'fx' ) . '</a></p>';
    }
}