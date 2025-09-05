<?php

if ( function_exists( 'fx_features_enabled' ) && ! fx_features_enabled() ) {
    require_once get_theme_file_path( 'inc/pro-locked/ui.php' );
    return;
}

require_once __DIR__ . '/post-grid/render.php';

function fx_register_pro_blocks() {
    
    register_block_type( __DIR__ . '/slider' );
    register_block_type(
        __DIR__ . '/post-grid',
        [
            'render_callback' => 'fx_render_post_grid',
        ]
    );
}
add_action( 'init', 'fx_register_pro_blocks' );