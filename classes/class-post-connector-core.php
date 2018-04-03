<?php

class Post_Connector_Core {

	/**
	 * Get the Post Connector Core directory
	 *
	 * @return string
	 */
	public static function get_core_dir() {
		return dirname( Post_Connector::get_plugin_file() ) . '/core/';
	}

	/**
	 * Get the Post Connector core file
	 *
	 * @access public
	 * @static
	 * @return String
	 */
	public static function get_core_file() {
		return __FILE__;
	}

	/**
	 * Initialize the plugin
	 */
	public function init() {

		// Load plugin textdomain
		load_plugin_textdomain( 'post-connector', false, dirname( plugin_basename( Post_Connector::get_plugin_file() ) ) . '/languages/' );

		// Setup autoloader
		require_once( self::get_core_dir() . '/classes/class-autoloader.php' );
		$autoloader = new SP_Autoloader( self::get_core_dir() );
		spl_autoload_register( array( $autoloader, 'load' ) );

		// Filters
		$manager_filter = new SP_Manager_Filter( plugin_dir_path( __FILE__ ) . 'filters/' );
		$manager_filter->load_filters();

		// Hooks
		$manager_hook = new SP_Manager_Hook( plugin_dir_path( __FILE__ ) . 'hooks/' );
		$manager_hook->load_hooks();

		// Shortcodes
		$manager_shortcode = new SP_Manager_Shortcode();
		$manager_shortcode->load_shortcodes();

		// Widgets
		$manager_widget = new SP_Manager_Widget();
		add_action( 'widgets_init', array( $manager_widget, 'register_show_children' ) );

		// Menu init
		SP_Admin_Menu::get();

		// Plugin upgrader
		if ( is_admin() ) {
			$plugin_updater = new SP_Upgrade_Manager();
			$plugin_updater->check_update();
		}
	}

}