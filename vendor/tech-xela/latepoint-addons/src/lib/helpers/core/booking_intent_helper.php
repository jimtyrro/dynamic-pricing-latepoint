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

if ( ! class_exists( 'TechXelaLatePointBookingIntentHelper' ) ) :

	class TechXelaLatePointBookingIntentHelper {
		public static function bookingIntentValidForForm( OsBookingIntentModel $bookingIntent, array $formBookingData = [], bool $checkCustomer = false ): bool {
			$isValid = true;

			if ( empty( $formBookingData ) ) {
				$formBookingData = OsParamsHelper::get_param('booking');
			}

			$intentBookingData = json_decode( $bookingIntent->booking_data, true );

			if ( is_array( $intentBookingData ) && is_array( $formBookingData ) ) {
				foreach ( $formBookingData as $dataKey => $dataValue ) {
					if ( $dataValue == 'any' || ! isset( $intentBookingData[ $dataKey ] ) || $intentBookingData[ $dataKey ] !== $dataValue ) {
						return false;
					}
				}
			}

			if ( $checkCustomer && OsAuthHelper::is_customer_logged_in() && $bookingIntent->customer_id != OsAuthHelper::get_logged_in_customer_id() ) {
				$isValid = false;
			}

			return $isValid;
		}
	}

endif;
