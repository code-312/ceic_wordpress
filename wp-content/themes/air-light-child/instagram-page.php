<?php

/**
 * Template Name: Instagram-Page
 *
 */

//  This page can hopefully be used for the user to add simple pages and have it match the current Figma designs - DA 8-6-20

get_header(); ?>

<div id="content" class="content-area">
    <main role="main" id="main" class="site-main">
        <div class="block">
            <div class="container">
                <?php if (have_posts()) {
                    while (have_posts()) {
                        the_post();
                        the_content();
                    }
                } else {
                    get_template_part('template-parts/content', 'none');
                } ?>
                
              <div class="slide-wrap">
                <div class="slideshow">

                  <!-- <div class="slide-entry">
                    <div class="slide-content">CONTENT</div>
                  </div> -->

                  <ul class="slide-nav">
                    <li id="prev-slide"><i>«</i></li>
                    <li id="next-slide"><i>»</i></li>
                  </ul> <!-- end .slide-nav -->

                </div> <!-- end .slideshow -->

              </div> <!-- end .slide-wrap -->
              
            </div>
        </div>

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer();
