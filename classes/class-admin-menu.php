<?php

class SP_Admin_Menu {

	private static $instance = null;

	private $license_manager = null;

	private function __construct() {
		// Hook the post handle to the admin init
		add_action( 'admin_init', array( $this, 'handle_post' ) );

		// AJAX trash
		add_action( 'wp_ajax_sp_delete_pt_link', array( $this, 'ajax_delete_link' ) );
	}

	public static function get() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup WP menu
	 *
	 * @access public
	 * @return void
	 */
	public function do_menu() {
		add_menu_page( 'Overview', 'Post Connector', 'manage_options', 'post_connector', array(
			$this,
			'screen_main'
		), 'div' );
		add_submenu_page( 'post_connector', __( 'Add New', 'post-connector' ), __( 'Add New', 'post-connector' ), 'manage_options', 'post_connector_edit', array(
			$this,
			'screen_edit'
		) );
	}

	/**
	 * Handle post requests
	 *
	 * @access public
	 * @return void
	 */
	public function handle_post() {
		if ( isset( $_POST['sp_save_check'] ) ) {

			// Check if user is allowed to do this	
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Check nonce
			if ( ! isset( $_POST['sp_edit_nonce'] ) || ! wp_verify_nonce( $_POST['sp_edit_nonce'], plugin_basename( __FILE__ ) ) ) {
				return;
			}

			$connection = new SP_Connection();

			// Check the id
			$id = ( ( isset( $_POST['id'] ) && $_POST['id'] != '' ) ? $_POST['id'] : null );
			$connection->set_id( $id );

			// Check the title
			if ( isset( $_POST['title'] ) && $_POST['title'] != '' ) {
				$connection->set_title( $_POST['title'] );
			}

			// Check the slug
			$slug = '';
			if ( isset( $_POST['slug'] ) && $_POST['slug'] != '' ) {
				$slug = $_POST['slug'];
			} else {
				$slug = sanitize_title_with_dashes( $_POST['title'] );
			}
			$connection->set_slug( $slug );

			// Parent
			$connection->set_parent( trim( $_POST['parent'] ) );

			// Child
			$connection->set_child( trim( $_POST['child'] ) );

			// Check the add new
			$add_new = 0;
			if ( isset( $_POST['add_new'] ) && $_POST['add_new'] == '1' ) {
				$add_new = 1;
			}
			$connection->set_add_new( $add_new );

			// Check the add existing
			$add_existing = 0;
			if ( isset( $_POST['add_existing'] ) && $_POST['add_existing'] == '1' ) {
				$add_existing = 1;
			}
			$connection->set_add_existing( $add_existing );

			// Check the display children
			$after_post_display_children = 0;
			if ( isset( $_POST['after_post_display_children'] ) && $_POST['after_post_display_children'] == '1' ) {
				$after_post_display_children = 1;
			}
			$connection->set_after_post_display_children( $after_post_display_children );

			/**
			 * Action: 'pc_connection_catch_post' - Action that is ran after the connection form data is post but before the connection is saved
			 *
			 * @api SP_Connection $connection The Connection
			 */
			$connection = apply_filters( 'pc_connection_catch_post', $connection );

			// Save the link
			$pt_link_manager = new SP_Connection_Manager();
			$pt_link_manager->save( $connection );

			wp_redirect( get_admin_url() . 'admin.php?page=post_connector' );
			exit();
		}
	}

	/**
	 * The sidebar
	 *
	 * @access private
	 * @return void
	 */
	public function sidebar() {
		?>
		<div class="pc-sidebar">

			<div class="pc-box">
				<div class="sidebar-header">
					<h3>Post Connector</h3>
				</div>

				<p><?php _e( 'Plugin version', 'post-connector' ); ?>: <?php echo SP_Constants::PLUGIN_VERSION_NAME; ?></p>

				<p><?php _e( 'PHP version:', 'post-connector' ); ?> <?php echo phpversion(); ?></p>
			</div>

			<?php
			 do_action( 'pc_sidebar' );
			?>

			<div class="pc-box">
				<h3 class="pc-title"><?php _e( 'More information', 'post-connector' ); ?></h3>

				<p><?php printf( __( "<a href='%s'>Configuration Guide</a>", 'post-connector' ), 'https://www.post-connector.com/documentation/configuration-guide/' ); ?></p>

				<p><?php printf( __( "<a href='%s'>Change log</a>", 'post-connector' ), 'https://www.post-connector.com/documentation/change-log/' ); ?></p>

				<p><?php printf( __( "<a href='%s'>API</a>", 'post-connector' ), 'https://www.post-connector.com/documentation/api/' ); ?></p>
			</div>

			<div class="pc-box">
				<h3 class="pc-title"><?php _e( 'About the developer', 'post-connector' ); ?></h3>

				<p><?php _e( "Barry has been a WordPress developer for years and is the author of various WordPress plugins.", 'post-connector' ); ?></p>

				<p><?php _e( "In his free time, Barry likes giving back by contributing various opensource projects. He also likes to visit and speak at WordPress meetups and WordCamps and is the organiser of the Dutch WordPress meetup in Tilburg.", 'post-connector' ); ?></p>

				<p><?php printf( __( "You can follow Barry on Twitter <a href='%s' target='_blank'>here</a>.", 'post-connector' ), 'https://twitter.com/barry_kooij' ); ?></p>
			</div>

		</div>
	<?php
	}

