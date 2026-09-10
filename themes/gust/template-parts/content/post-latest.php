<?php
/**
 * Displays the latest post
 * 
 * @package Gust
 */

$gust_image_options = array(
	'class' => 'absolute inset-0 object-cover object-center w-full h-full',
);
?>
<section <?php post_class( 'pb-8 bg-gray-100 md:p-16' ); ?>>
	<div class="grid max-w-screen-xl grid-cols-1 gap-8 mx-auto md:grid-cols-2">
		<a href="<?php the_permalink(); ?>" class="relative block overflow-hidden bg-gray-200 md:rounded-md">
			<div class="h-[20rem] md:h-auto md:pb-[85%]"></div>
			<?php the_post_thumbnail( 'gust_post_image', $gust_image_options ); ?>
		</a>
		<div class="flex items-center px-4 md:py-8 md:px-0">
			<div>
				<p class="text-lg font-bold leading-7 text-gray-600 uppercase">Latest post</p>
				<h1 class="text-4xl font-extrabold md:text-7xl">
					<a href="<?php the_permalink(); ?>">
						<?php the_title(); ?>
					</a>
				</h1>
			</div>
		</div>
	</div>
</section>
