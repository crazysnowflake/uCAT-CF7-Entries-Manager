<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class U_CF7_Entries_Table {

	/**
	 * The single instance of U_CF7_Entries_Table.
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
		$this->register_post_types();
		$this->register_taxonomies();
		$this->init_hooks();
	} // End __construct ()

	/**
	 * Hook into actions and filters
	 * @since  1.0.0
	 */
	public function init_hooks()
	{
		add_filter('init', array($this, 'register_post_statuses'));
		add_filter('manage_edit-u_cf7_entries_columns', array($this, 'manage_table_columns'));
		add_action('manage_u_cf7_entries_posts_custom_column', array($this, 'manage_table_columns_output'), 10, 2);
		add_filter('manage_edit-u_cf7_entries_sortable_columns', array($this, 'manage_table_sortable_columns'));
		add_filter('request', array($this, 'table_sort_columns'));
		add_filter('request', array($this, 'table_filter_entries'));
		
		add_action('restrict_manage_posts', array($this, 'forms_filter'), 10 );

		add_action('admin_menu', array( $this, 'admin_menu' ), 1);
		add_filter('parent_file', array( $this, 'parent_file' ));
		add_action( 'admin_head', array( $this, 'menu_order_count' ) );
	}	
	function forms_filter(){
	    global $typenow;
	    if ( 'u_cf7_entries' != $typenow ) {
	        return;
	    }
	    $form  = isset($_GET['form']) ? $_GET['form'] : '';
	    $forms = u_cf7_get_forms();
	    ?>
        <select name='form' id='form'>
            <option value=""><?php _e( 'All forms', 'wc_point_of_sale' ); ?></option>
            <?php if( $forms ) { ?>
            	<?php foreach ($forms as $f) {
            		?>
            		<option value="<?php echo $f->id(); ?>" <?php selected($form, $f->id(), true); ?> ><?php echo $f->title(); ?></option>
            	<?php }?>
            <?php }?>
        </select>
        <?php
	}
	public function register_post_statuses()
	{
		$statuses = get_u_cf7_entries_statuses();
		foreach ($statuses as $key => $status) {
			register_post_status( $key, array(
				'label'                     => $status,
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( $status . ' <span class="count">(%s)</span>', $status . ' <span class="count">(%s)</span>' ),
			) );
		}
	}

	public function register_post_types()
	{
		$options = array(
			'public'            => false,
			'has_archive'       => false,
			'hierarchical'      => false,
			'supports'          => array('comments'),
			'show_in_menu'      => 'wpcf7',
			'show_in_nav_menus' => false,
			'capabilities' => array(
			    'create_posts' => 'do_not_allow', // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
			),
			'map_meta_cap' => true, 
			);
		U_CF7_Entries()->register_post_type('u_cf7_entries', 'Entries', 'Entry', '', $options);
		add_filter('u_cf7_entries_labels', array($this, 'u_cf7_entries_labels'));
	}
	
	public function u_cf7_entries_labels($labels)
	{
		$labels['edit_item'] = __( 'View Entry' , 'u-cf7-entries' );
		return $labels;
	}

	public function register_taxonomies()
	{
		$args = array(
            'public'            => false,
            'show_in_nav_menus' => false,
            'show_tagcloud'     => false,
            'show_admin_column' => true,
        );
		U_CF7_Entries()->register_taxonomy( 'u_cf7e_cat', __( 'Category' , 'u-cf7-entries' ), __( 'Category' , 'u-cf7-entries' ), array('u_cf7_entries'), $args ) ;
	}

	public function admin_menu()
	{
		add_submenu_page(
                'wpcf7',
                'Cat',
                __("Category", 'u-cf7-entries'),
                '',
                'edit-tags.php?taxonomy=u_cf7e_cat&post_type=u_cf7_entries',
                null
            );
	}

	public function parent_file($parent_file)
	{
		global $submenu_file, $current_screen, $pagenow;

        if($current_screen->post_type == 'u_cf7_entries') {

            if($pagenow == 'post.php'){
                $submenu_file = 'edit.php?post_type='.$current_screen->post_type;
            }

            if($pagenow == 'edit-tags.php'){
                $submenu_file = 'edit-tags.php?taxonomy=u_cf7e_cat&post_type='.$current_screen->post_type;
            }

            $parent_file = 'wpcf7';

        }

        return $parent_file;
	}

	public function menu_order_count()
	{
		global $submenu;
		//var_dump($submenu); die;
		if( isset($submenu['wpcf7']) && $submenu['wpcf7'][0][3] == 'Cat'){
			$cat = $submenu['wpcf7'][0];
			unset($submenu['wpcf7'][0]);
			$submenu['wpcf7'][] = $cat;
		}		
	}

	public function manage_table_columns( $columns ) {
		$_columns = array();
		$form_id  = isset($_GET['form']) ? $_GET['form'] : '';
		$contact_form = wpcf7_contact_form( $form_id );
		if( !empty($form_id) && $contact_form && $tags = $contact_form->form_scan_shortcode() ){
			$_columns['cb']  = $columns['cb'];
			$_columns["entry"] = __("Entry", 'u-cf7-entries');
			foreach ($tags as $tag ) { if( $tag['basetype'] == 'submit' ) continue;	
				$_columns[$tag['name']] = $tag['name'];
			}
			$_columns['date'] = $columns['date'];
		}else{
			foreach ($columns as $key => $column) {
				if( $key == 'title'){
		    		$_columns["entry"] = __("Entry", 'u-cf7-entries');
		    		$_columns["contact_form"] = __("Contact Form", 'u-cf7-entries');
		    		$_columns["page"] = __("Referer", 'u-cf7-entries');
				}else{
					$_columns[$key] = $column;
				}
			}			
		}
	    return $_columns;
	}

	public function manage_table_columns_output( $colname, $pid ) {
		switch ($colname) {
			case 'contact_form':
				$_wpcf7 = get_post_meta( $pid, '_wpcf7', true );
				$title  = get_the_title($_wpcf7);
				$url    = admin_url('admin.php?page=wpcf7&post='.$_wpcf7.'&action=edit');
				printf('<a href="%1$s">%2$s</a>', $url, $title);
				break;
			case 'entry':

				/*$_properties  = get_post_meta( $pid, '_properties', true );			
				$_posted_data = get_post_meta( $pid, '_posted_data', true );
				
				$sender      = __('No senders', 'u-cf7-entries');
				if( $_properties && isset($_properties['mail'])){
					$args = array(
						'html' => false,
						'exclude_blank' => false
					);

					$sender =  u_cf7_entries_replace_tags( $_properties['mail']['sender'], $args, $_posted_data );
					$sender = htmlentities ($sender);
					$sender = sprintf(__('Sender: %s', 'u-cf7-entries'), $sender);
				}
				printf('<a href="%1$s">%2$s</a>', get_edit_post_link($pid), $sender);*/
				printf('<a href="%1$s">%2$s</a>', get_edit_post_link($pid), '#'.$pid);
				break;
			case 'page':
				$_page = get_post_meta( $pid, '_u_cf7_page_located', true );
				if( $_page ){
					printf('<a href="%1$s" target="_blank">%2$s</a>', get_the_permalink($_page), get_the_title($_page));					
				}else{
					echo '---';
				}
				break;
			default:
				$meta = get_post_meta($pid, $colname, true );
				if( $meta ){
					if( is_array( $meta ) ){
						$meta = implode(', ', $meta);
					}
					echo $meta;
				}else{
					echo '---';
				}
				break;
		}
          
	}

	public function manage_table_sortable_columns( $columns ) {

		$form_id  = isset($_GET['form']) ? $_GET['form'] : '';
		$contact_form = wpcf7_contact_form( $form_id );
		if( !empty($form_id) && $contact_form && $tags = $contact_form->form_scan_shortcode() ){
			if( isset($columns['cb']) ){
				$_columns['cb']  = $columns['cb'];				
			}
			$_columns["entry"] = __("Entry", 'u-cf7-entries');
			foreach ($tags as $tag ) { if( $tag['basetype'] == 'submit' ) continue;	
				$_columns[$tag['name']] = $tag['name'];
			}
			if( isset($columns['date']) ){
				$_columns['date']  = $columns['date'];				
			}
		}
		
	    $columns["contact_form"] = 'contact_form';
	    return $columns;
	}

	/**
	*Check the passed query variables array for column name under the 'orderby' key
	*/
	public function table_sort_columns( $vars ) {
      if( array_key_exists('orderby', $vars )) {
           if('contact_form' == $vars['orderby']) {
                $vars['orderby'] = 'meta_value';
                $vars['meta_key'] = '_wpcf7';
           }
      }
      return $vars;
	}

	public function table_filter_entries( $vars ) {
      global $typenow, $wp_query;
        if ( $typenow == 'u_cf7_entries' ) {
        	if(isset( $_GET['form'] ) && !empty($_GET['form']) ){
                $vars['meta_query'][] = array(
                    'key'     => '_wpcf7',
                    'value'   => $_GET['form'],
                    'compare' => '=',
                );

                /**/
            }
        }
        return $vars;
	}	
	

	/**
	 * Main U_CF7_Entries_Table Instance
	 *
	 * Ensures only one instance of U_CF7_Entries_Table is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see U_CF7_Entries_Table()
	 * @return Main U_CF7_Entries_Table instance
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
