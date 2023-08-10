<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Hook_Link_Post_Screen extends SP_Hook {
	protected $tag = 'admin_menu';

	public function run() {
		$this->check_if_allowed();

		$this->handle_create_link();
		$this->handle_bulk_link();

		// Add Page
		$screen_hook = add_submenu_page( null, 'LinkPostScreen', 'LinkPostScreen', 'edit_posts', 'link_post_screen', array( $this, 'link_post_screen_content' ) );

		// Init Screen
		add_action( 'load-' . $screen_hook, array( $this, 'init_screen' ) );
	}

	/**
	 * Check if the current user is allowed to create an existing link for this connection
	 */
	private function check_if_allowed() {
		// Check if GET var is set
		if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'link_post_screen' ) && isset( $_GET['sp_pt_link'] ) ) {

			// Check if user is allowed to do this by capability
			$parent = SP_Parent_Param::get_current_parent( $_GET['sp_parent'] );
			if ( ! current_user_can( SP_Cap_Manager::get_capability( $parent[0] ) ) ) {
				wp_die( __( "You're not allowed to do this.", 'post-connector' ) );
			}

			// Check if user is allowed to do this
			$ptl_manager = new SP_Connection_Manager();
			$ptl         = $ptl_manager->get_link( $_GET['sp_pt_link'] );
			if ( $ptl->get_add_existing() != '1' ) {
				wp_die( __( "You're not allowed to do this.", 'post-connector' ) );
			}

		}
	}

	/**
	 * Handle the create link action
	 */
	private function handle_create_link() {

		// Check if link is chosen
		if ( isset( $_GET['sp_post_link'] ) ) {

			// Check if all vars are set
			if ( ! isset( $_GET['sp_pt_link'] ) || ! isset( $_GET['sp_parent'] ) || ! isset( $_GET['sp_post_link'] ) ) {
				return;
			}

			// Check if user is allowed to do this
			if ( ! current_user_can( SP_Cap_Manager::get_capability( $_GET['sp_post_link'] ) ) ) {
				return;
			}

			// Get parent
			$parent = SP_Parent_Param::get_current_parent( $_GET['sp_parent'] );

			// Create link
			$post_link_manager = new SP_Post_Link_Manager();

			// Check what way we're linking
			if ( 1 == $parent[2] ) {
				// Create a 'backwards' child < parent link
				$post_link_manager->add( $_GET['sp_pt_link'], $_GET['sp_post_link'], $parent[0] );
			} else {
				// Create a 'normal' parent > child link
				$post_link_manager->add( $_GET['sp_pt_link'], $parent[0], $_GET['sp_post_link'] );
			}

			// Send back
			$redirect_url = get_admin_url() . "post.php?post={$parent[0]}&action=edit";

			// Check if parent as a ptl
			if ( isset( $parent[1] ) && $parent[1] != '' ) {
				$redirect_url .= '&sp_pt_link=' . $parent[1];
			}

			// Check if there are any parents left
			$sp_parent_rest = SP_Parent_Param::strip_sp_parent_parent( $_GET['sp_parent'] );
			if ( $sp_parent_rest != '' ) {
				$redirect_url .= '&sp_parent=' . $sp_parent_rest;
			}

			wp_redirect( $redirect_url );
			exit;
		}

	}

	/**
	 * Handle the bulk creation of links
	 */
	private function handle_bulk_link() {

		if ( isset( $_POST['sp_bulk'] ) ) {

			// Get parent
			$parent = SP_Parent_Param::get_current_parent( $_GET['sp_parent'] );

			// Check if user is allowed to do this
			if ( ! current_user_can( SP_Cap_Manager::get_capability( $parent ) ) ) {
				return;
			}

			// Post Link Manager
			$post_link_manager = new SP_Post_Link_Manager();

			if ( count( $_POST['sp_bulk'] ) > 0 ) {
				foreach ( $_POST['sp_bulk'] as $bulk_post ) {

					// Check what way we're linking
					if ( 1 == $parent[2] ) {
						// Create a 'backwards' child < parent link
						$post_link_manager->add( $_GET['sp_pt_link'], $bulk_post, $parent[0] );
					} else {
						// Create a 'normal' parent > child link
						$post_link_manager->add( $_GET['sp_pt_link'], $parent[0], $bulk_post );
					}

				}
			}

			// Send back
			$redirect_url = get_admin_url() . "post.php?post={$parent[0]}&action=edit";

			// Check if parent as a ptl
			if ( isset( $parent[1] ) && $parent[1] != '' ) {
				$redirect_url .= '&sp_pt_link=' . $parent[1];
			}

			// Check if there are any parents left
			$sp_parent_rest = SP_Parent_Param::strip_sp_parent_parent( $_GET['sp_parent'] );
			if ( $sp_parent_rest != '' ) {
				$redirect_url .= '&sp_parent=' . $sp_parent_rest;
			}

			wp_redirect( $redirect_url );
			exit;

		}

	}

	/**
	 * Init screen, add screen option to screen
	 */
	public function init_screen() {
		add_screen_option( 'per_page', array( 'label' => 'Posts', 'default' => 20, 'option' => 'post_connector_per_page' ) );
	}

	/**
	 * The screen content
	 */
	public function link_post_screen_content() {

		// Get the connection
		$ptl_manager    = new SP_Connection_Manager();
		$connection = $ptl_manager->get_link( $_GET['sp_pt_link'] );

		// Parent
		$parent = SP_Parent_Param::get_current_parent( $_GET['sp_parent'] );

		// Get child post type
		if ( '1' == $parent[2] ) {
			$post_type = get_post_type_object( $connection->get_parent() );
		} else {
			$post_type = get_post_type_object( $connection->get_child() );
		}

		// Setup cancel URL
		$cancel_url = get_admin_url() . "post.php?post={$parent[0]}&action=edit";

		// Check if parent as a ptl
		if ( isset( $parent[1] ) && $parent[1] != '' ) {
			$cancel_url .= '&sp_pt_link=' . $parent[1];
		}

		// Check if there are any parents left
		$sp_parent_rest = SP_Parent_Param::strip_sp_parent_parent( $_GET['sp_parent'] );
		if ( $sp_parent_rest != '' ) {
			$cancel_url .= '&sp_parent=' . $sp_parent_rest;
		}

		// Catch search string
		$search = null;
		if ( isset( $_POST['s'] ) && $_POST['s'] != '' ) {
			$search = $_POST['s'];
		}

		?>
		<div class="wrap">
			<h2>
				<?php echo $post_type->labels->name; ?>
				<a href="<?php echo $cancel_url; ?>" class="add-new-h2"><?php _e( 'Cancel linking', 'post-connector' ); ?></a>
			</h2>

			<form id="sp-list-table-form" method="post">
				<input type="hidden" name="page" value="<?php esc_attr_e( $_REQUEST['page'] ); ?>" />
				<?php
				// Create the link table
				$list_table = new SP_Create_Link_List_Table( $post_type->name, $connection );

				// Set the search
				$list_table->set_search( $search );

				// Load the items
				$list_table->prepare_items();

				// Add the search box
				$list_table->search_box( __( 'Search', 'post-connector' ), 'sp-search' );

				// Display the table
				$list_table->display();
				?>
			</form>
		</div>

	<?php
	}
}