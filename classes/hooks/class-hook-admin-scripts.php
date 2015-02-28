<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class SP_Hook_Admin_Scripts extends SP_Hook {
	protected $tag = 'admin_enqueue_scripts';

	public function run() {
		global $pagenow;

		// Connection overview script
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'post_connector' ) {

			// Load PTL JS
			wp_enqueue_script(
				'post-connector-ptl',
				plugins_url( 'core/assets/js/post-connector-ptl' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', Post_Connector::get_plugin_file() ),
				array( 'jquery' )
			);

			// Make PTL JavaScript strings translatable
			wp_localize_script( 'post-connector-ptl', 'sp_js', SP_Javascript_Strings::get() );
		}

		// Connection edit script
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'post_connector_edit' ) {

			// Load PTL JS
			wp_enqueue_script(
				'pc-connection-edit',
				plugins_url( 'core/assets/js/post-connector-connection-edit' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', Post_Connector::get_plugin_file() ),
				array( 'jquery' )
			);

			// Make PTL JavaScript strings translatable
			wp_localize_script( 'pc-connection-edit', 'sp_js', SP_Javascript_Strings::get() );
		}

		// Post Link script
		if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {

			// Load PL JS
			wp_enqueue_script(
				'post-connector-pl',
				plugins_url( 'core/assets/js/post-connector-pl' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', Post_Connector::get_plugin_file() ),
				array( 'jquery', 'jquery-ui-sortable' )
			);

			// Make PL JavaScript strings translatable
			wp_localize_script( 'post-connector-pl', 'sp_js', SP_Javascript_Strings::get() );
		}

		// Widget & Shortcode script
		if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' || $pagenow == 'widgets.php' ) {

			// Load Widget & Shortcode script
			wp_enqueue_script(
				'post-connector-widget-shortcode',
				plugins_url( 'core/assets/js/post-connector-widget-shortcode' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', Post_Connector::get_plugin_file() ),
				array( 'jquery' )
			);

			// Make Widget / Shortcode JavaScript strings translatable
			wp_localize_script( 'post-connector-widget-shortcode', 'sp_js', SP_Javascript_Strings::get() );
		}

		// CSS
		wp_enqueue_style(
			'post_connector',
			plugins_url( 'core/assets/css/post-connector' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.css', Post_Connector::get_plugin_file() )
		);
	}
}