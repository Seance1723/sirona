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
function fx_woocommerce_setup() {
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
add_action( 'after_setup_theme', 'fx_woocommerce_setup' );

/**
 * Cart link used in header.
 */
function fx_cart_link() {
    ?>
    <a class="cart-contents" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'fx' ); ?>">
        <span class="count"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
    </a>
    <?php
}

/**
 * Display header mini cart.
 */
function fx_header_cart() {
    ?>
    <div class="header-cart">
        <?php fx_cart_link(); ?>
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
function fx_woocommerce_cart_link_fragment( $fragments ) {
    ob_start();
    fx_cart_link();
    $fragments['a.cart-contents'] = ob_get_clean();
    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'fx_woocommerce_cart_link_fragment' );

/**
 * Products per row option.
 *
 * @param int $cols Default columns.
 * @return int
 */
function fx_loop_columns( $cols ) {
    if ( 'list' === fx_get_option( 'woo_layout', 'grid' ) ) {
        return 1;
    }
    return max( 1, absint( fx_get_option( 'woo_columns_desktop', 3 ) ) );
}
add_filter( 'loop_shop_columns', 'fx_loop_columns' );