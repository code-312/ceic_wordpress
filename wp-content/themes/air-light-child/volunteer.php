<?php

/**
 * Template Name: Volunteer Opportunities
 *
 */

get_header(); ?>

<!-- Requires the Advanced Custom Fields Plugin - https://www.advancedcustomfields.com/ -->

<!-- Checking if page has been created in Advanced Custom Fields. If not, populates Lorem Ipsum version below -->
<!-- To test actual content, recreate page template in Advanced Custom Fields, then remove "!" in "!$contentCheck" conditional below.  -->
<?php
$contentCheck = get_field('first_section_-_welcome');
if ($contentCheck) : ?>


	<div id="content" class="content-area">
		<main role="main" id="main" class="site-main">
			<div class="block">
				<div class="container">


					<?php echo ("<h1>");
					the_title();
					echo ("</h1>"); ?>

					<?php
					$welcome = get_field('first_section_-_welcome');
					if ($welcome) : ?>
						<div class="section welcome-section">
							<!-- I put the "welcome text" into the figure to make the CSS easier. I imagine there's a better/more accessible way -DA -->
							<!-- Potential to do: use conditional logic to change the alt text if the user adds their own caption.  -DA -->
							<figure>
								<div>
									<p class="welcome-text"><?php echo $welcome['welcome_intro']; ?></p>
									<figcaption class="volunteer-caption">
										<?php echo $welcome['volunteer_image_description']; ?>
									</figcaption>

								</div>

								<img class="volunteer-image" src="<?php echo $welcome['volunteer_group_image']['url']; ?>" alt="<?php echo $welcome['volunteer_image_description']; ?>" />


							</figure>
						</div>


					<?php endif; ?>

					<!-- I couldn't figure out how to get sub_fields within sub_fields to render, so I ungrouped the columns in Advanced Custom Fields. -DA  -->
					<?php
					$help = get_field('second_section_-_how_to_help');
					if ($help) : ?>
						<div class="section help-section">
							<div class="help-content">
								<h2><?php echo $help['section_two_heading']; ?></h2>

								<div class="columns">
									<div class="column">
										<h3 class="column-heading"><?php echo $help['column_one_heading']; ?></h3>
										<p class="column-description"><?php echo $help['column_one_description']; ?></p>
									</div>
									<div class="column">
										<h3 class="column-heading"><?php echo $help['column_two_heading']; ?></h3>
										<p class="column-description"><?php echo $help['column_two_description']; ?></p>
									</div>
									<div class="column">
										<h3 class="column-heading"><?php echo $help['column_three_heading']; ?></h3>
										<p class="column-description"><?php echo $help['column_three_description']; ?></p>
									</div>
								</div>


							</div>
						</div>
					<?php endif; ?>

					<?php
					$events = get_field('third_section_-_upcoming_events');
					if ($events) : ?>
						<div class="section events-section">
							<div class="events-content">
								<h2><?php echo $events['section_three_heading']; ?></h2>
								<p><?php echo $events['placeholder']; ?></p>
							</div>
						</div>
					<?php endif; ?>

					<?php
					$donations = get_field('fourth_section_-_donations');
					if ($donations) : ?>
						<div class="section donations-section">
							<div class="donations-content">
								<h2><?php echo $donations['section_four_heading']; ?></h2>
								<p><?php echo $donations['section_four_description']; ?></p>
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
				<div class="container">
					<?php echo ("<h1>");
					the_title();
					echo ("</h1>"); ?>

					<div class="section welcome-section">
						<figure>
							<div>
								<p class="welcome-text">
									Lorem ipsum dolor sit amet Quam quas nihil eaque architecto.
									<br />
									<br />
									Sit, officia iure. Ea, doloremque. Lorem ipsum dolor sit amet.</p>
								<figcaption class="volunteer-caption">
									Lorem ipsum dolor.
									<br />
									Quam quas nihil eaque architecto.
								</figcaption>

							</div>

							<img class="volunteer-image" src="https://images.pexels.com/photos/66863/goose-water-bird-nature-bird-66863.jpeg?auto=compress&cs=tinysrgb&dpr=3&h=750&w=1260" alt="A goose up close and lookin at ya with some other geese hangin out in the background." />
						</figure>
					</div>

					<!-- I couldn't figure out how to get sub_fields within sub_fields to render, so I ungrouped the columns in Advanced Custom Fields. -DA -->

					<div class="section help-section">
						<div class="help-content">
							<h2>Lorem ipsum dolor sit amet consectetur?</h2>

							<div class="columns">
								<div class="column">
									<h3 class="column-heading">Lorem ipsum</h3>
									<p class="column-description">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Laborum aspernatur sit quibusdam odit modi ipsum, tempore eum non voluptatem dolorem nostrum minima molestiae quia a quo possimus praesentium consectetur. Dolorem, adipisci aut.</p>
								</div>
								<div class="column">
									<h3 class="column-heading">Dolor Sit</h3>
									<p class="column-description">
										Lorem ipsum dolor sit?
										<br />
										Amet consectetur adipisicing elit. Atque, nam sint dolorem nostrum cupiditate, alias est, recusandae ut tempora sapiente aperiam dicta possimus commodi nobis voluptates expedita omnis earum minima fugiat adipisci placeat repellat officiis ipsam? Eius quis numquam velit minus iste!</p>
								</div>
								<div class="column">
									<h3 class="column-heading">Consectetur?</h3>
									<p class="column-description">Lorem ipsum?
										<br />
										dolor sit amet consectetur adipisicing elit. Accusantium, consequuntur a? Corporis itaque iste cupiditate porro eos, iure delectus culpa sit eveniet voluptatum tempora quis inventore consequatur animi explicabo distinctio. Sit eum minima quis excepturi, quisquam veniam. Ipsa repellendus aliquam voluptas excepturi!</p>
								</div>
							</div>


						</div>
					</div>


					<div class="section events-section">
						<div class="events-content">
							<h2>Upcoming Events - need plugin?</h2>
							<p>TBD</p>
						</div>
					</div>



					<div class="section donations-section">
						<div class="donations-content">
							<h2>Lorem</h2>
							<p>
								Lorem ipsum dolor sit amet consectetur, adipisicing elit. Illum doloremque vitae labore laudantium maiores? Odit alias aut quis a aliquid modi, dolorem quaerat facilis sequi pariatur.

								<br />
								<br />

								Lorem ipsum dolor sit amet consectetur, adipisicing elit. Temporibus neque harum minus corporis quibusdam nam suscipit commodi veniam quas pariatur!

								<br />
								<br />

								Lorem ipsum dolor sit amet consectetur adipisicing elit. Corporis, cum quasi, aspernatur voluptatem magni deleniti et illum, ea labore a provident sit?

								<br />
								<br />

								Lorem ipsum dolor sit amet, consectetur adipisicing elit. Alias iure a rerum error ipsum magnam, natus reprehenderit reiciendis et vitae quia. Cupiditate repellendus nesciunt nemo voluptatum. Veniam, culpa! Alias consectetur laboriosam tenetur repudiandae ea tempora.

								<br />
								<br />

								Lorem ipsum dolor sit amet consectetur, adipisicing elit. Vel saepe exercitationem blanditiis quos eos vero sed quaerat dolor!
							</p>
						</div>
					</div>


				</div>
			</div>
	</div>

	</main><!-- #main -->
	</div><!-- #primary -->




<?php endif; ?>

<?php get_footer();
