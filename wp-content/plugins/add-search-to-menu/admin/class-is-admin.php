<?php

/**
 * The class defines all functionality for the dashboard of the plugin.
 *
 * @package IS
 * @since    1.0.0
 */
class IS_Admin
{
    /**
     * Stores plugin options.
     */
    public  $opt ;
    /**
     * Stores network activation status.
     */
    private  $networkactive ;
    /**
     * Core singleton class
     * @var self
     */
    private static  $_instance ;
    /**
     * Initializes this class.
     *
     */
    public function __construct()
    {
        $this->opt = Ivory_Search::load_options();
        $this->networkactive = is_multisite() && array_key_exists( plugin_basename( IS_PLUGIN_FILE ), (array) get_site_option( 'active_sitewide_plugins' ) );
    }
    
    /**
     * Gets the instance of this class.
     *
     * @return self
     */
    public static function getInstance()
    {
        if ( !self::$_instance instanceof self ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Loads plugin javascript and stylesheet files in the admin area.
     */
    function admin_enqueue_scripts( $hook_suffix )
    {
        if ( false === strpos( $hook_suffix, 'ivory-search' ) ) {
            return;
        }
        // Css rules for Color Picker
        wp_enqueue_style( 'wp-color-picker' );
        $min = ( defined( 'IS_DEBUG' ) && IS_DEBUG ? '' : '.min' );
        wp_enqueue_style(
            'is-admin-styles',
            plugins_url( '/admin/css/ivory-search-admin' . $min . '.css', IS_PLUGIN_FILE ),
            array(),
            IS_VERSION
        );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_register_script(
            'is-admin-scripts',
            plugins_url( '/admin/js/ivory-search-admin' . $min . '.js', IS_PLUGIN_FILE ),
            array(
            'jquery',
            'jquery-ui-tabs',
            'jquery-ui-accordion',
            'wp-color-picker'
        ),
            IS_VERSION,
            true
        );
        $args = array(
            'saveAlert' => __( "The changes you made will be lost if you navigate away from this page.", 'add-search-to-menu' ),
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        );
        
        if ( $this->custom_admin_pointers_check() ) {
            add_action( 'admin_print_footer_scripts', array( $this, 'custom_admin_pointers_footer' ), 999999 );
            wp_enqueue_script( 'wp-pointer' );
            wp_enqueue_style( 'wp-pointer' );
        }
        
        wp_localize_script( 'is-admin-scripts', 'ivory_search', $args );
        wp_enqueue_script( 'is-admin-scripts' );
    }
    
    function custom_admin_pointers_check()
    {
        $admin_pointers = $this->custom_admin_pointers();
        foreach ( $admin_pointers as $pointer => $array ) {
            if ( $array['active'] ) {
                return true;
            }
        }
    }
    
    function custom_admin_pointers_footer()
    {
        $admin_pointers = $this->custom_admin_pointers();
        ?>
	     <script type="text/javascript">
	     /* <![CDATA[ */
	     ( function($) {
                $( window ).on( 'load', function() {
		<?php 
        foreach ( $admin_pointers as $pointer => $array ) {
            
            if ( $array['active'] ) {
                ?>
		      $( '<?php 
                echo  $array['anchor_id'] ;
                ?>' ).pointer( {
			 content: '<?php 
                echo  $array['content'] ;
                ?>',
			 position: {
			 edge: '<?php 
                echo  $array['edge'] ;
                ?>',
			 align: '<?php 
                echo  $array['align'] ;
                ?>'
		      },
			 close: function() {
			    $.post( ajaxurl, {
			       pointer: '<?php 
                echo  $pointer ;
                ?>',
			       action: 'dismiss-wp-pointer'
			    } );
			 }
		      } ).pointer( 'open' );
		      <?php 
            }
        
        }
        ?>
                } );
	     } )(jQuery);
	     /* ]]> */
	     </script>
		<?php 
    }
    
    function custom_admin_pointers()
    {
        $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        $version = '1_0';
        // replace all periods in 1.0 with an underscore
        $prefix = 'is_admin_pointers_' . $version . '_';
        $new_pointer_content = '<h3>' . __( 'Edit Search Form', 'add-search-to-menu' ) . '</h3>';
        $new_pointer_content .= '<p>' . __( 'Click on the search form title to edit it.', 'add-search-to-menu' ) . '</p>';
        return array(
            $prefix . 'is_pointers' => array(
            'content'   => $new_pointer_content,
            'anchor_id' => '#the-list tr:first-child a.row-title',
            'edge'      => 'left',
            'align'     => 'left',
            'active'    => !in_array( $prefix . 'is_pointers', $dismissed ),
        ),
        );
    }
    
    /**
     * Adds a link to the settings page in the plugins list.
     *
     * @param array  $links array of links for the plugins, adapted when the current plugin is found.
     * @param string $file  the filename for the current plugin, which the filter loops through.
     *
     * @return array $links
     */
    function plugin_action_links( $links, $file )
    {
        
        if ( IS_PLUGIN_BASE === $file ) {
            $mylinks = array( '<a href="' . esc_url( menu_page_url( 'ivory-search', false ) ) . '">' . esc_html__( 'Settings', 'add-search-to-menu' ) . '</a>' );
            $links = array_merge( $mylinks, $links );
        }
        
        return $links;
    }
    
    /**
     * Show row meta on the plugin screen.
     *
     * @param mixed $links Plugin Row Meta.
     * @param mixed $file  Plugin Base file.
     *
     * @return array
     */
    function plugin_row_meta( $links, $file )
    {
        
        if ( IS_PLUGIN_BASE === $file ) {
            $row_meta = array(
                'docs'    => '<a href="https://ivorysearch.com/documentation/" aria-label="' . esc_attr__( 'View Ivory Search documentation', 'add-search-to-menu' ) . '">' . esc_html__( 'Docs', 'add-search-to-menu' ) . '</a>',
                'support' => '<a href="https://ivorysearch.com/support" aria-label="' . esc_attr__( 'Visit plugin customer support', 'add-search-to-menu' ) . '">' . esc_html__( 'Support', 'add-search-to-menu' ) . '</a>',
            );
            return array_merge( $links, $row_meta );
        }
        
        return (array) $links;
    }
    
    /**
     * Change the admin footer text on Ivory Search admin pages.
     */
    public function admin_footer_text( $footer_text )
    {
        $screen = get_current_screen();
        $is_ivory = strpos( $screen->id, 'ivory-search' );
        // Check to make sure we're on a Ivory Search admin page.
        if ( FALSE !== $is_ivory ) {
            // Change the footer text
            $footer_text = sprintf( __( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'add-search-to-menu' ), sprintf( '<strong>%s</strong>', esc_html__( 'Ivory Search', 'add-search-to-menu' ) ), '<a href="https://wordpress.org/support/plugin/add-search-to-menu/reviews?rate=5#new-post" target="_blank" class="is-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'add-search-to-menu' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' );
        }
        return $footer_text;
    }
    
    /**
     * Displays plugin configuration notice in admin area.
     */
    function all_admin_notices()
    {
        $hascaps = ( $this->networkactive ? is_network_admin() && current_user_can( 'manage_network_plugins' ) : current_user_can( 'manage_options' ) );
        
        if ( $hascaps ) {
            $screen = get_current_screen();
            $is_ivory = strpos( $screen->id, 'ivory-search' );
            $display_review = true;
            //Don't display if dismissed
            if ( isset( $this->opt['is_notices']['review'] ) && $this->opt['is_notices']['review'] || isset( $_GET['is_dismiss'] ) && 'notice_review' == $_GET['is_dismiss'] ) {
                $display_review = false;
            }
            //Don't display on seoncary screens, don't be too nagging
            if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' || $screen->action == 'add' || $screen->base == 'plugins' || $screen->base == 'widgets' ) {
                $display_review = false;
            }
            $date = get_option( 'is_install', false );
            if ( $date && $display_review ) {
                
                if ( strtotime( '-7 days' ) >= strtotime( $date ) ) {
                    global  $current_user ;
                    echo  '<div class="is-notice notice"><div class="is-notice-image"></div><div class="is-notice-body">' ;
                    echo  '<a class="is-notice-dismiss" href="' . add_query_arg( 'is_dismiss', 'notice_review' ) . '">' . esc_html__( 'Dismiss', 'add-search-to-menu' ) . '</a>' ;
                    echo  '<div class="is-notice-content">' ;
                    printf( __( "Hey %s, it's Vinod Dalvi from %s. You have used this free plugin for some time now, and I hope you like it!", 'add-search-to-menu' ), '<strong>' . $current_user->display_name . '</strong>', '<strong>Ivory Search</strong>' );
                    ?><br/><br/><?php 
                    printf( __( "I have spent countless hours developing it, and it would mean a lot to me if you %ssupport it with a quick review on WordPress.org.%s", 'add-search-to-menu' ), '<strong><a target="_blank" href="https://wordpress.org/support/plugin/add-search-to-menu/reviews/?filter=5">', '</a></strong>' );
                    echo  '</div>' ;
                    echo  '<div class="is-notice-links">' ;
                    echo  '<a href="' . esc_url( 'https://wordpress.org/support/plugin/add-search-to-menu/reviews/?filter=5' ) . '" class="button button-primary btn-highlight" target="_blank" >' . esc_html__( 'Review Ivory Search', 'add-search-to-menu' ) . '</a>' ;
                    echo  '<a href="' . add_query_arg( 'is_dismiss', 'notice_review' ) . '" class="button button-primary">' . esc_html__( 'No, thanks', 'add-search-to-menu' ) . '</a>' ;
                    echo  '</div></div></div>' ;
                }
            
            }
        }
    
    }
    
