<?php
/**
 * Mega menu fields for nav menu items.
 */

// Display custom fields in menu item editor.
function fortiveax_megamenu_fields( $item_id, $item ) {
    $enabled   = get_post_meta( $item_id, '_fx_mega_enabled', true );
    $cols      = get_post_meta( $item_id, '_fx_mega_cols', true );
    $bg_color  = get_post_meta( $item_id, '_fx_mega_bg_color', true );
    $bg_image  = get_post_meta( $item_id, '_fx_mega_bg_image', true );
    $width     = get_post_meta( $item_id, '_fx_mega_width', true );
    $custom    = get_post_meta( $item_id, '_fx_mega_custom', true );
    ?>
    <p class="field-enable-mega description description-wide">
        <label for="edit-fx-mega-enabled-<?php echo esc_attr( $item_id ); ?>">
            <input type="checkbox" id="edit-fx-mega-enabled-<?php echo esc_attr( $item_id ); ?>" name="fx_mega_enabled[<?php echo esc_attr( $item_id ); ?>]" value="1" <?php checked( $enabled, '1' ); ?> />
            <?php esc_html_e( 'Enable Mega Menu', 'fortiveax' ); ?>
        </label>
    </p>
    <p class="field-mega-cols description description-wide">
        <label for="edit-fx-mega-cols-<?php echo esc_attr( $item_id ); ?>">
            <?php esc_html_e( 'Columns', 'fortiveax' ); ?>
            <select id="edit-fx-mega-cols-<?php echo esc_attr( $item_id ); ?>" name="fx_mega_cols[<?php echo esc_attr( $item_id ); ?>]">
                <?php for ( $i = 2; $i <= 6; $i++ ) : ?>
                    <option value="<?php echo esc_attr( $i ); ?>" <?php selected( $cols, $i ); ?>><?php echo esc_html( $i ); ?></option>
                <?php endfor; ?>
            </select>
        </label>
    </p>
    <p class="field-mega-bg-color description description-wide">
        <label for="edit-fx-mega-bg-color-<?php echo esc_attr( $item_id ); ?>">
            <?php esc_html_e( 'Background Color', 'fortiveax' ); ?>
            <input type="text" id="edit-fx-mega-bg-color-<?php echo esc_attr( $item_id ); ?>" name="fx_mega_bg_color[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $bg_color ); ?>" class="widefat" />
        </label>
    </p>
    <p class="field-mega-bg-image description description-wide">
        <label for="edit-fx-mega-bg-image-<?php echo esc_attr( $item_id ); ?>">
            <?php esc_html_e( 'Background Image URL', 'fortiveax' ); ?>
            <input type="text" id="edit-fx-mega-bg-image-<?php echo esc_attr( $item_id ); ?>" name="fx_mega_bg_image[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $bg_image ); ?>" class="widefat" />
        </label>
    </p>
    <p class="field-mega-width description description-wide">
        <label for="edit-fx-mega-width-<?php echo esc_attr( $item_id ); ?>">
            <?php esc_html_e( 'Panel Width (e.g. 800px)', 'fortiveax' ); ?>
            <input type="text" id="edit-fx-mega-width-<?php echo esc_attr( $item_id ); ?>" name="fx_mega_width[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $width ); ?>" class="widefat" />
        </label>
    </p>
    <p class="field-mega-custom description description-wide">
        <label for="edit-fx-mega-custom-<?php echo esc_attr( $item_id ); ?>">
            <?php esc_html_e( 'Custom HTML', 'fortiveax' ); ?>
            <textarea id="edit-fx-mega-custom-<?php echo esc_attr( $item_id ); ?>" name="fx_mega_custom[<?php echo esc_attr( $item_id ); ?>]" class="widefat" rows="3"><?php echo esc_textarea( $custom ); ?></textarea>
        </label>
    </p>
    <?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'fortiveax_megamenu_fields', 10, 2 );

// Save meta on menu item update.
function fortiveax_save_megamenu_fields( $menu_id, $menu_item_db_id ) {
    $fields = array(
        'fx_mega_enabled' => '_fx_mega_enabled',
        'fx_mega_cols'    => '_fx_mega_cols',
        'fx_mega_bg_color'=> '_fx_mega_bg_color',
        'fx_mega_bg_image'=> '_fx_mega_bg_image',
        'fx_mega_width'   => '_fx_mega_width',
        'fx_mega_custom'  => '_fx_mega_custom',
    );
    foreach ( $fields as $post_key => $meta_key ) {
        $value = isset( $_POST[ $post_key ][ $menu_item_db_id ] ) ? $_POST[ $post_key ][ $menu_item_db_id ] : '';
        if ( $value !== '' ) {
            update_post_meta( $menu_item_db_id, $meta_key, $value );
        } else {
            delete_post_meta( $menu_item_db_id, $meta_key );
        }
    }
}
add_action( 'wp_update_nav_menu_item', 'fortiveax_save_megamenu_fields', 10, 2 );