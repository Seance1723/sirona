<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render a global element by ID.
 *
 * @param int $id Post ID.
 * @return string
 */
function fx_render_global_element( $id ) {
    $post = get_post( $id );
    if ( ! $post || 'fx_global' !== $post->post_type ) {
        return '';
    }
    $blocks  = parse_blocks( $post->post_content );
    $content = '';
    foreach ( $blocks as $block ) {
        $content .= render_block( $block );
    }
    return $content;
}

/**
 * Replace placeholders with rendered global elements.
 *
 * @param string $content Post content.
 * @return string
 */
function fx_replace_global_placeholders( $content ) {
    return preg_replace_callback(
        '/<!--fx_global:(\d+)-->/',
        function ( $matches ) {
            return fx_render_global_element( intval( $matches[1] ) );
        },
        $content
    );
}
add_filter( 'the_content', 'fx_replace_global_placeholders', 1 );

/**
 * Export all global elements as JSON.
 *
 * @return string
 */
function fx_global_elements_export() {
    $posts = get_posts(
        array(
            'post_type'      => 'fx_global',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        )
    );
    $data = array();
    foreach ( $posts as $p ) {
        $data[] = array(
            'title'   => $p->post_title,
            'content' => $p->post_content,
        );
    }
    return wp_json_encode( $data );
}

/**
 * Import global elements from JSON.
 *
 * @param string $json JSON string.
 */
function fx_global_elements_import( $json ) {
    $items = json_decode( wp_unslash( $json ), true );
    if ( ! is_array( $items ) ) {
        return;
    }
    foreach ( $items as $item ) {
        wp_insert_post(
            array(
                'post_type'    => 'fx_global',
                'post_status'  => 'publish',
                'post_title'   => isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '',
                'post_content' => isset( $item['content'] ) ? $item['content'] : '',
            )
        );
    }
}

/**
 * Enqueue editor assets for saving selection as global.
 */
function fx_global_elements_editor_assets() {
    wp_enqueue_script(
        'fx-global-elements-editor',
        get_template_directory_uri() . '/inc/global-elements/editor.js',
        array( 'wp-i18n', 'wp-plugins', 'wp-edit-post', 'wp-data', 'wp-blocks', 'wp-api-fetch', 'wp-components', 'wp-element' ),
        filemtime( get_template_directory() . '/inc/global-elements/editor.js' ),
        true
    );
}
add_action( 'enqueue_block_editor_assets', 'fx_global_elements_editor_assets' );