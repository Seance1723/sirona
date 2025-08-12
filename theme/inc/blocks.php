<?php
/**
 * Blocks and shortcodes for custom post type listings.
 */
function fortiveax_render_cpt_list( $post_type, $layout = 'grid' ) {
    $query = new WP_Query(
        array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
        )
    );

    if ( ! $query->have_posts() ) {
        return '';
    }

    ob_start();
    $classes = 'fortiveax-list fortiveax-list-' . esc_attr( $layout );
    echo '<div class="' . $classes . '">';
    while ( $query->have_posts() ) {
        $query->the_post();
        echo '<div class="fortiveax-item">';
        if ( has_post_thumbnail() ) {
            the_post_thumbnail();
        }
        echo '<h3>' . esc_html( get_the_title() ) . '</h3>';
        echo '<div class="entry-excerpt">' . get_the_excerpt() . '</div>';
        echo '</div>';
    }
    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
}

function fortiveax_portfolio_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'layout' => 'grid' ), $atts, 'portfolio_list' );
    return fortiveax_render_cpt_list( 'portfolio', $atts['layout'] );
}
add_shortcode( 'portfolio_list', 'fortiveax_portfolio_shortcode' );

function fortiveax_testimonial_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'layout' => 'grid' ), $atts, 'testimonial_list' );
    return fortiveax_render_cpt_list( 'testimonial', $atts['layout'] );
}
add_shortcode( 'testimonial_list', 'fortiveax_testimonial_shortcode' );

function fortiveax_team_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'layout' => 'grid' ), $atts, 'team_list' );
    return fortiveax_render_cpt_list( 'team', $atts['layout'] );
}
add_shortcode( 'team_list', 'fortiveax_team_shortcode' );

function fortiveax_faq_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'layout' => 'grid' ), $atts, 'faq_list' );
    return fortiveax_render_cpt_list( 'faq', $atts['layout'] );
}
add_shortcode( 'faq_list', 'fortiveax_faq_shortcode' );

function fortiveax_register_blocks() {
    $blocks = array(
        'portfolio'   => __( 'Portfolio List', 'fortiveax' ),
        'testimonial' => __( 'Testimonial List', 'fortiveax' ),
        'team'        => __( 'Team List', 'fortiveax' ),
        'faq'         => __( 'FAQ List', 'fortiveax' ),
    );

    foreach ( $blocks as $type => $title ) {
        register_block_type(
            'fortiveax/' . $type . '-list',
            array(
                'api_version'     => 2,
                'title'           => $title,
                'icon'            => 'admin-post',
                'category'        => 'widgets',
                'attributes'      => array(
                    'layout' => array(
                        'type'    => 'string',
                        'default' => 'grid',
                    ),
                ),
                'render_callback' => function( $attributes ) use ( $type ) {
                    $layout = isset( $attributes['layout'] ) ? $attributes['layout'] : 'grid';
                    return fortiveax_render_cpt_list( $type, $layout );
                },
            )
        );
    }
}
add_action( 'init', 'fortiveax_register_blocks' );
