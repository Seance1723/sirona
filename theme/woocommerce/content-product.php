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

    if ( fxo( 'woo_badge_sale', 1 ) && $product->is_on_sale() ) {
        echo '<span class="woo-badge badge-sale">' . esc_html__( 'Sale', 'fortiveax' ) . '</span>';
    }
    if ( fxo( 'woo_badge_new', 1 ) && ( time() - strtotime( $product->get_date_created() ) ) < 30 * DAY_IN_SECONDS ) {
        echo '<span class="woo-badge badge-new">' . esc_html__( 'New', 'fortiveax' ) . '</span>';
    }
    if ( fxo( 'woo_badge_featured', 1 ) && $product->is_featured() ) {
        echo '<span class="woo-badge badge-featured">' . esc_html__( 'Featured', 'fortiveax' ) . '</span>';
    }

    do_action( 'woocommerce_shop_loop_item_title' );
    do_action( 'woocommerce_after_shop_loop_item_title' );

    if ( fxo( 'woo_quick_view' ) ) {
        echo '<button class="woo-quick-view" data-product-id="' . esc_attr( $product->get_id() ) . '">' . esc_html__( 'Quick View', 'fortiveax' ) . '</button>';
    }
    
    do_action( 'woocommerce_after_shop_loop_item' );
    ?>
</li>