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
function sirona_demo_import_admin_menu() {
    add_theme_page(
        __( 'Demo Import', 'sirona' ),
        __( 'Demo Import', 'sirona' ),
        'manage_options',
        'sirona-demo-import',
        'sirona_demo_import_admin_page'
    );
}
add_action( 'admin_menu', 'sirona_demo_import_admin_menu' );

/**
 * Render the Demo Import admin page.
 */
function sirona_demo_import_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_POST['sirona_import_demo'] ) && check_admin_referer( 'sirona_import_demo_action', 'sirona_import_demo_nonce' ) ) {
        $imported = sirona_import_demo_data();
        echo '<div class="updated"><p>' . esc_html__( 'Demo data imported.', 'sirona' ) . '</p></div>';
    }

    if ( isset( $_POST['sirona_reset_demo'] ) && check_admin_referer( 'sirona_reset_demo_action', 'sirona_reset_demo_nonce' ) ) {
        sirona_reset_demo_data();
        echo '<div class="updated"><p>' . esc_html__( 'Demo data removed.', 'sirona' ) . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Demo Import', 'sirona' ); ?></h1>
        <?php if ( get_option( 'sirona_demo_imported' ) ) : ?>
            <p><?php esc_html_e( 'Demo data has already been imported.', 'sirona' ); ?></p>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field( 'sirona_import_demo_action', 'sirona_import_demo_nonce' ); ?>
            <p><input type="submit" class="button button-primary" name="sirona_import_demo" value="<?php esc_attr_e( 'Import Demo Data', 'sirona' ); ?>" /></p>
        </form>
        <form method="post" style="margin-top:2em;">
            <?php wp_nonce_field( 'sirona_reset_demo_action', 'sirona_reset_demo_nonce' ); ?>
            <p><input type="submit" class="button" name="sirona_reset_demo" value="<?php esc_attr_e( 'Reset to Defaults', 'sirona' ); ?>" /></p>
        </form>
    </div>
    <?php
}

/**
 * Import demo data from the bundled demo pack.
 *
 * @return bool True on success.
 */
function sirona_import_demo_data() {
    if ( get_option( 'sirona_demo_imported' ) ) {
        return false; // Already imported.
    }

    $demo_dir = trailingslashit( get_template_directory() ) . 'demo-packs/default/';
    $content_file    = $demo_dir . 'content.xml';
    $widgets_file    = $demo_dir . 'widgets.wie';
    $customizer_file = $demo_dir . 'customizer.json';

    $imported_posts = array();

    // Import content from WXR file.
    if ( file_exists( $content_file ) ) {
        $xml = simplexml_load_file( $content_file );
        if ( $xml && isset( $xml->channel->item ) ) {
            foreach ( $xml->channel->item as $item ) {
                $wp = $item->children( 'http://wordpress.org/export/1.2/' );
                $content = $item->children( 'http://purl.org/rss/1.0/modules/content/' );
                $post_type = (string) $wp->post_type;
                $post_name = (string) $wp->post_name;
                $post_title = (string) $item->title;
                $post_content = (string) $content->encoded;

                if ( 'post' !== $post_type && 'page' !== $post_type ) {
                    continue; // Only basic posts/pages.
                }

                // Skip if a post with this name already exists (idempotent).
                if ( get_page_by_path( $post_name, OBJECT, $post_type ) ) {
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
                    add_post_meta( $post_id, '_sirona_demo', 1 );
                    $imported_posts[] = $post_id;
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
                        $option_name = 'widget_' . $widget_base_id;
                        $all_instances = get_option( $option_name, array() );
                        $all_instances[] = $settings + array( '_sirona_demo' => 1 );
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

    update_option( 'sirona_demo_imported', 1 );
    update_option( 'sirona_imported_posts', $imported_posts );

    return true;
}

/**
 * Remove all imported demo data and reset theme mods.
 */
function sirona_reset_demo_data() {
    $imported_posts = get_option( 'sirona_imported_posts', array() );
    if ( $imported_posts ) {
        foreach ( $imported_posts as $post_id ) {
            wp_delete_post( $post_id, true );
        }
    }
    delete_option( 'sirona_imported_posts' );

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
            if ( isset( $instances[ $instance ]['_sirona_demo'] ) ) {
                unset( $instances[ $instance ] );
                unset( $sidebars_widgets[ $sidebar_id ][ $key ] );
                update_option( $option_name, $instances );
            }
        }
    }
    update_option( 'sidebars_widgets', $sidebars_widgets );

    // Reset customizer options.
    remove_theme_mods();
    delete_option( 'sirona_demo_imported' );
}