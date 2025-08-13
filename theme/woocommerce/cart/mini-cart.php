<?php
/**
 * Mini cart template for FortiveaX theme.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_before_mini_cart' );
?>
<div class="fx-mini-cart-panel<?php echo fx_get_option( 'woo_offcanvas_cart' ) ? ' fx-is-offcanvas' : ''; ?>">
<ul class="woocommerce-mini-cart cart_list product_list_widget">
    <?php if ( ! WC()->cart->is_empty() ) : ?>
        <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = $cart_item['product_id'];
            if ( ! $_product || ! $_product->exists() || 0 === $cart_item['quantity'] ) {
                continue;
            }
            ?>
            <li class="woocommerce-mini-cart-item">
                <?php echo wc_get_cart_remove_link( $cart_item_key ); ?>
                <?php echo $_product->get_image(); ?>
                <a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"><?php echo esc_html( $_product->get_name() ); ?></a>
                <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                <?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times; %s', $cart_item['quantity'], wc_price( $_product->get_price() ) ) . '</span>', $cart_item, $cart_item_key ); ?>
            </li>
        <?php endforeach; ?>
    <?php else : ?>
        <li class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'fx' ); ?></li>
    <?php endif; ?>
</ul>
<?php if ( ! WC()->cart->is_empty() ) : ?>
    <p class="woocommerce-mini-cart__total total">
        <?php esc_html_e( 'Subtotal', 'fx' ); ?>: <?php echo WC()->cart->get_cart_subtotal(); ?>
    </p>
    <p class="woocommerce-mini-cart__buttons buttons">
        <?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?>
    </p>
<?php endif; ?>
</div>
<?php do_action( 'woocommerce_after_mini_cart' ); ?>