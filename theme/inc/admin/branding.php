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
function fortiveax_get_brand_name() {
    $name = fxo( 'wl_brand_name', 'FortiveaX' );
    if ( fxo( 'wl_hide_name' ) && $name ) {
        return $name;
    }
    return 'FortiveaX';
}

/**
 * Get the branded logo URL.
 *
 * @return string
 */
function fortiveax_get_brand_logo() {
    return fxo( 'wl_logo' );
}

/**
 * Customize admin footer text.
 *
 * @param string $text Existing footer text.
 * @return string
 */
function fortiveax_admin_footer_text( $text ) {
    $brand        = fortiveax_get_brand_name();
    $support_url  = fxo( 'wl_support_url' );
    $support_mail = fxo( 'wl_support_email' );
    $support      = '';

    if ( $support_url ) {
        $support = '<a href="' . esc_url( $support_url ) . '" target="_blank">' . esc_html__( 'Support', 'fortiveax' ) . '</a>';
    } elseif ( $support_mail ) {
        $support = '<a href="mailto:' . esc_attr( $support_mail ) . '">' . esc_html__( 'Support', 'fortiveax' ) . '</a>';
    }

    $text = sprintf( esc_html__( 'Thank you for using %s.', 'fortiveax' ), esc_html( $brand ) );
    if ( $support ) {
        $text .= ' ' . $support;
    }
    return $text;
}
add_filter( 'admin_footer_text', 'fortiveax_admin_footer_text' );

/**
 * Add a branded dashboard widget.
 */
function fortiveax_branding_dashboard_widget() {
    wp_add_dashboard_widget( 'fortiveax_brand_widget', fortiveax_get_brand_name(), 'fortiveax_branding_dashboard_widget_output' );
}
add_action( 'wp_dashboard_setup', 'fortiveax_branding_dashboard_widget' );

/**
 * Output for the branded dashboard widget.
 */
function fortiveax_branding_dashboard_widget_output() {
    $logo        = fortiveax_get_brand_logo();
    $support_url = fxo( 'wl_support_url' );
    $support_mail = fxo( 'wl_support_email' );

    if ( $logo ) {
        echo '<p><img src="' . esc_url( $logo ) . '" style="max-width:100%;height:auto" alt="" /></p>';
    }

    if ( $support_url ) {
        echo '<p><a href="' . esc_url( $support_url ) . '" target="_blank">' . esc_html__( 'Support', 'fortiveax' ) . '</a></p>';
    } elseif ( $support_mail ) {
        echo '<p><a href="mailto:' . esc_attr( $support_mail ) . '">' . esc_html__( 'Support', 'fortiveax' ) . '</a></p>';
    }
}