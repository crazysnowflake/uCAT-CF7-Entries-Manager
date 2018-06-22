<?php
$_properties  = get_post_meta( $post->ID, '_properties', true );
$_posted_data = $posted_data = get_post_meta( $post->ID, '_posted_data', true );
$contact_form = wpcf7_contact_form($_posted_data['_wpcf7']);
$tags = false;
if( $contact_form ){
	$tags = $contact_form->form_scan_shortcode();	
}
#var_dump($contact_form);
#var_dump($tags);
?>
<div class="wp-filter">
	<ul class="filter-links" id="u-cf7-entries-tabs-links">
		<li class="plugin-install-submission"><a class=" current" href="#submission"><?php _e('Submission', 'u-cf7-entries'); ?></a> </li>
		<li class="plugin-install-mail1"><a href="#mail1"><?php printf(__('Mail %s', 'u-cf7-entries'), $_properties['mail_2']['active'] === true ? '1' : '') ; ?></a> </li>
		<?php if( $_properties['mail_2']['active'] === true ): ?>
		<li class="plugin-install-mail2"><a href="#mail2"><?php _e('Mail 2', 'u-cf7-entries'); ?></a> </li>
		<?php endif; ?>
		<li class="plugin-install-other"><a href="#other"><?php _e('Other', 'u-cf7-entries'); ?></a> </li>
	</ul>
