<?php
/**
 * Adds IS_Widget widget.
 */
class IS_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'is_widget', // Base ID
			esc_html__( 'Ivory Search', 'add-search-to-menu' ), // Name
			array( 'description' => esc_html__( 'Displays ivory search form.', 'add-search-to-menu' ), 'classname' => 'widget_is_search widget_search', ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		if ( ! empty( $instance['search_form'] ) ) {
				echo do_shortcode( '[ivory-search id="' . $instance['search_form'] . '"]' );
		} else {
			$page = get_page_by_path( 'default-search-form', OBJECT, 'is_search_form' );
            if ( ! empty( $page ) ) {
					echo do_shortcode( '[ivory-search id="' . $page->ID . '"]' );
            } else {
                    _e( 'Please select search form in the Ivory Search widget.', 'add-search-to-menu' );
                }
         }
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$search_form = ! empty( $instance['search_form'] ) ? $instance['search_form'] : 0;
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'add-search-to-menu' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<?php
			$html = '';
			$args = array( 'numberposts' => -1, 'post_type' => 'is_search_form' );

			$posts = get_posts( $args );

			if ( ! empty( $posts ) ) {
				$html .= '<label for="'. esc_attr( $this->get_field_id( 'search_form' ) ) .'">'. esc_attr_e( 'Search Form:', 'add-search-to-menu' ) .'</label>';
				$html .= '<select class="widefat" id="'.esc_attr( $this->get_field_id( 'search_form' ) ).'" name="'.esc_attr( $this->get_field_name( 'search_form' ) ).'" >';
				$html .= '<option value="0">' . __( 'Click to select Search Form', 'add-search-to-menu' ) . '</option>';
				if ( ! isset( $instance['search_form'] ) && ! $search_form ) {
				foreach ($posts as $val) {
				       if ( 'default-search-form' === $val->post_name ) {
				           $search_form = $val->ID;
				       }
				   }
				}
				foreach ( $posts as $post ) {
					$html .= '<option value="' . $post->ID . '"' . selected( $post->ID, $search_form, false ) . ' >' . $post->post_title . '</option>';
				}

				$html .= '</select>';
				if ( $search_form && get_post_type( $search_form ) ) {
					$html .= '<a href="' . get_admin_url( null, 'admin.php?page=ivory-search&post='.$search_form.'&action=edit' ) . '">  ' . esc_html__( "Edit", 'add-search-to-menu' ) . '</a>';
				} else {
					$html .= '<a href="' . get_admin_url( null, 'admin.php?page=ivory-search-new' ) . '">  ' . esc_html__( "Create New", 'add-search-to-menu' ) . '</a>';
				}
				echo $html;
			}
		?>
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['search_form'] = ( ! empty( $new_instance['search_form'] ) ) ? sanitize_text_field( $new_instance['search_form'] ) : '';
		return $instance;
	}

} // class IS_Widget


// Register Ivory Search Widget
function is_register_widget() {
	register_widget( 'IS_Widget' );
}
add_action( 'widgets_init', 'is_register_widget' );