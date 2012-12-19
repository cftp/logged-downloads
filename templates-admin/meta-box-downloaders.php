<?php if ( ! defined( 'ABSPATH' ) ) { die( 'No direct access.' ); } ?>

<?php if ( $downloaders ) : ?>

	<ul>
		<?php foreach ( $downloaders as & $downloader ) : ?>

			<li>
				<?php
					echo esc_html( $downloader[ 'first_name' ] );
					echo esc_html( ' ' . $downloader[ 'last_name' ] );
					if ( $downloader[ 'role' ] )
						echo ', ' . $downloader[ 'role' ];
					if ( $downloader[ 'employer' ] )
						echo ' at ' . $downloader[ 'employer' ];
				?>
				(<a href="<?php echo esc_url( 'mailto:' . $downloader[ 'user_email' ] ); ?>"><?php echo esc_html( $downloader[ 'user_email' ] ); ?></a>)
			</li>

		<?php endforeach; ?>
	</ul>

<?php else : ?>

	<p><em>No recorded downloads yet.</em></p>

<?php endif; ?>
