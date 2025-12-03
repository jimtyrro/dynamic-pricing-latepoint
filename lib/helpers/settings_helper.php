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
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'TechXelaLatePointDynamicPricingSettingsHelper' ) ) {

	final class TechXelaLatePointDynamicPricingSettingsHelper {
		use \TechXela\LatePointAddons\Traits\IsSettingsHelper;

		public const scopedOptionPrefix = 'dynamic_pricing';
	}

}