<?php
/**
 * Frontend renderer for header/footer layouts.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render a layout.
 *
 * @param string $type    header|footer.
 * @param string $variant Variant slug.
 * @return bool Whether layout rendered.
 */
function fx_hf_render_layout( $type, $variant ) {
    $data = fx_hf_get_layout( $type, $variant );
    if ( ! $data ) {
        $presets = fx_hf_default_presets();
        $data    = isset( $presets[ $type ][ $variant ] ) ? $presets[ $type ][ $variant ] : null;
    }

    if ( empty( $data['layout'] ) ) {
        return false;
    }

    $layout = $data['layout'];
    $break  = fx_get_option( 'mobile_breakpoint', 768 );

    $classes = array( "fx-{$type}-layout" );
    if ( ! empty( $layout['sticky'] ) ) {
        $classes[] = 'is-sticky';
    }
    if ( ! empty( $layout['transparent'] ) ) {
        $classes[] = 'is-transparent';
    }
    $class_str = implode( ' ', array_map( 'sanitize_html_class', $classes ) );

    $tag = 'header' === $type ? 'header' : 'footer';
    echo '<' . $tag . ' class="' . esc_attr( $class_str ) . '" style="--fx-mobile-breakpoint:' . esc_attr( (int) $break ) . 'px">';

    foreach ( (array) $layout['rows'] as $row ) {
        echo '<div class="fx-row">';
        foreach ( (array) $row['cols'] as $col ) {
            echo '<div class="fx-col">';
            foreach ( (array) $col['elements'] as $element ) {
                get_template_part( 'template-parts/builder/' . sanitize_key( $element ) );
            }
            echo '</div>';
        }
        echo '</div>';
    }

    echo '</' . $tag . '>';

    return true;
}

/**
 * Get current variant for type.
 *
 * @param string $type Layout type.
 * @return string
 */
function fx_hf_current_variant( $type ) {
    $post_id = get_queried_object_id();
    $meta    = get_post_meta( $post_id, '_fx_' . $type . '_variant', true );
    return $meta ? $meta : 'classic';
}

/**
 * Render header.
 *
 * @return bool
 */
function fx_hf_render_header() {
    return fx_hf_render_layout( 'header', fx_hf_current_variant( 'header' ) );
}

/**
 * Render footer.
 *
 * @return bool
 */
function fx_hf_render_footer() {
    return fx_hf_render_layout( 'footer', fx_hf_current_variant( 'footer' ) );
}