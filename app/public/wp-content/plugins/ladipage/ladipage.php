<?php
/*
Plugin Name: LadiPage - Landing Page Builder
Plugin URI: http://ladipage.com
Description: Connector to access content from LadiPage service. (LadiPage: Landing Page Platform for Advertiser)
Author: LadiPage
Author URI: http://ladipage.com
Version: 4.0
*/

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

if( ! class_exists( "PageTemplater" ) ){
	require plugin_dir_path( __FILE__ ) . 'add-template.php';
}

if ( isset( $_SERVER['HTTP_ORIGIN'] ) ) {
	header( "Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}" );
	header( 'Access-Control-Allow-Credentials: true' );
	header( 'Access-Control-Max-Age: 86400' ); // cache for 1 day
}

if ( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' ) {
	if ( isset( $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ) ) {
		header( "Access-Control-Allow-Methods: GET, POST, OPTIONS" );
	}
	if ( isset( $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ) ) {
		header( "Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}" );
	}
	exit( 0 );
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Send Data To LadiFlow from Contact Form 7
add_action( 'wpcf7_before_send_mail', 'wpcf7_handle_data');
function wpcf7_handle_data( $wpcf7 ) {
    $submission = WPCF7_Submission::get_instance();
	$data = $submission->get_posted_data();
	$data['source'] = 'WPFORM7';
	$data['channels'] = array('WPFORM7');
	if ( $wpcf7->id()) {
		$data['wpform7_id'] = $wpcf7->id();
		$data['form_id'] = $wpcf7->id();
	}
	
	if ($submission->get_meta('user_agent' ))
		$data['user_agent'] = $submission->get_meta('user_agent');
	
	if ($submission->get_meta('url'))
		$data['last_url'] = $submission->get_meta('url');
	
	if ($submission->get_meta('remote_ip'))
		$data['last_ip'] = $submission->get_meta('remote_ip');

		sendToLadiFlow($data);
}

// Send Data To LadiFlow from WPForms
add_action( 'wpforms_process_complete', 'wpforms_handle_data', 10, 4 );
function wpforms_handle_data( $fields, $entry, $form_data, $entry_id) {
	$data = [];
	foreach ($fields as $key => $value) {
		$_field = 'f' . $key;
		$data[$_field] = $value['value'];
	}
	
	$data['form_id'] = $form_data['id'];
	$data['last_ip'] = $_SERVER['REMOTE_ADDR'];
	$data['last_url'] = $_SERVER['HTTP_REFERER'];
	$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	$data['source'] = 'WPFORMS';
	$data['channels'] = array('WPFORMS');
	sendToLadiFlow($data);
}

function sendToLadiFlow($data) {
	$config = get_option( 'ladipage_config', '');
	$ladiflowHookConfig = get_option('ladiflow_hook_configs', array());
	if ($config['ladiflow_api_key'] && $config['ladiflow_webhook_active'] == 1) {
		if ($data['form_id'] && $ladiflowHookConfig[$data['form_id']] && $ladiflowHookConfig[$data['form_id']]['url']) {
			$url = $ladiflowHookConfig[$data['form_id']]['url'];
		} else {
			$url = @$config['ladiflow_api_url'] . '/customer/create-or-update';
		}
		curlPost($url, $data, $config['ladiflow_api_key']);
	}
}

function getRandomUserAgent()
{
    $userAgents = array(
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6)    Gecko/20070725 Firefox/2.0.0.6",
        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",
        "Opera/9.20 (Windows NT 6.0; U; en)",
        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en) Opera 8.50",
        "Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 5.1) Opera 7.02 [en]",
        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; fr; rv:1.7) Gecko/20040624 Firefox/0.9",
        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48"       
    );
    $random = rand(0,count($userAgents)-1);
    return $userAgents[$random];
}

function curlPost($url, $data, $apiKey = '4fb4d9fbd59070b3a5e5706bdc6e80c3cf8678c4083d6ae6') {
	$data_string = json_encode($data);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl,CURLOPT_USERAGENT,getRandomUserAgent());
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	    'Content-Type: application/json',
	    'Content-Length: ' . strlen($data_string),
		'Api-Key:' . $apiKey
	));

	$result = curl_exec($curl);
	curl_close($curl);
	return $result;
}