	/**
	 * Setup main screen
	 *
	 * @access public
	 * @return void
	 */
	public function screen_main() {
		?>
		<div class="wrap">
			<h2>Post Connector: <?php echo _e( 'Connections', 'post-connector' ); ?></h2>

			<div class="pc-content">
				<?php
				// Add nonce
				wp_nonce_field( plugin_basename( __FILE__ ), 'sp_settings_nonce' );
				?>
				<p><?php _e( "Post Connector connections are the connections you've created between post types. Post Connector will add a meta box to the post edit screens of these post types so you can link post to each other in minutes.", 'post-connector' ); ?></p>

				<h3 class="pc-title"><?php _e( 'Active Connections', 'post-connector' ); ?></h3>
				<?php
				$list_table = new SP_Manage_Links_List_Table();
				$list_table->prepare_items();
				$list_table->display();
				?>

				<h3 class="pc-title"><?php _e( 'Create a new connection', 'post-connector' ); ?></h3>

				<p><?php _e( "Before you can link posts to each other you need to create a connection between their post types first. Click the 'Create a new connection' button to get started.", 'post-connector' ); ?></p>

				<p>
					<?php
					echo "<span id='view-post-btn'><a href='" . get_admin_url() . "admin.php?page=post_connector_edit' class='button-primary'>" . __( 'Create a new connection', 'post-connector' ) . "</a></span>\n";
					?>
				</p>
			</div>
			<?php $this->sidebar(); ?>
		</div>
	<?php
	}

