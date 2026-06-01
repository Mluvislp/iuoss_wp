

<div class='jeg_aside_item jeg_mobile_lang_switcher <?php echo esc_attr( get_theme_mod( 'jnews_header_language_dropdown', false ) ? 'dropdown' : '' ); ?>'>
<?php
	$elements = get_theme_mod( 'jnews_hb_element_mobile_drawer_bottom_center', jnews_header_default( 'drawer_element_bottom' ) );
	jnews_language_switcher( get_theme_mod( 'jnews_header_language_dropdown', false ) && ! in_array( 'language', $elements ), true );
?>
</div>