<?php
/**
 * Licensing helpers for FortiveaX.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-fx-license.php';

/**
 * Retrieve the license instance.
 *
 * @return FX_License
 */
function fx_license() {
	static $instance = null;
	if ( null === $instance ) {
		$instance = new FX_License();
	}
	return $instance;
}

add_action( 'fx_check_license', array( fx_license(), 'check_remote' ) );