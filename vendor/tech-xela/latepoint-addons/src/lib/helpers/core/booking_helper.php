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

if ( ! class_exists( 'TechXelaLatePointBookingHelper' ) ) :

	class TechXelaLatePointBookingHelper {
		public static function isBookingStillAvailable( OsBookingModel $booking ): bool {
			if ( $booking->agent_id == LATEPOINT_ANY_AGENT ) {
				$available = OsBookingHelper::get_any_agent_for_booking_by_rule( $booking );
			} else {
				$available = OsBookingHelper::is_booking_request_available( \LatePoint\Misc\BookingRequest::create_from_booking_model( $booking ) );
			}

			return apply_filters( 'tx_latepoint_woocommerce_is_booking_still_available', $available, $booking );
		}

		public static function getGroupBookings( OsBookingModel $booking ): array {
			return ( new OsBookingModel() )->where(
				[
					'id !='       => $booking->id,
					'start_date'  => $booking->start_date,
					'end_date'    => $booking->end_date,
					'start_time'  => $booking->start_time,
					'end_time'    => $booking->end_time,
					'service_id'  => $booking->service_id,
					'location_id' => $booking->location_id
				]
			)->get_results_as_models();
		}

		public static function getAmountToCharge( OsBookingModel $booking, $applyCoupons = false, $applyTaxes = false ) {
			if ( $booking->payment_portion == LATEPOINT_PAYMENT_PORTION_DEPOSIT ) {
				return $booking->deposit_amount_to_charge( $applyCoupons );
			} else {
				return $booking->full_amount_to_charge( $applyCoupons, $applyTaxes );
			}
		}
	}

endif;
