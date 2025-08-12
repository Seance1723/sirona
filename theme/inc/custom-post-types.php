<?php
/**
 * Register custom post types for the theme.
 */
function fortiveax_register_cpts() {
    $supports   = array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' );
    $taxonomies = array( 'category', 'post_tag' );

    register_post_type(
        'portfolio',
        array(
            'labels' => array(
                'name'          => __( 'Portfolios', 'fortiveax' ),
                'singular_name' => __( 'Portfolio', 'fortiveax' ),
            ),
            'public'      => true,
            'has_archive' => true,
            'supports'    => $supports,
            'taxonomies'  => $taxonomies,
            'show_in_rest'=> true,
        )
    );

    register_post_type(
        'testimonial',
        array(
            'labels' => array(
                'name'          => __( 'Testimonials', 'fortiveax' ),
                'singular_name' => __( 'Testimonial', 'fortiveax' ),
            ),
            'public'      => true,
            'has_archive' => true,
            'supports'    => $supports,
            'taxonomies'  => $taxonomies,
            'show_in_rest'=> true,
        )
    );

    register_post_type(
        'team',
        array(
            'labels' => array(
                'name'          => __( 'Team Members', 'fortiveax' ),
                'singular_name' => __( 'Team Member', 'fortiveax' ),
            ),
            'public'      => true,
            'has_archive' => true,
            'supports'    => $supports,
            'taxonomies'  => $taxonomies,
            'show_in_rest'=> true,
        )
    );

    register_post_type(
        'faq',
        array(
            'labels' => array(
                'name'          => __( 'FAQs', 'fortiveax' ),
                'singular_name' => __( 'FAQ', 'fortiveax' ),
            ),
            'public'      => true,
            'has_archive' => true,
            'supports'    => $supports,
            'taxonomies'  => $taxonomies,
            'show_in_rest'=> true,
        )
    );
}
add_action( 'init', 'fortiveax_register_cpts' );