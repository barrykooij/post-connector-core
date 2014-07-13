<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SP_Javascript_Strings {

	private static $value = null;

	private static function fill() {
		self::$value = array(
				'confirm_delete_child' => __( 'Are you sure you want to delete this post?', 'post-connector' ),
				'confirm_delete_link'  => __( 'Are you sure you want to delete this link?', 'post-connector' ),
				'current_page'         => __( 'Current page', 'post-connector' )
		);
	}

	public static function get() {
		if ( self::$value === null ) {
			self::fill();
		}
		return self::$value;
	}

}
