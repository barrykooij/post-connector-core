<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Post_Link_Manager {

	private $temp_child_order;

	/**
	 * Create query arguments used to fetch links
	 *
	 * @access private
	 *
	 * @param string $pt_slug
	 * @param int $post_id
	 * @param string $meta_key
	 *
	 * @return array
	 */
	private function create_link_args( $pt_slug, $meta_key, $post_id ) {
		$args = array(
			'context'        => SP_Constants::QUERY_CONTEXT,
			'sp_slug'        => $pt_slug,
			'post_type'      => SP_Constants::CPT_LINK,
			'posts_per_page' => - 1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => $meta_key,
					'value'   => $post_id,
					'compare' => '=',
				)
			)
		);

		return $args;
	}

	/**
	 * Get amount of links based on post type link id and (post) parent id
	 *
	 * @access private
	 *
	 * @param int $pt_link_id
	 * @param int $parent_id
	 *
	 * @return int
	 */
	private function get_link_count( $pt_link_id, $parent_id ) {
		$link_query = new WP_Query(
			array(
				'post_type'      => SP_Constants::CPT_LINK,
				'posts_per_page' => - 1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'meta_query'     => array(
					array(
						'key'     => SP_Constants::PM_PT_LINK,
						'value'   => $pt_link_id,
						'compare' => '=',
					),
					array(
						'key'     => SP_Constants::PM_PARENT,
						'value'   => $parent_id,
						'compare' => '=',
					),
				)
			)
		);

		// Reset global post variables
		wp_reset_postdata();

		return $link_query->found_posts;
	}

	/**
	 * Method to add a PostLink
	 *
	 * @access public
	 *
	 * @param int $pt_link_id
	 * @param int $parent_id
	 * @param int $child_id
	 *
	 * @return int ($link_id)
	 */
	public function add( $pt_link_id, $parent_id, $child_id ) {

		// Post Type Link Manager
		$post_type_link_manager = new SP_Connection_Manager();

		// Get the Post Type Link
		$ptl = $post_type_link_manager->get_link( $pt_link_id );

		// Create post link
		$link_id = wp_insert_post( array(
			'post_title'  => 'sp_' . $ptl->get_slug(),
			'post_type'   => SP_Constants::CPT_LINK,
			'post_status' => 'publish',
			'menu_order'  => $this->get_link_count( $pt_link_id, $parent_id ),
		) );

		// Create post meta
		add_post_meta( $link_id, SP_Constants::PM_PT_LINK, $pt_link_id );
		add_post_meta( $link_id, SP_Constants::PM_PARENT, $parent_id );
		add_post_meta( $link_id, SP_Constants::PM_CHILD, $child_id );

		// Action
		do_action( 'sp_after_link_add', $link_id );

		// Return link id
		return $link_id;
	}

	/**
	 * Delete a link
	 *
	 * @access public
	 *
	 * @param id $link_id
	 *
	 * @return void
	 */
	public function delete( $link_id ) {
		// Action
		do_action( 'sp_before_link_delete', $link_id );

		// Delete link
		wp_delete_post( $link_id, true );

		// Action
		do_action( 'sp_after_link_delete', $link_id );

		return;
	}

	/**
	 * Get children based on link_id and parent_id.
	 * It's possible to add extra arguments to the WP_Query with the $extra_args argument
	 *
	 * @access public
	 *
	 * @param string $pt_slug
	 * @param int $parent_id
	 * @param array $extra_args
	 *
	 * @return array
	 */
	public function get_children( $pt_slug, $parent_id, $extra_args = null ) {
		global $post;

		// Store current post
		$o_post = $post;

		// Only check if WP_DEBUG is true
		if ( WP_DEBUG ) {
			// Check if a PTL with given slug exists
			$ptl_manager = new SP_Connection_Manager();
			if ( ! $ptl_manager->slug_exists( $pt_slug ) ) {
				// Trigger error
				trigger_error( sprintf( __( "Slug '%s' does not exists" ), $pt_slug ), E_USER_NOTICE );

				// Return empty array for backwards compatibility
				return array();
			}
		}

		// Do WP_Query
		$link_args = $this->create_link_args( $pt_slug, SP_Constants::PM_PARENT, $parent_id );

		/*
		 * Check $extra_args for `posts_per_page`.
		 * This is the only arg that should be added to link query instead of the child query
		 */
		if ( isset( $extra_args['posts_per_page'] ) && ( ! isset( $extra_args['order'] ) || ! isset( $extra_args['orderby'] ) ) ) {

			// Set posts_per_page to link arguments
			$link_args['posts_per_page'] = $extra_args['posts_per_page'];
			unset( $extra_args['posts_per_page'] );
		}

		/*
		 * Check $extra_args for `order`.
		 * If 'order' is set without 'orderby', we should add it to the link arguments
		 */
		if ( isset( $extra_args['order'] ) && ! isset( $extra_args['orderby'] ) ) {
			$link_args['order'] = $extra_args['order'];
			unset( $extra_args['order'] );
		}

		// Create link query
		$link_query = new WP_Query( $link_args );

		// Store child ids
		// @todo remove the usage of get_the_id()
		$child_ids = array();
		while ( $link_query->have_posts() ) : $link_query->the_post();
			$child_ids[ get_the_id() ] = get_post_meta( get_the_id(), SP_Constants::PM_CHILD, true );
		endwhile;

		// Get children with custom args
		if ( $extra_args !== null && count( $extra_args ) > 0 ) {

			if ( ! isset( $extra_args['orderby'] ) ) {
				$this->temp_child_order = array();
				foreach ( $child_ids as $child_id ) {
					$this->temp_child_order[] = $child_id;
				}
			}

			// Get child again, but this time by $extra_args
			$children = array();

			//Child WP_Query arguments
			if ( count( $child_ids ) > 0 ) {
				$child_id_values = array_values( $child_ids );
				$child_post_type = get_post_type( array_shift( $child_id_values ) );
				
				// default child args
				$child_args      = array(
					'post_type'      => $child_post_type,
					'post__in'       => $child_ids,
				);

				// add post per page -1 to $child_args if post_per_page doesn't exist in $extra_args
				if ( ! isset( $extra_args[ 'posts_per_page' ] ) ) {
					$child_args[ 'posts_per_page' ] = -1;
				}			

				// Extra arguments
				$child_args = array_merge_recursive( $child_args, $extra_args );

				// Child Query
				$child_query = new WP_Query( $child_args );

				while ( $child_query->have_posts() ) : $child_query->the_post();
					// Add post to correct original sort key
					$children[ array_search( $child_query->post->ID, $child_ids ) ] = $child_query->post;
				endwhile;

				// Fix sorting
				if ( ! isset( $extra_args['orderby'] ) ) {
					uasort( $children, array( $this, 'sort_get_children_children' ) );
				}

			}
		} else {
			// No custom arguments found, get all objects of stored ID's
			$children = array();
			foreach ( $child_ids as $link_id => $child_id ) {
				$children[ $link_id ] = get_post( $child_id );
			}
		}

		// Reset global post variables
		wp_reset_postdata();

		// Restoring post
		$post = $o_post;

		// Return children
		return $children;
	}

	/**
	 * Custom sort method to reorder children
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return mixed
	 */
	public function sort_get_children_children( $a, $b ) {
		return array_search( $a->ID, $this->temp_child_order ) - array_search( $b->ID, $this->temp_child_order );
	}

	/**
	 * Get parent based on link_id and child_id.
	 * It's possible to add extra arguments to the WP_Query with the $extra_args argument
	 *
	 * @access public
	 *
	 * @param string $pt_slug
	 * @param int $child_id
	 *
	 * @return array
	 */
	public function get_parents( $pt_slug, $child_id ) {
		global $post;

		// opost
		$o_post = $post;

		// Do WP_Query
		$link_query = new WP_Query( $this->create_link_args( $pt_slug, SP_Constants::PM_CHILD, $child_id ) );

		$parents = array();

		while ( $link_query->have_posts() ) : $link_query->the_post();
			// Add post to correct original sort key
			$parents[ $link_query->post->ID ] = get_post( get_post_meta( $link_query->post->ID, SP_Constants::PM_PARENT, true ) );
		endwhile;

		// Reset global post variables
		wp_reset_postdata();

		// Set back
		$post = $o_post;

		return $parents;
	}

	/**
	 * Delete all links involved in given post_id
	 *
	 * @access public
	 *
	 * @param $post_id
	 */
	public function delete_links_related_to( $post_id ) {
		$involved_query = new WP_Query( array(
			'post_type'      => SP_Constants::CPT_LINK,
			'posts_per_page' => - 1,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => SP_Constants::PM_PARENT,
					'value'   => $post_id,
					'compare' => '=',
				),
				array(
					'key'     => SP_Constants::PM_CHILD,
					'value'   => $post_id,
					'compare' => '=',
				)
			)
		) );
		while ( $involved_query->have_posts() ) : $involved_query->the_post();
			wp_delete_post( $involved_query->post->ID, true );
		endwhile;
	}


	public function get_all_links( $pt_link_id ) {
		$link_query = new WP_Query(
			array(
				'post_type'      => SP_Constants::CPT_LINK,
				'posts_per_page' => - 1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'meta_query'     => array(
					array(
						'key'     => SP_Constants::PM_PT_LINK,
						'value'   => $pt_link_id,
						'compare' => '=',
					),
				)
			)
		);
		$links      = array();
		while ( $link_query->have_posts() ) : $link_query->the_post();
			$links[] = $link_query->post;
		endwhile;

		// Reset global post variables
		wp_reset_postdata();

		return $links;
	}

	/**
	 * Generate the generic list of posts
	 *
	 * @param array $posts
	 * @param string $slug
	 * @param string $link
	 * @param string $display_excerpt
	 * @param string $display_image
	 * @param string $header_tag
	 *
	 * @return string
	 */
	protected function generate_list( $posts, $slug, $link, $display_excerpt, $display_image, $header_tag ) {

		// String to bool, blame backwards compatibility
		if ( 'false' === $display_excerpt ) {
			$display_excerpt = false;
		}

		if ( 'false' === $display_image ) {
			$display_image = false;
		}

		$return = '';
		if ( count( $posts ) > 0 ) {
			$return .= "<div class='pc-post-list pc-{$slug}'>\n";

			$return .= "<ul class='subposts_show-childs subposts_slug_{$slug}'>\n";
			foreach ( $posts as $post ) {

				$return .= "<li class='subposts_child subposts_{$post->ID}'>";

				if ( true == $display_image ) {
					if ( has_post_thumbnail( $post->ID ) ) {

						/**
						 * Filter: 'pc_apdc_thumbnail_size' - Allows changing the thumbnail size of the thumbnail in de APDC section
						 *
						 * @api String $thumbnail_size The current/default thumbnail size.
						 */
						$thumb_size = apply_filters( 'pc_apdc_thumbnail_size', 'post-thumbnail' );

						$return .= "<a href='" . get_permalink( $post->ID ) . "'>";
						$return .= get_the_post_thumbnail( $post->ID, $thumb_size );
						$return .= "</a>";
					}
				}

				$return .= "<{$header_tag}>";
				if ( $link == 'true' ) {
					$return .= "<a href='" . get_permalink( $post->ID ) . "'>";
				}
				$return .= $post->post_title;
				if ( $link == 'true' ) {
					$return .= "</a>";
				}
				$return .= "</{$header_tag}>";

				// Excerpt
				if ( true == $display_excerpt ) {
					$the_excerpt = ( '' != $post->post_excerpt ) ? $post->post_excerpt : wp_trim_words( strip_tags( strip_shortcodes( $post->post_content ) ), apply_filters( 'pc_list_excerpt_length', apply_filters( 'excerpt_length', 20 ) ) );
					if ( $the_excerpt != '' ) {
						$return .= "<p>{$the_excerpt}</p>";
					}
				}

				$return .= "</li>\n";
			}
			$return .= "</ul>\n";

			$return .= "</div>\n";
		}

		return $return;
	}

	/**
	 * Generate the children list
	 *
	 * @param string $slug
	 * @param string $parent
	 * @param string $link
	 * @param string $excerpt
	 * @param string $header_tag
	 * @param bool $display_image
	 *
	 * @return string
	 */
	public function generate_children_list( $slug, $parent, $link, $excerpt, $header_tag = 'b', $display_image = false ) {

		// Make the header tag filterable
		$header_tag = apply_filters( 'pc_children_list_header_tag', $header_tag );

		// Get the children
		$children = $this->get_children( $slug, $parent );

		// Returned string
		$return = $this->generate_list( $children, $slug, $link, $excerpt, $display_image, $header_tag );

		// Restore global $post of main query
		wp_reset_postdata();

		return $return;
	}
}