import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl } from '@wordpress/components';

registerBlockType( 'fx/animated-box', {
    edit( { attributes, setAttributes } ) {
        const { content, animation, hideDesktop, hideTablet, hideMobile } = attributes;
        const blockProps = useBlockProps( {
            className: [
                hideDesktop ? 'fx-hide-desktop' : '',
                hideTablet ? 'fx-hide-tablet' : '',
                hideMobile ? 'fx-hide-mobile' : '',
            ].join( ' ' ),
            'data-animation': animation !== 'none' ? animation : undefined,
        } );

        return (
            <>
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
                    <PanelBody title={ __( 'Animation', 'fx' ) } initialOpen={ false }>
                        <SelectControl
                            label={ __( 'Animation', 'fx' ) }
                            value={ animation }
                            options={ [
                                { label: __( 'None', 'fx' ), value: 'none' },
                                { label: __( 'Fade In', 'fx' ), value: 'fadeIn' },
                                { label: __( 'Slide Up', 'fx' ), value: 'slideUp' },
                            ] }
                            onChange={ ( value ) => setAttributes( { animation: value } ) }
                        />
                    </PanelBody>
                </InspectorControls>
                <RichText
                    { ...blockProps }
                    tagName="div"
                    value={ content }
                    allowedFormats={ [] }
                    onChange={ ( value ) => setAttributes( { content: value } ) }
                    placeholder={ __( 'Add content…', 'fx' ) }
                />
            </>
        );
    },
    save() {
        return null;
    },
} );