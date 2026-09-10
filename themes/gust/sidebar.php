<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Gust
 */

?>

<?php if ( is_active_sidebar( 'sidebar-primary' ) ) : ?>
<aside class="py-16 bg-gray-100 border-b">
	<div class="max-w-screen-lg px-4 mx-auto space-y-8">
		<?php dynamic_sidebar( 'sidebar-primary' ); ?>
	</div>
</aside>
<?php endif; ?>
