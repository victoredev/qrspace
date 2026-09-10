<?php
/**
 * Displays the post list item
 * 
 * @package Gust
 */

$gust_image_options = array(
	'class' => 'absolute inset-0 object-cover object-center w-full h-full',
);
?>

<div <?php post_class( 'overflow-hidden rounded shadow sm:flex' ); ?> >
	<a href="<?php the_permalink(); ?>" class="relative block overflow-hidden bg-gray-200 sm:w-[15.6875rem]">
		<div class="min-h-[10rem]"></div>
		<?php the_post_thumbnail( 'gust_post_thumbnail', $gust_image_options ); ?>
	</a>
	<div class="flex items-center p-4 md:py-8 sm:flex-1">
		<div>
			<h2 class="text-2xl font-extrabold leading-8">
				<a href="<?php the_permalink(); ?>">
					<?php the_title(); ?>
				</a>
			</h2>
			<p class="mt-2 mb-4 text-sm font-semibold leading-5 text-gray-400"><?php the_date(); ?></p>
			<div class="text-gray-700">
				<?php the_excerpt(); ?>
			</div>
		</div>
	</div>
</div>

