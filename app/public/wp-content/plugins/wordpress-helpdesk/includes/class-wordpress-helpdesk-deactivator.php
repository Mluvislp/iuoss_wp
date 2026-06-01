<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://www.welaunch.io
 * @since      1.0.0
 *
 * @package    WordPress_Helpdesk
 * @subpackage WordPress_Helpdesk/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WordPress_Helpdesk
 * @subpackage WordPress_Helpdesk/includes
 * @author     Daniel Barenkamp <support@welaunch.io>
 */
class WordPress_Helpdesk_Deactivator {

	/**
	 * On Plugin deactivation remove roles
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://www.welaunch.io
	 * @return  [type]                       [description]
	 */
	public static function deactivate() {
        remove_role('helpdesk_agent');
        remove_role('helpdesk_reporter');

		wp_clear_scheduled_hook('run_wordpress_helpdesk_inbox_fetching');
	}
}