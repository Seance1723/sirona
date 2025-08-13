<?php
/**
 * Product content within loops.
 *
 * @package FortiveaX
 */

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}
?>
<li <?php wc_product_class( 'product', $product ); ?>>
    <?php
    do_action( 'woocommerce_before_shop_loop_item' );
    do_action( 'woocommerce_before_shop_loop_item_title' );

    if ( fx_get_option( 'woo_badge_sale', 1 ) && $product->is_on_sale() ) {
        echo '<span class="fx-woo-badge fx-badge-sale">' . esc_html__( 'Sale', 'fx' ) . '</span>';
    }
    if ( fx_get_option( 'woo_badge_new', 1 ) && ( time() - strtotime( $product->get_date_created() ) ) < 30 * DAY_IN_SECONDS ) {
        echo '<span class="fx-woo-badge fx-badge-new">' . esc_html__( 'New', 'fx' ) . '</span>';
    }
    if ( fx_get_option( 'woo_badge_featured', 1 ) && $product->is_featured() ) {
        echo '<span class="fx-woo-badge fx-badge-featured">' . esc_html__( 'Featured', 'fx' ) . '</span>';
    }

    do_action( 'woocommerce_shop_loop_item_title' );
    do_action( 'woocommerce_after_shop_loop_item_title' );

    if ( fx_get_option( 'woo_quick_view' ) ) {
        echo '<button class="fx-woo-quick-view" data-product-id="' . esc_attr( $product->get_id() ) . '">' . esc_html__( 'Quick View', 'fx' ) . '</button>';
    }
    
    do_action( 'woocommerce_after_shop_loop_item' );
    ?>
</li>