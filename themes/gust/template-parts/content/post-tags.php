<?php
/**
 * Displays a post's tags
 * 
 * @package Gust
 */

$gust_term = isset( $args['term'] ) ? $args['term'] : 'post_tag';
$gust_tags = get_the_terms( get_the_ID(), $gust_term );
?>
<?php if ( ! empty( $gust_tags ) ) : ?>
	<div class="mt-2 space-x-2">
		<?php foreach ( $gust_tags as $gust_tag ) : ?>
			<a href="<?php echo esc_url( get_term_link( $gust_tag ) ); ?>" class="px-2 py-1 my-2 text-xs font-semibold text-white rounded-full bg-primary hover:opacity-80">
				<?php echo esc_html( $gust_tag->name ); ?>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
