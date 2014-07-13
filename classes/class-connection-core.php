<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

abstract class SP_Connection_Core {

	private $id = 0;
	private $slug = '';
	private $title = '';
	private $parent = '';
	private $child = '';
	private $add_new = '1';
	private $add_existing = '1';
	private $after_post_display_children = '0';

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Method to set id of link
	 *
	 * @param $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Method to get the id
	 *
	 * @access public
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @param string $slug
	 */
	public function set_slug( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * Method to get the slug
	 *
	 * @return String
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @param string $title
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Method to get the title
	 *
	 * @access public
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Method to get the parent
	 *
	 * @access public
	 * @return string
	 */
	public function get_parent() {
		return $this->parent;
	}

	/**
	 * Method to set the parent
	 *
	 * @access public
	 *
	 * @param string $parent
	 *
	 * @return void
	 */
	public function set_parent( $parent ) {
		$this->parent = $parent;
	}

	/**
	 * @param string $child
	 */
	public function set_child( $child ) {
		$this->child = $child;
	}

	/**
	 * Method to get the child
	 *
	 * @access public
	 * @return string
	 */
	public function get_child() {
		return $this->child;
	}

	/**
	 * @param string $add_existing
	 */
	public function set_add_existing( $add_existing ) {
		$this->add_existing = $add_existing;
	}

	/**
	 * Method to get if connection allows adding existing children
	 *
	 * @access public
	 * @return int
	 */
	public function get_add_existing() {
		return $this->add_existing;
	}

	/**
	 * @param string $add_new
	 */
	public function set_add_new( $add_new ) {
		$this->add_new = $add_new;
	}

	/**
	 * Method to get if connection allows adding new children
	 *
	 * @access public
	 * @return int
	 */
	public function get_add_new() {
		return $this->add_new;
	}

	/**
	 * Method to set the $after_post_display_children value
	 *
	 * @param int $after_post_display_children
	 */
	public function set_after_post_display_children( $after_post_display_children ) {
		$this->after_post_display_children = $after_post_display_children;
	}

	/**
	 * Method to get the $after_post_display_children value
	 *
	 * @return int
	 */
	public function get_after_post_display_children() {
		return $this->after_post_display_children;
	}

}