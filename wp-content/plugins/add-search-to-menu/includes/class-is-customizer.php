<?php
/**
 * Customizer
 *
 * @package IS
 * @since 4.3
 */

if ( ! class_exists( 'IS_Customizer' ) ) :

	/**
	 * Customizer Panel
	 *
	 * @since 4.3
	 */
	class IS_Customizer {

		/**
		 * Instance
		 *
		 * @since 4.3
		 *
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Panels
		 *
		 * @since 4.3
		 *
		 * @access private
		 * @var object Class object.
		 */
		private $panels = array();

		/**
		 * Initiator
		 *
		 * @since 4.3
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 4.3
		 */
		public function __construct() {
			add_action('customize_register', array( $this, 'customize_register' ) );
		}

            /**
	     * Add postMessage support for site title and description for the Theme Customizer.
	     *
	     * @param object $wp_customize Theme Customizer object.
	     */
	    function customize_register( $wp_customize ) 
	    {
	    	include_once IS_PLUGIN_DIR . '/includes/customizer/controls/radio-image/class-is-control-radio-image.php';

	    	// Added custom customizer controls.
	        if ( method_exists( $wp_customize, 'register_control_type' ) ) {
	            $wp_customize->register_control_type( 'IS_Control_Radio_Image' );
	        }
	    }
	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	IS_Customizer::get_instance();

endif;
