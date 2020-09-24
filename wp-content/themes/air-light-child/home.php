<?php

/**
 * Template Name: Home Page
 *
 */

get_header(); ?>


<!-- Requires the Advanced Custom Fields Plugin - https://www.advancedcustomfields.com/ -->

<!-- Checking if page has been created in Advanced Custom Fields. If not, populates Lorem Ipsum version below -->
<!-- To test actual content, recreate page template in Advanced Custom Fields, then remove "!" in "!$contentCheck" conditional below.  -->
<?php
if (have_posts()) {

    $contentCheck = get_field('image_one_group');
    if ($contentCheck) : ?>


        <div id="content" class="content-area">
            <main role="main" id="main" class="site-main">
                <div class="block">
                    <div class="container home-page">

                        <?php
                        $imageOne = get_field('image_one_group');
                        if ($imageOne) : ?>
                            <div class="image-one-group">
                                <figure>
                                    <img class="image-one" src="<?php echo $imageOne['image_one']['url']; ?>" alt="<?php echo $imageOne['image_one_caption']; ?>" />
                                    <figcaption class="image-one-caption">
                                        <?php echo $imageOne['image_one_caption']; ?>
                                    </figcaption>
                                </figure>
                            </div>
                        <?php endif; ?>

                        <?php
                        $sectionOne = get_field('section_one');
                        if ($sectionOne) : ?>
                            <div class="text-section">
                                <h1 class="section-heading"><?php echo $sectionOne['section_one_heading']; ?></h1>
                                <div class="columns">
                                    <div class="column">
                                        <p class="column-one">
                                            <?php echo $sectionOne['column_one']; ?>
                                        </p>
                                    </div>
                                    <div class="column">
                                        <div class="column-two">
                                            <div class="column-image-group">
                                                <figure>
                                                    <img class="column-image-large" src="<?php echo $sectionOne['column_two_image']['url']; ?>" alt="<?php echo $sectionOne['column_two_caption']; ?>" />
                                                    <figcaption class="column-image-caption">
                                                        <?php echo $sectionOne['column_two_caption']; ?>
                                                    </figcaption>
                                                </figure>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php
                        $sectionTwo = get_field('section_two');
                        if ($sectionTwo) : ?>
                            <div class="text-section">

                                <div class="columns">
                                    <div class="column">
                                        <h1 class="column-one-heading"><?php echo $sectionTwo['column_one_heading']; ?></h1>
                                        <p class="column-one"><?php echo $sectionTwo['column_one']; ?>
                                        </p>
                                    </div>
                                    <div class="column">
                                        <h1 class="column-two-heading"><?php echo $sectionTwo['column_two_heading']; ?></h1>
                                        <p class="column-two"><?php echo $sectionTwo['column_two']; ?>
                                        </p>
                                        <div class="column-image-group">
                                            <?php
                                            $smallImage = get_field($sectionTwo['column_two_image']);
                                            if ($smallImage) : ?>
                                                <figure>
                                                    <img class="column-image-small" src="<?php echo $sectionTwo['column_two_image']['url']; ?>" alt="<?php echo $SectionTwo['column_two_image_caption']; ?>" />
                                                    <figcaption class="column-image-caption">
                                                        <?php echo $sectionTwo['column_two_image_caption']; ?>
                                                    </figcaption>
                                                </figure>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </main><!-- #main -->
        </div><!-- #primary -->


        <!-- Lorem Ipsum Version -->
    <?php else : ?>

        <div id="content" class="content-area">
            <main role="main" id="main" class="site-main">
                <div class="block">
                    <div class="container home-page">

                        <div class="image-one-group">
                            <figure>
                                <img class="image-one" src="https://images.pexels.com/photos/2373357/pexels-photo-2373357.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500" alt="A flock of birds." />
                                <figcaption class="image-one-caption">
                                    Lorem ipsum dolor.
                                </figcaption>
                            </figure>
                        </div>

                        <div class="text-section">
                            <h1 class="section-heading">Lorem ipsum</h1>
                            <div class="columns">
                                <div class="column">
                                    <p class="column-one">Lorem ipsum dolor sit amet, consectetur <strong>adipisicing elit. Laborum aspernatur sit quibusdam odit modi ipsum,</strong> tempore eum non voluptatem dolorem nostrum minima molestiae quia a quo possimus praesentium consectetur. Dolorem, adipisci aut.
                                        <br />
                                        <br />
                                        <br />
                                        <br />
                                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim, vero quaerat facere nemo nihil temporibus rem deserunt qui deleniti quidem?
                                        <br />
                                        <br />
                                        Lorem ipsum dolor sit <strong>amet consectetur adipisicing elit.</strong> Repellat eveniet aliquam ad obcaecati architecto explicabo!
                                        <br />
                                        <br />
                                        <br />
                                        <br />
                                        <strong>Lorem ipsum dolor sit amet consectetur adipisicing elit. </strong>Officia magnam tenetur quidem blanditiis. Dicta ipsam voluptatem deserunt maiores aut illum!
                                        <br />
                                        <br />
                                        <br />
                                        <br />
                                        Lorem ipsum dolor sit, amet consectetur adipisicing elit. Nam qui dignissimos ullam <strong>aliquam rem quod, aliquid nemo officia,</strong>ipsa, ab architecto iure rerum.

                                    </p>
                                </div>
                                <div class="column">
                                    <div class="column-two">
                                        <div class="column-image-group">
                                            <figure>
                                                <img class="column-image-large" src="https://images.pexels.com/photos/2373357/pexels-photo-2373357.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500" alt="A flock of birds." />
                                                <figcaption class="column-image-caption">
                                                    Lorem ipsum, dolor sit amet dolor cotur adipisicing.
                                                    <br />
                                                    <br />
                                                    Lorem, ipsum
                                                    <br />
                                                    Lorem ipsum dolor sit amet sit ametadi elit. Magnam fuga, vel magnam fuga vel recus <strong> tempos autem deserunt</strong> illum.
                                                </figcaption>
                                            </figure>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="text-section">

                            <div class="columns">
                                <div class="column">
                                    <h1 class="column-one-heading">Lorem</h1>
                                    <p class="column-one">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Laborum aspernatur sit quibusdam odit modi ipsum, tempore eum non voluptatem dolorem nostrum minima molestiae quia a quo possimus praesentium consectetur. Dolorem, adipisci aut.

                                        <br /><br />
                                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus dolor vel corrupti repellat dolore, velit provident dolorum magni suscipit accusantium.
                                        <br /><br />
                                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Tempore, placeat.
                                    </p>
                                </div>
                                <div class="column">
                                    <h1 class="column-two-heading">Ipsum</h1>
                                    <p class="column-two">
                                        Amet consectetur adipisicing elit. Atque, nam sint dolorem nostrum cupiditate, alias est, recusandae ut tempora sapiente aperiam dicta possimus commodi nobis voluptates expedita omnis earum minima fugiat adipisci placeat repellat officiis ipsam? Eius quis numquam velit minus iste!</p>
                                    <div class="column-image-group">
                                        <figure>
                                            <img class="column-image-small" src="https://images.pexels.com/photos/2373357/pexels-photo-2373357.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500" alt="A flock of birds." />
                                            <figcaption class="column-image-caption">

                                            </figcaption>
                                        </figure>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </main><!-- #main -->
        </div><!-- #primary -->


<?php endif;
} ?>



<?php get_footer();
