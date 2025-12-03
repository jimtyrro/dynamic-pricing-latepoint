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

defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'OsTechXelaDynamicPricingRulesController' ) ) {

	class OsTechXelaDynamicPricingRulesController extends OsController {
		use \TechXela\LatePointAddons\Traits\FormBlock\IsFormBlockController;

		public function __construct() {
			parent::__construct();

			$this->formBlockHelper            = TechXelaLatePointDynamicPricingRulesHelper::class;
			$this->logTagPrefix               = 'dynamic_pricing_rules';
			$this->newFormBlockName           = esc_html_tx__( 'New Rule', 'dynamic-pricing-latepoint' );
			$this->formBlockObjectName        = esc_html_tx__( 'Rule', 'dynamic-pricing-latepoint' );
			$this->formBlockObjectNamePlural  = esc_html_tx__( 'Rules', 'dynamic-pricing-latepoint' );
			$this->formBlocksContainerClasses = ' tx-dynamic-pricing-rules-w';
			$this->formBlocksAddBoxLabel      = esc_html_tx__( 'Add Rule', 'dynamic-pricing-latepoint' );
		}
	}
}