	/**
	 * Setup edit screen
	 *
	 * @access public
	 * @return void
	 */
	public function screen_edit() {

		$connection_manager = new SP_Connection_Manager();

		if ( isset( $_GET['id'] ) ) {
			$connection = $connection_manager->get_connection( $_GET['id'] );
		} else {
			$connection = new SP_Connection();
		}

		// Get Post Types
		$raw_post_types     = get_post_types( array( '_builtin' => false ), 'objects' );
		$post_types         = array();
		$post_types['post'] = __( 'Posts', 'post-connector' );
		$post_types['page'] = __( 'Pages', 'post-connector' );

		if ( count( $raw_post_types ) > 0 ) {
			foreach ( $raw_post_types as $pt_key => $post_type ) {
				if ( $pt_key == 'attachments' || $pt_key == 'sub_posts_link' ) {
					continue;
				}
				$post_types[ $pt_key ] = $post_type->labels->name;
			}
		}
		?>
		<div class="wrap">
			<h2>Post Connector: <?php
				if ( isset( $_GET['id'] ) ) {
					_e( 'Edit connection', 'post-connector' );
				} else {
					_e( 'Create a new connection', 'post-connector' );
				}
				?></h2>

			<div class="pc-content pc-edit-screen">
				<form method="post" action="" id="pc-connection-form">
					<?php

					// Add nonce
					wp_nonce_field( plugin_basename( __FILE__ ), 'sp_edit_nonce' );

					if ( $connection->get_id() != 0 ) {
						echo "<input type='hidden' name='id' value='" . $connection->get_id() . "' />\n";
					}

					echo "<input type='hidden' name='pc-ajax-nonce' id='pc-ajax-nonce' value='" . wp_create_nonce( 'post-connector-ajax-nonce-omgrandomword' ) . "' />\n";
					?>
					<input type="hidden" name="sp_save_check" value="true" />

					<p><?php _e( "To create or edit a post connection, fill in the below form and press the 'save connection' button.", 'post-connector' ); ?></p>

					<h3 class="pc-title"><?php _e( 'Main settings', 'post-connector' ); ?></h3>

					<table class="form-table">
						<tbody>

						<tr valign="top">
							<th scope="row">
								<label for="title" class="sp_label"><?php _e( 'Connection Title', 'post-connector' ); ?></label>
							</th>
							<td colspan="2" class="nowrap">
								<input type="text" name="title" value="<?php esc_attr_e( $connection->get_title() ); ?>" placeholder="<?php _e( 'Title', 'post-connector' ); ?>" class="widefat mandatory" id="title" />

								<p class="help"><?php _e( 'The title of the connection, can be automatically shown above linked posts.', 'post-connector' ); ?></p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<label for="slug"><?php _e( 'Connection Slug', 'post-connector' ); ?></label></th>
							<td colspan="2" class="nowrap">
								<input type="text" name="slug" value="<?php esc_attr_e( $connection->get_slug() ); ?>" placeholder="<?php _e( 'Slug', 'post-connector' ); ?>" class="widefat" id="slug"<?php echo( ( isset( $_GET['id'] ) ) ? ' disabled="disabled"' : '' ); ?> />

								<p class="help"><?php _e( 'Unique identifier of the connection, will automatically generate if left empty.', 'post-connector' ); ?></p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<label for="parent" class="sp_label"><?php _e( 'Parent post type', 'post-connector' ); ?></label>
							</th>
							<td colspan="2" class="nowrap">
								<select name="parent" class="widefat mandatory" id="parent">
									<option value="0"><?php _e( 'Select a post type', 'post-connector' ); ?></option>
									<?php
									if ( count( $post_types ) > 0 ) {
										foreach ( $post_types as $pt_key => $pt_name ) {
											echo "<option value='{$pt_key}'" . ( ( $connection->get_parent() == $pt_key ) ? " selected='selected'" : "" ) . ">{$pt_name}</option>";
										}
									}
									?>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<label for="child" class="sp_label"><?php _e( 'Child post type', 'post-connector' ); ?></label>
							</th>
							<td colspan="2" class="nowrap">
								<select name="child" class="widefat mandatory" id="child">
									<option value="0"><?php _e( 'Select a post type', 'post-connector' ); ?></option>
									<?php
									if ( count( $post_types ) > 0 ) {
										foreach ( $post_types as $pt_key => $pt_name ) {
											echo "<option value='{$pt_key}'" . ( ( $connection->get_child() == $pt_key ) ? " selected='selected'" : "" ) . ">{$pt_name}</option>";
										}
									}
									?>
								</select>
							</td>
						</tr>

						</tbody>
					</table>

					<h3 class="pc-title"><?php _e( 'Create new link settings', 'post-connector' ); ?></h3>

					<table class="form-table">
						<tbody>

						<tr valign="top">
							<th scope="row"><?php _e( 'Add New', 'post-connector' ); ?></th>
							<td colspan="2" class="nowrap">
								<label for="add_new" class=""><input type="checkbox" name="add_new" id="add_new" value="1"<?php echo( ( $connection->get_add_new() == '1' ) ? " checked='checked'" : "" ); ?> /> <?php _e( 'Allow users to create and link new posts.', 'post-connector' ); ?>
								</label>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e( 'Add Existing', 'post-connector' ); ?></th>
							<td colspan="2" class="nowrap">
								<label for="add_existing" class=""><input type="checkbox" name="add_existing" id="add_existing" value="1"<?php echo( ( $connection->get_add_existing() == '1' ) ? " checked='checked'" : "" ); ?> /> <?php _e( 'Allow users to link existing posts.', 'post-connector' ); ?>
								</label>
							</td>
						</tr>

						</tbody>
					</table>

					<h3 class="pc-title"><?php _e( 'Automatically display linked children', 'post-connector' ); ?></h3>

					<table class="form-table">
						<tbody>

						<tr valign="top">
							<th scope="row"><?php _e( 'Display linked children?', 'post-connector' ); ?></th>
							<td colspan="2" class="nowrap">
								<label for="after_post_display_children" class=""><input type="checkbox" name="after_post_display_children" id="after_post_display_children" value="1"<?php echo( ( $connection->get_after_post_display_children() == '1' ) ? " checked='checked'" : "" ); ?> /> <?php _e( 'Display the linked child posts under each parent post.', 'post-connector' ); ?>
								</label>
							</td>
						</tr>

                        <?php
                        /**
                         * Action: 'pc_connection_edit_form_display_linked_children' - Allow adding custom fields to display linked children section
                         *
                         * @api SP_Connection $connection The Connection that is currently edited
                         */
                        do_action( 'pc_connection_edit_form_display_linked_children', $connection );
                        ?>

						</tbody>
					</table>

					<?php
					/**
					 * Action: 'pc_connection_edit_form' - Allow adding custom fields to connection edit screen
					 *
					 * @api SP_Connection $connection The Connection that is currently edited
					 */
					do_action( 'pc_connection_edit_form', $connection );
					?>

					<p class="submit">
						<input name="save" type="submit" class="button-primary" id="publish" accesskey="p" value="<?php _e( 'Save Connection', 'post-connector' ); ?>">
					</p>
				</form>
			</div>
			<?php $this->sidebar(); ?>
		</div>
	<?php
	}

	/**
	 * AJAX method to delete link
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_delete_link() {

		// Check if user is allowed to do this
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], plugin_basename( __FILE__ ) ) ) {
			return;
		}

		// Delete PT link
		$pt_type_manager = new SP_Connection_Manager();
		$pt_type_manager->delete( $_POST['id'] );

		// Generate JSON response
		$response = json_encode( array( 'success' => true ) );
		header( 'Content-Type: application/json' );
		echo $response;

		// Bye
		exit();
	}

}

?>