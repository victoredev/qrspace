<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Gust
 */

get_header();
?>
<div class="max-w-screen-lg px-4 mx-auto mb-8">
	<main id="site-content">
		<header class="mb-8">
			<h1 class="text-4xl font-bold">
				<?php _e( '404 - page not found', 'gust' ); ?>
			</h1>
		</header>
		<p class="my-8 text-lg text-gray-700">
			<?php _e( 'Sorry, we can’t find that page.', 'gust' ); ?>
		</p>
	</main>
</div>
<?php
get_sidebar();
get_footer();
