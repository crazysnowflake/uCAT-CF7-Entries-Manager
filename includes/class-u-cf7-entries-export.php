<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class U_CF7_Entries_Export {

	/**
	 * Hook into actions and filters
	 * @since  1.0.0
	 */
	public static function init()
	{
		#add_filter('comment_row_actions', array($this, 'comment_row_actions'), 50, 2);
		add_action( 'admin_menu', array( __CLASS__, 'admin_menus' ) );
		add_action( 'admin_post_u_cf7_entries_export', array( __CLASS__, 'entries_export' ) );
	}
	public static function admin_menus()
	{
		$hook = add_submenu_page ( 'wpcf7', __( 'Export Entries', 'u-cf7-entries' ), __( 'Export Entries', 'u-cf7-entries' ), 'manage_options', 'u_cf7_entries-export', array( __CLASS__, 'output_export_settings' ) );
		add_action( "load-$hook", array( __CLASS__, 'register_style' ) );

	}

	public static function register_style()
	{
		wp_register_style('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
  		wp_enqueue_style( 'jquery-ui' );
	}

	public static function output_export_settings()
	{
		include_once 'views/html-export-entries.php';
	}

	public static function entries_export()
	{
		$form_id         = $_POST['export_form'];
		$date_range      = $_POST['date_range'];
		$export_fields   = isset($_POST['export_fields']) ? $_POST['export_fields'] : false;
		$condition_rules = isset($_POST['condition_rules']) ? $_POST['condition_rules'] : false;
		$rule_match      = isset($_POST['condition_rule_match']) ? $_POST['condition_rule_match'] : 'any';

		$contact_form = wpcf7_contact_form($form_id);
		
		$keys = array();
		if( $contact_form ){
			$form_tags = $contact_form->form_scan_shortcode();
			foreach ($form_tags as $tag){
				if( $tag['basetype'] == 'submit' ) continue;
				$tag = new WPCF7_Shortcode( $tag );
				if( $export_fields && !in_array($tag->name, $export_fields) ) continue;
				$keys[$tag->name] = '';
			}
		}	

		$args = array(
			'posts_per_page' => -1,
			'post_type'      => 'u_cf7_entries',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids'
			);
		if( !empty($date_range[0]) || !empty($date_range[1]) ){
			$args['date_query'] = array(array('inclusive' => true));
			if( !empty($date_range[0]) ){
				$d = strtotime($date_range[0]);
				$args['date_query'][0]['after'] = array(
						'year'  => date('Y', $d),
	                    'month' => date('m', $d),
	                    'day'   => date('d', $d),
					);
			}
			if( !empty($date_range[1]) ){
				$d = strtotime($date_range[1]);
				$args['date_query'][0]['before'] = array(
						'year'  => date('Y', $d),
	                    'month' => date('m', $d),
	                    'day'   => date('d', $d),
					);
			}
		}
		if( $condition_rules ){
			$meta_query = array();
			foreach ($condition_rules as $line) {
				$meta  = array();
				
				if($line['tag'] != '___any'){
					$meta['key'] = trim($line['tag']);
				}
				$meta['value']   = trim($line['value']);
				$meta['compare'] = $line['rule'] == 'is' ? '=' : 'LIKE';
				$meta_query[] = $meta;
			}
			$meta_query['relation'] = $rule_match == 'all' ? 'AND' : 'OR';
			$args['meta_query'] = $meta_query;
		}
		$result  = array();
		#var_dump($args);
		$entries = new WP_Query($args);
		if ( $entries->have_posts() ){
			foreach ( $entries->posts as $entry_id) {
				
				$entry        = $keys;
				$_posted_data = get_post_meta( $entry_id, '_posted_data', true );

				foreach ($_posted_data as $key => $data){
					if( $export_fields && !in_array($key, $export_fields) ) continue;
					if( strrpos($key, '_wpcf7') === 0 || strrpos($key, '_u_cf7') === 0 ) continue;
					$value = is_array($data) ? implode(', ', $data) : $data;
					$entry[$key] = $value;

					if( !isset($keys[$key])){
						$keys[$key] = '';
					}
				}
				$result[] = $entry;
			}
		}

		self::download_send_headers("export_entries_" . date("Y-m-d") . ".csv");
		echo self::array2csv($result, $keys);
		die();

	}
	public static function download_send_headers($filename) {
	    // disable caching
	    $now = gmdate("D, d M Y H:i:s");
	    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
	    header("Last-Modified: {$now} GMT");

	    // force download  
	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-Type: application/download");

	    // disposition / encoding on response body
	    header("Content-Disposition: attachment;filename={$filename}");
	    header("Content-Transfer-Encoding: binary");
	}

	public static function array2csv(array &$array, array &$keys )
	{
	   if (count($array) == 0) {
	     return null;
	   }
	   ob_start();
	   $df = fopen("php://output", 'w');
	   if( !empty($keys) ){
	   		fputcsv($df, array_keys($keys));
	   }else{
			fputcsv($df, array_keys(reset($array)));
	   }
	   foreach ($array as $row) {
	      fputcsv($df, $row);
	   }
	   fclose($df);
	   return ob_get_clean();
	}

}
U_CF7_Entries_Export::init();