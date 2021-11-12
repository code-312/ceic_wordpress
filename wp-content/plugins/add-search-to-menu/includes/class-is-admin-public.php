<?php
/**
 * This class defines all plugin functionality for the dashboard of the plugin and site front end.
 *
 * @package IS
 * @since    4.0
 */

class IS_Admin_Public {

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
	 * Initializes this class and stores the plugin options.
	 */
	public function __construct() {
		$this->opt = Ivory_Search::load_options();
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
	 * Added MIME support
	 *
	 * @since  4.3
	 * @param array $mimes Mime types.
	 */
    function add_custom_mime_types( $mimes ){
        return array_merge( $mimes, array (
            'gif' => 'image/gif',
        ));
    }

    /**
     * Customizer settings
     * 
     * Register customizer settings for each search form.
     *
     * @since 4.3
     * @param  object $wp_customize Customizer Object.
     * @return void
     */
	function customize_register( $wp_customize ) {

		$query_args = apply_filters( 'is_customize_register_args', array(
			'post_type'  => 'is_search_form',

			// Query performance optimization.
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'posts_per_page' => -1,
            'orderby'	 => 'Date',
            'order'		 => 'DESC',
            'suppress_filters' => true,
		));

		$meta_query = new WP_Query( $query_args );

		if( $meta_query->posts ) {
			foreach ( $meta_query->posts as $key => $post_id ) {
				$option_name = 'is_search_' . $post_id;
                                
				// Section 
				$sections[ 'is_section_' . $post_id ] = array(
					'title'   => get_the_title( $post_id ),
					'options' => $this->settings( $post_id, $option_name ),
				);
			}
		}

		/* General Panel */
		IS_Customizer_Panel::get_instance()->add_panel(
			'is_search_form_panel', array(
				'title'    => __( 'Ivory Search', 'add-search-to-menu' ),
				'sections' => $sections,
			)
		);

		// Register all panels.
		IS_Customizer_Panel::get_instance()->register_panels( $wp_customize );

	}

	/**
	 * Customizer Settings
	 *
	 * @since 4.3
	 * 
	 * @param  int $post_id      Post ID.
	 * @param  string $setting_name Setting name.
	 * @return array               Customizer settings.
	 */
	function settings( $post_id, $setting_name ) {

		$fields = array();

		$search_form = IS_Search_Form::get_instance( $post_id );

		if( $search_form ) {

			$_ajax = $search_form->prop( '_is_ajax' );
			if ( isset( $_ajax['enable_ajax'] ) ) {
				$fields[ $setting_name . '[loader-image]' ] = array(
					'setting' => array(
						'type'    => 'option',
						'default' => IS_PLUGIN_URI . 'public/images/spinner.gif',
					),
					'control' => array(
						'class'      => 'WP_Customize_Image_Control',
						'label'      => __( 'Loader Image', 'add-search-to-menu' ),
						'type'       => 'image',
						'capability' => 'edit_theme_options',
                        'description'=> __( 'AJAX loader image.', 'add-search-to-menu' ),
					)
				);
			}

			// Customize options.
			$_customize = $search_form->prop('_is_customize');
			if( isset( $_customize['enable_customize'] ) || isset( $_ajax['enable_ajax'] ) || 'default-search-form' != $search_form->name() ) {
		
				$fields[ $setting_name . '[form-style]' ] = array(
					'setting' => array(
						'type'    => 'option',
						'default' => 'is-form-style-3',
					),
					'control' => array(
						'class'      => 'IS_Control_Radio_Image',
						'type'       => 'is-radio-image',
						'label'      => __( 'Search Form Style', 'add-search-to-menu' ),
                                                'description'=> __( 'Search form submit button field style.', 'add-search-to-menu' ),
						'capability' => 'edit_theme_options',
						'choices'  => array(
							'is-form-style-1' => array(
								'label' => __( 'Style 1', 'add-search-to-menu' ),
								'path'  => IS_PLUGIN_URI . 'includes/customizer/controls/radio-image/images/style-1.png',
							),
							'is-form-style-2' => array(
								'label' => __( 'Style 2', 'add-search-to-menu' ),
								'path'  => IS_PLUGIN_URI . 'includes/customizer/controls/radio-image/images/style-2.png',
							),
							'is-form-style-3' => array(
								'label' => __( 'Style 3', 'add-search-to-menu' ),
								'path'  => IS_PLUGIN_URI . 'includes/customizer/controls/radio-image/images/style-3.png',
							)
						),
					)
				);

				$fields[ $setting_name . '[placeholder-text]' ] = array(
					'setting' => array(
						'type'              => 'option',
						'default'           => __( 'Search here...', 'add-search-to-menu' ),
					),
					'control' => array(
						'class'      => 'WP_Customize_Control',
						'label'      => __( 'Text Box Placeholder', 'add-search-to-menu' ),
						'type'       => 'text',
						'capability' => 'edit_theme_options',
					)
				);

				$fields[ $setting_name . '[search-btn-text]' ] = array(
					'setting' => array(
						'type'    => 'option',
						'default' => __( 'Search', 'add-search-to-menu' ),
					),
					'control' => array(
						'class'      => 'WP_Customize_Control',
						'label'      => __( 'Search Button', 'add-search-to-menu' ),
						'type'       => 'text',
						'capability' => 'edit_theme_options',
					)
				);

				$colors = array(
					// Input.
					'text-box-bg'     => '',
					'text-box-text'   => '',
					'text-box-border' => '',

					// Submit.
					'submit-button-bg' 	=> '',
					'submit-button-text'  => '',
					'submit-button-border' => '',
				);
				foreach ($colors as $color_key => $default_color) {
					$color_key_modified = $color_key;
					$color_key_modified = str_replace('-h-', ' hover ', $color_key_modified);
					$color_key_modified = str_replace('-bg', ' background ', $color_key_modified);
					$color_key_modified = str_replace('-', ' ', $color_key_modified);
					$fields[ $setting_name . '['.$color_key.']' ] = array(
						'setting' => array(
							'type'              => 'option',
							'default'           => $default_color,
							'sanitize_callback' => 'sanitize_hex_color',
						),
						'control' => array(
							'class'      => 'WP_Customize_Color_Control',
							'label'      => ucwords( $color_key_modified ),
							'type'       => 'color',
							'capability' => 'edit_theme_options',
						)
					);
				}

			}

			// AJAX customizer fields.
			if ( isset( $_ajax['enable_ajax'] ) ) {

				// Suggestion Box.
				$colors = array(
					'search-results-bg'       => '',
					'search-results-hover'    => '',
					'search-results-text'     => '',
					'search-results-link'     => '',
					'search-results-border'   => '',
				);
				foreach ($colors as $color_key => $default_color) {
					$color_key_modified = 'AJAX ' . $color_key;
					$color_key_modified = str_replace('-h-', ' hover ', $color_key_modified);
					$color_key_modified = str_replace('-bg', ' background ', $color_key_modified);
					$color_key_modified = str_replace('-', ' ', $color_key_modified);
					$fields[ $setting_name . '['.$color_key.']' ] = array(
						'setting' => array(
							'type'              => 'option',
							'default'           => $default_color,
							'sanitize_callback' => 'sanitize_hex_color',
						),
						'control' => array(
							'class'      => 'WP_Customize_Color_Control',
							'label'      => ucwords( $color_key_modified ),
							'type'       => 'color',
							'capability' => 'edit_theme_options',
						)
					);
				}

			}
		}

		return apply_filters( 'is_customize_fields', $fields );
	}


	/**
	 * Executes actions on initialization.
	 */
	function init() {
		/* Registers post types */
		if ( class_exists( 'IS_Search_Form' ) ) {
			IS_Search_Form::register_post_type();
		}
	}

	/**
	 * Changes default search form.
	 */
	function get_search_form( $form ) {

                if ( isset( $this->opt['disable'] ) ) {
                    return '';
		}

		if ( isset( $this->opt['default_search'] ) ) {
			return $form;
		}

		$page = get_page_by_path( 'default-search-form', OBJECT, 'is_search_form' );

		if ( ! empty( $page ) ) {
                        $search_form = IS_Search_Form::get_instance( $page->ID );
                        if ( $search_form ) {
                            $atts['id'] = (int) $page->ID;
                            $form  = $search_form->form_html( $atts, 'n' );
                        }
                }

		return $form;
        }


	/**
	 * Formats attributes.
	 */
	public static function format_atts( $atts ) {
		$html = '';

		$prioritized_atts = array( 'type', 'name', 'value' );

		foreach ( $prioritized_atts as $att ) {
			if ( isset( $atts[$att] ) ) {
				$value = trim( $atts[$att] );
				$html .= sprintf( ' %s="%s"', $att, esc_attr( $value ) );
				unset( $atts[$att] );
			}
		}

		foreach ( $atts as $key => $value ) {
			$key = strtolower( trim( $key ) );

			if ( ! preg_match( '/^[a-z_:][a-z_:.0-9-]*$/', $key ) ) {
				continue;
			}

			$value = trim( $value );

			if ( '' !== $value ) {
				$html .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
			}
		}

		$html = trim( $html );

		return $html;
	}
}
