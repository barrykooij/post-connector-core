<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Widget_Show_Children extends WP_Widget {

	private $default_vars = array(
		'title'     => '',
		'postlink'  => '',
		'parent'    => '',
		'link'      => true,
		'excerpt'   => true,
		'thumbnail' => false
	);

	public function __construct() {
		// Parent construct
		parent::__construct(
			'sp_widget_showchilds', // Can't change this, backwards compatibility :(
			__( 'Post Connector - Show Children', 'post-connector' ),
			array( 'description' => __( 'Display linked children by a parent or current post', 'post-connector' ) )
		);
	}

	public function widget( $args, $instance ) {

		// Setup widget vars
		$instance = array_merge( $this->default_vars, $instance );

		// Don't output anything if there's no postlink
		if ( $instance['postlink'] == '' ) {
			return;
		}

		// Don't output the widget on the frontpage if the frontpage show_on_front is posts
		if ( is_front_page() && false === is_page() ) {
			return;
		}

		// Use custom post of parent is null
		if ( null === $instance['parent'] ) {
			$instance['parent'] = get_the_ID();
		}

		// Load the PTL
		$post_type_link_manager = new SP_Connection_Manager();
		$connection             = $post_type_link_manager->get_connection( $instance['postlink'] );

		// Return if there is no connection
		if ( null == $connection ) {
			return;
		}

		// Only display widget on pages where the post type is the $instance['parent'] equals the Connection post type
		if ( $connection->get_parent() != get_post_type( $instance['parent'] ) ) {
			return;
		}

		// Setup the post link manager
		$post_link_manager = new SP_Post_Link_Manager();

		// Generate the widget content
		$widget_content = $post_link_manager->generate_children_list( $connection->get_slug(), $instance['parent'],
			$instance['link'], $instance['excerpt'], 'b', $instance['thumbnail'] );

		// Don't ouput the widget if there is no widget content
		if ( '' == $widget_content ) {
			return;
		}

		// Output the widget
		echo $args['before_widget'];
		echo $args['before_title'] . $instance['title'] . $args['after_title'] . "\n";
		echo $widget_content;
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance              = array();
		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['postlink']  = $new_instance['postlink'];
		$instance['parent']    = ( $new_instance['parent'] == 'current' ) ? null : $new_instance['parent'];
		$instance['link']      = ( $new_instance['link'] == 'false' ) ? false : true;
		$instance['excerpt']   = ( $new_instance['excerpt'] == 'false' ) ? false : true;
		$instance['thumbnail'] = ( $new_instance['thumbnail'] == 'false' ) ? false : true;

		return $instance;
	}

	public function form( $instance ) {

		$instance = array_merge( $this->default_vars, $instance );

		$selected_link = null;

		echo "<div class='pc_ajax_child'>\n";

		wp_nonce_field( 'sp_ajax_sc_gpp', 'sp_widget_child_nonce' );

		echo "<p>";
		echo '<label for="' . esc_attr( $this->get_field_id( 'title' ) ) . '">' . __( 'Title',
				'post-connector' ) . ':</label>';
		echo '<input class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" type="text" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" value="' . esc_attr( $instance['title'] ) . '" />';
		echo "</p>\n";

		echo "<p>";
		echo '<label for="' . esc_attr( $this->get_field_id( 'postlink' ) ) . '">' . __( 'Post Link',
				'post-connector' ) . ':</label>';

		// Get the connections
		$connection_manager = new SP_Connection_Manager();
		$links              = $connection_manager->get_connections();

		echo '<select class="widefat postlink" name="' . $this->get_field_name( 'postlink' ) . '" id="' . $this->get_field_id( 'postlink' ) . '" >';
		echo '<option value="0">' . __( 'Select Post Link', 'post-connector' ) . '</option>';
		if ( count( $links ) > 0 ) {
			foreach ( $links as $link ) {
				echo '<option value="' . esc_attr( $link->get_id() ) . '"';
				if ( $link->get_id() == $instance['postlink'] ) {
					echo ' selected="selected"';
					$selected_link = $link;
				}
				echo '>' . esc_html( $link->get_title() ) . '</option>';
			}
		}
		echo '</select>';
		echo "</p>\n";

		echo "<p>";
		echo '<label for="' . $this->get_field_id( 'parent' ) . '">' . __( 'Parent', 'post-connector' ) . ':</label>';

		echo '<select class="widefat child" name="' . $this->get_field_name( 'parent' ) . '" id="' . $this->get_field_id( 'parent' ) . '" >';

		if ( $selected_link != null ) {
			$parent_posts = get_posts( array(
				'post_type'      => $selected_link->get_parent(),
				'posts_per_page' => - 1,
				'orderby'        => 'title',
				'order'          => 'ASC'
			) );
			if ( count( $parent_posts ) > 0 ) {
				echo "<option value='current'>" . __( 'Current page', 'post-connector' ) . "</option>\n";
				foreach ( $parent_posts as $parent_post ) {
					echo "<option value='" . esc_attr( $parent_post->ID ) . "'";
					if ( $parent_post->ID == $instance['parent'] ) {
						echo " selected='selected'";
					}
					echo ">" . esc_html( $parent_post->post_title ) . "</option>\n";
				}
			}
		}

		echo '</select>';
		echo "</p>\n";

		echo "<p>";
		echo '<label for="' . esc_attr( $this->get_field_id( 'link' ) ) . '">' . __( 'Make children clickable',
				'post-connector' ) . ':</label>';
		echo '<select class="widefat" name="' . esc_attr( $this->get_field_name( 'link' ) ) . '" id="' . esc_attr( $this->get_field_id( 'link' ) ) . '" >';
		echo '<option value="true"' . ( ( $instance['link'] == true ) ? ' selected="selected"' : '' ) . '>Yes</option>';
		echo '<option value="false"' . ( ( $instance['link'] == false ) ? ' selected="selected"' : '' ) . '>No</option>';
		echo '</select>';
		echo "</p>\n";

		echo "<p>";
		echo '<label for="' . esc_attr( $this->get_field_id( 'excerpt' ) ) . '">' . __( 'Display excerpt',
				'post-connector' ) . ':</label>';
		echo '<select class="widefat" name="' . esc_attr( $this->get_field_name( 'excerpt' ) ) . '" id="' . esc_attr( $this->get_field_id( 'excerpt' ) ) . '" >';
		echo '<option value="true"' . ( ( $instance['excerpt'] == true ) ? ' selected="selected"' : '' ) . '>Yes</option>';
		echo '<option value="false"' . ( ( $instance['excerpt'] == false ) ? ' selected="selected"' : '' ) . '>No</option>';
		echo '</select>';
		echo "</p>\n";

		echo "<p>";
		echo '<label for="' . esc_attr( $this->get_field_id( 'thumbnail' ) ) . '">' . __( 'Display thumbnail',
				'post-connector' ) . ':</label>';
		echo '<select class="widefat" name="' . esc_attr( $this->get_field_name( 'thumbnail' ) ) . '" id="' . esc_attr( $this->get_field_id( 'thumbnail' ) ) . '" >';
		echo '<option value="true"' . ( ( $instance['thumbnail'] == true ) ? ' selected="selected"' : '' ) . '>Yes</option>';
		echo '<option value="false"' . ( ( $instance['thumbnail'] == false ) ? ' selected="selected"' : '' ) . '>No</option>';
		echo '</select>';
		echo "</p>\n";

		echo "</div>\n";


	}

}