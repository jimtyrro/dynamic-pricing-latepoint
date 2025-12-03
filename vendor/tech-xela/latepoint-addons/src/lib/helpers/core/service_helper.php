<?php
/*
 * LatePoint Addons Framework
 * Copyright (c) 2021-2023 TechXela (https://codecanyon.net/user/tech-xela). All Rights Reserved.
 *
 * LICENSE
 * -------
 * This software is furnished under license(s) and may be used and copied only in accordance with the terms of such
 * license(s) along with the inclusion of the above copyright notice. If you purchased an item through CodeCanyon, in
 * which booking software came included, please read the full license(s) at: https://codecanyon.net/licenses/standard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'TechXelaLatePointServiceHelper' ) ) :

	class TechXelaLatePointServiceHelper {
		public static function getAmountToCharge( OsBookingModel $booking, $applyCoupons = false, $applyTaxes = false ) {
			if ( empty( $booking->service_id ) ) {
				return 0;
			}

			$service = new OsServiceModel( $booking->service_id );

			if ( $booking->payment_portion == LATEPOINT_PAYMENT_PORTION_DEPOSIT ) {
				$amount = $service->get_deposit_amount_for_duration( $booking->duration );

				return apply_filters( 'latepoint_deposit_amount_for_service', $amount, $booking, $applyCoupons );
			} else {
				$amount = $service->get_charge_amount_for_duration( $booking->duration );

				return apply_filters( 'latepoint_full_amount_for_service', $amount, $booking, $applyCoupons, $applyTaxes );
			}
		}
	}

endif;
