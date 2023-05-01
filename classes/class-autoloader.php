<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Autoloader {

	private $base_path;

	/**
	 * Constructor
	 *
	 * @param $base_path
	 */
	public function __construct( $base_path ) {
		$this->base_path = $base_path;
	}


	/**
	 * Autoloader load method. Load the class.
	 *
	 * @param $class
	 */
	public function load( $class ) {


		// Only autoload Post Connector or Yoast classes
		if ( 0 === strpos( $class, 'SP_' ) ) {

			// String to lower
			$class = strtolower( $class );

			// Format file name
			$file_name = 'class-' . str_ireplace( '_', '-',
					str_ireplace( array( 'SP_Yoast_', 'SP_', ), '', $class ) ) . '.php';

			// Full file path
			$class_path = $this->base_path . 'classes/';

			// Check if we need to extend the class path
			if ( strpos( $class, 'sp_filter' ) === 0 ) {
				$class_path .= 'filters/';
			} elseif ( strpos( $class, 'sp_co_filter' ) === 0 ) {
				$class_path .= 'co-filters/';
			} elseif ( strpos( $class, 'sp_hook' ) === 0 ) {
				$class_path .= 'hooks/';
			} elseif ( strpos( $class, 'sp_co_hook' ) === 0 ) {
				$class_path .= 'co-hooks/';
			} elseif ( strpos( $class, 'sp_shortcode' ) === 0 ) {
				$class_path .= 'shortcodes/';
			} elseif ( strpos( $class, 'sp_widget' ) === 0 ) {
				$class_path .= 'widgets/';
			} elseif ( strpos( $class, 'sp_meta_box' ) === 0 ) {
				$class_path .= 'meta-boxes/';
			} elseif ( strpos( $class, 'sp_yoast' ) === 0 ) {
				$class_path .= 'license-manager/';
			}

			// Append file name to clas path
			$full_path = $class_path . $file_name;

			// Check & load file
			if ( file_exists( $full_path ) ) {
				require_once( $full_path );
			}

		}

	}


}