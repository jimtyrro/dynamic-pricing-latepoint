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

if ( ! class_exists( 'TechXelaLatePointDynamicPricingBookingHelper' ) ) {

	final class TechXelaLatePointDynamicPricingBookingHelper {
		public static function priceBreakdownRows( $rows, $booking, $rowsToHide ): array {
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('=== PRICE BREAKDOWN DEBUG ===');
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Rows received: ' . print_r(array_keys($rows), true));

			// CRITICAL FIX: Handle both cart and booking objects
			// LatePoint 5.2.4+ cart hook passes OsCartModel with potentially multiple items
			$bookings = [];
			if ( is_a( $booking, 'OsCartModel' ) ) {
				if (defined('WP_DEBUG') && WP_DEBUG) error_log('Received OsCartModel, extracting bookings from all cart items...');
				$cart = $booking;
				$items = $cart->get_items();
				if ( empty( $items ) ) {
					if (defined('WP_DEBUG') && WP_DEBUG) error_log('Cart has no items, returning rows unchanged');
					return $rows;
				}
				// Extract booking from each cart item
				foreach ( $items as $index => $cartItem ) {
					$bookingObj = $cartItem->build_original_object_from_item_data();
					if ( is_a( $bookingObj, 'OsBookingModel' ) ) {
						$bookings[] = $bookingObj;
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('Extracted booking ' . ($index + 1) . ' from cart');
					}
				}
				if (defined('WP_DEBUG') && WP_DEBUG) error_log('Total bookings extracted: ' . count($bookings));
			} else {
				// Single booking object (old LatePoint 4.9.x)
				if ( is_a( $booking, 'OsBookingModel' ) ) {
					$bookings[] = $booking;
					if (defined('WP_DEBUG') && WP_DEBUG) error_log('Single booking object received (old system)');
				}
			}

			// Verify we have at least one booking
			if ( empty( $bookings ) ) {
				if (defined('WP_DEBUG') && WP_DEBUG) error_log('ERROR: No valid bookings found');
				return $rows;
			}

			if ( TechXelaLatePointLicenseHelper::isLicenseActive( TECHXELA_LATEPOINT_DYNAMIC_PRICING_PRODUCT_ID ) ) {
				$priceModifiersRowBeforeSubtotal = [ 'items' => [] ];
				$priceModifiersRowAfterSubtotal  = [
					'heading' => esc_html_tx__( 'Price Modifiers', 'dynamic-pricing-latepoint' ),
					'items'   => []
				];

				$rules = TechXelaLatePointDynamicPricingRulesHelper::getFormBlocksArr();
				if (defined('WP_DEBUG') && WP_DEBUG) error_log('Total pricing rules: ' . count($rules));

				// Process rules for EACH booking in the cart
				foreach ( $bookings as $bookingIndex => $currentBooking ) {
					if (defined('WP_DEBUG') && WP_DEBUG) error_log('--- Processing booking ' . ($bookingIndex + 1) . ' of ' . count($bookings) . ' ---');

					// Get service name for this booking to make modifiers more descriptive
					$serviceName = '';
					if ( isset( $currentBooking->service ) && $currentBooking->service ) {
						$serviceName = $currentBooking->service->name;
					} elseif ( isset( $currentBooking->service_id ) ) {
						$service = new OsServiceModel( $currentBooking->service_id );
						if ( $service ) {
							$serviceName = $service->name;
						}
					}

					foreach ( $rules as $ruleId => $rule ) {
						$showOnSummary = TechXelaLatePointDynamicPricingRulesHelper::isOn( $rule, 'show_on_summary' );
						$satisfied = TechXelaLatePointDynamicPricingRulesHelper::isFormBlockSatisfied( $rule, $currentBooking );

						$ruleName = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue($rule, 'name', 'Unnamed');
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('  Rule: ' . $ruleName);
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('    Show on summary: ' . ($showOnSummary ? 'YES' : 'NO'));
						if (defined('WP_DEBUG') && WP_DEBUG) error_log('    Satisfied: ' . ($satisfied ? 'YES' : 'NO'));

						if ( $showOnSummary && $satisfied ) {
							$modifier       = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier' );
							$modifierAmount = OsMoneyHelper::format_price(
								(float) TechXelaLatePointDynamicPricingRulesHelper::getSettingValue(
									$rule,
									'modifier_amount'
								),
								false
							);
							$modifierType   = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_type' );
							$ruleAmount     = self::calculateRuleAmountForTarget( $rule, $currentBooking );

							if (defined('WP_DEBUG') && WP_DEBUG) error_log('    Rule amount calculated: ' . $ruleAmount);

							if ( $ruleAmount > 0 ) {
								$showModifier = TechXelaLatePointDynamicPricingRulesHelper::isOn( $rule, 'show_modifier_on_summary' );
								$label        = ( $modifierType == 'fixed' ) ? $ruleName : ( $ruleName . ( $showModifier ? " ($modifier$modifierAmount%)" : '' ) );

								// Add service name to label if multiple bookings to distinguish them
								if ( count($bookings) > 1 && ! empty( $serviceName ) ) {
									$label = esc_html( $serviceName ) . ': ' . $label;
								}

								$multiplier = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_multiplier' );
								if ( ! empty( $multiplier ) ) {
									switch ( $multiplier ) {
										case 'booking__total_attendies':
											if ( $currentBooking->total_attendies ) {
												$modifierAmountMoney = OsMoneyHelper::format_price(
													(float) TechXelaLatePointDynamicPricingRulesHelper::getSettingValue(
														$rule,
														'modifier_amount'
													)
												);
												$attendeesLabel      = sprintf( _n( '%s person', '%s people', $currentBooking->total_attendies, 'latepoint-group-bookings' ), $currentBooking->total_attendies );
												$baseLabel           = ( $modifierType == 'fixed' ) ? "$ruleName ($modifierAmountMoney × $attendeesLabel)" : "{$ruleName} ($modifier$modifierAmount% × $attendeesLabel)";

												// Add service name if multiple bookings
												if ( count($bookings) > 1 && ! empty( $serviceName ) ) {
													$label = esc_html( $serviceName ) . ': ' . $baseLabel;
												} else {
													$label = $baseLabel;
												}
											}
									}
								}

								$row = [
									'label'     => $label,
									'style'     => 'strong',
									'raw_value' => OsMoneyHelper::pad_to_db_format( $ruleAmount ),
									'value'     => OsMoneyHelper::format_price( $ruleAmount, true, false )
								];

								if ( $modifier == '-' ) {
									$row['raw_value'] = - $row['raw_value'];
									$row['value']     = "-{$row['value']}";
									$row['type']      = 'credit';
								}

								if (defined('WP_DEBUG') && WP_DEBUG) error_log('    Adding row: ' . $label . ' = ' . $row['value']);

								$modifierTarget = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_target', 'booking' );
								switch ( $modifierTarget ) {
									case 'booking':
										$priceModifiersRowAfterSubtotal['items'][] = $row;
										if (defined('WP_DEBUG') && WP_DEBUG) error_log('    Added to AFTER subtotal');
										break;
									case 'service':
										$priceModifiersRowBeforeSubtotal['items'][] = $row;
										if (defined('WP_DEBUG') && WP_DEBUG) error_log('    Added to BEFORE subtotal');
								}
							}
						}
					}
				}

				if ( ! empty( $priceModifiersRowBeforeSubtotal['items'] ) ) {
					$rows['before_subtotal'][] = $priceModifiersRowBeforeSubtotal;
					if (defined('WP_DEBUG') && WP_DEBUG) error_log('Added ' . count($priceModifiersRowBeforeSubtotal['items']) . ' total modifier items BEFORE subtotal');
				}

				if ( ! empty( $priceModifiersRowAfterSubtotal['items'] ) ) {
					$rows['after_subtotal'][] = $priceModifiersRowAfterSubtotal;
					if (defined('WP_DEBUG') && WP_DEBUG) error_log('Added ' . count($priceModifiersRowAfterSubtotal['items']) . ' total modifier items AFTER subtotal');

				// CRITICAL FIX: Adjust subtotal to exclude booking-level modifiers that appear after subtotal
				// The subtotal currently includes the modified price, but it should only show base service price
				if ( isset( $rows['subtotal'] ) ) {
					// Calculate total modifier amount that goes after subtotal
					$afterSubtotalModifierTotal = 0;
					foreach ( $priceModifiersRowAfterSubtotal['items'] as $item ) {
						$afterSubtotalModifierTotal += floatval( $item['raw_value'] );
					}

					if (defined('WP_DEBUG') && WP_DEBUG) error_log('After-subtotal modifier total: ' . $afterSubtotalModifierTotal);
					if (defined('WP_DEBUG') && WP_DEBUG) error_log('Original subtotal: ' . $rows['subtotal']['raw_value']);

					// Subtract the after-subtotal modifiers from the subtotal to show base price
					if ( $afterSubtotalModifierTotal != 0 ) {
						$originalSubtotal = floatval( $rows['subtotal']['raw_value'] );
						$correctedSubtotal = $originalSubtotal - $afterSubtotalModifierTotal;

						$rows['subtotal']['raw_value'] = number_format( $correctedSubtotal, 4, '.', '' );
						$rows['subtotal']['value'] = OsMoneyHelper::format_price( $correctedSubtotal, true, false );

						if (defined('WP_DEBUG') && WP_DEBUG) error_log('Corrected subtotal: ' . $rows['subtotal']['raw_value']);
					}
				}
				}
			}

			if (defined('WP_DEBUG') && WP_DEBUG) error_log('Final rows structure: ' . print_r(array_keys($rows), true));
			if (defined('WP_DEBUG') && WP_DEBUG) error_log('=== END PRICE BREAKDOWN ===');
			return $rows;
		}

		public static function priceBreakdownServiceRowItem( $serviceRowItem, $booking ) {
			set_tx_lp_global( 'dynamic_pricing_calculating_service_amount', true );

			return $serviceRowItem;
		}

		public static function calculateRuleAmountForTarget( array $rule, OsBookingModel $booking ) {
			$ruleAmount     = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_amount' );
			$modifierTarget = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_target', 'booking' );

			// Get the base amount to calculate percentage from
			if ( $modifierTarget == 'booking' ) {
				// For booking-level modifiers, use the full booking amount
				unset_tx_lp_global( 'dynamic_pricing_calculating_booking_amount' );
				$targetAmount = OsBookingHelper::calculate_full_amount_for_booking( $booking );
				set_tx_lp_global( 'dynamic_pricing_calculating_booking_amount', true );
			} else {
				// For service-level modifiers, use the service amount only
				unset_tx_lp_global( 'dynamic_pricing_calculating_service_amount' );
				$targetAmount = OsBookingHelper::calculate_full_amount_for_service( $booking );
				set_tx_lp_global( 'dynamic_pricing_calculating_service_amount', true );
			}

			// Convert percentage to actual amount
			if ( TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_type' ) == 'percent' ) {
				$ruleAmount = $targetAmount * ( floatval( $ruleAmount ) / 100 );
			}

			// Apply multiplier if set (e.g., for group bookings)
			$multiplier = TechXelaLatePointDynamicPricingRulesHelper::getSettingValue( $rule, 'modifier_multiplier' );
			if ( ! empty( $multiplier ) ) {
				switch ( $multiplier ) {
					case 'booking__total_attendies':
						if ( $booking->total_attendies ) {
							$ruleAmount *= $booking->total_attendies;
						}
				}
			}

			return $ruleAmount;
		}

	}

}
