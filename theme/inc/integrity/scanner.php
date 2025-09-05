<?php
/**
 * Integrity scanner: compares theme files to manifest checksums.
 * Admin-only execution, weekly cron, and rate-limited.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Compute current integrity diff and store in option.
 *
 * @return array Diff array with changed, missing, added, scanned_at.
 */
function fx_integrity_scan() {
    $theme_dir  = get_template_directory();
    $manifest   = include get_theme_file_path( 'inc/integrity/manifest.php' );
    $files_map  = isset( $manifest['files'] ) && is_array( $manifest['files'] ) ? $manifest['files'] : array();

    $changed = array();
    $missing = array();
    $added   = array();

    // Compare tracked files.
    foreach ( $files_map as $rel => $expected_hash ) {
        $abs = trailingslashit( $theme_dir ) . ltrim( $rel, '/\\' );
        if ( ! file_exists( $abs ) ) {
            $missing[] = $rel;
            continue;
        }
        $hash = @hash_file( 'sha256', $abs ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
        if ( ! $hash || strtolower( $hash ) !== strtolower( $expected_hash ) ) {
            $changed[] = $rel;
        }
    }

    // Detect added PHP files not in manifest (lightweight signal-only).
    $tracked = array_fill_keys( array_keys( $files_map ), true );
    $rii     = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme_dir, FilesystemIterator::SKIP_DOTS ) );
    foreach ( $rii as $file ) {
        /** @var SplFileInfo $file */
        if ( ! $file->isFile() ) {
            continue;
        }
        $ext = strtolower( pathinfo( $file->getFilename(), PATHINFO_EXTENSION ) );
        if ( 'php' !== $ext ) {
            continue;
        }
        $rel = ltrim( str_replace( '\\', '/', substr( $file->getPathname(), strlen( $theme_dir ) + 1 ) ), '/');
        if ( ! isset( $tracked[ $rel ] ) ) {
            $added[] = $rel;
        }
    }

    $diff = array(
        'changed'   => array_values( array_unique( $changed ) ),
        'missing'   => array_values( array_unique( $missing ) ),
        'added'     => array_values( array_unique( $added ) ),
        'scanned_at'=> current_time( 'mysql' ),
    );

    update_option( 'fortiveax_integrity_diff', $diff );
    if ( ! empty( $changed ) || ! empty( $missing ) ) {
        update_option( 'fortiveax_integrity_fail', 1 );
    }

    return $diff;
}

/**
 * Get last integrity diff.
 *
 * @return array
 */
function fx_integrity_get_diff() {
    $diff = get_option( 'fortiveax_integrity_diff', array() );
    return is_array( $diff ) ? $diff : array();
}

/**
 * Maybe run scan for admins, rate-limited.
 */
function fx_integrity_maybe_scan_admin() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( get_transient( 'fortiveax_integrity_rate' ) ) {
        return;
    }
    set_transient( 'fortiveax_integrity_rate', time(), 10 * MINUTE_IN_SECONDS );
    fx_integrity_scan();
}
add_action( 'admin_init', 'fx_integrity_maybe_scan_admin' );

/**
 * Schedule weekly integrity scan.
 */
function fx_integrity_schedule_cron() {
    if ( ! wp_next_scheduled( 'fx_integrity_scan_event' ) ) {
        wp_schedule_event( time(), 'weekly', 'fx_integrity_scan_event' );
    }
}
add_action( 'init', 'fx_integrity_schedule_cron' );
add_action( 'fx_integrity_scan_event', 'fx_integrity_scan' );

