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
<a class="skip-link" href="#primary"><?php esc_html_e( 'Skip to content', 'fx' ); ?></a>
<?php
$header_rendered = function_exists( 'fx_hf_render_header' ) && fx_hf_render_header();
if ( ! $header_rendered ) :
	$header_classes = array( 'site-header' );
	if ( fx_get_option( 'header_sticky' ) ) {
		$header_classes[] = 'is-sticky';
	}
	if ( is_front_page() && fx_get_option( 'show_hero', 1 ) ) {
		$header_classes[] = 'is-transparent';
	}
	$header_class = implode( ' ', array_map( 'sanitize_html_class', $header_classes ) );
	$logo         = fx_get_option( 'logo' );
	$logo_dark    = fx_get_option( 'logo_dark', $logo );
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
			<nav class="secondary-navigation" aria-label="<?php esc_attr_e( 'Secondary menu', 'fx' ); ?>">
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
			<nav class="fx-primary-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'fx' ); ?>">
				<?php
				$args = array(
                    'theme_location' => 'primary',
                    'menu_id'        => 'primary-menu',
                    'container'      => false,
                );
                if ( function_exists( 'fx_features_enabled' ) && fx_features_enabled() ) {
                    $args['walker'] = new FX_Mega_Menu_Walker();
                }
                wp_nav_menu( $args );
                ?>
			</nav>
		<?php endif; ?>
		<button class="fx-menu-toggle" aria-controls="fx-mobile-menu" aria-expanded="false">
			<?php esc_html_e( 'Menu', 'fx' ); ?>
		</button>
		<button class="fx-search-toggle" aria-controls="fx-search-modal" aria-expanded="false">
			<?php esc_html_e( 'Search', 'fx' ); ?>
		</button>
		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<?php fx_header_cart(); ?>
		<?php endif; ?>
	</div>
</header>
<?php endif; ?>
<div id="fx-mobile-menu" class="fx-mobile-menu fx-off-canvas" aria-hidden="true">
	<button class="fx-close-menu" aria-label="<?php esc_attr_e( 'Close menu', 'fx' ); ?>">&times;</button>
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'menu_id'        => 'fx-mobile-menu-list',
			'container'      => false,
		)
	);
	if ( has_nav_menu( 'secondary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'secondary',
				'menu_id'        => 'fx-mobile-secondary-menu',
				'container'      => false,
			)
		);
	}
	?>
</div>
<div id="fx-search-modal" class="fx-search-modal" aria-hidden="true">
	<button class="fx-close-search" aria-label="<?php esc_attr_e( 'Close search', 'fx' ); ?>">&times;</button>
	<?php get_search_form(); ?>
</div>