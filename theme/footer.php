<?php
/**
 * Footer template.
 *
 * @package FortiveaX
 */
?>
<footer class="site-footer">
    <?php if ( has_nav_menu( 'footer' ) ) : ?>
        <nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer menu', 'fortiveax' ); ?>">
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'footer',
                    'menu_id'        => 'footer-menu',
                    'container'      => false,
                )
            );
            ?>
        </nav>
    <?php endif; ?>
    <?php
    $social_links_raw = fxo( 'social_links', '' );
    $social_links     = array();
    if ( ! empty( $social_links_raw ) ) {
        $lines = array_map( 'trim', explode( "\n", $social_links_raw ) );
        foreach ( $lines as $line ) {
            if ( strpos( $line, '|' ) !== false ) {
                list( $network, $url ) = array_map( 'trim', explode( '|', $line, 2 ) );
                if ( $network && $url ) {
                    $social_links[ sanitize_key( $network ) ] = esc_url_raw( $url );
                }
            }
        }
    }
    ?>
    <?php if ( ! empty( $social_links ) ) : ?>
        <ul class="social-links">
            <?php foreach ( $social_links as $network => $url ) : ?>
                <li class="social-link social-<?php echo esc_attr( $network ); ?>">
                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
                        <?php echo esc_html( ucfirst( $network ) ); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <p class="site-info">
        <?php
        $footer_text = fxo( 'footer_text', '' );
        if ( ! empty( $footer_text ) ) {
            echo wp_kses_post( $footer_text );
        } else {
            echo '&copy; ' . esc_html( date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) );
        }
        ?>
    </p>
</footer>
<?php wp_footer(); ?>
</body>
</html>