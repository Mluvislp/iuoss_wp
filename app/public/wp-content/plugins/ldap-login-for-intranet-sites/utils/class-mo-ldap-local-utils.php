<?php
/**
 * Plugin utilities.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage utils
 */

namespace MO_LDAP\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Mo_Ldap_Local_Utils' ) ) {
	/**
	 * Utility class.
	 */
	class Mo_Ldap_Local_Utils {

		/**
		 * Function is_extension_installed : Check if PHP extension is enabled
		 *
		 * @param  mixed $name : PHP extension name.
		 * @return bool
		 */
		public static function is_extension_installed( $name ) {
			return in_array( $name, get_loaded_extensions(), true );
		}

		/**
		 * Function is_customer_registered : Check if customer is registered.
		 *
		 * @return bool
		 */
		public static function is_customer_registered() {
			$email        = get_option( 'mo_ldap_local_admin_email' );
			$customer_key = get_option( 'mo_ldap_local_admin_customer_key' );
			if ( ! $email || ! $customer_key || ! is_numeric( trim( $customer_key ) ) ) {
				return 0;
			} else {
				return 1;
			}
		}

		/**
		 * Function get_role_names : Get role names.
		 *
		 * @return array
		 */
		public static function get_role_names() {
			global $wp_roles;
			return $wp_roles->get_names();
		}

		/**
		 * Function encrypt : Encrypt a string
		 *
		 * @param  string $str : String to be encrypted.
		 * @return string
		 */
		public static function encrypt( $str ) {
			if ( ! self::is_extension_installed( 'openssl' ) ) {
				return;
			}

			$key           = get_option( 'mo_ldap_local_customer_token' );
			$method        = 'AES-128-ECB';
			$encrypted_str = openssl_encrypt( $str, $method, $key, OPENSSL_RAW_DATA || OPENSSL_ZERO_PADDING );

			return base64_encode( $encrypted_str ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- function not being used to obfuscate the code
		}

		/**
		 * Function decrypt : Decrypt a string
		 *
		 * @param  string $value : String to be decrypted.
		 * @return string
		 */
		public static function decrypt( $value ) {
			if ( ! self::is_extension_installed( 'openssl' ) ) {
				return;
			}

			$str_in  = base64_decode( $value ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- function not being used to obfuscate the code
			$key     = get_option( 'mo_ldap_local_customer_token' );
			$method  = 'AES-128-ECB';
			$iv_size = openssl_cipher_iv_length( $method );
			$data    = substr( $str_in, $iv_size );
			return openssl_decrypt( $data, $method, $key, OPENSSL_RAW_DATA || OPENSSL_ZERO_PADDING );
		}

		/**
		 * Function generate_random_string : Used to form a random string
		 *
		 * @param  int $length : Lenngth of the randomo strength requested.
		 * @return string
		 */
		public static function generate_random_string( $length = 8 ) {
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

			$crypto_rand_secure = function ( $min, $max ) {
				$range = $max - $min;
				if ( $range < 0 ) {
					return $min;
				}
				$log    = log( $range, 2 );
				$bytes  = (int) ( $log / 8 ) + 1;
				$bits   = (int) $log + 1;
				$filter = (int) ( 1 << $bits ) - 1;
				do {
					$rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
					$rnd = $rnd & $filter;
				} while ( $rnd >= $range );
				return $min + $rnd;
			};

			$token = '';
			$max   = strlen( $pool );
			for ( $i = 0; $i < $length; $i++ ) {
				$token .= $pool[ $crypto_rand_secure( 0, $max ) ];
			}
			return $token;
		}

		/**
		 * Function mo_ldap_report_update : Add log to user auth report.
		 *
		 * @param  mixed $username : Username of user who attempted login.
		 * @param  mixed $status : Status of Login.
		 * @param  mixed $ldap_error : LDAP error message.
		 * @return void
		 */
		public function mo_ldap_report_update( $username, $status, $ldap_error ) {
			if ( strcmp( get_option( 'mo_ldap_local_user_report_log' ), '1' ) === 0 ) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'user_report';
				$wpdb->insert( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Inserting data into a custom table.
					$table_name,
					array(
						'user_name'   => $username,
						'time'        => current_time( 'mysql' ),
						'ldap_status' => $status,
						'ldap_error'  => $ldap_error,

					)
				);
				wp_cache_delete( 'mo_ldap_user_report_cache' );
				wp_cache_delete( 'mo_ldap_user_report_count_cache' );
				wp_cache_delete( 'wp_user_reports_pagination_cache' );
			}
		}

		/**
		 * Function check_empty_or_null : Check of string is empty or null
		 *
		 * @param  string $value : String to be checked.
		 * @return bool
		 */
		public static function check_empty_or_null( $value ) {
			if ( ! isset( $value ) || empty( $value ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Function is_administrator_user : Check if user is administrator.
		 *
		 * @param  object $user : WordPress user object.
		 * @return bool
		 */
		public function is_administrator_user( $user ) {
			$user_role = ( $user->roles );
			return ( ! is_null( $user_role ) && in_array( 'administrator', $user_role, true ) );
		}

		/**
		 * Function mo_ldap_is_user_logs_empty : Check if user auth logs exist or not.
		 *
		 * @return bool
		 */
		public static function mo_ldap_is_user_logs_empty() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'user_report';

			wp_cache_delete( 'mo_ldap_user_report_count_cache' );
			$mo_user_report_table_exist = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) ) === $table_name; //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Fetching data from a custom table.
			if ( $mo_user_report_table_exist ) {
				$wp_user_reports_count_cache = wp_cache_get( 'mo_ldap_user_report_count_cache' );
				if ( $wp_user_reports_count_cache ) {
					$user_count = $wp_user_reports_count_cache;
				} else {
					$user_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}user_report" ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Fetching data from a custom table.
					wp_cache_set( 'mo_ldap_user_report_count_cache', $user_count );
				}
				if ( 0 < $user_count ) {
					return false;
				}
			}
			return true;
		}

		/**
		 * Function success_message : Show Success message on UI.
		 *
		 * @return void
		 */
		public function success_message() {
			$class        = 'updated';
			$message      = get_option( 'mo_ldap_local_message' );
			$success_list = explode( '<br>', $message );
			$right_icon   = MO_LDAP_LOCAL_IMAGES . 'success_msg.png';
			echo "<div id='success' class='mo_ldap_local_message_container'>
					<div class='mo_ldap_local_message mo_ldap_local_message_desc'>
						<div class='mo_ldap_local_message_left'>
							<img width='26px' height='26px' src='" . esc_url( $right_icon ) . "'/>
							<p id='mo_ldap_local_message_title' class='mo_ldap_local_message_content'>" . wp_kses( $success_list[0], MO_LDAP_LOCAL_ESC_ALLOWED ) . "</p>
							<p id='mo_ldap_local_message_desc' class='mo_ldap_local_message_content_desc d-none'>" . wp_kses( $message, MO_LDAP_LOCAL_ESC_ALLOWED ) . '</p>
						</div>
						' . ( ( count( $success_list ) > 1 ) ? "<button id='mo_ldap_local_view_more_button' class='mo_ldap_local_view_more_button'><svg enable-background='new 0 0 32 32' height='32px' viewBox='0 0 65 40' width='32px' xml:space='preserve'><path d='M18.221,7.206l9.585,9.585c0.879,0.879,0.879,2.317,0,3.195l-0.8,0.801c-0.877,0.878-2.316,0.878-3.194,0  l-7.315-7.315l-7.315,7.315c-0.878,0.878-2.317,0.878-3.194,0l-0.8-0.801c-0.879-0.878-0.879-2.316,0-3.195l9.587-9.585  c0.471-0.472,1.103-0.682,1.723-0.647C17.115,6.524,17.748,6.734,18.221,7.206z' fill='#ffffff'/></svg></button>" : '' ) . '
					</div>
				</div>';
		}

		/**
		 * Function error_message : Add/Display error message on UI.
		 *
		 * @return void
		 */
		public function error_message() {
			$message    = get_option( 'mo_ldap_local_message' );
			$error_list = explode( '<br>', $message );
			$wrong_icon = MO_LDAP_LOCAL_IMAGES . 'error_msg.png';
			echo "<div id='error' class='mo_ldap_local_message_container'>
					<div class='mo_ldap_local_message mo_ldap_error_message'>
						<div class='mo_ldap_local_message_left'>
							<img width='26px' height='26px' src='" . esc_url( $wrong_icon ) . "'/>
							<p id='mo_ldap_local_message_title' class='mo_ldap_local_message_content'>" . wp_kses( $error_list[0], MO_LDAP_LOCAL_ESC_ALLOWED ) . "</p>
							<p id='mo_ldap_local_message_desc' class='mo_ldap_local_message_content_desc d-none'>" . wp_kses( $message, MO_LDAP_LOCAL_ESC_ALLOWED ) . '</p>
						</div>
						' . ( ( count( $error_list ) > 1 ) ? "<button id='mo_ldap_local_view_more_button' class='mo_ldap_local_view_more_button'><svg enable-background='new 0 0 32 32' height='32px' viewBox='0 0 65 40' width='32px' xml:space='preserve'><path d='M18.221,7.206l9.585,9.585c0.879,0.879,0.879,2.317,0,3.195l-0.8,0.801c-0.877,0.878-2.316,0.878-3.194,0  l-7.315-7.315l-7.315,7.315c-0.878,0.878-2.317,0.878-3.194,0l-0.8-0.801c-0.879-0.878-0.879-2.316,0-3.195l9.587-9.585  c0.471-0.472,1.103-0.682,1.723-0.647C17.115,6.524,17.748,6.734,18.221,7.206z' fill='#ffffff'/></svg></button>" : '' ) . '
					</div>
				</div>';
		}

		/**
		 * Function show_error_message : Calls error_message
		 *
		 * @return void
		 */
		public function show_error_message() {
			remove_action( 'admin_notices', array( $this, 'success_message' ) );
			add_action( 'admin_notices', array( $this, 'error_message' ) );
		}

		/**
		 * Function show_success_message : Calls success_message
		 *
		 * @return void
		 */
		public function show_success_message() {
			remove_action( 'admin_notices', array( $this, 'error_message' ) );
			add_action( 'admin_notices', array( $this, 'success_message' ) );
		}

		/**
		 * Function update_user_auth_table_headers : Updates the user report table columns
		 *
		 * @return void
		 */
		public static function update_user_auth_table_headers() {
			global $wpdb;

			$wpdb->query( "ALTER TABLE {$wpdb->prefix}user_report CHANGE `Ldap_status` `ldap_status` VARCHAR(250), CHANGE `Ldap_error` `ldap_error` VARCHAR(250)" ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange,  - Fetching data from a custom table.
		}

		/**
		 * Function get_faqs : returns an array of faqs.
		 *
		 * @return array
		 */
		public function get_faqs() {
			$faqs = array(
				'How to enable PHP LDAP extension? (Pre-requisite)' => '<ul>
						<li style="font-size: large; font-weight: bold">Step 1 </li>
						<li style="font-size: medium; font-weight: bold">Loaded configuration file : <?php echo esc_attr( php_ini_loaded_file() ); ?></li>
						<li style="list-style-type:square;margin-left:20px">Open php.ini file from above file path</li><br/>
						<li style="font-size: large; font-weight: bold">Step 2</li>
						<li style="font-weight: bold;color: #C31111">For Windows users using Apache Server</li>
						<li style="list-style-type:square;margin-left:20px">Search for <strong>"extension=php_ldap.dll"</strong> in php.ini file. Uncomment this line, if not present then add this line in the file and save the file.</li>
						<li style="font-weight: bold;color: #C31111">For Windows users using IIS server</li>
						<li style="list-style-type:square;margin-left:20px">Search for <strong>"ExtensionList"</strong> in the php.ini file. Uncomment the <strong>"extension=php_ldap.dll"</strong> line, if not present then add this line in the file and save the file.</li>
						<li style="font-weight: bold;color: #C31111">For Linux users</li>
							<ul style="list-style-type:square;margin-left: 20px">
								<li style="margin-top: 5px">Install php ldap extension (If not installed yet)
									<ul style="list-style-type:disc;margin-left: 15px;margin-top: 5px">
										<li>For Ubuntu/Debian, the installation command would be <strong>sudo apt-get -y install php-ldap</strong></li>
										<li>For RHEL based systems, the command would be <strong>yum install php-ldap</strong></li></ul></li>
								<li>Search for <strong>"extension=php_ldap.so"</strong> in php.ini file. Uncomment this line, if not present then add this line in the file and save the file.</li></ul><br/>
						<li style="margin-top: 5px;font-size: large; font-weight: bold">Step 3</li>
						<li style="list-style-type:square;margin-left:20px">Restart your server. After that refresh the "LDAP/AD" plugin configuration page.</li>
					</ul>',
				'What is an instance?'                    => '<ul>
						<li>A WordPress instance refers to a single installation of a WordPress site. It refers to each individual website where the plugin is active. In the case of a single site WordPress, each website will be counted as a single instance.</li>
						<li>For example, You have 3 sites hosted like one each for development, staging, and production. This will be counted as 3 instances.</li>
					</ul>',
				'What is a multisite network?'            => '<ul>
						<li>A multisite network means managing multiple sites within the same WordPress installation and has the same database.</li>
						<li>For example, You have 1 WordPress instance/site with 3 subsites in it then it will be counted as 1 instance with 3 subsites. You have 1 WordPress instance/site with 3 subsites and another WordPress instance/site with 2 subsites then it will be counted as 2 instances with 3 subsites.</li>
					</ul>',
				'How to setup/connect LDAP Server using LDAPS (LDAP over SSL)?' => '<ul>
						<li><a href="https://www.miniorange.com/guide-to-setup-ldaps-on-windows-server" rel="noopener" target="_blank">Click here</a> to go through the configuration steps to connect with LDAP server over LDAPS (LDAP over SSL:636).</li>
					</ul>',

				'Why is Contact LDAP Server not working?' => '<ol>
						<li>Check your LDAP Server URL to see if it is correct.<br>
						eg. ldap://myldapserver.domain:389 , ldap://x.x.x.x:389. When using SSL, the host may have to take the form ldaps://host:636.</li>
						<li>Your LDAP Server may be behind a firewall. Check if the firewall is open to allow requests from your WordPress installation.</li>
					</ol>',

				'I can connect to LDAP server through the command line (using ping/telnet) but get an error when I test connection from the plugin.' => '<ul>
						<li>This issue usually occurs for users whose WordPress is hosted on CentOS server. this error because SELinux Boolean httpd_can_network_connect is not set.<br></li>
						<li>Follow these steps to resolve the issue:</li>
						<li>1. Run command: setsebool -P httpd_can_network_connect on</li>
						<li>2. Restart apache server.</li>
						<li>3. Run command: getsebool -a | grep httpd and make sure that httpd_can_network_connect is on</li>
						<li>4. Try Ldap connect from the plugin again</li>
					</ul>',
				'What\'s the difference between a single site vs multisite network?' => '<ul>
						<li>A single site network only has one site, whereas a multisite network manages several sites all using the same WordPress installation and database.</li>
					</ul>',
				'Why is Test LDAP Configuration not working?' => '<ol>
						<li>Check if you have entered valid Service Account DN(distinguished Name) of the LDAP server. <br>e.g. cn=username,cn=group,dc=domain,dc=com<br>
						uid=username,ou=organisational unit,dc=domain,dc=com</li>
						<li>Check if you have entered correct Password for the Service Account.</li>
					</ol>',
				'Why is Test Authentication not working?' => '<ol>
						<li>The username/password combination you provided may be incorrect.</li>
						<li>You may have provided a <strong>Search Base(s)</strong> in which the user does not exist.</li>
					</ol>',
				'What are the LDAP Service Account Credentials?' => '<ol>
						<li>Service account is an non privileged user which is used to bind to the LDAP Server. It is the preferred method of binding to the LDAP Server if you have to perform search operations on the directory.</li>
						<li>The distinguished name(DN) of the service account object and the password are provided as credentials.</li>
					</ol>',
				'What is meant by Search Base in my LDAP environment?' => '<ol>
						<li>Search Base denotes the location in the directory where the search for a particular directory object begins.</li>
						<li>It is denoted as the distinguished name of the search base directory object. eg: CN=Users,DC=domain,DC=com.</li>
					</ol>',
				'What is meant by Search Filter in my LDAP environment?' => '<ol>
						<li>Search Filter is a basic LDAP Query for searching users based on mapping of username to a particular LDAP attribute.</li>
						<li>The following are some commonly used Search Filters. You will need to use a search filter which uses the attributes specific to your LDAP environment. Confirm from your LDAP administrator.</li>
						<ul>
							<table aria-hidden="true">
								<tr><td style="width:50%">common name</td><td>(&(|(objectClass=person)(objectClass=user)(objectClass=posixAccount))(<strong>cn</strong>=?))</td></tr>
								<tr><td>email</td><td>(&(|(objectClass=person)(objectClass=user)(objectClass=posixAccount))(<strong>mail</strong>=?))</td></tr>
								<tr><td>logon name</td><td>(&(|(objectClass=person)(objectClass=user)(objectClass=posixAccount))(<strong>sAMAccountName</strong>=?))<br/>(&(objectClass=person)(<strong>userPrincipalName</strong>=?))</td></tr>
								<tr><td>custom attribute where you store your WordPress usernames use</td> <td>(&(|(objectClass=person)(objectClass=user)(objectClass=posixAccount))(<strong>customAttribute</strong>=?))</td></tr>
								<tr><td>if you store WordPress usernames in multiple attributes(eg: some users login using email and others using their username)</td><td>(&(|(objectClass=person)(objectClass=user)(objectClass=posixAccount))(<strong>|</strong>(<strong>cn=?</strong>)(<strong>mail=?</strong>)))</td></tr>
							</table>
						</ul>
					</ol>',
				'How do users present in different Organizational Units (OUs) login into WordPress? <span class="mo_ldap_local_premium_faq">Premium</span>' => '<ol>
						<li>Support for multiple search bases is present in the <a href="https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=ldap%20intranet%20plugin&utm_medium=ldap%20free%20plugin&utm_campaign=ldap%20intranet%20free%20plugin" target="_blank" rel="noopener">Premium Version</a> of the plugin.</li>
					</ol>',
				'Some of my users login using their email and the rest using their usernames. How will both of them be able to login?<span class="mo_ldap_local_premium_faq">Premium</span>' => '<ul>
						<li>Support for multiple username attributes is present in the <a href="https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=ldap%20intranet%20plugin&utm_medium=ldap%20free%20plugin&utm_campaign=ldap%20intranet%20free%20plugin" target="_blank" rel="noopener">Premium version</a> of the plugin.</li>
					</ul>',
				'How Role Mapping works?<span class="mo_ldap_local_premium_faq">Premium</span>' => '<ul>
						<li>Support for Advanced Role Mapping is present in the <a href="https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=ldap%20intranet%20plugin&utm_medium=ldap%20free%20plugin&utm_campaign=ldap%20intranet%20free%20plugin" target="_blank" rel="noopener">Premium Version</a> of the plugin.</li>
					</ul>',
				'How Role Mapping works if user belongs to multiple groups?<span class="mo_ldap_local_premium_faq">Premium</span>' => '<ul>
						<li>Support for Advanced Role Mapping is present in the <a href="https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=ldap%20intranet%20plugin&utm_medium=ldap%20free%20plugin&utm_campaign=ldap%20intranet%20free%20plugin" target="_blank" rel="noopener">Premium version</a> of the plugin.</li>
					</ul>',
			);

			return $faqs;
		}

		/**
		 * Function mo_ldap_local_get_user_auth_logs : returns an array user authentication logs.
		 *
		 * @return array
		 */
		public function mo_ldap_local_get_user_auth_logs() {
			global $wpdb;

			$mo_ldap_local_user_auth_logs_cache = wp_cache_get( 'mo_ldap_local_user_auth_logs' );
			$table_name                         = $wpdb->prefix . 'user_report';
			$result                             = array();

			if ( $mo_ldap_local_user_auth_logs_cache ) {
				$result = $mo_ldap_local_user_auth_logs_cache;
			} elseif ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) ) === $table_name ) { //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, - Fetching data from a custom table.
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}user_report ORDER BY time DESC", 'ARRAY_A' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, - Fetching data from a custom table.
				wp_cache_set( 'mo_ldap_local_user_auth_logs', $result );
			}
			return $result;
		}
	}
}
