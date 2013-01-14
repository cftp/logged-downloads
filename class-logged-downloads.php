<?php
 
/*  Copyright 2013 Code for the People Ltd

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

/**
 * 
 */
class CFTP_Logged_Downloads extends CFTP_Logged_Downloads_Plugin {
	
	/**
	 * A version for cache busting, DB updates, etc.
	 *
	 * @var string
	 **/
	public $version;
	
	/**
	 * Let's go!
	 *
	 * @return void
	 **/
	public function __construct() {
		$this->setup( 'logged-downloads', 'plugin' );

		$this->add_action( 'wp_ajax_cftp_log_download', 'ajax_log_download' );
		$this->add_action( 'wp_ajax_nopriv_cftp_log_download', 'ajax_log_download' );
		$this->add_action( 'admin_init' );
		$this->add_action( 'add_meta_boxes_logged_download', 'add_meta_boxes' );
		$this->add_action( 'init' );
		$this->add_action( 'save_post', null, null, 2 );
		$this->add_action( 'wp_enqueue_scripts', 'wp_enqueue_scripts_early', 0 );
		$this->add_action( 'load-post.php', 'load_post_screen' );

		$this->add_filter( 'ld_wrap_content', 'wrap_content' );
		$this->add_filter( 'the_content' );
		$this->add_filter( 'wp_generate_attachment_metadata', null, null, 2 );
		
		$this->version = 3;
	}
	
	/**
	 * Hook fired on the post editing screen. Controller for sending a CSV file of the post's
	 * "Downloaders" to the user's browser.
	 *
	 * @author John Blackbourn
	 * @since 1.2
	 * @return null
	 */
	function load_post_screen() {

		$post = get_post( $_GET['post'] );

		if ( 'logged_download' != $post->post_type )
			return;
		if ( !isset( $_GET['downloaders'] ) )
			return;
		if ( 'csv' != $_GET['downloaders'] )
			return;

		check_admin_referer( "downloaders_csv_{$post->ID}" );

		$leechers = get_post_meta( $post->ID, '_cftp_logged_downloaded_leechers', true );

		if ( empty( $leechers ) )
			wp_die( 'No recorded downloads yet.' );

		header( sprintf( 'Content-Type: text/csv; charset=%s', get_bloginfo( 'charset' ) ) );
	#	The post name can be quite long so we'd better stick with the post ID in the filename for now
	#	header( sprintf( 'Content-Disposition: attachment; filename=downloaders-%s.csv', $post->post_name ) );
		header( sprintf( 'Content-Disposition: attachment; filename=downloaders-%s.csv', $post->ID ) );

		$file = fopen( 'php://output', 'w' );

		# Heading
	#	fputcsv( $file, array(
	#		sprintf( 'List of downloaders for "%s"', get_the_title( $post->ID ) ),
	#	) );

		# Blank line:
	#	fputcsv( $file, array() );

		# Column headers:
		fputcsv( $file, array(
			'First Name',
			'Last Name',
			'Role',
			'Employer',
			'Email Address'
		) );

		foreach ( $leechers as $leecher ) {

			# Row data:
			fputcsv( $file, array(
				$leecher['first_name'],
				$leecher['last_name'],
				$leecher['role'],
				$leecher['employer'],
				$leecher['user_email'] )
			 );

		}

		die();

	}

	function ajax_log_download() {
		$user_id = isset( $_GET[ 'user_id' ] ) ? absint( $_GET[ 'user_id' ] ) : 0;
		$post_id = isset( $_GET[ 'post_id' ] ) ? absint( $_GET[ 'post_id' ] ) : 0;
		$link = isset( $_GET[ 'link' ] ) ? esc_url( $_GET[ 'link' ] ) : false;
		$user = new WP_User( $user_id );
		$post = get_post( $post_id );
		$leechers = get_post_meta( $post_id, '_cftp_logged_downloaded_leechers', true );
		if ( !is_array( $leechers ) )
			$leechers = array();

		// An alternative to the AJAX logging might be to have them go through a URL which logs the
		// leecher, triggers a download file thing over HTTP, AND directs them back to the page.

		// @TODO: At this point we need to log the various new user meta information from Gravity Forms
		$leechers[ $user->ID ] = array( 
			'ID' => $user->ID, 
			'user_email' => $user->user_email,
			'first_name' => get_user_meta( $user->ID, 'first_name', true ),
			'last_name' => get_user_meta( $user->ID, 'last_name', true ),
			'employer' => get_user_meta( $user->ID, 'ir_employer', true ),
			'role' => get_user_meta( $user->ID, 'ir_role', true ),
		);
		update_post_meta( $post_id, '_cftp_logged_downloaded_leechers', $leechers );
		if ( $link ) {
			$url = $link;
		} else {
			$selected_attachment_id = get_post_meta( $post_id, '_cftp_logged_download_selected_attachment_id', true );
			$url = wp_get_attachment_url( $selected_attachment_id );
		}
		$data = array( 'redirect' => $url );
		echo json_encode( $data );
		exit;
	}
	
