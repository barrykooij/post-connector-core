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
			$new_views[$key] = "<a href='" . esc_url( add_query_arg( array( 'pc-view' => $key, 'paged' => 1 ) ) ) . "'" . ( ( $current == $key ) ? " class='current'" : "" ) . ">{$val}</a>";
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

		// Get per page
		$screen   = get_current_screen();
		$per_page = absint( get_user_meta( get_current_user_id(), $screen->get_option( 'per_page', 'option' ), true ) );
		$per_page = ( ( $per_page > 0 ) ? $per_page : 20 );
		$paged    = absint( isset( $_GET['paged'] ) ? $_GET['paged'] : 1 );
		$orderby  = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'title';
		$order    = isset( $_GET['order'] ) ? $_GET['order'] : 'asc';

		// Get posts
		$post_query = new WP_Query( apply_filters( 'pc_manual_link_post_query_args', array(
			'post_type'        => $this->post_type,
			'posts_per_page'   => $per_page,
			'paged'            => $paged,
			'suppress_filters' => false,
			'orderby'          => $orderby,
			'order'            => $order,
			'post_status'      => apply_filters( 'pc_manual_link_post_statuses' , array( 'publish', 'private' ) ),
		) ) );

		// Format data for table
		if ( $post_query->have_posts() ) {
			while($post_query->have_posts()) {
				$next_post = $post_query->next_post();
				$this->data[] = array( 'ID' => $next_post->ID, 'title' => $next_post->post_title );
			}
		}

		// Remove search filter
		remove_filter( 'posts_where', array( $this, 'filter_posts_where' ) );

		// Pagination
		$this->set_pagination_args( array(
			'total_items' => $post_query->found_posts,
			'per_page'    => $per_page
		) );

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
	 * Checkbox column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
				'<input type="checkbox" name="sp_bulk[]" value="%s" />', esc_attr( $item['ID'] )
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
