<?php
/*
 * LatePoint Addons Framework
 * Copyright (c) 2021-2023 TechXela (https://codecanyon.net/user/tech-xela). All Rights Reserved.
 *
 * LICENSE
 * -------
 * This software is furnished under license(s) and may be used and copied only in accordance with the terms of such
 * license(s) along with the inclusion of the above copyright notice. If you purchased an item through CodeCanyon, in
 * which this software came included, please read the full license(s) at: https://codecanyon.net/licenses/standard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'TechXelaLatePointModelHelper' ) ) :

	class TechXelaLatePointModelHelper {
		public static function modelSave( OsModel $model ) {
			if ( is_a( $model, 'OsSettingsModel' ) ) {
				do_action( 'techxela_latepoint_settings_saved', $model );
			} elseif ( is_a( $model, 'OsTransactionModel' ) &&
			           ( OsRouterHelper::get_request_param( 'route_name' ) == 'bookings__update' ||
			             isset_tx_lp_global( 'transaction_will_update' ) ) ) {
				do_action( 'techxela_latepoint_transaction_updated', $model );
			} elseif ( is_a( $model, 'OsCouponModel' ) ) {
				if ( in_array( OsRouterHelper::get_request_param( 'route_name' ), [
						'coupons__create',
						'tech_xela_bulk_imports_coupons__import',
					] ) || isset_tx_lp_global( 'coupon_is_new_record' ) ) {

					do_action( 'techxela_latepoint_coupon_created', $model );

					unset_tx_lp_global( 'coupon_is_new_record' );
				} else {
					do_action( 'techxela_latepoint_coupon_updated', $model );
				}
			}
		}

		public static function modelWillBeDeleted( $model ) {
			$modelRootClassName = self::getOsModelRootClassName( $model, true, true );
			if ( $modelRootClassName ) {
				do_action( "techxela_latepoint_{$modelRootClassName}_will_be_deleted", $model );
			}
		}

		public static function modelDeleted( $model, int $modelId ) {
			$modelRootClassName = self::getOsModelRootClassName( $model, true, true );
			if ( $modelRootClassName ) {
				do_action( "techxela_latepoint_{$modelRootClassName}_deleted", $model, $modelId );
			}
		}

		public static function getOsModelRootClassName( $model, bool $toLowerCase = false, bool $toCamelCase = false ) {
			if ( is_object( $model ) && is_a( $model, '\OsModel' ) ) {
				$className = get_class( $model );

				if ( $className !== 'OsModel' ) {
					if ( str_starts_with( $className, 'Os' ) && str_ends_with( $className, 'Model' ) ) {
						$className = substr( substr( $className, 2 ), 0, - 5 );
					}

					if ( $toCamelCase ) {
						$className = preg_replace(
							'/(?:\d++|[A-Za-z]?[a-z]++)\K(?!$)/',
							'_',
							$className
						);
					}

					if ( $toLowerCase ) {
						$className = strtolower( $className );
					}

					return $className;
				}
			}

			return false;
		}

		public static function getTxModelRootClassName( $model, bool $toLowerCase = false, bool $toCamelCase = false ) {
			if ( is_object( $model ) && is_a( $model, '\TechXelaLatePointModel' ) ) {
				$className = get_class( $model );

				if ( $className !== 'TechXelaLatePointModel' ) {
					if ( str_ends_with( $className, 'Model' ) ) {
						$className = substr( $className, 0, - 5 );
					}

					if ( $toCamelCase ) {
						$className = preg_replace(
							'/(?:\d++|[A-Za-z]?[a-z]++)\K(?!$)/',
							'_',
							$className
						);
					}

					if ( $toLowerCase ) {
						$className = strtolower( $className );
					}

					return $className;
				}
			}

			return false;
		}

		public static function getOsModelChildClasses(): array {
			$children = [];

			foreach ( get_declared_classes() as $class ) {
				if ( is_subclass_of( $class, '\OsModel' ) ) {
					$children[] = $class;
				}
			}

			return $children;
		}

		public static function modelOptionsForMultiSelect( $options, $property ) {
			switch ( $property ) {
				case 'agent':
				case 'OsAgentModel':
					$options = [];
					$agents  = ( new OsAgentModel() )->order_by( 'id desc' )->get_results_as_models();
					foreach ( $agents as $agent ) {
						$options[] = [ 'value' => $agent->id, 'label' => "$agent->full_name (#$agent->id)" ];
					}
					break;
				case 'customer':
				case 'OsCustomerModel':
					$options   = [];
					$customers = ( new OsCustomerModel() )->order_by( 'id desc' )->get_results_as_models();
					foreach ( $customers as $customer ) {
						$options[] = [ 'value' => $customer->id, 'label' => "$customer->full_name (#$customer->id)" ];
					}
					break;
			}

			return $options;
		}

		public static function cloneOsModel( OsModel $specimen, OsModel $clone ): OsModel {
			foreach ( get_object_vars( $specimen ) as $var => $value ) {
				$clone->$var = $value;
			}

			return $clone;
		}

		public static function directUpdateModelAttributes( OsModel $model, array $attributes ) {
			global $wpdb;

			return $wpdb->update( $model->table_name, $attributes, [ 'id' => $model->id ] );
		}
	}

endif;
