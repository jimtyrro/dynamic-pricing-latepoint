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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'TechXelaLatePointPaymentsSettingsHelper' ) ) :

	class TechXelaLatePointPaymentsSettingsHelper {
		use \TechXela\LatePointAddons\Traits\IsSettingsHelper;

		const scopedOptionPrefix = 'payments';

		/**
		 * @param string $paymentProcessorClass
		 *
		 * @return mixed|string
		 */
		public static function getPaymentMethodName( $paymentProcessorClass ) {
			/** @var \TechXela\LatePointAddons\PaymentsAddon $paymentProcessorObject */
			$paymentProcessorObject = get_tx_lp_global( lcfirst( $paymentProcessorClass ) );

			return TechXelaLatePointPaymentsSettingsHelper::getSetting( $paymentProcessorObject->paymentProcessorCode, 'method_name' ) ?: $paymentProcessorObject->paymentProcessorName;
		}

		/**
		 * @param string $paymentProcessorClass
		 *
		 * @return mixed|string
		 */
		public static function getPaymentMethodImageUrl( $paymentProcessorClass ) {
			/** @var \TechXela\LatePointAddons\PaymentsAddon $paymentProcessorObject */
			$paymentProcessorObject = get_tx_lp_global( lcfirst( $paymentProcessorClass ) );
			$methodImageId          = TechXelaLatePointPaymentsSettingsHelper::getSetting( $paymentProcessorObject->paymentProcessorCode, 'method_image_id' );
			if ( $methodImageId ) {
				return OsImageHelper::get_image_url_by_id( $methodImageId );
			}

			return $paymentProcessorObject->publicImgUrl( $paymentProcessorObject->paymentMethodLogoImg );
		}
	}

endif;
