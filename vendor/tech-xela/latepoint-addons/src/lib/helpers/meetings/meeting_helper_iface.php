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

if ( ! interface_exists( 'TechXelaLatePointMeetingHelperIface' ) ) {

	interface TechXelaLatePointMeetingHelperIface {

		public static function createMeeting( OsBookingModel $booking, bool $updateIfExists = false ): void;

		public static function deleteMeetingForBookingId( $bookingId ): void;
	}

}