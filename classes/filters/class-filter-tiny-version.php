<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Filter_Tiny_Version extends SP_Filter {
	protected $tag = 'tiny_mce_version';

	public function run( $ver ) {
		$ver += 3;

		return $ver;
	}
}