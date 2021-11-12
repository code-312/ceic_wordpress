<?php

/**
 * This class defines all plugin functionality for the site front end.
 *
 * @package IS
 * @since    1.0.0
 */
class IS_Public
{
    /**
     * Stores plugin options.
     */
    public  $opt ;
    /**
     * Core singleton class
     * @var self
     */
    private static  $_instance ;
    /**
     * Initializes this class and stores the plugin options.
     */
    public function __construct()
    {
        $this->opt = Ivory_Search::load_options();
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
     * Enqueues search menu style files.
     */
    function wp_enqueue_styles()
    {
        global  $wp_query ;
        $min = ( defined( 'IS_DEBUG' ) && IS_DEBUG ? '' : '.min' );
        if ( !isset( $this->opt['not_load_files']['css'] ) ) {
            wp_enqueue_style(
                'ivory-search-styles',
                plugins_url( '/public/css/ivory-search' . $min . '.css', IS_PLUGIN_FILE ),
                array(),
                IS_VERSION
            );
        }
    }
    
    /**
     * Enqueues search menu script files.
     */
    function wp_enqueue_scripts()
    {
        global  $wp_query ;
        $min = ( defined( 'IS_DEBUG' ) && IS_DEBUG ? '' : '.min' );
        
        if ( !isset( $this->opt['not_load_files']['js'] ) ) {
            wp_enqueue_script(
                'ivory-search-scripts',
                plugins_url( '/public/js/ivory-search' . $min . '.js', IS_PLUGIN_FILE ),
                array( 'jquery' ),
                IS_VERSION,
                true
            );
            $is_analytics = get_option( 'is_analytics', array() );
            $analytics_disabled = ( isset( $is_analytics['disable_analytics'] ) ? $is_analytics['disable_analytics'] : 0 );
            
            if ( !$analytics_disabled ) {
                $is_temp = array(
                    'is_analytics_enabled' => 1,
                );
                
                if ( is_search() ) {
                    if ( isset( $_GET['id'] ) ) {
                        $is_temp['is_id'] = $_GET['id'];
                    }
                    if ( isset( $_GET['s'] ) ) {
                        $is_temp['is_label'] = $_GET['s'];
                    }
                    
                    if ( 0 == $wp_query->found_posts ) {
                        $is_temp['is_cat'] = 'Nothing Found';
                    } else {
                        $is_temp['is_cat'] = 'Results Found';
                    }
                
                }
                
                wp_localize_script( 'ivory-search-scripts', 'IvorySearchVars', $is_temp );
            }
            
            wp_register_script(
                'ivory-ajax-search-scripts',
                plugins_url( '/public/js/ivory-ajax-search' . $min . '.js', IS_PLUGIN_FILE ),
                array( 'jquery' ),
                IS_VERSION,
                true
            );
            wp_localize_script( 'ivory-ajax-search-scripts', 'IvoryAjaxVars', array(
                'ajaxurl'    => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => wp_create_nonce( 'is_ajax_nonce' ),
            ) );
            wp_register_script(
                'is-highlight',
                plugins_url( '/public/js/is-highlight' . $min . '.js', IS_PLUGIN_FILE ),
                array( 'jquery' ),
                IS_VERSION,
                true
            );
            if ( is_search() && isset( $wp_query->query_vars['_is_settings']['highlight_terms'] ) && 0 !== $wp_query->found_posts ) {
                wp_enqueue_script( 'is-highlight' );
            }
        }
        
        
        if ( is_search() && isset( $wp_query->query_vars['_is_settings']['highlight_terms'] ) && 0 !== $wp_query->found_posts ) {
            $areas = array(
                '#groups-dir-list',
                '#members-dir-list',
                // BuddyPress compat
                'div.bbp-topic-content,div.bbp-reply-content,li.bbp-forum-info,.bbp-topic-title,.bbp-reply-title',
                // bbPress compat
                'article',
                'div.hentry',
                'div.post',
                '#content',
                '#main',
                'div.content',
                '#middle',
                '#container',
                'div.container',
                'div.page',
                '#wrapper',
                'body',
            );
            $script = 'var is_terms = ';
            $script .= ( isset( $wp_query->query_vars['search_terms'] ) ? wp_json_encode( (array) array_map( 'esc_html', $wp_query->query_vars['search_terms'] ) ) : '[]' );
            $script .= '; var is_areas = ' . wp_json_encode( (array) $areas ) . ';';
            wp_add_inline_script( 'is-highlight', $script, 'before' );
        }
    
    }
    
    /**
     * Add classes to body element.
     */
    function is_body_classes( $classes )
    {
        $classes[] = get_template();
        return $classes;
    }
    
    /**
     * Displays menu search form.
     * 
     * @since 4.0
     *
     * @param bool $echo Default to echo and not return the form.
     * @return string|void String when $echo is false.
     */
    function get_menu_search_form( $echo = true )
    {
        $result = '';
        $search_form = false;
        $menu_search_form = ( isset( $this->opt['menu_search_form'] ) ? $this->opt['menu_search_form'] : 0 );
        if ( $menu_search_form ) {
            $search_form = IS_Search_Form::get_instance( $menu_search_form );
        }
        
        if ( !$menu_search_form || !$search_form ) {
            $page = get_page_by_path( 'default-search-form', OBJECT, 'is_search_form' );
            if ( !empty($page) ) {
                $search_form = IS_Search_Form::get_instance( $page->ID );
            }
        }
        
        
        if ( $search_form ) {
            $atts['id'] = $menu_search_form;
            $display_id = '';
            if ( 0 === $menu_search_form || 'default-search-form' === $search_form->name() ) {
                $display_id = 'n';
            }
            $result = $search_form->form_html( $atts, $display_id );
        }
        
        
        if ( $echo ) {
            echo  $result ;
        } else {
            return $result;
        }
    
    }
    
    /**
     * Displays search form in the navigation bar in the front end of site.
     */
    function wp_nav_menu_items( $items, $args )
    {
        $menu_name = '';
        
        if ( is_object( $args->menu ) ) {
            $menu_name = $args->menu->slug;
        } else {
            if ( is_string( $args->menu ) ) {
                $menu_name = $args->menu;
            }
        }
        
        
        if ( isset( $this->opt['menus'] ) && isset( $this->opt['menus'][$args->theme_location] ) || isset( $this->opt['menu_name'] ) && isset( $this->opt['menu_name'][$menu_name] ) ) {
            $temp = '';
            
            if ( isset( $this->opt['menu_gcse'] ) && '' != $this->opt['menu_gcse'] ) {
                $temp .= '<li class="gsc-cse-search-menu">' . $this->opt['menu_gcse'] . '</li>';
            } else {
                $search_class = ( isset( $this->opt['menu_classes'] ) ? $this->opt['menu_classes'] . ' astm-search-menu is-menu ' : ' astm-search-menu is-menu ' );
                $search_class .= ( isset( $this->opt['menu_style'] ) && 'dropdown' != $this->opt['menu_style'] ? $this->opt['menu_style'] : 'is-dropdown' );
                $search_class .= ( isset( $this->opt['first_menu_item'] ) && $this->opt['first_menu_item'] ? ' is-first' : '' );
                $title = ( isset( $this->opt['menu_title'] ) ? $this->opt['menu_title'] : '' );
                $temp .= '<li class="' . esc_attr( $search_class ) . ' menu-item">';
                
                if ( !isset( $this->opt['menu_style'] ) || $this->opt['menu_style'] != 'default' ) {
                    
                    if ( '' !== $title ) {
                        $link_title = ( apply_filters( 'is_show_menu_link_title', true ) ? 'title="' . esc_attr( $title ) . '"' : '' );
                        $temp .= '<a ' . $link_title . ' href="#" aria-label="' . __( "Search Title Link", "ivory-search" ) . '">';
                    } else {
                        $temp .= '<a href="#" aria-label="' . __( "Search Icon Link", "ivory-search" ) . '">';
                    }
                    
                    
                    if ( '' == $title ) {
                        $temp .= '<svg width="20" height="20" class="search-icon" role="img" viewBox="2 9 20 5" focusable="false" aria-label="' . __( "Search", "ivory-search" ) . '">
						<path class="search-icon-path" d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path></svg>';
                    } else {
                        $temp .= $title;
                    }
                    
                    $temp .= '</a>';
                }
                
                
                if ( !isset( $this->opt['menu_style'] ) || $this->opt['menu_style'] !== 'popup' ) {
                    $temp .= $this->get_menu_search_form( false );
                    if ( !isset( $this->opt['menu_style'] ) || isset( $this->opt['menu_close_icon'] ) && $this->opt['menu_close_icon'] ) {
                        $temp .= '<div class="search-close"></div>';
                    }
                }
                
                $temp .= '</li>';
            }
            
            
            if ( isset( $this->opt['first_menu_item'] ) && $this->opt['first_menu_item'] ) {
                $items = $temp . $items;
            } else {
                $items .= $temp;
            }
        
        }
        
        return $items;
    }
    
    /**
     * Displays search form in mobile header in the front end of site.
     */
    function header_menu_search()
    {
        $items = '';
        
        if ( isset( $this->opt['menu_gcse'] ) && $this->opt['menu_gcse'] != '' ) {
            $items .= '<div class="astm-search-menu-wrapper is-menu-wrapper"><div class="gsc-cse-search-menu">' . $this->opt['menu_gcse'] . '</div></div>';
        } else {
            $search_class = ( isset( $this->opt['menu_classes'] ) ? $this->opt['menu_classes'] . ' astm-search-menu is-menu ' : ' astm-search-menu is-menu ' );
            $search_class .= ( isset( $this->opt['menu_style'] ) && 'dropdown' != $this->opt['menu_style'] ? $this->opt['menu_style'] : 'is-dropdown' );
            $title = ( isset( $this->opt['menu_title'] ) ? $this->opt['menu_title'] : '' );
            $items .= '<div class="astm-search-menu-wrapper is-menu-wrapper"><div>';
            $items .= '<span class="' . esc_attr( $search_class ) . '">';
            
            if ( !isset( $this->opt['menu_style'] ) || $this->opt['menu_style'] != 'default' ) {
                $link_title = ( apply_filters( 'is_show_menu_link_title', true ) ? 'title="' . esc_attr( $title ) . '"' : '' );
                $items .= '<a ' . $link_title . ' href="#">';
                
                if ( '' == $title ) {
                    $items .= '<svg width="20" height="20" class="search-icon" role="img" viewBox="2 9 20 5" focusable="false" aria-label="' . __( "Search", "ivory-search" ) . '">
					<path class="search-icon-path" d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path></svg>';
                } else {
                    $items .= $title;
                }
                
                $items .= '</a>';
            }
            
            
            if ( !isset( $this->opt['menu_style'] ) || $this->opt['menu_style'] !== 'popup' ) {
                $items .= $this->get_menu_search_form( false );
                if ( !isset( $this->opt['menu_style'] ) || isset( $this->opt['menu_close_icon'] ) && $this->opt['menu_close_icon'] ) {
                    $items .= '<div class="search-close"></div>';
                }
            }
            
            $items .= '</span></div></div>';
        }
        
        echo  $items ;
    }
    
    /**
     * Adds query vars to searches.
     */
    function query_vars( $vars )
    {
        $vars[] = "id";
        return $vars;
    }
    
    /**
     * Filters search after the query variable object is created, but before the actual query is run.
     */
    function pre_get_posts( $query )
    {
        if ( !$query->is_search() ) {
            return;
        }
        $is_id = '';
        
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $is_id = ( isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '-1' );
        } else {
            if ( is_admin() || !$query->is_main_query() ) {
                return;
            }
            $is_id = get_query_var( 'id' );
        }
        
        
        if ( '' === $is_id ) {
            if ( isset( $this->opt['default_search'] ) ) {
                return;
            }
            $page = get_page_by_path( 'default-search-form', OBJECT, 'is_search_form' );
            if ( !empty($page) ) {
                $is_id = $page->ID;
            }
        }
        
        
        if ( !isset( $query->query_vars['s'] ) || empty($query->query_vars['s']) ) {
            $query->set( 's', $query->query['s'] );
            $query->set( 'post__in', false );
            $query->set( 'orderby', 'date' );
        }
        
        $q = $query->query_vars;
        
        if ( '' !== $is_id && is_numeric( $is_id ) ) {
            
            if ( function_exists( 'pll_current_language' ) ) {
                $lang = pll_current_language();
                $query->set( 'lang', $lang );
            }
            
            
            if ( isset( $this->opt['stopwords'] ) && (isset( $q['s'] ) && '' !== $q['s']) ) {
                $stopwords = explode( ',', $this->opt['stopwords'] );
                $stopwords = array_map( 'trim', $stopwords );
                $q['s'] = preg_replace( '/\\b(' . implode( '|', $stopwords ) . ')\\b/', '', $q['s'] );
                $query->query_vars['s'] = trim( preg_replace( '/\\s\\s+/', ' ', str_replace( "\n", " ", $q['s'] ) ) );
                
                if ( empty($query->query_vars['s']) || 1 == strlen( $query->query_vars['s'] ) && preg_match( '/[^a-zA-Z\\d]/', $query->query_vars['s'] ) ) {
                    $query->is_home = false;
                    $query->is_404 = true;
                    $query->set( 'post__in', array( 9999999999 ) );
                    return;
                }
            
            }
            
            $is_fields = get_post_meta( $is_id );
            if ( !empty($is_fields) ) {
                foreach ( $is_fields as $key => $val ) {
                    
                    if ( isset( $val[0] ) && '' !== $val[0] ) {
                        $temp = maybe_unserialize( $val[0] );
                        $query->query_vars[$key] = $temp;
                        switch ( $key ) {
                            case '_is_includes':
                                
                                if ( !empty($temp) ) {
                                    $temp = apply_filters( 'is_pre_get_posts_includes', $temp );
                                    foreach ( $temp as $inc_key => $inc_val ) {
                                        if ( is_array( $inc_val ) && !empty($inc_val) || '' !== $inc_val ) {
                                            switch ( $inc_key ) {
                                                case 'post__in':
                                                    $query->set( $inc_key, array_values( $inc_val ) );
                                                    break;
                                                case 'post_type':
                                                    $pt_val = array_values( $inc_val );
                                                    $query->set( $inc_key, $pt_val );
                                                    if ( in_array( 'attachment', $inc_val ) ) {
                                                        $query->set( 'post_status', array( 'publish', 'inherit' ) );
                                                    }
                                                    break;
                                                case 'tax_query':
                                                    $tax_rel = ( isset( $temp['tax_rel'] ) && 'AND' === $temp['tax_rel'] ? 'AND' : 'OR' );
                                                    $tax_args = array(
                                                        'relation' => $tax_rel,
                                                    );
                                                    $temp2 = array();
                                                    foreach ( $inc_val as $tax_key => $tax_val ) {
                                                        
                                                        if ( !empty($tax_val) ) {
                                                            $tax_arr = array(
                                                                'taxonomy' => $tax_key,
                                                                'field'    => 'term_taxonomy_id',
                                                                'terms'    => array_values( $tax_val ),
                                                            );
                                                            $tax_arr['post_type'] = $temp['tax_post_type'][$tax_key];
                                                            if ( empty($temp2) || !in_array( $temp['tax_post_type'][$tax_key], $temp2 ) ) {
                                                                array_push( $temp2, $temp['tax_post_type'][$tax_key] );
                                                            }
                                                            array_push( $tax_args, $tax_arr );
                                                        }
                                                    
                                                    }
                                                    if ( count( $temp2 ) > 1 ) {
                                                        $tax_args['relation'] = 'OR';
                                                    }
                                                    $query->set( $inc_key, $tax_args );
                                                    break;
                                                case 'author':
                                                    break;
                                                case 'date_query':
                                                    foreach ( $inc_val as $key => $value ) {
                                                        
                                                        if ( isset( $inc_val[$key]['date'] ) && !empty($inc_val[$key]['date']) ) {
                                                            $temp = explode( '-', $inc_val[$key]['date'] );
                                                            
                                                            if ( is_array( $temp ) && !empty($temp) ) {
                                                                if ( isset( $temp[0] ) && $temp[0] > 0 && $temp[0] < 32 ) {
                                                                    $inc_val[$key]['day'] = $temp[0];
                                                                }
                                                                if ( isset( $temp[1] ) ) {
                                                                    $inc_val[$key]['month'] = $temp[1];
                                                                }
                                                                if ( isset( $temp[2] ) ) {
                                                                    $inc_val[$key]['year'] = $temp[2];
                                                                }
                                                            }
                                                            
                                                            unset( $inc_val[$key]['date'] );
                                                        } else {
                                                            unset( $inc_val[$key] );
                                                        }
                                                    
                                                    }
                                                    
                                                    if ( !empty($inc_val['before']) || !empty($inc_val['after']) ) {
                                                        $date_args = array_merge( array(
                                                            'inclusive' => true,
                                                        ), $inc_val );
                                                        $query->set( $inc_key, $date_args );
                                                    }
                                                    
                                                    break;
                                                case 'has_password':
                                                    $temp = ( '1' === $inc_val ? true : FALSE );
                                                    if ( 'null' !== $inc_val ) {
                                                        $query->set( $inc_key, $temp );
                                                    }
                                                    break;
                                                case 'post_status':
                                                    $query->set( $inc_key, array(
                                                        'publish' => 'publish',
                                                        'inherit' => 'inherit',
                                                    ) );
                                                    break;
                                                case 'comment_count':
                                                    break;
                                            }
                                        }
                                    }
                                }
                                
                                break;
                            case '_is_excludes':
                                
                                if ( !empty($temp) ) {
                                    $temp = apply_filters( 'is_pre_get_posts_excludes', $temp );
                                    foreach ( $temp as $inc_key => $inc_val ) {
                                        if ( is_array( $inc_val ) && !empty($inc_val) || '' !== $inc_val ) {
                                            switch ( $inc_key ) {
                                                case 'post__not_in':
                                                case 'ignore_sticky_posts':
                                                    $values = array();
                                                    if ( isset( $query->query_vars['_is_excludes']['ignore_sticky_posts'] ) ) {
                                                        $values = get_option( 'sticky_posts' );
                                                    }
                                                    
                                                    if ( isset( $query->query_vars['_is_excludes']['post__not_in'] ) ) {
                                                        $values = array_merge( $values, array_values( $query->query_vars['_is_excludes']['post__not_in'] ) );
                                                        $exclude_child = apply_filters( 'is_exclude_child', false );
                                                        if ( $exclude_child ) {
                                                            $query->set( 'post_parent__not_in', $values );
                                                        }
                                                    }
                                                    
                                                    $query->set( 'post__not_in', $values );
                                                    break;
                                                case 'tax_query':
                                                    
                                                    if ( !isset( $query->query_vars['tax_query'] ) ) {
                                                        $tax_args = array();
                                                        foreach ( $inc_val as $tax_key => $tax_val ) {
                                                            
                                                            if ( !empty($tax_val) ) {
                                                                $tax_arr = array(
                                                                    'taxonomy' => $tax_key,
                                                                    'field'    => 'term_taxonomy_id',
                                                                    'terms'    => array_values( $tax_val ),
                                                                    'operator' => 'NOT IN',
                                                                );
                                                                array_push( $tax_args, $tax_arr );
                                                            }
                                                        
                                                        }
                                                        
                                                        if ( !empty($tax_args) ) {
                                                            array_push( $tax_args, array(
                                                                'relation' => 'AND',
                                                            ) );
                                                            $query->set( $inc_key, $tax_args );
                                                        }
                                                    
                                                    }
                                                    
                                                    break;
                                                case 'author':
                                                    break;
                                                case 'woo':
                                                    break;
                                            }
                                        }
                                    }
                                }
                                
                                break;
                            case '_is_settings':
                                
                                if ( !empty($temp) ) {
                                    $temp = apply_filters( 'is_pre_get_posts_settings', $temp );
                                    foreach ( $temp as $inc_key => $inc_val ) {
                                        if ( is_array( $inc_val ) && !empty($inc_val) || '' !== $inc_val ) {
                                            switch ( $inc_key ) {
                                                case 'posts_per_page':
                                                    $query->set( $inc_key, $inc_val );
                                                    break;
                                                case 'move_sticky_posts':
                                                    if ( !$query->is_paged() && !isset( $query->query_vars['_is_excludes']['ignore_sticky_posts'] ) ) {
                                                        add_filter(
                                                            'the_posts',
                                                            function ( $posts ) {
                                                            
                                                            if ( !empty($posts) ) {
                                                                $sticky_posts = array();
                                                                foreach ( $posts as $key => $post ) {
                                                                    
                                                                    if ( is_sticky( $post->ID ) ) {
                                                                        $sticky_posts[] = $post;
                                                                        unset( $posts[$key] );
                                                                    }
                                                                
                                                                }
                                                                if ( !empty($sticky_posts) ) {
                                                                    $posts = array_merge( $sticky_posts, array_values( $posts ) );
                                                                }
                                                            }
                                                            
                                                            return $posts;
                                                        },
                                                            99,
                                                            2
                                                        );
                                                    }
                                                    break;
                                                case 'order':
                                                    break;
                                                case 'orderby':
                                                    break;
                                                case 'empty_search':
                                                    // If 's' request variable is set but empty
                                                    
                                                    if ( isset( $query->query_vars['s'] ) && empty($query->query_vars['s']) ) {
                                                        $query->is_home = false;
                                                        $query->is_404 = true;
                                                    }
                                                    
                                                    break;
                                            }
                                        }
                                    }
                                }
                                
                                break;
                        }
                    }
                
                }
            }
        }
        
        do_action( 'is_pre_get_posts', $query );
    }
    
