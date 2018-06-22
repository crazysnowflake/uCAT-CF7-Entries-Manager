<?php
	global $action;

	$post_type = $post->post_type;
	$post_type_object = get_post_type_object($post_type);
	$can_publish = current_user_can($post_type_object->cap->publish_posts);
	$print_url = admin_url('admin.php?page=u_cf7_entries-print&entry='.$post->ID);
?>
<div class="submitbox" id="submitpost">

	<div id="minor-publishing">		
		<div id="misc-publishing-actions">			
			<div class="misc-pub-section misc-pub-post-status"><label for="post_status"><?php _e('Status:') ?></label>
				<span id="post-status-display">
				<?php
				$statuses = get_u_cf7_entries_statuses();
				echo isset($statuses[$post->post_status]) ? $statuses[$post->post_status] : $post->post_status;
				?>
				</span>
				<a href="#post_status" <?php if ( 'private' == $post->post_status ) { ?>style="display:none;" <?php } ?>class="edit-post-status hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit status' ); ?></span></a>

				<div id="post-status-select" class="hide-if-js">
				<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo $post->post_status; ?>" />
				<select name='post_status' id='post_status'>
					<?php foreach ($statuses as $key => $value): ?>
					<option<?php selected( $post->post_status, $key ); ?> value='<?php echo $key; ?>'><?php echo $value; ?></option>
					<?php endforeach; ?>
				</select>
				 <a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e('OK'); ?></a>
				 <a href="#post_status" class="cancel-post-status hide-if-no-js button-cancel"><?php _e('Cancel'); ?></a>
				</div>
			</div><!-- .misc-pub-section -->
			<?php
			$_wpcf7 = get_post_meta( $post->ID, '_wpcf7', true );
			$title  = get_the_title($_wpcf7);
			$url    = admin_url('admin.php?page=wpcf7&post='.$_wpcf7.'&action=edit');
			$_page  = get_post_meta( $post->ID, '_u_cf7_page_located', true );
			?>
			<div class="misc-pub-section misc-pub-custom misc-pub-form">
				<?php _e('Form:', 'u-cf7-entries'); ?>
				<b><?php printf('<a href="%1$s">%2$s</a>', $url, $title); ?></b>
			</div><?php // /misc-pub-form ?>
			<div class="misc-pub-section misc-pub-custom misc-pub-referer">
				<?php _e('Referer:', 'u-cf7-entries'); ?>
				<b>
				<?php 
				if( $_page ){
					printf('<a href="%1$s" target="_blank">%2$s</a>', get_the_permalink($_page), get_the_title($_page));					
				}else{
					echo '---';
				}
				?>
				</b>
			</div><?php // /misc-pub-referer ?>
			<?php
			$username = __('Guest', 'u-cf7-entries');
			if( $post->post_author > 0 ){
			$username = '<a href="'. get_edit_user_link( $post->post_author ) .'">'. esc_attr( get_the_author_meta('display_name', $post->post_author) ) .'</a>';
			}
			$stamp = __('Submitted by: <b>%1$s</b>', 'u-cf7-entries');
			?>
			<div class="misc-pub-section misc-pub-custom misc-pub-submitted_by">
				<?php printf($stamp, $username); ?>
			</div><?php // /misc-pub-submitted_By ?>
			<?php
			$datef = __( 'M j, Y @ H:i' );
			$stamp = __('Submitted on: <b>%1$s</b>', 'u-cf7-entries');
			$date = date_i18n( $datef, strtotime( $post->post_date ) );
			?>
			<div class="misc-pub-section curtime misc-pub-curtime">
				<span id="timestamp">
				<?php printf($stamp, $date); ?></span>
			</div><?php // /misc-pub-section ?>
			<?php
			$datef = __( 'M j, Y @ H:i' );
			$stamp = __('Modified on: <b>%1$s</b>', 'u-cf7-entries');
			$date = date_i18n( $datef, strtotime( $post->post_modified ) );
			?>
			<div class="misc-pub-section misc-pub-revisions">
				<span>
				<?php printf($stamp, $date); ?>
				</span>
			</div><?php // /misc-pub-revisions ?>
			<div class="misc-pub-section misc-pub-custom misc-pub-print">
				<?php _e('Print:', 'u-cf7-entries'); ?>
				<b><?php printf('<a href="%1$s" target="_blank">%2$s</a>', $print_url, __('Preview', 'u-cf7-entries')); ?></b>
			</div><?php // /misc-pub-print ?>
			<!-- <div class="misc-pub-section misc-pub-custom misc-pub-discussion">
				<?php _e('Discussion:', 'u-cf7-entries'); ?>
				<b><?php printf('<a href="%1$s">%2$s</a>', $url, $title); ?></b>
			</div><?php // /misc-pub-discussion ?> -->
			<div class="clear"></div>
		</div><!-- #minor-publishing-actions -->
	</div>
	<div id="major-publishing-actions">
		<div id="delete-action">
		<?php
		if ( current_user_can( "delete_post", $post->ID ) && current_user_can( WPCF7_ADMIN_READ_WRITE_CAPABILITY )) {
				$delete_text = __('Move to Trash');
			?>
			<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
		} ?>
		</div>

	<div id="publishing-action">
		<span class="spinner"></span>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
		<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e( 'Update' ) ?>" />
	</div>
	<div class="clear"></div>
	</div>
</div>
