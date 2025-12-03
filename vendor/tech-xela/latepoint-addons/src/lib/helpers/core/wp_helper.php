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

if ( ! class_exists( 'TechXelaLatePointWPHelper' ) ) :

	class TechXelaLatePointWPHelper {
		public static function getPagesForSelect(): array {
			$pages   = [];
			$dbPages = get_pages();
			foreach ( $dbPages as $page ) {
				$pages[] = [
					'value' => $page->ID,
					'label' => "$page->post_title (#$page->ID)"
				];
			}

			return $pages;
		}

		public static function adminUrl( $url ): string {
			$queryArgs      = [ 'fromTXLP', 'TXLPReturnTo' ];
			$queryArgsToAdd = [];
			foreach ( $queryArgs as $queryArg ) {
				if ( $queryArgVal = OsRouterHelper::get_request_param( $queryArg ) ) {
					$queryArgsToAdd[ $queryArg ] = $queryArgVal;
				}
			}

			if ( ! empty( $queryArgsToAdd ) ) {
				$url = add_query_arg( $queryArgsToAdd, $url );
			}

			return $url;
		}
	}

endif;
