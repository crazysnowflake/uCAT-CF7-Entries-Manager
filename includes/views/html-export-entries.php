<?php
$forms = u_cf7_get_forms();
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<p><?php _e('Select a form below to export entries. Once you have selected a form you may select the fields you would like to export and then define optional filters for field values and the date range. When you click the download button below, will be created a CSV file for you to save to your computer.', 'u-cf7-entries'); ?></p>
	<form novalidate="novalidate" action="<?php echo admin_url('admin-post.php'); ?>" method="post">
		<input type="hidden" name="action" value="u_cf7_entries_export">
		<table class="form-table">
			<tbody>
				<tr class="export_form">
					<th scope="row">
						<label for="export_form"><?php  _e( 'Select a form', 'u-cf7-entries' ); ?></label>
					</th>
					<td>
						<select name="export_form" id="export_form" required="required">
							<?php if( $forms ) { 
								foreach ($forms as $form) { ?>
								<option value="<?php echo $form->id(); ?>"><?php echo $form->title(); ?></option>								
								<?php
								}
							} ?>
						</select>
						<p class="description">
						<?php  _e( 'Select the form you would like to export entry data from. You may only export data from one form at a time.', 'u-cf7-entries' ); ?>
						</p>
					</td>
				</tr>
				<?php
				if($forms){
					$tags    = u_cf7_get_form_tags($forms[0]->id());
					include_once 'html-export-form-tags.php';					
				}
				?>
				<tr>
					<th scope="row">
						<label><?php  _e( 'Select date range', 'u-cf7-entries' ); ?></label>
					</th>
					<td>
						<div style="width:150px; float:left; margin-right: 20px;" >
							<input type="text" name="date_range[]" class="date_range" style="width:100%;">
							<strong>
								<?php  _e( 'Start', 'u-cf7-entries' ); ?>
							</strong>						
						</div>
						<div style="width:150px; float:left;" >
							<input type="text" name="date_range[]" class="date_range" style="width:100%;">
							<strong>
								<?php  _e( 'End', 'u-cf7-entries' ); ?>
							</strong>						
						</div>
						<div class="clear">
							<p class="description">
							<?php  _e( 'Setting a range will limit the export to entries submitting during that date range. If no range is set, all entries will be exported.', 'u-cf7-entries' ); ?>
							</p>							
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" value="<?php  _e( 'Download Export File', 'u-cf7-entries' ); ?>" class="button button-primary" id="submit" name="submit"></p>
	</form>
</div>