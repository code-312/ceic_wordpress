<?php
/**
 * The class is the core plugin responsible for including and
 * instantiating all of the code that composes the plugin.
 *
 * The class includes an instance to the plugin
 * Loader which is responsible for coordinating the hooks that exist within the
 * plugin.
 *
 * @since    1.0.0
 * @package IS
 */

class IS_Loader {

	/**
	 * Stores plugin options.
	 */
	public $opt;

	/**
	 * Core singleton class
	 * @var self
	 */
	private static $_instance;

	/**
	 * Instantiates the plugin by setting up the core properties and loading
	 * all necessary dependencies and defining the hooks.
	 *
	 * The constructor uses internal functions to import all the
	 * plugin dependencies, and will leverage the Ivory_Search for
	 * registering the hooks and the callback functions used throughout the plugin.
	 */
	public function __construct() {

		$this->opt = Ivory_Search::load_options();

	}

	/**
	 * Gets the instance of this class.
	 *
	 * @return self
	 */
	public static function getInstance( $is_opt = null ) {
		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self( $is_opt );
		}

		return self::$_instance;
	}

	/**
	 * Loads plugin functionality.
	 */
	function load() {
            if ( ! ivory_search_is_json_request() ) {
		$this->set_locale();

		$this->admin_public_hooks();

		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$this->admin_hooks();
		} 
                if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$this->public_hooks();
		}
            }
	}

	/**
	 * Defines the locale for this plugin for internationalization.
	 *
	 * Uses the IS_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$is_i18n = IS_I18n::getInstance();
		add_action( 'init', array( $is_i18n, 'load_is_textdomain' ) );
	}

	/**
	 * Defines the hooks and callback functions which are executed both in admin and front end areas.
	 *
	 * @access    private
	 */
	private function admin_public_hooks() {
		$admin_public = IS_Admin_Public::getInstance();
		add_action( 'init', array( $admin_public, 'init' ) );
		add_filter( 'get_search_form', array( $admin_public, 'get_search_form' ), 9999999 );
		add_action( 'customize_register', array( $admin_public, 'customize_register' ) );
		add_filter( 'upload_mimes', array( $admin_public, 'add_custom_mime_types' ) );
	}

	/**
	 * Defines the hooks and callback functions that are used for setting up the plugin's admin options.
	 *
	 * @access    private
	 */
	private function admin_hooks() {
		$admin = IS_Admin::getInstance();

		add_action( 'all_admin_notices', array( $admin, 'all_admin_notices' ) );
		add_action( 'admin_footer', array( $admin, 'admin_footer' ), 100 );
		add_action( 'plugin_action_links', array( $admin, 'plugin_action_links' ), 10, 2 );
                add_filter( 'plugin_row_meta', array( $admin, 'plugin_row_meta' ), 10, 2 );
		add_action( 'admin_menu', array( $admin, 'admin_menu' ) );
		add_action( 'wp_ajax_nopriv_display_posts', array( $admin, 'display_posts' ) );
		add_action( 'wp_ajax_display_posts', array( $admin, 'display_posts' ) );
		add_action( 'admin_enqueue_scripts', array( $admin, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( $admin, 'admin_init' ) );
		add_action( 'is_admin_notices', array( $admin, 'admin_updated_message' ) );
		add_filter( 'map_meta_cap', array( $admin, 'map_meta_cap' ), 10, 4 );
		add_filter( 'admin_footer_text', array( $admin, 'admin_footer_text' ), 1 );
	}

	/**
	 * Defines the hooks and callback functions that are used for executing plugin functionality
	 * in the front end of site.
	 *
	 * @access    private
	 */
	private function public_hooks() {

		$public = IS_Public::getInstance();

                if ( isset( $this->opt['disable'] ) ) {
                    return;
		}

		add_action( 'wp_enqueue_scripts', array( $public, 'wp_enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $public, 'wp_enqueue_scripts' ), 9999999 );
		add_filter( 'query_vars', array( $public, 'query_vars' ) );
        add_filter( 'body_class', array( $public, 'is_body_classes' ) );

		$header_menu_search = isset( $this->opt['header_menu_search'] ) ? $this->opt['header_menu_search'] : 0;
		$site_cache = isset( $this->opt['site_uses_cache'] ) ? $this->opt['site_uses_cache'] : 0;
		$display_in_mobile_menu = false;
		if ( function_exists( 'wp_is_mobile' ) ) {
			$display_in_mobile_menu = $header_menu_search && wp_is_mobile() ? true : false;
		}

		if ( $display_in_mobile_menu || $site_cache ) {
			add_action( 'wp_head', array( $public, 'header_menu_search' ), 9999999 );
		}

		if ( ! $display_in_mobile_menu || $site_cache ) {
			add_filter( 'wp_nav_menu_items', array( $public, 'wp_nav_menu_items' ), 9999999, 2 );
		}

		add_action( 'init', function () {
			$public = IS_Public::getInstance();
			add_filter( 'posts_distinct_request', array( $public, 'posts_distinct_request' ), 9999999, 2 );
			add_filter( 'posts_join' , array( $public, 'posts_join' ), 9999999, 2 );
			add_filter( 'posts_search', array( $public, 'posts_search' ), 9999999, 2 );
			add_action( 'pre_get_posts', array( $public, 'pre_get_posts' ), 9 );
			add_action( 'wp_footer', array( $public, 'wp_footer' ) );
			add_action( 'wp_head', array( $public, 'wp_head' ), 9999999 );
		}, 9999999 );

                $ajax = IS_Ajax::getInstance();
		add_action( 'wp_ajax_is_ajax_load_posts', array( $ajax, 'ajax_load_posts' ) );
		add_action( 'wp_ajax_nopriv_is_ajax_load_posts', array( $ajax, 'ajax_load_posts' ) );
	}

	/**
	 * Displays search form by processing shortcode.
	 */
	function search_form_shortcode( $atts ) {

		if ( is_feed() ) {
			return '[ivory-search]';
		}

        if ( isset( $this->opt['disable'] ) ) {
                    return;
		}

		$atts = shortcode_atts(
			array(
				'id'	     => 0,
				'title'	     => '',
			),
			$atts, 'ivory-search'
		);

		$id = (int) $atts['id'];

        $search_form = IS_Search_Form::get_instance( $id );

		if ( ! $search_form ) {
			return '[ivory-search 404 "The search form '.$id.' does not exist"]';
		} 

		$form  = $search_form->form_html( $atts );

		return $form;
	}

}

$is_loader = IS_Loader::getInstance();
add_shortcode( 'ivory-search', array( $is_loader, 'search_form_shortcode' ) );