<?php
require_once __DIR__ . '/post-grid/render.php';

function fortiveax_register_pro_blocks() {
    register_block_type( __DIR__ . '/slider' );
    register_block_type(
        __DIR__ . '/post-grid',
        [
            'render_callback' => 'fortiveax_render_post_grid',
        ]
    );
}
add_action( 'init', 'fortiveax_register_pro_blocks' );