<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Hook_Ajax_Get_Parent_Posts extends SP_Hook {
	protected $tag = 'wp_ajax_pc_get_parent_posts';

	public function run() {
		global $wpdb;

		// Check nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'sp_ajax_sc_gpp' ) ) {
			echo '-1';

			return;
		}

		$identifier = esc_sql( $_POST['identifier'] );

		$ptl_manager = new SP_Connection_Manager();

		if ( isset( $_POST['by_slug'] ) && $_POST['by_slug'] == 'true' ) {
			$ptl = $ptl_manager->get_link_by_slug( $identifier );
		} else {
			$ptl = $ptl_manager->get_link( $identifier );
		}

		// Get children
		$parent_posts = get_posts( array(
				'post_type'      => $ptl->get_parent(),
				'posts_per_page' => - 1,
				'orderby'        => 'title',
				'order'          => 'ASC'
		) );

		$json_posts = array();

		if ( count( $parent_posts ) > 0 ) {
			foreach ( $parent_posts as $parent_post ) {
				$json_posts[$parent_post->ID] = $parent_post->post_title;
			}
		}

		// Send the JSON
		wp_send_json( $json_posts );

		exit; // Better safe than sorry lol
	}
}