<?php
/**
 * Demo Importer for Sirona theme.
 *
 * Provides admin UI to import demo data (content, widgets, customizer) and
 * reset to defaults. The importer is idempotent – running it multiple times
 * will not duplicate content.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register the Demo Import admin page.
 */
function fx_demo_import_admin_menu() {
    add_theme_page(
        __( 'Demo Import', 'fx' ),
        __( 'Demo Import', 'fx' ),
        'manage_options',
        'fx-demo-import',
        'fx_demo_import_admin_page'
    );
}
add_action( 'admin_menu', 'fx_demo_import_admin_menu' );

/**
 * Render the Demo Import admin page.
 */
function fx_demo_import_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $packs        = fx_get_demo_packs();
    $selected_pack = isset( $_POST['fx_demo_pack'] ) ? sanitize_text_field( wp_unslash( $_POST['fx_demo_pack'] ) ) : key( $packs );

    if ( isset( $_POST['fx_import_demo'] ) && check_admin_referer( 'fx_import_demo_action', 'fx_import_demo_nonce' ) ) {
        fx_import_demo_data( $selected_pack );
        echo '<div class="updated"><p>' . esc_html__( 'Demo data imported.', 'fx' ) . '</p></div>';
    }

    if ( isset( $_POST['fx_reset_demo'] ) && check_admin_referer( 'fx_reset_demo_action', 'fx_reset_demo_nonce' ) ) {
        fx_reset_demo_data();
        echo '<div class="updated"><p>' . esc_html__( 'Demo data removed.', 'fx' ) . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Demo Import', 'fx' ); ?></h1>
        <?php if ( fx_has_imported_demo() ) : ?>
            <p><?php esc_html_e( 'Demo data has already been imported. You can safely re-run the importer; existing content will be skipped.', 'fx' ); ?></p>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field( 'fx_import_demo_action', 'fx_import_demo_nonce' ); ?>
            <p>
                <label for="fx_demo_pack"><?php esc_html_e( 'Demo Pack', 'fx' ); ?></label>
                <select name="fx_demo_pack" id="fx_demo_pack">
                    <?php foreach ( $packs as $slug => $label ) : ?>
                        <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $selected_pack, $slug ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p><input type="submit" class="button button-primary" name="fx_import_demo" value="<?php esc_attr_e( 'Import Demo Data', 'fx' ); ?>" /></p>
        </form>
        <form method="post" style="margin-top:2em;">
            <?php wp_nonce_field( 'fx_reset_demo_action', 'fx_reset_demo_nonce' ); ?>
            <p><input type="submit" class="button" name="fx_reset_demo" value="<?php esc_attr_e( 'Reset to Defaults', 'fx' ); ?>" /></p>
        </form>
    </div>
    <?php
}

/**
 * Get available demo packs.
 *
 * @return array slug => label pairs.
 */
function fx_get_demo_packs() {
    $packs   = array();
    $base    = trailingslashit( get_theme_file_path( 'demo-packs' ) );
    $folders = glob( $base . '*', GLOB_ONLYDIR );
    if ( $folders ) {
        foreach ( $folders as $folder ) {
            $slug        = basename( $folder );
            $packs[ $slug ] = ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
        }
    }
    return $packs;
}

    /**
 * Check if demo content has been imported.
 *
 * @return bool
 */
function fx_has_imported_demo() {
    $posts = get_posts(
        array(
            'post_type'      => 'any',
            'meta_key'       => '_fx_demo',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        )
    );

    return ! empty( $posts );
}

/**
 * Import demo data from the bundled demo pack.
 *
 * @return bool True on success.
 */
