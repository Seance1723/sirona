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
    return 'footer' === $type ? 'fx_footer_layouts' : 'fx_header_layouts';
}

/**
 * Retrieve layouts for a type.
 *
 * @param string $type Layout type.
 * @return array
 */
function fx_hf_get_layouts( $type ) {
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
    $layouts         = fx_hf_get_layouts( $type );
    $layouts[ $slug ] = $layout;
    update_option( fx_hf_option_name( $type ), $layouts );
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