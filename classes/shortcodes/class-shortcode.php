<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

abstract class SP_Shortcode {
	protected $tag = null;
	protected $arguments = array();

	/**
	 * Construct method. Set tag and register hook.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->register();
	}

	/**
	 * Register the Shortcode.
	 *
	 * @access public
	 * @return void
	 */
	public function register() {
		// Tag must be set
		if ( $this->tag === null ) {
			trigger_error( 'ERROR IN SHORTCODE: NO TAG SET', E_USER_ERROR );
		}

//		add_shortcode( 'subposts_' . $this->tag, array( $this, 'run' ) );
		add_shortcode( 'post_connector_' . $this->tag, array( $this, 'run' ) );
	}

}