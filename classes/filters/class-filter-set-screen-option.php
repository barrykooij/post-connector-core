<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Filter_Set_Screen_Option extends SP_Filter {
	protected $tag = 'set-screen-option';
	protected $args = 3;

	/**
	 * Save custom screen option
	 *
	 * @param string $status
	 * @param string $option
	 * @param string $value
	 *
	 * @return string
	 */
	public function run( $status, $option, $value ) {
		if ( 'post_connector_per_page' == $option ) {
			return $value;
		}

		return $status;
	}
}