    /**
     * Requests distinct results
     * 
     * @return string $distinct
     */
    function posts_distinct_request( $distinct, $query )
    {
        if ( (!is_admin() || defined( 'DOING_AJAX' ) && DOING_AJAX) && !empty($query->query_vars['s']) ) {
            return 'DISTINCT';
        }
        return $distinct;
    }
    
    /**
     * Filters the search SQL that is used in the WHERE clause of WP_Query.
     */
    function posts_search( $search, $query )
    {
        $q = $query->query_vars;
        
        if ( empty($q['search_terms']) || is_admin() && !(defined( 'DOING_AJAX' ) && DOING_AJAX) || !isset( $q['_is_includes'] ) ) {
            return $search;
            // skip processing
        } else {
            if ( is_array( $q['search_terms'] ) && 1 == count( $q['search_terms'] ) ) {
                if ( 0 !== strpos( $q['s'], '"' ) ) {
                    $q['search_terms'] = explode( ' ', $q['search_terms'][0] );
                }
            }
        }
        
        $terms_relation_type = ( isset( $q['_is_settings']['term_rel'] ) && 'OR' === $q['_is_settings']['term_rel'] ? 'OR' : 'AND' );
        
        if ( isset( $this->opt['synonyms'] ) && 'OR' === $terms_relation_type ) {
            $pairs = preg_split( '/\\r\\n|\\r|\\n/', $this->opt['synonyms'] );
            foreach ( $pairs as $pair ) {
                if ( empty($pair) ) {
                    // Skip empty rows.
                    continue;
                }
                $parts = explode( '=', $pair );
                $key = strval( trim( $parts[0] ) );
                $value = trim( $parts[1] );
                if ( in_array( $key, (array) $q['search_terms'] ) && !in_array( $value, (array) $q['search_terms'] ) ) {
                    array_push( $q['search_terms'], $value );
                }
                if ( in_array( $value, (array) $q['search_terms'] ) && !in_array( $key, (array) $q['search_terms'] ) ) {
                    array_push( $q['search_terms'], $key );
                }
            }
            $query->query_vars['search_terms'] = $q['search_terms'];
        }
        
        global  $wpdb ;
        $f = '%';
        $l = '%';
        $like = 'LIKE';
        
        if ( isset( $q['_is_settings']['fuzzy_match'] ) && '2' !== $q['_is_settings']['fuzzy_match'] ) {
            $like = 'REGEXP';
            $f = '([[:blank:][:punct:]]|^)';
            $l = '([[:blank:][:punct:]]|$)';
        }
        
        $searchand = '';
        $search = " AND ( ";
        $OR = '';
        foreach ( (array) $q['search_terms'] as $term2 ) {
            $term = $f . $wpdb->esc_like( $term2 ) . $l;
            $OR = '';
            $search .= "{$searchand} (";
            
            if ( isset( $q['_is_includes']['search_title'] ) ) {
                $search .= $wpdb->prepare( "({$wpdb->posts}.post_title {$like} '%s')", $term );
                $OR = ' OR ';
            }
            
            
            if ( isset( $q['_is_includes']['search_content'] ) ) {
                $search .= $OR;
                $search .= $wpdb->prepare( "({$wpdb->posts}.post_content {$like} '%s')", $term );
                $OR = ' OR ';
            }
            
            
            if ( isset( $q['_is_includes']['search_excerpt'] ) ) {
                $search .= $OR;
                $search .= $wpdb->prepare( "({$wpdb->posts}.post_excerpt {$like} '%s')", $term );
                $OR = ' OR ';
            }
            
            
            if ( isset( $q['_is_includes']['search_tax_title'] ) || isset( $q['_is_includes']['search_tax_desp'] ) ) {
                $tax_OR = '';
                $search .= $OR;
                $search .= '( ';
                
                if ( isset( $q['_is_includes']['search_tax_title'] ) ) {
                    $search .= $wpdb->prepare( "( t.name {$like} '%s' )", $term );
                    $tax_OR = ' OR ';
                }
                
                
                if ( isset( $q['_is_includes']['search_tax_desp'] ) ) {
                    $search .= $tax_OR;
                    $search .= $wpdb->prepare( "( tt.description {$like} '%s' )", $term );
                }
                
                $search .= ' )';
                $OR = ' OR ';
            }
            
            
            if ( isset( $q['_is_includes']['search_comment'] ) ) {
                $search .= $OR;
                $search .= $wpdb->prepare( "(cm.comment_content {$like} '%s')", $term );
                $OR = ' OR ';
            }
            
            
            if ( isset( $q['_is_includes']['search_author'] ) ) {
                $search .= $OR;
                $search .= $wpdb->prepare( "(users.display_name {$like} '%s')", $term );
                $OR = ' OR ';
            }
            
            
            if ( isset( $q['_is_includes']['custom_field'] ) ) {
                $meta_key_OR = '';
                $search .= $OR;
                foreach ( $q['_is_includes']['custom_field'] as $key_slug ) {
                    $search .= $wpdb->prepare( "{$meta_key_OR} (pm.meta_key = '%s' AND pm.meta_value {$like} '%s')", $key_slug, $term );
                    $meta_key_OR = ' OR ';
                }
                $OR = ' OR ';
            }
            
            $search .= ")";
            $searchand = " {$terms_relation_type} ";
        }
        if ( '' === $OR ) {
            $search = " AND ( 0 ";
        }
        $search = apply_filters( 'is_posts_search_terms', $search, $q['search_terms'] );
        $search .= ")";
        if ( isset( $q['post_type'] ) && NULL !== $q['post_type'] && !is_array( $q['post_type'] ) ) {
            $q['post_type'] = array( $q['post_type'] );
        }
        
        if ( isset( $q['_is_includes']['tax_query'] ) && count( $q['post_type'] ) > 1 ) {
            $search .= " AND ( ( ";
            $OR = '';
            $i = 0;
            $tax_post_type = $q['post_type'];
            foreach ( $q['tax_query'] as $value ) {
                
                if ( isset( $value['terms'] ) ) {
                    $tax_post_type = array_diff( $tax_post_type, array( $value['post_type'] ) );
                    
                    if ( 'OR' === $q['tax_query']['relation'] ) {
                        $search .= $OR;
                        $search .= "tr.term_taxonomy_id IN (" . implode( ',', $value['terms'] ) . ')';
                        $OR = " " . $q['tax_query']['relation'] . " ";
                    } else {
                        foreach ( $value['terms'] as $term2 ) {
                            $alias = ( $i ? 'tr' . $i : 'tr' );
                            $search .= $OR;
                            $search .= "{$alias}.term_taxonomy_id = " . $term2;
                            $OR = " " . $q['tax_query']['relation'] . " ";
                            $i++;
                        }
                    }
                
                }
            
            }
            $search .= ")";
            if ( !empty($tax_post_type) ) {
                $search .= " OR {$wpdb->posts}.post_type IN ('" . join( "', '", array_map( 'esc_sql', $tax_post_type ) ) . "')";
            }
            $search .= ")";
            $query->query_vars['tax_query'] = '';
        }
        
        
        if ( isset( $q['_is_excludes']['tax_query'] ) ) {
            $AND = '';
            $search .= " AND ( ";
            foreach ( $q['_is_excludes']['tax_query'] as $value ) {
                $search .= $AND;
                $search .= "( {$wpdb->posts}.ID NOT IN ( SELECT {$wpdb->term_relationships}.object_id FROM {$wpdb->term_relationships} WHERE {$wpdb->term_relationships}.term_taxonomy_id IN ( " . implode( ',', $value ) . ") ) )";
                $AND = " AND ";
            }
            $search .= ")";
        }
        
        $search = apply_filters( 'is_posts_search', $search );
        return $search;
    }
    
