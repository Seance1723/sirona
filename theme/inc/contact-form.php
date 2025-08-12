<?php
/**
 * AJAX handler for the built-in contact form.
 */

function fortiveax_handle_contact() {
    if ( ! isset( $_POST['fortiveax_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fortiveax_contact_nonce'] ) ), 'fortiveax_contact' ) ) {
        wp_send_json_error( __( 'Invalid form submission.', 'fortiveax' ) );
    }

    $name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
    $email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

    if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
        wp_send_json_error( __( 'All fields are required.', 'fortiveax' ) );
    }

    $admin_email = get_option( 'admin_email' );
    $subject     = sprintf( __( 'Contact form message from %s', 'fortiveax' ), $name );
    $body        = sprintf( "Name: %s\nEmail: %s\n\n%s", $name, $email, $message );

    wp_mail( $admin_email, $subject, $body );

    wp_send_json_success(
        array(
            'message' => __( 'Thanks for contacting us!', 'fortiveax' ),
        )
    );
}
add_action( 'wp_ajax_fortiveax_contact_submit', 'fortiveax_handle_contact' );
add_action( 'wp_ajax_nopriv_fortiveax_contact_submit', 'fortiveax_handle_contact' );