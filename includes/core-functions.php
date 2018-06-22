<?php
function get_u_cf7_entries_statuses()
{
	$statuses = array(
		'ucf7e_readed' => __("Readed", 'u-cf7-entries'),
		'ucf7e_unread' => __("Unread", 'u-cf7-entries'),
	);
	return $statuses + apply_filters('u_cf7_entries_statuses', array());
}
function u_cf7_get_forms()
{
	$args = array('orderby' => 'title');	
	return WPCF7_ContactForm::find( $args );
}

function u_cf7_get_form_tags($form_id)
{
	$tags         = array();
	$contact_form = wpcf7_contact_form($form_id);
	if( $contact_form ){
		$form_tags = $contact_form->form_scan_shortcode();
		foreach ($form_tags as $tag){
			if( $tag['basetype'] == 'submit' ) continue;
			$tag = new WPCF7_Shortcode( $tag );
			$tags[] = $tag->name;
		}
	}
	return $tags;
}