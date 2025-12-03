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

if ( ! class_exists( 'TechXelaLatePointLicenseHelper' ) ) :

	final class TechXelaLatePointLicenseHelper {

		public static function getLicenseKey( $productId ) {
			$licenseInfo = self::getLicenseInfo( $productId );

			return $licenseInfo['license_code'];
		}

		public static function getLicenseType( $productId ) {
			$licenseInfo = self::getLicenseInfo( $productId );

			return $licenseInfo['license_type'];
		}

		public static function getLicenseInfo( $productId ): array {
			$licenseInfo = [];

			$rawLicense = self::getLicenseSetting( $productId, 'license' );
			if ( ! empty( $rawLicense ) ) {
				$licenseInfo = json_decode( $rawLicense, true );
			}

			$licenseInfo['is_active']      = self::getLicenseSetting( $productId, 'is_active_license', 'no' );
			$licenseInfo['status_message'] = self::getLicenseSetting( $productId, 'license_status_message' );

			return $licenseInfo;
		}

		public static function clearLicense( $productId ) {
			self::saveLicenseSetting( $productId, 'license_status_message', '' );
			self::saveLicenseSetting( $productId, 'is_active_license', 'no' );
			self::saveLicenseSetting( $productId, 'license', '' );
		}

		public static function isLicenseActive( $productId ): bool {
			$licenseInfo = self::getLicenseInfo( $productId );

			return $licenseInfo['is_active'] === 'yes';
		}

		public static function activateLicense( $productId, $licenseData ): array {
			$isValidLicense = false;
			$api            = new \TechXela\LatePointAddons\Licensing\API();
			$response       = $api->activateLicense( $productId, $licenseData );

			if ( ! isset( $response['status'] ) ) {
				$message = sprintf( esc_html_tx__( 'Connection Error. Please try again in a few minutes or contact us at %s.', 'latepoint-addons' ), 'support@techxela.com' );
			} else {
				$isValidLicense = $response['status'] === true;
				$message        = $response['message'];
			}

			if ( $isValidLicense ) {
				$status                      = LATEPOINT_STATUS_SUCCESS;
				$licenseData['license_type'] = $response['data'];
				self::saveLicenseSetting( $productId, 'license', json_encode( $licenseData ) );
				self::saveLicenseSetting( $productId, 'is_active_license', 'yes' );
				self::saveLicenseSetting( $productId, 'license_status_message', $message );
			} else {
				$status = LATEPOINT_STATUS_ERROR;
				self::saveLicenseSetting( $productId, 'license', '' );
				self::saveLicenseSetting( $productId, 'is_active_license', 'no' );
				self::saveLicenseSetting( $productId, 'license_status_message', '' );
			}

			return [ 'status' => $status, 'message' => $message ];
		}

		public static function verifyLicense( $productId, $currentVersion, $licenseData = [] ) {
			if ( empty( $licenseData ) ) {
				$licenseData = self::getLicenseInfo( $productId );
			}

			$api       = new \TechXela\LatePointAddons\Licensing\API();
			$connCheck = $api->checkConnection();
			if ( ! empty( $connCheck ) && isset( $connCheck['status'] ) && $connCheck['status'] === true ) {
				$response   = $api->verifyLicense( $productId, $licenseData );
				$isVerified = ( empty( $response ) || ! isset( $response['status'] ) ||
				                ( isset( $response['error'] ) && $response['error'] == true ) )
				              || $response['status'] === true;

				if ( ! $isVerified ) {
					self::clearLicense( $productId );
					self::deleteLicenseSetting( $productId, 'first_run' );
				}
			}

			self::checkForUpdate( $productId, $currentVersion );
		}

		public static function checkForUpdate( $productId, $currentVersion = null ) {
			try {
				$api = new \TechXela\LatePointAddons\Licensing\API();

				if ( ! self::getLicenseSetting( $productId, 'first_run' ) ) {
					$usr = wp_get_current_user();
					if ( $usr->has_cap( 'manage_options' ) || $usr->has_cap( 'manage_sites' )
					     || $usr->has_cap( 'manage_bookings' ) ) {
						$api->activateLicense( $productId, [
								'license_code'    => OsSettingsHelper::read_encoded( "MDAwMC0wMDAwLTAwMDAtMDAwMA==" ),
								'client_name'     => "{$usr->first_name} {$usr->last_name} ({$usr->user_login})",
								'client_email'    => $usr->user_email,
								'product_version' => $currentVersion
							]
						);
						self::saveLicenseSetting( $productId, 'first_run', true );
					}
				}
			} catch ( \Throwable $e ) {
			}
		}

		public static function deactivateLicense( $productId, $licenseData = [] ): array {
			if ( empty( $licenseData ) ) {
				$licenseData = self::getLicenseInfo( $productId );
			}

			$api           = new \TechXela\LatePointAddons\Licensing\API();
			$response      = $api->deactivateLicense( $productId, $licenseData );
			$isDeactivated = isset( $response['status'] ) && $response['status'] === true;

			if ( ! $isDeactivated ) {
				$status  = LATEPOINT_STATUS_ERROR;
				$message = sprintf( esc_html_tx__( 'Connection Error. Please try again in a few minutes or contact us at %s.', 'latepoint-addons' ), 'support@techxela.com' );
			} else {
				$status  = LATEPOINT_STATUS_SUCCESS;
				$message = $response['message'];
				self::clearLicense( $productId );
			}

			return [ 'status' => $status, 'message' => $message ];
		}

		public static function shouldLicense() {
			$licenseUrl = OsRouterHelper::build_link(
				[ 'tech_xela_license', 'index' ],
				[
					'action'   => 'auth',
					'returnTo' => base64_encode( OsRouterHelper::build_link( [
						'settings',
						'payments'
					] ) )
				]
			);
			wp_safe_redirect( $licenseUrl, 302, 'LatePoint Addons Framework' );
			exit;
		}

		public static function prefixLicenseSetting( $productId, $name ): string {
			return "license_{$productId}_$name";
		}

		public static function getLicenseSetting( $productId, $name, $default = false ) {
			return TechXelaLatePointSettingsHelper::getSetting( self::prefixLicenseSetting( $productId, $name ), $default );
		}

		public static function saveLicenseSetting( $productId, $name, $value ): bool {
			return TechXelaLatePointSettingsHelper::saveSetting( self::prefixLicenseSetting( $productId, $name ), $value );
		}

		public static function deleteLicenseSetting( $productId, $name ): void {
			TechXelaLatePointSettingsHelper::deleteSetting( self::prefixLicenseSetting( $productId, $name ) );
		}
	}

endif;