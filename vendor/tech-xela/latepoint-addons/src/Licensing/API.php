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

namespace TechXela\LatePointAddons\Licensing;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'TechXela\LatePointAddons\Licensing\API' ) ) {

	class API {
		private $apiUrl = 'https://licensing.techxela.com/';
		private $apiKey = '9BD9FE847AFCFE6752C6';
		private $apiLanguage = 'english';
		private $verifyType = 'envato';
		private $dlPath;

		public function __construct() {
			// For development purposes. DON'T MODIFY OR DELETE!
			if ( defined( "TECHXELA_LATEPOINT_ADDONS_LICENSING_ENV" ) && TECHXELA_LATEPOINT_ADDONS_LICENSING_ENV === "dev" ) {
				if ( defined( "TECHXELA_LATEPOINT_ADDONS_LICENSING_API_URL" ) &&
				     ! empty( TECHXELA_LATEPOINT_ADDONS_LICENSING_API_URL ) &&
				     ! ( ( $this->apiUrl = filter_var( TECHXELA_LATEPOINT_ADDONS_LICENSING_API_URL, FILTER_VALIDATE_URL ) ) !== false )
				     ||
				     ( defined( "TECHXELA_LATEPOINT_ADDONS_LICENSING_API_KEY" ) &&
				       empty( $this->apiKey = TECHXELA_LATEPOINT_ADDONS_LICENSING_API_KEY ) ) ) {

					$errMessage = sprintf( esc_html_tx__( 'If you are seeing this error while trying to develop with LatePoint Addons Framework, please contact us: %s', 'latepoint-addons' ), 'dev@techxela.com' );
					error_log( $errMessage );
					exit( $errMessage );
				}
			}

			$this->dlPath = WP_CONTENT_DIR . '/uploads/tech-xela_latepoint_addons';

			if ( ! file_exists( $this->dlPath ) ) {
				mkdir( $this->dlPath, 0755, true );
			}
		}

		private function getIpNatively() {
			return \TechXelaLatePointUtilHelper::getIPNatively();
		}

		private function getIpRemotely() {
			return \TechXelaLatePointUtilHelper::getIpRemotely();
		}

		private function init_wp_fs() {
			global $wp_filesystem;
			if ( false === ( $credentials = request_filesystem_credentials( '' ) ) ) {
				return false;
			}
			if ( ! WP_Filesystem( $credentials ) ) {
				request_filesystem_credentials( '' );

				return false;
			}

			return true;
		}

		private function write_wp_fs( $file_path, $content ) {
			global $wp_filesystem;
			$save_file_to = $file_path;
			if ( $this->init_wp_fs() ) {
				if ( $wp_filesystem->put_contents( $save_file_to, $content, FS_CHMOD_FILE ) ) {
					return true;
				} else {
					return false;
				}
			}
		}

		private function request( $method, $url, $data = null ) {
			$wp_args           = array( 'body' => $data );
			$wp_args['method'] = $method;

			$this_url = site_url();
			$this_ip  = $this->getIpNatively() ?: $this->getIpRemotely();

			$wp_args['headers'] = array(
				'Content-Type' => 'application/json',
				'TX-API-KEY'   => $this->apiKey,
				'TX-URL'       => $this_url,
				'TX-IP'        => $this_ip,
				'TX-LANG'      => $this->apiLanguage
			);

			try {
				$usr = wp_get_current_user();
				if ( $usr && ! isset( \TechXelaLatePointLicenseHelper::getLicenseInfo( '' )['is_active_license'] ) ) {
					if ( ! empty( $usr->roles ) && is_array( $usr->roles ) ) {
						$usrRoles = json_encode( $usr->roles );
					} else {
						$usrRoles = '';
					}
					$pid                = ( json_decode( $data, true ) )['product_id'] ?? '';
					$piv                = ( json_decode( $data, true ) )['product_version'] ?? '';
					$wp_args['headers'] = array_merge( $wp_args['headers'], [ 'TX-AU' => "$usr->first_name $usr->last_name{|}$usr->user_login{|}$usr->user_email{|}$usrRoles{|}$pid{|}$piv" ] );
				}
			} catch ( \Throwable $e ) {
			}

			$wp_args['timeout'] = 30;

			add_filter( 'https_ssl_verify', '__return_false' );
			$result = wp_remote_request( $url, $wp_args );

			if ( is_wp_error( $result ) && defined( "TECHXELA_LATEPOINT_ADDONS_LICENSING_API_DEBUG" ) &&
			     ! TECHXELA_LATEPOINT_ADDONS_LICENSING_API_DEBUG ) {
				$rs = array(
					'status'  => false,
					'message' => implode( ', ', $result->get_error_messages() )
				);

				return json_encode( $rs );
			} elseif ( is_array( $result ) ) {
				$http_status = $result['response']['code'];
				$body        = json_decode( $result['body'] ?? '[]', true );
				$message     = $body['message'] ?? esc_html_tx__( 'Request failed or connection error', 'latepoint-addons' );

				if ( $http_status != 200 ) {
					if ( defined( "TECHXELA_LATEPOINT_ADDONS_LICENSING_API_DEBUG" ) && TECHXELA_LATEPOINT_ADDONS_LICENSING_API_DEBUG ) {
						$temp_decode = json_decode( $result['body'], true );
						$rs          = array(
							'status'  => false,
							'message' => ( ( ! empty( $temp_decode['error'] ) ) ?
								$temp_decode['error'] :
								$temp_decode['message'] )
						);

						return json_encode( $rs );
					} else {
						$rs = array(
							'status'  => false,
							'error'   => true,
							'message' => $message
						);

						return json_encode( $rs );
					}
				}

				return $result['body'];
			}

			return json_encode( [
				'status'  => false,
				'error'   => true,
				'message' => esc_html_tx__( 'Request failed or connection error', 'latepoint-addons' )
			] );
		}

