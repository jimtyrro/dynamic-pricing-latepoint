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

if ( ! class_exists( 'TechXelaLatePointUpdatesHelper' ) ) :

	class TechXelaLatePointUpdatesHelper {

		public static function getListOfAddons() {
			$api = new \TechXela\LatePointAddons\Licensing\API();
			$response = $api->getListOfAddons( 'latepoint' );
			$addons = false;

			if ( ! empty( $response ) ) {
				if ( isset( $response->status ) && ! $response->status ) {
					TechXelaLatePointDebugHelper::logError( 'laf_list_of_addons', $response->message );
				} else {
					$addons = $response;

					for ( $idx = 0; $idx < count( $addons ); $idx ++ ) {
						$addon = $addons[ $idx ];
						if ( ( isset( $addon->private ) && ! empty( $addon->private ) ) &&
						     ! OsAddonsHelper::is_addon_installed( $addon->wp_plugin_path ) ) {
							unset( $addons[ $idx ] );
						}
					}
				}
			}

			return $addons;
		}

		public static function checkAddonsLatestVersion( $addons = [] ) {
			if ( empty( $addons ) ) {
				$addons = self::getListOfAddons();
			}

			$addonsToUpdate = [];

			if ( ! empty( $addons ) ) {
				foreach ( $addons as $addon ) {
					if ( OsAddonsHelper::is_addon_installed( $addon->wp_plugin_path ) ) {
						$addonData = get_plugin_data( OsAddonsHelper::get_addon_plugin_path( $addon->wp_plugin_path ) );
						if ( version_compare( $addon->version, ( $addonData['Version'] ?? '1.0.0' ), 'gt' ) ) {
							$addonsToUpdate[] = $addon->wp_plugin_name;
						}
					}
				}
			}

			if ( ! empty( $addonsToUpdate ) ) {
				TechXelaLatePointSettingsHelper::saveSetting( 'techxela_latepoint_addons_update_available', true );
			} else {
				TechXelaLatePointSettingsHelper::saveSetting( 'techxela_latepoint_addons_update_available', false );
			}
		}

		public static function checkPluginsLatestVersion( $transient ) {
			if ( ! empty( $transient ) ) {
				$addons = self::getListOfAddons();

				if ( ! empty( $addons ) ) {
					foreach ( $addons as $addon ) {
						if ( OsAddonsHelper::is_addon_installed( $addon->wp_plugin_path ) ) {
							$addonData = get_plugin_data( OsAddonsHelper::get_addon_plugin_path( $addon->wp_plugin_path ) );
							if ( version_compare( $addon->version, ( $addonData['Version'] ?? '1.0.0' ), 'gt' ) ) {
								$addonObj                                      = new stdClass();
								$addonObj->id                                  = OsUtilHelper::random_text();
								$addonObj->new_version                         = $addon->version;
								$addonObj->url                                 = admin_url( 'admin.php?page=latepoint&route_name=tech_xela_addons__index' );
								$transient->response[ $addon->wp_plugin_path ] = $addonObj;
							}
						}
					}
				}
			}

			return $transient;
		}

		public static function isUpdateAvailableForAddons() {
			return TechXelaLatePointSettingsHelper::getSetting( 'techxela_latepoint_addons_update_available' );
		}
	}

endif;