<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Hook_Menu extends SP_Hook {
	protected $tag = 'admin_menu';

	public function run() {
		SP_Admin_Menu::get()->do_menu();
	}
}