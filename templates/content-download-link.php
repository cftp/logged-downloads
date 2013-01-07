<?php
	// WARNING! Override this template at your own risk! Note that the 
	//          Javascript which actually logs the download DEPENDS on the
	//          ID of the containing DIV element AND the SCRIPT element 
	//          containing the call to the cftp_ld_init_logger function.
?>
<div class="cftp-logged-download-link" id="cftp-logged-download-link-<?php echo esc_attr( $token ); ?>-<?php echo absint( $post_id ); ?>">

	<?php if ( $selected_attachment_id && is_user_logged_in() ) : ?>
	
		<?php if ( $selected_attachment_id ) : ?>
			<p>
				Download this resource: 
				<a href="<?php echo wp_get_attachment_url( $selected_attachment_id ); ?>">
					<?php echo get_the_title( $selected_attachment_id ); ?>
				</a>
					(<?php
						if ( preg_match( '/^.*?\.(\w+)$/', get_attached_file( $selected_attachment_id ), $matches ) )
							echo esc_html( strtoupper( $matches[1] ) );
						else
							echo esc_html( strtoupper( str_replace( 'image/', '', get_post_mime_type( $selected_attachment_id ) ) ) );
						$metadata = get_post_meta( $selected_attachment_id, '_wp_attachment_metadata', true );
						echo esc_html( isset( $metadata[ 'cftp_hr_filesize' ] ) ?  ', ' . $metadata[ 'cftp_hr_filesize' ] : '' );
					?>)
			</p>
		<?php endif; ?>

		<script type="text/javascript">
		//<![CDATA[
			cftp_ld_init_logger( '#cftp-logged-download-link-<?php echo esc_attr( $token ); ?>-<?php echo absint( $post_id ); ?>', <?php echo absint( $post_id ); ?>, false );
		//]]>
		</script>
		
	<?php elseif ( ! is_user_logged_in()  ) :  ?>
		
		<p>
			<em>You must be logged in to access this resource: 
				<strong>log in below</strong>, if you have a username and password, or 
				<strong><a href="<?php 
					echo add_query_arg( 
							array( 'action' => 'register' ), 
							wp_login_url( get_permalink( $post_id ) ) 
						); ?>">register</a></strong></em>
		</p>
		
		<p><?php wp_login_form( array( 'redirect' => get_permalink( $post_id ) ) ); ?></p>
		
	<?php endif; ?>
	
</div>
