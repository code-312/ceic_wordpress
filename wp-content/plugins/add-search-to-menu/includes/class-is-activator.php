<?php
/**
 * Fires during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    IS
 * @subpackage IS/includes
 * @author     Ivory Search <admin@ivorysearch.com>
 */

class IS_Activator {

	/**
	 * The code that runs during plugin activation.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		/* Creates default search forms */
		$search_form = get_page_by_path( 'default-search-form', OBJECT, IS_Search_Form::post_type );

		if ( NULL == $search_form ) {

			$admin = IS_Admin::getInstance();

			$args['id'] = -1;
			$args['title'] = 'Custom Search Form';
			$args['_is_locale'] = 'en_US';
			$args['_is_includes'] = '';
			$args['_is_excludes'] = '';
			$args['_is_settings'] = '';
			$admin->save_form( $args );

			$args['title'] = 'Default Search Form';
			$admin->save_form( $args );

			$args['title'] = 'AJAX Search Form';
			$args['_is_ajax'] = array( 
			    'enable_ajax' => 1,
			    'show_description' => 1,
			    'description_source' => 'excerpt',
			    'description_length' => 20,
			    'show_image' => 1,
			    'min_no_for_search' => 1,
			    'result_box_max_height' => 400,
			    'nothing_found_text' => 'Nothing found',
			    'show_more_result' => 1,
			    'more_result_text' => 'More Results..',
			    'search_results' => 'both',
			    'show_price' => 1,
			    'show_matching_categories' => 1,
			    'show_details_box' => 1,
			    'product_list' => 'all',
			    'order_by' => 'date',
			    'order' => 'desc',
			);
			$admin->save_form( $args );

			$args['title'] = 'AJAX Search Form for WooCommerce';
			$args['_is_includes'] = array(
                'post_type' => array( 'product' => 'product' ),
                'search_title'   => 1,
                'search_content' => 1,
                'search_excerpt' => 1,
                'post_status' => array( 'publish' => 'publish', 'inherit' => 'inherit' ),
            );
			$admin->save_form( $args );
		}
	}
}