<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

abstract class SP_Connection_Manager_Core {

	public function __construct() {

	}

	/**
	 * Method to add a Connection
	 *
	 * @access private
	 *
	 * @param SP_Connection $connection
	 *
	 * @return SP_Connection
	 */
	private function add( $connection ) {

		// Check if link exists with same slug
		if ( $this->slug_exists( $connection->get_slug() ) ) {
			return false;
		}

		// Create post type
		$connection_id = wp_insert_post( array(
			'post_title'  => 'Post Connector Connection',
			'post_type'   => SP_Constants::CPT_PT_LINK,
			'post_status' => 'publish'
		) );

		// Set the Connection ID
		$connection->set_id( $connection_id );

		// Create post meta
		add_post_meta( $connection_id, SP_Constants::PM_PTL_SLUG, $connection->get_slug() );
		add_post_meta( $connection_id, SP_Constants::PM_PTL_TITLE, $connection->get_title() );
		add_post_meta( $connection_id, SP_Constants::PM_PTL_PARENT, $connection->get_parent() );
		add_post_meta( $connection_id, SP_Constants::PM_PTL_CHILD, $connection->get_child() );

		add_post_meta( $connection_id, SP_Constants::PM_PTL_ADD_NEW, $connection->get_add_new() );
		add_post_meta( $connection_id, SP_Constants::PM_PTL_ADD_EXISTING, $connection->get_add_existing() );
		add_post_meta( $connection_id, SP_Constants::PM_PTL_APDC, $connection->get_after_post_display_children() );

		/**
		 * Action: 'pc_after_connection_add' - Action in ran after a connection is added
		 *
		 * @api int $connection The Connection
		 */
		do_action( 'pc_after_connection_add', $connection );

		// Backwards compatibility
		do_action( 'sp_after_post_type_link_add', $connection_id );

		// Return link
		return $connection;
	}

	/**
	 * Method to edit a PostTypeLink
	 *
	 * @access private
	 *
	 * @param SP_Connection $connection
	 *
	 * @return SP_Connection
	 */
	private function edit( $connection ) {

		// Update post meta
		//update_post_meta( $link->get_id(), SP_Constants::PM_PTL_SLUG, $link->get_slug() ); // SLUG CAN'T BE EDITED BECAUSE OF PERFORMANCE PATCH #123, THIS MIGHT CHANGE IN THE FUTURE.
		update_post_meta( $connection->get_id(), SP_Constants::PM_PTL_TITLE, $connection->get_title() );
		update_post_meta( $connection->get_id(), SP_Constants::PM_PTL_PARENT, $connection->get_parent() );
		update_post_meta( $connection->get_id(), SP_Constants::PM_PTL_CHILD, $connection->get_child() );

		update_post_meta( $connection->get_id(), SP_Constants::PM_PTL_ADD_NEW, $connection->get_add_new() );
		update_post_meta( $connection->get_id(), SP_Constants::PM_PTL_ADD_EXISTING, $connection->get_add_existing() );
		update_post_meta( $connection->get_id(), SP_Constants::PM_PTL_APDC, $connection->get_after_post_display_children() );

		/**
		 * Action: 'pc_after_connection_edit' - Action in ran after a connection is edited
		 *
		 * @api SP_Connection $connection The Connection
		 */
		do_action( 'pc_after_connection_edit', $connection );

		// Backwards compatibility
		do_action( 'sp_after_post_type_link_edit', $connection->get_id() );

		// Return link
		return $connection;
	}

