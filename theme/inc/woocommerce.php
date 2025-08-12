<?php
/**
 * WooCommerce setup and customizations for FortiveaX theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register WooCommerce support and image sizes.
 */
function fortiveax_woocommerce_setup() {
    add_theme_support(
        'woocommerce',
        array(
            'thumbnail_image_width' => 400,
            'single_image_width'    => 800,
        )
    );

    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'fortiveax_woocommerce_setup' );

/**
 * Cart link used in header.
 */
function fortiveax_cart_link() {
    ?>
    <a class="cart-contents" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'fortiveax' ); ?>">
        <span class="count"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
    </a>
    <?php
}

/**
 * Display header mini cart.
 */
function fortiveax_header_cart() {
    ?>
    <div class="header-cart">
        <?php fortiveax_cart_link(); ?>
        <?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
    </div>
    <?php
}

/**
 * Ensure cart contents update when products are added.
 *
 * @param array $fragments Fragments to refresh.
 * @return array
 */
function fortiveax_woocommerce_cart_link_fragment( $fragments ) {
    ob_start();
    fortiveax_cart_link();
    $fragments['a.cart-contents'] = ob_get_clean();
    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'fortiveax_woocommerce_cart_link_fragment' );

/**
 * Products per row option.
 *
 * @param int $cols Default columns.
 * @return int
 */
function fortiveax_loop_columns( $cols ) {
    if ( 'list' === fxo( 'woo_layout', 'grid' ) ) {
        return 1;
    }
    return max( 1, absint( fxo( 'woo_products_per_row', 3 ) ) );
}
add_filter( 'loop_shop_columns', 'fortiveax_loop_columns' );

/**
 * Add layout class to body based on option.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function fortiveax_woocommerce_layout_class( $classes ) {
    if ( is_shop() || is_product_taxonomy() ) {
        $layout    = fxo( 'woo_layout', 'grid' );
        $classes[] = 'woo-layout-' . sanitize_html_class( $layout );
    }
    return $classes;
}
add_filter( 'body_class', 'fortiveax_woocommerce_layout_class' );