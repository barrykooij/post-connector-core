<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Filter_Shortcode_Tiny_Button extends SP_Filter {
	protected $tag = 'mce_buttons';

	public function run( $buttons ) {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		if ( get_user_option( 'rich_editing' ) != 'true' ) {
			return;
		}

		array_push( $buttons, "|", "post_connector_shortcodes_button" );

		return $buttons;
	}
}