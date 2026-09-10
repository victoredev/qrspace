<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
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
