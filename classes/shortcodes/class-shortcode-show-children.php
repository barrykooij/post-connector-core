<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Shortcode_Show_Children extends SP_Shortcode {
	protected $tag = 'show_children';
	protected $arguments = array(
			'slug'    => '',
			'parent'  => null,
			'link'    => 'true',
			'excerpt' => 'true',
	);

	public function run( $atts ) {

		extract( shortcode_atts( $this->arguments, $atts ) );

		if ( $slug == "" ) {
			return;
		}

		if ( $parent === null ) {
			$parent = get_the_ID();
		}

		$post_link_manager = new SP_Post_Link_Manager();

		return $post_link_manager->generate_children_list( $slug, $parent, $link, $excerpt );
	}
}