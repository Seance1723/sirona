<?php
/**
 * Validation helpers for licensing.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sanitize license credentials.
 *
 * @param array $creds Raw credentials.
 * @return array
 */
function fx_license_sanitize_creds( $creds ) {
    return array(
        'email'    => isset( $creds['email'] ) ? sanitize_email( $creds['email'] ) : '',
        'password' => isset( $creds['password'] ) ? (string) $creds['password'] : '',
    );
}