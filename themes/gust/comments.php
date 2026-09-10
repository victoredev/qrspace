<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Gust
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="my-8 border-t comments-area">
	<?php if ( have_comments() ) : ?>
	<div class="my-8">
		<h2 class="my-8 text-3xl font-bold">
			<?php	
			$gust_comment_count = get_comments_number();
			if ( '1' === $gust_comment_count ) {
				printf(
				/* translators: 1: title. */
					esc_html__( 'One thought on &ldquo;%1$s&rdquo;', 'gust' ),
					'<span>' . wp_kses_post( get_the_title() ) . '</span>'
				);
			} else {
				printf(
				/* translators: 1: comment count number, 2: title. */
					esc_html( _nx( '%1$s thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', $gust_comment_count, 'comments title', 'gust' ) ),
					number_format_i18n( $gust_comment_count ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'<span>' . wp_kses_post( get_the_title() ) . '</span>'
				);
			}
			?>
		</h2>

		<div>
			<ol class="space-y-8">
				<?php
					wp_list_comments(
						array(
							'style'      => 'ol',
							'short_ping' => true,
							'walker'     => new Gust_Comment_Walker(),
						)
					);
				?>
			</ol>
		</div>
	</div>
	<?php endif; ?>
	<?php
	$gust = Gust::get_instance();
	the_comments_navigation();

	$gust->posts->comment_form();
	?>
</div>
