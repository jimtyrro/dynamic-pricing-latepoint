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

if ( ! class_exists( 'TechXelaLatePointDateTimeHelper' ) ) :

	class TechXelaLatePointDateTimeHelper {

		/**
		 * @param $date
		 * @param bool $fallback
		 *
		 * @return DateTime|false
		 */
		public static function createDateFromImplicitFormat( $date, bool $fallback = false ) {
			if ( is_array( $date ) ) {
				$dateValue  = $date['value'] ?? false;
				$dateFormat = $date['format'] ?? false;
			} else {
				$dateValue = $date;
			}

			$defaultFormat = \OsSettingsHelper::get_date_format();

			if ( ! empty( $dateValue ) ) {
				$dateFormats = wp_list_pluck( \OsTimeHelper::get_date_formats_list_for_select(), 'value' );

				if ( ! empty( $dateFormat ) && in_array( $dateFormat, $dateFormats ) ) {
					return \OsWpDateTime::os_createFromFormat( $dateFormat, $dateValue );
				}

				$potentialDateObjects = [];
				foreach ( $dateFormats as $format ) {
					if ( ! empty( $dateObject = \OsWpDateTime::os_createFromFormat( $format, $dateValue ) ) &&
					     $dateObject->format( $format ) == $dateValue ) {
						$potentialDateObjects[ $format ] = $dateObject;
					}
				}

				if ( ! empty( $potentialDateObjects ) ) {
					if ( isset( $potentialDateObjects[ $defaultFormat ] ) ) {
						return $potentialDateObjects[ $defaultFormat ];
					}
				}
			}

			if ( $fallback ) {
				return \OsWpDateTime::os_createFromFormat( $defaultFormat, $dateValue ) ?:
					( \OsWpDateTime::os_createFromFormat( 'Y-m-d', $dateValue ) ?: new \OsWpDateTime() );
			}

			return false;
		}

		public static function isDateValid( $date ): bool {
			if ( is_array( $date ) ) {
				$dateValue  = $date['value'] ?? false;
				$dateFormat = $date['format'] ?? false;

				if ( $dateValue && $dateFormat &&
				     \OsWpDateTime::os_createFromFormat( $dateFormat, $dateValue )
				                  ->format( $dateFormat ) == $dateValue ) {
					return true;
				}
			} else {
				$dateValue = $date;
			}

			foreach ( wp_list_pluck( \OsTimeHelper::get_date_formats_list_for_select(), 'value' ) as $dateFormat ) {
				if ( ! empty( $dateObject = \OsWpDateTime::os_createFromFormat( $dateFormat, $dateValue ) ) &&
				     $dateObject->format( $dateFormat ) == $dateValue ) {
					return true;
				}
			}

			return false;
		}
	}

endif;
