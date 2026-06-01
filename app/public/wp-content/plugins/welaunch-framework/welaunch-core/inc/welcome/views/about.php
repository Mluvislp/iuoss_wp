<?php
if (!defined('WELAUNCH_DOWNLOAD_ENDPOINT')) {
    define('WELAUNCH_DOWNLOAD_ENDPOINT', 'https://www.welaunch.io/updates/paddle/download_by_license.php');
}

$domain = parse_url( get_site_url() )['host'];

/**
 * Admin View: Page - About
 *
 * @package weLaunch Framework
 */

defined( 'ABSPATH' ) || exit;

if (isset($_POST['welaunch_recheck']) && check_admin_referer('welaunch_recheck_nonce')) {
    welaunch_validate_licenses_cron_handler(true);
    echo '<div class="notice notice-success"><p>Re-checked licenses.</p></div>';
}

?>
<link
	rel='stylesheet' id='welaunch-welcome-css' <?php // phpcs:ignore WordPress.WP.EnqueuedResources ?>
	href='<?php echo esc_url( weLaunch_Core::$url ); ?>inc/welcome/css/welaunch-welcome.css'
	type='text/css' media='all'/>

<style type="text/css">
	.welaunch-badge:before {
	<?php echo is_rtl() ? 'right' : 'left'; ?>: 0;
	}

	.about-wrap .welaunch-badge {
	<?php echo is_rtl() ? 'left' : 'right'; ?>: 0;
	}

	.about-wrap .feature-rest div {
		padding- <?php echo is_rtl() ? 'left' : 'right'; ?>: 100px;
	}

	.about-wrap .feature-rest div.last-feature {
		padding- <?php echo is_rtl() ? 'right' : 'left'; ?>: 100px;
		padding- <?php echo is_rtl() ? 'left' : 'right'; ?>: 0;
	}

	.about-wrap .feature-rest div.icon:before {
		margin: <?php echo is_rtl() ? '0 -100px 0 0' : '0 0 0 -100px'; ?>;
	}
	.welaunch-row {
		margin: 40px 0;
		width: 100%;
		clear: both;
	}

	.welaunch-col-3 {
		width: 25%;
		float: left;
	}

	.about-wrap h2 {
		text-align: left;
		font-weight: 900;
	}

	.about-wrap h1 {
		color: #000;
		font-weight: 900;
	}

	.about-wrap h3 {
		color: #000;
		font-weight: 700;
	}

	.welaunch-product .name {
	    color: #23282d;
	    font-size: 26px;
	    font-weight: 800;
	}

	.welaunch-row:before {
	    clear: both;
	    float: none;
	    content: " ";
	    display: block;
	}

	.welaunch-product {
		padding: 25px;

	}

	.welaunch-product h2, .welaunch-product h3, .welaunch-product .name {
		margin-top: 0;
	}

	.welaunch-social-link {
		margin-right: 10px;
	}

	.welaunch-social-link svg {
		width: 30px;
		fill: #3171ee;
	}

	.welaunch-product ul {
	    list-style: disc;
	    margin-left: 20px;
	}

	input.btn.button {
	    background: #3171ee;
	    border-radius: 50px;
	    color: #fff;
	    border: none;
	    padding: 0 15px;
	}

	input.btn.button:hover {
		background-color: #467ff0;
	    color: #fff;
	    border: none;
	}

	.welaunch-product .type.we-invalid {
		background-color: red;
	}

			  .welaunch-button {
				  	background: #3171ee !important;
				    border-radius: 50px !important;
				    color: #fff;
				    border: none;
				    padding: 0 15px !important;
				  }
				  .welaunch-button.button-secondary {
				  	background: #fff !important;
				  	color: #3171ee !important;
				  }

</style>

