<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SP_Manage_Links_List_Table extends WP_List_Table {

	private $data;
	private $search;

	public function __construct() {
		parent::__construct();
	}

	public function get_columns() {
		$columns = array(
			'title'  => __( 'Title', 'post-connector' ),
			'slug'   => __( 'Slug', 'post-connector' ),
			'parent' => __( 'Parent post', 'post-connector' ),
			'child'  => __( 'Child post', 'post-connector' ),
		);
		return $columns;
	}

	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Get Data
		$this->data      = array();
		$pt_link_manager = new SP_Connection_Manager();
		/**
		 * @todo Change te $pt_link_manager and the $link variables
		 */

		foreach ( $pt_link_manager->get_connections() as $link ) {

			// Get parent
			$parent    = get_post_types( array( 'name' => $link->get_parent() ), 'object' );
			$pt_parent = array_shift( $parent );

			// Get child
			$child    = get_post_types( array( 'name' => $link->get_child() ), 'object' );
			$pt_child = array_shift( $child );

			$parent_label = "";
			if ( isset( $pt_parent ) && isset( $pt_parent->labels ) && isset( $pt_parent->labels->name ) ) {
				$parent_label = $pt_parent->labels->name;
			}

			$child_label = "";
			if ( isset( $pt_child ) && isset( $pt_child->labels ) && isset( $pt_child->labels->name ) ) {
				$child_label = $pt_child->labels->name;
			}

			$this->data[] = array(
				'ID'     => $link->get_id(),
				'title'  => $link->get_title(),
				'slug'   => $link->get_slug(),
				'parent' => $parent_label,
				'child'  => $child_label
			);
		}

		// Sort
		if ( count( $this->data ) > 0 ) {
			usort( $this->data, array( $this, 'custom_reorder' ) );
		}

		// Set
		$this->items = $this->data;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'title'  => array( 'title', false ),
			'slug'   => array( 'slug', false ),
			'parent' => array( 'parent', false ),
			'child'  => array( 'child', false )
		);
		return $sortable_columns;
	}

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

	public function column_title( $item ) {
		$actions = array(
			'edit'  => sprintf(
				'<a href="%sadmin.php?page=post_connector_edit&id=%s">' . __( 'Edit', 'post-connector' ) . '</a>',
				get_admin_url(),
				$item['ID']
			),
			'trash' => sprintf(
				'<a href="javascript:;" id="%s">' . __( 'Delete', 'post-connector' ) . '</a>',
				$item['ID']
			),
		);
		return sprintf(
			'%1$s %2$s',
				'<strong><a href="' . get_admin_url() . 'admin.php?page=post_connector_edit&id=' . $item['ID'] . '">' . $item['title'] . '</a></strong>',
			$this->row_actions( $actions )
		);
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
				return $item[$column_name];
			default:
				return $item[$column_name];
		}
	}

}
