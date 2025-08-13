import { registerBlockType } from '@wordpress/blocks';
import { SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType( 'fortiveax/global-element', {
    edit( { attributes, setAttributes } ) {
        const globals = useSelect( ( select ) => select( 'core' ).getEntityRecords( 'postType', 'fx_global', { per_page: -1 } ), [] ) || [];
        const options = [ { label: 'Select Global', value: 0 }, ...globals.map( ( g ) => ( { label: g.title.rendered, value: g.id } ) ) ];
        return (
            <div { ...useBlockProps() }>
                <SelectControl
                    label="Global Element"
                    value={ attributes.id }
                    options={ options }
                    onChange={ ( value ) => setAttributes( { id: parseInt( value, 10 ) } ) }
                />
            </div>
        );
    },
    save() {
        return null;
    },
} );