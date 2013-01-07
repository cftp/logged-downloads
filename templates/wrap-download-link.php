<?php
	// WARNING! Override this template at your own risk! Note that the 
	//          Javascript which actually logs the download DEPENDS on the
	//          ID of the containing DIV element AND the SCRIPT element 
	//          containing the call to the cftp_ld_init_logger function.
?>
<div class="cftp-logged-download-link" id="cftp-logged-download-link-<?php echo esc_attr( $token ); ?>-<?php echo absint( $post_id ); ?>">

	<?php echo $content; // The HTML content, no escaping possible :S (think the_content filter) ?>
	
	<?php if ( is_user_logged_in() ) : ?>

		<script type="text/javascript">
		//<![CDATA[
			cftp_ld_init_logger( '#cftp-logged-download-link-<?php echo esc_attr( $token ); ?>-<?php echo absint( $post_id ); ?>', <?php echo absint( $post_id ); ?>, true );
		//]]>
		</script>
		
	<?php endif; ?>
	
</div>
