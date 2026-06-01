<?php // phpcs:ignore Squiz.Commenting.FileComment.Missing
/**
 * weLaunch, a simple, truly extensible and fully responsive options framework
 * for WordPress themes and plugins. Developed with WordPress coding
 * standards and PHP best practices in mind.
 *
 * Plugin Name:     weLaunch Framework
 * Plugin URI:      https://welaunch.io
 * Description:     Framework for weLaunch Plugins (this is a fork of Redux Plugin)
 * Author:          weLaunch.io
 * Author URI:      https://welaunch.io
 * Version:         1.2.4
 * Text Domain:     welaunch-framework
 * License:         GPLv3 or later
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Provides:        weLaunchFramework
 *
 * @package         weLaunchFramework
 * @author          weLaunch.io
 * @license         GNU General Public License, version 3
 * @copyright       2012-2020 weLaunch.io
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WELAUNCH_PLUGIN_FILE' ) ) {
	define( 'WELAUNCH_PLUGIN_FILE', __FILE__ );
}

require __DIR__ . '/vendor/autoload.php';

global $weLaunchLicenses, $weLaunchPlugins;
if(is_multisite()) {
	$weLaunchLicenses = get_network_option(0, 'welaunch_licenses');
} else {
	$weLaunchLicenses = get_option('welaunch_licenses');
}

// add_filter( 'unzip_file_use_ziparchive', '__return_false' );

//list all private plugins we are interested in
$weLaunchPlugins = array(
	'welaunch-framework',
	'woocommerce-delivery',
	'wordpress-store-locator',
	'wordpress-gdpr',
	'woocommerce-pdf-catalog',
	'woocommerce-store-locator',
	'woocommerce-single-variations',
	'woocommerce-reward-points',
	'woocommerce-catalog-mode',
	'wordpress-multilingual-multisite',
	'woocommerce-plugin-bundle',
	'woocommerce-print-products',
	'wordpress-helpdesk',
	'wordpress-country-selector',
	'woocommerce-my-account',
	'wordpress-print-posts',
	'woocommerce-packing-slips',
	'woocommerce-ultimate-pdf-invoices',
	'woocommerce-variations-table',
	'woocommerce-group-attributes',
	'woocommerce-gallery-images',
	'woocommerce-advanced-categories',
	'woocommerce-attribute-images',
	'woocommerce-ultimate-pricing',
	'woocommerce-ultimate-tabs',
	'woocommerce-better-compare',
	'wordpress-multisite-sync',
	'wordpress-cf7-stripe',
	'woocommerce-buying-guide',
	'woocommerce-product-accessories',
	'wordpress-fire-push',
	'wordpress-cf7-paypal',
	'woocommerce-multisite-duplicate',
	'woocommerce-cart-pdf',
	'woocommerce-bought-together',
	'woocommerce-shop-look',
	'woocommerce-dark-pattern',
	'woocommerce-wishlist',
	'vc-restaurant-menu',
	'woocommerce-quick-order',
	'woocommerce-quick-view',
	'wordpress-pdf-catalog',
	'vc-personalization',

	'woocommerce-product-retailers',
	'woocommerce-multi-inventory',
	'woocommerce-bottle-deposit',
	'woocommerce-quote-negotiation',

	'wordpress-form-wizard',
	'woocommerce-lense-configurator',
	'woocommerce-pdf-orders',
);

require __DIR__ . '/welaunch-core/core/lc.php';

if ( ! function_exists( 'get_plugins' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if(class_exists('Puc_v4_Factory')) {

	$weLaunchUpdater = array();
	$weLaunchAllPlugins = get_plugins();
	foreach($weLaunchAllPlugins as $weLaunchAllPluginSlug => $weLaunchAllPluginInfo) {

		$weLaunchAllPluginSlug =  explode('/', $weLaunchAllPluginSlug)[0];
		$welaunchPlugin = array_search($weLaunchAllPluginSlug, $weLaunchPlugins, true);
		if($welaunchPlugin !== false) {

			$welaunchPlugin = $weLaunchPlugins[$welaunchPlugin];
	        $weLaunchFilePath = trailingslashit(WP_PLUGIN_DIR) . $welaunchPlugin . '/' . $welaunchPlugin . '.php';

	        $license = '';
	        $pluginLicense = $welaunchPlugin;
	        if($welaunchPlugin == "woocommerce-catalog-mode") {
	        	$pluginLicense = 'woocommerce-product-catalog-mode';
	        }

		    if ( isset($weLaunchLicenses[$pluginLicense]) && !empty($weLaunchLicenses[$pluginLicense]) ) {
		        $license = $weLaunchLicenses[$pluginLicense];
		    }
		
		    if ( substr($welaunchPlugin, 0, 11) === 'woocommerce' && isset($weLaunchLicenses['woocommerce-plugin-bundle']) && !empty($weLaunchLicenses['woocommerce-plugin-bundle']) ) {
		        $license = $weLaunchLicenses['woocommerce-plugin-bundle'];
		    }

		    if ( substr($welaunchPlugin, 0, 11) === 'wordpress' && isset($weLaunchLicenses['wordpress-plugin-bundle']) && !empty($weLaunchLicenses['wordpress-plugin-bundle']) ) {
		        $license = $weLaunchLicenses['wordpress-plugin-bundle'];
		    }

		    if ( isset($weLaunchLicenses['welaunch-plugin-bundle']) && !empty($weLaunchLicenses['welaunch-plugin-bundle']) ) {
		        $license = $weLaunchLicenses['welaunch-plugin-bundle'];
		    }

		    if ( isset($weLaunchLicenses['agency-bundle']) && !empty($weLaunchLicenses['agency-bundle']) ) {
		    	$license = $weLaunchLicenses['agency-bundle'];
	    	}

		    if(empty($license) && $welaunchPlugin !== "welaunch-framework") {
		    	continue;
		    }
			
			$domain = parse_url( get_site_url() )['host'];

	        $weLaunchUpdater[] = Puc_v4_Factory::buildUpdateChecker(
	            'https://www.welaunch.io/updates/paddle/updates.php?plugin=' . $welaunchPlugin . '&license=' . $license . '&domain=' . $domain,
	            $weLaunchFilePath,
	            $welaunchPlugin
	        );
	    }
	}
}

// Require the main plugin class.
require_once plugin_dir_path( __FILE__ ) . 'class-welaunch-framework-plugin.php';

// Register hooks that are fired when the plugin is activated and deactivated, respectively.
register_activation_hook( __FILE__, array( 'weLaunch_Framework_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'weLaunch_Framework_Plugin', 'deactivate' ) );

// Get plugin instance.
weLaunch_Framework_Plugin::instance();

add_action( 'before_woocommerce_init', function() {



    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {

    	global $weLaunchPlugins;

    	foreach($weLaunchPlugins as $weLaunchPlugin) {

    		if(!is_plugin_active($weLaunchPlugin . '/' . $weLaunchPlugin . '.php')) {
    			continue;
    		}

			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $weLaunchPlugin, true );
		}

    }
} );