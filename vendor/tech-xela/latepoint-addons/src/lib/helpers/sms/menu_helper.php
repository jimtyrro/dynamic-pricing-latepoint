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

if ( ! class_exists( 'TechXelaLatePointSmsMenuHelper' ) ) :
	final class TechXelaLatePointSmsMenuHelper {
		public static function menuItems(): array {
			return [
				[
					'id'               => 'techxela_sms_tester_menu',
					'is_techxela_menu' => true,
					'label'            => esc_html_tx__( 'SMS Tester', 'latepoint-addons' ),
					'icon'             => '',
					'link'             => \OsRouterHelper::build_link( [ 'tech_xela_sms', 'tester' ] )
				]
			];
		}
	}
endif;
