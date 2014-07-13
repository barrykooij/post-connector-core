<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SP_Create_Link_List_Table extends WP_List_Table {

	private $post_type;
	private $data;
	private $search;

	public function __construct( $post_type, $connection ) {
		$this->post_type      = $post_type;

		parent::__construct();
		add_filter( 'views_' . $this->screen->id, array( $this, 'add_page_views' ) );
	}

	/**
	 * Get the current view
	 *
	 * @return string
	 */
	private function get_current_view() {
		return ( isset ( $_GET['pc-view'] ) ? $_GET['pc-view'] : 'all' );
	}

	/**
	 * Add page views
	 *
	 * @param array $views
	 *
	 * @return array
	 */
	public function add_page_views( $views ) {

		// Get current
		$current = $this->get_current_view();

		$views_arr = array(
				'all' => __( 'All', 'post-connector' ),
		);

		$new_views = array();

		foreach ( $views_arr as $key => $val ) {
			$new_views[$key] = "<a href='" . add_query_arg( array( 'pc-view' => $key, 'paged' => 1 ) ) . "'" . ( ( $current == $key ) ? " class='current'" : "" ) . ">{$val}</a>";
		}

		return $new_views;
	}

	/**
	 * Set the search string
	 *
	 * @param $search
	 */
	public function set_search( $search ) {
		$this->search = $search;
	}

	/**
	 * Get the columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
				'cb'    => '<input type="checkbox" />',
				'title' => __( 'Title', 'post-connector' ),
		);

		return $columns;
	}

	/**
	 * Prepare the items
	 */
	public function prepare_items() {

		// Get current view
		$view = $this->get_current_view();

		// Set table properties
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Vies
		$this->views();

		// Set search
		if ( $this->search !== null ) {
			add_filter( 'posts_where', array( $this, 'filter_posts_where' ) );
		}

		// Get Data
		$this->data = array();

		// Get posts
		$posts = get_posts( array( 'post_type' => $this->post_type, 'posts_per_page' => '-1', 'suppress_filters' => false ) );

		// Format data for table
		if ( count( $posts ) > 0 ) {
			foreach ( $posts as $post ) {
				$this->data[] = array( 'ID' => $post->ID, 'title' => $post->post_title );
			}
		}

		// Remove search filter
		remove_filter( 'posts_where', array( $this, 'filter_posts_where' ) );

		// Sort
		if ( count( $this->data ) > 0 ) {
			usort( $this->data, array( $this, 'custom_reorder' ) );
		}

		// Set items
		$this->items = $this->data;
	}

	/**
	 * Get the sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array();
		$sortable_columns['title'] = array( 'title', false );
		return $sortable_columns;
	}

	/**
	 * Method to do the custom reorder
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public function custom_reorder( $a, $b ) {
		// If no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'title';
		// If no order, default to asc
		$order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';
		// Determine sort order
		$result = strcmp( $a[$orderby], $b[$orderby] );

		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : - $result;
	}

	/**
	 * Checkbox column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
				'<input type="checkbox" name="sp_bulk[]" value="%s" />', $item['ID']
		);
	}

	/**
	 * Title column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_title( $item ) {
		$actions = array(
				'link' => sprintf(
						'<a href="?page=%s&sp_parent=%s&sp_pt_link=%s&sp_post_link=%s">' . __( 'Link', 'post-connector' ) . '</a>',
						$_REQUEST['page'],
						$_GET['sp_parent'],
						$_GET['sp_pt_link'],
						$item['ID']
				),
		);

		return sprintf( '%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
	}

	/**
	 * Default column
	 *
	 * @param $item
	 * @param $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
				return $item[$column_name];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Get the bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
				'link' => __( 'Link', 'post-connector' )
		);

		return $actions;
	}

	/**
	 * Filter on the post where
	 *
	 * @param $where
	 *
	 * @return string
	 */
	public function filter_posts_where( $where ) {
		global $wpdb;
		$where .= $wpdb->prepare( " AND {$wpdb->prefix}posts.post_title LIKE '%%%s%%' ", $this->search );

		return $where;
	}

}
