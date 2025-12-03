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

if ( ! class_exists( 'TechXelaLatePointAddonsHelper' ) ) :

	final class TechXelaLatePointAddonsHelper {

		public static function getActivatedAddons(): array {
			$addons = [];

			$addonClasses = \TechXela\LatePointAddons\CoreAddon::getChildAddonClasses();

			foreach ( $addonClasses as $addonClass ) {
				/** @var \TechXela\LatePointAddons\CoreAddon $addonInstance */
				$addonInstance = new $addonClass();

				$plugin_data = get_plugin_data( OsAddonsHelper::get_addon_plugin_path( $addonInstance->pluginFilePath() ) );
				$license     = TechXelaLatePointLicenseHelper::getLicenseInfo( $addonInstance->productId );

				$addon = [
					'product_id' => $addonInstance->productId,
					'slug'       => $addonInstance->addonSlug,
					'name'       => $plugin_data['Name'],
					'version'    => $addonInstance->version,
					'license'    => $license,
				];

				$addons[] = $addon;
			}

			return $addons;
		}

		public static function checkAddonVersions() {
			TechXelaLatePointUpdatesHelper::checkAddonsLatestVersion();
		}

		public static function mutateAddonPlugin( $params, $action, $successWord ): array {
			if ( empty( $params['addon_path'] ) ) {
				return [
					'status'  => false,
					'message' => esc_html_tx__( 'Unable to perform that action. Please try again', 'latepoint-addons' )
				];
			}

			$addonTitle = $params['addon_title'];
			$addonPath  = $params['addon_path'];

			$result        = @$action( [ $addonPath ] );
			$status        = is_wp_error( $result ) ? LATEPOINT_STATUS_ERROR : LATEPOINT_STATUS_SUCCESS;
			$response_html = is_wp_error( $result ) ?
				str_replace( $addonPath, $addonTitle, $result->get_error_message() ) :
				sprintf( esc_html_tx__( 'Addon %s', 'latepoint-addons' ), $successWord );

			return [ 'status' => $status, 'message' => $response_html ];
		}

		public static function addTxAddonsToOsListOfAddons( $addons = false ) {
			if ( ! $addons || ! is_array( $addons ) ) {
				$addons = [];
			}

			$txAddons = TechXelaLatePointUpdatesHelper::getListOfAddons();
			if ( ! empty( $txAddons ) && is_array( $txAddons ) ) {
				$addons = array_merge( $txAddons, $addons );
			}

			return $addons;
		}

		public static function getAddonObject( string $addonClass ) {
			$addonObjectName = lcfirst( $addonClass );

			/** @var \TechXela\LatePointAddons\CoreAddon $addonObject */
			if ( isset_tx_lp_global( $addonObjectName ) ) {
				$addonObject = get_tx_lp_global( $addonObjectName );
			} else {
				$addonObject = new $addonClass();
				set_tx_lp_global( $addonObjectName, $addonObject );
			}

			return $addonObject;
		}

		public static function generateMissingAddonLink( $label, $link = false ): string {
			return '<a target="_blank" href="' . ( $link ?: OsRouterHelper::build_link( [ 'tech_xela', 'addons' ] ) ) . '" class="os-add-box">
              <div class="add-box-graphic-w"><div class="add-box-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div></div>
              <div class="add-box-label">' . $label . '</div>
            </a>';
		}

		public static function isOsAddonActive( string $addonMnemonic ): bool {
			return is_plugin_active( "latepoint-$addonMnemonic/latepoint-$addonMnemonic.php" );
		}

		public static function isTxAddonActive( string $addonMnemonic ): bool {
			return is_plugin_active( "$addonMnemonic-latepoint/$addonMnemonic-latepoint.php" );
		}
	}

endif;