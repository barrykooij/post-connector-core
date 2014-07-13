<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Hook_Meta_Box extends SP_Hook {
	protected $tag = 'admin_init';

	public function run() {

		$connection_manager = new SP_Connection_Manager();
		$pt_links   = $connection_manager->get_connections();

		// Setup Meta Boxes
		if ( count( $pt_links ) > 0 ) {

			foreach ( $pt_links as $connection ) {
				new SP_Meta_Box_Manage( $connection );
				new SP_Meta_Box_Meta( $connection );

				/**
				 * Action: 'pc_meta_box_creation_after' - Allow actions to be added in meta box manage construct
				 *
				 * @api SP_Connection $connection The Connection used in this meta box
				 */
				do_action( 'pc_meta_box_creation_after', $connection );

			}

		}

	}
}