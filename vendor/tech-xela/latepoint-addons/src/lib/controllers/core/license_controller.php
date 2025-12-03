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

if ( ! class_exists( 'OsTechXelaLicenseController' ) ) :


	class OsTechXelaLicenseController extends \OsController {

		public function __construct() {
			parent::__construct();

			$this->views_folder        = TECHXELA_LATEPOINT_ADDONS_CORE_VIEWS_ABSPATH . 'license/';
			$this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id( 'techxela_settings_menu' );
		}

		public function index() {
			$action   = $this->params['action'] ?? false;
			$returnTo = $this->params['returnTo'] ?? false;

			switch ( $action ) {
				case 'auth':
					$flashMsg = esc_html_tx__( 'Please register your license to perform that action.', 'latepoint-addons' );
					break;
				default:
					$flashMsg = false;
			}

			if ( $flashMsg ) {
				$this->vars['flashMsg'] = $flashMsg;
			}
			if ( $returnTo ) {
				$this->vars['returnTo'] = $returnTo;
			}

			$addons = TechXelaLatePointAddonsHelper::getActivatedAddons();

			$this->vars['addons'] = $addons;

			$this->format_render( __FUNCTION__ );
		}

		public function activateLicense() {
			$productId   = $this->params['product_id'];
			$licenseData = $this->params['license'];

			if ( empty( $productId ) || empty( $licenseData ) || empty( $licenseData['license_code'] ) ||
			     empty( $licenseData['client_name'] ) || empty( $licenseData['client_email'] ) ) {
				$this->send_json( [
					'result'  => false,
					'message' => esc_html_tx__( 'Please check your entries and try again.', 'latepoint-addons' )
				] );

				exit;
			}

			$result = TechXelaLatePointLicenseHelper::activateLicense( $productId, $licenseData );

			if ( $this->get_return_format() == 'json' ) {
				$response = [ 'status' => $result['status'], 'message' => $result['message'] ];

				if ( isset( $this->params['returnTo'] ) ) {
					$response['returnTo'] = $this->params['returnTo'];
				}
				$this->send_json( $response );
			}
		}

		public function deactivateLicense() {
			$productId = $this->params['product_id'];

			if ( empty( $productId ) ) {
				$this->send_json( [
					'result'  => false,
					'message' => esc_html_tx__( 'No addon specified. Please refresh the page and try again.', 'latepoint-addons' )
				] );

				exit;
			}

			$result = TechXelaLatePointLicenseHelper::deactivateLicense( $productId );

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( [ 'status' => $result['status'], 'message' => $result['message'] ] );
			}
		}
	}

endif;