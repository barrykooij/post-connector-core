<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Hook_Clean_Links extends SP_Hook {
	protected $tag = 'delete_post';

	public function run( $post_id ) {
		// Remove Hook
		remove_action( $this->tag, array( $this, 'run' ), $this->priority, $this->args );

		// Delete all links related to post_id
		$post_link_manager = new SP_Post_Link_Manager();
		$post_link_manager->delete_links_related_to( $post_id );

		// Re-add Hook
		add_action( $this->tag, array( $this, 'run' ), $this->priority, $this->args );
	}
}