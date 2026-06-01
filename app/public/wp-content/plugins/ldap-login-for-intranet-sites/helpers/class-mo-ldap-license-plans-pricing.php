<?php
/**
 * This file stores pricing of the licensing plans.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage helpers
 */

namespace MO_LDAP\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'MO_LDAP_License_Plans_Pricing' ) ) {
	/**
	 * MO_LDAP_License_Plans_Pricing : Class for pricing of the licensing plans.
	 */
	class MO_LDAP_License_Plans_Pricing {

		/**
		 * Var pricing_kerberos
		 *
		 * @var array
		 */
		public $pricing_kerberos;
		/**
		 * Var pricing_standard
		 *
		 * @var array
		 */
		public $pricing_standard;
		/**
		 * Var pricing_enterprise
		 *
		 * @var array
		 */
		public $pricing_enterprise;
		/**
		 * Var subsite_intances
		 *
		 * @var array
		 */
		public $subsite_intances;
		/**
		 * Var mulpricing_kerberos
		 *
		 * @var array
		 */
		public $mulpricing_kerberos;
		/**
		 * Var mulpricing_standard
		 *
		 * @var array
		 */
		public $mulpricing_standard;
		/**
		 * Var mulpricing_enterprise
		 *
		 * @var array
		 */
		public $mulpricing_enterprise;

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {

			$this->pricing_kerberos = array(
				'1' => '249',
				'2' => '438',
				'3' => '620',
				'4' => '797',
				'5' => '971',
			);

			$this->pricing_standard = array(
				'1' => '349',
				'2' => '628',
				'3' => '890',
				'4' => '1,133',
				'5' => '1,359',
			);

			$this->pricing_enterprise = array(
				'1' => '449',
				'2' => '808',
				'3' => '1,144',
				'4' => '1,458',
				'5' => '1,748',
			);

			$this->mulpricing_kerberos = array(
				'1' => '249',
				'2' => '438',
				'3' => '620',
				'4' => '797',
				'5' => '971',
			);

			$this->mulpricing_standard = array(
				'1' => '349',
				'2' => '628',
				'3' => '890',
				'4' => '1,133',
				'5' => '1,359',
			);

			$this->mulpricing_enterprise = array(
				'1' => '449',
				'2' => '808',
				'3' => '1,144',
				'4' => '1,458',
				'5' => '1,748',
			);

			$this->subsite_intances = array(
				'3'  => '60',
				'5'  => '90',
				'10' => '160',
				'15' => '200',
				'20' => '240',
			);
		}
	}
}
