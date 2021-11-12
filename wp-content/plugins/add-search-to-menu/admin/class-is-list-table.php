<?php
/**
 * Represents the view for the plugin search forms page.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user to search forms.
 *
 * @package IS
 */


/**
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Creates a new list table package that extends the core WP_List_Table class.
 */

class IS_List_Table extends WP_List_Table {

	public static function define_columns() {
		$columns = array(
			'cb'		=> '<input type="checkbox" />',
			'title'		=> __( 'Title', 'add-search-to-menu' ),
			'shortcode' => __( 'Shortcode', 'add-search-to-menu' ),
			'date'		=> __( 'Date', 'add-search-to-menu' ),
		);

		return $columns;
	}

	function __construct() {
		parent::__construct( array(
			'singular'	=> 'post',
			'plural'	=> 'posts',
			'ajax'		=> false,
		) );
	}

	function prepare_items() {
		$current_screen = get_current_screen();
		$per_page = $this->get_items_per_page( 'is_search_forms_per_page' );

		$this->_column_headers = $this->get_column_info();

		$args = array(
			'posts_per_page' => $per_page,
			'orderby'		 => 'Date',
			'order'			 => 'DESC',
			'offset'		 => ( $this->get_pagenum() - 1 ) * $per_page,
		);

		if ( ! empty( $_REQUEST['s'] ) ) {
			$args['s'] = $_REQUEST['s'];
		}

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			if ( 'title' == $_REQUEST['orderby'] ) {
				$args['orderby'] = 'title';
			} elseif ( 'date' == $_REQUEST['orderby'] ) {
				$args['orderby'] = 'date';
			}
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			if ( 'asc' == strtolower( $_REQUEST['order'] ) ) {
				$args['order'] = 'ASC';
			} elseif ( 'desc' == strtolower( $_REQUEST['order'] ) ) {
				$args['order'] = 'DESC';
			}
		}

		$this->items = IS_Search_Form::find( $args );

		$total_items = IS_Search_Form::count();
		$total_pages = ceil( $total_items / $per_page );

                if ( 1 == $total_items && ! isset( $_GET['s'] )  ) {
                    if ( isset( $this->items[0] ) && $this->items[0]->id() ) {
                        $redirect_to = esc_url( menu_page_url( 'ivory-search', false ) ) . '&post='.$this->items[0]->id().'&action=edit';
			wp_safe_redirect( $redirect_to );
			exit();
                    }
                }

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page'	  => $per_page,
		) );
	}

	function get_columns() {
		return get_column_headers( get_current_screen() );
	}

	function get_sortable_columns() {
		$columns = array(
			'title'	 => array( 'title', true ),
			'date'   => array( 'date', false ),
		);

		return $columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'add-search-to-menu' ),
		);

		return $actions;
	}

	function column_default( $item, $column_name ) {
		return '';
	}

	function column_cb( $item ) {
		if ( 'default-search-form' !== $item->name() || defined( 'DELETE_DEFAULT_SEARCH_FORM' ) ) {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				$this->_args['singular'],
				$item->id() );
		} else {
			return '';
		}
	}

	function column_title( $item ) {
		$url = admin_url( 'admin.php?page=ivory-search&post=' . absint( $item->id() ) );
		$edit_link = add_query_arg( array( 'action' => 'edit' ), $url );

		$output = sprintf(
			'<a class="row-title" href="%1$s" title="%2$s">%3$s</a>',
			esc_url( $edit_link ),
			/* translators: %s: title of search form */
			esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'add-search-to-menu' ),
				$item->title() ) ),
			esc_html( $item->title() )
		);

		$output = sprintf( '<strong>%s</strong>', $output );

		$actions = array(
			'edit' => sprintf( '<a href="%1$s">%2$s</a>',
				esc_url( $edit_link ),
				esc_html( __( 'Edit', 'add-search-to-menu' ) ) ) );

		if ( current_user_can( 'is_edit_search_form', $item->id() ) ) {
			$copy_link = wp_nonce_url(
				add_query_arg( array( 'action' => 'copy' ), $url ),
				'is-copy-search-form_' . absint( $item->id() ) );

			$actions = array_merge( $actions, array(
				'copy' => sprintf( '<a href="%1$s">%2$s</a>',
					esc_url( $copy_link ),
					esc_html( __( 'Duplicate', 'add-search-to-menu' ) )
				),
			) );
		}

		if ( current_user_can( 'is_delete_search_form', $item->id() ) && ( 'default-search-form' !== $item->name() || defined( 'DELETE_DEFAULT_SEARCH_FORM' ) ) ) {
			$delete_link = wp_nonce_url(
				add_query_arg( array( 'action' => 'delete' ), $url ),
				'is-delete-search-form_' . absint( $item->id() ) );

			$actions = array_merge( $actions, array(
				'delete' => sprintf( '<a href="%1$s" %2$s>%3$s</a>',
					esc_url( $delete_link ),
					"onclick=\"if (confirm('" . esc_js( __( "You are about to delete this search form.\n  'Cancel' to stop, 'OK' to delete.", 'add-search-to-menu' ) ) . "')) {return true;} return false;\"",
					esc_html( __( 'Delete', 'add-search-to-menu' ) )
				),
			) );
		}

		$output .= $this->row_actions( $actions );

		return $output;
	}

	function column_shortcode( $item ) {
		$shortcodes = array( $item->shortcode() );

		$output = '';

		foreach ( $shortcodes as $shortcode ) {
			$output .= "\n" . '<span class="shortcode"><input type="text"'
				. ' onfocus="this.select();" readonly="readonly"'
				. ' value="' . esc_attr( $shortcode ) . '"'
				. ' class="large-text code" /></span>';
		}

		return trim( $output );
	}

	function column_date( $item ) {
		$post = get_post( $item->id() );

		if ( ! $post ) {
			return;
		}

		$t_time = mysql2date( __( 'Y/m/d g:i:s A', 'add-search-to-menu' ),
			$post->post_date, true );
		$m_time = $post->post_date;
		$time = mysql2date( 'G', $post->post_date )
			- get_option( 'gmt_offset' ) * 3600;

		$time_diff = time() - $time;

		if ( $time_diff > 0 && $time_diff < 24*60*60 ) {
			/* translators: %s: time since the creation of the search form */
			$h_time = sprintf(
				__( '%s ago', 'add-search-to-menu' ), human_time_diff( $time ) );
		} else {
			$h_time = mysql2date( __( 'd/m/Y', 'add-search-to-menu' ), $m_time );
		}

		return '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
	}
}