	function admin_init() {
		$this->maybe_upgrade();
	}

	function init() {
		
        $labels = array(
            'name' =>               'Downloads',
            'singular_name' =>      'Download',
            'add_new' =>            'Add New Download',
            'add_new_item' =>       'Create New Download',
            'edit_item' =>          'Edit Download',
            'new_item' =>           'New Download',
            'view_item' =>          'View Download',
            'search_items' =>       'Search Download',
            'not_found' =>          'No download found.',
            'not_found_in_trash' => 'No download found in Trash.',
            'all_items' =>          'All Download',
        );
        $args = array(
			'description' => 'Download, a single resource which can only be downloaded by logged in users.',
			'has_archive' => false,
			'hierarchical' => false,
			'show_in_menu' => true,
			'supports' => array( 'title', 'editor', 'thumbnail' ),
            'can_export' => true,
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
			'rewrite' => array( 'slug' => 'download' ),
			'menu_position' => 44
        );
		register_post_type( 'logged_download', $args );
	}
	
	function save_post( $post_id, $post ) {
		
		if ( 'logged_download' != $post->post_type )
			return;
		if ( ! isset( $_POST[ '_cftp_logged_downloads_nonce' ] ) )
			return;
	
		check_admin_referer( 'cftp_logged_downloads_selected_attachment', '_cftp_logged_downloads_nonce');
		
		if ( $selected_attachment_id = absint( $_POST[ 'cftp_logged_selected_attachment_id' ] ) )
			update_post_meta ( $post_id, '_cftp_logged_download_selected_attachment_id', $selected_attachment_id );
		else
			delete_post_meta ( $post_id, '_cftp_logged_download_selected_attachment_id' );
		
	}
	
	/**
	 * Hooks the WP add_meta_boxes_logged_download action.
	 * 
	 * @return void
	 **/
	public function add_meta_boxes( $post ) {
		add_meta_box( 'logged_download_select', 'Select Download', array( & $this, 'meta_box_select_download' ), 'logged_download', 'advanced', 'core' );
		add_meta_box( 'logged_download_downloaders', 'Downloaders', array( & $this, 'meta_box_downloaders' ), 'logged_download', 'advanced', 'core' );
	}
	
