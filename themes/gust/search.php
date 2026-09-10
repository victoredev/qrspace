<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Gust
 */

get_header();
?>
<main id="site-content">
	<div class="max-w-screen-lg px-4 mx-auto mb-8 text-gray-900">
		<header class="mb-8">
			<h1 class="text-4xl font-bold">
				<?php printf( esc_html__( 'Search Results for: %s', 'gust' ), '<span>' . get_search_query() . '</span>' ); ?>
			</h1>
		</header>
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
			<div><?php echo esc_html__( 'We couldn’t find anything, sorry!', 'gust' ); ?></div>
		<?php endif; ?>
	</div>
</main>

<?php
get_sidebar();
get_footer();
