<?php
/**
 * Plugin Name: Ivory Search
 * Plugin URI:  https://ivorysearch.com
 * Description: The WordPress Search plugin that includes Search Form Customizer, WooCommerce Search, Image Search, Search Shortcode, AJAX Search & Live Search support!
 * Version:     4.7
 * Author:      Ivory Search
 * Author URI:  https://ivorysearch.com/
 * License:     GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages/
 * Text Domain: add-search-to-menu
 *
  * 
 * WC tested up to: 5.7
 *
 * Ivory Search is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Ivory Search is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Ivory Search. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */


/**
 * Includes necessary dependencies and starts the plugin.
 *
 * @package IS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exits if accessed directly.
}

if ( function_exists( 'is_fs' ) ) {
	is_fs()->set_basename( false, __FILE__ );
	return;
}

/**
 * Main Ivory Search Class.
 *
 * @class Ivory_Search
 */
final class Ivory_Search {

	/**
	 * Stores plugin options.
	 */
	private static $opt;

	/**
	 * Core singleton class
	 * @var self
	 */
	private static $_instance;

	/**
	 * Ivory Search Constructor.
	 */
	public function __construct() {
		Ivory_Search::$opt = self::load_options();
	}

	/**
	 * Gets the instance of this class.
	 *
	 * @return self
	 */
	public static function getInstance() {

		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Loads plugin options.
	 *
	 */
	public static function load_options() {
		if ( empty( Ivory_Search::$opt ) ) {
			$temp = (array)get_option( 'ivory_search', array() );
			$is_menu_search = get_option( 'is_menu_search', array() );
			$temp2 = array_merge( $temp, (array)$is_menu_search );
			$is_settings = get_option( 'is_settings', array() );
			$temp3 = array_merge( $temp2, (array)$is_settings );
			$is_notices = get_option( 'is_notices', array() );
			Ivory_Search::$opt = array_merge( $temp3, (array)$is_notices );
			return Ivory_Search::$opt;
		} else {
			return Ivory_Search::$opt;
		}
	}

	/**
	 * Defines Ivory Search Constants.
	 */
	private function define_constants() {

		if ( ! defined( 'IS_VERSION' ) ) {
			define( 'IS_VERSION', '4.7' );
		}
		if ( ! defined( 'IS_PLUGIN_FILE' ) ) {
			define( 'IS_PLUGIN_FILE', __FILE__ );
		}
		if ( ! defined( 'IS_PLUGIN_BASE' ) ) {
			define( 'IS_PLUGIN_BASE', plugin_basename( IS_PLUGIN_FILE ) );
		}
		if ( ! defined( 'IS_PLUGIN_DIR' ) ) {
			define( 'IS_PLUGIN_DIR', plugin_dir_path( IS_PLUGIN_FILE ) );
		}
		if ( ! defined( 'IS_PLUGIN_URI' ) ) {
			define( 'IS_PLUGIN_URI', plugins_url( '/', IS_PLUGIN_FILE ) );
		}
		if ( ! defined( 'IS_ADMIN_READ_CAPABILITY' ) ) {
			define( 'IS_ADMIN_READ_CAPABILITY', 'edit_posts' );
		}

		if ( ! defined( 'IS_ADMIN_READ_WRITE_CAPABILITY' ) ) {
			define( 'IS_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );
		}
	}

	/**
	 * Includes required core files used in admin and on the frontend.
	 */
	public function includes() {

		foreach( glob( IS_PLUGIN_DIR.'includes/' . "*.php" ) as $file ) {
			require_once $file;
		}

		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			foreach( glob( IS_PLUGIN_DIR.'admin/' . "*.php" ) as $file ) {
				require_once $file;
			}
		}

        if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			foreach( glob( IS_PLUGIN_DIR.'public/' . "*.php" ) as $file ) {
				require_once $file;
			}
		}
	}

	/**
	 * Hooks into initialization actions and filters.
	 */
	private function init_hooks() {
		// Executes necessary actions on plugin activation and deactivation.
		register_activation_hook( IS_PLUGIN_FILE, array( 'IS_Activator', 'activate' ) );
		register_deactivation_hook( IS_PLUGIN_FILE, array( 'IS_Deactivator', 'deactivate' ) );
	}

	/**
	 * Starts plugin execution.
	 */
	function start() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		$is_loader = IS_Loader::getInstance();
		$is_loader->load();
	}
}

/**
 * Starts plugin execution.
 */
function ivory_search_start(){
	$is = Ivory_Search::getInstance();
	$is->start();
}
add_action( 'plugins_loaded', 'ivory_search_start' );