    /**
     * Filters the JOIN clause of the query.
     */
    function posts_join( $join, $query )
    {
        global  $wpdb ;
        if ( empty($wpdb) || !isset( $query->query_vars ) ) {
            return $join;
        }
        $q = $query->query_vars;
        if ( empty($q['s']) || !isset( $q['_is_includes'] ) || is_admin() && !(defined( 'DOING_AJAX' ) && DOING_AJAX) ) {
            return $join;
        }
        if ( isset( $q['_is_includes']['search_comment'] ) ) {
            $join .= " LEFT JOIN {$wpdb->comments} AS cm ON ( {$wpdb->posts}.ID = cm.comment_post_ID AND cm.comment_approved =  '1') ";
        }
        if ( isset( $q['_is_includes']['search_author'] ) ) {
            $join .= " LEFT JOIN {$wpdb->users} users ON ({$wpdb->posts}.post_author = users.ID) ";
        }
        $woo_sku = false;
        $exc_custom_fields = false;
        if ( class_exists( 'WooCommerce' ) && is_fs()->is_plan_or_trial__premium_only( 'pro_plus' ) ) {
            $woo_sku = ( isset( $q['_is_includes']['woo']['sku'] ) ? true : false );
        }
        if ( isset( $q['_is_includes']['custom_field'] ) || $exc_custom_fields || $woo_sku ) {
            $join .= " LEFT JOIN {$wpdb->postmeta} pm ON ({$wpdb->posts}.ID = pm.post_id) ";
        }
        $tt_table = ( isset( $q['_is_includes']['search_tax_title'] ) || isset( $q['_is_includes']['search_tax_desp'] ) ? true : false );
        $i = 0;
        if ( isset( $q['_is_includes']['tax_query'] ) || isset( $q['_is_excludes']['tax_query'] ) || $tt_table ) {
            
            if ( isset( $q['_is_includes']['tax_rel'] ) && 'AND' === $q['_is_includes']['tax_rel'] && isset( $q['_is_includes']['tax_query'] ) ) {
                foreach ( $q['_is_includes']['tax_query'] as $value ) {
                    if ( !empty($value) ) {
                        foreach ( $value as $terms ) {
                            $alias = ( $i ? 'tr' . $i : 'tr' );
                            $join .= " LEFT JOIN {$wpdb->term_relationships} AS {$alias}";
                            $join .= " ON ({$wpdb->posts}.ID = {$alias}.object_id)";
                            $i++;
                        }
                    }
                }
            } else {
                $join .= " LEFT JOIN {$wpdb->term_relationships} AS tr ON ({$wpdb->posts}.ID = tr.object_id) ";
            }
        
        }
        
        if ( $tt_table ) {
            $join .= " LEFT JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) ";
            $join .= " LEFT JOIN {$wpdb->terms} AS t ON (tt.term_id = t.term_id) ";
        }
        
        $join = apply_filters( 'is_posts_join', $join );
        return $join;
    }
    
