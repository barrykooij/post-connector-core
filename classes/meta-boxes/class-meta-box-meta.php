<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Meta_Box_Meta {

	private $ptl;
	private $mb_id;

	public function __construct( $post_type_link ) {

		// Check if admin
		if ( ! is_admin() ) {
			return;
		}

		// Set vars
		$this->ptl = $post_type_link;

		$this->mb_id = 'sp_metabox_' . $this->ptl->get_child() . '_' . $this->ptl->get_parent();

		// Link parent of a Post Connector meta post must be set
		if ( ( ! isset( $_GET['sp_pt_link'] ) || $_GET['sp_pt_link'] != $this->ptl->get_id() ) && ! isset( $_POST['sp_meta'] ) ) {
			return;
		}

		// Add filters and hooks
		add_filter( 'wp_insert_post_data', array( $this, 'pre_save' ), '99', 2 );
		add_action( 'save_post', array( $this, 'save' ), 99, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'admin_head', array( $this, 'css_hide_mb' ) );

	}

	/**
	 * Check if current user is allowed to link posts
	 *
	 * @todo check if we can use $this->ptl instead of creating a new $ptl object
	 *
	 * @param $sp_pt_link
	 */
	private function check_if_allowed( $sp_pt_link ) {

		// Check if user is allowed to do this
		$ptl_manager = new SP_Connection_Manager();
		$ptl         = $ptl_manager->get_link( $sp_pt_link );

		if ( $ptl->get_add_new() != '1' ) {
			wp_die( __( "You're not allowed to do this." ) );
		}
	}

	/**
	 * Hide the metabox
	 */
	public function css_hide_mb() {
		echo '<style type="text/css">
						#' . $this->mb_id . '{display:none;}
					</style>';
	}

	/**
	 * Add metabox to dashboard
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_box() {

		// Add meta box to child
		add_meta_box(
				$this->mb_id,
				'Post Connector Meta',
				array( $this, 'callback' ),
				$this->ptl->get_child(),
				'side',
				'core'
		);


		// Add meta box to parent for backwards linking
		add_meta_box(
				$this->mb_id,
				'Post Connector Meta',
				array( $this, 'callback' ),
				$this->ptl->get_parent(),
				'side',
				'core'
		);

	}

	/**
	 * Metabox content
	 *
	 * @param object $post
	 *
	 * @access public
	 * @return void
	 */
	public function callback( $post ) {
		// Check if allowed
		$this->check_if_allowed( $_GET['sp_pt_link'] );

		echo "<div class='sp_mb_meta_content'>\n";

		// Add nonce
		wp_nonce_field( plugin_basename( __FILE__ ), 'sp_meta_nonce' );

		// Meta to tell it's our post
		echo "<input type='text' name='sp_meta' value='true' />\n";

		// Check if this is a new post
		if ( ! isset( $_GET['post'] ) ) {
			echo "<input type='text' name='sp_new' value='true' />\n";
		}

		// Set parent id
		if ( isset( $_GET['sp_parent'] ) ) {
			echo "<input type='text' name='sp_parent' value='{$_GET['sp_parent']}' />\n";
		}

		// Set post type link id
		if ( isset( $_GET['sp_pt_link'] ) ) {
			echo "<input type='text' name='sp_pt_link' value='{$_GET['sp_pt_link']}' />\n";
		}
		echo "</div>\n";

		echo "<p>This MetaBox is required by Post Connector.</p>\n";
	}

	/**
	 * Post status must always be 'Publish'
	 *
	 * @access public
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	function pre_save( $data ) {

		// Check if the sp_parent post data is set
		if ( ! isset ( $_POST['sp_parent'] ) ) {
			return $data;
		}

		// Get parent parameter
		$parent = SP_Parent_Param::get_current_parent( $_POST['sp_parent'] );

		// Check post type
		if ( '1' == $parent[2] ) {
			if ( $data['post_type'] != $this->ptl->get_parent() ) {
				return $data;
			}
		} else {
			if ( $data['post_type'] != $this->ptl->get_child() ) {
				return $data;
			}
		}

		// Check post status
		if ( $data['post_status'] == 'auto-draft' || $data['post_status'] == 'trash' ) {
			return $data;
		}

		// Set new post status
		$data['post_status'] = 'publish';

		return $data;
	}

	/**
	 * Save hook, create the link
	 *
	 * @param int    $post_id
	 * @param object $post
	 *
	 * @access public
	 * @return void
	 */
	public function save( $post_id, $post ) {

		// Check nonce
		if ( ! isset( $_POST['sp_meta_nonce'] ) || ! wp_verify_nonce( $_POST['sp_meta_nonce'], plugin_basename( __FILE__ ) ) ) {
			return;
		}

		// Check if user is allowed to do this
		if ( ! current_user_can( SP_Cap_Manager::get_capability( $post_id ) ) ) {
			return;
		}

		// Verify post is not a revision
		if ( wp_is_post_revision( $post_id ) ) {
			return $post_id;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check meta settings
		if ( ! isset( $_POST['sp_meta'] ) ) {
			return;
		}

		// Check post exists
		if ( $post == null ) {
			return;
		}

		// Check if it's a publish
		if ( $post->post_status != 'publish' ) {
			return;
		}

		// Check if allowed
		$this->check_if_allowed( $_POST['sp_pt_link'] );

		// Get parent id
		$parent = SP_Parent_Param::get_current_parent( $_POST['sp_parent'] );

		// Check if post type equals the child or parent post type
		if ( '1' == $parent[2] ) {
			if ( $post->post_type != $this->ptl->get_parent() ) {
				return;
			}
		} else {
			if ( $post->post_type != $this->ptl->get_child() ) {
				return;
			}
		}

		// Create link if it's a new post
		if ( isset( $_POST['sp_new'] ) ) {

			// Unhook the save hook to avoid an infinite loop
			remove_action( 'save_post', array( $this, 'save' ), 99 );

			// Create link
			$post_link_manager = new SP_Post_Link_Manager();

			// Check which way to link
			if ( '1' == $parent[2] ) {
				// Create a backwards link
				$post_link_manager->add( $_POST['sp_pt_link'], $post_id, $parent[0] );
			} else {
				// Create a 'normal' link
				$post_link_manager->add( $_POST['sp_pt_link'], $parent[0], $post_id );
			}

			// Re-hook hook
			add_action( 'save_post', array( $this, 'save' ), 99 );
		}

		// Send back
		$redirect_url = get_admin_url() . "post.php?post={$parent[0]}&action=edit";

		// Check if parent as a ptl
		if ( isset( $parent[1] ) && $parent[1] != '' ) {
			$redirect_url .= '&sp_pt_link=' . $parent[1];
		}

		// Check if there are any parents left
		$sp_parent_rest = SP_Parent_Param::strip_sp_parent_parent( $_POST['sp_parent'] );
		if ( $sp_parent_rest != '' ) {
			$redirect_url .= '&sp_parent=' . $sp_parent_rest;
		}

		// Redirecting user
		wp_redirect( $redirect_url );
		exit;
	}

}