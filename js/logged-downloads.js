
function cftp_init_logger( id_to_log ) {
	var post_id = Number( id_to_log.replace( /#cftp\-logged\-download\-link\-/, '' ) );
	jQuery( id_to_log )
		.find( 'a' )
		.click( cftp_log_download )
		.data( 'post_id', post_id );
}

function cftp_log_download( e ) {
	var post_id = jQuery( this ).data( 'post_id' ),
	data = {
		action: 'cftp_log_download',
		user_id: cftp_ld.user_id,
		post_id: post_id
	};
	jQuery
		.get( cftp_ld.ajax_url, data, cftp_log_download_success, 'json' )
		.error( function() { alert( 'Sorry, something has gone wrong. Please try again or contact us to report this issue.' ); } );
	e.preventDefault();
}

function cftp_log_download_success( data ) {
	window.location.href = data.redirect;
}
