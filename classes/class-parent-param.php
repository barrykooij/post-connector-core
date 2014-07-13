<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SP_Parent_Param {

	/**
	 * Method to generate new sp_parent parameter
	 *
	 * @access public
	 *
	 * @param int    $post_id
	 * @param int    $ptl_id
	 * @param string $sp_parent (default: '')
	 * @param int $backwards (default: 0)
	 *
	 * @return string
	 */
	public static function generate_sp_parent_param( $post_id, $ptl_id, $sp_parent = '', $backwards = 0 ) {
		$parts   = explode( '-', $sp_parent );
		$parts[] = $post_id . '_' . $ptl_id . '_' . $backwards;
		return implode( '-', $parts );
	}

	/**
	 * Method to strip last var from parent parameter
	 *
	 * @access public
	 *
	 * @param string $sp_parent
	 *
	 * @return string
	 */
	public static function strip_sp_parent_parent( $sp_parent ) {
		$parts = explode( '-', $sp_parent );

		$return = '';
		if ( count( $parts ) > 0 ) {
			array_pop( $parts );
			$return = implode( '-', $parts );
		}
		return $return;
	}

	/**
	 * Method to get last param from parent parameter
	 *
	 * @access public
	 *
	 * @param string $sp_parent
	 *
	 * @return string
	 */
	public static function get_current_parent( $sp_parent ) {
		$parts = explode( '-', $sp_parent );
		return explode( '_', array_pop( $parts ) );
	}

}