<?php
/**
 * Header template.
 *
 * @package FortiveaX
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#primary"><?php esc_html_e( 'Skip to content', 'fortiveax' ); ?></a>
<?php
$header_classes = array( 'site-header' );
if ( fxo( 'header_sticky' ) ) {
    $header_classes[] = 'is-sticky';
}
if ( is_front_page() && fxo( 'show_hero', 1 ) ) {
    $header_classes[] = 'is-transparent';
}
$header_class = implode( ' ', array_map( 'sanitize_html_class', $header_classes ) );
$logo       = fxo( 'logo' );
$logo_dark  = fxo( 'logo_dark', $logo );
?>
<?php
$header_classes = array( 'site-header' );
if ( fxo( 'header_sticky' ) ) {
    $header_classes[] = 'is-sticky';
}
if ( is_front_page() && fxo( 'show_hero', 1 ) ) {
    $header_classes[] = 'is-transparent';
}
$header_class = implode( ' ', array_map( 'sanitize_html_class', $header_classes ) );
$logo       = fxo( 'logo' );
$logo_dark  = fxo( 'logo_dark', $logo );
?>
<header class="<?php echo esc_attr( $header_class ); ?>">
    <div class="header-inner">
        <div class="site-branding">
            <?php if ( $logo ) : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="custom-logo-link">
                    <img class="logo-light" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="lazy" />
                    <?php if ( $logo_dark ) : ?>
                        <img class="logo-dark" src="<?php echo esc_url( $logo_dark ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="lazy" />
                    <?php endif; ?>
                </a>
            <?php elseif ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
            <?php endif; ?>
        </div>
        <?php if ( has_nav_menu( 'secondary' ) ) : ?>
            <nav class="secondary-navigation" aria-label="<?php esc_attr_e( 'Secondary menu', 'fortiveax' ); ?>">
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'secondary',
                        'menu_id'        => 'secondary-menu',
                        'container'      => false,
                    )
                );
                ?>
            </nav>
        <?php endif; ?>
        <?php if ( has_nav_menu( 'primary' ) ) : ?>
            <nav class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'fortiveax' ); ?>">
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'container'      => false,
                        'walker'         => new FortiveaX_Mega_Menu_Walker(),
                    )
                );
                ?>
            </nav>
        <?php endif; ?>
        <button class="menu-toggle" aria-controls="mobile-menu" aria-expanded="false">
            <?php esc_html_e( 'Menu', 'fortiveax' ); ?>
        </button>
        <button class="search-toggle" aria-controls="search-modal" aria-expanded="false">
            <?php esc_html_e( 'Search', 'fortiveax' ); ?>
        </button>
        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
            <?php fortiveax_header_cart(); ?>
        <?php endif; ?>
    </div>
</header>
<div id="mobile-menu" class="mobile-menu off-canvas" aria-hidden="true">
    <button class="close-menu" aria-label="<?php esc_attr_e( 'Close menu', 'fortiveax' ); ?>">&times;</button>
    <?php
    wp_nav_menu(
        array(
            'theme_location' => 'primary',
            'menu_id'        => 'mobile-menu-list',
            'container'      => false,
        )
    );
    if ( has_nav_menu( 'secondary' ) ) {
        wp_nav_menu(
            array(
                'theme_location' => 'secondary',
                'menu_id'        => 'mobile-secondary-menu',
                'container'      => false,
            )
        );
    }
    ?>
</div>
<div id="search-modal" class="search-modal" aria-hidden="true">
    <button class="close-search" aria-label="<?php esc_attr_e( 'Close search', 'fortiveax' ); ?>">&times;</button>
    <?php get_search_form(); ?>
</div>