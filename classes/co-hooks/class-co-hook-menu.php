<?php

class SP_Co_Hook_Menu extends SP_Hook {
	protected $tag = 'admin_menu';

	public function run() {

		add_submenu_page( 'post_connector', __( 'Upgrade to Pro', 'post-connector' ), __( 'Upgrade to Pro', 'post-connector' ), 'manage_options', 'post_connector_go_pro', array(
			$this,
			'output_screen'
		) );
	}

	public function output_screen() {
		//
	}

}