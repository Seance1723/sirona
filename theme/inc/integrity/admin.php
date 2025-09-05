<?php
/**
 * Admin notice for integrity drift with repair link.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function fx_integrity_admin_notice() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $diff = fx_integrity_get_diff();
    $has  = ! empty( $diff['changed'] ) || ! empty( $diff['missing'] );
    if ( ! $has ) {
        return;
    }
    $license_url = admin_url( 'admin.php?page=fx-options&tab=license' );
    echo '<div class="notice notice-error"><p>' . esc_html__( 'Theme integrity check detected file changes.', 'fx' ) . ' ' . sprintf( '<a href="%s">%s</a>', esc_url( $license_url ), esc_html__( 'Review and repair', 'fx' ) ) . '</p></div>';
}
add_action( 'admin_notices', 'fx_integrity_admin_notice' );

