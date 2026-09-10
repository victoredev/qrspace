<?php
/**
 * Renders the Gust settings
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();

?>
<div class="wrap">
	<h1>Gust</h1>
	<p>
		If you have updated your safelist or Tailwind Config, you'll need to re-build your stylesheets.
	</p>
	<button class="button" id="gust-rebuild-css">Rebuild CSS</button>
	<table class="form-table">
		<tbody>

			<?php if ( $gust['urls']['header'] ) : ?>
				<tr>
					<th scope="row">Header</th>
					<td>
						<p>
							<a href="<?php echo esc_attr( $gust['urls']['header'] ); ?>" class="button">Edit Header</a>
						</p>
						<p>
							<a href="#" data-gust-action="gust_reset_region" data-gust-payload="header" class="gust-confirm-action">Reset default header</a>
						</p>
					</td>
				</tr>
			<?php endif; ?>
			<?php if ( $gust['urls']['footer'] ) : ?>
				<tr>
					<th scope="row">Footer</th>
					<td>
						<p>
							<a href="<?php echo esc_attr( $gust['urls']['footer'] ); ?>" class="button">Edit Footer</a>
						</p>
						<p>
							<a href="#" data-gust-action="gust_reset_region" data-gust-payload="footer" class="gust-confirm-action">Reset default footer</a>
						</p>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<form action="options.php" method="post">
		<?php
		settings_fields( 'gust' );
		do_settings_sections( 'gust' );
		submit_button( 'Save Settings' );
		?>
	</form>
</div>
