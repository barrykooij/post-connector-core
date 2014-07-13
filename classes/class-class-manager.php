<?php

class SP_Class_Manager {

	/**
	 * Uppercase the character after the underscore, used as callback by SP_Class_Manager::format_class_name.
	 *
	 * @param $part
	 *
	 * @return string
	 */
	public static function capitalize_part( $part ) {
		return '_' . strtoupper( substr( $part[0], 1, 1 ) );
	}

	/**
	 * Format the class name by the file name
	 *
	 * @param $file_name
	 *
	 * @return string
	 */
	public static function format_class_name( $file_name ) {
		return preg_replace_callback( "/(_[a-z])/", array( 'SP_Class_Manager', 'capitalize_part' ), 'SP_' . str_ireplace( '-', '_', str_ireplace( array( 'class-', '.php' ), "", $file_name ) ) );
	}

}