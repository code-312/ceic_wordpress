<?php

/**
 * Template Name: About Page
 *
 */

get_header(); ?>

<!-- Requires the Advanced Custom Fields Plugin - https://www.advancedcustomfields.com/ -->

<!-- Checking if page has been created in Advanced Custom Fields. If not, populates Lorem Ipsum version below -->
<!-- To test actual content, recreate page template in Advanced Custom Fields, then remove "!" in "!$contentCheck" conditional below.  -->
<?php
$contentCheck = get_field('image_one_group');
if ($contentCheck) : ?>


    <div id="content" class="content-area">
        <main role="main" id="main" class="site-main">
            <div class="block">
                <div class="container">


                    <div class="container about-page">

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
                                        <p class="column-one"><?php echo $sectionOne['column_one']; ?>
                                        </p>
                                    </div>
                                    <div class="column">
                                        <p class="column-two"><?php echo $sectionOne['column_two']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php
                        $imageTwo = get_field('image_two_group');
                        if ($imageTwo) : ?>
                            <div class="image-two-group">
                                <figure>
                                    <img class="image-two" src="<?php echo $imageTwo['image_two']['url']; ?>" alt="<?php echo $imageTwo['image_two_caption']; ?>" />
                                    <figcaption class="image-two-caption">
                                        <?php echo $imageTwo['image_two_caption']; ?>
                                    </figcaption>
                                </figure>
                            </div>
                        <?php endif; ?>

                        <?php
                        $sectionTwo = get_field('section_two');
                        if ($sectionTwo) : ?>
                            <div class="text-section">

                                <div class="columns">
                                    <div class="column">
                                        <h1 class="column-one-heading"><?php echo $sectionTwo['column_one_heading']; ?></h1>
                                        <p class="column-one"><?php echo $sectionTwo['column_one']; ?></p>
                                    </div>
                                    <div class="column">
                                        <h1 class="column-two-heading"><?php echo $sectionTwo['column_two_heading']; ?></h1>
                                        <p class="column-two"><?php echo $sectionTwo['column_two']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php
                        $imageThree = get_field('image_three_group');
                        if ($imageThree) : ?>
                            <div class="image-three-group">
                                <figure>
                                    <img class="image-three" src="<?php echo $imageThree['image_three']['url']; ?>" alt="<?php echo $imageThree['image_three_caption']; ?>" />
                                    <figcaption class="image-three-caption">
                                        <?php echo $imageThree['image_three_caption']; ?>
                                    </figcaption>
                                </figure>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </main><!-- #main -->
    </div><!-- #primary -->


    <!-- Lorem Ipsum Version -->
<?php else : ?>

    <div id="content" class="content-area">
        <main role="main" id="main" class="site-main">
            <div class="block">
                <div class="container about-page">

                    <div class="image-one-group">
                        <figure>
                            <img class="image-one" src="https://cannabisequityil.org/wp-content/uploads/2020/09/Coalition-planning-2.jpg" alt="A flock of birds." />
                            <figcaption class="image-one-caption">
                                Lorem ipsum dolor.
                            </figcaption>
                        </figure>
                    </div>

                    <div class="text-section">
                        <h1 class="section-heading">Lorem ipsum</h1>
                        <div class="columns">
                            <div class="column">
                                <p class="column-one">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Laborum aspernatur sit quibusdam odit modi ipsum, tempore eum non voluptatem dolorem nostrum minima molestiae quia a quo possimus praesentium consectetur. Dolorem, adipisci aut.
                                    <br />
                                    <br />
                                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim, vero quaerat facere nemo nihil temporibus rem deserunt qui deleniti quidem?
                                    <br />
                                    <br />
                                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellat eveniet aliquam ad obcaecati architecto explicabo!
                                </p>
                            </div>
                            <div class="column">
                                <p class="column-two">
                                    Amet consectetur adipisicing elit. Atque, nam sint dolorem nostrum cupiditate, alias est, recusandae ut tempora sapiente aperiam dicta possimus commodi nobis voluptates expedita omnis earum minima fugiat adipisci placeat repellat officiis ipsam? Eius quis numquam velit minus iste!</p>
                            </div>
                        </div>
                    </div>
                    <div class="image-two-group">
                        <figure>
                            <img class="image-two" src="https://cannabisequityil.org/wp-content/uploads/2020/09/Regina-Sharon-Minnie-RAP.jpg" alt="A goose up close and lookin at ya with some other geese hangin out in the background." />
                            <figcaption class="image-two-caption">
                                Lorem ipsum dolor.
                            </figcaption>
                        </figure>
                    </div>

                    <div class="text-section">

                        <div class="columns">
                            <div class="column">
                                <h1 class="column-one-heading">Lorem ipsum</h1>
                                <p class="column-one">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Laborum aspernatur sit quibusdam odit modi ipsum, tempore eum non voluptatem dolorem nostrum minima molestiae quia a quo possimus praesentium consectetur. Dolorem, adipisci aut.

                                    <br /><br />
                                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus dolor vel corrupti repellat dolore, velit provident dolorum magni suscipit accusantium.
                                    <br /><br />
                                    Lorem, ipsum dolor sit amet consectetur adipisicing elit. Tempore, placeat.
                                </p>
                            </div>
                            <div class="column">
                                <h1 class="column-two-heading">Lorem ipsum</h1>
                                <p class="column-two">
                                    Amet consectetur adipisicing elit. Atque, nam sint dolorem nostrum cupiditate, alias est, recusandae ut tempora sapiente aperiam dicta possimus commodi nobis voluptates expedita omnis earum minima fugiat adipisci placeat repellat officiis ipsam? Eius quis numquam velit minus iste!</p>
                            </div>
                        </div>
                    </div>
                    <div class="image-three-group">
                        <figure>
                            <img class="image-three" src="https://cannabisequityil.org/wp-content/uploads/2020/09/resource-1.jpg" alt="A goose standing on a chain-link fence." />
                            <figcaption class="image-three-caption">
                                Lorem ipsum dolor.
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </div>

        </main><!-- #main -->
    </div><!-- #primary -->


<?php endif; ?>



<?php get_footer();
