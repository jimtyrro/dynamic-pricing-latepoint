<?php
/*
 * Dynamic Pricing for LatePoint
 * Copyright (c) 2023 TechXela (https://codecanyon.net/user/tech-xela). All Rights Reserved.
 *
 * LICENSE
 * -------
 * This software is furnished under license(s) and may be used and copied only in accordance with the terms of such
 * license(s) along with the inclusion of the above copyright notice. If you purchased this software through CodeCanyon,
 * please read the full license(s) at: https://codecanyon.net/licenses/standard
 */

if ( ! class_exists( 'TechXelaLatePointDynamicPricingMenuHelper' ) ) {

	final class TechXelaLatePointDynamicPricingMenuHelper {
		public static function sideMenu( $menus ) {
			if ( OsAuthHelper::is_admin_logged_in() ) {
				$dynamicPricingSettingsMenu = [
					'id' => 'techxela_dynamic_pricing_settings_menu',
					'label' => esc_html_tx__( 'Dynamic Pricing', 'dynamic-pricing-latepoint' ),
					'icon' => '',
					'link' => OsRouterHelper::build_link( [
						'tech_xela_late_point_dynamic_pricing_admin',
						'settings'
					] )
				];

				return TechXelaLatePointMenuHelper::insertMenuItems( $menus, [ $dynamicPricingSettingsMenu ], 'settings', 0, 0, 5 );
			}

			return $menus;
		}
	}

}