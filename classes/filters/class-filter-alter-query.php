<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Filter_Alter_Query extends SP_Filter {
	protected $tag = 'posts_clauses';
	protected $args = 2;

	public function run( $clauses, $wp_query ) {
		if ( $wp_query->get( 'context' ) == SP_Constants::QUERY_CONTEXT ) {
			global $wpdb;

			// Slug
			$slug = esc_sql( $wp_query->get( 'sp_slug' ) );

			// Add where clause
			$clauses['where'] .= " AND ( {$wpdb->posts}.post_title = 'sp_" . $slug . "' )";
		}

		return $clauses;
	}
}