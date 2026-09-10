<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();

get_header();
?>
<main id="site-content">
<?php
if ( have_posts() ) :
	?>
	<?php
	if ( ! is_paged() ) :
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content/post-latest' ); 
			break;
		endwhile;
	endif;
	?>

	<div class="my-8 space-y-4 md:my-16">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<div class="max-w-screen-md mx-4 md:w-8/12 md:mx-auto">
			<?php get_template_part( 'template-parts/content/post-list-item' ); ?>
		</div>
	<?php endwhile; ?>
	</div>
	<div class="max-w-screen-md mx-4 md:mx-auto">
		<?php the_posts_pagination(); ?>
	</div>
	<?php
else :
	get_template_part( 'template-parts/content/content-none' );
endif;
?>
</main>
<?php
get_sidebar();
get_footer();
