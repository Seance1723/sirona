import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl } from '@wordpress/components';

registerBlockType( 'fortiveax/animated-box', {
    edit( { attributes, setAttributes } ) {
        const { content, animation, hideDesktop, hideTablet, hideMobile } = attributes;
        const blockProps = useBlockProps( {
            className: [
                hideDesktop ? 'fortiveax-hide-desktop' : '',
                hideTablet ? 'fortiveax-hide-tablet' : '',
                hideMobile ? 'fortiveax-hide-mobile' : '',
            ].join( ' ' ),
            'data-animation': animation !== 'none' ? animation : undefined,
        } );

        return (
            <>
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
                    <PanelBody title={ __( 'Animation', 'fortiveax' ) } initialOpen={ false }>
                        <SelectControl
                            label={ __( 'Animation', 'fortiveax' ) }
                            value={ animation }
                            options={ [
                                { label: __( 'None', 'fortiveax' ), value: 'none' },
                                { label: __( 'Fade In', 'fortiveax' ), value: 'fadeIn' },
                                { label: __( 'Slide Up', 'fortiveax' ), value: 'slideUp' },
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
                    placeholder={ __( 'Add content…', 'fortiveax' ) }
                />
            </>
        );
    },
    save() {
        return null;
    },
} );