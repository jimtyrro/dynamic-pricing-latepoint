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

if ( ! class_exists( 'TechXelaLatePointDebugHelper' ) ) :

	class TechXelaLatePointDebugHelper {
		public static function logException( \Throwable $exception, string $tag, $persist = false ) {
			self::logError( $tag, "{$exception->getMessage()} :: {$exception->getTraceAsString()}", $persist );
		}

		public static function logError( string $tag, string $message, $persist = false ) {
			$tag       = strtoupper( $tag );
			$timestamp = date( 'Y-m-d H:i:s' );
			error_log( "$timestamp - $tag ::: $message\n", 3, WP_CONTENT_DIR . '/tx_lp_error.log' );

			if ( $persist ) {
				OsDebugHelper::log( esc_html_tx__( 'Error encountered. See exception', 'latepoint-addons' ), strtolower( $tag ) . '_error', [ 'exception' => $message ] );
			}
		}
	}

endif;
