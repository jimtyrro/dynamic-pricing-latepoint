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

if ( ! class_exists( 'TechXelaLatePointMeetingsSettingsHelper' ) ) :

	class TechXelaLatePointMeetingsSettingsHelper {
		use \TechXela\LatePointAddons\Traits\IsSettingsHelper;

		const scopedOptionPrefix = 'meetings';

		public static function getMeetingSystemCode( string $meetingSystemClass ) {
			return TechXelaLatePointAddonsHelper::getAddonObject( $meetingSystemClass )->meetingSystemCode;
		}

		public static function getMeetingSystemName( string $meetingSystemClass ) {
			return TechXelaLatePointAddonsHelper::getAddonObject( $meetingSystemClass )->meetingSystemName;
		}

		public static function getMeetingSystemLogoUrl( string $meetingSystemClass ): string {
			/** @var \TechXela\LatePointAddons\MeetingsAddon $meetingSystemObject */
			$meetingSystemObject = TechXelaLatePointAddonsHelper::getAddonObject( $meetingSystemClass );

			$logoImg = $meetingSystemObject->meetingSystemLogoImg ?: "$meetingSystemObject->addonSlug-logo.svg";

			return $meetingSystemObject->publicImgUrl( $logoImg );
		}

		public static function getMeetingSystemIconUrl( string $meetingSystemClass ): string {
			/** @var \TechXela\LatePointAddons\MeetingsAddon $meetingSystemObject */
			$meetingSystemObject = TechXelaLatePointAddonsHelper::getAddonObject( $meetingSystemClass );

			$iconImg = $meetingSystemObject->meetingSystemIconImg ?: "$meetingSystemObject->addonSlug-icon.svg";

			return $meetingSystemObject->publicImgUrl( $iconImg );
		}
	}

endif;