<div class="wrap about-wrap">
	<div class="error hide">
		<p>weLaunch.io is running from within one of your products. To keep your site safe, please install the weLaunch
			Framework
			plugin from WordPress.org.</p>
	</div>
	<h1><?php printf( esc_html__( 'Welcome to', 'welaunch-framework' ) . ' weLaunch Framework', esc_html( $this->display_version ) ); ?></h1>


	<div class="about-text">
		<?php esc_html_e( "This framworks adds the possiblity to manage your licenses & adds the option to create admin panels.", 'welaunch-framework' ); ?>
		<p>Enter your license / purchase code from here to receive auto updates.<br>
		Licenses bought from our website are sent via Email and you see them directly after purchase.<br>
		For old CodeCanyon license keys please see: <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank">Where is my purchase code?</a></p>
		<b>License Support:</b> <a href="mailto:support@welaunch.io">support@welaunch.io</a>.<br>
	</div>

	<div class="welaunch-row">
		<form action="<?php echo esc_url($_SERVER['REQUEST_URI']) ?>" method="POST">
			<input type="hidden" name="action" value="welaunch_add_license">
			<input type="text" name="license" placeholder="Enter your license here ..."><input type="submit" value="Add License" class="btn button">
		</form>

        <form method="post">
            <?php wp_nonce_field('welaunch_recheck_nonce'); ?>
            <p><button class="button button-secondary" name="welaunch_recheck" value="1">Re-check now</button></p>
        </form>
	</div>

	<div class="welaunch-row">
	<?php
	    $lics      = welaunch_get_licenses();              // ['assoc'=>[item=>key], 'flat'=>[keys...]]
	    $weLaunchLicenses     = is_array($lics['assoc']) ? $lics['assoc'] : [];
	    $statusOpt = wl_get_option(WELAUNCH_LIC_STATUS_OPTION, []);
	    $statusMap = (is_array($statusOpt) && !empty($statusOpt['licenses'])) ? (array)$statusOpt['licenses'] : [];
	    $lastCheck = wl_get_option(WELAUNCH_LIC_LAST_CHECK, 0);
	    $lastCheckStr = $lastCheck ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), (int)$lastCheck) : 'never';

		if(empty($weLaunchLicenses)) {
			echo 'No licenses activated yet';
		} else {
			foreach ($weLaunchLicenses as $itemName => $license) {

	            $licKey = trim((string)$license);
	            $st     = isset($statusMap[$licKey]) && is_array($statusMap[$licKey]) ? $statusMap[$licKey] : [];
	            $isValid   = !empty($st['valid']);
	            $isExpired = !empty($st['expired']);
	            $domainOk  = array_key_exists('domain_ok', $st) ? (bool)$st['domain_ok'] : null;
	            $validUntil= !empty($st['valid_until']) ? $st['valid_until'] : null;
	            $message   = !empty($st['message']) ? $st['message'] : ($isValid ? 'OK' : '—');

				?>
				<div class="welaunch-product welaunch-col-3 ">
					<h3 class="name"><?php echo ucwords( str_replace('-', ' ', $itemName) ) ?></h3>
					<p class="author">By <a href="https://welaunch.io" target="_blank">weLaunch</a>
						<span class="type plugin <?php echo $isValid ? '' : 'we-invalid' ?>">
							
	                        <?php if ($isExpired): ?><span class="wl-badge err">Expired</span><?php endif; ?>
	                        <?php if ($domainOk === false): ?><span class="wl-badge err">Domain mismatch</span><?php endif; ?>
	                        <?php if ($domainOk === true && !$isExpired && $isValid): ?><span class="wl-badge ok">Active</span><?php endif; ?>
						</span>
					</p>
					<hr style="margin: 0 0 15px 0;padding:0;">
					<p class="author">
						<small>
							License: <?php echo $license ?>
						</small>
					</p>
					<?php if ($validUntil): ?>
                        <div class="wl-kv"><strong>Valid until:</strong> <?php echo esc_html($validUntil); ?></div>
                    <?php endif; ?>
                    <div class="wl-kv"><strong>Status:</strong> <?php echo esc_html($message); ?></div>
                    <br>

					<form action="<?php echo esc_url($_SERVER['REQUEST_URI']) ?>" method="POST">

                    	<a target="_blank" class="welaunch-button button button-primary button-hero" href="https://www.welaunch.io/product/<?php echo $itemName ?>">Buy License</a>
                    	<a target="_blank" class="welaunch-button button button-primary button-hero" href="<?php echo WELAUNCH_DOWNLOAD_ENDPOINT . '?license=' . rawurlencode($license) . '&domain=' . rawurlencode($domain); ?>">Download Plugin</a>

						<input type="hidden" name="action" value="welaunch_remove_license">
						<input type="hidden" name="item" value="<?php echo $itemName ?>">
						<input type="hidden" name="license" value="<?php echo $license ?>">
						<input class="welaunch-button button button-primary button-hero button-secondary" type="submit" value="Remove License" class="btn button">
					</form>

				</div>

				<?php
			}
		}
	?>
	</div>
	<div class="welaunch-row">
		<h2>Explore weLaunch</h2>

		<div class="welaunch-col-3 welaunch-product">
			<h3>Useful links</h3>
			<p>Before you submit a ticket or contact our support, please use our knowledge base or watch one of our documentation videos.</p>
			<ul>
				<li><a href="https://www.welaunch.io/en/knowledge-base/" target="_blank">Knowledge Base & Documentation</a></li>
				<li><a href="https://www.welaunch.io/en/shop/plugins/" target="_blank">Our Plugins</a></li>
				<li><a href="https://www.welaunch.io/en/contact/#new-ticket" target="_blank">Submit a Ticket</a></li>
			</ul>
			
			<a href="https://www.facebook.com/welaunch.io/" class="welaunch-social-link" target="_blank">
				<svg role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>Facebook icon</title><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
			</a>
			<a href="https://www.youtube.com/channel/UChBb04b2ImK2UvckLzOmF1Q" class="welaunch-social-link" target="_blank">
				<svg role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>YouTube icon</title><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
			</a>
		</div>

		<div class="welaunch-col-3 welaunch-product">
			<h3>How To Videos</h3>
			<iframe width="100%" height="315" src="https://www.youtube.com/embed/videoseries?list=PL3X3Yzbs6Tue7EoIAIDDZpA8j25eio23b" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</div>
	</div>
</div>
