/**
 * Set a click event on a link to log a download.
 *
 * @author Simon Wheatley
 * 
 * @param id_to_log string A jQuery selector for the link to log
 * @param post_id int The post ID of the download post on which to log the download
 * @return void
 */
function cftp_ld_init_logger( id_to_log, post_id, use_link ) {
	var $link = jQuery( id_to_log )
		.find( 'a' )
		.click( cftp_ld_log_download )
		.data( 'post_id', post_id );
	if ( use_link ) {
		console.log( 'Use link' );
		$link.data( 'use_link', true );
	}
}

/**
 * Callback function to be bound to a click event 
 *
 * @author Simon Wheatley
 * 
 * @param id_to_log string A jQuery selector for the link to log
 * @param post_id int The post ID of the download post on which to log the download
 * @return void
 */
function cftp_ld_log_download( e ) {
	var post_id = jQuery( this ).data( 'post_id' ),
	data = {
		action: 'cftp_log_download',
		post_id: post_id,
		user_id: cftp_ld.user_id
	};
	if ( jQuery( this ).data( 'use_link' ) )
		data.link = jQuery( this ).attr( 'href' );

	jQuery
		.get( cftp_ld.ajax_url, data, cftp_ld_log_download_success, 'json' )
		.error( function() { alert( 'Sorry, something has gone wrong. Please try again or contact us to report this issue.' ); } );
	e.preventDefault();
}

function cftp_ld_log_download_success( data ) {
	window.location.href = data.redirect;
}