</div>
<div class="inside_content"  id="u-cf7-entries-tabs">
	<div id="submission" class="u-cf7-entries-tab">
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th class="manage-column column-field"><?php _e('Field', 'u-cf7-entries'); ?></th>
					<th class="manage-column column-value"><?php _e('Value', 'u-cf7-entries'); ?></th>
					<th class="manage-column column-actions"></th>
				</tr>
			</thead>
			<tbody>
			<?php if($contact_form && $tags): ?>
				<?php foreach ($tags as $tag): if( $tag['basetype'] == 'submit' ) continue; $tag = new WPCF7_Shortcode( $tag ); ?>
				<tr>
					<td class="manage-column column-field">
						<?php echo $tag->name; ?>	
					</td>
					<td class="manage-column column-value">
						<?php
						$tag_data = '';
						if( isset($_posted_data[$tag->name])) {
							$tag_data = $_posted_data[$tag->name];
							unset($_posted_data[$tag->name]);
						}
							//var_dump($tag);
							$value = is_array($tag_data) ? implode(', ', $tag_data) : $tag_data;
							switch ($tag->basetype) {
								case 'textarea':
									printf('<textarea name="_posted_data[%1$s]">%2$s</textarea>',
									sanitize_html_class( $tag->name ), esc_textarea( $value ) );
									break;
								default:
									printf('<input type="text" name="_posted_data[%1$s]" value="%2$s">',
									sanitize_html_class( $tag->name ), esc_textarea( $value ) );


									break;
							}
							$domain = $_SERVER['SERVER_NAME'];							
							
							if( strpos($value, $domain) !== false || strpos($value, 'http') !== false ){
								$check  = wp_check_filetype( $value );
								$url    = strstr($value, $domain);
								$url    = empty($url) ? $value : $url;
								$img    = '';
								
								if( !empty($check['type']) && $check['type'] ){
									$img = wp_mime_type_icon($check['type']);
								}

								$image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );
								if ( !empty( $check['ext'] ) && $check['ext'] && in_array( $check['ext'], $image_exts ) ) {
									if( strpos($url, 'http') === false ){
										$url = '//'.$url;
									}
									$img = $url;
								}		
								if( !empty($img)){
									printf('<a href="%1$s" target="_blank" ><img src="%2$s" alt="%3$s" height="70"></a>', $url, $img, sanitize_html_class( $tag->name ));
								}
							}
						?>	
					</td>
					<td class="manage-column column-actions"></td>
				</tr>
				<?php endforeach; ?>							
			<?php endif;
			$hidden = array();
			if( count($_posted_data) > 0 ): ?>
				<?php foreach ($_posted_data as $key => $data): ?>
				<?php if( strrpos($key, '_wpcf7') === 0 || strrpos($key, '_u_cf7') === 0 ){ $hidden[$key] = $data;  continue; } ?>
				<tr>
					<td class="manage-column column-field">
						<?php echo $key; ?>	
					</td>
					<td class="manage-column column-value">
						<?php
							$value = is_array($data) ? implode(', ', $data) : $data;
							printf('<input type="text" name="_posted_data[%1$s]" value="%2$s">',
								sanitize_html_class( $key ), esc_textarea( $value ) ); ?>
					</td>
					<td class="manage-column column-actions"><a href="#delete_row" class="delete_row" title="<?php _e('Delete row', 'u-cf7-entries'); ?>">&times;</a></td>
				</tr>
				<?php endforeach; ?>					
			<?php endif; ?>
			</tbody>
		</table>
		<?php
		if(!empty($hidden) ) :
			foreach ($hidden as $key => $data):
				$value = is_array($data) ? implode(', ', $data) : $data;
				printf('<input type="hidden" name="_posted_data[%1$s]" value="%2$s">',
					sanitize_html_class( $key ), esc_textarea( $value ) );
			endforeach;
		endif;
		?>
		<div class="u-cf7-entry-bulk-actions">
			<button type="button" id="add_field" class="button button-large"><?php _e('Add field', 'u-cf7-entries'); ?></button>
		</div>
	</div>
	<?php
	for ($i=1; $i <= 2; $i++) {
		if( $_properties ){
			$prop = $i == 1 ? $_properties['mail'] : $_properties['mail_2'];
		?>
	<div id="mail<?php echo $i; ?>" class="u-cf7-entries-tab mailtab" style="display: none;">
		<div class="mail_content entries_tab_content">
			<table>
				<tbody>
				<?php
					$args = array(
						'html' => $prop['use_html'],
						'exclude_blank' => $prop['exclude_blank']
					);
					
					?>
						<tr>
							<th>
								<?php _e('To:', 'u-cf7-entries'); ?>
							</th>
							<td>
								<?php
									$recipient =  u_cf7_entries_replace_tags( $prop['recipient'], $args, $posted_data );
									echo htmlentities ($recipient);
								?>
							</td>
						</tr>
						<tr>
							<th>
								<?php _e('From:', 'u-cf7-entries'); ?>
							</th>
							<td>
								<?php
									$sender =  u_cf7_entries_replace_tags( $prop['sender'], $args, $posted_data );
									echo htmlentities ($sender);
								?>
							</td>
						</tr>
						<tr>
							<th>
								<?php _e('Subject:', 'u-cf7-entries'); ?>
							</th>
							<td>
								<?php
									$subject =  u_cf7_entries_replace_tags( $prop['subject'], $args, $posted_data );
									echo htmlentities ($subject);
								?>
							</td>
						</tr>
						<tr>
							<th>
								<?php _e('Body:', 'u-cf7-entries'); ?>
							</th>
							<td class="body-mail">
								<?php
									$body =  u_cf7_entries_replace_tags( $prop['body'], $args, $posted_data );
									if( !$args['html']){
										$body = htmlentities ($body);
									}
									echo wpautop( wptexturize( wp_kses_post( $body ) ) );
								?>
							</td>
						</tr>
						<tr>
							<th>
								<?php _e('Attachments:', 'u-cf7-entries'); ?>
							</th>
							<td>
								<?php
									if( !empty( $prop['attachments'] ) ){
										$attachments = $this->attachments($prop['attachments']);
										$domain = $_SERVER['SERVER_NAME'];							
										foreach ($attachments as $attach) {
											if( strpos($attach, 'http') === 0 ){
												printf('<a href="%1$s">%1$s</a><br>', $attach);
											}else if( strpos($attach, $domain) !== false ){
												$url    = strstr($attach, $domain);
												$url    = empty($url) ? $attach : $url;
												printf('<a href="//%1$s">%2$s</a><br>', $url, basename($attach));
											}
										}
									}else{
										 _e('None', 'u-cf7-entries');
									}
								?>
							</td>
						</tr>
				</tbody>
			</table>
		</div>
		<div class="u-cf7-entry-bulk-actions">
			<label for="change_recipient_<?php echo $i; ?>"><input type="checkbox" id="change_recipient_<?php echo $i; ?>" class="change_recipient"><?php _e('Change recipient', 'u-cf7-entries'); ?></label>
			<button type="button" class="button button-large u-cf7-resend_notifications" data-mail='<?php echo $i; ?>'><?php _e('Resend Notifications', 'u-cf7-entries'); ?></button>
		</div>
	</div>
		<?php
		}
	}
	?>
	<div id="other" class="u-cf7-entries-tab"  style="display: none;">
		<div class="entries_tab_content">
			<table>
				<tbody>
					<tr>
						<th><?php _e('User Agent:', 'u-cf7-entries'); ?></th>
						<td><?php echo get_post_meta( $post->ID, '_u_cf7_user_agent', true ); ?></td>
					</tr>
					<tr>
						<th><?php _e('IP Address:', 'u-cf7-entries'); ?></th>
						<td><?php echo get_post_meta( $post->ID, '_u_cf7_client_ip', true ); ?></td>
					</tr>
					<?php do_action('u-cf7-entries-other-tab'); ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script type="text/template" id="u_cf7_e_new_row">
	<tr>
		<td class="manage-column column-field">
			<input type="text" name="_new_field[]">
		</td>
		<td class="manage-column column-value">
			<input type="text" name="_new_value[]">
		</td>
		<td class="manage-column column-actions"><a href="#delete_row" class="delete_row" title="<?php _e('Delete row', 'u-cf7-entries'); ?>">&times;</a></td>
	</tr>
</script>
