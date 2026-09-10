<?php
/**
 * Displays a post's categories
 * 
 * @package Gust
 */

// get the name from the template, or default to category.
$gust_tax_name   = isset( $args['tax'] ) ? $args['tax'] : 'category';
$gust_categories = get_the_terms( get_the_ID(), $gust_tax_name );
?>
<?php if ( ! empty( $gust_categories ) ) : ?>
	<div class="mb-2 space-x-2">
		<?php foreach ( $gust_categories as $gust_cat_idx => $gust_category ) : ?>
			<?php if ( $gust_cat_idx > 0 ) : ?>
				<span class="inline-block h-2 border-l-2 border-gray-300"></span>
			<?php endif; ?>
			<a href="<?php echo esc_url( get_term_link( $gust_category ) ); ?>" class="text-xs font-semibold text-gray-500 uppercase">
				<?php echo esc_html( $gust_category->name ); ?>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
