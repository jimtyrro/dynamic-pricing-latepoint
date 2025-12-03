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

if ( ! class_exists( 'TechXelaLatePointDynamicPricingDatabaseHelper' ) ) :

	final class TechXelaLatePointDynamicPricingDatabaseHelper {
		public static function addonsSqls( $sqls ): array {
			if ( version_compare( TECHXELA_LATEPOINT_DYNAMIC_PRICING_DB_VERSION, '1.0.2', 'ge' ) ) {
				foreach ( TechXelaLatePointDynamicPricingRulesHelper::getFormBlocksArr() as $rule ) {
					$ruleUpdated = false;

					if ( ! isset( $rule['apply_to_full_amount'] ) || ! isset( $rule['apply_to_deposit_amount'] ) ) {
						$rule        = array_merge( $rule, [
							'apply_to_full_amount'    => 'on',
							'apply_to_deposit_amount' => 'off'
						] );
						$ruleUpdated = true;
					}

					if ( ! isset( $rule['recurring'] ) || ! isset( $rule['recurrence'] ) ) {
						$rule        = array_merge( $rule, [
							'recurring'  => 'off',
							'recurrence' => [
								'value' => 1,
								'unit'  => 'D'
							]
						] );
						$ruleUpdated = true;
					}

					if ( $ruleUpdated ) {
						TechXelaLatePointDynamicPricingRulesHelper::save( $rule );
					}
				}
			}

			return $sqls;
		}
	}

endif;