	function wp_enqueue_scripts_early() {
		if ( ! is_singular( 'logged_download' ) && ! is_post_type_archive( 'logged_download' ) )
			return;
		wp_enqueue_script( 'logged-downloads', $this->url( 'js/logged-downloads.js' ), array( 'jquery' ), time() );
		$data = array( 
			'user_id' => get_current_user_id(),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script( 'logged-downloads', 'cftp_ld', $data );
	}
	
	/**
	 * Hooks the WP wp_generate_attachment_metadata filter to add information 
	 * about filesize, human readable and machine useful.
	 * 
	 * @param array $metadata The array of metadata
	 * @param int $attachment_id The ID of the Attachment post
	 * @return array The metadata array
	 */
	function wp_generate_attachment_metadata( $metadata, $attachment_id ) {
		$dirs = wp_upload_dir();
		$metadata[ 'cftp_bytes_filesize' ] = filesize( trailingslashit( $dirs[ 'basedir' ] ) . $metadata[ 'file' ] );
		$metadata[ 'cftp_hr_filesize' ] = wp_convert_bytes_to_hr( $metadata[ 'cftp_bytes_filesize' ] );
		// People won't understand B for Bytes, so use decimals of kB.
		if ( preg_match( '/(\d+)B/i', $metadata[ 'cftp_hr_filesize' ], $matches ) )
			$metadata[ 'cftp_hr_filesize' ] = ($matches[1] / 1024) . "kB" ;
		// Reduce to 1 decimal place
		if ( preg_match( '/(\d+.\d+)(\w+)/i', $metadata[ 'cftp_hr_filesize' ], $matches ) )
			$metadata[ 'cftp_hr_filesize' ] = sprintf( "%01.1f", $matches[1] ) . $matches[2];
		return $metadata;
	}

	/**
	 * Hooks the WP the_content filter to add the download link section
	 * to the bottom of logged_download posts which have a selected
	 * attachment ID.
	 * 
	 * @filter the_content
	 * 
	 * @param string $content The content
	 * @return string The content
	 */
	function the_content( $content ) {
		$post = get_post( get_the_ID() );
		if ( 'logged_download' != $post->post_type )
			return $content;
		
		$vars = array();
		$vars[ 'post_id' ] = $post->ID;
		$vars[ 'token' ] = 'the_content';
		$vars[ 'selected_attachment_id' ] = get_post_meta( $post->ID, '_cftp_logged_download_selected_attachment_id', true );
		$content = $content . $this->capture( 'content-download-link.php', $vars );
		
		return $content;
	}

	/**
	 * Hooks the Logged Downloads ld_wrap_content filter to wrap a
	 * block of HTML in the relevant DIV element and initiate logging
	 * on it. Any link (A) element in that block will have clicks
	 * logged as downloads.
	 * 
	 * Must be used in The Loop.
	 * 
	 * If the current post in the loop is not a logged_download post
	 * then nothing will be logged.
	 * 
	 * @filter ld_wrap_content
	 * 
	 * @param string $content An HTML block to wrap with logging
	 * @return string The 
	 */
	function wrap_content( $content ) {
		$post = get_post( get_the_ID() );
		if ( 'logged_download' != $post->post_type )
			return $content;
		
		$vars = array();
		$vars[ 'post_id' ] = $post->ID;
		$vars[ 'token' ] = uniqid();
		$vars[ 'selected_attachment_id' ] = false;
		$vars[ 'content' ] = $content;
		$content = $this->capture( 'wrap-download-link.php', $vars );
		
		return $content;
	}
	
	// CALLBACKS 
	// =========
	
	function meta_box_select_download( $post, $box ) {
		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'attachment',
			'post_parent' => $post->ID,
			'post_status' => 'inherit',
			'fields' => 'ids',
		);
		$attachments = new WP_Query( $args );
		$vars = array();
		$vars[ 'selected_attachment_id' ] = get_post_meta( $post->ID, '_cftp_logged_download_selected_attachment_id', true );
		$vars[ 'attachments' ] = $attachments;
		$this->render_admin( 'meta-box-select_download.php', $vars );
	}
	
	function meta_box_downloaders( $post, $box ) {
		$downloaders = get_post_meta( $post->ID, '_cftp_logged_downloaded_leechers', true );
		$vars = array();
		$vars[ 'downloaders' ] = $downloaders;
		$vars[ 'downloaders_csv' ] = wp_nonce_url( add_query_arg( array(
			'downloaders' => 'csv',
		), get_edit_post_link( $post->ID ) ), "downloaders_csv_{$post->ID}" );
		$this->render_admin( 'meta-box-downloaders.php', $vars );
	}

	// UTILITIES
	// =========
	
	/**
	 * Checks the DB structure is up to date.
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	function maybe_upgrade() {
		global $wpdb;
		$option_name = 'cftp-logged-downloads-version';
		$version = get_option( $option_name, 0 );
		
		$done_upgrade = false;

		if ( $version == $this->version )
			return;

		if ( $version < 1 ) {
			flush_rewrite_rules();
			error_log( "Logged Downloads: Flushed rewrite rules" );
		}

		if ( $version < 2 ) {
			$args = array(
				'post_type' => 'logged_download',
				'post_status' => 'any',
				'fields' => 'ids',
				'posts_per_page' => -1,
			);
			$downloads = new WP_Query( $args );
			foreach ( $downloads->posts as $download_id ) {
				if ( $leechers = get_post_meta( $download_id, '_cftp_logged_downloaded_leechers', true ) ) {
					foreach ( $leechers as $user_id => & $leecher ) {
						$user = new WP_User( $user_id );
						$leecher[ 'last_name' ] = get_user_meta( $user->ID, 'last_name', true );
					}
					update_post_meta( $download_id, '_cftp_logged_downloaded_leechers', $leechers );
				}
			}
			error_log( "Logged Downloads: Updated leechers" );
		}

		error_log( "Logged Downloads: Done upgrade" );
		update_option( $option_name, $this->version );
	}
	
}

$GLOBALS[ 'cftp_logged_downloads' ] = new CFTP_Logged_Downloads;

