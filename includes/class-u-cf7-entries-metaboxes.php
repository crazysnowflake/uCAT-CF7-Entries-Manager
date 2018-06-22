<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class U_CF7_Entries_Metaboxes {

	/**
	 * The single instance of U_CF7_Entries_Metaboxes.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;
	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->init_hooks();
	} // End __construct ()

	/**
	 * Hook into actions and filters
	 * @since  1.0.0
	 */
	public function init_hooks()
	{
		add_action( 'admin_menu', array($this, 'remove_meta_boxes') );
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action( 'save_post', array($this, 'save_meta_boxes'), 50, 1 );
		add_filter('comment_row_actions', array($this, 'comment_row_actions'), 50, 2);
	}

	public function remove_meta_boxes()
	{
		remove_meta_box( 'submitdiv', 'u_cf7_entries', 'side' );
		remove_meta_box( 'commentstatusdiv', 'u_cf7_entries', 'normal' );
	}

	public function add_meta_boxes()
	{
		
		add_meta_box( 'entry_general_info', 'General', array($this, 'output_entry_general_info'), 'u_cf7_entries', 'normal' ,'high' );
		add_meta_box( 'submitdiv', 'Stats', array($this, 'output_entry_stats'), 'u_cf7_entries', 'side' ,'high' );
	}

	public function save_meta_boxes($post_id)
	{
		
		if ( !isset($_POST['u_cf7_entry_general_info']) || ! wp_verify_nonce( $_POST['u_cf7_entry_general_info'], plugin_basename(__FILE__) ) )
			return $post_id;

		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_id;

		if ( 'u_cf7_entries' == $_POST['post_type'] && ! current_user_can( WPCF7_ADMIN_READ_WRITE_CAPABILITY, $post_id ) ) {
			  return $post_id;
	  	/*
		} elseif( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;*/
		}
		$_posted_data = array();
		if ( isset( $_POST['_posted_data'] ) ){
			$_posted_data = $_POST['_posted_data'];
			foreach ($_posted_data as $key => $data) {
				update_post_meta($post_id, $key, $data);
			}
		}

		if ( isset( $_POST['_new_field'] ) && isset( $_POST['_new_value'] ) ){
			$_fields = $_POST['_new_field'];
			$_values = $_POST['_new_value'];
			foreach ($_fields as $i => $key) {
				$data = isset($_values[$i]) ? $_values[$i] : '';
				update_post_meta($post_id, $key, $data);
				$_posted_data[$key] = $data;
			}
		}

		update_post_meta( $post_id, '_posted_data', $_posted_data );
		return $post_id;
	}

	public function output_entry_general_info( $post )
	{
		wp_nonce_field( plugin_basename(__FILE__), 'u_cf7_entry_general_info' );
		include_once 'views/html-entry_general_info.php';
	}

	public function output_entry_stats( $post )
	{
		include_once 'views/html-entry_stats.php';
	}

	public function comment_row_actions($actions, $post)
	{
		
		$post_type = get_post_type($post->comment_post_ID);
		if( $post_type == 'u_cf7_entries'){
			#var_dump($actions);
			unset($actions['approve']);
			unset($actions['unapprove']);
			unset($actions['edit']);
			unset($actions['reply']);
			unset($actions['spam']);
		}
		return $actions;
	}

	private function attachments( $template ) {
		$attachments = array();

		if ( $submission = WPCF7_Submission::get_instance() ) {
			$uploaded_files = $submission->uploaded_files();

			foreach ( (array) $uploaded_files as $name => $path ) {
				if ( false !== strpos( $template, "[${name}]" )
				&& ! empty( $path ) ) {
					$attachments[] = $path;
				}
			}
		}

		foreach ( explode( "\n", $template ) as $line ) {
			$line = trim( $line );

			if ( '[' == substr( $line, 0, 1 ) ) {
				continue;
			}

			$path = path_join( WP_CONTENT_DIR, $line );

			if ( @is_readable( $path ) && @is_file( $path ) ) {
				$attachments[] = $path;
			}
		}

		return $attachments;
	}

	

	/**
	 * Main U_CF7_Entries_Metaboxes Instance
	 *
	 * Ensures only one instance of U_CF7_Entries_Metaboxes is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see U_CF7_Entries_Metaboxes()
	 * @return Main U_CF7_Entries_Metaboxes instance
	 */
	public static function instance () {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

}
