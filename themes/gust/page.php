<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Gust
 */

get_header();

$gust = Gust::get_instance();
while ( have_posts() ) :
	the_post();

	if ( $gust->admin->does_post_use_gust() ) {
		$gust->get_post_content();
	} else {
		get_template_part( 'template-parts/content/content-single' );
	}
endwhile;
get_sidebar();
get_footer();
