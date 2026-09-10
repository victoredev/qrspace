<?php
/**
 * Renders post comments
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();

class Gust_Comment_Walker extends Walker_Comment {

	protected function html5_comment( $comment, $depth, $args ) {
		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';

		$commenter          = wp_get_current_commenter();
		$show_pending_links = ! empty( $commenter['comment_author'] );

		if ( $commenter['comment_author_email'] ) {
			$moderation_note = __( 'Your comment is awaiting moderation.', 'gust' );
		} else {
			$moderation_note = __( 'Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.', 'gust' );
		}
		?>
	<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $this->has_children ? 'parent' : '', $comment ); ?>>
	  <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
		<footer class="comment-meta">
		  <div class="flex items-center mb-2 comment-author vcard">
			<?php
			if ( 0 != $args['avatar_size'] ) {
				echo "<div class='mr-2'>";
				echo get_avatar( $comment, $args['avatar_size'] );
				echo '</div>';
			}
			?>
			<div className="flex-grow">
			  <?php
				$comment_author = get_comment_author_link( $comment );

				if ( '0' == $comment->comment_approved && ! $show_pending_links ) {
					$comment_author = get_comment_author( $comment );
				}

				printf(
				/* translators: %s: Comment author link. */
					__( '%s <span class="says">says:</span>', 'gust' ),
					sprintf( '<b class="fn">%s</b>', $comment_author )
				);
				?>
			</div>
		  </div><!-- .comment-author -->

		  <div class="text-xs font-medium text-gray-500 comment-metadata">
			  <?php
				printf(
					'<a href="%s" class="underline"><time datetime="%s">%s</time></a>',
					esc_url( get_comment_link( $comment, $args ) ),
					get_comment_time( 'c' ),
					sprintf(
					/* translators: 1: Comment date, 2: Comment time. */
						__( '%1$s at %2$s', 'gust' ),
						get_comment_date( '', $comment ),
						get_comment_time()
					)
				);

			  edit_comment_link( __( 'Edit', 'gust' ), ' <span class="underline edit-link">', '</span>' );
				?>
		  </div><!-- .comment-metadata -->

			<?php if ( '0' == $comment->comment_approved ) : ?>
			<em class="comment-awaiting-moderation"><?php echo $moderation_note; ?></em>
		  <?php endif; ?>
		</footer><!-- .comment-meta -->

		<div class="mt-2 prose comment-content">
			<?php comment_text(); ?>
		</div><!-- .comment-content -->

		  <?php
			if ( '1' == $comment->comment_approved || $show_pending_links ) {
				comment_reply_link(
					array_merge(
						$args,
						array(
							'add_below' => 'div-comment',
							'depth'     => $depth,
							'max_depth' => $args['max_depth'],
							'before'    => '<div class="text-xs font-medium text-gray-500 reply ">',
							'after'     => '</div>',
						)
					)
				);
			}
			?>
	  </article><!-- .comment-body -->
		<?php
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @since 2.7.0
	 *
	 * @see Walker::start_lvl()
	 * @global int $comment_depth
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Optional. Depth of the current comment. Default 0.
	 * @param array  $args   Optional. Uses 'style' argument for type of HTML list. Default empty array.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 1;
 
		switch ( $args['style'] ) {
			case 'div':
				break;
			case 'ol':
				$output .= '<ol class="pl-2 mt-2 ml-2 space-y-8 border-l children">' . "\n";
				break;
			case 'ul':
			default:
				$output .= '<ul class="pl-2 mt-2 ml-2 space-y-8 border-l children">' . "\n";
				break;
		}
	}
}
