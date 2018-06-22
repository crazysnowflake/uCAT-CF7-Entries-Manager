<tr class="select_fields">
	<th scope="row">
		<label><?php  _e( 'Select fields', 'u-cf7-entries' ); ?></label>
	</th>
	<td>
		<fieldset>
		<?php
		if( $tags ) {
			foreach ($tags as $i => $tag) {
				?>
				<label for="tag_<?php echo $i; ?>" style="display: block;">
					<input type="checkbox" name="export_fields[]" value="<?php echo $tag; ?>" id="tag_<?php echo $i; ?>" >
					<?php echo $tag; ?>
				</label>
				<?php
			}
		}
		?>
		</fieldset>
		<p class="description">
		<?php  _e( 'Select the fields you would like to include in the export. Don\'t select if you want to export all fields.', 'u-cf7-entries' ); ?>
		</p>
	</td>
</tr>
<tr class="conditional_logic">
	<th scope="row">
		<label><?php  _e( 'Conditional logic', 'u-cf7-entries' ); ?></label>
	</th>
	<td>
		<button class="button button-small" id="u_cf7_entries_add_condition" type="button"><?php  _e( 'Add a condition', 'u-cf7-entries' ); ?></button>
		<table class="u_cf7_entries_conditions" >
			<tbody>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4">
						<?php $select = '<select name="condition_rule_match" id="u_cf7_entries_condition_match"><option value="all">'.__('All', 'u-cf7-entries').'</option><option value="any">'.__('Any', 'u-cf7-entries').'</option></select>' ?>
						<?php printf(__('Export entries if %s of the above match.'), $select ) ?>
					</td>
				</tr>
			</tfoot>
		</table>
		<script type="text/template" id="tmpl-u_cf7_entries_condition_line">
			<tr data-index="{{i}}">
				<td>
					<select name="condition_rules[{{i}}][tag]">
						<option value="___any"><?php  _e( 'Any form field', 'u-cf7-entries' ); ?></option>
					<?php
					if( $tags ) { foreach ($tags as $i => $tag) {
						?>
						<option value="<?php echo $tag; ?>"><?php echo $tag; ?></option>
						<?php
					} }
					?>
					</select>
				</td>
				<td>
					<select name="condition_rules[{{i}}][rule]">
						<option value="contains"><?php  _e( 'contains', 'u-cf7-entries' ); ?></option>
						<option value="is"><?php  _e( 'is', 'u-cf7-entries' ); ?></option>
					</select>
				</td>
				<td>
					<input type="text" name="condition_rules[{{i}}][value]">
				</td>
				<td>
					<button class="button button-small" id="u_cf7_entries_remove_condition_line" type="button">-</button>
				</td>				
			</tr>
		</script>
		<p class="description">
		<?php  _e( 'Filter the entries by adding conditions.', 'u-cf7-entries' ); ?>
		</p>
	</td>
</tr>