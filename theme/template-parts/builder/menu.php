<?php
/**
 * Menu element for builder layouts.
 *
 * @package FortiveaX
 */

wp_nav_menu(
    array(
        'theme_location' => 'primary',
        'container'      => false,
        'menu_class'     => 'fx-builder-menu',
    )
);