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

registerBlockType( 'fx/post-grid', {
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
                    <PanelBody title={ __( 'Query', 'fx' ) }>
                        <SelectControl
                            label={ __( 'Post Type', 'fx' ) }
                            value={ postType }
                            options={ postTypeOptions }
                            onChange={ ( value ) =>
                                setAttributes( { postType: value } )
                            }
                        />
                        <RangeControl
                            label={ __( 'Posts Per Page', 'fx' ) }
                            value={ postsPerPage }
                            onChange={ ( value ) =>
                                setAttributes( { postsPerPage: value } )
                            }
                            min={ 1 }
                            max={ 20 }
                        />
                        <SelectControl
                            label={ __( 'Order', 'fx' ) }
                            value={ order }
                            options={ [
                                { label: __( 'Ascending', 'fx' ), value: 'ASC' },
                                { label: __( 'Descending', 'fx' ), value: 'DESC' },
                            ] }
                            onChange={ ( value ) =>
                                setAttributes( { order: value } )
                            }
                        />
                        <SelectControl
                            label={ __( 'Order By', 'fx' ) }
                            value={ orderBy }
                            options={ [
                                { label: __( 'Date', 'fx' ), value: 'date' },
                                { label: __( 'Title', 'fx' ), value: 'title' },
                            ] }
                            onChange={ ( value ) =>
                                setAttributes( { orderBy: value } )
                            }
                        />
                        <SelectControl
                            label={ __( 'Pagination', 'fx' ) }
                            value={ pagination }
                            options={ [
                                { label: __( 'None', 'fx' ), value: 'none' },
                                { label: __( 'Ajax', 'fx' ), value: 'ajax' },
                                { label: __( 'Infinite Scroll', 'fx' ), value: 'infinite' },
                            ] }
                            onChange={ ( value ) =>
                                setAttributes( { pagination: value } )
                            }
                        />
                    </PanelBody>
                </InspectorControls>
                <div { ...blockProps }>
                    <ServerSideRender
                        block="fx/post-grid"
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