<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_CO_Filter_After_Post extends SP_Filter {
	protected $tag = 'the_content';
	protected $priority = 99;

	/**
	 * Generate the post list
	 *
	 * @param $slug
	 * @param $title
	 * @param $posts
	 *
	 * @return string
	 */
	private function create_post_list( $slug, $title, $posts ) {

		$content = "<div class='pc-post-list pc-{$slug}'>\n";

		// Output the relation title
		$content .= "<h3>" . $title . "</h3 > \n";

		// Open the list
		$content .= "<ul>\n";

		foreach ( $posts as $pc_post ) {

			// Output the linked post
			$content .= "<li><a href='" . get_permalink( $pc_post->ID ) . "'>" . $pc_post->post_title . "</a></li>\n";

		}

		// Close the wrapper div
		$content .= "</ul>\n";
		$content .= "</div>\n";

		return $content;

	}

	/**
	 * the_content filter that will add linked posts to the bottom of the main post content
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function run( $content ) {
		/**
		 * Wow, what's going on here?! Well, setup_postdata() sets a lot of variables but does not change the $post variable.
		 * All checks return the main queried ID but we want to check if this specific filter call is the for the 'main' content.
		 * The method setup_postdata() does global and set the $id variable, so we're checking that.
		 */
		global $id;

		// Only run on single
		if ( ! is_singular() || ! is_main_query() || $id != get_queried_object_id() ) {
			return $content;
		}

		$ptl_manager = new SP_Connection_Manager();

		// Add a meta query so we only get relations that have the PM_PTL_APDC or PM_PTL_APDP set to true (1)
		$args = array(
			'meta_query' => array(
				array(
					'key'   => SP_Constants::PM_PTL_APDC,
					'value' => '1'
				)
			)
		);

		// Get the connections
		$relations = $ptl_manager->get_connections( $args );

		// Check relations
		if ( count( $relations ) > 0 ) {

			// Post Link Manager
			$pl_manager = new SP_Post_Link_Manager();

			// Current post ID
			$post_id = get_the_ID();

			// Loop relations
			foreach ( $relations as $relation ) {

				// Check if this relation allows children links to show
				if ( '1' === $relation->get_after_post_display_children() ) {
					$children_relations[] = $relation;
				}

			}


			// Opening the wrapper div
			$content .= "<div class='pc-post-children'>\n";

			foreach ( $relations as $relation ) {

				// Get the linked posts
				$pc_posts = $pl_manager->get_children( $relation->get_slug(), $post_id );

				if ( count( $pc_posts ) > 0 ) {

					$content .= $this->create_post_list( $relation->get_slug(), $relation->get_title(), $pc_posts );

				}

			}

			// Close the wrapper div
			$content .= "</div>\n";


		}

		return $content;
	}
}