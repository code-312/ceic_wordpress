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
              <div id="instagram-carousel"> Instagram Div </div>
            </div>
        </div>

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer();
