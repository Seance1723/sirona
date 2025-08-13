import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';
import {
    PanelBody,
    SelectControl,
    RangeControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import {
    VisibilityControls,
    gsapAttributes,
    useThemePalette,
} from '../utils';
import './style.scss';

registerBlockType( 'fortiveax/post-grid', {
    edit( { attributes, setAttributes } ) {
        const {
            postType,
            order,
            orderBy,
            postsPerPage,
            pagination,
            animation,
        } = attributes;
        const blockProps = useBlockProps( {
            ...gsapAttributes( { animation } ),
        } );

        // Load post types for control
        const postTypes =
            useSelect( ( select ) =>
                select( 'core' ).getPostTypes( { per_page: -1 } ),
            [],
            ) || [];
        const postTypeOptions = postTypes
            .filter( ( p ) => p.viewable )
            .map( ( p ) => ( { label: p.name, value: p.slug } ) );

        useThemePalette();

        return (
            <>
                <VisibilityControls
                    attributes={ attributes }
                    setAttributes={ setAttributes }
                />
                <InspectorControls>
                    <PanelBody title={ __( 'Query', 'fortiveax' ) }>
                        <SelectControl
                            label={ __( 'Post Type', 'fortiveax' ) }
                            value={ postType }
                            options={ postTypeOptions }
                            onChange={ ( value ) =>
                                setAttributes( { postType: value } )
                            }
                        />
                        <RangeControl
                            label={ __( 'Posts Per Page', 'fortiveax' ) }
                            value={ postsPerPage }
                            onChange={ ( value ) =>
                                setAttributes( { postsPerPage: value } )
                            }
                            min={ 1 }
                            max={ 20 }
                        />
                        <SelectControl
                            label={ __( 'Order', 'fortiveax' ) }
                            value={ order }
                            options={ [
                                { label: __( 'Ascending', 'fortiveax' ), value: 'ASC' },
                                { label: __( 'Descending', 'fortiveax' ), value: 'DESC' },
                            ] }
                            onChange={ ( value ) =>
                                setAttributes( { order: value } )
                            }
                        />
                        <SelectControl
                            label={ __( 'Order By', 'fortiveax' ) }
                            value={ orderBy }
                            options={ [
                                { label: __( 'Date', 'fortiveax' ), value: 'date' },
                                { label: __( 'Title', 'fortiveax' ), value: 'title' },
                            ] }
                            onChange={ ( value ) =>
                                setAttributes( { orderBy: value } )
                            }
                        />
                        <SelectControl
                            label={ __( 'Pagination', 'fortiveax' ) }
                            value={ pagination }
                            options={ [
                                { label: __( 'None', 'fortiveax' ), value: 'none' },
                                { label: __( 'Ajax', 'fortiveax' ), value: 'ajax' },
                                { label: __( 'Infinite Scroll', 'fortiveax' ), value: 'infinite' },
                            ] }
                            onChange={ ( value ) =>
                                setAttributes( { pagination: value } )
                            }
                        />
                    </PanelBody>
                </InspectorControls>
                <div { ...blockProps }>
                    <ServerSideRender
                        block="fortiveax/post-grid"
                        attributes={ attributes }
                    />
                </div>
            </>
        );
    },
    save() {
        return null;
    },
} );