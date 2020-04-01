<?php

/**
* Template Name: Generic Page
*
*/

get_header(); ?>

<div id="content" class="content-area">
  <main role="main" id="main" class="site-main">
    <div class="block">
      <div class="container">
        <?php if ( have_posts() ) {
        	while ( have_posts() ) {
                echo("<h1>");
                the_title();
                echo("</h1>");
        		the_post();
        		the_content();
					}
        } else {
        	get_template_part( 'template-parts/content', 'none' );
        } ?>

      </div>
    </div>

  </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer();