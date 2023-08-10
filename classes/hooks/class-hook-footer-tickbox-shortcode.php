<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Hook_Footer_Tickbox_Shortcode extends SP_Hook {
	protected $tag = 'admin_footer';

	public function run( $post_id ) {

		if ( in_array( basename( $_SERVER['SCRIPT_FILENAME'] ),
			array( 'post.php', 'page.php', 'page-new.php', 'post-new.php' ) ) ) {
			add_thickbox();
			?>
            <div id="sp_tb_shortcode" style="height: 500px; display:none;">
                <div class="wrap">
                    <div class="pc_ajax_parent">
                        <div style="padding:15px 15px 0 15px;">
                            <h2><?php _e( 'Insert Post Connector show_children shortcode', 'post-connector' ); ?></h2>
                            <span><?php _e( 'Use the form below to generate a show_children shortcode.',
									'post-connector' ); ?></span>
                        </div>
                        <div style="padding:15px 15px 0 15px;">

							<?php
							echo "<div class='sp_showchilds_ajax'>\n";

							wp_nonce_field( 'sp_ajax_sc_gpp', 'sp_widget_child_nonce' );

							echo '<input type="hidden" name="by_slug" id="by_slug" value="true" />';

							echo "<p>";
							echo '<label for="sp_sc_postlink">' . __( 'Connection', 'post-connector' ) . ':</label>';

							// Get the connections
							$connection_manger = new SP_Connection_Manager();
							$connections       = $connection_manger->get_connections();

							echo '<select class="widefat mandatory postlink" name="sp_sc_postlink" id="sp_sc_postlink" >';
							echo '<option value="0">' . __( 'Select Connection', 'post-connector' ) . '</option>';
							if ( count( $connections ) > 0 ) {
								foreach ( $connections as $connection ) {
									echo '<option value="' . esc_attr( get_post_meta( $connection->get_id(),
											SP_Constants::PM_PTL_SLUG,
											true ) ) . '">' . esc_html( $connection->get_title() ) . '</option>';
								}
							}
							echo '</select>';
							echo "</p>\n";

							echo "<p>";
							echo '<label for="sp_sc_parent">' . __( 'Parent', 'post-connector' ) . ':</label>';

							echo '<select class="widefat mandatory parent" name="sp_sc_parent" id="sp_sc_parent" >';
							echo '</select>';
							echo "</p>\n";

							echo "<p>";
							echo '<label for="sp_sc_link">' . __( 'Make children clickable',
									'post-connector' ) . ':</label>';
							echo '<select class="widefat" name="sp_sc_link" id="sp_sc_link" >';
							echo '<option value="true">Yes</option>';
							echo '<option value="false">No</option>';
							echo '</select>';
							echo "</p>\n";

							echo "<p>";
							echo '<label for="sp_sc_excerpt">' . __( 'Display excerpt',
									'post-connector' ) . ':</label>';
							echo '<select class="widefat" name="sp_sc_excerpt" id="sp_sc_excerpt" >';
							echo '<option value="true">Yes</option>';
							echo '<option value="false">No</option>';
							echo '</select>';
							echo "</p>\n";

							echo "</div>\n";
							?>

                        </div>


                        <div style="padding:15px 15px 0;">
                            <input type="button" class="button-primary"
                                   value="<?php esc_attr_e( __( 'Insert Shortcode', 'post-connector' ) ); ?>"
                                   onclick="insertShortcode_ShowChilds();"/>&nbsp;&nbsp;&nbsp;
                            <a class="button" style="color:#bbb;" href="#"
                               onclick="tb_remove(); return false;"><?php _e( 'Cancel', 'post-connector' ); ?></a>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}
	}
}