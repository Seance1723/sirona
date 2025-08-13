<?php
function fortiveax_render_post_grid( $attributes ) {
    $defaults = [
        'postType'     => 'post',
        'order'        => 'DESC',
        'orderBy'      => 'date',
        'postsPerPage' => 6,
    ];
    $attrs = wp_parse_args( $attributes, $defaults );

    $query_args = [
        'post_type'      => $attrs['postType'],
        'order'          => $attrs['order'],
        'orderby'        => $attrs['orderBy'],
        'posts_per_page' => (int) $attrs['postsPerPage'],
    ];

    $query = new WP_Query( $query_args );
    if ( ! $query->have_posts() ) {
        return '<div class="wp-block-fortiveax-post-grid"></div>';
    }

    ob_start();
    echo '<div class="wp-block-fortiveax-post-grid">';
    while ( $query->have_posts() ) {
        $query->the_post();
        echo '<article class="post-item">';
        if ( has_post_thumbnail() ) {
            the_post_thumbnail( 'medium' );
        }
        echo '<h3><a href="' . esc_url( get_the_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
        echo '</article>';
    }
    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
}