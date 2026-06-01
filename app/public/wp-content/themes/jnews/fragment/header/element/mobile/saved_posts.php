<?php
if ( ! is_user_logged_in() || ! get_theme_mod( 'jnews_header_saved_posts_hide', false ) || is_customize_preview() ) {
	?>
		<div class="jeg_nav_item jeg_button_saved jeg_saved_posts">
			<?php
			jnews_saved_posts_button();
			?>
		</div>
	<?php
}
