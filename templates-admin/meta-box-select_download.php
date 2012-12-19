<?php if ( ! defined( 'ABSPATH' ) ) { die( 'No direct access.' ); } ?>
<?php wp_nonce_field( 'cftp_logged_downloads_selected_attachment', '_cftp_logged_downloads_nonce' ); ?>

<p>
	<?php if ( $attachments->have_posts() ) : ?>

		<label for="cftp_logged_selected_attachment_id">
			Select attachment to use as download:<br />
			<label><input type="radio" name="cftp_logged_selected_attachment_id" value="" <?php checked( ! $selected_attachment_id ); ?> /> None</label><br />
			<?php while ( $attachments->have_posts() ) : $attachments->the_post(); ?>
				<label>
					<input type="radio" name="cftp_logged_selected_attachment_id" value="<?php echo esc_attr( get_the_ID() ); ?>" <?php checked( $selected_attachment_id, get_the_ID() ); ?>>
					<?php the_title(); ?>
					(<?php
						if ( preg_match( '/^.*?\.(\w+)$/', get_attached_file( get_the_ID() ), $matches ) )
							echo esc_html( strtoupper( $matches[1] ) );
						else
							echo strtoupper( str_replace( 'image/', '', get_post_mime_type() ) );
					?>,
					<?php 
						$metadata = get_post_meta( $selected_attachment_id, '_wp_attachment_metadata', true );
	//					var_dump( $metadata );
						echo esc_html( $metadata[ 'cftp_hr_filesize' ] );
					?>)
					<?php // var_dump( get_post_meta( get_the_ID() ) ); ?>
				</label><br />
			<?php endwhile; ?>
		</label>

	<?php else : ?>
	
		<em>Please upload an attachment to select from.</em>
	
	<?php endif; ?>
</p>

<p><em>Save the post to refresh this view.</em></p>