	/**
	 * Method to get all PostTypeLink
	 *
	 * @param array $extra_args
	 *
	 * @access public
	 *
	 * @return array<SP_Connection>
	 */
	public function get_connections( $extra_args = array() ) {
		global $post;
		$old_post = $post; // wp_reset_postdata() won't work in backend

		// It's not allowed to change the post_type arg
		unset( $extra_args['post_type'] );

		// Build WP_Query args
		$args = array_merge( array(
			'post_type'      => SP_Constants::CPT_PT_LINK,
			'posts_per_page' => '-1'
		), $extra_args );

		// Do WP_Query
		$connections      = array();
		$connection_query = new WP_Query( $args );

		if ( $connection_query->have_posts() ) {
			while ( $connection_query->have_posts() ) {
				$connection_query->the_post();

				// Create the connection
				$connection = new SP_Connection();

				// Set the connection properties
				$connection->set_id( $connection_query->post->ID );
				$connection->set_slug( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_SLUG, true ) );
				$connection->set_title( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_TITLE, true ) );
				$connection->set_parent( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_PARENT, true ) );
				$connection->set_child( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_CHILD, true ) );
				$connection->set_add_new( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_ADD_NEW, true ) );
				$connection->set_add_existing( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_ADD_EXISTING, true ) );
				$connection->set_after_post_display_children( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_APDC, true ) );

				/**
				 * Action: 'pc_connection_init' - Action that is ran after the connection is initialized
				 *
				 * @api SP_Connection $connection The Connection
				 */
				$connection = apply_filters( 'pc_connection_init', $connection );

				$connections[] = $connection;
			}
		}

		wp_reset_postdata();

		$post = $old_post; // reset because wp_reset_postdata() won't work in backend

		return $connections;
	}

	/**
	 * Method to get a Connection by id
	 *
	 * @access public
	 *
	 * @param int $id
	 *
	 * @return SP_Connection
	 */
	public function get_connection( $id ) {

		$connection       = null;
		$connection_query = new WP_Query( array(
			'p'              => $id,
			'post_type'      => SP_Constants::CPT_PT_LINK,
			'posts_per_page' => '1',
		) );

		if ( $connection_query->have_posts() ) {
			$connection_query->next_post();

			// Create the connection
			$connection = new SP_Connection();

			// Set the connection properties
			$connection->set_id( $connection_query->post->ID );
			$connection->set_slug( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_SLUG, true ) );
			$connection->set_title( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_TITLE, true ) );
			$connection->set_parent( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_PARENT, true ) );
			$connection->set_child( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_CHILD, true ) );
			$connection->set_add_new( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_ADD_NEW, true ) );
			$connection->set_add_existing( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_ADD_EXISTING, true ) );
			$connection->set_after_post_display_children( get_post_meta( $connection_query->post->ID, SP_Constants::PM_PTL_APDC, true ) );

			/**
			 * Action: 'pc_connection_init' - Action that is ran after the connection is initialized
			 *
			 * @api SP_Connection $connection The Connection
			 */
			$connection = apply_filters( 'pc_connection_init', $connection );

		}

		return $connection;
	}

	/**
	 * @deprecated 1.5.0
	 */
	public function get_link( $id ) {
		return $this->get_connection( $id );
	}

	/**
	 * Method to get a Connection by slug
	 *
	 * @access public
	 *
	 * @param String $slug
	 *
	 * @return SP_Connection
	 */
	public function get_connection_by_slug( $slug ) {

		// Default connection reponse
		$connection = null;

		// Build the custom arguments
		$args = array(
			'posts_per_page' => '1',
			'meta_query'     => array(
				array(
					'key'   => SP_Constants::PM_PTL_SLUG,
					'value' => $slug
				)
			)
		);

		// Get the connection
		$connections = $this->get_connections( $args );

		// If we've got a connection, make sure to return a single SP_Connection object
		if ( count( $connections ) > 0 ) {
			$connection = array_shift( $connections );
		}

		// Return connection
		return $connection;
	}

	/**
	 * @deprecated 1.6.0
	 */
	public function get_link_by_slug( $slug ) {
		return $this->get_connection_by_slug( $slug );
	}

	/**
	 * Public method to save a PostTypeLink. This method will create a PostTypeLink if there is no id set in $link
	 *
	 * @access public
	 *
	 * @param SP_Connection $link
	 *
	 * @return SP_Connection $result
	 */
	public function save( $connection ) {

		// Check the argument type
		if ( get_class( $connection ) != 'SP_Connection' ) {
			trigger_error( 'Parameter is an incorrect object type' );
			exit();
		}

		if ( 0 == $connection->get_id() ) {
			// Add new link
			$result = $this->add( $connection );
		} else {
			// Edit link
			$result = $this->edit( $connection );
		}

		return $result;
	}

	/**
	 * Method to delete a PostTypeLink
	 *
	 * @access public
	 *
	 * @param int $id
	 *
	 * @return boolean
	 */
	public function delete( $id ) {

		/**
		 * Action: 'pc_before_connection_delete' - Action that is ran before the connection is deleted
		 *
		 * @api int $id The Connection id
		 */
		do_action( 'pc_before_connection_delete', $id );

		// Backwards compatibility
		do_action( 'sp_before_post_type_link_delete', $id ); // Can't remove this because of BC

		// Delete all links
		$post_link_manager = new SP_Post_Link_Manager();
		$links             = $post_link_manager->get_all_links( $id );
		if ( count( $links ) > 0 ) {
			foreach ( $links as $link ) {
				wp_delete_post( $link->ID, true );
			}
		}

		// Delete the connection
		wp_delete_post( $id, true );

		/**
		 * Action: 'pc_after_connection_delete' - Action that is ran after the connection is deleted
		 *
		 * @api int $id The Connection id
		 */
		do_action( 'pc_after_connection_delete', $id );

		// Backwards compatibility
		do_action( 'sp_after_post_type_link_delete', $id ); // Can't remove this because of BC

		return true;
	}

	/**
	 * Method to check if a connection exists with given slug.
	 *
	 * @since  1.3.0.0
	 * @access public
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function slug_exists( $slug ) {
		$query_check = new WP_Query( array(
			'post_type'  => SP_Constants::CPT_PT_LINK,
			'meta_query' => array(
				array(
					'key'   => SP_Constants::PM_PTL_SLUG,
					'value' => $slug
				)
			)
		) );

		// Return false if PostTypeLink already exists
		return $query_check->have_posts();
	}

}