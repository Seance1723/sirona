/**
 * Render callback for Global Element block.
 */
if ( empty( $attributes['id'] ) ) {
    return '';
}
return '<!--fx_global:' . intval( $attributes['id'] ) . '-->';