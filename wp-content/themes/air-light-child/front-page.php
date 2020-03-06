<?php
/**
 * The template for displaying front page
 *
 * Contains the closing of the #content div and all content after.
 * Initial styles for front page template.
 *
 * @Date:   2019-10-15 12:30:02
 * @Last Modified by:   Roni Laukkarinen
 * @Last Modified time: 2019-12-19 19:19:33
 * @package air-light
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 */

// Featured image.
$featured_image = '';
if ( has_post_thumbnail() ) {
	$featured_image = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
} else {
	$featured_image = get_theme_file_uri( 'images/default.jpg' );
}

get_header(); ?>

<div id="content" class="content-area">
  <main role="main" id="main" class="site-main">
    <div class="block">
      <div class="container">
      <div class="kyr-hero">
        <img src="<?php echo get_stylesheet_directory_uri() . "/KYR short.png"?>" />
    </div>

        <?php if ( have_posts() ) {
        	while ( have_posts() ) {
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
