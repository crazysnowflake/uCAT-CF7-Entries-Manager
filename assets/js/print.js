jQuery( document ).ready( function ( $ ) {
	$('#include_submission').change(function(event) {
		if( $(this).is(':checked') ){
			$('#submission').show();
		}else{
			$('#submission').hide();
		}
	});
	$('#include_comments').change(function(event) {
		if( $(this).is(':checked') ){
			$('#commentsdiv').show();
		}else{
			$('#commentsdiv').hide();
		}
	});
});