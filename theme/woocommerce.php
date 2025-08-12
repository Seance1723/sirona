<?php
/**
 * WooCommerce compatibility template.
 *
 * @package FortiveaX
 */

get_header();
?>
<main id="primary" class="site-main" tabindex="-1">
    <?php woocommerce_content(); ?>
</main>
<?php
get_sidebar();
get_footer();