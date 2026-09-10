<?php
/**
 * Displays a single post
 * 
 * @package Gust
 */

$gust_image_options = array(
	'class' => 'absolute inset-0 object-cover object-center w-full h-full',
);
$gust_wrapper_classes = 'max-w-screen-lg text-center';
$gust_container_wrapper_classes = 'py-16';
if ( has_post_thumbnail() ) {
	$gust_container_wrapper_classes = 'pb-8 md:py-16';
	$gust_wrapper_classes = 'grid grid-cols-1 gap-8 max-w-screen-xl md:grid-cols-2';
}

$gust_is_page = is_singular( 'page' );
?>
<main id="site-content">
<div <?php post_class( 'bg-gray-100 ' . $gust_container_wrapper_classes ); ?>>
	<header class="mx-auto <?php echo esc_attr( $gust_wrapper_classes ); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
		<div class="relative block overflow-hidden bg-gray-200 md:rounded-md">
			<div class="h-[20rem] md:h-auto md:pb-[85%]"></div>
			<?php the_post_thumbnail( 'gust_post_image', $gust_image_options ); ?>
		</div>
		<?php endif; ?>
		<div class="flex items-center px-4 md:py-8 md:px-0">
			<div class="flex-1">
				<?php
				if ( ! $gust_is_page ) : 
					get_template_part( 'template-parts/content/post-categories' );
				endif;
				?>
				<h1 class="text-4xl font-extrabold md:text-7xl">
					<?php the_title(); ?>
				</h1>
				<?php if ( ! $gust_is_page ) : ?>
					<p class="mt-2 text-lg font-bold leading-7 text-gray-600"><?php the_date(); ?></p>
					<?php get_template_part( 'template-parts/content/post-tags' ); ?>
				<?php endif; ?>
			</div>
		</div>
	</header>
</div>
<?php
	$gust_content_class_names = 'mx-auto my-8 space-y-4 prose md:my-16 entry-content';
	$gust_content_class_names = apply_filters( 'gust_content_class_names', $gust_content_class_names );
?>
<div class="<?php echo esc_attr( $gust_content_class_names ); ?>">
	<?php the_content(); ?>
	<div class="clear-both"></div>
	<?php wp_link_pages(); ?>
</div>
<?php if ( ! $gust_is_page ) : ?>
<div class="px-4 mx-auto max-w-prose">
	<?php
	the_post_navigation(
		array(
			'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'gust' ) . '</span> <span class="nav-title">%title</span>',
			'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'gust' ) . '</span> <span class="nav-title">%title</span>',
		)
	);
	?>
</div>
<?php endif; ?>
<?php if ( comments_open() || get_comments_number() ) : ?>
	<div class="max-w-screen-lg px-4 mx-auto">
		<?php comments_template(); ?>
	</div>
<?php endif ?>
</main>
