<?php
/**
 * Logo element for builder layouts.
 *
 * @package FortiveaX
 */

if ( has_custom_logo() ) {
    the_custom_logo();
} else {
    echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="site-title">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
}