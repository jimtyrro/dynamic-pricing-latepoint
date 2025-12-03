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

if ( ! class_exists( 'TechXelaLatePointDynamicPricingWooHelper' ) ) :

	final class TechXelaLatePointDynamicPricingWooHelper {

		public static function woocommerceCalculateCartItemPrice( $cartItemPrice, $cartItem, OsBookingModel $booking ) {
			if ( TechXelaLatePointLicenseHelper::isLicenseActive( TECHXELA_LATEPOINT_DYNAMIC_PRICING_PRODUCT_ID ) ) {
				$cartItemPrice = floatval( $cartItemPrice );
				foreach ( TechXelaLatePointDynamicPricingRulesHelper::getFormBlocksArr() as $ruleId => $rule ) {
					if ( TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_target', 'booking' ) == 'service' &&
					     TechXelaLatePointDynamicPricingRulesHelper::isFormBlockSatisfied( $rule, $booking ) ) {
						TechXelaLatePointDynamicPricingMoneyHelper::modifyAmount( $cartItemPrice, $rule, $booking );
					}
				}
			}

			return $cartItemPrice;
		}

		public static function woocommerceBeforeCalculateTotals( $wcCart, $cartItem ) {
			if ( TechXelaLatePointLicenseHelper::isLicenseActive( TECHXELA_LATEPOINT_DYNAMIC_PRICING_PRODUCT_ID ) ) {
				$cartItemTxMeta = $cartItem[ TECHXELA_WOOCOMMERCE_LATEPOINT_ORDER_ITEM_META_KEY ];
				OsStepsHelper::set_booking_object( $cartItemTxMeta['booking'] );
				/** @var OsBookingModel $booking */
				$booking = OsStepsHelper::get_booking_object();

				foreach ( TechXelaLatePointDynamicPricingRulesHelper::getFormBlocksArr() as $ruleId => $rule ) {
					if ( TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_target', 'booking' ) == 'booking' &&
					     TechXelaLatePointDynamicPricingRulesHelper::isFormBlockSatisfied( $rule, $booking ) ) {
						$modifier       = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier' );
						$modifierAmount = floatval( TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_amount' ) );
						if ( TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_type' ) == 'percent' ) {
							$modifierAmount = $cartItem['data']->get_price() * ( $modifierAmount / 100.00 );
						}

						$wcCart->add_fee(
							TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'name' ),
							floatval( "$modifier$modifierAmount" ),
							true
						);
					}
				}
			}
		}

		public static function woocommerceGetItemData( $itemData, $cartItem, $booking ) {
			if ( TechXelaLatePointLicenseHelper::isLicenseActive( TECHXELA_LATEPOINT_DYNAMIC_PRICING_PRODUCT_ID ) ) {
				foreach ( TechXelaLatePointDynamicPricingRulesHelper::getFormBlocksArr() as $ruleId => $rule ) {
					if ( TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_target', 'booking' ) == 'service' &&
					     TechXelaLatePointDynamicPricingRulesHelper::isFormBlockSatisfied( $rule, $booking ) ) {
						$ruleName       = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'name' );
						$modifier       = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier' );
						$modifierAmount = OsMoneyHelper::format_price(
							(float) TechXelaLatePointDynamicPricingRulesHelper::getSettingValue(
								$rule,
								'modifier_amount'
							),
							false
						);
						$modifierType   = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_type' );
						$ruleAmount     = OsMoneyHelper::format_price(
							TechXelaLatePointDynamicPricingBookingHelper::calculateRuleAmountForTarget( $rule, $booking ),
							true, false );

						$itemData[] = [
							'key'   => ( $modifierType == 'fixed' ) ? $ruleName : "{$ruleName} ($modifier$modifierAmount%)",
							'value' =>  ( $modifier == '-' ) ? "$modifier$ruleAmount" : $ruleAmount
						];
					}
				}
			}

			return $itemData;
		}
	}

endif;