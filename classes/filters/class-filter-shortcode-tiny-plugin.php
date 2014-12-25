<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Filter_Shortcode_Tiny_Plugin extends SP_Filter {
	protected $tag = 'mce_external_plugins';

	public function run( $plugin_array ) {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return $plugin_array;
		}
		if ( get_user_option( 'rich_editing' ) != 'true' ) {
			return $plugin_array;
		}

		$plugin_array['Post_Connector_Shortcodes'] = plugins_url( 'core/assets/js/tinymce/editor_shortcode.js', Post_Connector::get_plugin_file() );

		return $plugin_array;
	}
}
