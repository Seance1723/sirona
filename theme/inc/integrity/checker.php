<?php
/**
 * File integrity checker.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Verify integrity of critical files.
 */
function fx_integrity_check() {
    if ( get_transient( 'fortiveax_integrity_checked' ) ) {
        return;
    }

    set_transient( 'fortiveax_integrity_checked', 1, 12 * HOUR_IN_SECONDS );

    $manifest = include get_theme_file_path( 'inc/integrity/manifest.php' );
    if ( ! is_array( $manifest ) || empty( $manifest['files'] ) ) {
        return;
    }

    $payload = array(
        'version'      => $manifest['version'] ?? '',
        'generated_at' => $manifest['generated_at'] ?? '',
        'files'        => $manifest['files'] ?? array(),
    );

    $secret = getenv( 'FX_RELEASE_SECRET' ) ?: '';
    $sig    = hash_hmac( 'sha256', wp_json_encode( $payload ), $secret );
    if ( ! hash_equals( $sig, $manifest['sig'] ?? '' ) ) {
        update_option( 'fortiveax_integrity_fail', 1 );
        return;
    }

    foreach ( $manifest['files'] as $rel => $hash ) {
        $file = get_theme_file_path( $rel );
        if ( ! file_exists( $file ) || ! hash_equals( $hash, hash_file( 'sha256', $file ) ) ) {
            update_option( 'fortiveax_integrity_fail', 1 );
            return;
        }
    }

    update_option( 'fortiveax_integrity_fail', 0 );
}