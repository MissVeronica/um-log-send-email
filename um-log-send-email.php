<?php
/**
 * Plugin Name:     Ultimate Member - Log Send Email
 * Description:     Extension to Ultimate Member for logging of notification emails within UM.
 * Version:         2.0.0
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
    public $log_file          = '';

    public $html = '<!DOCTYPE html><html lang="en-US">
                    <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    <title>Log Send Email</title>
                    </head>
                    <body>';

    function __construct( ) {

        add_action( 'um_user_register', array( $this, 'um_user_register_trace_log' ), -1, 2 );

    }

    public function pre_information_test_trace_log() {

        ob_start();?>

        <div><?php echo $this->current_timestamp;?></div> 
        <div>--- UM Email Configurations ---</div>

        <div>Mail appears from:  			<?php $mail_from = UM()->options()->get('mail_from'); if( ! empty( $mail_from ) ){echo UM()->options()->get('mail_from');}else{echo "-";}; echo "\n";?></div>
        <div>Mail appears from address:  	<?php $mail_from_addr = UM()->options()->get('mail_from_addr'); if( ! empty( $mail_from_addr ) ){echo UM()->options()->get('mail_from_addr');}else{echo "-";}; echo "\n";?></div>
        <div>Use HTML for E-mails:   		<?php echo $this->info_value( UM()->options()->get('email_html'), 'yesno', true ); ?></div>

        <div>Account Welcome Email:  		<?php echo $this->info_value( UM()->options()->get('welcome_email_on'), 'yesno', true ); ?></div>
        <div>Account Activation Email:   	<?php echo $this->info_value( UM()->options()->get('checkmail_email_on'), 'yesno', true ); ?></div>
        <div>Pending Review Email:   		<?php echo $this->info_value( UM()->options()->get('pending_email_on'), 'yesno', true ); ?></div>
        <div>Account Approved Email: 		<?php echo $this->info_value( UM()->options()->get('approved_email_on'), 'yesno', true ); ?></div>
        <div>Account Rejected Email: 		<?php echo $this->info_value( UM()->options()->get('rejected_email_on'), 'yesno', true ); ?></div>
        <div>Account Deactivated Email:  	<?php echo $this->info_value( UM()->options()->get('inactive_email_on'), 'yesno', true ); ?></div>
        <div>Account Deleted Email:  		<?php echo $this->info_value( UM()->options()->get('deletion_email_on'), 'yesno', true ); ?></div>
        <div>Password Reset Email:   		<?php echo $this->info_value( UM()->options()->get('resetpw_email_on'), 'yesno', true ); ?></div>
        <div>Password Changed Email: 		<?php echo $this->info_value( UM()->options()->get('changedpw_email_on'), 'yesno', true ); ?></div>

        <div>Account Updated Email: 		    <?php echo $this->info_value( UM()->options()->get('changedaccount_email_on'), 'yesno', true ); ?></div>
        <div>New User Notification: 		    <?php echo $this->info_value( UM()->options()->get('notification_new_user_on'), 'yesno', true ); ?></div>
        <div>Account Needs Review Notification: <?php echo $this->info_value( UM()->options()->get('notification_review_on'), 'yesno', true ); ?></div>
        <div>Account Deletion Notification:     <?php echo $this->info_value( UM()->options()->get('notification_deletion_on'), 'yesno', true ); ?></div>

        <?php
        if ( ! empty( array_intersect( array_map( 'strtolower', get_loaded_extensions()), array( 'mod_security', 'mod security' )))) {
            echo '<div>WARNING: MOD SECURITY is active</div>';
        }

        if ( extension_loaded( 'suhosin' )) {
            echo '<div>WARNING: SUHOSIN is active</div>';
        }

        $basedir = ini_get( 'open_basedir' );
        if ( ! empty( $basedir )) {
            echo '<div>WARNING: open_basedir is active: ' . esc_html( $basedir ) . '</div>';
        }

        $content = ob_get_contents();
        ob_end_clean();

        file_put_contents( $this->log_file, $content, FILE_APPEND );
    }

    public function info_value( $raw_value = '', $type = 'yesno', $default = '' ) {

        if ( $type == 'yesno' ) {
            $raw_value = ( $default == $raw_value ) ? "Yes" : "No";
        } elseif( $type == 'onoff' ) {
            $raw_value = ( $default == $raw_value ) ? "On" : "Off";
        }

        return $raw_value;
    }

    public function um_log_send_email_filewrite( $content ) {

        $trace = '<div>' . $this->current_timestamp . $content . '</div>';
        file_put_contents( $this->log_file, $trace, FILE_APPEND );

        return;

        $e = new \Exception;
        $trace = '<div><pre>' . str_replace( ABSPATH, '...', $e->getTraceAsString()) . '</pre><div>';
        file_put_contents( $this->log_file, $trace, FILE_APPEND );
    }

    public function um_user_register_trace_log( $user_id, $args ) {

        $this->log_file = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'um_trace_log.html';
        $this->current_timestamp = date_i18n( 'Y-m-d H:i:s ', current_time( 'timestamp' ));

        if ( ! file_exists( $this->log_file )) {                                        
            file_put_contents( $this->log_file, $this->html );
            $this->pre_information_test_trace_log();
        }

        add_action( 'um_registration_complete',             array( $this, 'um_registration_complete_trace_log' ), 10, 2 );
        add_filter( 'um_locate_email_template',             array( $this, 'um_locate_email_template_trace_log' ), 10, 2 );
        add_action( 'um_after_user_status_is_changed',      array( $this, 'um_after_user_status_is_changed_trace_log' ), 10, 2 );
        add_action( 'um_before_email_notification_sending', array( $this, 'um_before_email_notification_sending_trace_log' ), 10, 3 );
        add_filter( 'um_email_send_message_content',        array( $this, 'um_email_send_message_content_trace_log' ), 10, 3 );
        add_filter( 'wp_mail',                              array( $this, 'wp_mail_um_trace_log' ), 10, 1 );
        add_filter( 'x_redirect_by',                        array( $this, 'wp_redirect_trace_log' ), 10, 3 );
        add_action( 'doing_it_wrong_run',                   array( $this, 'doing_it_wrong_run_trace_log' ), 10, 3 );

        $this->um_log_send_email_filewrite( 'um_user_register user_id: ' . $user_id . ' role ID: ' . $args['role'] );
    }

    public function um_registration_complete_trace_log( $user_id, $args ) {

        $this->um_log_send_email_filewrite( 'um_registration_complete user_id: ' . $user_id . ' role ID: ' . $args['role']);         
    }

    public function um_locate_email_template_trace_log( $template, $template_name ) {

        if ( file_exists( $template )) $found = ' template found OK';
        else $found = ' template NOT found';

        $this->um_log_send_email_filewrite( 'um_locate_email_template: ' . $template_name . ' path: ' . str_replace( ABSPATH, '...', $template ) . $found );
        return $template;
    }

    public function um_after_user_status_is_changed_trace_log( $status, $user_id ) {

        $this->um_log_send_email_filewrite( 'um_after_user_status_is_changed: ' . $status . ' user_id: ' . $user_id );
    }

    public function um_before_email_notification_sending_trace_log( $email, $template, $args ) {

        $this->um_log_send_email_filewrite( 'um_before_email_notification_sending: ' . $email . ' template: ' . $template );
    }

    public function um_email_send_message_content_trace_log( $message, $slug, $args ) {

        $this->um_log_send_email_filewrite( 'um_email_send_message_content slug: ' . $slug . '<div>email template:</div><div>' . esc_html( $message ) . '</div><div>...</div>' );
        return $message;
    }

    public function wp_mail_um_trace_log( $array ) {

        $this->um_log_send_email_filewrite( 'wp_mail: ' . $array['to'] . ' subject: ' . $array['subject'] . '<div>email message:</div><div>' . esc_html( $array['message'] ) . '</div><div>...</div>' );
        return $array;
    }

    public function wp_redirect_trace_log( $x_redirect_by, $status, $location ) {

        $this->um_log_send_email_filewrite( 'wp_redirect by: ' . $x_redirect_by . ' status: ' . $status . ' location: ' . str_replace( get_bloginfo( 'url' ), '...', $location ));    
        return $x_redirect_by;
    }

    public function doing_it_wrong_run_trace_log( $function_name, $message, $version ) {

        $this->um_log_send_email_filewrite( 'WARNING doing_it_wrong_run: ' . $function_name . '<div>email message:</div><div>' . esc_html( $message ) . '</div><div>...</div>' );
    }

}

new UM_Log_Send_Email();
