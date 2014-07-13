<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Filter_Shortcode_Tiny_Lang extends SP_Filter {
	protected $tag = 'mce_external_languages';

	public function run( $arr ) {
		$arr['Post_Connector_Shortcodes'] = Post_Connector::get_core_dir() . 'assets/js/tinymce/editor_plugin_lang.php';

		return $arr;
	}
}