if ( ! class_exists( 'Ladipage' ) ) {

	class Ladipage {
		protected static $_instance = null;

		protected $_notices = array();

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public function __construct() {
			try {
				_mkdir();
			} catch (Exception $ex) {

			}
			add_action( 'init', array( $this, 'init_endpoint' ) );
			add_action( 'admin_init', array( $this, 'check_environment' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
			add_action( 'admin_menu', array( $this, 'add_ladipage_menu_item' ) );
			add_action( 'wp_ajax_ladipage_save_config', array( $this, 'save_config' ) );
			add_action( 'wp_ajax_ladipage_publish_lp', array( $this, 'publish_lp' ) );
			add_action( 'wp_ajax_ladiflow_save_hook', array( $this, 'ladiflow_save_hook' ) );

			register_activation_hook( __FILE__, array( $this, 'activation_process' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation_process' ) );
		}

		public function activation_process() {
		    $this->init_endpoint();
			flush_rewrite_rules();
		}

		public function deactivation_process() {
			flush_rewrite_rules();
		}

		/* add hook and do action */
		public function init_endpoint() {
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'sniff_requests' ) );
			add_rewrite_rule( '^ladipage/api', 'index.php?ladipage_api=1', 'top' );
		}

		public function add_query_vars( $vars ) {
			$vars[] = 'ladipage_api';

			return $vars;
		}

		public function sniff_requests() {
			global $wp, $wpdb;
			$query = $GLOBALS['wpdb'];
			$config = get_option( 'ladipage_config', '');
			$params      = filter_input_array( INPUT_POST );
			$isLadipage = isset( $wp->query_vars['ladipage_api'] ) ? $wp->query_vars['ladipage_api'] : null;
			// Params for products & orders
			$productID = isset( $_GET['product_id'] ) ? $_GET['product_id'] : null;
			$limit = isset( $_GET['limit'] ) ? $_GET['limit'] : 50;
			$paged = isset( $_GET['page'] ) ? $_GET['page'] : 0;
			$categoryIds = isset( $_GET['category_ids'] ) ? $_GET['category_ids'] : null;
			$variantID = isset( $_GET['variant_id'] ) ? $_GET['variant_id'] : null;
			$coupon = isset( $_GET['coupon'] ) ? $_GET['coupon'] : null;
			$actProduct = isset( $_GET['action'] ) ? $_GET['action'] : null;
			$qty = isset( $_GET['qty'] ) ? $_GET['qty'] : 1;
			$cartItemKey = isset( $_GET['cart_item_key'] ) ? $_GET['cart_item_key'] : null;
			$keyword = isset( $_GET['keyword'] ) ? $_GET['keyword'] : null;
			
			$ladiFlowApiKey = isset( $_GET['ladiflow_api_key'] ) ? $_GET['ladiflow_api_key'] : null;
			$appApiKey = isset( $_GET['app_api_key'] ) ? $_GET['app_api_key'] : null;
			$apiKey = isset( $_GET['api_key'] ) ? $_GET['api_key'] : null;
			$webhookURL = isset( $_GET['webhook_url'] ) ? $_GET['webhook_url'] : null;
			$webhookApiVersion = isset( $_GET['wh_api_version'] ) ? $_GET['wh_api_version'] : 2;
			$webhookApiUserId = isset( $_GET['wh_api_user_id'] ) ? $_GET['wh_api_user_id'] : 1;
			$isConnectedLadiFlow = (int) @$_GET['is_connected_ladiflow'];
			$isConnectedLadiShare = (int) @$_GET['is_connected_ladishare'];
			$events = isset( $_GET['events'] ) ? $_GET['events'] : 'order.created,order.updated,customer.created,customer.updated';
			$postType = isset( $_GET['post_type'] ) ? $_GET['post_type'] : null;
			$formID = isset( $_GET['form_id'] ) ? $_GET['form_id'] : null;
			$landingpageKey = isset( $_GET['ladipage_key'] ) ? $_GET['ladipage_key'] : null;
			$couponData        = isset( $params['coupon_data'] ) ? $params['coupon_data'] : null;

			$appName = '';
			if ($isConnectedLadiFlow) {
				$appName = 'LADIFLOW';
				$appApiKey = $ladiFlowApiKey;
			}
			if ($isConnectedLadiShare)	$appName = 'LADISHARE';
			
			if ($appApiKey && $actProduct === 'verify_webhook') {
				
				$args1 = array(
					 'role' => 'administrator',
					 'order' => 'ASC'
					);
				 $subscribers = get_users($args1);
				 foreach ($subscribers as $user) {
				 	$webhookApiUserId = $user->ID;
					 break;
				 }
				if ($appName)
					$this->updateWebhook($appName, $appApiKey, $apiKey, $config, $webhookURL, $events, $webhookApiVersion, $webhookApiUserId);
			}
			
			if ($appApiKey && $actProduct === 'delete_webhook') {
				$this->deleteWebhook($appName, $appApiKey, $apiKey, $config);
			}
			
			// Action for Intergrated products & orders.
			if ($actProduct !== null) {
				switch($actProduct) {
					case 'product_info': 
						if ( !is_null($productID)) {
							$this->getProduct($productID);
						}
						break;
					case 'cart_add': 
						if ( !is_null($productID)) {
							$this->addToCart($productID, $qty, $variantID);
							exit;
						}
						break;
					case 'cart_info': 
						$this->cartInfo();
						break;
					case 'cart_empty': 
						$this->cartEmpty();
						break;
					case 'apply_coupon': 
						$this->applyCoupon($coupon);
						break;
					case 'remove_coupon': 
						$this->removeCoupon($coupon);
						break;
					case 'cart_remove_item': 
						$this->cartRemoveItem($cartItemKey);
						break;
					case 'cart_update_item_qty': 
						$this->cartUpdateItemQty($cartItemKey, $qty);
						break;
					case 'list_category':
						$this->listCategory($keyword);
						break;
					case 'product_list':
						$this->searchProduct($keyword, $categoryIds, $paged, $limit);
						break;
					case 'order_create': 
						$this->createOrder();
						break;
					case 'list_forms': 
						$this->listForms($postType);
						break;
					case 'list_form_fields': 
						$this->listFormFields($formID, $postType);
						break;
					case 'publishldp': 
						$this->publish_lp($landingpageKey);
						break;
					default:
				}
			}
			
			if ( ! is_null( $isLadipage ) && $isLadipage === "1" ) {
				$api_key     = isset( $params['token'] ) ? sanitize_text_field($params['token']) : null;
				$action      = isset( $params['action'] ) ? sanitize_text_field($params['action']) : null;
				$url         = isset( $params['url'] ) ? sanitize_text_field($params['url']) : null;
				$title       = isset( $params['title'] ) ? sanitize_text_field($params['title']) : null;
				$html        = isset( $params['html'] ) ? $params['html'] : '';
				$type       = isset( $params['type'] ) ? sanitize_text_field($params['type']) : null;
				
				
				// Check key for action products & orders
				if (isset( $_GET['ldp_token'] )) {
					$api_key = @$_GET['ldp_token'];
				}
					
				if ( $api_key !== $config['api_key'] ) {
					wp_send_json( array(
						'code'    => 190
					) );
				}
				
				// Action for Intergrated products & orders.
				if ($actProduct !== null) {
					switch($actProduct) {
						case 'order_create': 
							$this->createOrder();
							break;
					}
				}
				
				// Action for publish Landing Page
				switch ( $action ) {
					case 'create':
						if ( $this->get_id_by_slug($url) ) {
							wp_send_json( array(
								'code'    => 205
							) );
						}
						kses_remove_filters();
						if ($type==null) {
							$post_type = 'page';
						} else {
							$post_type = $type;
						}

						$id = wp_insert_post(
							array(
								'post_title'=>$title, 
								'post_name'=>$url, 
								'post_type'=>$post_type, 
								'post_content'=> trim($html), 
								'post_status' => 'publish',
								'filter' => true ,
								'page_template'  => 'null-template.php'
							)
						);

						if ($id) {
							updateSource($url, $html);
							wp_send_json( array(
								'code'    => 200,
								'url'    => get_permalink($id)
							) );
						}else {
							wp_send_json( array(
								'code'    => 400,
								'message' => __( 'Create failed, please try again.' )
							) );
						}
						break;
					
					case 'update':
						if ( ! $this->get_id_by_slug($url) ) {
							wp_send_json( array(
								'code'    => 400,
								'message' => __( 'URL does not exist' )
							) );
						}else{
							$id = $this->get_id_by_slug($url);
							$post = array(
								'ID' => $id,
								'post_title'=>$title, 
					            'post_content' => trim($html), 
							);
					        kses_remove_filters();
					        if (wp_update_post( $post )) {
								updateSource($url, $html);
								wp_send_json( array(
									'code'    => 200,
									'url'    => get_permalink($id)
								) );
					        } else {
					        	wp_send_json( array(
									'code'    => 400,
									'message' => __( 'Update failed, please try again.' )
								) );
					        }
						}
						break;

					case 'delete':
						if ( ! $this->get_id_by_slug($url) ) {
							wp_send_json( array(
								'code'    => 400,
								'message' => __( 'URL does not exist' )
							) );
						}else{
							$id = $this->get_id_by_slug($url);
							$result = wp_delete_post($id);
							try {
								stopLandingPage($url);
							} catch (Exception $ex) {
								wp_send_json( array(
									'code'    => 400,
									'message' => __( 'Delete failed, please try again.' )
								) );
							}

					        if($result){
					        	wp_send_json( array(
									'code'    => 200
								) );
					        }else{
					        	wp_send_json( array(
									'code'    => 400,
									'message' => __( 'Delete failed, please try again.' )
								) );
					        }
						}
						break;
					case 'checkurl':
						if ( ! $this->get_id_by_slug($url) ) {
							wp_send_json( array(
								'code'    => 206
							) );
						}else{
							wp_send_json( array(
								'code'    => 205
							) );
						}
						break;
					case 'checktoken':
						if ( $api_key === $config['api_key'] ) {
							wp_send_json( array(
								'code'    => 191
							) );
						}
						break;
					case 'update_coupon': 
						$this->updateCoupon($couponData);
						break;	
					default:
						wp_send_json( array(
							'code'    => 400,
							'message' => __( 'LadiPage action is not set or incorrect.' )
						) );
						break;
				}
			}
		}
		
		function listFormFields($formID, $postType) {
			if ($postType === 'wpcf7_contact_form') {
				$form = WPCF7_ContactForm::get_instance( $formID );
				$fields = $form->scan_form_tags();
				$data = array();
				for ($i = 0; $i < count($fields); $i++) {
					if ($fields[$i]->basetype != 'submit')
						$data[$i] = array(
							'id' => $i + 1,
							'name' => $fields[$i]->name,
							'label' => $fields[$i]->label,
							'type' => $fields[$i]->basetype
						);
				}
				wp_send_json(
					array(
						'code'    => 200,
						'data' => $data
					)
				);
			}

			if ($postType === 'wpforms') {
				$form         = wpforms()->form->get( $formID );
				$data = [];
				if ($form) {
					$postContent = @json_decode($form->post_content);
					if ($postContent->fields) {
						$fields = (array) $postContent->fields;
						
						if (count($fields) > 0) {
							$i = 0;
							foreach ($fields as $field) {
								$data[$i] = array(
									'id' => $i + 1,
									'name' => $field->id,
									'label' => $field->label,
									'type' => $field->type
								);
								$i++;
							}
						}
					}
				}
				
				wp_send_json(
					array(
						'code'    => 200,
						'data' => $data
					)
				);
			}

			wp_send_json(
				array(
					'code'    => 200,
					'data' => []
				)
			);
		}

		function listForms($postType) {
			if (!$postType) {
				wp_send_json(
					array(
						'code'    => 200,
						'data' => []
					)
				);
				exit;
			}

			$args['post_type'] = $postType;
			$forms = get_posts( $args );
			$_forms = [];
			$i = 0;
			foreach ($forms as $form) {
				$_formItem = array(
					'id' => $form->ID,
					'name' => $form->post_title
				);
				$_forms[$i] = $_formItem;
				$i++;
			}
			wp_send_json(
				array(
					'code'    => 200,
					'data' => array(
						'forms' => $_forms,
						'post_type' => $postType
					)
				)
			);
			exit;
		}

		function createWebhook($name, $topic, $secret, $deliveryURL, $status, $apiVersion = 2, $apiUserId = 1)
		{
			$webhook = new WC_Webhook();
			$webhook->set_name( $name );
			$webhook->set_user_id($apiUserId);
			$webhook->set_topic( $topic ); // Event used to trigger a webhook.
			$webhook->set_secret( $secret ); // Secret to validate webhook when received.
			$webhook->set_delivery_url( $deliveryURL ); // URL where webhook should be sent.
			$webhook->set_status( $status ); // Webhook status.
			$webhook->set_api_version($apiVersion);
			$save = $webhook->save();
			return $save;
		}
		
		public function deleteWebhook($appName, $appApiKey = null, $apiKey = null, $config) {
			try {	
				if (!$appApiKey || !$apiKey) {
					wp_send_json(
							array(
								'code'    => 500,
								'data' => null,
								'message' => 'Request invalid!'
							)
						);
				}
						
				$apiURL = '';
				if ($appName === 'LADIFLOW') $apiURL = $config['ladiflow_api_url'];
				if ($appName === 'LADISHARE') $apiURL = $config['ladishare_api_url'];

				if ( $apiKey === $config['api_key'] ) {
					if ($appName === 'LADIFLOW') {
						$config['ladiflow_webhook_active'] = 0;
					}

					if ($appName === 'LADISHARE') {
						$config['ladishare_webhook_active'] = 0;
					}

					update_option( 'ladipage_config', $config );
					
					if ( class_exists( 'WooCommerce' ) ) {
						global $wpdb;
    					$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wc_webhooks" );
						foreach($results as $result) {
							if (strpos($result->delivery_url, $apiURL) !== false) {
								$wh = new WC_Webhook();
								$wh->set_id($result->webhook_id);
								$wh->delete();
							}
						}
					}
					wp_send_json(
						array(
							'code'    => 200,
							'data' => '',
							'message' => 'Delete webhook Success'
						)
					);
				} else {
					wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => 'Can not verify Api Key.'
					)
				);
				}
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}

		public function updateWebhook($appName, $appApiKey, $apiKey = null, $config = [], $webhookURL, $events = '') {
			try {	
				global $wpdb;
				if (!$apiKey || !$webhookURL) {
					wp_send_json(
							array(
								'code'    => 500,
								'data' => null,
								'message' => 'Request invalid!'
							)
						);
				}

				if ($appName == 'LADISHARE') {
					$config['ladishare_api_url'] = $webhookURL;
					$config['ladishare_webhook_active'] = 1;
				}

				if ($appName == 'LADIFLOW') {
					$config['ladiflow_api_url'] = $webhookURL;
					$config['ladiflow_webhook_active'] = 1;
				}
						
				if ( $apiKey === $config['api_key'] ) {
					update_option( 'ladipage_config', $config );
					if ( class_exists( 'WooCommerce' ) ) {
						$arrayEvent = [];
						if ($events)
							$arrayEvent = @explode(',', $events);
						
    					$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wc_webhooks" );
						$currentTopic = [];
						foreach ($results as $result) {
							if (strpos($result->delivery_url, $webhookURL) !== false) {
								$currentTopic[] = $result->topic;
							}
						}
						
						$_events = [];
						if (count($results) == 0) {
							$_events = $arrayEvent;
						} else {
							for ($i = 0; $i < count($arrayEvent); $i++) {
								if (!in_array($arrayEvent[$i], $currentTopic)) {
									$_events[] = $arrayEvent[$i];
								}
							}
						}
			
						if (count($_events) > 0) {
							$arrayWebhookID = array();
							for ($i = 0; $i < count($_events); $i++) {
								$name = '';
								if ($_events[$i] === 'order.created')
									$name = $appName . " Create Order";

								if ($_events[$i] === 'order.updated')
									$name = $appName . " Update Order";

								$_webhookURL = $webhookURL . '/webhook/wordpress';
								$webhookID = $this->createWebhook($name, $_events[$i], $appApiKey, $_webhookURL, 'active');
								if ($webhookID) {
									$arrayWebhookID[] = $webhookID;
								}
							}
							if ($appName === 'LADISHARE') {
								$config['ladishare_webhook_ids'] = @json_decode($arrayWebhookID);
								$config['ladishare_webhook_active'] = 1;
							}

							if ($appName === 'LADIFLOW') {
								$config['ladiflow_webhook_ids'] = @json_decode($arrayWebhookID);
								$config['ladiflow_webhook_active'] = 1;
							}

							update_option( 'ladipage_config', $config );
						}
					}
					wp_send_json(
						array(
							'code'    => 200,
							'data' => 'Webhook',
							'message' => 'Success'
						)
					);
				} else {
					wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => 'Can not verify Api Key.'
					)
				);
				}
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}

		public function listCategory() {
			try {					
				$orderby = 'name';
				$order = 'asc';
				$hide_empty = false ;
				$args = array(
					'orderby'    => $orderby,
					'order'      => $order,
					'hide_empty' => $hide_empty,
				);

				$arrayCate = [];
				$categories = get_terms( 'product_cat', $args );
				$i = 0;
				foreach ($categories as $category) {
					$arrayCate[$i]['id'] = $category->term_id;
					$arrayCate[$i]['name'] = $category->name;
					$i++;
				}					
				wp_send_json(
					array(
						'code'    => 200,
						'data' => $arrayCate,
						'message' => 'Success'
					)
				);
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}
		
		public function searchProduct($keyword = '', $categoryIds = null, $page = 0, $limit = 50) {
			try {
				
				$args = array(
					'posts_per_page' => $limit,
					'paged' => $page,
					'post_type' => array('product'),
					'stock_status' => array('instock', 'outofstock')
				);
				
				if ($categoryIds) {
					$categoryIds = explode('|', $categoryIds);
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'product_cat',
							'terms' => $categoryIds,
							'operator' => 'IN'
						)
					);
				}
				
				if ($keyword != '') {
					$args['s'] = $keyword;	
				}	
				
				
				$query = new WP_Query( $args );
				$count = $query->found_posts;
				$productData = array();
				$postParentID = array();
				$weightUnit = get_option('woocommerce_weight_unit');
				if ( $query->have_posts() ) {
            	$products = $query->posts;
					foreach ($products as $product) {
						setup_postdata( $product );
						$productAttribute = get_post_meta( $product->ID, '_product_attributes' );
						$_attrData = array();
						$i = 0;
						$product2 = new WC_product($product->ID);
						$attachment_ids = $product2->get_gallery_image_ids();
						$productGallery = [];
						$m = 0;
						foreach( $attachment_ids as $attachment_id ) {
							$m++;
							$productGallery[] = [
								'src' => wp_get_attachment_url( $attachment_id ),
								'position'=> $m,
								'product_id' => $product->ID
							];
						}
						
						$productAttribute = (array) @$productAttribute[0];
							
						foreach ($productAttribute as $key => $attr) {
							
							if ($attr['is_taxonomy'] == 1) {	
								$label = wc_attribute_label($attr['name']);
								$_itemAttr = array(
									'product_id' => $productID,
									'product_option_id' => $i,
									'name' => $label,
									'type' => 1,
									'position' => $attr['position'],
									'values' => array()
								);
								
							$optionValues = [];
								$_attributeArray = woocommerce_get_product_terms($productID, $attr['name'], 'all');
								for ($k = 0; $k < count($_attributeArray); $k++) {
									$optionValues[$k] = ['name' => @trim($_attributeArray[$k]->slug), 'label' => @trim($_attributeArray[$k]->name)];
								}
								$_itemAttr['values'] = $optionValues;
							} else {
								$_itemAttr = array(
									'product_id' => $productID,
									'product_option_id' => $i,
									'name' => $attr['name'],
									'type' => 1,
									'position' => $attr['position'],
									'values' => explode(' | ', $attr['value'])
								);
								$optionValues = @explode(' | ', $attr['value']);
								if (count($optionValues) > 0) {
									for ($k = 0; $k < count($optionValues); $k++) {
										$optionValues[$k] = ['name' => @trim($optionValues[$k]),  'label' => @trim($optionValues[$k])];
									}	
									$_itemAttr['values'] = $optionValues;
								}
								
							}
							$_attrData[$i] = $_itemAttr;
							$i++;
						}
						
						$price = get_post_meta( $product->ID, '_price', true );
						$productPrice = get_post_meta( $product->ID, '_sale_price', true );
						if (!$productPrice)
							$productPrice = $price;
						$comparePrice = get_post_meta( $product->ID, '_regular_price', true );

						$sku = get_post_meta( $product->ID, '_sku', true );
						$image = get_the_post_thumbnail_url($product->ID);
						$imageObj = array();
						if ($image) {
							$imageObj = array(
								'src' => $image,
								'position'=> 0,
								'product_id' => $product->ID
							);
						}
						$stockProduct = (int) get_post_meta( $product->ID, '_stock', true );
						$_weight = (float) get_post_meta( $product->ID, '_weight', true );
						
						$_product = array(
							'_id' => $product->ID,
							'product_id' => $product->ID,
							'name' => $product->post_title,
							'description' => $product->post_content,
							'short_description' => $product->post_excerpt,
							'unit' => null,
							'price' => (float) $price,
							'sale_price' => (float) $productPrice,
							'price_compare' => (float) $comparePrice,
							'compare_price' => (float) $comparePrice,
							'options' => $_attrData,
							'images' => $productGallery,
							'image' => $imageObj,
							'tags' => array(),
							'variants' => array(
								
							),
							'relates' => array(),
							'cross_sells' => array(),
							'upsells' => array(),
							'post_parent' => $product->post_parent
						);
						$parentManageStock = $product2->get_manage_stock() == true ? 1: 0;
						$productVariants = $this->getVariants($product->ID, $product->post_title, $parentManageStock);
						$_product['variants'] = $productVariants;
						
						$productData[] = $_product;
					}
				}
				wp_reset_postdata();
				
				for ($i = 0; $i < count($productData); $i++) {
					if (count ($productData[$i]['variants']) == 0) {
						$productData[$i]['variants'][] = array(
									'_id' => $productData[$i]['product_id'],
									'product_id' => $productData[$i]['product_id'],
									'product_variant_id' => $productData[$i]['product_id'],
									'store_id'=> null,
									'product_name' => $productData[$i]['name'],
									'description' => $productData[$i]['description'],
									'short_description' => $productData[$i]['excerpt'],
									'price' => (float) @$productData[$i]['price'],
									'sale_price' => (float) @$productData[$i]['sale_price'],
									'price_compare' => (float) @$productData[$i]['compare_price'],
									'compare_price' => (float) @$productData[$i]['compare_price'],
									'cost_per_item' => 0,
									'sku' => @$productData[$i]['sku'],
									'quantity' => (int) @$productData[$i]['stock_quantity'],
									'inventory_checked' => @$productData[$i]['manage_stock'] === true ? 1 : 0,
									'weight'=> @$productData[$i]['weight'],
									'weight_unit' => @$productData[$i]['weight_unit'],
									'height'=> null,
									'bar_code'=> null,
									'src' => null,
									'option_ids' => '',
									'text_quantity' => '',
									'title' => $productData[$i]['name'],
									'post_parent' => @$productData[$i]['post_parent'],
									'option1' => null,
									'option2' => null,
									'option3' => null,
									'created_at' => @$productData[$i]['date_created'],
									'updated_at' => @$productData[$i]['date_modified']
								);
					}
				}
				
				// Search Product
				wp_send_json( array(
					'code'    => 200,
					'data' => array('products' => $productData, 'total_record' => $count)
				) );
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}
		
		public function getVariants($postParentID, $productName = '', $parentManageStock = 0) {
			$argsVariant = array(
					'posts_per_page' => 500,
					'post_type' => array('product_variation'),
					'post_parent__in' => [$postParentID]
				);
				$productData = [];
				$weightUnit = get_option('woocommerce_weight_unit');
				$queryVariant = new WP_Query( $argsVariant );
				if ( $queryVariant->have_posts() ) {
            	$productVariants = $queryVariant->posts;
					foreach ($productVariants as $variant) {
						$variation = new WC_Product_Variation( $variant->ID );

						$image = wp_get_attachment_url( $variation->get_image_id() );
						$imageObj = array();
						if ($image) {
							$imageObj = array(
								'src' => $image,
								'position'=> 0,
								'product_id' => $variant->ID
							);
						}
						$variantAttributes = $variation->get_attributes();
						$arrayVariantOption = [];
						foreach ($variantAttributes as $key => $value) {
							$arrayVariantOption[] = $value;
						}
						$option1 = @$arrayVariantOption[0];
						$option2 = @$arrayVariantOption[1];
						$option3 = @$arrayVariantOption[2];
						
						$variantSalePrice = @get_post_meta( $variant->ID, '_sale_price', true ) 
							? get_post_meta( $variant->ID, '_sale_price', true ) : get_post_meta( $variant->post_parent, '_sale_price', true );
						$variantPrice = (float )@get_post_meta( $variant->ID, '_price', true ) 
							? get_post_meta( $variant->ID, '_price', true ) : get_post_meta( $variant->post_parent, '_price', true );
						if (!$variantSalePrice)
								$variantSalePrice = $variantPrice;

						$variantComparePrice = (float) @get_post_meta( $variant->ID, '_regular_price', true ) 
							? get_post_meta( $variant->ID, '_regular_price', true ) : get_post_meta( $variant->post_parent, '_regular_price', true );
						$variantSKU = get_post_meta( $variant->ID, '_sku', true );
						$stockVariant = (int) get_post_meta( $variant->ID, '_stock', true );
						$_weight = (float) get_post_meta( $variant->ID, '_weight', true );
						
						$variantTitle = '';
						if ($option1)
							$variantTitle .= $option1;
						if ($option2)
							$variantTitle .= ', ' . $option2;
						if ($option3)
							$variantTitle .= ', ' . $option3;
						$_variant = array(
							'_id' => $variant->ID,
							'product_id' => $variant->post_parent,
							'product_variant_id' => $variant->ID,
							'store_id'=> null,
							'product_name' => $productName,
							'description' => $variant->post_content,
							'short_description' => $variant->post_excerpt,
							'price' => (float) $variantPrice,
							'sale_price' => (float) $variantSalePrice,
							'price_compare' => (float) $variantComparePrice,
							'compare_price' => (float) $variantComparePrice,
							'cost_per_item' => 0,
							'sku' => $variantSKU,
							'quantity' => $stockVariant,
							'inventory_checked' => $variation->get_manage_stock() === true ? 1 : $parentManageStock,
							'weight'=> $_weight,
							'weight_unit' => $weightUnit,
							'height'=> null,
							'bar_code'=> null,
							'src' => null,
							'image' => $imageObj,
							'option_ids' => '',
							'text_quantity' => '',
							'title' => $variantTitle,
							'post_parent' => $variant->post_parent,
							'option1' => $option1,
							'option2' => $option2,
							'option3' => $option3,
							'created_at' => $variant->post_date,
							'updated_at' => $variant->post_modified
						);
						if ($_variant['price'] > 0 && $_variant['price_compare'] > 0)
							$productData[] = $_variant;
					}
			}
			return $productData;
		}
		
		public function getProduct($productID = null) {
			try {	
				global $_product;
				if ( class_exists( 'WooCommerce' ) ) {
					$_product = wc_get_product( $productID );
					$productData = $_product->get_data();
					$productData['product_id'] = (int) $productID;
					$productData['_id'] = (int) $productID;
					$productData['price'] = (float) $_product->get_price();
					$productData['compare_price'] = (float) $_product->get_regular_price();
					$productData['price_compare'] = $productData['compare_price'];
					$productData['sale_price'] = (float) $_product->get_sale_price();
					$productName = $_product->get_name();
					$parentManageStock = $_product->get_manage_stock() == true ? 1: 0;
					$productAttribute = get_post_meta( $productID, '_product_attributes' );					
						$_attrData = array();
						$i = 0;
						
						$productAttribute = (array) @$productAttribute[0];
						foreach ($productAttribute as $key => $attr) {
							if ($attr['is_taxonomy'] == 1) {	
								$taxonomy = $attr['name'];
								$label = wc_attribute_label($taxonomy);
								$_itemAttr = array(
									'product_id' => $productID,
									'product_option_id' => $i,
									'name' => $label,
									'type' => 1,
									'position' => $attr['position'],
									'values' => array()
								);
								
								$optionValues = [];
								$_attributeArray = woocommerce_get_product_terms($productID, $attr['name'], 'all');
								for ($k = 0; $k < count($_attributeArray); $k++) {
									$optionValues[$k] = ['name' => @trim($_attributeArray[$k]->slug), 'label' => @trim($_attributeArray[$k]->name)];
								}
								$_itemAttr['values'] = $optionValues;
							} else {
								$_itemAttr = array(
									'product_id' => $productID,
									'product_option_id' => $i,
									'name' => $attr['name'],
									'type' => 1,
									'position' => $attr['position'],
									'values' => explode(' | ', $attr['value'])
								);
								$optionValues = @explode(' | ', $attr['value']);
								if (count($optionValues) > 0) {
									for ($k = 0; $k < count($optionValues); $k++) {
										$optionValues[$k] = ['name' => @trim($optionValues[$k]), 'label' => @trim($optionValues[$k])];
									}	
									$_itemAttr['values'] = $optionValues;
								}
								
							}	
							$_attrData[$i] = $_itemAttr;								
							$i++;
						}
					$productData['options'] = $_attrData;
					if ($productData['parent_id'] == 0) {
						$variants = $this->getVariants($productID, $productName, $parentManageStock);
						if (count($variants) == 0) {
							$productData['variants'][] = array(
									'_id' => $productData['id'],
									'product_id' => $productData['id'],
									'product_variant_id' => $productData['id'],
									'store_id'=> null,
									'product_name' => @$productData['name'],
									'description' => @$productData['description'],
									'short_description' => @$productData['short_description'],
									'price' => (float) $productData['price'],
									'sale_price' => (float) @$productData['sale_price'],
									'price_compare' => (float) @$productData['price_compare'],
									'compare_price' => (float) @$productData['compare_price'],
									'cost_per_item' => 0,
									'sku' => $productData['sku'],
									'quantity' => (int) $productData['stock_quantity'],
									'inventory_checked' => $productData['manage_stock'] === true ? 1 : 0,
									'weight'=> $productData['weight'],
									'weight_unit' => @$productData['weight_unit'],
									'height'=> null,
									'bar_code'=> null,
									'src' => null,
									'option_ids' => '',
									'text_quantity' => '',
									'title' => $productData['name'],
									'post_parent' => $productData['parent_id'],
									'option1' => null,
									'option2' => null,
									'option3' => null,
									'created_at' => $productData['date_created'],
									'updated_at' => $productData['date_modified']
								);
						} else {
							$productData['variants'] = $variants;
						}
					}
					
					$image = wp_get_attachment_url( $productData['image_id'] );
					$imageObj = array();
					if ($image) {
						$imageObj = array(
							'src' => $image,
							'position'=> 0,
							'product_id' => $productID
						);
					}
					
					
					$productData['image'] = $imageObj;
					$productGallery = [];
					for ($i = 0; $i < count($productData['gallery_image_ids']); $i++) {
						$productGallery[] = [
								'src' => wp_get_attachment_url( $productData['gallery_image_ids'][$i] ),
								'position'=> $i,
								'product_id' => $productID
							];
					}
					$productData['images'] = $productGallery;
					
					if ($productData) {
						wp_send_json(
							array(
								'code'    => 200,
								'data' => array('product' => $productData),
								'message' => __( 'Success' )
							)
						);
					} else {
						wp_send_json(
							array(
								'code'    => 404,
								'data' => null,
								'message' => __( 'Can not find this product.' )
							)
						);
					}
				}
				wp_send_json(
					array(
						'code'    => 400,
						'data' => null,
						'message' => __( 'Bad request!' )
					)
				);
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}
		
		public function applyCoupon($coupon) {
			try {		
        		if ( WC()->cart->has_discount( $coupon ) ) {
					wp_send_json(
						array(
							'code'    => 500,
							'data' => null,
							'message' => 'Coupon had existed!'
						)
					);
				}
				$result = WC()->cart->apply_coupon( $coupon );
        		if ($result) {
					$money = strip_tags(WC()->cart->get_discount_total());
					wp_send_json(
						array(
							'code'    => 200,
							'data' => (float) $money,
							'message' => 'Coupon applied success!'
						)
					);
				}
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => 'Can not apply this Coupon Code!'
					)
				);
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}	
		
		public function removeCoupon($coupon) {
			try {
				if (WC()->cart->remove_coupon( $coupon )) {
					wp_send_json(
						array(
							'code'    => 200,
							'data' => 0,
							'message' => 'Coupon removed success!'
						)
					);
				}
			wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => 'Can not remove this Coupon Code!'
					)
				);
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}

		public function updateCoupon($_couponData) {
			try {
				$couponData = json_decode($_couponData);
				 if (!$couponData || !$couponData->code) {
					wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => 'Request invalid!'
					)
				); 
				 }
				$coupon = new WC_Coupon();
				$cp = wc_get_coupon_id_by_code($couponData->code);
				if ($cp) {
					$coupon = new WC_Coupon($couponData->code);
				} else {
					$coupon->set_code($couponData->code);
				}
				$coupon->set_discount_type($couponData->discount_type);
				$coupon->set_amount($couponData->discount);
				if ($couponData->end_date)
					$coupon->set_date_expires($couponData->end_date);
				$coupon->save();

				wp_send_json(
					array(
						'code'    => 200,
						'data' => null,
						'message' => 'OK'
					)
				);
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}
			
		public function cartInfo() {
			try {
				$cart = WC()->cart->get_cart();
				$cartData = [];
				foreach($cart as $item => $values) {	
					$cartItem = $values['data'];					
					$values['image'] = wp_get_attachment_url( $cartItem->get_image_id());
					$values['product_name'] = $cartItem->get_name();
					$values['sale_price'] = (float) $cartItem->get_sale_price();
					$values['regular_price'] = (float) $cartItem->get_regular_price();
					$values['price'] = (float) $cartItem->get_price();
					$cartData[] = $values;
				} 
				
				wp_send_json(
					array(
						'code'    => 200,
						'data' => $cartData,
						'message' => 'Success'
					)
				);
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}
		
		public function cartEmpty() {
			try {
				$cart = WC()->cart->empty_cart();
				wp_send_json(
					array(
						'code'    => 200,
						'data' => null,
						'message' => 'Success'
					)
				);
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}
		
		public function cartUpdateItemQty($key, $qty) {
			try {
				$qty = (int) $qty;
				$result = WC()->cart->set_quantity($key, $qty);
				if ($result) {
					$cart = WC()->cart->get_cart();
					$cartData = [];
					foreach($cart as $item => $values) {	
						$cartItem = $values['data'];					
						$values['image'] = wp_get_attachment_url( $cartItem->get_image_id());
						$values['product_name'] = $cartItem->get_name();
						$values['sale_price'] = (float) $cartItem->get_sale_price();
						$values['regular_price'] = (float) $cartItem->get_regular_price();
						$values['price'] = (float) $cartItem->get_price();
						$cartData[] = $values;
					}										 				
					wp_send_json(
						array(
							'code'    => 200,
							'data' => $cartData,
							'message' => 'Success'
						)
					);
				}
				wp_send_json(
						array(
							'code'    => 500,
							'data' => null,
							'message' => 'Error'
						)
					);
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}
		
		public function cartRemoveItem($key) {
			try {
				if (WC()->cart->remove_cart_item($key)) {
					$cartData = [];
					$cart = WC()->cart->get_cart();
					foreach($cart as $item => $values) {	
						$cartItem = $values['data'];					
						$values['image'] = wp_get_attachment_url( $cartItem->get_image_id());
						$values['product_name'] = $cartItem->get_name();
						$values['sale_price'] = (float) $cartItem->get_sale_price();
						$values['regular_price'] = (float) $cartItem->get_regular_price();
						$values['price'] = (float) $cartItem->get_price();
						$cartData[] = $values;
					}
					wp_send_json(
						array(
							'code'    => 200,
							'data' => $cartData,
							'message' => 'Success'
						)
					);
				}
				wp_send_json(
						array(
							'code'    => 500,
							'data' => null,
							'message' => 'Error'
						)
					);
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}
		
		public function addToCart($productID = 0, $qty, $variantID = 0, $arrOptions = []) {
			try {
				if ($productID === $variantID)
					$variantID = 0;
					
				$qty = (int) $qty;
				$variantAttributes = [];
				if ($variantID && $variantID !== $productID) {
					$variation = new WC_Product_Variation($variantID);
					$variantAttributes = $variation->get_attributes();
				}
				
				$result = WC()->cart->add_to_cart( $productID, $qty, $variantID, $variantAttributes);
				if ($result) {
					$cart = WC()->cart->get_cart();
					$cartData = [];
					foreach($cart as $item => $values) {	
						$cartItem = $values['data'];					
						$values['image'] = wp_get_attachment_url( $cartItem->get_image_id());
						$values['product_name'] = $cartItem->get_name();
						$values['sale_price'] = (float) $cartItem->get_sale_price();
						$values['regular_price'] = (float) $cartItem->get_regular_price();
						$values['price'] = (float) $cartItem->get_price();
						$cartData[] = $values;
					} 
					wp_send_json(
						array(
							'code'    => 200,
							'data' => $cartData,
							'message' => 'Success'
						)
					);
				}
			
			wp_send_json(
						array(
							'code'    => 500,
							'data' => null,
							'message' => 'Can not add item to cart.'
						)
					);
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}
		
		public function createOrder($data = null) {
			try {
					global $woocommerce;
					$data = [];
					if (isset($_POST['form_data'])) {
						$data = @$_POST['form_data'];
						$data = str_replace ('\"','"', $data);						
						$data = json_decode($data, true);
					}
				
					if (count($data) == 0) {
						wp_send_json(
							array(
								'code'    => 500,
								'data' => null,
								'message' => 'Data Empty'
							)
						);
					}
				
				$order = wc_create_order();
				for ($i = 0; $i < count($data['line_items']); $i++) {
					$pID = $data['line_items'][$i]['product_id'];
					if (isset($data['line_items'][$i]['variation_id']) && $data['line_items'][$i]['variation_id'] > 0) {
						$pID = $data['line_items'][$i]['variation_id'];
					}
					
					$__product = wc_get_product($pID); 
					$order->add_product( $__product, $data['line_items'][$i]['quantity']);
				}
				
				$order->add_order_note(@$data['message']);
				// Set addresses
				$order->set_address( $data['billing'], 'billing' );
				$order->set_address( $data['shipping'], 'shipping' );
				if (isset($data['coupon']) && $data['coupon']) {
					try {
						$order->apply_coupon($data['coupon']);
					} catch (Exception $ex) {
						
					}
				}

				// Set payment gateway
				$payment_gateways = WC()->payment_gateways->payment_gateways();
				$order->set_payment_method( $payment_gateways['bacs'] );

				// Calculate totals
				$order->calculate_totals();
				$order->update_status( 'Completed', 'Order Created From LadiPage - ', TRUE);
				if ($order) {
					wp_send_json(
						array(
							'code'    => 200,
							'data' => $order,
							'message' => 'SUCCESS'
						)
					);
				}
				wp_send_json(
						array(
							'code'    => 500,
							'data' => null,
							'message' => 'ERROR'
						)
					);
			} catch (Exception $ex) {
				wp_send_json(
					array(
						'code'    => 500,
						'data' => null,
						'message' => $ex->getMessage()
					)
				);
			}
		}
		
		protected function get_id_by_slug($page_slug) {
		    $page = get_page_by_path($page_slug,'OBJECT', ['post','page','product','property']);
		    if ($page) {
		        return $page->ID;
		    } else {
		        return null;
		    }
		} 

		public function get_option( $id, $default = '' ) {
			$options = get_option( 'ladipage_config', array() );
			if ( isset( $options[ $id ] ) && $options[ $id ] != '' ) {
				return $options[ $id ];
			} else {
				return $default;
			}
		}

		/* Add menu and option */
		public function check_environment() {
			if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
				if ( ! function_exists( 'curl_init' ) ) {
					$this->add_admin_notice( 'curl_not_exist', 'error', __( 'LadiPage requires cURL to be installed.', 'ladipage' ) );
				}
			}
		}

		public function add_admin_notice( $slug, $class, $message ) {
			$this->_notices[ $slug ] = array(
				'class'   => $class,
				'message' => $message,
			);
		}

		public function admin_notices() {
			foreach ( $this->_notices as $notice_key => $notice ) {
				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
				echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) );
				echo '</p></div>';
			}
		}

		public function add_ladipage_menu_item() {
			add_menu_page( __( "LadiApp" ), __( "LadiApp" ), "manage_options", "ladipage-config", array(
				$this,
				'ladipage_settings_page'
			), null, 30 );
		}

		public function ladipage_settings_page() {
			if ( ! empty( $this->_notices ) ) {
				?>
                <div>Please install cURL to use LadiApp plugin</div>
				<?php
			} else {
				?>
				<?php
					$tabs = array( 'general' => 'General', 'ladipage' => 'LadiPage', 'ladiflow' => 'LadiFlow' );
				?>
				<div class="wrap">
                    <h2 class="title">LadiApp</h2><br>
					<p>An All-in-One application for businesses</p>
					<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
					<?php
						$_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
						foreach( $tabs as $tab => $name ){
							$class = ( $tab == $_tab ) ? ' nav-tab-active' : "";
							echo "<a class='nav-tab $class' href='?page=ladipage-config&tab=$tab'>$name</a>";
						}
					?>
					</nav>
                    <div>
						<?php if ($_tab === 'general') :?>
							<form id="ladipage_config" class="ladiui-panel">
							<h3><strong>Config API Key</strong></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="api_key">API KEY</label>
                                </th>
                                <td>
                                	<?php
                                		$config = get_option( 'ladipage_config', array());
                                		
                            		    if(!isset($config['api_key']) || trim($config['api_key']) == ''){
                            		    	$config['api_key'] = $this->generateRandomString(32);
                            		    	update_option( 'ladipage_config', $config );
                            		    }

                                	?>
                                    <input onClick="this.select();" readonly="readonly" name="api_key" id="api_key" type="text" class="regular-text ladiui input"
                                           value="<?php echo $this->get_option( 'api_key', '' ); ?>">
                                           <button type="button" id="ladipage_new_api" class="ladiui button primary">NEW API KEY</button>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
									<label for="webiste_ladipage">Website URL</label>
                                </th>
                                <td>
                                    <input onClick="this.select();" readonly="readonly" name="webiste_ladipage" id="webiste_ladipage" type="text" class="regular-text ladiui input" value="<?php echo get_home_url(); ?>">
                                </td>
                            </tr>
                        </table>

                        <div class="submit">
                            <button class="button button-primary ladiui button primary" id="ladipage_save_option" type="button">Save Changes
                            </button>
                        </div>
                    </form>
						<?php endif;?>
					<?php if ($_tab === 'ladipage') :?>
						
                    <form class="ladiui-panel" id="ladipage-publish-form">
                    	<h3><strong>Manualy LadiPage Publish</strong></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="api_key">LadiPage KEY</label>
                                </th>
                                <td>
                                    <input name="ladipage_key" id="ladipage_key" type="text" class="regular-text ladiui input" placeholder="Your LadiPage Key"><br/>
                                </td>
                            </tr>
							<tr>
								<td></td>
								<td>
								<span id="ladipage-message" class="lp-hide" style="color:#0c61f2;font-style:italic">Processing...</span>
								<style>.lp-hide{display:none}</style>
								</td>
							</tr>
                        </table>
                        <div class="submit">
                            <button type="button" id="ladipage_publish" class="button button-primary ladiui button primary">Publish</button>
							
                        </div>
						</form>
						<?php endif;?>
						<?php 
							if ($_tab === 'ladiflow') :
								$forms = get_posts( array('post_type' => 'wpcf7_contact_form') );
						?>
						<form style="margin: 40px 10px;" method="post" action="" id="ladiflow_hook_form">
							<style>
								.ladiflow-hook label{
									min-width: 350px; display:inline-block
								}
								.ladiflow-hook input {
									min-width: 550px;
								}
								.ladiflow-hook .hook-item{
									display:block; margin-bottom: 10px; padding-left: 10px
								}
								.lp-hide{
									display:none;
								}
							</style>
							<table class="ladiflow-hook">
								<?php $ladiflowHookConfigs = get_option( 'ladiflow_hook_configs', array() );?>
								<tr>
									<td>
										<h3>Webhook for Form Plugins (Contact Form 7, WPForms, ..)</h3>
										<p style="font-style: italic">
											Enter the Catch Hook URL to receive data according to each Form below when user submit.
										</p>
									</td>
								</tr>
								<tr>
									<td>
										<?php foreach ($forms as $form) : ?>
											<div class="hook-item">
												<label><?php echo $form->post_title;?> - Contact Form 7</label>
												<input id="<?php echo $form->ID;?>" data-form-type="wpcf7_contact_form" data-form-id="<?php echo $form->ID;?>" type="text" value="<?php echo @$ladiflowHookConfigs[$form->ID]['url'];?>" placeholder="Webhook URL"/>
											</div>
										<?php endforeach;?>
									</td>
								</tr>
								<tr>
									<td>
										<?php $forms = get_posts( array('post_type' => 'wpforms') ); ?>
										<?php foreach ($forms as $form) : ?>
										<div class="hook-item">
											<label><?php echo $form->post_title;?> - WPForms</label>
											<input id="<?php echo $form->ID;?>" data-form-type="wpforms" data-form-id="<?php echo $form->ID;?>" type="text" value="<?php echo @$ladiflowHookConfigs[$form->ID]['url'];?>" placeholder="Webhook URL"/>
										</div>
										<?php endforeach;?>
									</td>
								</tr>
								<tr>
									<td>
										<div class="submit">
                            <button type="button" name="save_ladiflow_hook" id="save_ladiflow_hook" class="button button-primary ladiui button primary">Save</button>
										</div>
									</td>
								</tr>
								<tr>
									<td><span id="ladipage-message" class="lp-hide" style="color:#0c61f2;font-style:italic">Processing...</span></td>
								</tr>
							</table>
						</form>
						<?php endif;?>
					</div>
                    <script>
                        (function ($) {
                        	function generateRandomString(length = 10) {
							  	var text = "";
							  	var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
							  	for (var i = 0; i < length; i++)
							    text += possible.charAt(Math.floor(Math.random() * possible.length));
							  	return text;
							}
							
							function validURL(str) {
								if (str.indexOf('ladiflow.com/hooks/catch') < 0) {
									return false;
								}
							  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
								'((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
								'((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
								'(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
								'(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
								'(\\#[-a-z\\d_]*)?$','i'); // fragment locator
							  return !!pattern.test(str);
							}
							
                            $(document).ready(function () {
                                $('#ladipage_save_option').on('click', function (event) {
                                    var data = JSON.stringify($('#ladipage_config').serializeArray());
                                    $.ajax({
                                        url: ajaxurl,
                                        type: 'POST',
                                        data: {
                                            action: 'ladipage_save_config',
                                            data: data
                                        },
                                        success: function (response) {
                                            alert('Save Success');
                                        }
                                    });
                                    event.preventDefault();
                                });
                                $("#ladipage_new_api").click(function(){
                                	var api = generateRandomString(32);
                                	$("#ladipage_config #api_key").val(api);
                                });

                                $('#ladipage_publish').on('click', function (event) {
									event.preventDefault();
									$('#ladipage-message').removeClass('lp-hide');
									$('#ladipage-message').empty().text('Processing...');
                                	var ladiPageKey = $('#ladipage_key').val();
                                	if (ladiPageKey == '') {
                                		alert('Please enter your LadiPage Key!');
                                		return false;
                                	}

                                    $.ajax({
                                        url: ajaxurl,
                                        type: 'POST',
                                        data: {
                                            action: 'ladipage_publish_lp',
                                            ladipage_key: ladiPageKey
                                        },
                                        success: function (res) {
											$('#ladipage-message').empty().html(res.message);
                                        }
                                    });
                                    event.preventDefault();

                                });
								
								$('#save_ladiflow_hook').on('click', function (event) {
									event.preventDefault();
									var _data = {};
									$('#ladipage-message').removeClass('lp-hide');
									$('#ladipage-message').empty().text('Processing...');
									$('#ladiflow_hook_form input').each(function () {
										var input = $(this);
										var formID = input.attr('data-form-id');
										var postType = input.attr('data-form-type');
										var url = input.val();
										if (url) {
											if (!validURL(url)) {
												$('#' + formID).focus().css("border-color", "red");
												return false;
											} else {
												$('#' + formID).focus().css("border-color", "#8c8f94");
												_data[formID] = {
													id: formID,
													post_type: postType,
													url: url
												};
											}
										}	
									});
                                    $.ajax({
                                        url: ajaxurl,
                                        type: 'POST',
                                        data: {
                                            action: 'ladiflow_save_hook',
											data: _data
                                        },
                                        success: function (res) {
											console.log(res);
											$('#ladipage-message').empty().text('Success!');
                                        }
                                    });
                                    event.preventDefault();

                                });

                            });
                        })(jQuery);
                    </script>
                </div>
				<?php
			}
		}

		public function save_config() {
			$data   = sanitize_text_field($_POST['data']);
			$data   = json_decode( stripslashes( $data ) );
			$option = array();

			foreach ( $data as $key => $value ) {
				$option[ $value->name ] = $value->value;
			}
			update_option( 'ladipage_config', $option );
			die;
		}
		
		public function ladiflow_save_hook() {
			global $wp, $wpdb;
			$query = $GLOBALS['wpdb'];
			//echo json_decode($_POST['data']);
			print_r($_POST['data']);
			update_option( 'ladiflow_hook_configs', $_POST['data'] );
			exit;
		}

		public function publish_lp($ladiPageKey = '') {
			global $wp, $wpdb;
			$query = $GLOBALS['wpdb'];
			
			if ($ladiPageKey === '' && (isset($_POST['ladipage_key']) && trim($_POST['ladipage_key']) != ''))
				$ladiPageKey = trim($_POST['ladipage_key']);
			
			if ($ladiPageKey) {
				$url = sprintf("https://api.ladipage.com/2.0/get-source-by-ladipage-key?ladipage_key=%s", $ladiPageKey);
				$jsonString = file_get_contents($url);
				if (!$jsonString) {
					$jsonString = get_web_page($url);
				}

				if ($jsonString) {
					$response = json_decode($jsonString);
					if (isset($response->code) && $response->code == 200) {
						$data = $response->data;
						if (!isset($data->url) || $data->url == '') {
							wp_send_json( array(
								'code'    => 403,
								'message' => __( 'Page URL invalid!' )
							) ); exit;
						}

						$pageId = $this->get_id_by_slug($data->url);
						if (!$pageId) {
							try {
								kses_remove_filters();
								$id = wp_insert_post(
									array(
										'post_title'=>$data->title . ' - LadiPage', 
										'post_name'=>$data->url, 
										'post_type'=>'page', 
										'post_content'=> trim($data->html), 
										'post_status' => 'publish',
										'filter' => true ,
										'page_template'  => 'null-template.php'
									)
								);

								if ($id) {
									updateSource($data->url, $data->html);
									wp_send_json( array(
										'code'    => 200,
										'message' => __( "Publish successfully! Page URL: " . site_url() . '/' . $data->url)
									) ); exit;
								}
							} catch (Exception $ex) {
								wp_send_json( array(
									'code'    => 500,
									'message' => __( $ex->message )
								) ); exit;
							}
							
						} else {							
							kses_remove_filters();
							$post = array(
								'ID' => $pageId,
								'post_title' => $data->title . ' - LadiPage', 
					            'post_content' => trim($data->html), 
							);
					        if (wp_update_post( $post )) {
								updateSource($data->url, $data->html);
								wp_send_json( array(
										'code'    => 200,
										'message' => __( "Publish successfully! Page URL: " . site_url() . '/' . $data->url)
									) ); exit;
							}
							wp_send_json( array(
								'code'    => 500,
								'message' => __( "Can not update HTML for this Page")
							) ); exit;
						}
					} else {
						wp_send_json( array(
							'code'    => 500,
							'message' => __( $response->message )
						) ); exit;
					}
				}

				wp_send_json( array(
					'code'    => 500,
					'message' => __( "Can not update HTML from this LadiPage Key. Please try publish again" )
				) ); exit;
			}
		}

		public function generateRandomString($length = 10) {
		    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		    $charactersLength = strlen($characters);
		    $randomString = '';
		    for ($i = 0; $i < $length; $i++) {
		        $randomString .= $characters[rand(0, $charactersLength - 1)];
		    }
		    return $randomString;
		}

	}

	function Ladipage() {
		return Ladipage::instance();
	}

	Ladipage();
}

