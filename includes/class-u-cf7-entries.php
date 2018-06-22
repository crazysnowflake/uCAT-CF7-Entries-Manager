<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class U_CF7_Entries {

	/**
	 * The single instance of U_CF7_Entries.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'u_cf7_entries';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new U_CF7_Entries_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		$this->includes();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		add_action( 'init', array( $this, 'init' ), 1 );

		$this->init_hooks();
	} // End __construct ()


	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes()
	{
		require_once( 'core-functions.php' );
		require_once( 'class-u-cf7-entries-replacement.php' );
		require_once( 'class-u-cf7-entries-table.php' );
		require_once( 'class-u-cf7-entries-metaboxes.php' );
		require_once( 'class-u-cf7-entries-export.php' );
		require_once( 'class-u-cf7-entries-ajax.php' );		
	}

	/**
	 * Hook into actions and filters
	 * @since  1.0.0
	 */
	public function init_hooks()
	{
		add_filter('wpcf7_form_hidden_fields', array($this, 'form_hidden_fields'), 180);
		add_filter('wpcf7_before_send_mail', array($this, 'save_data'));
	}

	public function form_hidden_fields($fields)
	{
		
		if( is_single() || is_page() ){
			global $post;
			$fields['_u_cf7_page_located'] = $post->ID;
			$fields['_u_cf7_user_agent']   = $_SERVER['HTTP_USER_AGENT'];
			$fields['_u_cf7_client_ip']    = $this->get_client_ip();
		}
		return $fields;
	}

	private function get_client_ip() {
	    $ipaddress = '';
	    if (isset($_SERVER['HTTP_CLIENT_IP']))
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    else if(isset($_SERVER['REMOTE_ADDR']))
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;
	}

	public function save_data($contact_form)
	{
		$uploaded_files = array();
		if (!isset($contact_form->posted_data) && class_exists('WPCF7_Submission')) {
	        
	        // Contact Form 7 version 3.9 removed $contact_form->posted_data and now
	        // we have to retrieve it from an API
	        $submission = WPCF7_Submission::get_instance();
	        if ($submission) {
	            $posted_data    = $submission->get_posted_data();
	            $uploaded_files = $submission->uploaded_files();
	        }
	    } elseif (isset($contact_form->posted_data)) {
	        // For pre-3.9 versions of Contact Form 7
	        $posted_data = $contact_form->posted_data;
	    } else {
	        // We can't retrieve the form data
	        return $contact_form;
	    }

	    $args = array(
		     'post_title'    => 'CF7 Entry @' . current_time('mysql'),
		     'post_content'  => '',
		     'post_type'     => 'u_cf7_entries',
		     'post_status'   => 'ucf7e_unread',
		     'post_author'   => 0,
	    );
	    $current_user = wp_get_current_user();
	    if( $current_user ){
	    	$args['post_author'] = $current_user->ID;
	    }
		 
		$post_id = wp_insert_post( $args );
		if( $post_id ){
			$_properties = $contact_form->get_properties();

			foreach ($posted_data as $key => $data) {
				if( isset($uploaded_files[$key]) ){
					$data = addslashes($uploaded_files[$key]);
					$posted_data[$key] = $data;
				}
				update_post_meta($post_id, $key, $data);
			}
			$properties = array(
				'mail'   => $_properties['mail'],
				'mail_2' => $_properties['mail_2']
				);
			update_post_meta($post_id, '_posted_data', $posted_data);
			update_post_meta($post_id, '_properties', $properties);
		}
		#var_dump($posted_data);die;
	}

	/**
	 * Init U_CF7_Entries when WordPress Initialises.
	 */
	public function init()
	{
		// Before init action
		do_action( 'before_u_cf7_entries_init' );
		
		// Load class instances
		$this->entries_table = new U_CF7_Entries_Table();
		$this->entries_metaboxes = new U_CF7_Entries_Metaboxes();

		// Print
		if ( ! empty( $_GET['page'] ) ) {
			switch ( $_GET['page'] ) {
				case 'u_cf7_entries-print' :
					include_once( 'class-u-cf7-entries-print.php' );
				break;
			}
		}

		// Init action
		do_action( 'u_cf7_entries_init' );
	}

	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new U_CF7_Entries_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new U_CF7_Entries_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( 'jquery-blockUI', esc_url( $this->assets_url ) . 'plugins/jquery-blockui/jquery.blockUI.css', array(), $this->_version );
		wp_register_style( $this->_token . '-fonts', esc_url( $this->assets_url ) . 'css/fonts.css', array(), $this->_version );
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array($this->_token . '-fonts'), $this->_version );
		wp_enqueue_style( 'jquery-blockUI' );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( 'jquery-blockui', esc_url( $this->assets_url ) . 'plugins/jquery-blockui/jquery.blockUI' . $this->script_suffix . '.js', array( 'jquery' ), '2.70' );

		$depth = array( 'jquery', 'jquery-blockui', 'jquery-ui-datepicker' );
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', $depth, $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );

		wp_localize_script($this->_token . '-admin', 'u_cf7e_i18n', array(
        	'delete_row'      => __( "Are you sure you want to delete this row?", 'u-cf7-entries'),
        	'email_addresses' => __( "Enter a comma separated list of email addresses you would like to receive the selected notification emails.", 'u-cf7-entries'),
        ) );
        wp_localize_script($this->_token . '-admin', 'u_cf7e_st', array(
        	'ajax_url'                   => admin_url( 'admin-ajax.php' ),
        	'resend_notifications_nonce' => wp_create_nonce( 'resend_notifications' ),
        	'load_form_tags_nonce'       => wp_create_nonce( 'load_form_tags_nonce' ),

        ) );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'u-cf7-entries', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'u-cf7-entries';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()


	/**
	 * Main U_CF7_Entries Instance
	 *
	 * Ensures only one instance of U_CF7_Entries is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see U_CF7_Entries()
	 * @return Main U_CF7_Entries instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
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

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();

		#$this->wpcf7_init_uploads();
	} // End install ()

	/* File uploading functions */

	private function wpcf7_init_uploads() {
		$dir = wpcf7_upload_tmp_dir();
		wp_mkdir_p( $dir );

		$htaccess_file = trailingslashit( $dir ) . '.htaccess';

			if ( $handle = @fopen( $htaccess_file, 'w' ) ) {
				fwrite( $handle, "RewriteEngine on\n
# serve everyone from specific-domain\n
RewriteCond %{HTTP_REFERER} ^".$_SERVER['HTTP_REFERER']."\n
RewriteRule ^ - [L]\n
\n
# everybody else receives a forbidden\n
RewriteRule ^ - [F]\n
\n
ErrorDocument 403 /forbidden.html\n" );
				fclose( $handle );
			}

	}

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
