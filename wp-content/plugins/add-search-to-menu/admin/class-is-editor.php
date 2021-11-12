<?php

class IS_Search_Editor
{
    private  $search_form ;
    private  $panels = array() ;
    private  $is_premium_plugin = false ;
    public function __construct( IS_Search_Form $search_form )
    {
        $this->search_form = $search_form;
    }
    
    function is_name( $string )
    {
        return preg_match( '/^[A-Za-z][-A-Za-z0-9_:.]*$/', $string );
    }
    
    public function add_panel(
        $id,
        $title,
        $callback,
        $description
    )
    {
        if ( $this->is_name( $id ) ) {
            $this->panels[$id] = array(
                'title'       => $title,
                'callback'    => $callback,
                'description' => $description,
            );
        }
    }
    
    public function display()
    {
        if ( empty($this->panels) ) {
            return;
        }
        echo  '<ul id="search-form-editor-tabs">' ;
        $url = esc_url( menu_page_url( 'ivory-search-new', false ) );
        if ( isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) {
            $url = esc_url( menu_page_url( 'ivory-search', false ) ) . '&post=' . $_GET['post'] . '&action=edit';
        }
        $tab = 'includes';
        if ( isset( $_GET['tab'] ) ) {
            switch ( $_GET['tab'] ) {
                case 'excludes':
                    $tab = 'excludes';
                    break;
                case 'customize':
                    $tab = 'customize';
                    break;
                case 'ajax':
                    $tab = 'ajax';
                    break;
                case 'options':
                    $tab = 'options';
                    break;
            }
        }
        foreach ( $this->panels as $id => $panel ) {
            $class = ( $tab == $id ? 'active' : '' );
            echo  sprintf(
                '<li id="%1$s-tab" class="%2$s"><a href="%3$s" title="%4$s">%5$s</a></li>',
                esc_attr( $id ),
                esc_attr( $class ),
                $url . '&tab=' . $id,
                esc_attr( $panel['description'] ),
                esc_html( $panel['title'] )
            ) ;
        }
        echo  '</ul>' ;
        echo  sprintf( '<div class="search-form-editor-panel" id="%1$s">', esc_attr( $tab ) ) ;
        $this->notice( $tab, $tab . '_panel' );
        $callback = $tab . '_panel';
        
        if ( method_exists( $this, $callback ) ) {
            $this->{$callback}( $this->search_form );
        } else {
            _e( 'The requested section does not exist.', 'add-search-to-menu' );
        }
        
        echo  '</div>' ;
    }
    
    public function notice( $id, $panel )
    {
        echo  '<div class="config-error"></div>' ;
    }
    
    /**
     * Gets all public meta keys of post types
     *
     * @global Object $wpdb WPDB object
     * @return Array array of meta keys
     */
    function is_meta_keys( $post_type )
    {
        global  $wpdb ;
        $is_fields = $wpdb->get_results( apply_filters( 'is_meta_keys_query', "select DISTINCT meta_key from {$wpdb->postmeta} pt LEFT JOIN {$wpdb->posts} p ON (pt.post_id = p.ID) where meta_key NOT LIKE '\\_%' AND post_type IN ( '{$post_type}' ) ORDER BY meta_key ASC" ) );
        $meta_keys = array();
        if ( is_array( $is_fields ) && !empty($is_fields) ) {
            foreach ( $is_fields as $field ) {
                if ( isset( $field->meta_key ) ) {
                    $meta_keys[] = $field->meta_key;
                }
            }
        }
        /**
         * Filter results of SQL query for meta keys
         */
        return apply_filters( 'is_meta_keys', $meta_keys );
    }
    
    public function inc_exc_url( $section )
    {
        $includes_url = '';
        $sec_name = __( "Search", 'add-search-to-menu' );
        if ( 'excludes' === $section ) {
            $sec_name = __( "Exclude", 'add-search-to-menu' );
        }
        
        if ( isset( $_REQUEST['post'] ) ) {
            $includes_url = '<a href="' . esc_url( menu_page_url( 'ivory-search', false ) ) . '&post=' . $_REQUEST['post'] . '&action=edit&tab=' . $section . '">' . $sec_name . '</a>';
        } else {
            if ( isset( $_REQUEST['page'] ) && 'ivory-search-new' === $_REQUEST['page'] ) {
                $includes_url = '<a href="' . esc_url( menu_page_url( 'ivory-search-new', false ) ) . '&tab=' . $section . '">' . $sec_name . '</a>';
            }
        }
        
        return $includes_url;
    }
    
    public function includes_panel( $post )
    {
        $id = '_is_includes';
        $includes = $post->prop( $id );
        $excludes = $post->prop( '_is_excludes' );
        $settings = $post->prop( '_is_settings' );
        $default_search = ( NULL == $post->id() ? true : false );
        ?>
		<h4 class="panel-desc">
			<?php 
        _e( "Configure Searchable Content", 'add-search-to-menu' );
        ?>
		</h4>
		<div class="search-form-editor-box" id="<?php 
        echo  $id ;
        ?>">

		<div class="form-table form-table-panel-includes">

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-post_type"><?php 
        esc_html_e( 'Post Types', 'add-search-to-menu' );
        ?></label>
				<span class="is-actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'add-search-to-menu' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'add-search-to-menu' );
        ?></a></span>
			</h3>
			<div>
				<?php 
        $content = __( 'Search selected post types.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $post_types = get_post_types( array(
            'public' => true,
        ), 'objects' );
        $post_types2 = array();
        
