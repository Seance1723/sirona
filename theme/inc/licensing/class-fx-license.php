<?php
/**
 * License handling for FortiveaX.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple license management.
 */
class FX_License {

	/**
	 * Activate the license.
	 *
	 * @param string $key License key.
	 * @return bool
	 */
	public function activate( $key ) {
		$key = sanitize_text_field( $key );
		update_option( 'fx_license_key', $key );
		return true;
	}

	/**
	 * Deactivate the license.
	 *
	 * @return void
	 */
	public function deactivate() {
		delete_option( 'fx_license_key' );
	}

	/**
	 * Determine if the license is active.
	 *
	 * @return bool
	 */
	public function is_active() {
		return (bool) get_option( 'fx_license_key' );
	}

	/**
	 * Get the license status string.
	 *
	 * @return string
	 */
	public function status() {
		return $this->is_active() ? 'active' : 'inactive';
	}

	/**
	 * Check remote server for license validity.
	 *
	 * @return bool
	 */
	public function check_remote() {
		// Placeholder for remote check.
		return true;
	}

	/**
	 * Schedule cron event for remote checks.
	 *
	 * @return void
	 */
	public function schedule_cron() {
		if ( ! wp_next_scheduled( 'fx_check_license' ) ) {
			wp_schedule_event( time(), 'daily', 'fx_check_license' );
		}
	}
}