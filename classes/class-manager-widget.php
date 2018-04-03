<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Manager_Widget {

	/**
	 * Register SP_Widget_Show_Children widget
	 */
	public function register_show_children() {
		register_widget( 'SP_Widget_Show_Children' );
	}

	/**
	 * Register SP_Widget_Show_Parents widget
	 */
	public function register_show_parents() {
		register_widget( 'SP_Widget_Show_Parents' );
	}

}