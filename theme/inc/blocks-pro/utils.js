import { useSelect } from '@wordpress/data';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Hook to read theme palette from localized data
export const useThemePalette = () => {
    return useSelect( () => {
        const palette = window?.fortiveaX?.colors || {};
        return palette;
    }, [] );
};

// Visibility controls for desktop, tablet, mobile
export const VisibilityControls = ( { attributes, setAttributes } ) => {
    const { hideDesktop, hideTablet, hideMobile } = attributes;
    return (
        <InspectorControls>
            <PanelBody title={ __( 'Visibility', 'fortiveax' ) }>
                <ToggleControl
                    label={ __( 'Hide on desktop', 'fortiveax' ) }
                    checked={ hideDesktop }
                    onChange={ ( value ) => setAttributes( { hideDesktop: value } ) }
                />
                <ToggleControl
                    label={ __( 'Hide on tablet', 'fortiveax' ) }
                    checked={ hideTablet }
                    onChange={ ( value ) => setAttributes( { hideTablet: value } ) }
                />
                <ToggleControl
                    label={ __( 'Hide on mobile', 'fortiveax' ) }
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