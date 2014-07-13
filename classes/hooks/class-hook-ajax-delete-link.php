<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Hook_Ajax_Delete_Link extends SP_Hook {
	protected $tag = 'wp_ajax_sp_delete_link';

	/**
	 * Hook into admin AJAX to delete a link
	 *
	 * @access public
	 * @return void
	 */
	public function run() {
		$post_id = $_POST['id'];

		// Check nonce
		check_ajax_referer( 'post-connector-ajax-nonce-omgrandomword', 'nonce' );

		// Check if user is allowed to do this
		if ( ! current_user_can( SP_Cap_Manager::get_capability( $post_id ) ) ) {
			return;
		}

		//  Load post
		$target_post = get_post( $post_id );

		// Only delete post type we control
		if ( $target_post->post_type != SP_Constants::CPT_LINK ) {
			return;
		}

		// Delete link
		$post_link_manager = new SP_Post_Link_Manager();
		$post_link_manager->delete( $target_post->ID );

		// Generate JSON response
		$response = json_encode( array( 'success' => true ) );
		header( 'Content-Type: application/json' );
		echo $response;

		// Bye
		exit();
	}

}