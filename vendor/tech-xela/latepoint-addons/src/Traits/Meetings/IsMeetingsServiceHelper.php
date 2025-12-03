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

if ( ! trait_exists( '\TechXela\LatePointAddons\Traits\Meetings\IsMeetingsServiceHelper' ) ) {
	trait IsMeetingsServiceHelper {
		/**
		 * @var string $meetingSystemViewsAbsPath;
		 *
		 * @var string $meetingSystemCode;
		 *
		 * @var string $meetingSystemName;
		 *
		 * @var \TechXela\LatePointAddons\Traits\IsSettingsHelper $settingsHelperClass;
		 */

		public static function serviceFormAfter( $service ) {
			include self::$meetingSystemViewsAbsPath . 'services/' . self::$meetingSystemCode . '_settings.php';
		}

		public static function serviceSaved( \OsServiceModel $service, $isNewRecord, $serviceParams ) {
			if ( isset( $serviceParams['meta'] ) ) {
				foreach ( $serviceParams['meta'] as $metaKey => $metaValue ) {
					if ( str_starts_with( $metaKey, self::$meetingSystemCode . '_' ) || str_ends_with( $metaKey, '_' . self::$meetingSystemCode ) ) {
						$service->save_meta_by_key( $metaKey, $metaValue );
					}
				}
			}
		}
	}

}