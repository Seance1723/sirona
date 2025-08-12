<?php
/**
 * Meta box for assigning header/footer layouts per page.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register meta box.
 */
function fx_layout_assign_meta_box() {
    add_meta_box(
        'fx-layout-assign',
        __( 'Layout Assignment', 'fortiveax' ),
        'fx_layout_assign_meta_box_cb',
        array( 'page', 'post' ),
        'side'
    );
}
add_action( 'add_meta_boxes', 'fx_layout_assign_meta_box' );

/**
 * Render meta box.
 *
 * @param WP_Post $post Post object.
 */
function fx_layout_assign_meta_box_cb( $post ) {
    $header  = get_post_meta( $post->ID, '_fx_header_variant', true );
    $footer  = get_post_meta( $post->ID, '_fx_footer_variant', true );
    $headers = fx_hf_get_layouts( 'header' );
    $footers = fx_hf_get_layouts( 'footer' );
    wp_nonce_field( 'fx_layout_assign_save', 'fx_layout_assign_nonce' );
    echo '<p><label for="fx_header_variant">' . esc_html__( 'Header Variant', 'fortiveax' ) . '</label><br/>';
    echo '<select name="fx_header_variant" id="fx_header_variant">';
    echo '<option value="">' . esc_html__( 'Default', 'fortiveax' ) . '</option>';
    foreach ( $headers as $slug => $data ) {
        printf( '<option value="%s"%s>%s</option>', esc_attr( $slug ), selected( $header, $slug, false ), esc_html( $data['label'] ) );
    }
    echo '</select></p>';
    echo '<p><label for="fx_footer_variant">' . esc_html__( 'Footer Variant', 'fortiveax' ) . '</label><br/>';
    echo '<select name="fx_footer_variant" id="fx_footer_variant">';
    echo '<option value="">' . esc_html__( 'Default', 'fortiveax' ) . '</option>';
    foreach ( $footers as $slug => $data ) {
        printf( '<option value="%s"%s>%s</option>', esc_attr( $slug ), selected( $footer, $slug, false ), esc_html( $data['label'] ) );
    }
    echo '</select></p>';
}

/**
 * Save meta box selections.
 *
 * @param int $post_id Post ID.
 */
function fx_layout_assign_save_meta( $post_id ) {
    if ( ! isset( $_POST['fx_layout_assign_nonce'] ) || ! wp_verify_nonce( $_POST['fx_layout_assign_nonce'], 'fx_layout_assign_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( isset( $_POST['fx_header_variant'] ) ) {
        $val = sanitize_key( $_POST['fx_header_variant'] );
        if ( $val ) {
            update_post_meta( $post_id, '_fx_header_variant', $val );
        } else {
            delete_post_meta( $post_id, '_fx_header_variant' );
        }
    }
    if ( isset( $_POST['fx_footer_variant'] ) ) {
        $val = sanitize_key( $_POST['fx_footer_variant'] );
        if ( $val ) {
            update_post_meta( $post_id, '_fx_footer_variant', $val );
        } else {
            delete_post_meta( $post_id, '_fx_footer_variant' );
        }
    }
}
add_action( 'save_post', 'fx_layout_assign_save_meta' );