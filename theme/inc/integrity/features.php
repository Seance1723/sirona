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
 * Determine if features are enabled.
 *
 * Declared only if the MU plugin didn't already provide it.
 * Matches the zero-argument usage across the theme.
 *
 * @return bool
 */
if ( ! function_exists( 'fx_features_enabled' ) ) {
        function fx_features_enabled() {
                $enabled = function_exists( 'fx_license' ) ? fx_license()->is_active() : false;

                /**
                 * Filter the feature enabled state.
                 *
                 * @param bool        $enabled Whether the feature is enabled.
                 * @param string|null $feature Optional feature slug (unused here).
                 */
                return apply_filters( 'fx_features_enabled', $enabled, null );
        }
}
