<?php
/**
 * Storage helpers for header/footer builder.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get option name for type.
 *
 * @param string $type Layout type.
 * @return string
 */
function fx_hf_option_name( $type ) {
    $type = sanitize_key( $type );

    return 'footer' === $type ? 'fx_footer_layouts' : 'fx_header_layouts';
}

/**
 * Retrieve layouts for a type.
 *
 * @param string $type Layout type.
 * @return array
 */
function fx_hf_get_layouts( $type ) {
    if ( ! function_exists( 'fx_features_enabled' ) || ! fx_features_enabled() ) {
        return array();
    }
    $type    = sanitize_key( $type );
    $option  = fx_hf_option_name( $type );
    $layouts = get_option( $option, array() );

    if ( empty( $layouts ) ) {
        $presets = fx_hf_default_presets();
        return isset( $presets[ $type ] ) ? $presets[ $type ] : array();
    }

    return $layouts;
}

/**
 * Retrieve single layout.
 *
 * @param string $type Layout type.
 * @param string $slug Variant slug.
 * @return array|null
 */
function fx_hf_get_layout( $type, $slug ) {
    if ( ! function_exists( 'fx_features_enabled' ) || ! fx_features_enabled() ) {
        return null;
    }
    $type    = sanitize_key( $type );
    $slug    = sanitize_key( $slug );
    $layouts = fx_hf_get_layouts( $type );
    return isset( $layouts[ $slug ] ) ? $layouts[ $slug ] : null;
}

/**
 * Save layout variant.
 *
 * @param string $type   Layout type.
 * @param string $slug   Variant slug.
 * @param array  $layout Layout data.
 */
function fx_hf_save_layout( $type, $slug, $layout ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( ! function_exists( 'fx_features_enabled' ) || ! fx_features_enabled() ) {
        return;
    }

    $type   = sanitize_key( $type );
    $slug   = sanitize_key( $slug );
    $layout = is_array( $layout ) ? fx_hf_sanitize_layout( $layout ) : array();

    $layouts          = fx_hf_get_layouts( $type );
    $layouts[ $slug ] = $layout;
    update_option( fx_hf_option_name( $type ), $layouts );
}

/**
 * Sanitize header/footer layout structure.
 *
 * @param array $layout Raw layout array.
 * @return array
 */
function fx_hf_sanitize_layout( $layout ) {
    $out = array(
        'sticky'      => ! empty( $layout['sticky'] ),
        'transparent' => ! empty( $layout['transparent'] ),
        'rows'        => array(),
    );

    if ( ! empty( $layout['rows'] ) && is_array( $layout['rows'] ) ) {
        foreach ( $layout['rows'] as $row ) {
            $row_out = array( 'cols' => array() );
            if ( ! empty( $row['cols'] ) && is_array( $row['cols'] ) ) {
                foreach ( $row['cols'] as $col ) {
                    $elements = array();
                    if ( ! empty( $col['elements'] ) && is_array( $col['elements'] ) ) {
                        foreach ( $col['elements'] as $el ) {
                            $elements[] = sanitize_key( $el );
                        }
                    }
                    $row_out['cols'][] = array( 'elements' => $elements );
                }
            }
            $out['rows'][] = $row_out;
        }
    }

    return $out;
}

/**
 * Default layout presets.
 *
 * @return array
 */
function fx_hf_default_presets() {
    return array(
        'header' => array(
            'classic' => array(
                'label'  => __( 'Classic', 'fx' ),
                'layout' => array(
                    'sticky'      => false,
                    'transparent' => false,
                    'rows'        => array(
                        array(
                            'cols' => array(
                                array( 'elements' => array( 'logo' ) ),
                                array( 'elements' => array( 'menu' ) ),
                            ),
                        ),
                    ),
                ),
            ),
            'centered' => array(
                'label'  => __( 'Centered', 'fx' ),
                'layout' => array(
                    'sticky'      => false,
                    'transparent' => false,
                    'rows'        => array(
                        array(
                            'cols' => array(
                                array( 'elements' => array( 'logo' ) ),
                            ),
                        ),
                        array(
                            'cols' => array(
                                array( 'elements' => array( 'menu' ) ),
                            ),
                        ),
                    ),
                ),
            ),
            'split' => array(
                'label'  => __( 'Split', 'fx' ),
                'layout' => array(
                    'sticky'      => false,
                    'transparent' => false,
                    'rows'        => array(
                        array(
                            'cols' => array(
                                array( 'elements' => array( 'menu' ) ),
                                array( 'elements' => array( 'logo' ) ),
                                array( 'elements' => array( 'menu' ) ),
                            ),
                        ),
                    ),
                ),
            ),
            'topbar' => array(
                'label'  => __( 'Topbar', 'fx' ),
                'layout' => array(
                    'sticky'      => false,
                    'transparent' => false,
                    'rows'        => array(
                        array(
                            'cols' => array(
                                array( 'elements' => array( 'menu' ) ),
                            ),
                        ),
                        array(
                            'cols' => array(
                                array( 'elements' => array( 'logo' ) ),
                                array( 'elements' => array( 'menu' ) ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'footer' => array(
            'classic' => array(
                'label'  => __( 'Classic', 'fx' ),
                'layout' => array(
                    'rows' => array(
                        array(
                            'cols' => array(
                                array( 'elements' => array( 'menu' ) ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );
}
