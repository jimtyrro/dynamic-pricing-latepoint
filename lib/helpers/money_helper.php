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

if ( ! class_exists( 'TechXelaLatePointDynamicPricingMoneyHelper' ) ) {

	final class TechXelaLatePointDynamicPricingMoneyHelper {

		public static function fullAmountForService( $amount, $booking, $applyCoupons = null, $applyTaxes = null ): float {
			// DEBUG: Log function entry
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('=== DYNAMIC PRICING DEBUG: fullAmountForService called ===');
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Amount: ' . $amount);
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Booking ID: ' . (isset($booking->id) ? $booking->id : 'N/A'));
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Booking Date: ' . (isset($booking->start_date) ? $booking->start_date : 'N/A'));
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Apply Coupons: ' . var_export($applyCoupons, true));
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Apply Taxes: ' . var_export($applyTaxes, true));

			$licenseActive = TechXelaLatePointLicenseHelper::isLicenseActive( TECHXELA_LATEPOINT_DYNAMIC_PRICING_PRODUCT_ID );
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('License Active: ' . ($licenseActive ? 'YES' : 'NO'));

			if ( $licenseActive ) {
				$globalSet = isset_tx_lp_global( 'dynamic_pricing_calculating_service_amount' );
				if (defined('WP_DEBUG') && WP_DEBUG) error_log('Global dynamic_pricing_calculating_service_amount set: ' . ($globalSet ? 'YES' : 'NO'));
				if (defined('WP_DEBUG') && WP_DEBUG) error_log('Condition check result: ' . (($globalSet || $applyTaxes || $booking) ? 'PASS' : 'FAIL'));

				if ( $globalSet || $applyTaxes || $booking ) {
					$rules = TechXelaLatePointDynamicPricingRulesHelper::getFormBlocksArr();
					if (defined('WP_DEBUG') && WP_DEBUG) error_log('Total pricing rules found: ' . count($rules));

					foreach ( $rules as $ruleId => $rule ) {
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('--- Checking Rule ID: ' . $ruleId . ' ---');

						$modifierTarget = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_target', 'booking' );
						$applyToFull = TechXelaLatePointDynamicPricingRulesHelper::isOn( $rule, 'apply_to_full_amount' );
						$satisfied = TechXelaLatePointDynamicPricingRulesHelper::isFormBlockSatisfied( $rule, $booking );

						if (defined('WP_DEBUG') && WP_DEBUG) error_log('Rule Name: ' . TechXelaLatePointDynamicPricingRulesHelper::getSettingValue($rule, 'name', 'Unnamed'));
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('Modifier Target: ' . $modifierTarget . ' (need: service)');
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('Apply to Full Amount: ' . ($applyToFull ? 'YES' : 'NO'));
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('Rule Satisfied: ' . ($satisfied ? 'YES' : 'NO'));

						if ( $modifierTarget == 'service' && $applyToFull && $satisfied ) {
							if (defined('WP_DEBUG') && WP_DEBUG) error_log('✓ RULE MATCHES! Applying modifier...');
							$beforeAmount = $amount;
							self::modifyAmount( $amount, $rule, $booking );
							if (defined('WP_DEBUG') && WP_DEBUG) error_log('Amount changed from ' . $beforeAmount . ' to ' . $amount);
						} else {
							if (defined('WP_DEBUG') && WP_DEBUG) error_log('✗ Rule does not match');
						}
					}
				} else {
					unset_tx_lp_global( 'dynamic_pricing_calculating_service_amount' );
					if (defined('WP_DEBUG') && WP_DEBUG) error_log('Conditions not met, unsetting global');
				}
			}

			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Final amount: ' . $amount);
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('=== END fullAmountForService ===');
			return $amount;
		}

		public static function fullAmount( $amount, $booking, $applyCoupons = null, $applyTaxes = null ): float {
			// DEBUG: Log function entry
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('=== DYNAMIC PRICING DEBUG: fullAmount called ===');
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Amount: ' . $amount);
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Booking ID: ' . (isset($booking->id) ? $booking->id : 'N/A'));
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Booking Date: ' . (isset($booking->start_date) ? $booking->start_date : 'N/A'));

			$licenseActive = TechXelaLatePointLicenseHelper::isLicenseActive( TECHXELA_LATEPOINT_DYNAMIC_PRICING_PRODUCT_ID );
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('License Active: ' . ($licenseActive ? 'YES' : 'NO'));

			if ( $licenseActive ) {
				$globalSet = isset_tx_lp_global( 'dynamic_pricing_calculating_booking_amount' );
				if (defined('WP_DEBUG') && WP_DEBUG) error_log('Global dynamic_pricing_calculating_booking_amount set: ' . ($globalSet ? 'YES' : 'NO'));

				if ( $globalSet || $applyTaxes || $booking ) {
					$rules = TechXelaLatePointDynamicPricingRulesHelper::getFormBlocksArr();
					if (defined('WP_DEBUG') && WP_DEBUG) error_log('Total pricing rules found: ' . count($rules));

					foreach ( $rules as $ruleId => $rule ) {
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('--- Checking Rule ID: ' . $ruleId . ' ---');

						$modifierTarget = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_target', 'booking' );
						$applyToFull = TechXelaLatePointDynamicPricingRulesHelper::isOn( $rule, 'apply_to_full_amount' );
						$satisfied = TechXelaLatePointDynamicPricingRulesHelper::isFormBlockSatisfied( $rule, $booking );

						if (defined('WP_DEBUG') && WP_DEBUG) error_log('Rule Name: ' . TechXelaLatePointDynamicPricingRulesHelper::getSettingValue($rule, 'name', 'Unnamed'));
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('Modifier Target: ' . $modifierTarget . ' (need: booking)');
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('Apply to Full Amount: ' . ($applyToFull ? 'YES' : 'NO'));
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('Rule Satisfied: ' . ($satisfied ? 'YES' : 'NO'));

						if ( $modifierTarget == 'booking' && $applyToFull && $satisfied ) {
							if (defined('WP_DEBUG') && WP_DEBUG) error_log('✓ RULE MATCHES! Applying modifier...');
							$beforeAmount = $amount;
							self::modifyAmount( $amount, $rule, $booking );
							if (defined('WP_DEBUG') && WP_DEBUG) error_log('Amount changed from ' . $beforeAmount . ' to ' . $amount);
						} else {
							if (defined('WP_DEBUG') && WP_DEBUG) error_log('✗ Rule does not match');
						}
					}
				} else {
					unset_tx_lp_global( 'dynamic_pricing_calculating_booking_amount' );
					if (defined('WP_DEBUG') && WP_DEBUG) error_log('Conditions not met, unsetting global');
				}
			}

			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Final amount: ' . $amount);
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('=== END fullAmount ===');
			return $amount;
		}

		public static function depositAmountForService( $amount, $booking, $applyCoupons = null ): float {

			if ( TechXelaLatePointLicenseHelper::isLicenseActive( TECHXELA_LATEPOINT_DYNAMIC_PRICING_PRODUCT_ID ) ) {
				if ( isset_tx_lp_global( 'dynamic_pricing_calculating_service_amount' ) || $booking ) {
					foreach ( TechXelaLatePointDynamicPricingRulesHelper::getFormBlocksArr() as $ruleId => $rule ) {
						if ( TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_target', 'booking' ) == 'service' &&
						     TechXelaLatePointDynamicPricingRulesHelper::isOn( $rule, 'apply_to_deposit_amount' ) &&
						     TechXelaLatePointDynamicPricingRulesHelper::isFormBlockSatisfied( $rule, $booking ) ) {
							self::modifyAmount( $amount, $rule, $booking );
						}
					}
				} else {
					unset_tx_lp_global( 'dynamic_pricing_calculating_service_amount' );
				}
			}

			return $amount;
		}

		public static function depositAmount( $amount, $booking, $applyCoupons = null ): float {

			if ( TechXelaLatePointLicenseHelper::isLicenseActive( TECHXELA_LATEPOINT_DYNAMIC_PRICING_PRODUCT_ID ) ) {
				if ( isset_tx_lp_global( 'dynamic_pricing_calculating_booking_amount' ) || $booking ) {
					foreach ( TechXelaLatePointDynamicPricingRulesHelper::getFormBlocksArr() as $ruleId => $rule ) {
						if ( TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_target', 'booking' ) == 'booking' &&
						     TechXelaLatePointDynamicPricingRulesHelper::isOn( $rule, 'apply_to_deposit_amount' ) &&
						     TechXelaLatePointDynamicPricingRulesHelper::isFormBlockSatisfied( $rule, $booking ) ) {
							self::modifyAmount( $amount, $rule, $booking );
						}
					}
				} else {
					unset_tx_lp_global( 'dynamic_pricing_calculating_booking_amount' );
				}
			}

			return $amount;
		}

		public static function modifyAmount( &$amount, $rule, OsBookingModel $booking ) {
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('--- modifyAmount called ---');

			$modifier       = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier' );
			$modifierAmount = floatval( TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_amount' ) );
			$modifierType = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_type' );

			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Modifier: ' . $modifier);
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Modifier Amount: ' . $modifierAmount);
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Modifier Type: ' . $modifierType);

			if ( $modifierType == 'percent' ) {
				$modifierAmount = $amount * ( $modifierAmount / 100 );
				if (defined('WP_DEBUG') && WP_DEBUG) error_log('Calculated percentage amount: ' . $modifierAmount);
			}

			$multiplier = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_multiplier' );
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Multiplier: ' . ($multiplier ? $multiplier : 'none'));

			if ( ! empty( $multiplier ) ) {
				switch ( $multiplier ) {
					case 'booking__total_attendies':
						if ( $booking->total_attendies ) {
							$oldAmount = $modifierAmount;
							$modifierAmount *= $booking->total_attendies;
							if (defined('WP_DEBUG') && WP_DEBUG) error_log('Applied attendies multiplier: ' . $oldAmount . ' * ' . $booking->total_attendies . ' = ' . $modifierAmount);
						}
				}
			}

			$originalAmount = $amount;
			if ( $modifier == '+' ) {
				$amount += $modifierAmount;
				if (defined('WP_DEBUG') && WP_DEBUG) error_log('Adding: ' . $originalAmount . ' + ' . $modifierAmount . ' = ' . $amount);
			} else {
				$amount -= $modifierAmount;
				if (defined('WP_DEBUG') && WP_DEBUG) error_log('Subtracting: ' . $originalAmount . ' - ' . $modifierAmount . ' = ' . $amount);
			}
		}

	}

}
