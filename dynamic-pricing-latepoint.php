<?php
/**
 * Plugin Name: Dynamic Pricing for LatePoint
 * Plugin URI:  https://1.envato.market/dynamic-pricing-latepoint
 * Description: LatePoint add-on which allows for dynamic service/booking prices, based on one or more conditions.
 * Version:     1.0.3.5
 * Author:      TechXela
 * Author URI:  http://techxela.com
 * Text Domain: dynamic-pricing-latepoint
 * Domain Path: /languages
 */

/*
 * Dynamic Pricing for LatePoint
 * Copyright (c) 2023 TechXela (https://codecanyon.net/user/tech-xela). All Rights Reserved.
 *
 * IMPROVEMENTS & LATEPOINT 5.2.4+ COMPATIBILITY
 * -------
 * Updated and improved for LatePoint 5.2.4+ by Dmitriy Salynin (https://5notedesign.com)
 * Fixes and enhancements: Cart support, multiple services, subtotal calculation, backward compatibility
 *
 * LICENSE
 * -------
 * This software is furnished under license(s) and may be used and copied only in accordance with the terms of such
 * license(s) along with the inclusion of the above copyright notice. If you purchased this software through CodeCanyon,
 * please read the full license(s) at: https://codecanyon.net/licenses/standard
 *
 * UPGRADE NOTES (v1.0.3)
 * -------
 * - Added support for LatePoint 5.2.4+ Order-based pricing system
 * - Maintains full backward compatibility with LatePoint 4.9.x
 * - New hooks for order and order intent pricing
 * - All existing functionality preserved
 *
 * CRITICAL FIX (v1.0.3.1)
 * -------
 * - Fixed pricing rule application issue where custom pricing was not displayed on frontend
 * - Root cause: Plugin was hooking into non-existent WordPress filters
 * - Solution: Corrected hook names to match actual LatePoint 5.2.4+ filter names
 * - Fixed: latepoint_calculate_deposit_amount_for_booking (doesn't exist) → latepoint_deposit_amount_for_service (exists)
 * - Removed: Non-existent order hooks and their helper files (order_helper.php, order_intent_pricing_helper.php)
 * - Order pricing now inherits from booking calculations automatically - no separate hooks needed
 * - Pricing rules now properly evaluate date conditions and apply modifiers on frontend
 *
 * CRITICAL FIX #2 (v1.0.3.2)
 * -------
 * - Fixed cost breakdown not displaying pricing modifiers
 * - Root cause: LatePoint 5.2.4+ uses cart-based hook 'latepoint_cart_price_breakdown_rows' instead of old 'latepoint_price_breakdown_rows'
 * - Solution: Added hook registration for 'latepoint_cart_price_breakdown_rows' while maintaining backward compatibility
 * - Cost breakdown now properly displays: Service price, Subtotal, Price Modifiers section, and Total
 *
 * CRITICAL FIX #3 (v1.0.3.3)
 * -------
 * - Fixed 500 Internal Server Error causing booking form to fail
 * - Root cause: Cart hook passes OsCartModel but priceBreakdownRows() expected OsBookingModel, causing TypeError
 * - Solution: Added object type detection in priceBreakdownRows() to extract booking from cart when needed
 * - Function now handles both OsCartModel (new 5.2.4+ cart hook) and OsBookingModel (old 4.9.x booking hook)
 * - Booking form now loads correctly and displays cost breakdown properly
 *
 * CRITICAL FIX #4 (v1.0.3.4)
 * -------
 * - Fixed multiple cart items not showing all pricing modifiers in cost breakdown
 * - Root cause: Code was only processing first cart item ($items[0]), ignoring additional services
 * - Solution: Loop through ALL cart items and extract booking from each, process pricing rules for all bookings
 * - Added service name prefix to modifier labels when multiple bookings exist for clarity
 * - Cost breakdown now shows pricing modifiers for ALL services in cart, not just the first one
 *
 * CRITICAL FIX #5 (v1.0.3.4 - Subtotal Correction)
 * -------
 * - Fixed subtotal showing modified price (service + modifier) instead of base service price
 * - Root cause: LatePoint calculates subtotal AFTER pricing modifiers have been applied to booking
 * - Solution: Subtract after-subtotal modifier amounts from subtotal to display only base service prices
 * - Result: Cost breakdown now shows correct flow: Base price → Subtotal → Modifiers → Total
 * - Example: Service $501 → Subtotal $501 (was $1,008) → Modifier +$507 → Total $1,008
 *
 * SECURITY & PERFORMANCE OPTIMIZATION (v1.0.3.5)
 * -------
 * - SECURITY FIX: Added esc_html() escaping for service names in cost breakdown (XSS prevention)
 * - Vulnerability: Service names were output without HTML escaping (lines 113, 132 in booking_helper.php)
 * - Impact: Prevents potential XSS attacks if service name contains malicious HTML/JavaScript
 * - PERFORMANCE: Wrapped all 72 error_log() statements with WP_DEBUG conditional checks
 * - Impact: Zero debug logging overhead when WP_DEBUG=false (typical production environment)
 * - Files optimized: booking_helper.php (24 statements), money_helper.php (48 statements)
 * - Compatible with: LatePoint 5.2.5+
 * - No logic changes - all functionality preserved and tested
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );

if ( ! class_exists( 'TechXelaLatePointDynamicPricing' ) ):

	/**
	 * Main Addon Class.
	 *
	 */

	final class TechXelaLatePointDynamicPricing extends \TechXela\LatePointAddons\CoreAddon {
		public $productId = '451FCA7F';
		public $version = '1.0.3.1';
		public $dbVersion = '1.0.5';
		public $addonSlug = 'dynamic-pricing';
		public $addonName = 'dynamic-pricing-latepoint';
		public $settingsRouteName = 'tech_xela_late_point_dynamic_pricing_admin__settings';

		protected function defineConstants() {
			$this->define( 'TECHXELA_LATEPOINT_DYNAMIC_PRICING_ABSPATH', dirname( __FILE__ ) . '/' );
			$this->define( 'TECHXELA_LATEPOINT_DYNAMIC_PRICING_VIEWS_ABSPATH', TECHXELA_LATEPOINT_DYNAMIC_PRICING_ABSPATH . 'lib/views/' );
			$this->define( 'TECHXELA_LATEPOINT_DYNAMIC_PRICING_PRODUCT_ID', $this->productId );
			$this->define( 'TECHXELA_LATEPOINT_DYNAMIC_PRICING_DB_VERSION', $this->dbVersion );
		}

		protected function includes() {
			include_once( TECHXELA_LATEPOINT_DYNAMIC_PRICING_ABSPATH . 'lib/helpers/booking_helper.php' );
			include_once( TECHXELA_LATEPOINT_DYNAMIC_PRICING_ABSPATH . 'lib/helpers/database_helper.php' );
			include_once( TECHXELA_LATEPOINT_DYNAMIC_PRICING_ABSPATH . 'lib/helpers/menu_helper.php' );
			include_once( TECHXELA_LATEPOINT_DYNAMIC_PRICING_ABSPATH . 'lib/helpers/money_helper.php' );
			include_once( TECHXELA_LATEPOINT_DYNAMIC_PRICING_ABSPATH . 'lib/helpers/rules_helper.php' );
			include_once( TECHXELA_LATEPOINT_DYNAMIC_PRICING_ABSPATH . 'lib/helpers/settings_helper.php' );
			include_once( TECHXELA_LATEPOINT_DYNAMIC_PRICING_ABSPATH . 'lib/helpers/woo_helper.php' );

			include_once( TECHXELA_LATEPOINT_DYNAMIC_PRICING_ABSPATH . 'lib/controllers/admin_controller.php' );
			include_once( TECHXELA_LATEPOINT_DYNAMIC_PRICING_ABSPATH . 'lib/controllers/rules_controller.php' );
		}

		protected function initHooks() {
			add_filter( 'latepoint_side_menu', [ TechXelaLatePointDynamicPricingMenuHelper::class, 'sideMenu' ], 7 );

			add_filter( 'latepoint_addons_sqls', [
				TechXelaLatePointDynamicPricingDatabaseHelper::class,
				'addonsSqls'
			] );

			// CRITICAL FIX: Use BOOKING-level function for BOOKING-level hook
			// The hook latepoint_calculate_full_amount_for_booking should call fullAmount (booking-level logic)
			// not fullAmountForService (service-level logic) because rules are configured with modifier_target='booking'
			add_filter( 'latepoint_calculate_full_amount_for_booking', [
				TechXelaLatePointDynamicPricingMoneyHelper::class,
				'fullAmount'
			], 12, 2 );

			add_filter( 'latepoint_full_amount', [
				TechXelaLatePointDynamicPricingMoneyHelper::class,
				'fullAmount'
			], 12, 4 );

			add_filter( 'latepoint_deposit_amount_for_service', [
				TechXelaLatePointDynamicPricingMoneyHelper::class,
				'depositAmountForService'
			], 12, 2 );

			add_filter( 'latepoint_deposit_amount', [
				TechXelaLatePointDynamicPricingMoneyHelper::class,
				'depositAmount'
			], 12, 3 );

			add_filter( 'latepoint_price_breakdown_service_row_item', [
				TechXelaLatePointDynamicPricingBookingHelper::class,
				'priceBreakdownServiceRowItem'
			], 12, 2 );

			// COST BREAKDOWN HOOKS
			// Old LatePoint 4.9.x hook (booking-based)
			add_filter( 'latepoint_price_breakdown_rows', [
				TechXelaLatePointDynamicPricingBookingHelper::class,
				'priceBreakdownRows'
			], 12, 3 );

			// New LatePoint 5.2.4+ hook (cart-based) - CRITICAL FIX!
			add_filter( 'latepoint_cart_price_breakdown_rows', [
				TechXelaLatePointDynamicPricingBookingHelper::class,
				'priceBreakdownRows'
			], 12, 3 );

			// ORDER-BASED PRICING NOTE (LatePoint 5.2.4+):
			// The new order system calls OsBookingHelper::calculate_full_amount_for_booking()
			// which already applies our booking-level filters above. No additional order hooks needed.
			// The hooks 'latepoint_order_item_total_amount' and 'latepoint_order_total_amount' do not exist.
			// Order item pricing uses 'latepoint_order_item_full_amount_to_charge' but it's redundant since
			// it internally calls the booking calculation which already has our filters applied.

			// WOOCOMMERCE HOOKS
			add_filter( 'tx_latepoint_woocommerce_calculate_cart_item_price', [
				TechXelaLatePointDynamicPricingWooHelper::class,
				'woocommerceCalculateCartItemPrice'
			], 10, 3 );

			add_action( 'tx_latepoint_woocommerce_before_calculate_totals', [
				TechXelaLatePointDynamicPricingWooHelper::class,
				'woocommerceBeforeCalculateTotals'
			], 10, 2 );

			add_filter( 'tx_latepoint_woocommerce_get_item_data', [
				TechXelaLatePointDynamicPricingWooHelper::class,
				'woocommerceGetItemData'
			], 10, 3 );
		}

		protected function loadAdminScriptsAndStyles() {
			$this->enqueueAdminStyle();
			$this->enqueueAdminScript();

		// Enqueue passive events polyfill to fix scroll performance issues
		// and reduce browser violations on scroll-blocking event listeners
		wp_enqueue_script(
			'dynamic-pricing-latepoint-passive-events',
			plugins_url( 'public/js/passive-events-polyfill.js', __FILE__ ),
			[],
			'1.0.0',
			true // Load in footer
		);

		}

		protected function localizedVarsForAdmin( $localizedVars ) {
			$localizedVars['tx_dynamic_pricing_rule_condition_refresh_route'] = OsRouterHelper::build_route_name( 'tech_xela_dynamic_pricing_rules', 'refresh_condition' );
			$localizedVars['tx_dynamic_pricing_rule_condition_refresh_error'] = esc_html_tx__( 'Error encountered while refreshing pricing rule condition. Please contact support.', 'dynamic-pricing-latepoint' );
			$localizedVars['tx_dynamic_pricing_rule_condition_deletion_warning'] = esc_html_tx__( 'You need to have at least one condition in your conditional pricing rule.', 'dynamic-pricing-latepoint' );

			return $localizedVars;
		}
	}

endif;

techxela_latepoint_addons_maybe_init( 'TechXelaLatePointDynamicPricing' );
