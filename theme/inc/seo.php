<?php
/**
 * SEO and schema helpers.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Output basic Open Graph and Twitter meta tags.
 */
function fortiveax_meta_tags() {
    if ( is_admin() ) {
        return;
    }
    global $post;
    $title       = is_singular() ? get_the_title( $post ) : get_bloginfo( 'name' );
    $description = is_singular() ? wp_strip_all_tags( get_the_excerpt( $post ) ) : fxo( 'meta_description', get_bloginfo( 'description' ) );
    $url         = is_singular() ? get_permalink( $post ) : home_url( '/' );
    $image       = is_singular() && has_post_thumbnail( $post ) ? get_the_post_thumbnail_url( $post, 'full' ) : fxo( 'og_image', fxo( 'logo' ) );

    // Per-post overrides.
    $og_title       = is_singular() ? get_post_meta( $post->ID, 'og_title', true ) : '';
    $og_description = is_singular() ? get_post_meta( $post->ID, 'og_description', true ) : '';
    $og_image       = is_singular() ? get_post_meta( $post->ID, 'og_image', true ) : '';
    $twitter_title       = is_singular() ? get_post_meta( $post->ID, 'twitter_title', true ) : '';
    $twitter_description = is_singular() ? get_post_meta( $post->ID, 'twitter_description', true ) : '';
    $twitter_image       = is_singular() ? get_post_meta( $post->ID, 'twitter_image', true ) : '';

    $og_title       = $og_title ? $og_title : $title;
    $og_description = $og_description ? $og_description : $description;
    $og_image       = $og_image ? $og_image : $image;

    $twitter_title       = $twitter_title ? $twitter_title : $og_title;
    $twitter_description = $twitter_description ? $twitter_description : $og_description;
    $twitter_image       = $twitter_image ? $twitter_image : $og_image;
    $twitter_handle      = fxo( 'twitter_handle' );

    echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $og_description ) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
    echo '<meta property="og:type" content="' . ( is_singular() ? 'article' : 'website' ) . '" />' . "\n";
    if ( $og_image ) {
        echo '<meta property="og:image" content="' . esc_url( $og_image ) . '" />' . "\n";
    }

    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    if ( $twitter_handle ) {
        echo '<meta name="twitter:site" content="' . esc_attr( $twitter_handle ) . '" />' . "\n";
    }
    echo '<meta name="twitter:title" content="' . esc_attr( $twitter_title ) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $twitter_description ) . '" />' . "\n";
    if ( $twitter_image ) {
        echo '<meta name="twitter:image" content="' . esc_url( $twitter_image ) . '" />' . "\n";
    }
}
add_action( 'wp_head', 'fortiveax_meta_tags', 1 );

/**
 * Output Organization schema.
 */
function fortiveax_schema_organization() {
    if ( ! fxo( 'schema_org', 1 ) ) {
        return;
    }
    $data = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'url'      => home_url( '/' ),
        'name'     => get_bloginfo( 'name' ),
    );
    $logo = fxo( 'logo' );
    if ( $logo ) {
        $data['logo'] = esc_url( $logo );
    }
    echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}
add_action( 'wp_head', 'fortiveax_schema_organization' );

/**
 * Output BreadcrumbList schema.
 */
function fortiveax_schema_breadcrumb() {
    if ( ! fxo( 'schema_breadcrumb', 1 ) || is_front_page() ) {
        return;
    }
    $items    = array();
    $position = 1;
    $items[]  = array(
        '@type'    => 'ListItem',
        'position' => $position++,
        'name'     => get_bloginfo( 'name' ),
        'item'     => home_url( '/' ),
    );

    if ( is_singular() ) {
        global $post;
        $ancestors = array_reverse( get_post_ancestors( $post ) );
        foreach ( $ancestors as $ancestor ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => get_the_title( $ancestor ),
                'item'     => get_permalink( $ancestor ),
            );
        }
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title( $post ),
        );
    } elseif ( is_archive() ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => post_type_archive_title( '', false ),
        );
    }

    if ( count( $items ) < 2 ) {
        return;
    }

    $data = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    );

    echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}
add_action( 'wp_head', 'fortiveax_schema_breadcrumb' );

/**
 * Output Article schema for posts.
 */
function fortiveax_schema_article() {
    if ( ! fxo( 'schema_article', 1 ) || ! is_singular( 'post' ) ) {
        return;
    }
    global $post;
    $data = array(
        '@context'      => 'https://schema.org',
        '@type'         => 'Article',
        'headline'      => get_the_title( $post ),
        'datePublished' => get_the_date( 'c', $post ),
        'dateModified'  => get_the_modified_date( 'c', $post ),
        'author'        => array(
            '@type' => 'Person',
            'name'  => get_the_author_meta( 'display_name', $post->post_author ),
        ),
        'publisher'     => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo( 'name' ),
        ),
    );
    if ( has_post_thumbnail( $post ) ) {
        $data['image'] = get_the_post_thumbnail_url( $post, 'full' );
    }
    echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}
add_action( 'wp_head', 'fortiveax_schema_article' );

/**
 * Output FAQ schema.
 */
function fortiveax_schema_faq() {
    if ( ! fxo( 'schema_faq', 1 ) ) {
        return;
    }
    $faqs = array();
    if ( is_singular( 'faq' ) ) {
        $faqs[] = array(
            '@type'          => 'Question',
            'name'           => get_the_title(),
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => wp_strip_all_tags( get_the_content() ),
            ),
        );
    } elseif ( is_post_type_archive( 'faq' ) ) {
        $posts = get_posts( array( 'post_type' => 'faq', 'numberposts' => -1 ) );
        foreach ( $posts as $faq ) {
            $faqs[] = array(
                '@type'          => 'Question',
                'name'           => get_the_title( $faq ),
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => wp_strip_all_tags( $faq->post_content ),
                ),
            );
        }
    } else {
        return;
    }
    if ( empty( $faqs ) ) {
        return;
    }
    $data = array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $faqs,
    );
    echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}
add_action( 'wp_head', 'fortiveax_schema_faq' );

/**
 * Output Service schema for portfolio items.
 */
function fortiveax_schema_service() {
    if ( ! fxo( 'schema_service', 1 ) || ! is_singular( 'portfolio' ) ) {
        return;
    }
    global $post;
    $data = array(
        '@context'   => 'https://schema.org',
        '@type'      => 'Service',
        'name'       => get_the_title( $post ),
        'description'=> wp_strip_all_tags( get_the_excerpt( $post ) ),
        'provider'   => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo( 'name' ),
        ),
    );
    if ( has_post_thumbnail( $post ) ) {
        $data['image'] = get_the_post_thumbnail_url( $post, 'full' );
    }
    echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}
add_action( 'wp_head', 'fortiveax_schema_service' );