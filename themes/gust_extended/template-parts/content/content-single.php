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
<?php
	$gust_content_class_names = 'mx-auto space-y-4 prose max-w-screen-lg entry-content';
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