function fx_import_demo_data( $pack = 'default' ) {
    $demo_dir       = trailingslashit( get_theme_file_path( 'demo-packs/' . $pack ) );
    $content_file    = $demo_dir . 'content.xml';
    $widgets_file    = $demo_dir . 'widgets.wie';
    $customizer_file = $demo_dir . 'customizer.json';

    // Import content from WXR file.
    if ( file_exists( $content_file ) ) {
        $xml = simplexml_load_file( $content_file );
        if ( $xml && isset( $xml->channel->item ) ) {
            foreach ( $xml->channel->item as $item ) {
                $wp = $item->children( 'http://wordpress.org/export/1.2/' );
                $content = $item->children( 'http://purl.org/rss/1.0/modules/content/' );
                $post_type    = (string) $wp->post_type;
                $post_name    = (string) $wp->post_name;
                $post_title   = (string) $item->title;
                $post_content = (string) $content->encoded;

                // Skip if a post with this slug already exists.
                $exists = get_posts(
                    array(
                        'name'           => $post_name,
                        'post_type'      => $post_type,
                        'post_status'    => 'any',
                        'fields'         => 'ids',
                        'posts_per_page' => 1,
                    )
                );
                if ( $exists ) {
                    continue;
                }

                $post_id = wp_insert_post(
                    array(
                        'post_type'   => $post_type,
                        'post_name'   => $post_name,
                        'post_title'  => $post_title,
                        'post_status' => 'publish',
                        'post_content'=> wp_kses_post( $post_content ),
                    ),
                    true
                );

                if ( ! is_wp_error( $post_id ) ) {
                    add_post_meta( $post_id, '_fx_demo', 1 );
                }
            }
        }
    }

    // Import widgets from WIE (JSON) file.
    if ( file_exists( $widgets_file ) ) {
        $widgets = json_decode( file_get_contents( $widgets_file ), true );
        if ( is_array( $widgets ) ) {
            foreach ( $widgets as $sidebar_id => $widget_types ) {
                foreach ( $widget_types as $widget_base_id => $settings_list ) {
                    foreach ( $settings_list as $settings ) {
                        $option_name    = 'widget_' . $widget_base_id;
                        $all_instances  = get_option( $option_name, array() );
                        $slug           = sanitize_title( isset( $settings['title'] ) ? $settings['title'] : $widget_base_id );

                        $exists = false;
                        foreach ( $all_instances as $instance ) {
                            if ( isset( $instance['_fx_demo_slug'] ) && $instance['_fx_demo_slug'] === $slug ) {
                                $exists = true;
                                break;
                            }
                        }
                        if ( $exists ) {
                            continue;
                        }

                        $settings['_fx_demo']      = 1;
                        $settings['_fx_demo_slug'] = $slug;
                        $all_instances[]           = $settings;
                        end( $all_instances );
                        $new_instance_id = key( $all_instances );
                        update_option( $option_name, $all_instances );

                        $sidebars_widgets = get_option( 'sidebars_widgets', array() );
                        if ( ! isset( $sidebars_widgets[ $sidebar_id ] ) ) {
                            $sidebars_widgets[ $sidebar_id ] = array();
                        }
                        $sidebars_widgets[ $sidebar_id ][] = $widget_base_id . '-' . $new_instance_id;
                        update_option( 'sidebars_widgets', $sidebars_widgets );
                    }
                }
            }
        }
    }

    // Import customizer settings.
    if ( file_exists( $customizer_file ) ) {
        $data = json_decode( file_get_contents( $customizer_file ), true );
        if ( isset( $data['theme_mods'] ) && is_array( $data['theme_mods'] ) ) {
            foreach ( $data['theme_mods'] as $mod => $val ) {
                set_theme_mod( $mod, $val );
            }
        }
    }

    return true;
}

/**
 * Remove all imported demo data and reset theme mods.
 */
function fx_reset_demo_data() {
    $posts = get_posts(
        array(
            'post_type'      => 'any',
            'meta_key'       => '_fx_demo',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        )
    );

    foreach ( $posts as $post_id ) {
        wp_delete_post( $post_id, true );
    }

    // Remove widgets that were flagged as demo widgets.
    $sidebars_widgets = get_option( 'sidebars_widgets', array() );
    foreach ( $sidebars_widgets as $sidebar_id => $widget_ids ) {
        if ( ! is_array( $widget_ids ) ) {
            continue;
        }
        foreach ( $widget_ids as $key => $widget_id ) {
            list( $base, $instance ) = array_pad( explode( '-', $widget_id ), 2, null );
            $option_name = 'widget_' . $base;
            $instances = get_option( $option_name, array() );
            if ( isset( $instances[ $instance ]['_fx_demo'] ) ) {
                unset( $instances[ $instance ] );
                unset( $sidebars_widgets[ $sidebar_id ][ $key ] );
                update_option( $option_name, $instances );
            }
        }
    }
    update_option( 'sidebars_widgets', $sidebars_widgets );

    // Reset customizer options.
    remove_theme_mods();
}