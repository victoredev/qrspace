<?php
/**
 * Gust custom search form
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Gust
 */

$gust_search_params = apply_filters(
	'gust_search_form_params',
	array(
		'placeholder'   => __( 'Search posts and pages...', 'gust' ),
		'form_action'   => home_url( '/' ),
		'input_name'    => 's',
		'input_class'   => 'border p-2 rounded-l flex-1 w-full',
		'button_text'   => __( 'Search', 'gust' ),
		'button_class'  => 'bg-primary text-white p-2 rounded-r',
		'wrapper_class' => 'flex',
	)
);
?>

<form action="<?php echo esc_attr( $gust_search_params['form_action'] ); ?>" method="get">
	<div class="<?php echo esc_attr( $gust_search_params['wrapper_class'] ); ?>">
		<input type="search" name="<?php echo esc_attr( $gust_search_params['input_name'] ); ?>" class="<?php echo esc_attr( $gust_search_params['input_class'] ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $gust_search_params['placeholder'] ); ?>" />
		<button type="submit" class="<?php echo esc_attr( $gust_search_params['button_class'] ); ?>">
			<?php echo esc_html( $gust_search_params['button_text'] ); ?>
		</button>
	</div>
</form>
