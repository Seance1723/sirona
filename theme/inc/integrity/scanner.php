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

/**
 * Determine a safe fallback core theme to switch to.
 *
 * @return string|false Stylesheet slug or false if none found.
 */
function fx_integrity_get_fallback_theme() {
    // Prefer recent default themes if installed.
    $candidates = array(
        'twentytwentyfive',
        'twentytwentyfour',
        'twentytwentythree',
        'twentytwentytwo',
        'twentytwentyone',
        'twentytwenty',
    );
    foreach ( $candidates as $slug ) {
        $theme = wp_get_theme( $slug );
        if ( $theme && $theme->exists() ) {
            return $slug;
        }
    }
    // Fallback: if WP core exposes a default, use it.
    if ( method_exists( 'WP_Theme', 'get_core_default_theme' ) ) {
        $slug  = WP_Theme::get_core_default_theme();
        $theme = $slug ? wp_get_theme( $slug ) : false;
        if ( $theme && $theme->exists() ) {
            return $slug;
        }
    }
    return false;
}

/**
 * On integrity drift, immediately switch to a core default theme.
 * Runs very early in the theme load.
 */
function fx_integrity_maybe_fallback_theme() {
    // Allow disabling via filter or option.
    $allowed = apply_filters( 'fx_integrity_auto_fallback', (bool) get_option( 'fortiveax_integrity_auto_fallback', true ) );
    if ( ! $allowed ) {
        return;
    }
    // Avoid loops if already switched.
    if ( get_option( 'fortiveax_integrity_fallback_done' ) ) {
        return;
    }
    // Only act if there is a recorded diff with changed/missing files.
    $diff = fx_integrity_get_diff();
    $has  = is_array( $diff ) && ( ! empty( $diff['changed'] ) || ! empty( $diff['missing'] ) );
    if ( ! $has ) {
        return;
    }
    $fallback = fx_integrity_get_fallback_theme();
    if ( ! $fallback ) {
        return;
    }
    // Store previous theme to allow admin to switch back after repair.
    $prev = array(
        'template'   => get_option( 'template' ),
        'stylesheet' => get_option( 'stylesheet' ),
        'time'       => time(),
    );
    update_option( 'fortiveax_integrity_prev_theme', $prev );
    update_option( 'fortiveax_integrity_fallback_done', 1 );

    // Ensure a helper MU plugin exists to guide repair from the default theme context.
    if ( function_exists( 'fx_integrity_install_fallback_mu' ) ) {
        fx_integrity_install_fallback_mu();
    } else {
        // Inline installer if function not yet declared (first load).
        require_once ABSPATH . 'wp-admin/includes/file.php';
        if ( ! file_exists( WP_CONTENT_DIR . '/mu-plugins' ) ) {
            wp_mkdir_p( WP_CONTENT_DIR . '/mu-plugins' );
        }
        if ( WP_Filesystem() ) {
            global $wp_filesystem;
            $mu_file = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/fortiveax-fallback.php';
            $code    = <<<'MU'
<?php
/**
 * FortiveaX Fallback Helper (MU)
 * Shows an admin notice after integrity fallback and provides a repair page.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    if ( ! get_option( 'fortiveax_integrity_fallback_done' ) ) { return; }
    $url = admin_url( 'tools.php?page=fortiveax-repair' );
    echo '<div class="notice notice-error"><p>' . esc_html__( 'FortiveaX detected theme file changes and switched to a default theme for safety.', 'fx' ) . ' ' . sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Repair and reactivate', 'fx' ) ) . '</p></div>';
});

add_action( 'admin_menu', function () {
    add_management_page( 'FortiveaX Repair', 'FortiveaX Repair', 'manage_options', 'fortiveax-repair', function () {
        if ( ! current_user_can( 'manage_options' ) ) { return; }
        $uploads = wp_upload_dir();
        $pkg     = trailingslashit( $uploads['basedir'] ) . 'fortiveax/fortiveax-theme.zip';
        echo '<div class="wrap"><h1>FortiveaX Repair</h1>';
        if ( ! file_exists( $pkg ) ) {
            echo '<div class="notice notice-warning"><p>' . sprintf( esc_html__( 'Upload the current theme package to %s, then click Repair.', 'fx' ), esc_html( str_replace( ABSPATH, '/', $pkg ) ) ) . '</p></div>';
        }
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' .
            '<input type="hidden" name="action" value="fortiveax_repair_do" />';
        wp_nonce_field( 'fortiveax_repair', '_fxr' );
        submit_button( __( 'Repair and Reactivate Theme', 'fx' ) );
        echo '</form></div>';
    });
});

add_action( 'admin_post_fortiveax_repair_do', function () {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( __( 'Insufficient permissions.', 'fx' ) ); }
    if ( ! isset( $_POST['_fxr'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_fxr'] ) ), 'fortiveax_repair' ) ) {
        wp_die( __( 'Invalid request.', 'fx' ) );
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    $uploads = wp_upload_dir();
    $pkg_dir = trailingslashit( $uploads['basedir'] ) . 'fortiveax';
    $pkg     = trailingslashit( $pkg_dir ) . 'fortiveax-theme.zip';
    if ( ! file_exists( $pkg ) ) {
        wp_safe_redirect( add_query_arg( 'fxr', 'pkg-missing', admin_url( 'tools.php?page=fortiveax-repair' ) ) );
        exit;
    }
    if ( ! WP_Filesystem() ) {
        wp_safe_redirect( add_query_arg( 'fxr', 'fs-error', admin_url( 'tools.php?page=fortiveax-repair' ) ) );
        exit;
    }
    global $wp_filesystem;
    $tmp = trailingslashit( $pkg_dir ) . '_tmp_' . wp_generate_password( 6, false );
    wp_mkdir_p( $tmp );
    $unzipped = unzip_file( $pkg, $tmp );
    if ( is_wp_error( $unzipped ) ) {
        wp_safe_redirect( add_query_arg( 'fxr', 'unzip', admin_url( 'tools.php?page=fortiveax-repair' ) ) );
        exit;
    }
    $prev = get_option( 'fortiveax_integrity_prev_theme' );
    $slug = isset( $prev['stylesheet'] ) ? sanitize_key( $prev['stylesheet'] ) : '';
    if ( ! $slug ) {
        wp_safe_redirect( add_query_arg( 'fxr', 'no-prev', admin_url( 'tools.php?page=fortiveax-repair' ) ) );
        exit;
    }
    // Locate extracted theme folder matching slug.
    $extracted = '';
    $rii = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $tmp, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::SELF_FIRST );
    foreach ( $rii as $file ) {
        if ( $file->isDir() && basename( $file->getPathname() ) === $slug ) { $extracted = $file->getPathname(); break; }
    }
    if ( ! $extracted ) { $extracted = $tmp . '/' . $slug; }
    $dest = trailingslashit( get_theme_root() ) . $slug;
    if ( ! file_exists( $dest ) ) { wp_mkdir_p( $dest ); }
    $it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $extracted, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::SELF_FIRST );
    foreach ( $it as $fs ) {
        $rel = ltrim( str_replace( '\\', '/', substr( $fs->getPathname(), strlen( $extracted ) ) ), '/' );
        $to  = trailingslashit( $dest ) . $rel;
        if ( $fs->isDir() ) { $wp_filesystem->mkdir( $to ); } else { $wp_filesystem->put_contents( $to, file_get_contents( $fs->getPathname() ), FS_CHMOD_FILE ); }
    }
    // Cleanup.
    $wp_filesystem->rmdir( $tmp, true );
    // Reactivate theme.
    $template   = isset( $prev['template'] ) ? sanitize_key( $prev['template'] ) : $slug;
    $stylesheet = $slug;
    switch_theme( $stylesheet, $template );
    delete_option( 'fortiveax_integrity_fallback_done' );
    wp_safe_redirect( admin_url( 'themes.php?fxr=done' ) );
    exit;
});
MU;
            $wp_filesystem->put_contents( $mu_file, $code, FS_CHMOD_FILE );
        }
    }

    // Switch theme now.
    switch_theme( $fallback );
}
add_action( 'setup_theme', 'fx_integrity_maybe_fallback_theme', 0 );
