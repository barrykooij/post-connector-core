<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SP_Manager_Shortcode {

	private $shortcode_dir;
	private static $shortcodes;

	public function __construct() {
		$this->shortcode_dir = plugin_dir_path( __FILE__ ) . 'shortcodes/';
	}

	/**
	 * Add BC for subposts_show_childs
	 */
	private function load_deprecated_shortcodes() {
		add_shortcode( 'subposts_show_childs', array( self::$shortcodes['SP_Shortcode_Show_Children'], 'run' ) );
	}

	/**
	 * Load and set shortcodes
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public function load_shortcodes() {

		foreach ( new DirectoryIterator( $this->shortcode_dir ) as $file ) {

			$file_name = $file->getFileName();

			if ( ! $file->isDir() && ( strpos( $file->getFileName(), '.' ) !== 0 ) ) {

				$class = SP_Class_Manager::format_class_name( $file->getFileName() );
				if ( 'SP_Shortcode' != $class ) {
					self::$shortcodes[$class] = new $class;
				}

			}

		}

		// Load the deprecated shortcode
		$this->load_deprecated_shortcodes();

	}

	/**
	 * Return instance of created shortcode
	 *
	 * @param $class_name
	 *
	 * @return Hook
	 */
	public function get_shortcode_instance( $class_name ) {
		if ( isset( self::$shortcodes[$class_name] ) ) {
			return self::$shortcodes[$class_name];
		}
		return null;
	}

}