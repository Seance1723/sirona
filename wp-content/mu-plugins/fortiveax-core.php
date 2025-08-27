<?php
/**
 * FortiveaX core must-use plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/fortiveax-core-lib.php';

const FX_LICENSE_PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnFxCorePlaceholder\n-----END PUBLIC KEY-----";
const FX_LICENSE_ISSUER     = 'fortiveax';

/**
 * Indicate core presence.
 *
 * @return bool
 */
function fx_core_present(): bool {
    return true;
}

/**
 * Determine if Pro features are enabled.
 *
 * @return bool
 */
function fx_features_enabled(): bool {
    if ( get_option( 'fortiveax_integrity_fail' ) ) {
        return false;
    }

    $token = get_option( 'fortiveax_license_token' );
    if ( ! $token ) {
        return false;
    }

    $claims = array(
        'iss' => FX_LICENSE_ISSUER,
        'aud' => wp_parse_url( home_url(), PHP_URL_HOST ),
    );

    $result = fx_jwt_verify_rs256( $token, FX_LICENSE_PUBLIC_KEY, $claims );
    return ! is_wp_error( $result );
}

/**
 * Perform periodic license and integrity checks.
 */
function fx_core_run_checks() {
    if ( get_transient( 'fortiveax_license_checked' ) ) {
        return;
    }

    set_transient( 'fortiveax_license_checked', 1, 12 * HOUR_IN_SECONDS );

    $status = array(
        'active'     => false,
        'exp'        => 0,
        'last_check' => time(),
        'msg'        => '',
    );

    $token = get_option( 'fortiveax_license_token' );
    if ( $token ) {
        $claims = array(
            'iss' => FX_LICENSE_ISSUER,
            'aud' => wp_parse_url( home_url(), PHP_URL_HOST ),
        );
        $verify = fx_jwt_verify_rs256( $token, FX_LICENSE_PUBLIC_KEY, $claims );
        if ( ! is_wp_error( $verify ) ) {
            $status['active'] = true;
            $status['exp']    = isset( $verify['exp'] ) ? (int) $verify['exp'] : 0;
        } else {
            $status['msg'] = $verify->get_error_message();
        }
    } else {
        $status['msg'] = 'missing';
    }

    update_option( 'fortiveax_license_status', $status );
    fx_core_integrity_check();
}

/**
 * Integrity check for the core file.
 */
function fx_core_integrity_check() {
    $hash   = fx_sha256_file( __FILE__ );
    $stored = get_option( 'fortiveax_core_hash' );
    if ( $stored && ! hash_equals( $stored, $hash ) ) {
        update_option( 'fortiveax_integrity_fail', 1 );
    } else {
        update_option( 'fortiveax_integrity_fail', 0 );
        if ( ! $stored ) {
            update_option( 'fortiveax_core_hash', $hash );
        }
    }
}

add_action( 'admin_init', 'fx_core_run_checks' );
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    fx_core_run_checks();
}

/**
 * Show license notice when Pro features disabled.
 */
function fx_core_license_notice() {
    if ( fx_features_enabled() ) {
        return;
    }
    $url = admin_url( 'themes.php?page=fx-license' );
    echo '<div class="notice notice-warning is-dismissible"><p>' .
        esc_html__( 'FortiveaX Pro features require activation.', 'fx' ) .
        ' <a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Open License', 'fx' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'fx_core_license_notice' );