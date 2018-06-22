<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * U_CF7_Entries_AJAX.
 *
 * AJAX Event Handler.
 *
 * @class    U_CF7_Entries_AJAX
 */
class U_CF7_Entries_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Set WC AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['wc-ajax'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'WC_DOING_AJAX' ) ) {
				define( 'WC_DOING_AJAX', true );
			}
			// Turn off display_errors during AJAX events to prevent malformed JSON
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 );
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Send headers for WC Ajax Requests
	 * @since 2.5.0
	 */
	private static function wc_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// EVENT => nopriv
		$ajax_events = array(
			'resend_notifications'                         => false,
			'load_form_tags_for_export'                    => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_u_cf7_entries_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_u_cf7_entries_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Resend notifications
	 */
	public static function resend_notifications() {
		check_ajax_referer( 'resend_notifications', 'security' );

		if ( ! current_user_can( WPCF7_ADMIN_READ_WRITE_CAPABILITY ) ) {
			die(-1);
		}
		$post_id = intval( $_POST['post_id'] );
		$mail    = intval( $_POST['mail'] );

		$_properties  = get_post_meta( $post_id, '_properties', true );
		$_posted_data = get_post_meta( $post_id, '_posted_data', true );
		switch ($mail) {
			case 1:
				$prop   = $_properties['mail'];
				if( isset($_POST['recipients']) && !empty($_POST['recipients']) ){
					$prop['recipient'] = $_POST['recipients'];
				}
				$prop   = self::replace_tags( $prop, $_posted_data );
				$result = WPCF7_Mail::send( $prop, 'mail' );
				break;
			case 2:
				$prop   = $_properties['mail_2'];
				if( isset($_POST['recipients']) && !empty($_POST['recipients']) ){
					$prop['recipient'] = $_POST['recipients'];
				}
				$prop   = self::replace_tags( $prop, $_posted_data );
				$result = WPCF7_Mail::send( $prop, 'mail_2' );
				break;
			default:
				do_action('u_cf7_entries_resend_notifications');
				break;
		}

		die();

	}

	private function replace_tags( $tags = array(), $posted_data )
	{
		$prop = array();
		$args = array(
			'html'          => $tags['use_html'],
			'exclude_blank' => $tags['exclude_blank']
		);
		foreach ($tags as $key => $value) {
			$prop[$key] = u_cf7_entries_replace_tags( $value, $args, $posted_data );
		}
		return $prop;
	}

	public function load_form_tags_for_export()
	{
		check_ajax_referer( 'load_form_tags_nonce', 'security' );

		if ( ! current_user_can( WPCF7_ADMIN_READ_CAPABILITY ) ) {
			die(-1);
		}
		$form_id = intval( $_POST['form_id'] );
		$tags    = u_cf7_get_form_tags($form_id);
		include_once 'views/html-export-form-tags.php';
		die;
	}

}

U_CF7_Entries_AJAX::init();
