<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Hook_Ajax_Connection_Generate_Slug extends SP_Hook {
	protected $tag = 'wp_ajax_pc_connection_generate_slug';

	/**
	 * Hook into admin AJAX to generate slug based on title
	 *
	 * @access public
	 * @return void
	 */
	public function run() {

		// Check nonce
		check_ajax_referer( 'post-connector-ajax-nonce-omgrandomword', 'nonce' );

		// Generate slug
		echo json_encode( array( 'slug' => sanitize_title_with_dashes( $_POST['title'] ) ) );

		// Bye
		exit();
	}

}