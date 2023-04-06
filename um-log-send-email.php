<?php
/**
 * Plugin Name:     Ultimate Member - Log Send Email
 * Description:     Extension to Ultimate Member for logging of notification emails within UM.
 * Version:         1.0.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'UM' ) ) return;

class UM_Log_Send_Email {

    public $current_timestamp = '';

    function __construct( ) {

        add_action( 'um_user_register',                     array( $this, 'um_user_register_trace_log' ), -1, 2 );
        add_action( 'um_registration_complete',             array( $this, 'um_registration_complete_trace_log' ), 10, 2 );
        add_filter( 'um_locate_email_template',             array( $this, 'um_locate_email_template_trace_log' ), 10, 2 );
        add_action( 'um_after_user_status_is_changed',      array( $this, 'um_after_user_status_is_changed_trace_log' ), 10, 2 );
        add_action( 'um_before_email_notification_sending', array( $this, 'um_before_email_notification_sending_trace_log' ), 10, 3 );
        add_filter( 'um_email_send_message_content',        array( $this, 'um_email_send_message_content_trace_log' ), 10, 3 );
        add_filter( 'wp_mail',                              array( $this, 'wp_mail_um_trace_log' ), 10, 1 );
        add_filter( 'x_redirect_by',                        array( $this, 'wp_redirect_trace_log' ), 10, 3 );

        $this->current_timestamp = date_i18n( 'Y-m-d H:i:s ', current_time( 'timestamp' ));
    }

    public function um_log_send_email_filewrite( $content ) {

        $trace = '<div>' . $this->current_timestamp . $content . '</div>';
        file_put_contents( WP_CONTENT_DIR . '/um_trace_log.html', $trace, FILE_APPEND );

        $e = new \Exception;
        $trace = '<div><pre>' . str_replace( ABSPATH, '...', $e->getTraceAsString()) . '</pre><div>';
        file_put_contents( WP_CONTENT_DIR . '/um_trace_log.html', $trace, FILE_APPEND );
    }

    public function um_user_register_trace_log( $user_id, $args ) {

        $this->um_log_send_email_filewrite( 'um_user_register user_id: ' . $user_id . ' role ID: ' . $args['role'] );
    }

    public function um_registration_complete_trace_log( $user_id, $args ) {

        $this->um_log_send_email_filewrite( 'um_registration_complete user_id: ' . $user_id . ' role ID: ' . $args['role']);         
    }

    public function um_locate_email_template_trace_log( $template, $template_name ) {

        $this->um_log_send_email_filewrite( 'um_locate_email_template: ' . $template_name . ' path: ' . str_replace( ABSPATH, '...', $template ));
        return $template;
    }

    public function um_after_user_status_is_changed_trace_log( $status, $user_id ) {

        $this->um_log_send_email_filewrite( 'um_after_user_status_is_changed: ' . $status . ' user_id: ' . $user_id );
    }

    public function um_before_email_notification_sending_trace_log( $email, $template, $args ) {

        $this->um_log_send_email_filewrite( 'um_before_email_notification_sending: ' . $email . ' template: ' . $template );
    }

    public function um_email_send_message_content_trace_log( $message, $slug, $args ) {

        $this->um_log_send_email_filewrite( 'um_email_send_message_content slug: ' . $slug . $message );
        return $message;
    }

    public function wp_mail_um_trace_log( $array ) {

        $this->um_log_send_email_filewrite( 'wp_mail: ' . $array['to'] . ' subject: ' . $array['subject'] . $array['message'] );
        return $array;
    }

    public function wp_redirect_trace_log( $x_redirect_by, $status, $location ) {

        $this->um_log_send_email_filewrite( 'wp_redirect by: ' . $x_redirect_by . ' status: ' . $status . ' location: ' . str_replace( get_bloginfo( 'url' ), '...', $location ));    
        return $x_redirect_by;
    }

}

new UM_Log_Send_Email();
