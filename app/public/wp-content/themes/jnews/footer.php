		<div class="footer-holder" id="footer" data-id="footer">
			<?php
				$footer_style = get_theme_mod( 'jnews_footer_style', '1' );
				$js_scroll    = get_theme_mod( 'jnews_scroll_to_top_desktop', true ) ? 'desktop' : '';
				$js_scroll    = get_theme_mod( 'jnews_scroll_to_top_mobile', false ) ? $js_scroll . ' mobile' : $js_scroll;
			if ( $footer_style === 'custom' ) {
				get_template_part( 'fragment/footer/footer-custom' );
			} else {
				get_template_part( 'fragment/footer/footer-' . $footer_style );
			}
			?>
		</div>
		<?php if ( ! empty( $js_scroll ) ) : ?>
			<div class="jscroll-to-top <?php echo esc_html( $js_scroll ); ?>">
				<a href="#back-to-top" class="jscroll-to-top_link"><i class="fa fa-angle-up"></i></a>
			</div>
		<?php endif; ?>
	</div>

	<?php

	if ( apply_filters( 'jnews_can_render_account_popup', false ) ) {
		get_template_part( 'fragment/account/account-popup' );
	}

		wp_footer();
	?>
</body>
</html>
