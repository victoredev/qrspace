<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Gust
 */

get_header();
?>
<main id="site-content">
	<div class="py-8 mb-8 bg-gray-100 md:py-16 md:mb-16">
		<header class="max-w-screen-lg px-4 mx-auto">
			<h1 class="text-4xl font-extrabold md:text-7xl">
				<?php the_archive_title(); ?>
			</h1>
			<div class="mt-8 text-lg text-gray-700">
				<?php the_archive_description(); ?>
			</div>
		</header>
	</div>
	<div class="max-w-screen-lg px-4 mx-auto mb-8 text-gray-900">
		<?php if ( have_posts() ) : ?>
		<div class="grid grid-cols-1 gap-8">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/post-list-item' );
			endwhile;
			?>
		</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content-none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_sidebar();
get_footer();
