<?php
/**
 * Admin notice for locked Pro features.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'fx_pro_locked_notice' ) ) {
    function fx_pro_locked_notice() {
        if ( ! is_admin() ) {
            return;
        }
        echo '<div class="notice notice-warning"><p>' . esc_html__( 'This feature requires a Pro license.', 'fx' ) . '</p></div>';
    }
    add_action( 'admin_notices', 'fx_pro_locked_notice' );
}