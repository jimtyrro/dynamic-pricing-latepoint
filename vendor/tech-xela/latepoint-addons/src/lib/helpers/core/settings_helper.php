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

if ( ! class_exists( 'TechXelaLatePointSettingsHelper' ) ) :

	final class TechXelaLatePointSettingsHelper {
		public static function prefixSetting( $name ): string {
			return 'techxela_' . $name;
		}

		public static function getSetting( $name, $default = false ) {
			return \OsSettingsHelper::get_settings_value( self::prefixSetting( $name ), $default );
		}

		public static function saveSetting( $name, $value ): bool {
			return \OsSettingsHelper::save_setting_by_name( self::prefixSetting( $name ), $value );
		}

		public static function deleteSetting( $name ): void {
			\OsSettingsHelper::remove_setting_by_name( self::prefixSetting( $name ) );
		}
	}

endif;