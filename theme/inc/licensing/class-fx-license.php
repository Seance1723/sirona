<?php
/**
 * License handling for FortiveaX.
 *
 * @package FortiveaX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple license management.
 */
class FX_License {
    const TOKEN_OPTION  = 'fortiveax_license_token';
    const STATUS_OPTION = 'fortiveax_license_status';
    const RATE_LIMIT    = 'fortiveax_license_rate';

    /**
     * Activate the license.
     *
     * @param array $creds Credentials array.
     * @return array|WP_Error License status or WP_Error.
     */
    public function activate( $creds ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'permission_denied', __( 'Insufficient permissions.', 'fx' ) );
        }

        if ( get_transient( self::RATE_LIMIT ) ) {
            return new WP_Error( 'rate_limited', __( 'Please wait before retrying.', 'fx' ) );
        }

        set_transient( self::RATE_LIMIT, time(), MINUTE_IN_SECONDS );

        $email    = isset( $creds['email'] ) ? sanitize_email( $creds['email'] ) : '';
        $password = isset( $creds['password'] ) ? (string) $creds['password'] : '';

        $body = array(
            'email'    => $email,
            'password' => $password,
            'site_url' => site_url(),
            'theme'    => 'fortiveax',
            'version'  => wp_get_theme()->get( 'Version' ),
        );

        $response = wp_remote_post(
            'https://licenses.example.com/api/login',
            array(
                'body'    => wp_json_encode( $body ),
                'headers' => array( 'Content-Type' => 'application/json' ),
                'timeout' => 15,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $data['token'] ) ) {
            return new WP_Error( 'login_invalid', __( 'Invalid email or password.', 'fx' ) );
        }

        if ( function_exists( 'fx_jwt_verify_rs256' ) && defined( 'FX_RSA_PUBLIC' ) ) {
            $claims = array(
                'iss' => defined( 'FX_JWT_ISS' ) ? FX_JWT_ISS : 'fortiveax',
                'aud' => defined( 'FX_JWT_AUD' ) ? FX_JWT_AUD : wp_parse_url( site_url(), PHP_URL_HOST ),
            );
            $verify = fx_jwt_verify_rs256( $data['token'], FX_RSA_PUBLIC, $claims );
            if ( is_wp_error( $verify ) ) {
                return $verify;
            }
        }

        update_option( self::TOKEN_OPTION, $data['token'] );

        if ( function_exists( 'fx_core_run_checks' ) ) {
            fx_core_run_checks();
        }

        // Persist minimal status fields only: plan, exp, last_check.
        $status = get_option( self::STATUS_OPTION, array() );
        $claims = array();
        if ( function_exists( 'fx_jwt_verify_rs256' ) && defined( 'FX_RSA_PUBLIC' ) ) {
            $claims = array(
                'iss' => defined( 'FX_JWT_ISS' ) ? FX_JWT_ISS : 'fortiveax',
                'aud' => defined( 'FX_JWT_AUD' ) ? FX_JWT_AUD : wp_parse_url( site_url(), PHP_URL_HOST ),
            );
        }
        if ( ! empty( $data['plan'] ) ) {
            $status['plan'] = sanitize_text_field( $data['plan'] );
        }
        // Try to decode or trust server-provided exp if present.
        if ( ! empty( $data['exp'] ) && is_numeric( $data['exp'] ) ) {
            $status['exp'] = (int) $data['exp'];
        } elseif ( ! empty( $claims ) && function_exists( 'fx_jwt_verify_rs256' ) ) {
            $verified = fx_jwt_verify_rs256( $data['token'], FX_RSA_PUBLIC, $claims );
            if ( is_array( $verified ) ) {
                if ( isset( $verified['plan'] ) ) {
                    $status['plan'] = sanitize_text_field( $verified['plan'] );
                }
                if ( isset( $verified['exp'] ) && is_numeric( $verified['exp'] ) ) {
                    $status['exp'] = (int) $verified['exp'];
                }
            }
        }
        $status['last_check'] = current_time( 'mysql' );
        update_option( self::STATUS_OPTION, $status );

        // Return the refreshed status from option (MU plugin will set active on next run).
        return get_option( self::STATUS_OPTION, array() );
    }

    /**
     * Deactivate the license.
     *
     * @return array|WP_Error License status or error.
     */
    public function deactivate() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'permission_denied', __( 'Insufficient permissions.', 'fx' ) );
        }

        delete_option( self::TOKEN_OPTION );

        // Immediately reflect deactivated state in status to avoid stale UI/pro flags.
        $status              = get_option( self::STATUS_OPTION, array() );
        $status['active']    = false;
        $status['last_check'] = current_time( 'mysql' );
        update_option( self::STATUS_OPTION, $status );

        if ( function_exists( 'fx_core_run_checks' ) ) {
            fx_core_run_checks();
        }

        return get_option( self::STATUS_OPTION, array() );
    }

    /**
     * Determine if the license is active.
     *
     * @return bool
     */
    public function is_active() {
        $status = get_option( self::STATUS_OPTION, array() );
        return ! empty( $status['active'] );
    }

    /**
     * Get the license status array.
     *
     * @return array
     */
    public function status() {
        return get_option( self::STATUS_OPTION, array() );
    }

    /**
     * Check remote server for license validity.
     *
     * @return void
     */
    public function check_remote() {
        if ( get_transient( self::RATE_LIMIT . '_check' ) ) {
            return;
        }

        set_transient( self::RATE_LIMIT . '_check', time(), MINUTE_IN_SECONDS );

        // Avoid network side-effects on frontend; defer to core checks if present.
        if ( is_admin() && function_exists( 'fx_core_run_checks' ) ) {
            fx_core_run_checks();
        }

        $status              = get_option( self::STATUS_OPTION, array() );
        $status['last_check'] = current_time( 'mysql' );
        update_option( self::STATUS_OPTION, $status );
    }

    /**
     * Schedule cron event for remote checks.
     *
     * @return void
     */
    public function schedule_cron() {
        add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
        if ( ! wp_next_scheduled( 'fx_check_license' ) ) {
            wp_schedule_event( time(), 'weekly', 'fx_check_license' );
        }
    }

    /**
     * Register weekly cron schedule.
     *
     * @param array $schedules Existing schedules.
     * @return array
     */
    public function cron_schedules( $schedules ) {
        if ( ! isset( $schedules['weekly'] ) ) {
            $schedules['weekly'] = array(
                'interval' => WEEK_IN_SECONDS,
                'display'  => __( 'Once Weekly', 'fx' ),
            );
        }
        return $schedules;
    }
}
