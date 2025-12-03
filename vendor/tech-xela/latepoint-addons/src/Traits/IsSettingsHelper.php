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

namespace TechXela\LatePointAddons\Traits;

if ( ! trait_exists( 'TechXela\LatePointAddons\Traits\IsSettingsHelper' ) ) {
	trait IsSettingsHelper {
		public static function optionName( string $optionPrefix, string $optionName ): string {
			if ( ! empty( $optionPrefix ) ) {
				$optionPrefix = "{$optionPrefix}_";
			}

			$scopedOptionPrefix = ! empty( self::scopedOptionPrefix ) ? ( self::scopedOptionPrefix . '_' ) : '';

			return \TechXelaLatePointSettingsHelper::prefixSetting( "{$scopedOptionPrefix}{$optionPrefix}$optionName" );
		}

		public static function settingName( string $optionPrefix, string $optionName ): string {
			$optionName = self::optionName( $optionPrefix, $optionName );

			return "settings[$optionName]";
		}

		public static function saveSetting( string $optionPrefix, string $optionName, $value ): bool {
			return \OsSettingsHelper::save_setting_by_name( self::optionName( $optionPrefix, $optionName ), $value );
		}

		public static function getSetting( string $optionPrefix, string $optionName, $default = false ) {
			return \OsSettingsHelper::get_settings_value( self::optionName( $optionPrefix, $optionName ), $default );
		}

		public static function deleteSetting( string $optionPrefix, string $optionName, $default = false ) {
			\OsSettingsHelper::remove_setting_by_name( self::optionName( $optionPrefix, $optionName ) );
		}

		public static function isOn( string $optionPrefix, string $optionName, $default = false ): bool {
			if ( ! $default || $default == 'off' ) {
				$default = false;
			}

			return self::getSetting( $optionPrefix, $optionName, $default ) == 'on';
		}
	}
}