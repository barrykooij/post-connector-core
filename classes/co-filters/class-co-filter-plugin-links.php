<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Co_Filter_Plugin_Links extends SP_Filter {
	protected $tag = 'plugin_action_links_post-connector/post-connector.php';

	public function run( $links ) {

		array_unshift( $links, '<a href="' . get_admin_url() . 'admin.php?page=post_connector">' . __( 'Manage', 'post-connector' ) . '</a>' );
		array_unshift( $links, '<a href="https://www.post-connector.com/?utm_source=plugin&utm_medium=link&utm_campaign=plugin-page" target="_blank" style="color:#297b7b;font-weight:bold;">' . __( 'GO PRO', 'post-connector' ) . '</a>' );

		return $links;
	}
}