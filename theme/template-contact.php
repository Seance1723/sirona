<?php
/**
 * Template Name: Contact
 */
get_header();
?>

<main id="primary" class="site-main contact-page">
    <?php while ( have_posts() ) : the_post(); ?>
        <?php the_content(); ?>
    <?php endwhile; ?>

    <form id="fx-contact-form" class="contact-form">
        <p>
            <label for="contact-name"><?php esc_html_e( 'Name', 'fx' ); ?></label>
            <input type="text" id="contact-name" name="name" required>
        </p>
        <p>
            <label for="contact-email"><?php esc_html_e( 'Email', 'fx' ); ?></label>
            <input type="email" id="contact-email" name="email" required>
        </p>
        <p>
            <label for="contact-message"><?php esc_html_e( 'Message', 'fx' ); ?></label>
            <textarea id="contact-message" name="message" required></textarea>
        </p>
        <?php wp_nonce_field( 'fx_contact', 'fx_contact_nonce' ); ?>
        <p><button type="submit"><?php esc_html_e( 'Send', 'fx' ); ?></button></p>
    </form>

    <div id="fx-contact-toast" class="fx-contact-toast" role="alert" hidden></div>
</main>

<?php
get_footer();
?>