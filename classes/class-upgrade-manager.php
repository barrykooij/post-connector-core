<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SP_Upgrade_Manager {

	/**
	 * Check if there's a plugin update
	 */
	public function check_update() {

		// Get current version
		$current_version = get_option( SP_Constants::OPTION_CURRENT_VERSION, 1 );

		// Check if update is required
		if( SP_Constants::PLUGIN_VERSION_CODE > $current_version ) {

			// Do update
			$this->do_update( $current_version );

			// Update version code
			$this->update_current_version_code();

		}

	}

	/**
	 * An update is required, do it
	 *
	 * @param $current_version
	 */
	private function do_update( $current_version ) {
	}

	/**
	 * Update the current version code
	 */
	private function update_current_version_code() {
		update_option( SP_Constants::OPTION_CURRENT_VERSION, SP_Constants::PLUGIN_VERSION_CODE );
	}

}