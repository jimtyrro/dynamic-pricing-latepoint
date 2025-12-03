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

if ( ! class_exists( 'OsTechXelaAddonsController' ) ) :

	class OsTechXelaAddonsController extends OsController {

		public function __construct() {
			parent::__construct();

			$this->views_folder        = TECHXELA_LATEPOINT_ADDONS_CORE_VIEWS_ABSPATH . 'addons/';
			$this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id( 'techxela_settings_menu' );
		}

		public function index() {
			$this->format_render( __FUNCTION__ );
		}

		public function loadAddonsList() {
			$addons               = TechXelaLatePointUpdatesHelper::getListOfAddons();
			$this->vars['addons'] = $addons;
			TechXelaLatePointUpdatesHelper::checkAddonsLatestVersion( $addons );
			$this->format_render( 'load_addons_list' );
		}

		public function installAddon() {
			if ( ! isset( $this->params['addon_path'] ) || empty( $this->params['addon_path'] ) ||
			     ! isset( $this->params['addon_pid'] ) || empty( $this->params['addon_pid'] ) ||
			     ! isset( $this->params['addon_title'] ) || empty( $this->params['addon_title'] ) ) {
				return;
			}

			$addonPath      = $this->params['addon_path'];
			$addonProductId = $this->params['addon_pid'];
			$addonTitle     = $this->params['addon_title'];

			$license = TechXelaLatePointLicenseHelper::getLicenseInfo( $addonProductId );

			if ( TechXelaLatePointLicenseHelper::isLicenseActive( $addonProductId ) ) {
				$api = new TechXela\LatePointAddons\Licensing\API();
				$updateCheck = $api->latestVersion( $addonProductId );

				if ( is_wp_error( $updateCheck ) || ! $updateCheck['status'] ) {
					$status        = LATEPOINT_STATUS_ERROR;
					$response_html = $updateCheck['message'];
					$code          = '500';
				} else {
					$addonNewVersion    = $updateCheck['latest_version'];
					$addonNewVersionVid = $updateCheck['update_id'];

					$updateData = [
						'product_id' => $addonProductId,
						'update_id'  => $addonNewVersionVid,
						'version'    => $addonNewVersion
					];

					$updateDownload = $api->downloadUpdate( $updateData, $license );

					if ( ! $updateDownload['status'] ) {
						$status        = LATEPOINT_STATUS_ERROR;
						$response_html = $updateDownload['message'];
						$code          = '500';
					} else {
						$addonInfo = [
							'url'         => $updateDownload['dl_url'],
							'plugin_path' => $addonPath,
							'version'     => $addonNewVersion
						];

						$result = OsAddonsHelper::install_addon( $addonInfo );

						if ( is_wp_error( $result ) ) {
							$status        = LATEPOINT_STATUS_ERROR;
							$response_html = $result->get_error_message();
							$code          = '500';
						} else {
							delete_transient( TechXelaLatePointSettingsHelper::prefixSetting(
								TechXelaLatePointLicenseHelper::prefixLicenseSetting( $addonProductId, 'next_update_check' )
							) );
							$status        = LATEPOINT_STATUS_SUCCESS;
							$code          = '200';
							$response_html = esc_html__( 'Addon installed successfully.', 'latepoint' );
						}
					}
				}
			} else {
				$this->vars['addon']   = [ 'product_id' => $addonProductId, 'name' => $addonTitle ];
				$this->vars['license'] = $license;
				$status                = LATEPOINT_STATUS_ERROR;
				$response_html         = $this->render( TECHXELA_LATEPOINT_ADDONS_CORE_VIEWS_ABSPATH . 'license/_license_form', 'none' );
				$code                  = '404';
			}

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status' => $status, 'code' => $code, 'message' => $response_html ) );
			}

		}

		private function mutateAddon( $params, $action, $successWord ) {
			$mutation = TechXelaLatePointAddonsHelper::mutateAddonPlugin( $params, $action, $successWord );

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( $mutation );
			}
		}

		public function activateAddon() {
			$this->mutateAddon( $this->params, 'activate_plugins', 'activated' );
		}

		public function deactivateAddon() {
			$this->mutateAddon( $this->params, 'deactivate_plugins', 'deactivated' );
		}

		public function deleteAddon() {
			$this->mutateAddon( $this->params, 'delete_plugins', 'deleted' );
		}
	}

endif;