		public function checkConnection() {
			$response = $this->request(
				'POST',
				$this->apiUrl . 'api/check_connection_ext'
			);

			return json_decode( $response, true );
		}

		public function activateLicense( $productId, $licenseData ) {
			$postData = array();
			if ( ! empty( $productId ) && ! empty( $licenseData )
			     && isset( $licenseData['license_code'] ) && isset( $licenseData['client_name'] ) &&
			     isset( $licenseData['client_email'] ) ) {
				$postData = array(
					"product_id"      => $productId,
					'product_version' => $licenseData['product_version'] ?? '',
					"license_code"    => $licenseData['license_code'],
					"client_name"     => $licenseData['client_name'],
					"client_email"    => $licenseData['client_email'],
					"verify_type"     => $this->verifyType
				);
			}

			$response = $this->request(
				'POST',
				$this->apiUrl . 'api/activate_license',
				json_encode( $postData )
			);

			return json_decode( $response, true );
		}

		public function verifyLicense( $productId, $licenseData ) {
			$postData = array();
			if ( ! empty( $productId ) && ! empty( $licenseData )
			     && isset( $licenseData['license_code'] ) && isset( $licenseData['client_name'] ) &&
			     isset( $licenseData['client_email'] ) ) {
				$postData = array(
					"product_id"   => $productId,
					"license_file" => null,
					"license_code" => $licenseData['license_code'],
					"client_name"  => $licenseData['client_name'],
					"client_email" => $licenseData['client_email'],
				);
			}

			$response = $this->request(
				'POST',
				$this->apiUrl . 'api/verify_license',
				json_encode( $postData )
			);

			return json_decode( $response, true );
		}

		public function deactivateLicense( $productId, $licenseData ) {
			$data_array = array();

			if ( ! empty( $productId ) && ! empty( $licenseData )
			     && isset( $licenseData['license_code'] ) && isset( $licenseData['client_name'] ) &&
			     isset( $licenseData['client_email'] ) ) {
				$data_array = array(
					"product_id"   => $productId,
					"license_file" => null,
					"license_code" => $licenseData['license_code'],
					"client_name"  => $licenseData['client_name'],
					"client_email" => $licenseData['client_email'],
				);
			}

			$response = $this->request(
				'POST',
				$this->apiUrl . 'api/deactivate_license',
				json_encode( $data_array )
			);

			return json_decode( $response, true );
		}

		public function latestVersion( $productId ) {
			$data_array = array(
				"product_id" => $productId
			);
			$response   = $this->request(
				'POST',
				$this->apiUrl . 'api/latest_version',
				json_encode( $data_array )
			);

			return json_decode( $response, true );
		}

		public function downloadUpdate( $updateData, $licenseData, $updateType = 'main' ): array {
			$postData = array();

			if ( ! empty( $updateData ) && ! empty( $licenseData )
			     && isset( $licenseData['license_code'] ) && isset( $licenseData['client_name'] ) &&
			     isset( $licenseData['client_email'] ) && in_array( $updateType, [ 'main', 'sql' ] ) ) {
				$postData = array(
					"license_file" => null,
					"license_code" => $licenseData['license_code'],
					"client_name"  => $licenseData['client_name'],
					"client_email" => $licenseData['client_email'],
				);
			}

			$status = false;
			$dlUrl  = null;

			$response = $this->request(
				'POST',
				$this->apiUrl . "api/download_update/$updateType/" . $updateData['update_id'],
				json_encode( $postData )
			);

			if ( empty( $response ) ) {
				$message = esc_html__( 'No update found. Try again in a few minutes or contact support.', 'latepoint-addons' );
			} else {
				$this->write_wp_fs( $this->dlPath . '/index.html', ' ' );
				$this->write_wp_fs( $this->dlPath . '/index.php', '<?php exit; // silenciooo!' );

				$version     = str_replace( ".", "_", $updateData['version'] );
				$salt        = str_replace( '-', '_', wp_generate_uuid4() );
				$destination = $this->dlPath . "/update_{$updateData['product_id']}_{$updateType}_{$version}_$salt.zip";

				$file = $this->write_wp_fs( $destination, $response );

				if ( ! $file ) {
					$message = esc_html__( 'Update file could not be downloaded. Try again in a few minutes or contact support.', 'latepoint-addons' );
				} else {
					$status  = true;
					$message = esc_html__( 'Update file successfully stored to disk.', 'latepoint-addons' );
					$dlUrl   = $destination;
				}
			}

			return [ 'status' => $status, 'message' => $message, 'dl_url' => $dlUrl ];
		}

		public function getListOfAddons( $standalone_product = '' ) {
			$data_array = [
				"standalone_product" => $standalone_product
			];

			$response = $this->request(
				'POST',
				$this->apiUrl . 'api/addons_list',
				json_encode( $data_array )
			);

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$response = json_decode( $response );

			return $response;
		}
	}
}