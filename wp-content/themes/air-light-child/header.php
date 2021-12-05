<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @Date:   2019-10-15 12:30:02
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2019-10-15 14:37:51
 * @package air-light
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="http://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>

<body <?php body_class('no-js'); ?>>
  <div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'air-light'); ?></a>

    <div class="nav-container">
      <header class="site-header" role="banner">
        <div class="site-branding">
          <div class="site-title">
            <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
              <img alt="Cannabis Equity Illinois logo" class="logo" src="<?php echo get_stylesheet_directory_uri() . '/assets/logo-transparent.png'; ?>" />
            </a>
            <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
              <img alt="Cannabis Equity Illinois logo" class="mobile-logo" src="<?php echo get_stylesheet_directory_uri() . '/assets/CEIC_Logo_Mobile_Green.png'; ?>" />
            </a>
          </div>

          <?php $description = get_bloginfo('description', 'display');
          if ($description || is_customize_preview()) : ?>
            <p class="site-description screen-reader-text"><?php echo $description; /* WPCS: xss ok. */ ?></p>
          <?php endif; ?>

        </div><!-- .site-branding -->

        <div class="main-navigation-wrapper" id="main-navigation-wrapper">
          <div class="navigation-buttons-wrapper">
            <?php echo do_shortcode( '[ivory-search id="86" title="Default Search Form"]' ); ?>

            <button id="donate-button" type="button">Donate</button>
          </div>
          <button id="nav-toggle" class="nav-toggle hamburger" type="button" aria-label="<?php esc_attr_e('Menu', 'air-light'); ?>">
            <span class="hamburger-box">
              <span class="hamburger-inner"></span>
            </span>
            <span id="nav-toggle-label" class="screen-reader-text" aria-label="<?php esc_attr_e('Menu', 'air-light'); ?>"><?php esc_attr_e('Menu', 'air-light'); ?></span>R
          </button>

          <nav id="nav" class="nav-primary" role="navigation">

            <?php wp_nav_menu(array(
              'theme_location'    => 'primary',
              'container'         => false,
              'depth'             => 4,
              'menu_class'        => 'menu-items',
              'menu_id'           => 'main-menu',
              'echo'              => true,
              'fallback_cb'       => 'Air_Light_Navwalker::fallback',
              'items_wrap'        => '<ul class="%2$s">%3$s</ul>',
              'walker'            => new Air_Light_Navwalker(),
            )); ?>

          </nav><!-- #nav -->
        </div>
      </header>
    </div><!-- .nav-container -->

    <div class="site-content">
