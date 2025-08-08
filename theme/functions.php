<?php
/**
 * Enqueue theme assets.
 */
function fortiveax_enqueue_assets() {
    $theme_version = wp_get_theme()->get( 'Version' );
    $dist_path = get_template_directory() . '/dist';
    $dist_uri  = get_template_directory_uri() . '/dist';

    if ( file_exists( "$dist_path/style.css" ) ) {
        wp_enqueue_style( 'fortiveax-style', "$dist_uri/style.css", array(), filemtime( "$dist_path/style.css" ) );
    }

    if ( file_exists( "$dist_path/main.js" ) ) {
        wp_enqueue_script( 'fortiveax-script', "$dist_uri/main.js", array(), filemtime( "$dist_path/main.js" ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'fortiveax_enqueue_assets' );