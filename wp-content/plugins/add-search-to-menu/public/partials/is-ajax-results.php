<?php
/**
 * Represents the AJAX resilts view.
 *
 * @package IS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exits if accessed directly.
}

if( 1 == $page ) { ?>
	<div class="is-ajax-search-items <?php echo esc_attr( $posts_class ); ?>">
<?php } 
	if ( isset( $is_includes['post_type'] ) && in_array( 'product', $is_includes['post_type']  ) ) {
		$strict = isset( $is_settings['fuzzy_match'] ) && 1 == $is_settings['fuzzy_match'] ? true : false;
	// Show matching tags.
	if( isset( $field['show_matching_tags'] ) && $field['show_matching_tags'] && 1 == $page ) {
		$this->term_title_markup( array(
			'taxonomy'      => 'product_tag',
			'search_term'   => $search_term,
			'title'         => __( 'Tag', 'add-search-to-menu' ),
			'wrapper_class' => 'is-ajax-search-tags',
			'strict'		=> $strict,
		) );
	}

	// Show matching categories.
	if( isset( $field['show_matching_categories'] ) && $field['show_matching_categories'] && 1 == $page ) {
		$this->term_title_markup( array(
			'taxonomy'      => 'product_cat',
			'search_term'   => $search_term,
			'title'         => __( 'Category', 'add-search-to-menu' ),
			'wrapper_class' => 'is-ajax-search-categories',
			'strict'		=> $strict,
		) );
	}
	}

	$post_args = array(
        'suppress_filters' => false,
        'post_type' => '',
        'posts_per_page' => $posts_per_page,
		's'     => $search_term,
		'paged' => $page,
	);

	$post_args = apply_filters( 'is_ajax_search_args', $post_args );
	$posts = new WP_Query( $post_args );

	if ( $posts->posts ) {

		if( 1 == $page ) { ?>
			<div class="is-ajax-search-posts">
		<?php }

	    foreach ( $posts->posts as $post ) :
	        setup_postdata( $post );

	        $product = '';
	        $product_class = '';
	        if( ( 'product' === $post->post_type || 'product_variation' === $post->post_type ) && function_exists( 'wc_get_product' ) ) {
	        	$product = wc_get_product( $post->ID );
	        	$product_class = 'is-product';
                            if ( isset( $field['show_sale_badge'] ) && $field['show_sale_badge'] ) {
                                $on_sale = ( $product->is_in_stock() ) ? $product->is_on_sale() : '';
                                if ( $on_sale ) {
                                    $product_class .= ' is-has-badge';
                                }
                            }
	        }
	        ?>
	        <div data-id="<?php echo esc_attr( $post->ID ); ?>" class="is-ajax-search-post is-ajax-search-post-<?php echo esc_attr( $post->ID ) .' '. esc_attr( $product_class ); ?>">
	        	<!-- Header -->
	        	<div class="is-search-sections">

	        		<?php $this->image_markup( $field, $post ); ?>

	        		<div class="right-section">

                                                    <?php
                                                    $this->title_markup( $field, $post, $product ); 
                                                    ?>

			        	<div class="meta">
                                                    <div>
                                                        <?php $this->product_price_markup( $field, $product ); ?>
						<?php $this->product_stock_status_markup( $field, $product ); ?>
                                                    	<?php $this->product_sku_markup( $field, $product ); ?>
                                                    </div>
                                                        <?php $this->date_markup( $field, $post ); ?>
		        			<?php $this->author_markup( $field ); ?>
		        			<?php $this->tags_markup( $field, $post ); ?>
		        			<?php $this->categories_markup( $field, $post ); ?>
			        	</div><!-- .meta -->

			        	<!-- Content -->
			        	<div class="is-search-content">
			        		<?php $this->description_markup( $field, $post ); ?>
                                                </div>

						<!-- WooCommerce Contents -->
						<?php
						$this->product_sale_badge_markup( $field, $product );
		                ?>
	        		</div>
	        	</div>

        	</div>
	    <?php endforeach;
	    wp_reset_postdata();?>

	    <?php if( 1 == $page ) {?>
	    	</div>
		<?php } ?>


	    <?php
	} else if( empty( $tags ) && empty( $categories )) {
		?>
		<div class="is-ajax-search-no-result">
			<?php echo html_entity_decode( $field['nothing_found_text'] ); ?>
		</div>
		<?php
	}
	?>

<?php if( 1 == $page ) { ?>
	</div>
<?php } ?>
        <?php
            if ( isset( $field['show_more_result'] ) && $field['show_more_result'] && ( $posts->found_posts > ( $posts_per_page * $page ) ) ) {
	    $next_page = $page + 1; 
	    $show_more_class = ( isset( $field['show_more_func'] ) && $field['show_more_func'] ) ? 'redirect-tosr' : '';
	    ?>
	    	<div class="is-show-more-results <?php echo $show_more_class;?>" data-page="<?php echo $next_page; ?>">
	    		<div class="is-show-more-results-text"><?php echo $field['more_result_text'].' <span>('. ( $posts->found_posts - ( $posts_per_page * $page ) ) .')</span>'; ?></div>
		    	<?php
		    	if ( '' === $show_more_class ){
		    	// AAJX Loader.
                        $settings = get_option( 'is_search_' . $search_post_id );
                        $loader_image = isset( $settings['loader-image'] ) ? $settings['loader-image'] : IS_PLUGIN_URI . 'public/images/spinner.gif';
                        if( $loader_image ) {
                                echo '<img class="is-load-more-image" alt="'. esc_attr__( "Loader Image", 'add-search-to-menu' ) .'" src="'.esc_attr( $loader_image ).'" style="display: none;" />';
                        }
                    }
		    	?>
	    	</div>
	    <?php } ?>		
<?php if( isset( $field['show_details_box'] ) && $field['show_details_box'] ) { ?>
    <div id="is-ajax-search-details-<?php echo $search_post_id; ?>" class="is-ajax-search-details">
                <div class="is-ajax-search-items">
	    <?php
                    if ( 1 == $page ) {
		if ( isset( $is_includes['post_type'] ) && in_array( 'product', $is_includes['post_type']  ) ) {
	    // Show product details by "tags".
		if( isset( $field['show_matching_tags'] ) && $field['show_matching_tags'] ) {
			$this->product_details_markup( array(
				'taxonomy'      => 'product_tag',
				'search_term'   => $search_term,
				'field'         => $field,
				'wrapper_class' => 'is-ajax-search-tags-details',
			) );
		}
		?>

	    <?php
	    // Show product details by "categories".
		if( isset( $field['show_matching_categories'] ) && $field['show_matching_categories'] ) {
			$this->product_details_markup( array(
				'taxonomy'      => 'product_cat',
				'search_term'   => $search_term,
				'field'         => $field,
				'wrapper_class' => 'is-ajax-search-categories-details',
			) );
		}
		}
                    }
		if ( $posts->posts ) { ?>
			<div class="is-ajax-search-posts-details">
				<?php
			    foreach ( $posts->posts as $post ) :
			        setup_postdata( $post );
			        $product = '';
                                        $product_class = '';
			        if( ( 'product' === $post->post_type || 'product_variation' === $post->post_type ) && function_exists( 'wc_get_product' ) ) {
			        	$product = wc_get_product( $post->ID );
                                            if ( isset( $field['show_sale_badge'] ) && $field['show_sale_badge'] ) {
                                                $on_sale = ( $product->is_in_stock() ) ? $product->is_on_sale() : '';
                                                if ( $on_sale ) {
                                                    $product_class .= ' is-has-badge';
                                                }
                                            }
			        }

			        // Is WooCommerce product then show details box.
			        if( $product ) { ?>
			            <div data-id="<?php echo esc_attr( $post->ID ); ?>" class="is-ajax-search-post-details is-ajax-search-post-details-<?php echo esc_attr( $post->ID ).' '. esc_attr( $product_class ); ?>">
			            	<div class="is-search-sections">
								<?php $this->image_markup( $field, $post ); ?>
			            		<div class="right-section">
                                                            <?php
                                                            $this->title_markup( $field, $post, $product );
                                                            ?> 
                                                            <div class="meta">
                                                                <div>
                                                                    <?php $this->product_price_markup( $field, $product ); ?>
                                                                    <?php $this->product_stock_status_markup( $field, $product ); ?>
                                                                    <?php $this->product_sku_markup( $field, $product ); ?>
                                                                </div>
                                                                    <?php $this->date_markup( $field, $post ); ?>
                                                                    <?php $this->author_markup( $field ); ?>
                                                                    <?php $this->tags_markup( $field, $post ); ?>
                                                                    <?php $this->categories_markup( $field, $post ); ?>
                                                            </div><!-- .meta -->
                                                  <!-- Content -->
			        	<div class="is-search-content">
			        		<?php $this->description_markup( $field, $post, true ); ?>
                                                </div>
					<?php $this->product_sale_badge_markup( $field, $product );

									if( $product ) { ?>
										<div class="is-ajax-woocommerce-actions">
											<?php
                                                                                                if ( function_exists( 'woocommerce_quantity_input' ) ) {
											woocommerce_quantity_input( array(
                                                                                                    'input_name'  => 'is-ajax-search-product-quantity',
                                                                                                ), $product, true );
											echo WC_Shortcodes::product_add_to_cart( array(
													'id'		 => $post->ID,
													'show_price' => false,
													'style'		 => '',
												) );
                                                                                                } ?>
										</div>
									<?php } ?>
			            		</div>
							</div>
						</div>
					<?php
			        }
		        endforeach;
		   		 wp_reset_postdata();?>
			</div>
		<?php } ?>
                </div>
    </div>
    <?php
}