    /**
     * Adds code in the header of site front end.
     */
    function wp_head()
    {
        if ( isset( $this->opt['header_search'] ) && $this->opt['header_search'] ) {
            echo  do_shortcode( '[ivory-search id="' . $this->opt['header_search'] . '"]' ) ;
        }
        
        if ( isset( $this->opt['not_load_files']['js'] ) ) {
            $is_temp_ajax = array(
                'ajaxurl'    => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => wp_create_nonce( 'is_ajax_nonce' ),
            );
            ?>
			<script id='ivory-search-js-extras'>
			var IvoryAjaxVars = <?php 
            echo  json_encode( $is_temp_ajax ) ;
            ?>;
			<?php 
            $is_analytics = get_option( 'is_analytics', array() );
            $analytics_disabled = ( isset( $is_analytics['disable_analytics'] ) ? $is_analytics['disable_analytics'] : 0 );
            
            if ( !$analytics_disabled ) {
                $is_temp = array(
                    'is_analytics_enabled' => "1",
                );
                
                if ( is_search() ) {
                    global  $wp_query ;
                    if ( isset( $_GET['id'] ) ) {
                        $is_temp['is_id'] = $_GET['id'];
                    }
                    if ( isset( $_GET['s'] ) ) {
                        $is_temp['is_label'] = $_GET['s'];
                    }
                    
                    if ( 0 == $wp_query->found_posts ) {
                        $is_temp['is_cat'] = 'Nothing Found';
                    } else {
                        $is_temp['is_cat'] = 'Results Found';
                    }
                
                }
                
                ?>
				var IvorySearchVars = <?php 
                echo  json_encode( $is_temp ) ;
                ?>;
				<?php 
            }
            
            ?>
			</script>
			<?php 
        }
    
    }
    
