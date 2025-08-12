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
    do_action( 'woocommerce_shop_loop_item_title' );
    do_action( 'woocommerce_after_shop_loop_item_title' );
    do_action( 'woocommerce_after_shop_loop_item' );
    ?>
</li>