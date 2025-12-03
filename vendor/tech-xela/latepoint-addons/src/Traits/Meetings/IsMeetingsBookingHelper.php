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

namespace TechXela\LatePointAddons\Traits\Meetings;

if ( ! trait_exists( '\TechXela\LatePointAddons\Traits\Meetings\IsMeetingsBookingHelper' ) ) {
	trait IsMeetingsBookingHelper {
		/**
		 * @var string $meetingSystemCode;
         *
		 * @var string $meetingSystemName;
         *
		 * @var \TechXela\LatePointAddons\Traits\IsSettingsHelper $settingsHelperClass;
         *
		 * @var \TechXelaLatePointMeetingsServiceHelper $serviceHelperClass;
         *
		 * @var \TechXelaLatePointMeetingHelperIface $meetingHelperClass;
		 */

		public static function getMeetingStatuses(): array {
			return apply_filters( 'tx_latepoint_' . self::$meetingSystemCode . '_meeting_booking_statuses', [ LATEPOINT_BOOKING_STATUS_APPROVED ] );
		}

		public static function bookingCreated( \OsBookingModel $booking ) {
			if ( in_array( $booking->status, self::getMeetingStatuses() ) &&
			     \OsMeetingSystemsHelper::is_external_meeting_system_enabled( self::$meetingSystemCode ) &&
			     self::$serviceHelperClass::isEnabledForService( $booking->service_id ) ) {
				self::$meetingHelperClass::createMeeting( $booking );
			}
		}

		public static function bookingUpdated( \OsBookingModel $booking, $oldBooking = false ) {
			if ( ! in_array( $booking->status, self::getMeetingStatuses() ) ||
			     ( $oldBooking && ( $oldBooking->agent_id != $booking->agent_id ||
			                        $oldBooking->service_id != $booking->service_id ||
			                        $oldBooking->location_id != $booking->location_id ) ) ) {
				self::bookingWillBeDeleted( $booking->id );
			}

			if ( in_array( $booking->status, self::getMeetingStatuses() ) &&
			     \OsMeetingSystemsHelper::is_external_meeting_system_enabled( self::$meetingSystemCode ) &&
			     self::$serviceHelperClass::isEnabledForService( $booking->service_id ) ) {
				self::$meetingHelperClass::createMeeting( $booking, true );
			}
		}

		public static function bookingWillBeDeleted( $bookingId ) {
			if ( \OsMeetingSystemsHelper::is_external_meeting_system_enabled( self::$meetingSystemCode ) ) {
				self::$meetingHelperClass::deleteMeetingForBookingId( $bookingId );
			}
		}

		public static function bookingQuickFormAfter( \OsBookingModel $booking ) {
		}

		protected static function getBookingVars(): array {
			return [];
		}

		protected static function prefixVarNeedle( $needle ): string {
			return self::$meetingSystemCode . "_$needle";
		}

		public static function availableVarsBooking() {
			if ( ! empty( self::getBookingVars() ) ) {
				echo '<li class="pt-10"><strong>' . self::$meetingSystemName . ':</strong></li>';
				foreach ( self::getBookingVars() as $varNeedle => $varLabel ) {
					$varNeedle = \TechXelaLatePointUtilHelper::wrapElementInDoubleCurlyBraces( $varNeedle );
					?>
                    <li>
                        <span class="var-label"><?= $varLabel ?>:</span>
                        <span class="var-code os-click-to-copy"><?= $varNeedle ?></span>
                    </li>
					<?php
				}
			}
		}

		public static function replaceBookingVars( $text, $booking, $originalText, $needles, $replacements ) {
			if ( ! empty( self::getBookingVars() ) ) {
				$needles      = \TechXelaLatePointUtilHelper::wrapElementsInDoubleCurlyBraces( array_keys( self::getBookingVars() ) );
				$replacements = self::getBookingVarsReplacements( $text, $needles, $booking );

				return str_replace( $needles, $replacements, $text );
			}

			return $text;
		}

		protected static function getBookingVarsReplacements( $text, $needles, $booking ): array {
			return [];
		}

	}

}