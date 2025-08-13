import { useSelect } from '@wordpress/data';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Hook to read theme palette from localized data
export const useThemePalette = () => {
    return useSelect( () => {
        const palette = window?.fxTheme?.colors || {};
        return palette;
    }, [] );
};

// Visibility controls for desktop, tablet, mobile
export const VisibilityControls = ( { attributes, setAttributes } ) => {
    const { hideDesktop, hideTablet, hideMobile } = attributes;
    return (
        <InspectorControls>
            <PanelBody title={ __( 'Visibility', 'fx' ) }>
                <ToggleControl
                    label={ __( 'Hide on desktop', 'fx' ) }
                    checked={ hideDesktop }
                    onChange={ ( value ) => setAttributes( { hideDesktop: value } ) }
                />
                <ToggleControl
                    label={ __( 'Hide on tablet', 'fx' ) }
                    checked={ hideTablet }
                    onChange={ ( value ) => setAttributes( { hideTablet: value } ) }
                />
                <ToggleControl
                    label={ __( 'Hide on mobile', 'fx' ) }
                    checked={ hideMobile }
                    onChange={ ( value ) => setAttributes( { hideMobile: value } ) }
                />
            </PanelBody>
        </InspectorControls>
    );
};

// Helper to apply GSAP data attributes
export const gsapAttributes = ( { animation } ) => {
    return animation && animation !== 'none'
        ? { 'data-animation': animation }
        : {};
};