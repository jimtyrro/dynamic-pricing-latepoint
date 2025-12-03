<?php
/*
 * Whatsapp for LatePoint
 * Copyright (c) 2022 TechXela (https://codecanyon.net/user/tech-xela). All Rights Reserved.
 *
 * LICENSE
 * -------
 * This software is furnished under license(s) and may be used and copied only in accordance with the terms of such
 * license(s) along with the inclusion of the above copyright notice. If you purchased an item through CodeCanyon, in
 * which this software came included, please read the full license(s) at: https://codecanyon.net/licenses/standard
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'TechXelaLatePointPaymentsDebugHelper' ) ) :

	final class TechXelaLatePointPaymentsDebugHelper {
		public static function logException( \Throwable $exception, $processorCode, $persist = false ) {
			TechXelaLatePointDebugHelper::logException( $exception, "PAYMENTS_$processorCode", $persist );
		}
	}

endif;
