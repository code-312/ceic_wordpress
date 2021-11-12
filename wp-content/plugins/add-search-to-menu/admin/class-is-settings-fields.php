<?php

/**
 * Defines plugin settings fields.
 *
 * This class defines all code necessary to manage plugin settings fields.
 *
 * @package IS
 */
class IS_Settings_Fields
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
    private  $is_premium_plugin = false ;
    /**
     * Instantiates the plugin by setting up the core properties and loading
     * all necessary dependencies and defining the hooks.
     *
     * The constructor uses internal functions to import all the
     * plugin dependencies, and will leverage the Ivory_Search for
     * registering the hooks and the callback functions used throughout the plugin.
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
     * Displays settings sections having custom markup.
     */
    public function is_do_settings_sections( $page, $sec )
    {
        global  $wp_settings_sections, $wp_settings_fields ;
        if ( !isset( $wp_settings_sections[$page] ) ) {
            return;
        }
        $section = (array) $wp_settings_sections[$page][$sec];
        if ( $section['title'] ) {
            echo  "<h2>{$section['title']}</h2>\n" ;
        }
        if ( $section['callback'] ) {
            call_user_func( $section['callback'], $section );
        }
        if ( !isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) ) {
            return;
        }
        ?>
		<div class="form-table search-form-editor-box">
		<?php 
        $this->is_do_settings_fields( $page, $section['id'] );
        ?>
		</div>
		<?php 
    }
    
    /**
     * Displays settings fields having custom markup.
     */
    public function is_do_settings_fields( $page, $section )
    {
        global  $wp_settings_fields ;
        if ( !isset( $wp_settings_fields[$page][$section] ) ) {
            return;
        }
        foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
            $class = '';
            if ( !empty($field['args']['class']) ) {
                $class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
            }
            
            if ( !empty($field['args']['label_for']) ) {
                ?>
			<h3 scope="row"><label for="<?php 
                esc_attr_e( $field['args']['label_for'] );
                ?>"><?php 
                echo  $field['title'] ;
                ?></label>
			<?php 
            } else {
                ?> 
			<h3 scope="row"><?php 
                echo  $field['title'] ;
            }
            
            
            if ( 'Custom CSS' === $field['title'] || 'Advanced' === $field['title'] ) {
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
            call_user_func( $field['callback'], $field['args'] );
            ?>
		    </div>
		    <?php 
        }
    }
    
    /**
     * Registers plugin settings fields.
     */
    function register_settings_fields()
    {
        
        if ( !empty($GLOBALS['pagenow']) && 'options.php' === $GLOBALS['pagenow'] ) {
            global  $wp_version ;
            $temp_oname = 'whitelist_options';
            if ( version_compare( $wp_version, '5.5', '>=' ) ) {
                $temp_oname = 'allowed_options';
            }
            
            if ( isset( $_POST['is_menu_search'] ) ) {
                add_filter( $temp_oname, function ( $allowed_options ) {
                    $allowed_options['ivory_search'][0] = 'is_menu_search';
                    return $allowed_options;
                } );
            } else {
                if ( isset( $_POST['is_analytics'] ) ) {
                    add_filter( $temp_oname, function ( $allowed_options ) {
                        $allowed_options['ivory_search'][0] = 'is_analytics';
                        return $allowed_options;
                    } );
                }
            }
        
        }
        
        $tab = 'settings';
        if ( isset( $_GET['tab'] ) ) {
            switch ( $_GET['tab'] ) {
                case 'menu-search':
                    $tab = 'menu-search';
                    break;
                case 'analytics':
                    $tab = 'analytics';
                    break;
            }
        }
        
        if ( 'settings' === $tab ) {
            add_settings_section(
                'ivory_search_settings',
                '',
                array( $this, 'settings_section_desc' ),
                'ivory_search'
            );
            add_settings_field(
                'ivory_search_css',
                __( 'Custom CSS', 'add-search-to-menu' ),
                array( $this, 'custom_css' ),
                'ivory_search',
                'ivory_search_settings'
            );
            add_settings_field(
                'ivory_search_stopwords',
                __( 'Stopwords', 'add-search-to-menu' ),
                array( $this, 'stopwords' ),
                'ivory_search',
                'ivory_search_settings'
            );
            add_settings_field(
                'ivory_search_synonyms',
                __( 'Synonyms', 'add-search-to-menu' ),
                array( $this, 'synonyms' ),
                'ivory_search',
                'ivory_search_settings'
            );
            add_settings_field(
                'ivory_search_header',
                __( 'Header Search', 'add-search-to-menu' ),
                array( $this, 'header' ),
                'ivory_search',
                'ivory_search_settings'
            );
            add_settings_field(
                'ivory_search_footer',
                __( 'Footer Search', 'add-search-to-menu' ),
                array( $this, 'footer' ),
                'ivory_search',
                'ivory_search_settings'
            );
            add_settings_field(
                'ivory_search_display_in_header',
                __( 'Mobile Search', 'add-search-to-menu' ),
                array( $this, 'menu_search_in_header' ),
                'ivory_search',
                'ivory_search_settings'
            );
            add_settings_field(
                'not_load_files',
                __( 'Plugin Files', 'add-search-to-menu' ),
                array( $this, 'plugin_files' ),
                'ivory_search',
                'ivory_search_settings'
            );
            add_settings_field(
                'ivory_search_extras',
                __( 'Advanced', 'add-search-to-menu' ),
                array( $this, 'advanced' ),
                'ivory_search',
                'ivory_search_settings'
            );
            register_setting( 'ivory_search', 'is_settings' );
        } else {
            
            if ( 'menu-search' === $tab ) {
                add_settings_section(
                    'ivory_search_section',
                    '',
                    array( $this, 'menu_search_section_desc' ),
                    'ivory_search'
                );
                add_settings_field(
                    'ivory_search_locations',
                    __( 'Menu Search Settings', 'add-search-to-menu' ),
                    array( $this, 'menu_settings' ),
                    'ivory_search',
                    'ivory_search_section'
                );
                register_setting( 'ivory_search', 'is_menu_search' );
            } else {
                
                if ( 'analytics' === $tab ) {
                    add_settings_section(
                        'ivory_search_analytics',
                        '',
                        array( $this, 'analytics_section_desc' ),
                        'ivory_search'
                    );
                    add_settings_field(
                        'ivory_search_analytics_fields',
                        __( 'Search Analytics', 'add-search-to-menu' ),
                        array( $this, 'analytics' ),
                        'ivory_search',
                        'ivory_search_analytics'
                    );
                    register_setting( 'ivory_search', 'is_analytics' );
                }
            
            }
        
        }
    
    }
    
    /**
     * Displays Search To Menu section description text.
     */
    function menu_search_section_desc()
    {
        ?>
		<h4 class="panel-desc">
			<?php 
        _e( 'Configure Menu Search', 'add-search-to-menu' );
        ?>
		</h4>
		<?php 
    }
    
    /**
     * Displays Analytics section description text.
     */
    function analytics_section_desc()
    {
        ?>
		<h4 class="panel-desc">
			<?php 
        _e( 'Search Analytics', 'add-search-to-menu' );
        ?>
		</h4>
		<?php 
    }
    
    /**
     * Displays Settings section description text.
     */
    function settings_section_desc()
    {
        ?>
		<h4 class="panel-desc">
			<?php 
        _e( 'Advanced Website Search Settings', 'add-search-to-menu' );
        ?>
		</h4>
		<?php 
    }
    
    /**
     * Displays menu settings fields.
     */
    function menu_settings()
    {
        /**
         * Displays choose menu locations field.
         */
        $content = __( 'Display search form on selected menu locations.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        $menus = get_registered_nav_menus();
        ?>
		<div>
		<?php 
        
        if ( !empty($menus) ) {
            $check_value = '';
            foreach ( $menus as $location => $description ) {
                
                if ( has_nav_menu( $location ) ) {
                    $check_value = ( isset( $this->opt['menus'][$location] ) ? $this->opt['menus'][$location] : 0 );
                    ?>
					<p><label for="is_menus<?php 
                    esc_attr_e( $location );
                    ?>"><input type="checkbox" class="ivory_search_locations" id="is_menus<?php 
                    esc_attr_e( $location );
                    ?>" name="is_menu_search[menus][<?php 
                    esc_attr_e( $location );
                    ?>]" value="<?php 
                    esc_attr_e( $location );
                    ?>" <?php 
                    checked( $location, $check_value, true );
                    ?>/>
					<span class="toggle-check-text"></span> <?php 
                    esc_html_e( $description );
                    ?> </label></p>
                <?php 
                }
            
            }
            if ( '' === $check_value ) {
                printf( __( 'No menu assigned to navigation menu location in the %sMenus screen%s.', 'add-search-to-menu' ), '<a target="_blank" href="' . admin_url( 'nav-menus.php' ) . '">', '</a>' );
            }
        } else {
            _e( 'Navigation menu location is not registered on the site.', 'add-search-to-menu' );
        }
        
        ?>
		</div><br />
  		 <?php 
        /**
         * Displays choose menu field.
         */
        $content = __( 'Display search form on selected menus.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        $menu_name = wp_get_nav_menus();
        ?>
		<div>
		<?php 
        
        if ( !empty($menu_name) ) {
            $check_value = '';
            foreach ( $menu_name as $value ) {
                $check_value = ( isset( $this->opt['menu_name'][$value->slug] ) ? $this->opt['menu_name'][$value->slug] : 0 );
                ?>

				<p><label for="is_menu_name<?php 
                esc_attr_e( $value->slug );
                ?>"><input type="checkbox" class="ivory_search_menu_name" id="is_menu_name<?php 
                esc_attr_e( $value->slug );
                ?>" name="is_menu_search[menu_name][<?php 
                esc_attr_e( $value->slug );
                ?>]" value="<?php 
                esc_attr_e( $value->slug );
                ?>"<?php 
                checked( $value->slug, $check_value, true );
                ?>/>
				<span class="toggle-check-text"></span> <?php 
                esc_html_e( $value->name );
                ?></label></p>
			<?php 
            }
        } else {
            printf( __( 'No menu created in the %sMenus screen%s.', 'add-search-to-menu' ), '<a target="_blank" href="' . admin_url( 'nav-menus.php' ) . '">', '</a>' );
        }
        
        ?>
		</div>
  		 <?php 
        if ( !isset( $this->opt['menus'] ) && !isset( $this->opt['menu_name'] ) || '' === $check_value ) {
            return;
        }
        ?>
        <div class="menu-settings-container"><br /><br />
		<?php 
        /**
         * Displays search form at the beginning of menu field.
         */
        $check_value = ( isset( $this->opt['first_menu_item'] ) ? $this->opt['first_menu_item'] : 0 );
        ?>
        <div>
		<label for="first_menu_item"><input class="ivory_search_first_menu_item" type="checkbox" id="first_menu_item" name="is_menu_search[first_menu_item]" value="first_menu_item" <?php 
        checked( 'first_menu_item', $check_value, true );
        ?> />
		<span class="toggle-check-text"></span><?php 
        esc_html_e( 'Display search form at the start of the navigation menu', 'add-search-to-menu' );
        ?></label>
		</div> <br /><br />
		<?php 
        /**
         * Displays form style field.
         */
        $content = __( 'Select menu search form style.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        $styles = array(
            'default'         => __( 'Default', 'add-search-to-menu' ),
            'dropdown'        => __( 'Dropdown', 'add-search-to-menu' ),
            'sliding'         => __( 'Sliding', 'add-search-to-menu' ),
            'full-width-menu' => __( 'Full Width', 'add-search-to-menu' ),
            'popup'           => __( 'Popup', 'add-search-to-menu' ),
        );
        $menu_close_icon = false;
        
        if ( empty($this->opt) || !isset( $this->opt['menu_style'] ) ) {
            $this->opt['menu_style'] = 'dropdown';
            $menu_close_icon = true;
        }
        
        $check_value = ( isset( $this->opt['menu_style'] ) ? $this->opt['menu_style'] : 'dropdown' );
        ?>
		<div class="search-form-style">
		<?php 
        foreach ( $styles as $key => $style ) {
            ?>
            <p>
			<label for="is_menu_style<?php 
            esc_attr_e( $key );
            ?>"><input class="ivory_search_style" type="radio" id="is_menu_style<?php 
            esc_attr_e( $key );
            ?>" name="is_menu_search[menu_style]" value="<?php 
            esc_attr_e( $key );
            ?>" <?php 
            checked( $key, $check_value, true );
            ?>/>
			<span class="toggle-check-text"></span><?php 
            esc_html_e( $style );
            ?></label>
			</p>
		<?php 
        }
        ?>
		</div><br /><br />
		<div class="form-style-dependent">
		<?php 
        /**
         * Displays menu search magnifier colorpicker field.
         */
        $color = ( isset( $this->opt['menu_magnifier_color'] ) ? $this->opt['menu_magnifier_color'] : '#848484' );
        ?>
		<input style="width: 80px;" class="menu-magnifier-color is-colorpicker" size="5" type="text" id="is-menu-magnifier-color" name="is_menu_search[menu_magnifier_color]" value="<?php 
        echo  $color ;
        ?>" />
		<br /><i> <?php 
        esc_html_e( 'Select menu magnifier icon color.', 'add-search-to-menu' );
        ?></i><br /><br />
		<?php 
        /**
         * Displays search form close icon field.
         */
        $check_value = ( isset( $this->opt['menu_close_icon'] ) ? $this->opt['menu_close_icon'] : 0 );
        if ( !$check_value && $menu_close_icon ) {
            $check_value = 'menu_close_icon';
        }
        ?>
        <div>
		<label for="menu_close_icon"><input class="ivory_search_close_icon" type="checkbox" id="menu_close_icon" name="is_menu_search[menu_close_icon]" value="menu_close_icon" <?php 
        checked( 'menu_close_icon', $check_value, true );
        ?> />
		<span class="toggle-check-text"></span><?php 
        esc_html_e( 'Display search form close icon', 'add-search-to-menu' );
        ?></label>
		</div> <br /><br />
		<?php 
        /**
         * Displays search menu title field.
         */
        $content = __( 'Add menu title to display in place of search icon.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        $this->opt['menu_title'] = ( isset( $this->opt['menu_title'] ) ? $this->opt['menu_title'] : '' );
        ?>
		<div><input type="text" class="ivory_search_title" id="is_menu_title" name="is_menu_search[menu_title]" value="<?php 
        esc_attr_e( $this->opt['menu_title'] );
        ?>" />
		</div> <br /><br />
		</div>
		<?php 
        /**
         * Displays menu search form field.
         */
        $content = __( 'Select search form that will control menu search functionality.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        $args = array(
            'numberposts' => -1,
            'post_type'   => 'is_search_form',
            'order'       => 'ASC',
        );
        $posts = get_posts( $args );
        ?>
		<div>
		<?php 
        
        if ( !empty($posts) ) {
            $check_value = ( isset( $this->opt['menu_search_form'] ) ? $this->opt['menu_search_form'] : 0 );
            ?>
			<select class="ivory_search_form" id="menu_search_form" name="is_menu_search[menu_search_form]" >
			<option value="0"><?php 
            _e( 'None', 'add-search-to-menu' );
            ?></option>
			<?php 
            foreach ( $posts as $post ) {
                ?>
				<option value="<?php 
                echo  $post->ID ;
                ?>" <?php 
                selected( $post->ID, $check_value, true );
                ?>><?php 
                echo  $post->post_title ;
                ?></option>
			<?php 
            }
            ?>
			</select>
			<?php 
            
            if ( $check_value ) {
                ?>
				<a href="<?php 
                echo  esc_url( menu_page_url( 'ivory-search', false ) ) ;
                ?>&post=<?php 
                echo  $check_value ;
                ?>&action=edit">  <?php 
                esc_html_e( 'Edit Search Form', 'add-search-to-menu' );
                ?></a>
			<?php 
            } else {
                ?>
				<a href="<?php 
                echo  esc_url( menu_page_url( 'ivory-search-new', false ) ) ;
                ?>">  <?php 
                esc_html_e( "Create New", 'add-search-to-menu' );
                ?></a>
			<?php 
            }
        
        }
        
        ?>
		</div><br /><br />
		<?php 
        /**
         * Displays search menu classes field.
         */
        $content = __( 'Add class to search form menu item.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        $this->opt['menu_classes'] = ( isset( $this->opt['menu_classes'] ) ? $this->opt['menu_classes'] : '' );
        ?>
		<div>
		<input type="text" class="ivory_search_classes" id="is_menu_classes" name="is_menu_search[menu_classes]" value="<?php 
        esc_attr_e( $this->opt['menu_classes'] );
        ?>" />
		<br /><label for="is_menu_classes" style="font-size: 10px;"><?php 
        esc_html_e( 'Add multiple classes seperated by space.', 'add-search-to-menu' );
        ?></label>
		</div><br /><br />
		<?php 
        /**
         * Displays google cse field.
         */
        $content = __( 'Add Google Custom Search( CSE ) search form code that will replace default search form.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        $this->opt['menu_gcse'] = ( isset( $this->opt['menu_gcse'] ) ? $this->opt['menu_gcse'] : '' );
        ?>
		<div>
		<input class="ivory_search_gcse" type="text" id="is_menu_gcse" name="is_menu_search[menu_gcse]" value="<?php 
        esc_attr_e( $this->opt['menu_gcse'] );
        ?>" />
		</div></div>
		<?php 
    }
    
    /**
     * Displays search analytics fields.
     */
    function analytics()
    {
        $is_analytics = get_option( 'is_analytics', array() );
        $check_value = ( isset( $is_analytics['disable_analytics'] ) ? $is_analytics['disable_analytics'] : 0 );
        ?>
        <div>
		<label for="is_disable_analytics"><select class="ivory_search_disable_analytics" id="is_disable_analytics" name="is_analytics[disable_analytics]" >
		<option value="0" <?php 
        selected( 0, $check_value, true );
        ?>><?php 
        _e( 'Enabled', 'add-search-to-menu' );
        ?></option>
		<option value="1" <?php 
        selected( 1, $check_value, true );
        ?>><?php 
        _e( 'Disabled', 'add-search-to-menu' );
        ?></option>
		</select> <?php 
        esc_html_e( 'Google Analytics tracking for searches', 'add-search-to-menu' );
        ?></label>
		<div class="analytics-info" <?php 
        echo  ( $check_value ? 'style="display:none;"' : '' ) ;
        ?> ><br/><br/><p><?php 
        _e( 'Search Analytics uses Google Analytics to track searches.', 'add-search-to-menu' );
        ?></p>
		<p><?php 
        printf( __( "You need %s Google Analytics %s to be installed on your site.", 'add-search-to-menu' ), "<a target='_blank' href='https://developers.google.com/analytics/devguides/collection/analyticsjs/'>", '</a>' );
        ?></p>
		<p><?php 
        _e( 'Data will be visible inside Google Analytics \'Events\' and \'Site Search\' report.', 'add-search-to-menu' );
        ?></p>
		<br/><p><?php 
        _e( 'Events will be as below:', 'add-search-to-menu' );
        ?></p>
		<p><b><?php 
        _e( 'Category - Results Found / Nothing Found', 'add-search-to-menu' );
        ?></b></p>
		<p><b><?php 
        _e( 'Action - Ivory Search - ID', 'add-search-to-menu' );
        ?></b></p>
		<p><b><?php 
        _e( 'Label - Value of search term', 'add-search-to-menu' );
        ?></b></p>
		<br/><p><?php 
        printf( __( "Need to %s activate Site Search feature %s inside Google Analytics to display data inside 'Site Search' report.", 'add-search-to-menu' ), "<a target='_blank' href='https://support.google.com/analytics/answer/1012264'>", '</a>' );
        ?></p>
		<p><?php 
        _e( 'Enable Site search Tracking option in Site Search Settings and set its parameters as below.', 'add-search-to-menu' );
        ?></p>
		<p><b><?php 
        _e( 'Query parameter - s', 'add-search-to-menu' );
        ?></b></p>
		<p><b><?php 
        _e( 'Category parameter - id / result', 'add-search-to-menu' );
        ?></b></p>
		</div></div>
		<?php 
    }
    
    /**
     * Displays search form in site header.
     */
    function header()
    {
        echo  __( 'Select search form to display in site header( Not Menu ).', 'add-search-to-menu' ) . '<br /><br />' ;
        $args = array(
            'numberposts' => -1,
            'post_type'   => 'is_search_form',
        );
        $posts = get_posts( $args );
        ?>
		<div>
		<?php 
        
        if ( !empty($posts) ) {
            $check_value = ( isset( $this->opt['header_search'] ) ? $this->opt['header_search'] : 0 );
            ?>
			<select class="ivory_search_header" id="is_header_search" name="is_settings[header_search]" >
			<option value="0" <?php 
            selected( 0, $check_value, true );
            ?>><?php 
            _e( 'None', 'add-search-to-menu' );
            ?></option>
			<?php 
            foreach ( $posts as $post ) {
                ?>
				<option value="<?php 
                echo  $post->ID ;
                ?>" <?php 
                selected( $post->ID, $check_value, true );
                ?>><?php 
                echo  $post->post_title ;
                ?></option>
			<?php 
            }
            ?>
			</select>
			<?php 
            
            if ( $check_value && get_post_type( $check_value ) ) {
                ?>
				<a href="<?php 
                echo  esc_url( menu_page_url( 'ivory-search', false ) ) . '&post=' . $check_value . '&action=edit' ;
                ?>"><?php 
                esc_html_e( "Edit", 'add-search-to-menu' );
                ?></a>
			<?php 
            } else {
                ?>
				<a href="<?php 
                echo  esc_url( menu_page_url( 'ivory-search-new', false ) ) ;
                ?>"><?php 
                esc_html_e( "Create New", 'add-search-to-menu' );
                ?></a>
			<?php 
            }
        
        }
        
        ?>
		<br/><br/><span class="is-help"><span class="is-info-warning"><?php 
        _e( 'Please note that the above option displays search form in site header and not in navigation menu.', 'add-search-to-menu' );
        ?></span></span></div>
	<?php 
    }
    
    /**
     * Displays search form in site footer.
     */
    function footer()
    {
        _e( 'Select search form to display in site footer.', 'add-search-to-menu' );
        ?>
		<br /><br />
		<div>
		<?php 
        $args = array(
            'numberposts' => -1,
            'post_type'   => 'is_search_form',
        );
        $posts = get_posts( $args );
        
        if ( !empty($posts) ) {
            $check_value = ( isset( $this->opt['footer_search'] ) ? $this->opt['footer_search'] : 0 );
            ?>
			<select class="ivory_search_footer" id="is_footer_search" name="is_settings[footer_search]" >
			<option value="0" <?php 
            selected( 0, $check_value, true );
            ?>><?php 
            _e( 'None', 'add-search-to-menu' );
            ?></option>
			<?php 
            foreach ( $posts as $post ) {
                ?>
				<option value="<?php 
                echo  $post->ID ;
                ?>" <?php 
                selected( $post->ID, $check_value, true );
                ?>><?php 
                echo  $post->post_title ;
                ?></option>
			<?php 
            }
            ?>
			</select>
			<?php 
            
            if ( $check_value && get_post_type( $check_value ) ) {
                ?>
				<a href="<?php 
                echo  esc_url( menu_page_url( 'ivory-search', false ) ) . '&post=' . $check_value . '&action=edit' ;
                ?>"> <?php 
                esc_html_e( "Edit", 'add-search-to-menu' );
                ?></a>
			<?php 
            } else {
                ?>
				<a href="<?php 
                echo  esc_url( menu_page_url( 'ivory-search-new', false ) ) ;
                ?>">  <?php 
                esc_html_e( "Create New", 'add-search-to-menu' );
                ?></a>
			<?php 
            }
        
        }
        
        ?>
		</div>
		<?php 
    }
    
    /**
     * Displays display in header field.
     */
    function menu_search_in_header()
    {
        $check_value = ( isset( $this->opt['header_menu_search'] ) ? $this->opt['header_menu_search'] : 0 );
        $check_string = checked( 'header_menu_search', $check_value, false );
        ?>
        <div>
		<label for="is_search_in_header"><input class="ivory_search_display_in_header" type="checkbox" id="is_search_in_header" name="is_settings[header_menu_search]" value="header_menu_search" <?php 
        echo  $check_string ;
        ?>/>
		<span class="toggle-check-text"></span><?php 
        esc_html_e( 'Display search form in site header on mobile devices', 'add-search-to-menu' );
        ?></label>
		</div><br />
		<?php 
        $content = __( 'If this site uses cache then please select the below option to display search form on mobile.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        $check_value = ( isset( $this->opt['site_uses_cache'] ) ? $this->opt['site_uses_cache'] : 0 );
        $check_string = checked( 'site_uses_cache', $check_value, false );
        ?>
		<div>
		<label for="is_site_uses_cache"><input class="ivory_search_display_in_header" type="checkbox" id="is_site_uses_cache" name="is_settings[site_uses_cache]" value="site_uses_cache" <?php 
        echo  $check_string ;
        ?>/>
		<span class="toggle-check-text"></span><?php 
        esc_html_e( 'This site uses cache', 'add-search-to-menu' );
        ?></label>
		</div>
		<?php 
    }
    
    /**
     * Displays custom css field.
     */
    function custom_css()
    {
        _e( 'Add custom CSS code.', 'add-search-to-menu' );
        $this->opt['custom_css'] = ( isset( $this->opt['custom_css'] ) ? $this->opt['custom_css'] : '' );
        ?>
		<br /><br />
		<div>
		<textarea class="ivory_search_css" rows="4" id="custom_css" name="is_settings[custom_css]" ><?php 
        esc_attr_e( $this->opt['custom_css'] );
        ?></textarea>
		</div>
		<?php 
    }
    
    /**
     * Displays stopwords field.
     */
    function stopwords()
    {
        echo  __( 'Add Stopwords that will not be searched.', 'add-search-to-menu' ) ;
        $this->opt['stopwords'] = ( isset( $this->opt['stopwords'] ) ? $this->opt['stopwords'] : '' );
        ?>
		<br /><br />
		<div>
		<textarea class="ivory_search_stopwords" rows="4" id="stopwords" name="is_settings[stopwords]" ><?php 
        esc_attr_e( $this->opt['stopwords'] );
        ?></textarea>
		<br /><label for="stopwords" style="font-size: 10px;"><?php 
        esc_html_e( 'Please separate multiple words with commas.', 'add-search-to-menu' );
        ?></label>
		</div>
		<?php 
    }
    
    /**
     * Displays synonyms field.
     */
    function synonyms()
    {
        _e( 'Add synonyms to make the searches find better results.', 'add-search-to-menu' );
        ?>
		<br /><br />
		<?php 
        $content = __( 'If you add bird = crow to the list of synonyms, searches for bird automatically become a search for bird crow and will thus match to posts that include either bird or crow.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        $this->opt['synonyms'] = ( isset( $this->opt['synonyms'] ) ? $this->opt['synonyms'] : '' );
        ?>
		<div>
		<textarea class="ivory_search_synonyms" rows="4" id="synonyms" name="is_settings[synonyms]" ><?php 
        esc_attr_e( $this->opt['synonyms'] );
        ?></textarea>
		<br /><label for="synonyms" style="font-size: 10px;"><?php 
        esc_html_e( 'The format here is key = value', 'add-search-to-menu' );
        ?></label>
		<br /><label for="synonyms" style="font-size: 10px;"><?php 
        esc_html_e( 'Please add every synonyms key = value pairs on new line.', 'add-search-to-menu' );
        ?></label>
		</div>
		<br /><span class="is-help"><span class="is-info-warning"><?php 
        _e( 'This only works for search forms configured to search any of the search terms(OR) and not all search terms(AND) in the search form Options.', 'add-search-to-menu' );
        ?></span></span>
		<?php 
    }
    
    /**
     * Displays do not load plugin files field.
     */
    function plugin_files()
    {
        $content = __( 'Enable below options to disable loading of plugin CSS and JavaScript files.', 'add-search-to-menu' );
        IS_Help::help_info( $content );
        $styles = array(
            'css' => __( 'Do not load plugin CSS files', 'add-search-to-menu' ),
            'js'  => __( 'Do not load plugin JavaScript files', 'add-search-to-menu' ),
        );
        ?> <div> <?php 
        foreach ( $styles as $key => $file ) {
            $check_value = ( isset( $this->opt['not_load_files'][$key] ) ? $this->opt['not_load_files'][$key] : 0 );
            $check_string = checked( $key, $check_value, false );
            if ( 'js' === $key ) {
                ?>
                            <br />
                        <?php 
            }
            ?>
			<br /><label for="not_load_files[<?php 
            esc_attr_e( $key );
            ?>]"><input class="not_load_files" type="checkbox" id="not_load_files[<?php 
            esc_attr_e( $key );
            ?>]" name="is_settings[not_load_files][<?php 
            esc_attr_e( $key );
            ?>]" value="<?php 
            esc_attr_e( $key );
            ?>" <?php 
            echo  $check_string ;
            ?>/>
			<span class="toggle-check-text"></span><?php 
            esc_html_e( $file );
            ?></label>
            <span class="not-load-wrapper">
			<?php 
            
            if ( 'css' === $key ) {
                ?>
				<br /><label for="not_load_files[<?php 
                esc_attr_e( $key );
                ?>]" style="font-size: 10px;"><?php 
                esc_html_e( 'If checked, you have to add following plugin file code into your child theme CSS file.', 'add-search-to-menu' );
                ?></label>
				<br /><a style="font-size: 13px;" target="_blank" href="<?php 
                echo  plugins_url( '/public/css/ivory-search.css', IS_PLUGIN_FILE ) ;
                ?>"><?php 
                echo  plugins_url( '/public/css/ivory-search.css', IS_PLUGIN_FILE ) ;
                ?></a>
				<br /><a style="font-size: 13px;" target="_blank" href="<?php 
                echo  plugins_url( '/public/css/ivory-ajax-search.css', IS_PLUGIN_FILE ) ;
                ?>"><?php 
                echo  plugins_url( '/public/css/ivory-ajax-search.css', IS_PLUGIN_FILE ) ;
                ?></a>
				<br />
			<?php 
            } else {
                ?>
				<br /><label for="not_load_files[<?php 
                esc_attr_e( $key );
                ?>]" style="font-size: 10px;"><?php 
                esc_html_e( "If checked, you have to add following plugin files code into your child theme JavaScript file.", 'add-search-to-menu' );
                ?></label>
				<br /><a style="font-size: 13px;" target="_blank" href="<?php 
                echo  plugins_url( '/public/js/ivory-search.js', IS_PLUGIN_FILE ) ;
                ?>"><?php 
                echo  plugins_url( '/public/js/ivory-search.js', IS_PLUGIN_FILE ) ;
                ?></a>
				<br /><a style="font-size: 13px;" target="_blank" href="<?php 
                echo  plugins_url( '/public/js/is-highlight.js', IS_PLUGIN_FILE ) ;
                ?>"><?php 
                echo  plugins_url( '/public/js/is-highlight.js', IS_PLUGIN_FILE ) ;
                ?></a>
                <br /><a style="font-size: 13px;" target="_blank" href="<?php 
                echo  plugins_url( '/public/js/ivory-ajax-search.js', IS_PLUGIN_FILE ) ;
                ?>"><?php 
                echo  plugins_url( '/public/js/ivory-ajax-search.js', IS_PLUGIN_FILE ) ;
                ?></a>
			<?php 
            }
            
            ?>
                </span>
		<?php 
        }
        ?>
		</div>
		<?php 
    }
    
    function advanced()
    {
        /**
         * Controls default search functionality.
         */
        $content = '<span class="is-info-warning">' . __( 'Warning: Use with caution.', 'add-search-to-menu' ) . '</span>';
        IS_Help::help_info( $content );
        $check_value = ( isset( $this->opt['default_search'] ) ? $this->opt['default_search'] : 0 );
        ?>
		<div>
		<label for="is_default_search"><input class="ivory_search_default" type="checkbox" id="is_default_search" name="is_settings[default_search]" value="1" <?php 
        checked( 1, $check_value, true );
        ?>/>
		<span class="toggle-check-text"></span><?php 
        esc_html_e( 'Do not use Default Search Form to control WordPress default search functionality', 'add-search-to-menu' );
        ?></label>
		</div><br />
		<?php 
        /**
         * Disables search functionality on whole site.
         */
        $check_value = ( isset( $this->opt['disable'] ) ? $this->opt['disable'] : 0 );
        ?>
		<div>
		<label for="is_disable"><input class="ivory_search_disable" type="checkbox" id="is_disable" name="is_settings[disable]" value="1" <?php 
        checked( 1, $check_value, true );
        ?> />
		<span class="toggle-check-text"></span><?php 
        esc_html_e( 'Disable search functionality on entire website', 'add-search-to-menu' );
        ?></label>
		</div><br />
		<?php 
        /**
         * Display search forms easy to edit links.
         */
        $check_value = ( isset( $this->opt['easy_edit'] ) ? $this->opt['easy_edit'] : 0 );
        ?>
		<div>
		<br /><label for="is_easy_edit"><input class="ivory_search_easy_edit" type="checkbox" id="is_easy_edit" name="is_settings[easy_edit]" value="1" <?php 
        checked( 1, $check_value, true );
        ?> />
		<span class="toggle-check-text"></span><?php 
        esc_html_e( 'Display easy edit links of search form on the website frontend to the admin users', 'add-search-to-menu' );
        ?></label>
		</div>
		<?php 
    }

}