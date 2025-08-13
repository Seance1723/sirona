<?php
/**
 * Register custom post types for the theme.
 */
function fx_register_cpts() {
    $supports   = array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' );
    $taxonomies = array( 'category', 'post_tag' );

    register_post_type(
        'portfolio',
        array(
            'labels' => array(
                'name'          => __( 'Portfolios', 'fx' ),
                'singular_name' => __( 'Portfolio', 'fx' ),
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
                'name'          => __( 'Testimonials', 'fx' ),
                'singular_name' => __( 'Testimonial', 'fx' ),
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
                'name'          => __( 'Team Members', 'fx' ),
                'singular_name' => __( 'Team Member', 'fx' ),
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
                'name'          => __( 'FAQs', 'fx' ),
                'singular_name' => __( 'FAQ', 'fx' ),
            ),
            'public'      => true,
            'has_archive' => true,
            'supports'    => $supports,
            'taxonomies'  => $taxonomies,
            'show_in_rest'=> true,
        )
    );
    register_post_type(
        'fx_global',
        array(
            'labels' => array(
                'name'          => __( 'Global Elements', 'fx' ),
                'singular_name' => __( 'Global Element', 'fx' ),
            ),
            'public'      => false,
            'show_ui'     => true,
            'supports'    => array( 'title', 'editor' ),
            'show_in_rest'=> true,
        )
    );
}
add_action( 'init', 'fx_register_cpts' );