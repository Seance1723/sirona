<?php
function fortiveax_render_animated_box( $attributes, $content ) {
    $classes = '';
    if ( ! empty( $attributes['hideDesktop'] ) ) {
        $classes .= ' fortiveax-hide-desktop';
    }
    if ( ! empty( $attributes['hideTablet'] ) ) {
        $classes .= ' fortiveax-hide-tablet';
    }
    if ( ! empty( $attributes['hideMobile'] ) ) {
        $classes .= ' fortiveax-hide-mobile';
    }

    $animation = isset( $attributes['animation'] ) ? $attributes['animation'] : 'none';

    $wrapper_attributes = get_block_wrapper_attributes(
        array(
            'class'          => trim( $classes ),
            'data-animation' => $animation !== 'none' ? $animation : null,
        )
    );

    return sprintf( '<div %1$s>%2$s</div>', $wrapper_attributes, $content );
}