    /**
     * Displays posts in the admin plugin options list using AJAX.
     */
    function display_posts()
    {
        $posts = get_posts( array(
            'post_type'      => $_REQUEST['post_type'],
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );
        
        if ( !empty($posts) ) {
            $temp = '';
            $meta = get_post_meta( $_REQUEST['post_id'] );
            
            if ( 'includes' === $_REQUEST['inc_exc'] && isset( $meta['_is_includes'] ) ) {
                $meta = maybe_unserialize( $meta['_is_includes'][0] );
            } else {
                if ( 'excludes' === $_REQUEST['inc_exc'] && isset( $meta['_is_excludes'] ) ) {
                    $meta = maybe_unserialize( $meta['_is_excludes'][0] );
                }
            }
            
            foreach ( $posts as $post2 ) {
                $checked = '';
                
                if ( 'includes' === $_REQUEST['inc_exc'] ) {
                    $checked = ( isset( $meta['post__in'] ) && in_array( $post2->ID, $meta['post__in'] ) ? $post2->ID : 0 );
                } else {
                    if ( 'excludes' === $_REQUEST['inc_exc'] ) {
                        $checked = ( isset( $meta['post__not_in'] ) && in_array( $post2->ID, $meta['post__not_in'] ) ? $post2->ID : 0 );
                    }
                }
                
                $post_title = ( isset( $post2->post_title ) && '' !== $post2->post_title ? esc_html( $post2->post_title ) : $post2->post_name );
                $temp .= '<option value="' . esc_attr( $post2->ID ) . '" ' . selected( $post2->ID, $checked, false ) . '>' . $post_title . '</option>';
            }
            echo  $temp ;
        } else {
            _e( 'No posts found', 'add-search-to-menu' );
        }
        
        die;
    }
    
    /**
     * Adds scripts in the admin footer
     */
    function admin_footer()
    {
        ?>
		<style type="text/css">
		/* ADMIN NOTICES */
		.is-notice { margin:20px 0; padding:0; overflow:hidden; background:#FFF;}
		.is-notice br {clear: none;}
		.is-notice-dismiss { display:block; float:right; color:#999; line-height:1; margin:0 0 0 15px; text-decoration:none; }
		.is-notice-image { float:left; margin:10px; width:90px; height:90px; background:url(<?php 
        echo  esc_url( plugins_url( 'assets/logo.png', __FILE__ ) ) ;
        ?>) no-repeat center; background-size:cover; }
		.is-notice-body { padding:15px; background:#fff; }
		.is-notice-content { margin:0 0 10px; padding:0; }
		.is-notice-links a.button { margin-right: 10px;text-decoration: none;background: #e7f2f7 !important;color: #30667b !important;box-shadow: none;text-shadow: none;}
		.is-notice-links a.btn-highlight {background: #0071a1 !important;color: #FFF !important;}
		</style>
	<?php 
    }
    
    /**
     * Registers plugin settings.
     */
    function admin_init()
    {
        /* Added to fix header already sent issue */
        ob_start();
        
        if ( isset( $_GET['is_dismiss'] ) && '' !== $_GET['is_dismiss'] ) {
            $is_notices = get_option( 'is_notices', array() );
            if ( 'notice_review' === $_GET['is_dismiss'] ) {
                $is_notices['is_notices']['review'] = 1;
            }
            update_option( 'is_notices', $is_notices );
            wp_redirect( remove_query_arg( 'is_dismiss' ) );
        }
        
        
        if ( empty($GLOBALS['pagenow']) || 'plugins.php' != $GLOBALS['pagenow'] ) {
            if ( !get_option( 'is_install', false ) ) {
                update_option( 'is_install', date( 'Y-m-d' ) );
            }
            
            if ( !empty($GLOBALS['pagenow']) && ('admin.php' === $GLOBALS['pagenow'] || 'options.php' === $GLOBALS['pagenow']) ) {
                $settings_fields = new IS_Settings_Fields();
                $settings_fields->register_settings_fields();
            }
            
            /* Creates default search form */
            $search_form = get_page_by_path( 'default-search-form', OBJECT, IS_Search_Form::post_type );
            
            if ( NULL == $search_form ) {
                $args['id'] = -1;
                $args['title'] = 'Default Search Form';
                $args['_is_locale'] = 'en_US';
                $args['_is_includes'] = '';
                $args['_is_excludes'] = '';
                $args['_is_settings'] = '';
                $this->save_form( $args );
            }
        
        }
    
    }
    
    /**
     * Maps custom capabilities.
     */
    function map_meta_cap(
        $caps,
        $cap,
        $user_id,
        $args
    )
    {
        $meta_caps = array(
            'is_edit_search_form'   => IS_ADMIN_READ_WRITE_CAPABILITY,
            'is_edit_search_forms'  => IS_ADMIN_READ_WRITE_CAPABILITY,
            'is_read_search_forms'  => IS_ADMIN_READ_CAPABILITY,
            'is_delete_search_form' => IS_ADMIN_READ_WRITE_CAPABILITY,
        );
        $meta_caps = apply_filters( 'is_map_meta_cap', $meta_caps );
        $caps = array_diff( $caps, array_keys( $meta_caps ) );
        if ( isset( $meta_caps[$cap] ) ) {
            $caps[] = $meta_caps[$cap];
        }
        return $caps;
    }
    
    /**
     * Displays admin messages on updating search form
     */
    function admin_updated_message()
    {
        if ( empty($_REQUEST['message']) ) {
            return;
        }
        
        if ( 'created' == $_REQUEST['message'] ) {
            $updated_message = __( "Search form created.", 'add-search-to-menu' );
        } elseif ( 'saved' == $_REQUEST['message'] ) {
            $updated_message = __( "Search form saved.", 'add-search-to-menu' );
        } elseif ( 'deleted' == $_REQUEST['message'] ) {
            $updated_message = __( "Search form deleted.", 'add-search-to-menu' );
        } elseif ( 'reset' == $_REQUEST['message'] ) {
            $updated_message = __( "Search form reset.", 'add-search-to-menu' );
        }
        
        
        if ( !empty($updated_message) ) {
            echo  sprintf( '<div id="message" class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) ) ;
            return;
        }
        
        
        if ( 'failed' == $_REQUEST['message'] ) {
            $updated_message = __( "There was an error saving the search form.", 'add-search-to-menu' );
            echo  sprintf( '<div id="message" class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) ) ;
            return;
        }
        
        
        if ( 'invalid' == $_REQUEST['message'] ) {
            $updated_message = __( "Validation error occurred.", 'add-search-to-menu' );
            $includes = __( "Includes", 'add-search-to-menu' );
            $excludes = __( "Excludes", 'add-search-to-menu' );
            
            if ( isset( $_REQUEST['tab'] ) ) {
                $url = esc_url( menu_page_url( 'ivory-search', false ) );
                if ( isset( $_REQUEST['post'] ) ) {
                    $url .= '&post=' . esc_html( $_REQUEST['post'] ) . '&action=edit';
                }
                
                if ( 'excludes' == $_REQUEST['tab'] ) {
                    $includes = '<a href="' . $url . '&tab=includes">' . __( "Includes", 'add-search-to-menu' ) . '</a>';
                } else {
                    if ( 'includes' == $_REQUEST['tab'] ) {
                        $excludes = '<a href="' . $url . '&tab=excludes">' . __( "Excludes", 'add-search-to-menu' ) . '</a>';
                    }
                }
            
            }
            
            $temp_mes = ( isset( $_REQUEST['data'] ) ? esc_html( $_REQUEST['data'] ) : '' );
            $updated_message2 = sprintf(
                __( "Please make sure you have not selected similar %s fields in the search form %s and %s sections.", 'add-search-to-menu' ),
                $temp_mes,
                $includes,
                $excludes
            );
            echo  sprintf( '<div id="message" class="notice notice-error is-dismissible"><p>%s</p><p>%s</p></div>', esc_html( $updated_message ), $updated_message2 ) ;
            return;
        }
    
    }
    
    /**
     * Registers plugin admin menu item.
     */
    function admin_menu()
    {
        add_menu_page(
            __( 'Ivory Search', 'add-search-to-menu' ),
            __( 'Ivory Search', 'add-search-to-menu' ),
            'manage_options',
            'ivory-search',
            array( $this, 'search_forms_page' ),
            'dashicons-search',
            '35.6282'
        );
        $edit = add_submenu_page(
            'ivory-search',
            __( 'Search Forms', 'add-search-to-menu' ),
            __( 'Search Forms', 'add-search-to-menu' ),
            'manage_options',
            'ivory-search',
            array( $this, 'search_forms_page' )
        );
        add_action( 'load-' . $edit, array( $this, 'load_admin_search_form' ) );
        $addnew = '';
        
        if ( isset( $_GET['page'] ) && 'ivory-search-new' == $_GET['page'] ) {
            $addnew = add_submenu_page(
                'ivory-search',
                __( 'Add New Search Form', 'add-search-to-menu' ),
                __( 'Add New', 'add-search-to-menu' ),
                'manage_options',
                'ivory-search-new',
                array( $this, 'new_search_form_page' )
            );
        } else {
            $addnew = add_submenu_page(
                '',
                __( 'Add New Search Form', 'add-search-to-menu' ),
                __( 'Add New', 'add-search-to-menu' ),
                'manage_options',
                'ivory-search-new',
                array( $this, 'new_search_form_page' )
            );
        }
        
        add_action( 'load-' . $addnew, array( $this, 'load_admin_search_form' ) );
        $settings = add_submenu_page(
            'ivory-search',
            __( 'Menu Search', 'add-search-to-menu' ),
            __( 'Menu Search', 'add-search-to-menu' ),
            'manage_options',
            'ivory-search-settings&tab=menu-search',
            array( $this, 'settings_page' )
        );
        add_action( 'load-' . $settings, array( $this, 'is_settings_add_help_tab' ) );
        $settings = add_submenu_page(
            'ivory-search',
            __( 'Ivory Search Settings', 'add-search-to-menu' ),
            __( 'Settings', 'add-search-to-menu' ),
            'manage_options',
            'ivory-search-settings',
            array( $this, 'settings_page' )
        );
        add_action( 'load-' . $settings, array( $this, 'is_settings_add_help_tab' ) );
    }
    
    /**
     * Adds help tab to settings page screen.
     */
    function is_settings_add_help_tab()
    {
        $current_screen = get_current_screen();
        $help_tabs = new IS_Help( $current_screen );
        $help_tabs->set_help_tabs( 'settings' );
    }
    
    /**
     * Renders the search forms page for this plugin.
     */
    function search_forms_page()
    {
        /* Edits search form */
        
        if ( $post = IS_Search_Form::get_current() ) {
            $post_id = ( $post->initial() ? -1 : $post->id() );
            include_once 'partials/search-form.php';
            return;
        }
        
        $list_table = new IS_List_Table();
        $list_table->prepare_items();
        ?>
	<div class="wrap">

		<h1 class="wp-heading-inline">
			<?php 
        echo  esc_html( __( 'Search Forms', 'add-search-to-menu' ) ) ;
        ?>
		</h1>

		<?php 
        if ( current_user_can( 'is_edit_search_forms' ) ) {
            echo  sprintf( '<a href="%1$s" class="add-new-h2">%2$s</a>', esc_url( menu_page_url( 'ivory-search-new', false ) ), esc_html( __( 'Add New Search Form', 'add-search-to-menu' ) ) ) ;
        }
        if ( !empty($_REQUEST['s']) ) {
            echo  sprintf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;', 'add-search-to-menu' ) . '</span>', esc_html( $_REQUEST['s'] ) ) ;
        }
        ?>

		<hr class="wp-header-end" />

		<?php 
        do_action( 'is_admin_notices' );
        ?>

		<form method="get" action="">
			<input type="hidden" name="page" value="<?php 
        echo  esc_attr( $_REQUEST['page'] ) ;
        ?>" />
			<?php 
        $list_table->search_box( __( 'Find Search Forms', 'add-search-to-menu' ), 'is-search' );
        ?>
			<?php 
        $list_table->display();
        ?>
		</form>

	</div>
	<?php 
    }
    
    /**
     * Renders the add new search form page for this plugin.
     */
    function new_search_form_page()
    {
        $post = IS_Search_Form::get_current();
        if ( !$post ) {
            $post = IS_Search_Form::get_template();
        }
        $post_id = -1;
        include_once 'partials/search-form.php';
    }
    
    /**
     * Renders the settings page for this plugin.
     */
    function settings_page()
    {
        include_once 'partials/settings-form.php';
    }
    
    /**
     * Performs various search forms operations.
     */
    function load_admin_search_form()
    {
        global  $plugin_page ;
        $action = ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ? $_REQUEST['action'] : false );
        
        if ( 'save' == $action ) {
            $id = ( isset( $_POST['post_ID'] ) ? $_POST['post_ID'] : '-1' );
            check_admin_referer( 'is-save-search-form_' . $id );
            if ( !current_user_can( 'is_edit_search_form', $id ) ) {
                wp_die( __( 'You are not allowed to edit this item.', 'add-search-to-menu' ) );
            }
            $args = $_REQUEST;
            $args['id'] = $id;
            $args['title'] = ( isset( $_POST['post_title'] ) ? $_POST['post_title'] : null );
            $args['title'] = ( null != $args['title'] && 'default search form' == strtolower( $args['title'] ) ? $args['title'] . ' ( Duplicate )' : $args['title'] );
            $args['_is_locale'] = ( isset( $_POST['is_locale'] ) ? $_POST['is_locale'] : null );
            $args['_is_includes'] = ( isset( $_POST['_is_includes'] ) ? $_POST['_is_includes'] : '' );
            $args['_is_excludes'] = ( isset( $_POST['_is_excludes'] ) ? $_POST['_is_excludes'] : '' );
            $args['_is_ajax'] = ( isset( $_POST['_is_ajax'] ) ? $_POST['_is_ajax'] : '' );
            $args['_is_customize'] = ( isset( $_POST['_is_customize'] ) ? $_POST['_is_customize'] : '' );
            $args['_is_settings'] = ( isset( $_POST['_is_settings'] ) ? $_POST['_is_settings'] : '' );
            $args['tab'] = ( isset( $_POST['tab'] ) ? $_POST['tab'] : 'includes' );
            $properties = array();
            
            if ( '-1' != $id ) {
                $search_form = IS_Search_Form::get_instance( $id );
                $properties = $search_form->get_properties();
            }
            
            
            if ( 'includes' === $args['tab'] && !empty($properties['_is_excludes']) ) {
                $args['_is_excludes'] = $properties['_is_excludes'];
            } else {
                if ( 'excludes' === $args['tab'] && !empty($properties['_is_includes']) ) {
                    $args['_is_includes'] = $properties['_is_includes'];
                }
            }
            
            $invalid = false;
            if ( !empty($args['_is_includes']) && !empty($args['_is_excludes']) ) {
                foreach ( $args['_is_includes'] as $key => $value ) {
                    if ( $invalid ) {
                        break;
                    }
                    if ( 'woo' === $key ) {
                        continue;
                    }
                    if ( isset( $args['_is_excludes'][$key] ) && !empty($args['_is_excludes'][$key]) ) {
                        if ( is_array( $value ) && is_array( $args['_is_excludes'][$key] ) ) {
                            foreach ( $value as $key2 => $val ) {
                                if ( $invalid ) {
                                    break;
                                }
                                
                                if ( is_array( $val ) && isset( $args['_is_excludes'][$key][$key2] ) && is_array( $args['_is_excludes'][$key][$key2] ) ) {
                                    $similar = array_intersect( $val, $args['_is_excludes'][$key][$key2] );
                                    if ( !empty($similar) ) {
                                        $invalid = $key;
                                    }
                                } else {
                                    if ( in_array( $val, $args['_is_excludes'][$key] ) ) {
                                        $invalid = $key;
                                    }
                                }
                            
                            }
                        }
                    }
                }
            }
            $query = '';
            
            if ( $invalid ) {
                $query = array(
                    'post' => $id,
                    'tab'  => ( isset( $_POST['tab'] ) ? $_POST['tab'] : 'includes' ),
                );
                $query['message'] = 'invalid';
                $query['data'] = $invalid;
            } else {
                $search_form = $this->save_form( $args );
                $query = array(
                    'post' => ( $search_form ? $search_form->id() : 0 ),
                    'tab'  => ( isset( $_POST['tab'] ) ? $_POST['tab'] : 'includes' ),
                );
                
                if ( !$search_form ) {
                    $query['message'] = 'failed';
                } elseif ( -1 == $id ) {
                    $query['message'] = 'created';
                } else {
                    $query['message'] = 'saved';
                }
            
            }
            
            $redirect_to = add_query_arg( $query, menu_page_url( 'ivory-search', false ) );
            wp_safe_redirect( $redirect_to );
            exit;
        }
        
        
        if ( 'reset' == $action ) {
            $id = ( empty($_POST['post_ID']) ? absint( $_REQUEST['post'] ) : absint( $_POST['post_ID'] ) );
            check_admin_referer( 'is-reset-search-form_' . $id );
            if ( !current_user_can( 'is_edit_search_form', $id ) ) {
                wp_die( __( 'You are not allowed to reset this item.', 'add-search-to-menu' ) );
            }
            $query = array();
            
            if ( $id ) {
                $args['id'] = $id;
                $args['title'] = ( isset( $_POST['post_title'] ) ? $_POST['post_title'] : null );
                $args['_is_locale'] = null;
                $args['_is_includes'] = '';
                $args['_is_excludes'] = '';
                $args['_is_ajax'] = '';
                $args['_is_customize'] = '';
                $args['_is_settings'] = '';
                $search_form = $this->save_form( $args );
                $query['post'] = $id;
                $query['tab'] = ( isset( $_POST['tab'] ) ? $_POST['tab'] : 'includes' );
                $query['message'] = 'reset';
            }
            
            $redirect_to = add_query_arg( $query, menu_page_url( 'ivory-search', false ) );
            wp_safe_redirect( $redirect_to );
            exit;
        }
        
        
        if ( 'copy' == $action ) {
            $id = ( empty($_POST['post_ID']) ? absint( $_REQUEST['post'] ) : absint( $_POST['post_ID'] ) );
            check_admin_referer( 'is-copy-search-form_' . $id );
            if ( !current_user_can( 'is_edit_search_form', $id ) ) {
                wp_die( __( 'You are not allowed to copy this item.', 'add-search-to-menu' ) );
            }
            $query = array();
            
            if ( $search_form = IS_Search_Form::get_instance( $id ) ) {
                $new_search_form = $search_form->copy();
                $new_search_form->save();
                $query['post'] = $new_search_form->id();
                $query['message'] = 'created';
            }
            
            $redirect_to = add_query_arg( $query, menu_page_url( 'ivory-search', false ) );
            wp_safe_redirect( $redirect_to );
            exit;
        }
        
        
        if ( 'delete' == $action ) {
            
            if ( !empty($_POST['post_ID']) ) {
                check_admin_referer( 'is-delete-search-form_' . $_POST['post_ID'] );
            } else {
                
                if ( !is_array( $_REQUEST['post'] ) ) {
                    check_admin_referer( 'is-delete-search-form_' . $_REQUEST['post'] );
                } else {
                    check_admin_referer( 'bulk-posts' );
                }
            
            }
            
            $posts = ( empty($_POST['post_ID']) ? (array) $_REQUEST['post'] : (array) $_POST['post_ID'] );
            $deleted = 0;
            foreach ( $posts as $post ) {
                $post = IS_Search_Form::get_instance( $post );
                if ( empty($post) ) {
                    continue;
                }
                if ( !current_user_can( 'is_delete_search_form', $post->id() ) ) {
                    wp_die( __( 'You are not allowed to delete this item.', 'add-search-to-menu' ) );
                }
                if ( !$post->delete() ) {
                    wp_die( __( 'Error in deleting.', 'add-search-to-menu' ) );
                }
                $deleted += 1;
            }
            $query = array();
            if ( $deleted ) {
                $query['message'] = 'deleted';
            }
            $redirect_to = add_query_arg( $query, menu_page_url( 'ivory-search', false ) );
            wp_safe_redirect( $redirect_to );
            exit;
        }
        
        $_GET['post'] = ( isset( $_GET['post'] ) ? $_GET['post'] : '' );
        $post = null;
        
        if ( 'ivory-search-new' == $plugin_page ) {
            $post = IS_Search_Form::get_template( array(
                'locale' => ( isset( $_GET['locale'] ) ? $_GET['locale'] : null ),
            ) );
        } elseif ( !empty($_GET['post']) ) {
            $post = IS_Search_Form::get_instance( $_GET['post'] );
        }
        
        $current_screen = get_current_screen();
        $help_tabs = new IS_Help( $current_screen );
        
        if ( $post && current_user_can( 'is_edit_search_form', $post->id() ) ) {
            $help_tabs->set_help_tabs( 'edit' );
        } else {
            $help_tabs->set_help_tabs( 'list' );
            add_filter( 'manage_' . $current_screen->id . '_columns', array( 'IS_List_Table', 'define_columns' ) );
            add_screen_option( 'per_page', array(
                'default' => 20,
                'option'  => 'is_search_forms_per_page',
            ) );
        }
    
    }
    
    /**
     * Saves search form.
     */
    function save_form( $args = '', $context = 'save' )
    {
        $args = wp_parse_args( $args, array(
            'id'            => -1,
            'title'         => null,
            '_is_locale'    => null,
            '_is_includes'  => null,
            '_is_excludes'  => null,
            '_is_ajax'      => null,
            '_is_customize' => null,
            '_is_settings'  => null,
            'tab'           => null,
        ) );
        $args['id'] = (int) $args['id'];
        $search_form = '';
        
        if ( -1 == $args['id'] ) {
            $search_form = IS_Search_Form::get_template();
        } else {
            $search_form = IS_Search_Form::get_instance( $args['id'] );
        }
        
        if ( empty($search_form) ) {
            return false;
        }
        if ( null !== $args['title'] ) {
            $search_form->set_title( $args['title'] );
        }
        if ( null !== $args['_is_locale'] ) {
            $search_form->set_locale( $args['_is_locale'] );
        }
        $properties = $search_form->get_properties();
        
        if ( null === $args['tab'] || 'includes' === $args['tab'] ) {
            
            if ( '' == $args['_is_includes'] ) {
                $post_types = get_post_types( array(
                    'public'              => true,
                    'exclude_from_search' => false,
                ) );
                if ( 'Default Search Form' === $args['title'] && is_array( $post_types ) && in_array( 'attachment', $post_types ) ) {
                    unset( $post_types['attachment'] );
                }
                $args['_is_includes'] = array(
                    'post_type'      => $post_types,
                    'search_title'   => 1,
                    'search_content' => 1,
                    'search_excerpt' => 1,
                    'post_status'    => array(
                    'publish' => 'publish',
                    'inherit' => 'inherit',
                ),
                );
            }
            
            $properties['_is_includes'] = $this->sanitize_includes( $args['_is_includes'] );
        }
        
        if ( null === $args['tab'] || 'excludes' === $args['tab'] ) {
            $properties['_is_excludes'] = $this->sanitize_excludes( $args['_is_excludes'] );
        }
        
        if ( null === $args['tab'] || 'options' === $args['tab'] ) {
            if ( '' == $args['_is_settings'] ) {
                $args['_is_settings'] = array(
                    'orderby' => 'date',
                    'order'   => 'DESC',
                );
            }
            $properties['_is_settings'] = $this->sanitize_settings( $args['_is_settings'] );
        }
        
        if ( null === $args['tab'] || 'ajax' === $args['tab'] ) {
            $properties['_is_ajax'] = $this->sanitize_settings( $args['_is_ajax'] );
        }
        if ( null === $args['tab'] || 'customize' === $args['tab'] ) {
            $properties['_is_customize'] = $this->sanitize_settings( $args['_is_customize'] );
        }
        $search_form->set_properties( $properties );
        do_action(
            'is_before_save_form',
            $search_form,
            $args,
            $context
        );
        if ( 'save' == $context ) {
            $search_form->save();
        }
        do_action(
            'is_after_save_form',
            $search_form,
            $args,
            $context
        );
        return $search_form;
    }
    
    /**
     * Sanitizes includes settings.
     */
    function sanitize_includes( $input, $defaults = array() )
    {
        if ( null === $input ) {
            return $defaults;
        }
        $defaults = wp_parse_args( $defaults, array(
            'post_type' => get_post_types( array(
            'public' => true,
        ) ),
        ) );
        $input = wp_parse_args( $input, $defaults );
        $output = $this->sanitize_fields( $input );
        return $output;
    }
    
    /**
     * Sanitizes excludes settings.
     */
    function sanitize_excludes( $input, $defaults = '' )
    {
        if ( null === $input ) {
            return $defaults;
        }
        $output = $this->sanitize_fields( $input );
        return $output;
    }
    
    /**
     * Sanitizes settings options.
     */
    function sanitize_settings( $input, $defaults = '' )
    {
        if ( null === $input ) {
            return $defaults;
        }
        $output = $this->sanitize_fields( $input );
        return $output;
    }
    
    /**
     * Sanitizes fields.
     */
    function sanitize_fields( $input )
    {
        $output = array();
        if ( is_array( $input ) && !empty($input) ) {
            foreach ( $input as $key => $value ) {
                
                if ( is_array( $value ) ) {
                    foreach ( $value as $key2 => $value2 ) {
                        
                        if ( is_array( $value2 ) ) {
                            foreach ( $value2 as $key3 => $value3 ) {
                                $output[$key][$key2][$key3] = esc_attr( $input[$key][$key2][$key3] );
                            }
                        } else {
                            $output[$key][$key2] = esc_attr( $input[$key][$key2] );
                        }
                    
                    }
                } else {
                    $output[$key] = esc_attr( $input[$key] );
                }
            
            }
        }
        return $output;
    }
    
    /**
     * Displays search form save button.
     */
    function save_button( $post_id )
    {
        static  $button = '' ;
        
        if ( !empty($button) ) {
            echo  $button ;
            return;
        }
        
        $nonce = wp_create_nonce( 'is-save-search-form_' . $post_id );
        $onclick = sprintf( "this.form._wpnonce.value = '%s';" . " this.form.action.value = 'save';" . " return true;", $nonce );
        $button = sprintf( '<input type="submit" class="button-primary" name="is_save" value="%1$s" onclick="%2$s" />', esc_attr( __( 'Save Form', 'add-search-to-menu' ) ), $onclick );
        echo  $button ;
    }
    
    /**
     * Returns premium plugin version link.
     */
    public static function pro_link( $plan = 'pro' )
    {
        $is_premium_plugin = false;
        $msg = esc_html__( "Upgrade to Pro to Access", 'add-search-to-menu' );
        
        if ( is_fs()->is_plan_or_trial( $plan ) ) {
            $msg = esc_html__( "Install Premium Version to Access", 'add-search-to-menu' );
        } else {
            if ( 'pro_plus' === $plan ) {
                $msg = esc_html__( "Upgrade to Pro Plus to Access", 'add-search-to-menu' );
            }
        }
        
        
        if ( is_fs()->is_plan_or_trial( $plan ) && $is_premium_plugin ) {
            return '';
        } else {
            return '<span class="upgrade-wrapper"><a class="upgrade-link" href="' . esc_url( menu_page_url( 'ivory-search-pricing', false ) ) . '">  ' . $msg . '</a></span>';
        }
    
    }

}