        if ( $default_search ) {
            $post_types2 = get_post_types( array(
                'public'              => true,
                'exclude_from_search' => false,
            ) );
        } else {
            if ( isset( $includes['post_type'] ) && !empty($includes['post_type']) && is_array( $includes['post_type'] ) ) {
                $post_types2 = array_values( $includes['post_type'] );
            }
        }
        
        
        if ( !empty($post_types) ) {
            echo  '<div class="is-cb-dropdown">' ;
            echo  '<div class="is-cb-title">' ;
            
            if ( empty($post_types2) ) {
                echo  '<span class="is-cb-select">' . __( 'Select Post Types', 'add-search-to-menu' ) . '</span><span class="is-cb-titles"></span>' ;
            } else {
                echo  '<span style="display:none;" class="is-cb-select">' . __( 'Select Post Types', 'add-search-to-menu' ) . '</span><span class="is-cb-titles">' ;
                foreach ( $post_types2 as $post_type2 ) {
                    if ( isset( $post_types[$post_type2] ) ) {
                        echo  '<span title="' . $post_type2 . '"> ' . $post_types[$post_type2]->labels->name . '</span>' ;
                    }
                }
                echo  '</span>' ;
            }
            
            echo  '</div>' ;
            echo  '<div class="is-cb-multisel">' ;
            foreach ( $post_types as $key => $post_type ) {
                $checked = ( $default_search && in_array( $key, $post_types2 ) || isset( $includes['post_type'][esc_attr( $key )] ) ? esc_attr( $key ) : 0 );
                echo  '<label for="' . $id . '-post_type-' . esc_attr( $key ) . '"> ' ;
                echo  '<input class="_is_includes-post_type" type="checkbox" id="' . $id . '-post_type-' . esc_attr( $key ) . '" name="' . $id . '[post_type][' . esc_attr( $key ) . ']" value="' . esc_attr( $key ) . '" ' . checked( $key, $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' ;
                echo  ucfirst( esc_html( $post_type->labels->name ) ) . '</label>' ;
            }
            echo  '</div></div>' ;
        } else {
            echo  '<span class="notice-is-info">' . __( 'No post types registered on the site.', 'add-search-to-menu' ) . '</span>' ;
        }
        
        
        if ( isset( $includes['post_type'] ) && is_array( $includes['post_type'] ) && 1 == count( $includes['post_type'] ) ) {
            $checked = ( isset( $includes['post_type_url'] ) ? 'y' : 'n' );
            echo  '<br /><p class="check-radio"><label for="' . $id . '-post_type_url"><input class="_is_includes-post_type_url" type="checkbox" id="' . $id . '-post_type_url" name="' . $id . '[post_type_url]" value="y" ' . checked( 'y', $checked, false ) . '/>' ;
            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Do not display post_type in the search URL", 'add-search-to-menu' ) . '</label></p>' ;
        }
        
        ?>
			</div></div>

                        <?php 
        foreach ( $post_types2 as $post_type ) {
            if ( !isset( $post_types[$post_type] ) ) {
                continue;
            }
            $accord_title = $post_types[$post_type]->labels->name;
            
            if ( 'product' == $post_type ) {
                $accord_title .= ' <i>' . __( '( WooCommerce )', 'add-search-to-menu' ) . '</i>';
            } else {
                if ( 'attachment' == $post_type ) {
                    $accord_title .= ' <i>' . __( '( Images, Videos, Audios, Docs, PDFs, Files & Attachments  )', 'add-search-to-menu' ) . '</i>';
                }
            }
            
            ?>

			<h3 scope="row" class="is-p-type post-type-<?php 
            echo  $post_type ;
            ?>">
				<label for="<?php 
            echo  $id ;
            ?>-post__in"><?php 
            echo  $accord_title ;
            ?></label>
			</h3>
			<div class="post-type-<?php 
            echo  $post_type ;
            ?>">
				<?php 
            echo  '<div>' ;
            
            if ( 'product' == $post_type && !class_exists( 'WooCommerce' ) ) {
                IS_Help::woocommerce_inactive_field_notice();
                echo  '</div></div>' ;
                continue;
            }
            
            $posts_found = false;
            $posts_per_page = ( defined( 'DISABLE_IS_LOAD_ALL' ) || isset( $includes['post__in'] ) ? -1 : 100 );
            $posts = get_posts( array(
                'post_type'      => $post_type,
                'posts_per_page' => $posts_per_page,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ) );
            $html = '<div class="is-posts">';
            $selected_pt = array();
            
            if ( !empty($posts) ) {
                $posts_found = true;
                $html .= '<div class="col-wrapper"><div class="col-title">';
                $col_title = '<span>' . $post_types[$post_type]->labels->name . '</span>';
                $temp = '';
                foreach ( $posts as $post2 ) {
                    $checked = ( isset( $includes['post__in'] ) && in_array( $post2->ID, $includes['post__in'] ) ? $post2->ID : 0 );
                    if ( $checked ) {
                        array_push( $selected_pt, $post_type );
                    }
                    $post_title = ( isset( $post2->post_title ) && '' !== $post2->post_title ? esc_html( $post2->post_title ) : $post2->post_name );
                    $temp .= '<option value="' . esc_attr( $post2->ID ) . '" ' . selected( $post2->ID, $checked, false ) . '>' . $post_title . '</option>';
                }
                if ( !empty($selected_pt) && in_array( $post_type, $selected_pt ) ) {
                    $col_title = '<strong>' . $col_title . '</strong>';
                }
                $html .= $col_title . '<input class="list-search" placeholder="' . __( "Search..", 'add-search-to-menu' ) . '" type="text"></div>';
                $html .= '<select class="_is_includes-post__in" name="' . $id . '[post__in][]" multiple size="8" >';
                $html .= $temp . '</select>';
                if ( count( $posts ) >= 100 && !defined( 'DISABLE_IS_LOAD_ALL' ) && !isset( $includes['post__in'] ) ) {
                    $html .= '<div id="' . $post_type . '" class="load-all">' . __( 'Load All', 'add-search-to-menu' ) . '</div>';
                }
                $html .= '</div><br />';
            }
            
            
            if ( !$posts_found ) {
                $html .= '<span class="notice-is-info">' . sprintf( __( 'No %s created.', 'add-search-to-menu' ), $post_types[$post_type]->labels->name ) . '</span>';
            } else {
                $html .= '<label for="' . $id . '-post__in" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'add-search-to-menu' ) . '</label><br />';
            }
            
            $html .= '</div>';
            $checked = 'all';
            
            if ( !empty($selected_pt) && in_array( $post_type, $selected_pt ) ) {
                $checked = 'selected';
            } else {
                if ( isset( $includes['post__in'] ) ) {
                    echo  '<span class="notice-is-info">' . sprintf( __( 'The %s are not searchable as the search form is configured to only search specific posts of another post type.', 'add-search-to-menu' ), strtolower( $post_types[$post_type]->labels->name ) ) . '</span><br /><br />' ;
                }
            }
            
            
            if ( $posts_found ) {
                echo  '<p class="check-radio"><label for="' . $post_type . '-post-search_all" ><input class="is-post-select" type="radio" id="' . $post_type . '-post-search_all" name="' . $post_type . 'i[post_search_radio]" value="all" ' . checked( 'all', $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . sprintf( esc_html__( "Search all %s", 'add-search-to-menu' ), strtolower( $post_types[$post_type]->labels->name ) ) . '</label></p>' ;
                echo  '<p class="check-radio"><label for="' . $post_type . '-post-search_selected" ><input class="is-post-select" type="radio" id="' . $post_type . '-post-search_selected" name="' . $post_type . 'i[post_search_radio]" value="selected" ' . checked( 'selected', $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . sprintf( esc_html__( "Search only selected %s", 'add-search-to-menu' ), strtolower( $post_types[$post_type]->labels->name ) ) . '</label></p>' ;
            }
            
            echo  $html ;
            $tax_objs = get_object_taxonomies( $post_type, 'objects' );
            
            if ( !empty($tax_objs) ) {
                $terms_exist = false;
                $html = '<div class="is-taxes">';
                $selected_tax = false;
                foreach ( $tax_objs as $key => $tax_obj ) {
                    $terms = get_terms( array(
                        'taxonomy' => $key,
                        'lang'     => '',
                    ) );
                    
                    if ( !empty($terms) && !empty($tax_obj->labels->name) ) {
                        $terms_exist = true;
                        $html .= '<div class="col-wrapper"><div class="col-title">';
                        $col_title = ucwords( str_replace( '-', ' ', str_replace( '_', ' ', esc_html( $tax_obj->labels->name ) ) ) );
                        
                        if ( isset( $includes['tax_query'][$key] ) ) {
                            $col_title = '<strong>' . $col_title . '</strong>';
                            $selected_tax = true;
                        }
                        
                        $html .= $col_title . '<input class="list-search" placeholder="' . __( "Search..", 'add-search-to-menu' ) . '" type="text"></div><input type="hidden" id="' . $id . '-tax_post_type" name="' . $id . '[tax_post_type][' . $key . ']" value="' . implode( ',', $tax_obj->object_type ) . '" />';
                        $html .= '<select class="_is_includes-tax_query" name="' . $id . '[tax_query][' . $key . '][]" multiple size="8" >';
                        foreach ( $terms as $key2 => $term ) {
                            $checked = ( isset( $includes['tax_query'][$key] ) && in_array( $term->term_taxonomy_id, $includes['tax_query'][$key] ) ? $term->term_taxonomy_id : 0 );
                            $html .= '<option value="' . esc_attr( $term->term_taxonomy_id ) . '" ' . selected( $term->term_taxonomy_id, $checked, false ) . '>' . esc_html( $term->name ) . '</option>';
                        }
                        $html .= '</select></div>';
                    }
                
                }
                
                if ( $terms_exist ) {
                    $html .= '<br /><label for="' . $id . '-tax_query" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'add-search-to-menu' ) . '</label><br />';
                    $html .= '</div>';
                    $checked = ( $selected_tax ? 'selected' : 'all' );
                    echo  '<br /><p class="check-radio"><label for="' . $post_type . '-tax-search_all" ><input class="is-tax-select" type="radio" id="' . $post_type . '-tax-search_all" name="' . $post_type . 'i[tax_search_radio]" value="all" ' . checked( 'all', $checked, false ) . '/>' ;
                    echo  '<span class="toggle-check-text"></span>' . sprintf(
                        esc_html__( "Search %s of all taxonomies (%s categories, tags & terms %s)", 'add-search-to-menu' ),
                        strtolower( $post_types[$post_type]->labels->name ),
                        '<i>',
                        '</i>'
                    ) . '</label></p>' ;
                    echo  '<p class="check-radio"><label for="' . $post_type . '-tax-search_selected" ><input class="is-tax-select" type="radio" id="' . $post_type . '-tax-search_selected" name="' . $post_type . 'i[tax_search_radio]" value="selected" ' . checked( 'selected', $checked, false ) . '/>' ;
                    echo  '<span class="toggle-check-text"></span>' . sprintf(
                        esc_html__( "Search %s of only selected taxonomies (%s categories, tags & terms %s)", 'add-search-to-menu' ),
                        strtolower( $post_types[$post_type]->labels->name ),
                        '<i>',
                        '</i>'
                    ) . '</label></p>' ;
                    echo  $html ;
                }
            
            }
            
            $meta_keys = $this->is_meta_keys( $post_type );
            
            if ( !empty($meta_keys) ) {
                $html = '<div class="col-wrapper is-metas">';
                $selected_meta = false;
                $html .= '<input class="list-search wide" placeholder="' . __( "Search..", 'add-search-to-menu' ) . '" type="text">';
                $html .= '<select class="_is_includes-custom_field" name="' . $id . '[custom_field][]" multiple size="8" >';
                foreach ( $meta_keys as $meta_key ) {
                    $checked = ( isset( $includes['custom_field'] ) && in_array( $meta_key, $includes['custom_field'] ) ? $meta_key : 0 );
                    if ( $checked ) {
                        $selected_meta = true;
                    }
                    $html .= '<option value="' . esc_attr( $meta_key ) . '" ' . selected( $meta_key, $checked, false ) . '>' . esc_html( $meta_key ) . '</option>';
                }
                $html .= '</select>';
                $html .= '<br /><label for="' . $id . '-custom_field" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'add-search-to-menu' ) . '</label><br />';
                $html .= '</div>';
                $checked = ( $selected_meta ? 'selected' : 'all' );
                echo  '<br /><p class="check-radio"><label for="' . $post_type . '-meta-search_selected" ><input class="is-meta-select" type="checkbox" id="' . $post_type . '-meta-search_selected" name="' . $post_type . 'i[meta_search_radio]" value="selected" ' . checked( 'selected', $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . sprintf( esc_html__( "Search selected %s custom fields values", 'add-search-to-menu' ), $post_type ) . '</label></p>' ;
                echo  $html ;
            }
            
            
            if ( 'product' == $post_type ) {
                $woo_sku_disable = ( is_fs()->is_plan_or_trial( 'pro_plus' ) && $this->is_premium_plugin ? '' : ' disabled ' );
                $checked = ( isset( $includes['woo']['sku'] ) && $includes['woo']['sku'] ? 1 : 0 );
                echo  '<br />' ;
                if ( '' !== $woo_sku_disable ) {
                    echo  '<div class="upgrade-parent">' ;
                }
                echo  '<p class="check-radio"><label for="' . $id . '-sku" ><input class="_is_includes-woocommerce" type="checkbox" ' . $woo_sku_disable . ' id="' . $id . '-sku" name="' . $id . '[woo][sku]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search product SKU", 'add-search-to-menu' ) . '</label></p>' ;
                $checked = ( isset( $includes['woo']['variation'] ) && $includes['woo']['variation'] ? 1 : 0 );
                echo  '<p class="check-radio"><label for="' . $id . '-variation" ><input class="_is_includes-woocommerce" type="checkbox" ' . $woo_sku_disable . ' id="' . $id . '-variation" name="' . $id . '[woo][variation]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search product variation", 'add-search-to-menu' ) . '</label>' ;
                echo  IS_Admin::pro_link( 'pro_plus' ) ;
                if ( '' !== $woo_sku_disable ) {
                    echo  '</div>' ;
                }
                echo  '</p>' ;
            }
            
            
            if ( 'attachment' == $post_type && empty($selected_pt) ) {
                global  $wp_version ;
                
                if ( 4.9 <= $wp_version ) {
                    
                    if ( !isset( $excludes['post_file_type'] ) ) {
                        echo  '<br />' ;
                        $file_types = get_allowed_mime_types();
                        
                        if ( !empty($file_types) ) {
                            $file_type_disable = ( is_fs()->is_plan_or_trial( 'pro_plus' ) && $this->is_premium_plugin ? '' : ' disabled ' );
                            if ( '' !== $file_type_disable ) {
                                echo  '<div class="upgrade-parent">' ;
                            }
                            ksort( $file_types );
                            $html = '<br /><div class="is-mime">';
                            $html .= '<input class="list-search wide" placeholder="' . __( "Search..", 'add-search-to-menu' ) . '" type="text">';
                            $html .= '<select class="_is_includes-post_file_type" name="' . $id . '[post_file_type][]" ' . $file_type_disable . ' multiple size="8" >';
                            foreach ( $file_types as $key => $file_type ) {
                                $checked = ( isset( $includes['post_file_type'] ) && in_array( $file_type, $includes['post_file_type'] ) ? $file_type : 0 );
                                $html .= '<option value="' . esc_attr( $file_type ) . '" ' . selected( $file_type, $checked, false ) . '>' . esc_html( $key ) . '</option>';
                            }
                            $html .= '</select>';
                            echo  IS_Admin::pro_link( 'pro_plus' ) ;
                            $html .= '<br /><label for="' . $id . '-post_file_type" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'add-search-to-menu' ) . '</label><br />';
                            
                            if ( isset( $includes['post_file_type'] ) ) {
                                $html .= __( 'Selected File Types :', 'add-search-to-menu' );
                                foreach ( $includes['post_file_type'] as $post_file_type ) {
                                    $html .= '<br /><span style="font-size: 11px;">' . $post_file_type . '</span>';
                                }
                            }
                            
                            $html .= '</div>';
                            $checked = ( isset( $includes['post_file_type'] ) && !empty($includes['post_file_type']) ? 'selected' : 'all' );
                            echo  '<p class="check-radio is-mime-radio"><label for="mime-search_all" ><input class="is-mime-select" type="radio" id="mime-search_all" name="mime_search_radio" value="all" ' . checked( 'all', $checked, false ) . '/>' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search all MIME types", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio is-mime-radio"><label for="mime-search_selected" ><input class="is-mime-select" type="radio" id="mime-search_selected" name="mime_search_radio" value="selected" ' . checked( 'selected', $checked, false ) . '/>' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search only selected  MIME types", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  $html ;
                            echo  '<span class="search-attachments-wrapper">' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_images"><input class="search-attachments" type="checkbox" id="' . $id . '-search_images" name="search_images" value="1" checked="checked" />' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search Images", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_videos"><input class="search-attachments" type="checkbox" id="' . $id . '-search_videos" name="search_videos" value="1" checked="checked" />' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search Videos", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_audios"><input class="search-attachments" type="checkbox" id="' . $id . '-search_audios" name="search_audios" value="1" checked="checked" />' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search Audios", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_text"><input class="search-attachments" type="checkbox" id="' . $id . '-search_text" name="search_text" value="1" checked="checked" />' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search Text Files", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_pdfs"><input class="search-attachments" type="checkbox" id="' . $id . '-search_pdfs" name="search_pdfs" value="1" checked="checked" />' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search PDF Files", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_docs"><input class="search-attachments" type="checkbox" id="' . $id . '-search_docs" name="search_docs" value="1" checked="checked"/>' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search Document Files", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '</span>' ;
                            if ( '' !== $file_type_disable ) {
                                echo  '</div>' ;
                            }
                        }
                    
                    } else {
                        echo  '<br /><span class="notice-is-info">' . sprintf( esc_html__( "This search form is configured in the %s section to not search specific MIME types.", 'add-search-to-menu' ), $this->inc_exc_url( 'excludes' ) ) . '</span>' ;
                    }
                
                } else {
                    echo  '<br /><span class="notice-is-info">' . __( 'You are using WordPress version less than 4.9 which does not support searching by MIME type.', 'add-search-to-menu' ) . '</span>' ;
                }
            
            }
            
            ?>
			</div></div>
                        <?php 
        }
        ?>

			<h3 scope="row">
                            <label for="<?php 
        echo  $id ;
        ?>-extras"><?php 
        echo  esc_html( __( 'Extras', 'add-search-to-menu' ) ) ;
        ?></label>
                            <span class="is-actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'add-search-to-menu' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'add-search-to-menu' );
        ?></a></span>
			</h3>
			<div><div class="includes_extras">
			<h4 scope="row" class="is-first-title">
				<label for="<?php 
        echo  $id ;
        ?>-search_content"><?php 
        echo  esc_html( __( 'Search Content', 'add-search-to-menu' ) ) ;
        ?></label>
			</h4>
			<?php 
        $checked = ( $default_search || isset( $includes['search_title'] ) && $includes['search_title'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-search_title"><input class="_is_includes-post_type" type="checkbox" id="' . $id . '-search_title" name="' . $id . '[search_title]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . sprintf( esc_html__( "Search post title %s( File title )%s", 'add-search-to-menu' ), '<i>', '</i>' ) . '</label></p>' ;
        $checked = ( $default_search || isset( $includes['search_content'] ) && $includes['search_content'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-search_content"><input class="_is_includes-post_type" type="checkbox" id="' . $id . '-search_content" name="' . $id . '[search_content]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . sprintf( esc_html__( "Search post content %s( File description )%s", 'add-search-to-menu' ), '<i>', '</i>' ) . '</label></p>' ;
        $checked = ( $default_search || isset( $includes['search_excerpt'] ) && $includes['search_excerpt'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-search_excerpt"><input class="_is_includes-post_type" type="checkbox" id="' . $id . '-search_excerpt" name="' . $id . '[search_excerpt]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . sprintf( esc_html__( "Search post excerpt %s( File caption )%s", 'add-search-to-menu' ), '<i>', '</i>' ) . '</label></p>' ;
        $checked = ( isset( $includes['search_tax_title'] ) && $includes['search_tax_title'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-search_tax_title" ><input class="_is_includes-tax_query" type="checkbox" id="' . $id . '-search_tax_title" name="' . $id . '[search_tax_title]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . sprintf( esc_html__( "Search category/tag title %s( Displays posts of the category/tag )%s", 'add-search-to-menu' ), '<i>', '</i>' ) . '</label></p>' ;
        $checked = ( isset( $includes['search_tax_desp'] ) && $includes['search_tax_desp'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-search_tax_desp" ><input class="_is_includes-tax_query" type="checkbox" id="' . $id . '-search_tax_desp" name="' . $id . '[search_tax_desp]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . sprintf( esc_html__( "Search category/tag description %s( Displays posts of the category/tag )%s", 'add-search-to-menu' ), '<i>', '</i>' ) . '</label></p>' ;
        
        if ( isset( $includes['tax_query'] ) ) {
            $tax_rel_disable = '';
            
            if ( isset( $includes['tax_post_type'] ) ) {
                $temp = array();
                foreach ( $includes['tax_query'] as $key => $value ) {
                    if ( isset( $includes['tax_post_type'][$key] ) && (empty($temp) || !in_array( $includes['tax_post_type'][$key], $temp )) ) {
                        array_push( $temp, $includes['tax_post_type'][$key] );
                    }
                    
                    if ( count( $temp ) > 1 ) {
                        $tax_rel_disable = 'disabled';
                        $includes['tax_rel'] = "OR";
                        break;
                    }
                
                }
            }
            
            echo  '<p class="check-radio">' ;
            
            if ( 'disabled' == $tax_rel_disable ) {
                echo  '<br />' ;
                $content = __( 'Note: The below option is disabled and set to OR as you have configured the search form to search multiple taxonomies.', 'add-search-to-menu' );
                IS_Help::help_info( $content );
            }
            
            $checked = ( isset( $includes['tax_rel'] ) && "AND" == $includes['tax_rel'] ? "AND" : "OR" );
            echo  '<label for="' . $id . '-tax_rel_and" ><input class="_is_includes-tax_query" type="radio" id="' . $id . '-tax_rel_and" ' . $tax_rel_disable . ' name="' . $id . '[tax_rel]" value="AND" ' . checked( 'AND', $checked, false ) . '/>' ;
            echo  '<span class="toggle-check-text"></span>' . esc_html__( "AND - Search posts having all the above selected category terms", 'add-search-to-menu' ) . '</label></p>' ;
            echo  '<p class="check-radio"><label for="' . $id . '-tax_rel_or" ><input class="_is_includes-tax_query" type="radio" id="' . $id . '-tax_rel_or" ' . $tax_rel_disable . ' name="' . $id . '[tax_rel]" value="OR" ' . checked( 'OR', $checked, false ) . '/>' ;
            echo  '<span class="toggle-check-text"></span>' . esc_html__( "OR - Search posts having any one of the above selected category terms", 'add-search-to-menu' ) . '</label></p>' ;
        }
        
        ?>
			</div>

			<h4 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-post_status"><?php 
        echo  esc_html( __( 'Post Status', 'add-search-to-menu' ) ) ;
        ?></label>
			</h4>
			<div>
				<?php 
        $content = __( 'Search posts having selected post statuses.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $post_statuses = get_post_stati();
        $post_status_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
        
        if ( !empty($post_statuses) ) {
            if ( '' !== $post_status_disable ) {
                echo  IS_Admin::pro_link() ;
            }
            echo  '<div class="is-cb-dropdown">' ;
            echo  '<div class="is-cb-title">' ;
            if ( $default_search || !isset( $includes['post_status'] ) || empty($includes['post_status']) ) {
                $includes = array(
                    'post_status' => array(
                    'publish' => 'publish',
                    'inherit' => 'inherit',
                ),
                );
            }
            echo  '<span style="display:none;" class="is-cb-select">' . __( 'Select Post Status', 'add-search-to-menu' ) . '</span><span class="is-cb-titles">' ;
            foreach ( $includes['post_status'] as $post_status2 ) {
                echo  '<span title="' . esc_html( $post_status2 ) . '"> ' . str_replace( '-', ' ', esc_html( $post_status2 ) ) . '</span>' ;
            }
            echo  '</span>' ;
            echo  '</div>' ;
            echo  '<div class="is-cb-multisel">' ;
            foreach ( $post_statuses as $key => $post_status ) {
                $checked = ( isset( $includes['post_status'][esc_attr( $key )] ) ? $includes['post_status'][esc_attr( $key )] : 0 );
                $temp = ( 'publish' === $post_status || 'inherit' === $post_status ? '' : $post_status_disable );
                echo  '<label for="' . $id . '-post_status-' . esc_attr( $key ) . '"><input class="_is_includes-post_status" type="checkbox" ' . $temp . ' id="' . $id . '-post_status-' . esc_attr( $key ) . '" name="' . $id . '[post_status][' . esc_attr( $key ) . ']" value="' . esc_attr( $key ) . '" ' . checked( $key, $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span> ' . ucwords( str_replace( '-', ' ', esc_html( $post_status ) ) ) . '</label>' ;
            }
            echo  '</div></div>' ;
        }
        
        ?>
			</div></div>
			<h4 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-author"><?php 
        echo  esc_html( __( 'Authors', 'add-search-to-menu' ) ) ;
        ?></label>
			</h4>
			<div>
				<?php 
        $content = __( 'Search posts created by selected authors.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $author_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
        
        if ( !isset( $excludes['author'] ) ) {
            $authors = get_users( array(
                'fields'  => array( 'ID', 'display_name' ),
                'orderby' => 'post_count',
                'order'   => 'DESC',
            ) );
            
            if ( !empty($authors) ) {
                if ( '' !== $author_disable ) {
                    echo  '<div class="upgrade-parent">' . IS_Admin::pro_link() ;
                }
                echo  '<div class="is-cb-dropdown">' ;
                echo  '<div class="is-cb-title">' ;
                
                if ( !isset( $includes['author'] ) || empty($includes['author']) ) {
                    echo  '<span class="is-cb-select">' . __( 'Searches all author posts', 'add-search-to-menu' ) . '</span><span class="is-cb-titles"></span>' ;
                } else {
                    echo  '<span style="display:none;" class="is-cb-select">' . __( 'Searches all author posts', 'add-search-to-menu' ) . '</span><span class="is-cb-titles">' ;
                    foreach ( $includes['author'] as $author2 ) {
                        $display_name = get_userdata( $author2 );
                        if ( $display_name ) {
                            echo  '<span title="' . ucfirst( esc_html( $display_name->display_name ) ) . '"> ' . esc_html( $display_name->display_name ) . '</span>' ;
                        }
                    }
                    echo  '</span>' ;
                }
                
                echo  '</div>' ;
                echo  '<div class="is-cb-multisel">' ;
                foreach ( $authors as $author ) {
                    $post_count = count_user_posts( $author->ID );
                    // Move on if user has not published a post (yet).
                    if ( !$post_count ) {
                        continue;
                    }
                    $checked = ( isset( $includes['author'][esc_attr( $author->ID )] ) ? $includes['author'][esc_attr( $author->ID )] : 0 );
                    echo  '<label for="' . $id . '-author-' . esc_attr( $author->ID ) . '"><input class="_is_includes-author" type="checkbox" ' . $author_disable . ' id="' . $id . '-author-' . esc_attr( $author->ID ) . '" name="' . $id . '[author][' . esc_attr( $author->ID ) . ']" value="' . esc_attr( $author->ID ) . '" ' . checked( $author->ID, $checked, false ) . '/>' ;
                    echo  '<span class="toggle-check-text"></span> ' . ucfirst( esc_html( $author->display_name ) ) . '</label>' ;
                }
                echo  '</div></div>' ;
            }
        
        } else {
            echo  '<br /><span class="notice-is-info">' . sprintf( esc_html__( "This search form is configured in the %s section to not search for specific author posts.", 'add-search-to-menu' ), $this->inc_exc_url( 'excludes' ) ) . '</span>' ;
        }
        
        if ( '' !== $author_disable ) {
            echo  '</div>' ;
        }
        $checked = ( isset( $includes['search_author'] ) && $includes['search_author'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-search_author" ><input class="_is_includes-author" type="checkbox" id="' . $id . '-search_author" name="' . $id . '[search_author]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search author Display Name and display the posts created by that author", 'add-search-to-menu' ) . '</label></p>' ;
        ?>
			</div></div>

			<h4 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-comment_count"><?php 
        echo  esc_html( __( 'Comments', 'add-search-to-menu' ) ) ;
        ?></label>
			</h4>
			<div>
				<?php 
        echo  '<div>' ;
        $comment_count_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
        if ( '' !== $comment_count_disable ) {
            echo  '<div class="upgrade-parent">' . IS_Admin::pro_link() ;
        }
        echo  '<label for="' . $id . '-comment_count-compare"> ' . esc_html( __( 'Search posts having number of comments', 'add-search-to-menu' ) ) . '</label><select class="_is_includes-comment_count" name="' . $id . '[comment_count][compare]" ' . $comment_count_disable . ' style="min-width: 50px;">' ;
        $checked = ( isset( $includes['comment_count']['compare'] ) ? htmlspecialchars_decode( $includes['comment_count']['compare'] ) : '=' );
        $compare = array(
            '=',
            '!=',
            '>',
            '>=',
            '<',
            '<='
        );
        foreach ( $compare as $d ) {
            echo  '<option value="' . htmlspecialchars_decode( $d ) . '" ' . selected( $d, $checked, false ) . '>' . esc_html( $d ) . '</option>' ;
        }
        echo  '</select>' ;
        echo  '<select class="_is_includes-comment_count" name="' . $id . '[comment_count][value]" ' . $comment_count_disable . ' >' ;
        $checked = ( isset( $includes['comment_count']['value'] ) ? $includes['comment_count']['value'] : 'na' );
        echo  '<option value="na" ' . selected( 'na', $checked, false ) . '>' . esc_html( __( 'NA', 'add-search-to-menu' ) ) . '</option>' ;
        for ( $d = 0 ;  $d <= 999 ;  $d++ ) {
            echo  '<option value="' . $d . '" ' . selected( $d, $checked, false ) . '>' . $d . '</option>' ;
        }
        echo  '</select>' ;
        if ( '' !== $comment_count_disable ) {
            echo  '</div>' ;
        }
        $checked = ( isset( $includes['search_comment'] ) && $includes['search_comment'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-search_comment" ><input class="_is_includes-comment_count" type="checkbox" id="' . $id . '-search_comment" name="' . $id . '[search_comment]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search approved comment content", 'add-search-to-menu' ) . '</label></p>' ;
        ?>
			</div></div>

			<h4 scope="row">
                            <label for="<?php 
        echo  $id ;
        ?>-has_password"><?php 
        echo  esc_html( __( 'Password Protected', 'add-search-to-menu' ) ) ;
        ?></label>
			</h4>
			<div><div>
				<?php 
        $checked = ( isset( $includes['has_password'] ) ? $includes['has_password'] : 'null' );
        echo  '<p class="check-radio"><label for="' . $id . '-has_password" ><input class="_is_includes-has_password" type="radio" id="' . $id . '-has_password" name="' . $id . '[has_password]" value="null" ' . checked( 'null', $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search posts with or without passwords", 'add-search-to-menu' ) . '</label></p>' ;
        echo  '<p class="check-radio"><label for="' . $id . '-has_password_1" ><input class="_is_includes-has_password" type="radio" id="' . $id . '-has_password_1" name="' . $id . '[has_password]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search posts with passwords", 'add-search-to-menu' ) . '</label></p>' ;
        echo  '<p class="check-radio"><label for="' . $id . '-has_password_0" ><input class="_is_includes-has_password" type="radio" id="' . $id . '-has_password_0" name="' . $id . '[has_password]" value="0" ' . checked( 0, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search posts without passwords", 'add-search-to-menu' ) . '</label></p>' ;
        ?>
			</div></div>
			<h4 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-date_query"><?php 
        echo  esc_html( __( 'Date', 'add-search-to-menu' ) ) ;
        ?></label>
			</h4>
			<div>
				<?php 
        $content = __( 'Search posts created only in the specified date range.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $range = array( 'after', 'before' );
        foreach ( $range as $value ) {
            $col_title = ( 'after' == $value ? __( 'From', 'add-search-to-menu' ) : __( 'To', 'add-search-to-menu' ) );
            echo  '<div class="col-wrapper ' . $value . '"><div class="col-title">' . $col_title . '</div>' ;
            $checked = ( isset( $includes['date_query'][$value]['date'] ) ? $includes['date_query'][$value]['date'] : '' );
            echo  '<input type="text" id="is-' . $value . '-datepicker" name="' . $id . '[date_query][' . $value . '][date]" value="' . $checked . '">' ;
            echo  '</div>' ;
        }
        ?>
			</div></div>
		</div>

		</div>

		</div>

	<?php 
    }
    
    public function customize_panel( $post )
    {
        $id = '_is_customize';
        $settings = $post->prop( $id );
        $enable_customize = ( isset( $settings['enable_customize'] ) ? $settings['enable_customize'] : false );
        $is_ajax = $post->prop( '_is_ajax' );
        ?>

		<h4 class="panel-desc"><?php 
        _e( "Design Search Form Colors, Text and Style", 'add-search-to-menu' );
        ?></h4>
		<div class="search-form-editor-box" id="<?php 
        echo  esc_attr( $id ) ;
        ?>">
			<?php 
        
        if ( 'default-search-form' == $post->name() && !isset( $is_ajax['enable_ajax'] ) ) {
            ?>
			<p class="check-radio enable-ajax-customize">
				<label for="<?php 
            echo  esc_attr( $id ) ;
            ?>-enable_customize">
					<input class="<?php 
            echo  esc_attr( $id ) ;
            ?>-enable_customize" type="checkbox" id="<?php 
            echo  esc_attr( $id ) ;
            ?>-enable_customize" name="<?php 
            echo  esc_attr( $id ) ;
            ?>[enable_customize]" value="1" <?php 
            checked( 1, $enable_customize );
            ?> data-depends="[<?php 
            echo  esc_attr( $id ) ;
            ?>-description_source_wrap,<?php 
            echo  esc_attr( $id ) ;
            ?>-description_length_wrap]"/>
					<span class="toggle-check-text"></span>
					<?php 
            esc_html_e( 'Enable Search Form Customization', 'add-search-to-menu' );
            ?>
				</label>
			</p>
			<?php 
        } else {
            $enable_customize = true;
        }
        
        $field_class = ( $enable_customize ? '' : 'is-field-disabled' );
        ?>
			<div class="form-table form-table-panel-customize">

				<!-- Search Results -->
				<h3 scope="row">
					<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-customizer"><?php 
        echo  esc_html( __( 'Customizer', 'add-search-to-menu' ) ) ;
        ?></label>
				</h3>
				<div class="is-field-wrap <?php 
        echo  esc_attr( $field_class ) ;
        ?>">
					<?php 
        
        if ( 'default-search-form' == $post->name() && !isset( $is_ajax['enable_ajax'] ) ) {
            ?>
					<span class="is-field-disabled-message"><span class="message"><?php 
            _e( 'Enable Search Form Customization', 'add-search-to-menu' );
            ?></span></span>
					<?php 
        }
        
        ?>
                                        <?php 
        IS_Help::help_info( __( 'Use below customizer to customize search form colors, text and search form style.', 'add-search-to-menu' ) );
        ?>
					<div>
                                            <?php 
        
        if ( isset( $_GET['post'] ) ) {
            $customizer_url = admin_url( 'customize.php?autofocus[section]=is_section_' . $_GET['post'] );
            if ( !$enable_customize ) {
                $customizer_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            }
            echo  '<a style="font-size: 20px;font-weight: 800; padding: 25px 0;display: block;text-align: center;box-shadow:none;"class="is-customize-link" href="' . esc_url( $customizer_url ) . '">' . __( "Search Form Customizer", "ivory-search" ) . '</a>' ;
        }
        
        ?>
					</div>
				</div>
			</div>
		</div>

		<?php 
    }
    
    public function ajax_panel( $post )
    {
        $id = '_is_ajax';
        $settings = $post->prop( $id );
        $includes = $post->prop( '_is_includes' );
        // If not have any settings saved then set default value for fields.
        
        if ( empty($settings) ) {
            $show_description = true;
            $show_details_box = true;
            $show_more_result = true;
            $show_more_func = false;
            $show_price = true;
            $show_matching_categories = true;
            $show_image = true;
            $search_results = 'both';
        } else {
            $show_description = ( isset( $settings['show_description'] ) && $settings['show_description'] ? 1 : 0 );
            $show_details_box = ( isset( $settings['show_details_box'] ) ? $settings['show_details_box'] : false );
            $show_more_result = ( isset( $settings['show_more_result'] ) && $settings['show_more_result'] ? 1 : 0 );
            $show_more_func = ( isset( $settings['show_more_func'] ) && $settings['show_more_func'] ? 1 : 0 );
            $show_price = ( isset( $settings['show_price'] ) && $settings['show_price'] ? 1 : 0 );
            $show_matching_categories = ( isset( $settings['show_matching_categories'] ) && $settings['show_matching_categories'] ? 1 : 0 );
            $show_image = ( isset( $settings['show_image'] ) ? 1 : 0 );
            $search_results = ( isset( $settings['search_results'] ) ? $settings['search_results'] : 'both' );
        }
        
        $enable_ajax = ( isset( $settings['enable_ajax'] ) ? $settings['enable_ajax'] : false );
        $description_source = ( isset( $settings['description_source'] ) ? $settings['description_source'] : 'excerpt' );
        $description_length = ( isset( $settings['description_length'] ) ? $settings['description_length'] : 20 );
        $hide_price_out_of_stock = ( isset( $settings['hide_price_out_of_stock'] ) && $settings['hide_price_out_of_stock'] ? 1 : 0 );
        $show_sale_badge = ( isset( $settings['show_sale_badge'] ) && $settings['show_sale_badge'] ? 1 : 0 );
        $show_categories = ( isset( $settings['show_categories'] ) && $settings['show_categories'] ? 1 : 0 );
        $show_tags = ( isset( $settings['show_tags'] ) && $settings['show_tags'] ? 1 : 0 );
        $show_sku = ( isset( $settings['show_sku'] ) && $settings['show_sku'] ? 1 : 0 );
        $show_matching_tags = ( isset( $settings['show_matching_tags'] ) && $settings['show_matching_tags'] ? 1 : 0 );
        $show_stock_status = ( isset( $settings['show_stock_status'] ) && $settings['show_stock_status'] ? 1 : 0 );
        $show_featured_icon = ( isset( $settings['show_featured_icon'] ) && $settings['show_featured_icon'] ? 1 : 0 );
        $nothing_found_text = ( isset( $settings['nothing_found_text'] ) ? $settings['nothing_found_text'] : __( 'Nothing found', 'add-search-to-menu' ) );
        $min_no_for_search = ( isset( $settings['min_no_for_search'] ) ? $settings['min_no_for_search'] : 1 );
        $view_all_results = ( isset( $settings['view_all_results'] ) ? $settings['view_all_results'] : false );
        $view_all_text = ( isset( $settings['view_all_text'] ) ? $settings['view_all_text'] : __( 'View All', 'add-search-to-menu' ) );
        // Result Layout.
        $result_box_max_height = ( isset( $settings['result_box_max_height'] ) ? $settings['result_box_max_height'] : 400 );
        $more_result_text = ( isset( $settings['more_result_text'] ) ? $settings['more_result_text'] : __( 'More Results..', 'add-search-to-menu' ) );
        $show_author = ( isset( $settings['show_author'] ) && $settings['show_author'] ? 1 : 0 );
        $show_date = ( isset( $settings['show_date'] ) && $settings['show_date'] ? 1 : 0 );
        // Details Box.
        $product_list = ( isset( $settings['product_list'] ) ? $settings['product_list'] : 'all' );
        $order_by = ( isset( $settings['order_by'] ) ? $settings['order_by'] : 'date' );
        $order = ( isset( $settings['order'] ) ? $settings['order'] : 'desc' );
        $field_class = ( $enable_ajax ? '' : 'is-field-disabled' );
        ?>
		<h4 class="panel-desc"><?php 
        _e( "Configure AJAX Search", 'add-search-to-menu' );
        ?></h4>
		<div class="search-form-editor-box" id="<?php 
        echo  esc_attr( $id ) ;
        ?>">

			<p class="check-radio enable-ajax-customize">
				<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-enable_ajax">
					<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-enable_ajax" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-enable_ajax" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[enable_ajax]" value="1" <?php 
        checked( 1, $enable_ajax );
        ?> data-depends="[<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_wrap,<?php 
        echo  esc_attr( $id ) ;
        ?>-description_length_wrap]"/>
					<span class="toggle-check-text"></span>
					<?php 
        esc_html_e( 'Enable AJAX Search', 'add-search-to-menu' );
        ?>
				</label>
			</p>

			<div class="form-table form-table-panel-ajax">
				<!-- Search Results -->
				<h3 scope="row">
					<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-search-form-search-results"><?php 
        esc_html_e( 'AJAX Search Results', 'add-search-to-menu' );
        ?></label>
					<span class="is-actions">
						<a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'add-search-to-menu' );
        ?></a>
						<a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'add-search-to-menu' );
        ?></a>
					</span>
				</h3>
				<div class="is-field-wrap <?php 
        echo  $field_class ;
        ?>">
					<span class="is-field-disabled-message"><span class="message"><?php 
        _e( 'Enable AJAX Search', 'add-search-to-menu' );
        ?></span></span>
                                        <?php 
        IS_Help::help_info( __( 'Display selected content in the search results.', 'add-search-to-menu' ) );
        ?>
					<!-- Description -->
					<div class="is-field <?php 
        echo  esc_attr( $id ) ;
        ?>-description_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_description">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_description" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_description" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_description]" value="1" <?php 
        checked( 1, $show_description );
        ?> data-depends="[<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_wrap,<?php 
        echo  esc_attr( $id ) ;
        ?>-description_length_wrap]"/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Description', 'add-search-to-menu' );
        ?>
							</label>
						</p>
					</div>
					<div class="is-field <?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_excerpt" >
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_excerpt" type="radio" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_excerpt" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[description_source]" value="excerpt" <?php 
        checked( 'excerpt', $description_source );
        ?>/>
								<span class="toggle-check-text"></span><?php 
        esc_html_e( "Excerpt", 'add-search-to-menu' );
        ?>
							</label>
						</p>
						<p class="check-radio" style="margin-top: .5em;">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_content" >
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_content" type="radio" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_content" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[description_source]" value="content" <?php 
        checked( 'content', $description_source );
        ?>/>
								<span class="toggle-check-text"></span><?php 
        esc_html_e( "Content", 'add-search-to-menu' );
        ?>
							</label>
						</p>
					</div>

					<!-- Description Length -->
					<div class="is-field <?php 
        echo  esc_attr( $id ) ;
        ?>-description_length_wrap"><br />
                                            <input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_length" min="1" type="number" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_length" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[description_length]" value="<?php 
        echo  esc_attr( $description_length ) ;
        ?>"/>
                                            <p class="description"><?php 
        _e( 'Description Length.', 'add-search-to-menu' );
        ?></p>
					</div>
					<!-- Image -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_image_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_image">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_image" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_image" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_image]" value="1" <?php 
        checked( 1, $show_image );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Image', 'add-search-to-menu' );
        ?>
							</label>
						</p>
					</div>

					<!-- Categories -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-categories_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_categories">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_categories" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_categories" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_categories]" value="1" <?php 
        checked( 1, $show_categories );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Categories', 'add-search-to-menu' );
        ?>
							</label>
						</p>
					</div>

					<!-- Tags -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-tags_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_tags">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_tags" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_tags" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_tags]" value="1" <?php 
        checked( 1, $show_tags );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Tags', 'add-search-to-menu' );
        ?>
							</label>
						</p>
					</div>

					<!-- Show Author in Results -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_author_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_author">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_author" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_author" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_author]" value="1" <?php 
        checked( 1, $show_author );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Author', 'add-search-to-menu' );
        ?>
							</label>
						</p>
					</div>
	
					<!-- Show Date in Results -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_date_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_date">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_date" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_date" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_date]" value="1" <?php 
        checked( 1, $show_date );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Date', 'add-search-to-menu' );
        ?>
							</label>
						</p>
					</div>
					<!-- Minimum Number of Characters -->
					<br /><div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-min_no_for_search_wrap">
                                            <input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-min_no_for_search" type="number" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-min_no_for_search" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[min_no_for_search]" value="<?php 
        echo  $min_no_for_search ;
        ?>" />
                                            <p class="description"><?php 
        _e( 'Minimum number of characters required to run ajax search.', 'add-search-to-menu' );
        ?></p>
					</div>
					<!-- Box Max Height -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-result_box_max_height_wrap">
                                            <input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-result_box_max_height" type="number" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-result_box_max_height" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[result_box_max_height]" value="<?php 
        echo  esc_attr( $result_box_max_height ) ;
        ?>"/>
                                            <p class="description"><?php 
        _e( 'Search results box max height.', 'add-search-to-menu' );
        ?></p>
					</div>
                                        <br />
                                        <?php 
        IS_Help::help_info( __( 'Configure the plugin text displayed in the search results.', 'add-search-to-menu' ) );
        ?>
					<!-- Nothing Found Text -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-nothing_found_text_wrap">
						<p>
                                                    <input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-nothing_found_text" type="text" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-nothing_found_text" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[nothing_found_text]" value="<?php 
        echo  $nothing_found_text ;
        ?>" />
                                                    <span class="description"><?php 
        _e( 'Text when there is no search results. HTML tags is allowed.', 'add-search-to-menu' );
        ?></span>
						</p>
					</div>
					<!-- Show More Result -->
					<br /><div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_result_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_result">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_result" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_result" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_more_result]" value="1" <?php 
        checked( 1, $show_more_result );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Show \'More Results..\' text in the bottom of the search results box', 'add-search-to-menu' );
        ?>
							</label>
						</p>
					</div>
					<!-- More Result Text -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-more_result_text_wrap">
						<p>
							<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-more_result_text" type="text" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-more_result_text" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[more_result_text]" value="<?php 
        echo  esc_attr( $more_result_text ) ;
        ?>"/>
                                                        <span class="description"><?php 
        _e( 'Text for the "More Results..".', 'add-search-to-menu' );
        ?></span>
						</p>
					</div>
					<!-- Show More Result Functionality  -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_func_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_func">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_func" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_func" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_more_func]" value="1" <?php 
        checked( 1, $show_more_func );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Redirect to search results page clicking on the \'More Results..\' text', 'add-search-to-menu' );
        ?>
							</label>
						</p>
					</div>
					<!-- Show 'View All Results' -->
					<!--<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_results_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_results">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_results" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_results" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[view_all_results]" value="1" <?php 
        checked( 1, $view_all_results );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'View All Result - Show link to search results page at the bottom of search results block.', 'add-search-to-menu' );
        ?>
							</label>
						</p>
					</div>-->

					<!-- View All Text -->
					<!--<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_text_wrap">
						<p>
							<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_text" type="text" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_text" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[view_all_text]" value="<?php 
        echo  esc_attr( $view_all_text ) ;
        ?>"/>
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_text"><?php 
        esc_html_e( 'Text for the "View All" which shown at the bottom of the search result.', 'add-search-to-menu' );
        ?></label>
						</p>
					</div>-->
                                        <!-- Search Button Functionality -->
                                        <br />
                                        <?php 
        IS_Help::help_info( __( 'Configure how the search button should work clicking on it.', 'add-search-to-menu' ) );
        ?>
					<div>
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-both" >
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-search_results" type="radio" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-both" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[search_results]" value="both" <?php 
        checked( 'both', $search_results );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( "Search button displays search results page", 'add-search-to-menu' );
        ?>
							</label>
						</p>
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-ajax_results" >
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-search_results" type="radio" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-ajax_results" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[search_results]" value="ajax_results" <?php 
        checked( 'ajax_results', $search_results );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( "Search button displays ajax search results", 'add-search-to-menu' );
        ?>
							</label>
						</p>
					</div>
				</div>

				<!-- WooCommerce -->
				<h3 scope="row">
					<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-search-form-woocommerce"><?php 
        esc_html_e( 'WooCommerce', 'add-search-to-menu' );
        ?></label>
				</h3>
				<div class="is-field-wrap <?php 
        echo  $field_class ;
        ?>">
					<?php 
        
        if ( IS_Help::is_woocommerce_inactive() ) {
            IS_Help::woocommerce_inactive_field_notice();
        } else {
            
            if ( !isset( $includes['post_type'] ) || !in_array( 'product', $includes['post_type'] ) ) {
                echo  '<span class="notice-is-info">' . sprintf( esc_html__( "Please first configure this search form in the %s section to search WooCommerce product post type.", 'add-search-to-menu' ), $this->inc_exc_url( 'includes' ) ) . '</span><br />' ;
            } else {
                ?>
						<span class="is-field-disabled-message"><span class="message"><?php 
                _e( 'Enable AJAX Search', 'add-search-to-menu' );
                ?></span></span>
                                                <?php 
                IS_Help::help_info( __( 'Display selected WooCommerce content in the search results.', 'add-search-to-menu' ) );
                ?>
						<!-- Price -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-price_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_price">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_price" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_price" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_price]" value="1" <?php 
                checked( 1, $show_price );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Price', 'add-search-to-menu' );
                ?>
								</label>
							</p>
						</div>

						<!-- Price Out of Stock -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-price_out_of_stock_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-hide_price_out_of_stock">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-hide_price_out_of_stock" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-hide_price_out_of_stock" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[hide_price_out_of_stock]" value="1" <?php 
                checked( 1, $hide_price_out_of_stock );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Hide Price for Out of Stock Products', 'add-search-to-menu' );
                ?>
								</label>
							</p>
						</div>

						<!-- Sale Badge -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-sale_badge_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sale_badge">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sale_badge" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sale_badge" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_sale_badge]" value="1" <?php 
                checked( 1, $show_sale_badge );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Sale Badge', 'add-search-to-menu' );
                ?>
								</label>
							</p>
						</div>

						<!-- SKU -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-sku_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sku">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sku" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sku" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_sku]" value="1" <?php 
                checked( 1, $show_sku );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'SKU', 'add-search-to-menu' );
                ?>
								</label>
							</p>
						</div>

						<!-- Stock Status -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-stock_status_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_stock_status">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_stock_status" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_stock_status" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_stock_status]" value="1" <?php 
                checked( 1, $show_stock_status );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Stock Status', 'add-search-to-menu' );
                ?>
								</label>
							</p>
						</div>

						<!-- Featured Icon -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-featured_icon_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_featured_icon">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_featured_icon" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_featured_icon" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_featured_icon]" value="1" <?php 
                checked( 1, $show_featured_icon );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Featured Icon', 'add-search-to-menu' );
                ?>
								</label>
							</p>
						</div>

						<!-- Display Matching Categories -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-matching_categories_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_categories">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_categories" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_categories" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_matching_categories]" value="1" <?php 
                checked( 1, $show_matching_categories );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Matching Categories', 'add-search-to-menu' );
                ?>
								</label>
							</p>
						</div>

						<!-- Display Matching Tags -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-matching_tags_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_tags">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_tags" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_tags" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_matching_tags]" value="1" <?php 
                checked( 1, $show_matching_tags );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Matching Tags', 'add-search-to-menu' );
                ?>
								</label>
							</p>
						</div>

						<!-- Show Details Box -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-details_box_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_details_box">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_details_box" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_details_box" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_details_box]" value="1" <?php 
                checked( 1, $show_details_box );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Details Box', 'add-search-to-menu' );
                ?>
								</label>
							</p>
						</div>
						<!-- Products List -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-product_list_wrap">
                                                        <?php 
                IS_Help::help_info( __( 'Below options only apply to matching categories or tags.', 'add-search-to-menu' ) );
                ?><br />
							<p><label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-product_list">
								<?php 
                esc_html_e( 'Product List', 'add-search-to-menu' );
                ?>
							</label>
							<select class="<?php 
                echo  esc_attr( $id ) ;
                ?>-product_list" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-product_list" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[product_list]">
								<option value="all" <?php 
                selected( $product_list, 'all' );
                ?>><?php 
                _e( 'All Product', 'add-search-to-menu' );
                ?></option>
								<option value="featured" <?php 
                selected( $product_list, 'featured' );
                ?>><?php 
                _e( 'Featured Products', 'add-search-to-menu' );
                ?></option>
								<option value="onsale" <?php 
                selected( $product_list, 'onsale' );
                ?>><?php 
                _e( 'On-sale Products</option>', 'add-search-to-menu' );
                ?></option>
							</select></p>
						</div>

						<!-- Order by -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-order_by_wrap">
							<p><label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-order_by">
								<?php 
                esc_html_e( 'Order by', 'add-search-to-menu' );
                ?>
							</label>
							<select class="<?php 
                echo  esc_attr( $id ) ;
                ?>-order_by" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-order_by" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[order_by]">
								<option value="date" <?php 
                selected( $order_by, 'date' );
                ?>><?php 
                _e( 'Date', 'add-search-to-menu' );
                ?></option>
								<option value="price" <?php 
                selected( $order_by, 'price' );
                ?>><?php 
                _e( 'Price', 'add-search-to-menu' );
                ?></option>
								<option value="rand" <?php 
                selected( $order_by, 'rand' );
                ?>><?php 
                _e( 'Random', 'add-search-to-menu' );
                ?></option>
								<option value="sales" <?php 
                selected( $order_by, 'sales' );
                ?>><?php 
                _e( 'Sales', 'add-search-to-menu' );
                ?></option>
							</select></p>
						</div>

						<!-- Order -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-order_wrap">
							<p><label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-order">
								<?php 
                esc_html_e( 'Order', 'add-search-to-menu' );
                ?>
							</label>
							<select class="<?php 
                echo  esc_attr( $id ) ;
                ?>-order" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-order" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[order]">
								<option value="asc" <?php 
                selected( $order, 'asc' );
                ?>><?php 
                _e( 'ASC', 'add-search-to-menu' );
                ?></option>
								<option value="desc" <?php 
                selected( $order, 'desc' );
                ?>><?php 
                _e( 'DESC', 'add-search-to-menu' );
                ?></option>
							</select></p>
						</div>

					<?php 
            }
        
        }
        
        ?>
				</div>
			</div>
		</div>
		<?php 
    }
    
    public function excludes_panel( $post )
    {
        $id = '_is_excludes';
        $excludes = $post->prop( $id );
        $includes = $post->prop( '_is_includes' );
        $default_search = ( NULL == $post->id() ? true : false );
        ?>
		<h4 class="panel-desc">
			<?php 
        _e( "Exclude Content From Search", 'add-search-to-menu' );
        ?>
		</h4>
		<div class="search-form-editor-box" id="<?php 
        echo  $id ;
        ?>">
		<div class="form-table form-table-panel-excludes">

                    <?php 
        $post_types = get_post_types( array(
            'public'              => true,
            'exclude_from_search' => false,
        ) );
        $post_types2 = get_post_types( '', 'objects' );
        if ( isset( $includes['post_type'] ) && !empty($includes['post_type']) && is_array( $includes['post_type'] ) ) {
            $post_types = array_values( $includes['post_type'] );
        }
        foreach ( $post_types as $key => $post_type ) {
            if ( !isset( $post_types2[$post_type] ) ) {
                continue;
            }
            $accord_title = $post_types2[$post_type]->labels->name;
            
            if ( 'product' == $post_type ) {
                $accord_title .= ' <i>' . __( '( WooCommerce )', 'add-search-to-menu' ) . '</i>';
            } else {
                if ( 'attachment' == $post_type ) {
                    $accord_title .= ' <i>' . __( '( Images, Videos, Audios, Docs, PDFs, Files & Attachments  )', 'add-search-to-menu' ) . '</i>';
                }
            }
            
            ?>
			<h3 scope="row">
                            <label for="<?php 
            echo  $id ;
            ?>-post__not_in"><?php 
            echo  $accord_title ;
            ?></label>
                            <?php 
            
            if ( is_numeric( $key ) && 0 == $key || 'post' === $key ) {
                ?>
                            <span class="is-actions"><a class="expand" href="#"><?php 
                esc_html_e( 'Expand All', 'add-search-to-menu' );
                ?></a><a class="collapse" href="#" style="display:none;"><?php 
                esc_html_e( 'Collapse All', 'add-search-to-menu' );
                ?></a></span>
                            <?php 
            }
            
            ?>
                        </h3>
			<div>
				<?php 
            echo  '<div>' ;
            
            if ( 'product' == $post_type && !class_exists( 'WooCommerce' ) ) {
                IS_Help::woocommerce_inactive_field_notice();
                echo  '</div></div>' ;
                continue;
            }
            
            
            if ( 'attachment' != $post_type || !isset( $includes['post_file_type'] ) ) {
                $posts_found = false;
                $posts_per_page = ( defined( 'DISABLE_IS_LOAD_ALL' ) || isset( $excludes['post__not_in'] ) ? -1 : 100 );
                $posts = get_posts( array(
                    'post_type'      => $post_type,
                    'posts_per_page' => $posts_per_page,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                ) );
                $html = '<div class="is-posts">';
                $selected_pt = array();
                $selected_pt2 = array();
                
                if ( !empty($posts) ) {
                    $posts_found = true;
                    $html .= '<div class="col-wrapper"><div class="col-title">';
                    $col_title = '<span>' . $post_types2[$post_type]->labels->name . '</span>';
                    $temp = '';
                    foreach ( $posts as $post2 ) {
                        $checked = ( isset( $includes['post__in'] ) && in_array( $post2->ID, $includes['post__in'] ) ? $post2->ID : 0 );
                        if ( $checked ) {
                            array_push( $selected_pt2, $post_type );
                        }
                        $checked = ( isset( $excludes['post__not_in'] ) && in_array( $post2->ID, $excludes['post__not_in'] ) ? $post2->ID : 0 );
                        if ( $checked ) {
                            array_push( $selected_pt, $post_type );
                        }
                        $post_title = ( isset( $post2->post_title ) && '' !== $post2->post_title ? esc_html( $post2->post_title ) : $post2->post_name );
                        $temp .= '<option value="' . esc_attr( $post2->ID ) . '" ' . selected( $post2->ID, $checked, false ) . '>' . $post_title . '</option>';
                    }
                    if ( !empty($selected_pt) && in_array( $post_type, $selected_pt ) ) {
                        $col_title = '<strong>' . $col_title . '</strong>';
                    }
                    $html .= $col_title . '<input class="list-search" placeholder="' . __( "Search..", 'add-search-to-menu' ) . '" type="text"></div>';
                    $html .= '<select class="_is_excludes-post__not_in" name="' . $id . '[post__not_in][]" multiple size="8" >';
                    $html .= $temp . '</select>';
                    if ( count( $posts ) >= 100 && !defined( 'DISABLE_IS_LOAD_ALL' ) && !isset( $excludes['post__not_in'] ) ) {
                        $html .= '<div id="' . $post_type . '" class="load-all">' . __( 'Load All', 'add-search-to-menu' ) . '</div>';
                    }
                    $html .= '</div>';
                }
                
                
                if ( !$posts_found ) {
                    $html .= '<br /><span class="notice-is-info">' . sprintf( __( 'No %s created.', 'add-search-to-menu' ), $post_types2[$post_type]->labels->name ) . '</span>';
                } else {
                    $html .= '<br /><label for="' . $id . '-post__not_in" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'add-search-to-menu' ) . '</label><br />';
                }
                
                $html .= '</div>';
                $checked = 'all';
                if ( !empty($selected_pt) && in_array( $post_type, $selected_pt ) ) {
                    $checked = 'selected';
                }
                
                if ( empty($selected_pt2) ) {
                    
                    if ( isset( $includes['post__in'] ) ) {
                        echo  '<span class="notice-is-info">' . sprintf( esc_html__( "The search form is configured in the %s section to only search specific posts of another post type.", 'add-search-to-menu' ), $this->inc_exc_url( 'includes' ) ) . '</span>' ;
                        echo  '</div></div>' ;
                        continue;
                    }
                    
                    echo  '<p class="check-radio"><label for="' . $post_type . '-post-search_all" ><input class="is-post-select" type="radio" id="' . $post_type . '-post-search_all" name="' . $post_type . 'i[post_search_radio]" value="all" ' . checked( 'all', $checked, false ) . '/>' ;
                    echo  '<span class="toggle-check-text"></span>' . sprintf( esc_html__( "Do not exclude any %s from search", 'add-search-to-menu' ), strtolower( $post_types2[$post_type]->labels->singular_name ) ) . '</label></p>' ;
                    echo  '<p class="check-radio"><label for="' . $post_type . '-post-search_selected" ><input class="is-post-select" type="radio" id="' . $post_type . '-post-search_selected" name="' . $post_type . 'i[post_search_radio]" value="selected" ' . checked( 'selected', $checked, false ) . '/>' ;
                    echo  '<span class="toggle-check-text"></span>' . sprintf( esc_html__( "Exclude selected %s from search", 'add-search-to-menu' ), strtolower( $post_types2[$post_type]->labels->name ) ) . '</label></p>' ;
                    echo  $html ;
                } else {
                    echo  '<span class="notice-is-info">' . sprintf( esc_html__( 'The search form is configured in the %1$s section to only search specific %2$s.', 'add-search-to-menu' ), $this->inc_exc_url( 'includes' ), strtolower( $post_types2[$post_type]->labels->name ) ) . '</span><br />' ;
                }
            
            }
            
            $tax_objs = get_object_taxonomies( $post_type, 'objects' );
            
            if ( !empty($tax_objs) ) {
                $html = '<div class="is-taxes">';
                $selected_tax = false;
                foreach ( $tax_objs as $key => $tax_obj ) {
                    $terms = get_terms( array(
                        'taxonomy' => $key,
                        'lang'     => '',
                    ) );
                    
                    if ( !empty($terms) && !empty($tax_obj->labels->name) ) {
                        $html .= '<div class="col-wrapper"><div class="col-title">';
                        $col_title = ucwords( str_replace( '-', ' ', str_replace( '_', ' ', esc_html( $tax_obj->labels->name ) ) ) );
                        
                        if ( isset( $excludes['tax_query'][$key] ) ) {
                            $col_title = '<strong>' . $col_title . '</strong>';
                            $selected_tax = true;
                        }
                        
                        $html .= $col_title . '<input class="list-search" placeholder="' . __( "Search..", 'add-search-to-menu' ) . '" type="text"></div><select class="_is_excludes-tax_query" name="' . $id . '[tax_query][' . $key . '][]" multiple size="8" >';
                        foreach ( $terms as $key2 => $term ) {
                            $checked = ( isset( $excludes['tax_query'][$key] ) && in_array( $term->term_taxonomy_id, $excludes['tax_query'][$key] ) ? $term->term_taxonomy_id : 0 );
                            $html .= '<option value="' . esc_attr( $term->term_taxonomy_id ) . '" ' . selected( $term->term_taxonomy_id, $checked, false ) . '>' . esc_html( $term->name ) . '</option>';
                        }
                        $html .= '</select></div>';
                    }
                
                }
                $html .= '<br /><label for="' . $id . '-tax_query" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'add-search-to-menu' ) . '</label><br />';
                $html .= '</div>';
                $checked = ( $selected_tax ? 'selected' : 'all' );
                echo  '<br /><p class="check-radio"><label for="' . $post_type . '-tax-search_all" ><input class="is-tax-select" type="radio" id="' . $post_type . '-tax-search_all" name="' . $post_type . 'i[tax_search_radio]" value="all" ' . checked( 'all', $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . sprintf(
                    esc_html__( "Do not exclude any %s from search of any taxonomies (%s categories, tags & terms %s)", 'add-search-to-menu' ),
                    strtolower( $post_types2[$post_type]->labels->singular_name ),
                    '<i>',
                    '</i>'
                ) . '</label></p>' ;
                echo  '<p class="check-radio"><label for="' . $post_type . '-tax-search_selected" ><input class="is-tax-select" type="radio" id="' . $post_type . '-tax-search_selected" name="' . $post_type . 'i[tax_search_radio]" value="selected" ' . checked( 'selected', $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . sprintf(
                    esc_html__( "Exclude %s from search of selected taxonomies (%s categories, tags & terms %s)", 'add-search-to-menu' ),
                    strtolower( $post_types2[$post_type]->labels->name ),
                    '<i>',
                    '</i>'
                ) . '</label></p>' ;
                echo  $html ;
            }
            
            $meta_keys = $this->is_meta_keys( $post_type );
            
            if ( !empty($meta_keys) ) {
                $html = '<div class="col-wrapper is-metas">';
                $selected_meta = false;
                $custom_field_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
                $html .= '<input class="list-search wide" placeholder="' . __( "Search..", 'add-search-to-menu' ) . '" type="text">';
                $html .= '<select class="_is_excludes-custom_field" name="' . $id . '[custom_field][]" ' . $custom_field_disable . ' multiple size="8" >';
                foreach ( $meta_keys as $meta_key ) {
                    $checked = ( isset( $excludes['custom_field'] ) && in_array( $meta_key, $excludes['custom_field'] ) ? $meta_key : 0 );
                    if ( $checked ) {
                        $selected_meta = true;
                    }
                    $html .= '<option value="' . esc_attr( $meta_key ) . '" ' . selected( $meta_key, $checked, false ) . '>' . esc_html( $meta_key ) . '</option>';
                }
                $html .= '</select>';
                $html .= IS_Admin::pro_link();
                $html .= '<br /><label for="' . $id . '-custom_field" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'add-search-to-menu' ) . '</label><br />';
                $html .= '</div>';
                $checked = ( $selected_meta ? 'selected' : 'all' );
                echo  '<br /><p class="check-radio"><label for="' . $post_type . '-meta-search_selected" ><input class="is-meta-select" type="checkbox" id="' . $post_type . '-meta-search_selected" name="' . $post_type . 'i[meta_search_radio]" value="selected" ' . checked( 'selected', $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . sprintf( esc_html__( "Exclude %s from search having selected custom fields", 'add-search-to-menu' ), strtolower( $post_types2[$post_type]->labels->name ) ) . '</label></p>' ;
                echo  $html ;
            }
            
            
            if ( 'product' == $post_type ) {
                echo  '<br />' ;
                $outofstock_disable = ( is_fs()->is_plan_or_trial( 'pro_plus' ) && $this->is_premium_plugin ? '' : ' disabled ' );
                if ( '' !== $outofstock_disable ) {
                    echo  '<br /><div class="upgrade-parent">' ;
                }
                $checked = ( isset( $excludes['woo']['outofstock'] ) && $excludes['woo']['outofstock'] ? 1 : 0 );
                echo  '<p class="check-radio"><label for="' . $id . '-outofstock" ><input class="_is_excludes-woocommerce" type="checkbox" ' . $outofstock_disable . ' id="' . $id . '-outofstock" name="' . $id . '[woo][outofstock]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . esc_html__( "Exclude 'Out of Stock' products from search", 'add-search-to-menu' ) . '</label></p>' ;
                echo  IS_Admin::pro_link( 'pro_plus' ) ;
                if ( '' !== $outofstock_disable ) {
                    echo  '</div>' ;
                }
            }
            
            
            if ( 'attachment' == $post_type ) {
                global  $wp_version ;
                
                if ( 4.9 <= $wp_version ) {
                    
                    if ( !isset( $includes['post_file_type'] ) ) {
                        echo  '<br />' ;
                        $file_types = get_allowed_mime_types();
                        
                        if ( !empty($file_types) ) {
                            $file_type_disable = ( is_fs()->is_plan_or_trial( 'pro_plus' ) && $this->is_premium_plugin ? '' : ' disabled ' );
                            if ( '' !== $file_type_disable ) {
                                echo  '<div class="upgrade-parent">' ;
                            }
                            ksort( $file_types );
                            $html = '<br /><div class="is-mime">';
                            $html .= '<input class="list-search wide" placeholder="' . __( "Search..", 'add-search-to-menu' ) . '" type="text">';
                            $html .= '<select class="_is_excludes-post_file_type" name="' . $id . '[post_file_type][]" ' . $file_type_disable . ' multiple size="8" >';
                            foreach ( $file_types as $key => $file_type ) {
                                $checked = ( isset( $excludes['post_file_type'] ) && in_array( $file_type, $excludes['post_file_type'] ) ? $file_type : 0 );
                                $html .= '<option value="' . esc_attr( $file_type ) . '" ' . selected( $file_type, $checked, false ) . '>' . esc_html( $key ) . '</option>';
                            }
                            $html .= '</select>';
                            echo  IS_Admin::pro_link( 'pro_plus' ) ;
                            $html .= '<br /><label for="' . $id . '-post_file_type" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'add-search-to-menu' ) . '</label><br />';
                            
                            if ( isset( $excludes['post_file_type'] ) ) {
                                $html .= __( 'Excluded File Types :', 'add-search-to-menu' );
                                foreach ( $excludes['post_file_type'] as $post_file_type ) {
                                    $html .= '<br /><span style="font-size: 11px;">' . $post_file_type . '</span>';
                                }
                            }
                            
                            $html .= '</div>';
                            $checked = ( isset( $excludes['post_file_type'] ) && !empty($excludes['post_file_type']) ? 'selected' : 'all' );
                            echo  '<p class="check-radio"><label for="mime-search_all" ><input class="is-mime-select" type="radio" id="mime-search_all" name="mime_search_radio" value="all" ' . checked( 'all', $checked, false ) . '/>' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search all MIME types", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio"><label for="mime-search_selected" ><input class="is-mime-select" type="radio" id="mime-search_selected" name="mime_search_radio" value="selected" ' . checked( 'selected', $checked, false ) . '/>' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Exclude selected  MIME types from search", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  $html ;
                            echo  '<span class="search-attachments-wrapper">' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_images"><input class="search-attachments exclude" type="checkbox" id="' . $id . '-search_images" name="search_images" value="1" />' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Exclude Images", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_videos"><input class="search-attachments exclude" type="checkbox" id="' . $id . '-search_videos" name="search_videos" value="1" />' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Exclude Videos", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_audios"><input class="search-attachments exclude" type="checkbox" id="' . $id . '-search_audios" name="search_audios" value="1" />' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Exclude Audios", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_text"><input class="search-attachments exclude" type="checkbox" id="' . $id . '-search_text" name="search_text" value="1" />' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Exclude Text Files", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_pdfs"><input class="search-attachments exclude" type="checkbox" id="' . $id . '-search_pdfs" name="search_pdfs" value="1" />' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Exclude PDF Files", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '<p class="check-radio"><label for="' . $id . '-search_docs"><input class="search-attachments exclude" type="checkbox" id="' . $id . '-search_docs" name="search_docs" value="1" />' ;
                            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Exclude Document Files", 'add-search-to-menu' ) . '</label></p>' ;
                            echo  '</span>' ;
                            if ( '' !== $file_type_disable ) {
                                echo  '</div>' ;
                            }
                        }
                    
                    } else {
                        echo  '<br /><span class="notice-is-info">' . sprintf( esc_html__( "This search form is configured in the %s section to search specific attachments.", 'add-search-to-menu' ), $this->inc_exc_url( 'includes' ) ) . '</span><br />' ;
                    }
                
                } else {
                    echo  '<span class="notice-is-info">' . __( 'You are using WordPress version less than 4.9 which does not support searching by MIME type.', 'add-search-to-menu' ) . '</span>' ;
                }
            
            }
            
            ?>
			</div></div>

                        <?php 
        }
        ?>
			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-extras"><?php 
        echo  esc_html( __( 'Extras', 'add-search-to-menu' ) ) ;
        ?></label>
                <span class="is-actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'add-search-to-menu' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'add-search-to-menu' );
        ?></a></span>
			</h3>
			<div>
			<h4 scope="row" class="is-first-title">
				<label for="<?php 
        echo  $id ;
        ?>-author"><?php 
        echo  esc_html( __( 'Authors', 'add-search-to-menu' ) ) ;
        ?></label>
			</h4>
			<div>
				<?php 
        $content = __( 'Exclude posts from search created by selected authors.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        
        if ( !isset( $includes['author'] ) ) {
            $author_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
            $authors = get_users( array(
                'fields'  => array( 'ID', 'display_name' ),
                'orderby' => 'post_count',
                'order'   => 'DESC',
                'who'     => 'authors',
            ) );
            
            if ( !empty($authors) ) {
                if ( '' !== $author_disable ) {
                    echo  IS_Admin::pro_link() ;
                }
                echo  '<div class="is-cb-dropdown">' ;
                echo  '<div class="is-cb-title">' ;
                
                if ( !isset( $excludes['author'] ) || empty($excludes['author']) ) {
                    echo  '<span class="is-cb-select">' . __( 'Search all author posts', 'add-search-to-menu' ) . '</span><span class="is-cb-titles"></span>' ;
                } else {
                    echo  '<span style="display:none;" class="is-cb-select">' . __( 'Search all author posts', 'add-search-to-menu' ) . '</span><span class="is-cb-titles">' ;
                    foreach ( $excludes['author'] as $author2 ) {
                        $display_name = get_userdata( $author2 );
                        if ( $display_name ) {
                            echo  '<span title="' . ucfirst( esc_html( $display_name->display_name ) ) . '"> ' . esc_html( $display_name->display_name ) . '</span>' ;
                        }
                    }
                    echo  '</span>' ;
                }
                
                echo  '</div>' ;
                echo  '<div class="is-cb-multisel">' ;
                foreach ( $authors as $author ) {
                    $post_count = count_user_posts( $author->ID );
                    // Move on if user has not published a post (yet).
                    if ( !$post_count ) {
                        continue;
                    }
                    $checked = ( isset( $excludes['author'][esc_attr( $author->ID )] ) ? $excludes['author'][esc_attr( $author->ID )] : 0 );
                    echo  '<label for="' . $id . '-author-' . esc_attr( $author->ID ) . '"><input class="_is_excludes-author" type="checkbox" ' . $author_disable . ' id="' . $id . '-author-' . esc_attr( $author->ID ) . '" name="' . $id . '[author][' . esc_attr( $author->ID ) . ']" value="' . esc_attr( $author->ID ) . '" ' . checked( $author->ID, $checked, false ) . '/>' ;
                    echo  '<span class="toggle-check-text"></span> ' . ucfirst( esc_html( $author->display_name ) ) . '</label>' ;
                }
                echo  '</div></div>' ;
            }
        
        } else {
            echo  '<br /><span class="notice-is-info">' . sprintf( esc_html__( "This search form is configured in the %s section to search posts created by specific authors.", 'add-search-to-menu' ), $this->inc_exc_url( 'includes' ) ) . '</span><br />' ;
        }
        
        ?>
			</div></div>

			<h4 scope="row">
                            <label for="<?php 
        echo  $id ;
        ?>-post_status"><?php 
        echo  esc_html( __( 'Post Status', 'add-search-to-menu' ) ) ;
        ?></label>
			</h4>
			<div>
				<?php 
        $content = __( 'Exclude posts from search having selected post statuses.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $checked = ( isset( $excludes['ignore_sticky_posts'] ) && $excludes['ignore_sticky_posts'] ? 1 : 0 );
        echo  '<label for="' . $id . '-ignore_sticky_posts" ><input class="_is_excludes-post_status" type="checkbox" id="' . $id . '-ignore_sticky_posts" name="' . $id . '[ignore_sticky_posts]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Exclude sticky posts from search", 'add-search-to-menu' ) . '</label>' ;
        ?>
			</div></div>
		</div>
		</div>
		</div>
	<?php 
    }
    
    public function options_panel( $post )
    {
        $id = '_is_settings';
        $settings = $post->prop( $id );
        ?>
		<h4 class="panel-desc">
			<?php 
        _e( "Advanced Search Form Options", 'add-search-to-menu' );
        ?>
		</h4>
		<div class="search-form-editor-box" id="<?php 
        echo  $id ;
        ?>">
		<div class="form-table form-table-panel-options">

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-posts_per_page"><?php 
        echo  esc_html( __( 'Posts Per Page', 'add-search-to-menu' ) ) ;
        ?></label>
			<span class="is-actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'add-search-to-menu' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'add-search-to-menu' );
        ?></a></span></h3>
			<div>
			<?php 
        $content = __( 'Display selected number of posts in search results.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        echo  '<select class="_is_settings-posts_per_page" name="' . $id . '[posts_per_page]" >' ;
        $default_per_page = get_option( 'posts_per_page', 10 );
        $checked = ( isset( $settings['posts_per_page'] ) ? $settings['posts_per_page'] : $default_per_page );
        for ( $d = 1 ;  $d <= 1000 ;  $d++ ) {
            echo  '<option value="' . $d . '" ' . selected( $d, $checked, false ) . '>' . $d . '</option>' ;
        }
        echo  '<option value="9999" ' . selected( 9999, $checked, false ) . '>9999</option>' ;
        echo  '<option value="-1" ' . selected( -1, $checked, false ) . '>-1</option>' ;
        echo  '</select>' ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-order"><?php 
        echo  esc_html( __( 'Order Search Results', 'add-search-to-menu' ) ) ;
        ?></label>
			</h3>
			<div><?php 
        $content = __( 'Display posts on search results page ordered by selected options.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $orderby_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
        echo  '<select class="_is_settings-order" name="' . $id . '[orderby]" ' . $orderby_disable . ' >' ;
        $checked = ( isset( $settings['orderby'] ) ? $settings['orderby'] : 'date' );
        $orderbys = array(
            'date',
            'relevance',
            'none',
            'ID',
            'author',
            'title',
            'name',
            'type',
            'modified',
            'parent',
            'rand',
            'comment_count',
            'menu_order',
            'meta_value',
            'meta_value_num',
            'post__in',
            'post_name__in',
            'post_parent__in'
        );
        foreach ( $orderbys as $orderby ) {
            echo  '<option value="' . $orderby . '" ' . selected( $orderby, $checked, false ) . '>' . ucwords( str_replace( '_', ' ', esc_html( $orderby ) ) ) . '</option>' ;
        }
        echo  '</select><select class="_is_settings-order" name="' . $id . '[order]" ' . $orderby_disable . ' >' ;
        $checked = ( isset( $settings['order'] ) ? $settings['order'] : 'DESC' );
        $orders = array( 'DESC', 'ASC' );
        foreach ( $orders as $order ) {
            echo  '<option value="' . $order . '" ' . selected( $order, $checked, false ) . '>' . ucwords( str_replace( '_', ' ', esc_html( $order ) ) ) . '</option>' ;
        }
        echo  '</select>' ;
        echo  IS_Admin::pro_link() ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-highlight_terms"><?php 
        echo  esc_html( __( 'Highlight Search Terms', 'add-search-to-menu' ) ) ;
        ?></label>
			</h3>
			<div><div>
			<?php 
        $checked = ( isset( $settings['highlight_terms'] ) && $settings['highlight_terms'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-highlight_terms" ><input class="_is_settings-highlight_terms" type="checkbox" id="' . $id . '-highlight_terms" name="' . $id . '[highlight_terms]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Highlight searched terms on search results page", 'add-search-to-menu' ) . '</label></p>' ;
        $color = ( isset( $settings['highlight_color'] ) ? $settings['highlight_color'] : '#FFFFB9' );
        echo  '<div class="highlight-container"><br /><input style="width: 80px;" class="_is_settings-highlight_terms is-colorpicker" size="5" type="text" id="' . $id . '-highlight_color" name="' . $id . '[highlight_color]" value="' . $color . '" />' ;
        echo  '<br /><i> ' . esc_html__( "Select text highlight color", 'add-search-to-menu' ) . '</i></div>' ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-term_rel"><?php 
        echo  esc_html( __( 'Search All Or Any Search Terms', 'add-search-to-menu' ) ) ;
        ?></label>
			</h3>
			<div>
			<?php 
        $content = __( 'Select whether to search posts having all or any of the words being searched.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $checked = ( isset( $settings['term_rel'] ) && "OR" === $settings['term_rel'] ? "OR" : "AND" );
        echo  '<p class="check-radio"><label for="' . $id . '-term_rel_or" ><input class="_is_settings-term_rel" type="radio" id="' . $id . '-term_rel_or" name="' . $id . '[term_rel]" value="OR" ' . checked( 'OR', $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "OR - Display content having any of the search terms", 'add-search-to-menu' ) . '</label></p>' ;
        echo  '<p class="check-radio"><label for="' . $id . '-term_rel_and" ><input class="_is_settings-term_rel" type="radio" id="' . $id . '-term_rel_and" name="' . $id . '[term_rel]" value="AND" ' . checked( 'AND', $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "AND - Display content having all the search terms", 'add-search-to-menu' ) . '</label></p>' ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-fuzzy_match"><?php 
        echo  esc_html( __( 'Fuzzy Matching', 'add-search-to-menu' ) ) ;
        ?></label>
			</h3>
			<div><?php 
        $content = __( 'Select whether to search posts having whole or partial word being searched.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $checked = ( isset( $settings['fuzzy_match'] ) ? $settings['fuzzy_match'] : '2' );
        echo  '<p class="check-radio"><label for="' . $id . '-whole" ><input class="_is_settings-fuzzy_match" type="radio" id="' . $id . '-whole" name="' . $id . '[fuzzy_match]" value="1" ' . checked( '1', $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Whole - Search posts that include the whole search term", 'add-search-to-menu' ) . '</label></p>' ;
        echo  '<p class="check-radio"><label for="' . $id . '-partial" ><input class="_is_settings-fuzzy_match" type="radio" id="' . $id . '-partial" name="' . $id . '[fuzzy_match]" value="2" ' . checked( '2', $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Partial - Also search words in the posts that begins or ends with the search term", 'add-search-to-menu' ) . '</label></p>' ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-keyword_stem"><?php 
        echo  esc_html( __( 'Keyword Stemming', 'add-search-to-menu' ) ) ;
        ?></label>
			</h3>
			<div>
			<?php 
        $content = __( 'Select whether to search the base word of a searched keyword.', 'add-search-to-menu' );
        $content .= '<p>' . __( 'For Example: If you search "doing" then it also searches base word of "doing" that is "do" in the specified post types.', 'add-search-to-menu' ) . '</p>';
        $content .= '<p><span class="is-info-warning">' . __( 'Not recommended to use when Fuzzy Matching option is set to Whole.', 'add-search-to-menu' ) . '</span></p>';
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $stem_disable = ( is_fs()->is_plan_or_trial( 'pro_plus' ) && $this->is_premium_plugin ? '' : ' disabled ' );
        $checked = ( isset( $settings['keyword_stem'] ) && $settings['keyword_stem'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-keyword_stem" ><input class="_is_settings-keyword_stem" type="checkbox" id="' . $id . '-keyword_stem" ' . $stem_disable . ' name="' . $id . '[keyword_stem]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Also search base word of searched keyword", 'add-search-to-menu' ) . '</label></p>' ;
        echo  IS_Admin::pro_link( 'pro_plus' ) ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-extras"><?php 
        echo  esc_html( __( 'Others', 'add-search-to-menu' ) ) ;
        ?></label>
			<span class="is-actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'add-search-to-menu' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'add-search-to-menu' );
        ?></a></span></h3>
			<div><div>
			<?php 
        $checked = ( isset( $settings['move_sticky_posts'] ) && $settings['move_sticky_posts'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-move_sticky_posts" ><input class="_is_settings-move_sticky_posts" type="checkbox" id="' . $id . '-move_sticky_posts" name="' . $id . '[move_sticky_posts]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Display sticky posts to the start of the search results page", 'add-search-to-menu' ) . '</label></p>' ;
        $checked = ( isset( $settings['demo'] ) && $settings['demo'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-demo" ><input class="_is_settings-demo" type="checkbox" id="' . $id . '-demo" name="' . $id . '[demo]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Display search form only for site administrator", 'add-search-to-menu' ) . '</label></p>' ;
        $checked = ( isset( $settings['disable'] ) && $settings['disable'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-disable" ><input class="_is_settings-disable" type="checkbox" id="' . $id . '-disable" name="' . $id . '[disable]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Disable this search form", 'add-search-to-menu' ) . '</label></p>' ;
        echo  '<br /><p class="check-radio">' ;
        $content = __( 'Select whether to display an error when user perform search without any search word.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        $checked = ( isset( $settings['empty_search'] ) && $settings['empty_search'] ? 1 : 0 );
        echo  '<br /><label for="' . $id . '-empty_search" ><input class="_is_settings-empty_search" type="checkbox" id="' . $id . '-empty_search" name="' . $id . '[empty_search]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Display an error for empty search query", 'add-search-to-menu' ) . '</label></p>' ;
        ?>
			</div></div>
		</div>
		</div>
		<?php 
    }

}