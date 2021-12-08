<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @Date:   2019-10-15 12:30:02
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2019-10-15 14:35:19
 * @package air-light
 */

get_header();

?>

<div id="content" class="content-area">
	<main role="main" id="main" class="site-main">
    <div class="container">

      <?php if ( have_posts() ) : ?>

        <header class="page-header">
          <h1 class="page-title"><?php printf( esc_html__( 'Search Results for: %s', 'air-light' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
        </header><!-- .page-header -->

      <?php while ( have_posts() ) {
      	the_post();
				get_template_part( 'template-parts/content', 'search' );
      }

      the_posts_navigation();
		else :
    	get_template_part( 'template-parts/content', 'none' );
		endif; ?>

    </div><!-- .container -->
  </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer();
