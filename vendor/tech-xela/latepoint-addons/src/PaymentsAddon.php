<?php
/*
 * LatePoint Addons Framework
 * Copyright (c) 2021-2023 TechXela (https://codecanyon.net/user/tech-xela). All Rights Reserved.
 *
 * LICENSE
 * -------
 * This software is furnished under license(s) and may be used and copied only in accordance with the terms of such
 * license(s) along with the inclusion of the above copyright notice. If you purchased an item through CodeCanyon, in
 * which this software came included, please read the full license(s) at: https://codecanyon.net/licenses/standard
 */

namespace TechXela\LatePointAddons;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\TechXela\LatePointAddons\PaymentsAddon' ) ) {

	abstract class PaymentsAddon extends CoreAddon {
		/**
		 * Base Payments Addon information.
		 */
		public $paymentProcessorCode;
		public $paymentProcessorName;
		public $paymentMethodName;
		public $paymentProcessorLogoImg = 'processor-logo.svg';
		public $paymentMethodLogoImg = 'method-logo.svg';
		public $paymentMethodTimeType = 'now';

		public function __construct() {
			parent::__construct();

			$this->paymentMethodName = $this->paymentProcessorName;
		}

		final protected function includes() {
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/payments/debug_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/payments/settings_helper.php';
			$this->paymentsAddonIncludes();
		}

		/**
		 * Payments addon-specific includes
		 */
		protected function paymentsAddonIncludes() {
		}

		final protected function initHooks() {
			add_filter( 'latepoint_payment_processors', [ $this, 'registerPaymentProcessor' ], 10, 2 );

			add_filter( 'latepoint_all_payment_methods', [ $this, 'registerPaymentMethods' ] );

			add_filter( 'latepoint_enabled_payment_methods', [ $this, 'registerEnabledPaymentMethods' ] );

			add_filter( 'latepoint_payment_sub_step_for_payment_step', [ $this, 'subStepForPaymentStep' ] );

			add_action( 'latepoint_payment_processor_settings', [ $this, '_paymentProcessorSettings' ], 10 );

			$this->paymentsAddonInitHooks();
		}

		protected function init() {
			$this->paymentsAddonInit();
		}

		protected function paymentsAddonInit() {
		}

		protected function latePointInit() {
			$this->paymentsAddonLatePointInit();
		}

		protected function paymentsAddonLatePointInit() {
		}

		protected function paymentsAddonInitHooks() {
		}

		final public function _paymentProcessorSettings( $processorCode ) {
			if ( $processorCode === $this->paymentProcessorCode ) {
				$this->paymentProcessorSettings();
			}
		}

		/**
		 * Payments addon-specific admin settings
		 */
		protected function paymentProcessorSettings() {
		}

		final public function subStepForPaymentStep( $sub_step ) {
			if ( \OsPaymentsHelper::is_payment_processor_enabled( $this->paymentProcessorCode ) && \TechXelaLatePointLicenseHelper::isLicenseActive( $this->productId ) ) {
				$sub_step = 'payment-method-content';
			}

			return $sub_step;
		}

		private function getSupportedPaymentMethods(): array {
			$methodName = \TechXelaLatePointPaymentsSettingsHelper::getPaymentMethodName( get_class( $this ) );

			$supportedPaymentMethods = [
				$this->paymentProcessorCode => [
					'name'      => $methodName,
					'label'     => $methodName,
					'image_url' => \TechXelaLatePointPaymentsSettingsHelper::getPaymentMethodImageUrl( get_class( $this ) ),
					'code'      => $this->paymentProcessorCode,
					'time_type' => $this->paymentMethodTimeType
				]
			];

			return apply_filters( 'techxela_latepoint_payments_supported_payment_methods', $supportedPaymentMethods, $this->paymentProcessorCode );
		}

		final public function registerEnabledPaymentMethods( $enabled_payment_methods ) {
			if ( \OsPaymentsHelper::is_payment_processor_enabled( $this->paymentProcessorCode ) && \TechXelaLatePointLicenseHelper::isLicenseActive( $this->productId ) ) {
				$enabled_payment_methods = array_merge( $enabled_payment_methods, $this->getSupportedPaymentMethods() );
			}

			return $enabled_payment_methods;
		}

		final public function registerPaymentMethods( $payment_methods ): array {
			return array_merge( $payment_methods, $this->getSupportedPaymentMethods() );
		}

		final public function registerPaymentProcessor( $payment_processors, $enabled_only ) {
			$payment_processors[ $this->paymentProcessorCode ] = [
				'code'      => $this->paymentProcessorCode,
				'name'      => $this->paymentProcessorName,
				'image_url' => $this->publicImgUrl() . $this->paymentProcessorLogoImg
			];

			return $payment_processors;
		}

		private function __loadAdminScriptsAndStyles() {
			wp_enqueue_style(
				'techxela-latepoint-addons-payments-admin',
				$this->frameworkSrcUrl() . '/public/css/payments-admin.css',
				[],
				self::$frameworkVersion
			);
		}

		protected function loadAdminScriptsAndStyles() {
			$this->__loadAdminScriptsAndStyles();
			$this->paymentsAddonLoadAdminScriptsAndStyles();
		}

		protected function paymentsAddonLoadAdminScriptsAndStyles() {

		}
	}

}