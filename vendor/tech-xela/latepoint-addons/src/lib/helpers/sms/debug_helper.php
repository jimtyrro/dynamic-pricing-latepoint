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

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'TechXelaLatePointSmsDebugHelper' ) ) :

	final class TechXelaLatePointSmsDebugHelper {
		public static function logException( \Throwable $exception, $smsProcessorCode, $persist = false ) {
			TechXelaLatePointDebugHelper::logException( $exception, "sms_$smsProcessorCode", $persist );
		}

		public static function logSendException( \Throwable $exception, $smsProcessorCode, $smsProcessorName ) {
			self::logException( $exception, $smsProcessorCode );
			OsDebugHelper::log(
				sprintf( esc_html_tx__( 'Error sending SMS via %s ', 'latepoint-addons' ), $smsProcessorName ),
				"error_sms_$smsProcessorCode",
				[ 'errors' => [ $exception->getMessage() ] ]
			);
		}
	}

endif;
