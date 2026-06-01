<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              https://welaunch.io/plugins/helpdesk/
 * @since             1.0.0
 * @package           WordPress_Helpdesk
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress TotalDesk
 * Plugin URI:        https://welaunch.io/plugins/helpdesk/
 * Description:       The All in One WordPress Helpdesk solution
 * Version:           1.8.3
 * Author:            weLaunch
 * Author URI:        https://welaunch.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wordpress-helpdesk
 * 
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wordpress-helpdesk-activator.php
 */
function activate_WordPress_Helpdesk() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-helpdesk-activator.php';
	$activator = new WordPress_Helpdesk_Activator();
	$activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wordpress-helpdesk-deactivator.php
 */
function deactivate_WordPress_Helpdesk() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-helpdesk-deactivator.php';
	WordPress_Helpdesk_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_WordPress_Helpdesk' );
register_deactivation_hook( __FILE__, 'deactivate_WordPress_Helpdesk' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-helpdesk.php';

/**
 * Run the Plugin
 * @author Daniel Barenkamp
 * @version 1.0.0
 * @since   1.0.0
 * @link    http://www.welaunch.io
 */
function run_WordPress_Helpdesk() {

	$plugin_data = get_plugin_data( __FILE__, true, false);
	$version = $plugin_data['Version'];

	$plugin = new WordPress_Helpdesk($version);
	$plugin->run();

}
function run_inbox_fetching($args = array()) {
	$plugin_data = get_plugin_data( __FILE__, true, false);
	$version = $plugin_data['Version'];

    $currentTime = time();
    $lastTimeChecked = get_option('helpdesk_inbox_check');
    if(!$lastTimeChecked) {
        $lastTimeChecked = update_option('helpdesk_inbox_check', $currentTime);
    } else {
        $nextTimeCheck = $lastTimeChecked + (60 * 60);
        if($currentTime < $nextTimeCheck) {
            return false;
        } else {
            $lastTimeChecked = update_option('helpdesk_inbox_check', $currentTime);
        }
    }

	$plugin = new WordPress_Helpdesk($version);
	$inbox = $plugin->inbox;
	$inbox->init();
	$inbox->run_cronjob();

}
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active('redux-dev-master/redux-framework.php') || is_plugin_active('redux-framework/redux-framework.php') ||  is_plugin_active('welaunch-framework/welaunch-framework.php') ){
	run_WordPress_Helpdesk();
	add_action ('run_wordpress_helpdesk_inbox_fetching', 'run_inbox_fetching'); 
} else {
	add_action( 'admin_notices', 'run_WordPress_Helpdesk_Not_Installed' );
}

function run_WordPress_Helpdesk_Not_Installed()
{
	?>
    <div class="error">
      <p><?php _e( 'WordPress Helpdesk requires the weLaunch Framework Please install or activate it before: https://www.welaunch.io/updates/welaunch-framework.zip', 'wordpress-helpdesk'); ?></p>
    </div>
    <?php
}