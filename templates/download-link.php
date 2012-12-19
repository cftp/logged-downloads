<div class="cftp-logged-download-link" id="cftp-logged-download-link-<?php echo absint( get_the_ID() ); ?>">

	<?php if ( ! $selected_attachment_id ) : ?>
	
		<p><em>This download is not available yet</em></p>
	
	<?php elseif ( is_user_logged_in() ) : ?>
	
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
				?>,
				<?php 
					$metadata = get_post_meta( $selected_attachment_id, '_wp_attachment_metadata', true );
					echo esc_html( $metadata[ 'cftp_hr_filesize' ] );
				?>)
		</p>

		<script type="text/javascript">
		//<![CDATA[
			cftp_init_logger( '#cftp-logged-download-link-<?php echo absint( get_the_ID() ); ?>' );
		//]]>
		</script>
		
	<?php else :  ?>
		
		<p><em>You must be logged in to download this resource: <strong>log in below</strong>, if you have a username and password, or <strong><a href="<?php echo add_query_arg( array( 'action' => 'register' ), wp_login_url( get_permalink( get_the_ID() ) ) ); ?>">register</a></strong></em></p>
		
		<p><?php wp_login_form( array( 'redirect' => get_permalink( get_the_ID() ) ) ); ?></p>
		
	<?php endif; ?>
	
</div>
