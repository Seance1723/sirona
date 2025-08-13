<?php
/**
 * The Template for displaying all single products with sticky add to cart bar.
 *
 * @package Sirona
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

get_header( 'shop' );

while ( have_posts() ) :
    the_post();
    wc_get_template_part( 'content', 'single-product' );
endwhile; // end of the loop.

?>
<div id="fx-woo-sticky-bar" class="fx-woo-sticky-bar" hidden>
    <div class="fx-woo-sticky-bar__inner">
        <span class="fx-woo-sticky-bar__title"><?php the_title(); ?></span>
        <?php woocommerce_template_single_add_to_cart(); ?>
    </div>
</div>
<?php
get_footer( 'shop' );