function get_web_page($request, $post = 0) {
	$data = array('message' => '', 'content' => '');

	if (function_exists('curl_exec')) {
		$ch = curl_init();
		if ($post == 1) {
			curl_setopt($ch, CURLOPT_POST,1);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, getRandomUserAgent());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		if (!$response) {
			$data['message'] = 'cURL Error Number ' . curl_errno($ch) . ' : ' . curl_error($ch);
		} else {
			$data['content'] = $response;
		}
		curl_close($ch);
	}

	return $response;
}

function _mkdir() {
	$sourceFolder = WP_CONTENT_DIR . '/ladipage/';
	if ( !file_exists( $sourceFolder ) && !is_dir( $sourceFolder ) ) {
		@mkdir($sourceFolder, 0777); 
	} 
}

function stopLandingPage($url) {
	try {
		$sourceFolder = WP_CONTENT_DIR . '/ladipage/';
		$path = $sourceFolder . '/' . $url . '.html';
		if ( file_exists( $path )) {
			@unlink($path);
		}
	} catch (Exception $ex) {
		print_r($ex);
	}
}

function updateSource($url, $html) {
	try {
		$sourceFolder = WP_CONTENT_DIR . '/ladipage/';
		$path = $sourceFolder . '/' . $url . '.html';
		@file_put_contents($path, $html);
	} catch (Exception $ex) {
		print_r($ex);
	}
}
?>
