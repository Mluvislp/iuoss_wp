<?php
	get_header();
	$account  = JNews\AccountPage::getInstance();
	$endpoint = $account->get_endpoint();
?>

<div class="jeg_main jeg_account_page">
	<div class="jeg_container">
		<div class="jeg_content">
			<div class="jeg_section">
				<div class="container">
					<div class="jeg_cat_content row">
						<div class="col-md-3 jeg_sticky_sidebar">
							<div class="jeg_account_left">
								<div class="jeg_account_nav">
									<ul>
										<?php foreach ( array_slice( $endpoint, 1 ) as $item ) : ?>
											<?php if ( isset( $item['guest'] ) && $item['guest'] ) : ?>
											<li>
												<a href="<?php echo esc_url( jnews_home_url_multilang( $endpoint['account']['slug'] . '/' . $item['slug'] ) ); ?>"><?php jnews_print_translation( $item['title'], 'jnews', $item['label'] ); ?></a>
											</li>
											<?php endif; ?>
										<?php endforeach ?>
									</ul>
								</div>
							</div>
						</div>
						<div class="col-md-9">
							<div class="jeg_account_right">
								<h1 class="jeg_account_title"><?php do_action( 'jnews_account_right_title' ); ?></h1>
								<?php do_action( 'jnews_account_right_content' ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php do_action( 'jnews_after_main' ); ?>
	</div>
</div>

<?php get_footer(); ?>