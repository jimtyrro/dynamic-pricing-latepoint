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

if ( ! class_exists( 'TechXelaLatePointDynamicPricingRulesHelper' ) ) :

	final class TechXelaLatePointDynamicPricingRulesHelper {
		use \TechXela\LatePointAddons\Traits\FormBlock\IsConditionalFormBlockHelper;
		use \TechXela\LatePointAddons\Traits\FormBlock\IsRecurringFormBlockHelper;

		public static $settingsHelperClass = TechXelaLatePointDynamicPricingSettingsHelper::class;

		public static $formBlocksAreDraggable = false;
		public static $formBlocksDbOptionName = 'rules';
		public static $formBlocksFormArrName = 'rules';
		public static $formBlockSettingPrefix = 'rul';

		public static $formBlockConditionSettingPrefix = 'rcond';
		public static $formBlockObjectPrefix = 'rule';
		public static $formBlocksRouteController = 'tech_xela_dynamic_pricing_rules';

		public static function customFormBlockProperties(): array {
			return [
				'modifier'                => '+',
				'modifier_type'           => 'fixed',
				'modifier_amount'         => '0.00',
				'modifier_target'         => 'booking',
				'apply_to_full_amount'    => 'on',
				'apply_to_deposit_amount' => 'off',
				'show_on_summary'         => 'on',
				'show_modifier_on_summary' => 'on',
			];
		}

		public static function getFormBlockType( array $formBlock ): string {
			$modifierType   = self::getSettingValue( $formBlock, 'modifier_type' );
			$modifierAmount = self::getSettingValue( $formBlock, 'modifier_amount' );

			return self::getSettingValue( $formBlock, 'modifier' ) .
			       ( ( $modifierType == 'fixed' ) ?
				       OsMoneyHelper::format_price( $modifierAmount, true, false ) :
				       $modifierAmount ) . ( ( $modifierType == 'percent' ) ? '%' : '' );
		}

		public static function afterFormBlockNameSection( array $formBlock ): void {
			?>
            <div class="sub-section-row">
                <div class="sub-section-label">
                    <h3><?php esc_html_tx_e( 'Modifier', 'dynamic-pricing-latepoint' ) ?></h3>
                </div>
                <div class="sub-section-content form-block-modifier-components">
                    <div class="os-row">
                        <div class="os-col-lg-4">
							<?php echo OsFormHelper::select_field( self::generateSettingName( $formBlock, 'modifier' ), false,
								self::getModifiers(),
								self::getSettingValue( $formBlock, 'modifier' ), [ 'class' => 'form-block-modifier' ] ); ?>
                        </div>
                        <div class="os-col-lg-4">
							<?php echo OsFormHelper::text_field( self::generateSettingName( $formBlock, 'modifier_amount' ),
								esc_attr_tx__( 'By', 'dynamic-pricing-latepoint' ) . ':',
								OsMoneyHelper::to_money_field_format( self::getSettingValue( $formBlock, 'modifier_amount' ) ),
								[ 'class' => 'form-block-modifier-amount' . ( self::getSettingValue( $formBlock, 'modifier_type' ) == 'fixed' ? ' os-mask-money' : ' os-mask-percent' ) ] ); ?>
                        </div>
                        <div class="os-col-lg-4">
							<?php echo OsFormHelper::select_field( self::generateSettingName( $formBlock, 'modifier_type' ), false,
								self::getModifierTypes(),
								self::getSettingValue( $formBlock, 'modifier_type' ), [ 'class' => 'form-block-modifier-type' ] ); ?>
                        </div>
                    </div>
                    <div class="os-row mt-2">
                        <div class="os-col-lg-4">
							<?php echo OsFormHelper::select_field(
								self::generateSettingName( $formBlock, 'modifier_target' ),
								esc_html_tx__( 'Apply modifier to', 'dynamic-pricing-latepoint' ) . ':',
								self::getModifierTargets(),
								self::getSettingValue( $formBlock, 'modifier_target', array_keys( self::getModifierTargets() )[0] ),
								[ 'class' => 'form-block-modifier-target' ]
							); ?>
                        </div>
                        <div class="os-col-lg-4 form-block-modifier-apply-to-full-amount">
							<?php echo OsFormHelper::toggler_field(
								self::generateSettingName( $formBlock, 'apply_to_full_amount' ),
								esc_html_tx__( 'Apply modifier to full amount', 'dynamic-pricing-latepoint' ),
								self::isOn( $formBlock, 'apply_to_full_amount' ),
								false,
								false,
								[ 'sub_label' => esc_html_tx__( 'Apply increase/decrease to full booking/service price', 'dynamic-pricing-latepoint' ) ]
							); ?>
                        </div>
                        <div class="os-col-lg-4 form-block-modifier-apply-to-deposit-amount">
							<?php echo OsFormHelper::toggler_field(
								self::generateSettingName( $formBlock, 'apply_to_deposit_amount' ),
								esc_html_tx__( 'Apply modifier to deposit amount', 'dynamic-pricing-latepoint' ),
								self::isOn( $formBlock, 'apply_to_deposit_amount' ),
								false,
								false,
								[ 'sub_label' => esc_html_tx__( 'Apply increase/decrease to booking/service deposit amount', 'dynamic-pricing-latepoint' ) ]
							); ?>
                        </div>
                    </div>

                    <div class="os-row">
                        <div class="os-col-lg-4">
							<?php echo OsFormHelper::select_field(
								self::generateSettingName( $formBlock, 'modifier_multiplier' ),
								esc_html_tx__( 'Multiply modifier by', 'dynamic-pricing-latepoint' ) . ':',
								self::getModifierMultipliers(),
								self::getSettingValue( $formBlock, 'modifier_multiplier', array_keys( self::getModifierMultipliers() )[0] ),
								[ 'class' => 'form-block-modifier-multiplier' ]
							); ?>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}

		public static function beforeFormBlockEnabledSection( array $formBlock ): void {
			?>
            <div class="sub-section-row">
                <div class="sub-section-label">
                    <h3><?php esc_html_tx_e( 'Appearance', 'dynamic-pricing-latepoint' ) ?></h3>
                </div>
                <div class="sub-section-content">
					<?= OsFormHelper::toggler_field(
						self::generateSettingName( $formBlock, 'show_on_summary' ),
						esc_html_tx__( 'Show rule name on booking summary and confirmation step', 'dynamic-pricing-latepoint' ),
						( $formBlock['show_on_summary'] == 'on' ),
						$formBlock['id'] . '_showOnSummarySettings'
					)
					?>
                    <div id="<?= "{$formBlock['id']}_showOnSummarySettings" ?>"
                         style="display:<?= ( $formBlock['show_on_summary'] == 'on' ) ? 'block' : 'none' ?>;">
						<?= OsFormHelper::toggler_field(
							self::generateSettingName( $formBlock, 'show_modifier_on_summary' ),
							esc_html_tx__( 'Display modifier with rule name', 'dynamic-pricing-latepoint' ),
							( $formBlock['show_modifier_on_summary'] == 'on' )
						)
						?>
                    </div>
                </div>
            </div>
			<?php
		}

		protected static function getValidationErrors( $formBlock, $errors ): array {
			if ( ! \TechXelaLatePointLicenseHelper::isLicenseActive( TECHXELA_LATEPOINT_DYNAMIC_PRICING_PRODUCT_ID ) ) {
				$errors[] = esc_html_tx__( 'Please register your license to perform that action.', 'latepoint-addons' );

				return [ $formBlock, $errors ];
			}

			if ( ! in_array( $formBlock['modifier'], array_keys( self::getModifiers() ) ) ) {
				$formBlock['modifier'] = array_key_first( self::getModifiers() );
			}

			if ( ! in_array( $formBlock['modifier_type'], array_keys( self::getModifierTypes() ) ) ) {
				$formBlock['modifier_type'] = array_key_first( self::getModifierTypes() );
			}

			if ( ! in_array( $formBlock['modifier_multiplier'], array_keys( self::getModifierMultipliers() ) ) ) {
				$formBlock['modifier_multiplier'] = array_key_first( self::getModifierMultipliers() );
			}

			if ( $formBlock['modifier_type'] == 'fixed' ) {
				$formBlock['modifier_amount'] = OsMoneyHelper::convert_amount_from_money_input_to_db_format( $formBlock['modifier_amount'] );
			} else {
				$formBlock['modifier_amount'] = OsMoneyHelper::convert_value_from_percent_input_to_db_format( $formBlock['modifier_amount'] );
			}

			if ( ! is_numeric( $formBlock['modifier_amount'] ) || floatval( $formBlock['modifier_amount'] ) < 0 ) {
				$errors[] = esc_html__( 'Modifier Amount must be a number greater than or equal to 0', 'dynamic-pricing-latepoint' );
			}

			return [ $formBlock, $errors ];
		}

		protected static function isCustomFormBlockSatisfied( array $formBlock, \OsBookingModel $bookingObject ): bool {
			if ( $bookingObject->payment_portion == LATEPOINT_PAYMENT_PORTION_DEPOSIT && self::isOn( $formBlock, 'apply_to_deposit_amount' ) ) {
				return true;
			} elseif ( self::isOn( $formBlock, 'apply_to_full_amount' ) ) {
				return true;
			}

			return false;
		}

		public static function getModifiers(): array {
			return [
				'+' => esc_html_tx__( 'Increase', 'dynamic-pricing-latepoint' ),
				'-' => esc_html_tx__( 'Decrease', 'dynamic-pricing-latepoint' )
			];
		}

		public static function getModifierTypes(): array {
			return [
				'fixed'   => esc_html_tx__( 'Fixed Amount', 'dynamic-pricing-latepoint' ),
				'percent' => esc_html_tx__( 'Percent', 'dynamic-pricing-latepoint' )
			];
		}

		public static function getModifierTargets(): array {
			return [
				'booking' => esc_html__( 'Booking', 'latepoint' ),
				'service' => esc_html__( 'Service', 'latepoint' )
			];
		}

		public static function getModifierMultipliers(): array {
			return [
				''                         => esc_html_tx__( 'Do not multiply', 'dynamic-pricing-latepoint' ),
				'booking__total_attendies' => join( ' ', [
					esc_html__( 'Booking', 'latepoint' ),
					esc_html__( 'Total Attendees', 'latepoint-addons' )
				] ),
			];
		}
	}

endif;