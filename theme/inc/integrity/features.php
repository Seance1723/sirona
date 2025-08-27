<?php
/**
 * Feature gating helpers.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine if a feature is enabled.
 *
 * @param string $feature Feature slug.
 * @return bool
 */
function fx_features_enabled( $feature ) {
	$enabled = fx_license()->is_active();

	/**
	 * Filter the feature enabled state.
	 *
	 * @param bool   $enabled Whether the feature is enabled.
	 * @param string $feature Feature slug.
	 */
	return apply_filters( 'fx_features_enabled', $enabled, $feature );
}