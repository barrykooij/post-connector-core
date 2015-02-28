<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Manager_Widget {

	private $dir;
	private static $instances;

	public function __construct( $dir ) {
		$this->dir =$dir;
	}

	/**
	 * Load and set hooks
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public function load() {

		foreach ( new DirectoryIterator( $this->dir ) as $file ) {
			$file_name = $file->getFileName();

			if ( ! $file->isDir() && ( strpos( $file->getFileName(), '.' ) !== 0 ) ) {

				$class = SP_Class_Manager::format_class_name( $file->getFileName() );
				add_action( 'widgets_init', create_function( '', 'register_widget( "' . $class . '" );' ) );
				
			}

		}

	}

	/**
	 * Return instance
	 *
	 * @param $class_name
	 *
	 * @return Hook
	 */
	public function get_instance( $class_name ) {
		if ( isset( self::$instances[$class_name] ) ) {
			return self::$instances[$class_name];
		}

		return null;
	}

}