jQuery( document ).ready( function ( $ ) {
	if( $('.contact_page_u_cf7_entries-export .date_range').length ){
		jQuery('.date_range').datepicker({
	        dateFormat : 'dd-mm-yy'
	    });

		$('.contact_page_u_cf7_entries-export').on('click', '#u_cf7_entries_add_condition', function(event) {
			var tpl    = $('#tmpl-u_cf7_entries_condition_line').html();
			var $table = $(this).next('table');
			var index  = -1;
			$table.find('tr').each(function(i, el) {
				var ii = parseInt( $(el).data('index') );
				if( ii > index )
					index = ii;
			});
			index++;

			tpl = tpl.replace(new RegExp('{{i}}', 'g'), index);
			$table.find('tbody').append(tpl);
			$table.find('tfoot').show();
		});
		$('.contact_page_u_cf7_entries-export').on('click', '#u_cf7_entries_remove_condition_line', function(event) {
			if( $(this).closest('tbody').find('tr').length == 1){
				$(this).closest('table').find('tfoot').hide();
			}
			$(this).closest('tr').remove();
		});
		$('#export_form').change(function(event) {
			var form_id = $(this).val();
			var data = {
				'action'   : 'u_cf7_entries_load_form_tags_for_export',
				'security' : u_cf7e_st.load_form_tags_nonce,
				'form_id'  : form_id
			};
			$( 'form' ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			$.post( u_cf7e_st.ajax_url, data, function( response ) {
				$('tr.select_fields, tr.conditional_logic').remove();
				$('tr.export_form').after(response);
				$( 'form' ).unblock();
			});
		});
	    
	}

	$('#u-cf7-entries-tabs-links li a').click(function(event) {
		var id = $(this).attr('href');
		$('#u-cf7-entries-tabs .u-cf7-entries-tab').hide();
		$(id).show();
		$('#u-cf7-entries-tabs-links li a').removeClass('current');
		$(this).addClass('current');
		
		return false;
	});
	$('#u-cf7-entries-tabs #add_field').click(function(event) {
		var html = $('#u_cf7_e_new_row').html();
		$('#u-cf7-entries-tabs #submission table tbody').append(html);
		return false;
	});
	
	$('#u-cf7-entries-tabs').on('click', '.u-cf7-resend_notifications', function(event) {
		var change_recipient = $(this).closest('.u-cf7-entry-bulk-actions').find('.change_recipient').is(':checked');
		var mail             = $(this).data('mail');
		var sent             = false;
		var email_addresses  = '';
		var data = {
			'action'   : 'u_cf7_entries_resend_notifications',
			'security' : u_cf7e_st.resend_notifications_nonce,
			'mail'     : mail,
			'post_id'  : $('#post_ID').val()
		};
		if( change_recipient ){
			data.recipients = window.prompt(u_cf7e_i18n.email_addresses);
		}
		$( '#u-cf7-entries-tabs' ).block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		$.post( u_cf7e_st.ajax_url, data, function( response ) {
			$( '#u-cf7-entries-tabs' ).unblock();
		});
	});
	$('#u-cf7-entries-tabs').on('click', '.delete_row', function(event) {
		if( confirm(u_cf7e_i18n.delete_row)){
			$(this).closest('tr').remove();
		}
		return false;
	});
});