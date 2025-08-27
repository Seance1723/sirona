<?php
/**
 * Core library functions for FortiveaX licensing.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Verify RS256 JWT token and validate claims.
 *
 * @param string $jwt            Token string.
 * @param string $public_pem     Public key in PEM format.
 * @param array  $expected_claims Expected claims key => value.
 *
 * @return array|WP_Error Parsed payload on success or error.
 */
function fx_jwt_verify_rs256( string $jwt, string $public_pem, array $expected_claims = array() ) {
    $parts = explode( '.', $jwt );
    if ( 3 !== count( $parts ) ) {
        return new WP_Error( 'jwt_malformed', 'Malformed token' );
    }

    list( $header_b64, $payload_b64, $signature_b64 ) = $parts;
    $header   = json_decode( base64_decode( strtr( $header_b64, '-_', '+/' ) ), true );
    $payload  = json_decode( base64_decode( strtr( $payload_b64, '-_', '+/' ) ), true );
    $signature = base64_decode( strtr( $signature_b64, '-_', '+/' ) );

    if ( empty( $header['alg'] ) || 'RS256' !== $header['alg'] ) {
        return new WP_Error( 'jwt_alg', 'Invalid algorithm' );
    }

    $verified = openssl_verify( $header_b64 . '.' . $payload_b64, $signature, $public_pem, OPENSSL_ALGO_SHA256 );
    if ( 1 !== $verified ) {
        return new WP_Error( 'jwt_signature', 'Invalid signature' );
    }

    $now = time();
    if ( isset( $payload['exp'] ) && $now >= (int) $payload['exp'] ) {
        return new WP_Error( 'jwt_expired', 'Token expired' );
    }
    if ( isset( $payload['nbf'] ) && $now < (int) $payload['nbf'] ) {
        return new WP_Error( 'jwt_nbf', 'Token not yet valid' );
    }

    foreach ( $expected_claims as $key => $value ) {
        if ( ! isset( $payload[ $key ] ) || $payload[ $key ] !== $value ) {
            return new WP_Error( 'jwt_claim', sprintf( '%s claim mismatch', $key ) );
        }
    }

    return $payload;
}

/**
 * Get SHA256 hash of a file.
 *
 * @param string $abs_path Absolute file path.
 *
 * @return string Hash string or empty string on failure.
 */
function fx_sha256_file( string $abs_path ): string {
    if ( ! file_exists( $abs_path ) || ! is_readable( $abs_path ) ) {
        return '';
    }

    return hash_file( 'sha256', $abs_path );
}