    /**
     * Adds code in the footer of site front end.
     */
    function wp_footer()
    {
        
        if ( isset( $this->opt['menu_style'] ) && 'default' !== $this->opt['menu_style'] && isset( $this->opt['menu_magnifier_color'] ) && !empty($this->opt['menu_magnifier_color']) ) {
            echo  '<style type="text/css" media="screen">' ;
            echo  '.is-menu path.search-icon-path { fill: ' . $this->opt['menu_magnifier_color'] . ';}' ;
            echo  'body .popup-search-close:after, body .search-close:after { border-color: ' . $this->opt['menu_magnifier_color'] . ';}' ;
            echo  'body .popup-search-close:before, body .search-close:before { border-color: ' . $this->opt['menu_magnifier_color'] . ';}' ;
            echo  '</style>' ;
        }
        
        
        if ( isset( $this->opt['custom_css'] ) && $this->opt['custom_css'] != '' ) {
            echo  '<style type="text/css" media="screen">' ;
            echo  '/* Ivory search custom CSS code */' ;
            echo  wp_specialchars_decode( esc_html( $this->opt['custom_css'] ), ENT_QUOTES ) ;
            echo  '</style>' ;
        }
        
        global  $wp_query ;
        
        if ( is_search() && isset( $wp_query->query_vars['_is_settings']['highlight_terms'] ) && isset( $wp_query->query_vars['_is_settings']['highlight_color'] ) ) {
            echo  '<style type="text/css" media="screen">' ;
            echo  '.is-highlight { background-color: ' . $wp_query->query_vars['_is_settings']['highlight_color'] . ' !important;}' ;
            echo  '</style>' ;
        }
        
        if ( isset( $this->opt['footer_search'] ) && $this->opt['footer_search'] ) {
            echo  do_shortcode( '[ivory-search id="' . $this->opt['footer_search'] . '"]' ) ;
        }
        
        if ( isset( $this->opt['menu_style'] ) && 'popup' === $this->opt['menu_style'] ) {
            echo  '<div id="is-popup-wrapper" style="display:none">' ;
            if ( !isset( $this->opt['menu_style'] ) || isset( $this->opt['menu_close_icon'] ) && $this->opt['menu_close_icon'] ) {
                echo  '<div class="popup-search-close"></div>' ;
            }
            echo  '<div class="is-popup-search-form">' ;
            do_action( 'is_before_popup_search_form' );
            $this->get_menu_search_form();
            do_action( 'is_after_popup_search_form' );
            echo  '</div></div>' ;
        }
    
    }

}