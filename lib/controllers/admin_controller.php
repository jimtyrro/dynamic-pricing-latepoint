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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'OsTechXelaLatePointDynamicPricingAdminController' ) ) :

	final class OsTechXelaLatePointDynamicPricingAdminController extends OsController {
		public function __construct() {
			parent::__construct();

			$this->views_folder          = TECHXELA_LATEPOINT_DYNAMIC_PRICING_VIEWS_ABSPATH;
			$this->vars['page_header']   = OsMenuHelper::get_menu_items_by_id( 'settings' );
			$this->vars['breadcrumbs'][] = [
				'label' => esc_html_tx__( 'Dynamic Pricing', 'dynamic-pricing-latepoint' ),
				'link'  => OsRouterHelper::build_link( [
					'tech_xela_late_point_dynamic_pricing_admin',
					'settings'
				] )
			];
		}

		public function settings() {
			$this->format_render( __FUNCTION__ . '/index' );
		}
	}

endif;
