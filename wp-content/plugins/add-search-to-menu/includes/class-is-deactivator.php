<?php
/**
 * Fires during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    IS
 * @subpackage IS/includes
 * @author     Ivory Search <admin@ivorysearch.com>
 */

class IS_Deactivator {

	/**
	 * The code that runs during plugin deactivation.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		delete_option( 'is_notices');
		delete_option( 'is_install');

		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		$version = '1_0'; // replace all periods in 1.0 with an underscore
		$prefix = 'is_admin_pointers_' . $version . '_';
		if ( in_array( $prefix . 'is_pointers', $dismissed ) ) {
			// Get the index in the array of the value we want to remove.
			$index = array_search(  $prefix . 'is_pointers', $dismissed );
			// Remove it.
			unset( $dismissed[$index] );
			// Make the list a comma separated string again.
			$pointers = implode( ',', $dismissed );
			// Save the updated value.
			update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $pointers );
		}
	}
}
