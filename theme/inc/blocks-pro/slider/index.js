import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InnerBlocks,
    InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
    VisibilityControls,
    gsapAttributes,
    useThemePalette,
} from '../utils';
import './style.scss';

const ALLOWED_BLOCKS = [ 'core/paragraph', 'core/image', 'core/heading' ];

registerBlockType( 'fortiveax/slider', {
    edit( { attributes, setAttributes } ) {
        const { autoplay, loop, arrows, dots, animation } = attributes;
        const blockProps = useBlockProps( {
            className: [
                arrows ? 'has-arrows' : '',
                dots ? 'has-dots' : '',
            ].join( ' ' ),
            ...gsapAttributes( { animation } ),
        } );

        // Palette hook example usage
        useThemePalette();

        return (
            <>
                <VisibilityControls
                    attributes={ attributes }
                    setAttributes={ setAttributes }
                />
                <InspectorControls>
                    <PanelBody title={ __( 'Slider Settings', 'fortiveax' ) }>
                        <ToggleControl
                            label={ __( 'Autoplay', 'fortiveax' ) }
                            checked={ autoplay }
                            onChange={ ( value ) =>
                                setAttributes( { autoplay: value } )
                            }
                        />
                        <ToggleControl
                            label={ __( 'Loop', 'fortiveax' ) }
                            checked={ loop }
                            onChange={ ( value ) =>
                                setAttributes( { loop: value } )
                            }
                        />
                        <ToggleControl
                            label={ __( 'Show arrows', 'fortiveax' ) }
                            checked={ arrows }
                            onChange={ ( value ) =>
                                setAttributes( { arrows: value } )
                            }
                        />
                        <ToggleControl
                            label={ __( 'Show dots', 'fortiveax' ) }
                            checked={ dots }
                            onChange={ ( value ) =>
                                setAttributes( { dots: value } )
                            }
                        />
                        <SelectControl
                            label={ __( 'Animation', 'fortiveax' ) }
                            value={ animation }
                            options={ [
                                { label: __( 'None', 'fortiveax' ), value: 'none' },
                                { label: __( 'Fade In', 'fortiveax' ), value: 'fadeIn' },
                                { label: __( 'Slide Up', 'fortiveax' ), value: 'slideUp' },
                            ] }
                            onChange={ ( value ) =>
                                setAttributes( { animation: value } )
                            }
                        />
                    </PanelBody>
                </InspectorControls>
                <div { ...blockProps }>
                    <InnerBlocks
                        allowedBlocks={ ALLOWED_BLOCKS }
                        orientation="horizontal"
                    />
                </div>
            </>
        );
    },
    save( { attributes } ) {
        const { arrows, dots, animation } = attributes;
        const blockProps = useBlockProps.save( {
            className: [
                arrows ? 'has-arrows' : '',
                dots ? 'has-dots' : '',
            ].join( ' ' ),
            ...gsapAttributes( { animation } ),
        } );
        return (
            <div { ...blockProps } data-scroll-snap="true">
                <InnerBlocks.Content />
            </div>
        );
    },
} );