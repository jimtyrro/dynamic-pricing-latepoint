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

namespace TechXela\LatePointAddons;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\TechXela\LatePointAddons\SMSAddon' ) ) {
	abstract class SMSAddon extends CoreAddon {
		use \TechXela\LatePointAddons\Traits\HasSetupInstructions;

		/**
		 * Base SMS Addon information.
		 */
		public $smsProcessorCode;
		public $smsProcessorName;
		protected $smsProcessorLogoImg = 'processor-logo.svg';


		final protected function defineConstants() {
			global $wpdb;
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_TABLE_SMS_LOGS', $wpdb->prefix . 'latepoint_techxela_sms_logs' );
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_SMS_HELPERS_ABSPATH', dirname( __FILE__ ) . '/lib/helpers/sms/' );
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_SMS_VIEWS_ABSPATH', dirname( __FILE__ ) . '/lib/views/sms/' );
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_SMS_QSMS_COUNT_BOOKING_META_KEY', 'tx_qsms_sent_count' );
		}

		protected function includes() {
			include_once TECHXELA_LATEPOINT_ADDONS_SMS_HELPERS_ABSPATH . 'activities_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_SMS_HELPERS_ABSPATH . 'booking_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_SMS_HELPERS_ABSPATH . 'database_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_SMS_HELPERS_ABSPATH . 'debug_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_SMS_HELPERS_ABSPATH . 'menu_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_SMS_HELPERS_ABSPATH . 'roles_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_SMS_HELPERS_ABSPATH . 'settings_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_SMS_HELPERS_ABSPATH . 'templates_helper.php';

			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/controllers/sms/quick_sms_controller.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/controllers/sms/sms_controller.php';
		}

		/**
		 * Register addon specific filters/actions
		 *
		 * @return void
		 */
		protected function initHooks() {
			add_filter( 'latepoint_addons_sqls', [ \TechXelaLatePointSmsDatabaseHelper::class, 'addonsSqls' ], 35 );

			add_filter( 'techxela_latepoint_settings_menu_children', [ $this, 'settingsMenuChildren' ] );

			add_filter( 'latepoint_sms_processors', [ $this, 'smsProcessors' ], 10, 2 );

			add_action( 'latepoint_sms_processor_settings', [ $this, 'addSmsProcessorSettings' ] );

			add_filter( 'latepoint_notifications_send_sms', [ $this, 'sendSms' ], 9, 3 );

			add_filter( 'latepoint_activity_view_vars', [
				\TechXelaLatePointSmsActivitiesHelper::class,
				'activityViewVars'
			], 9, 2 );

			add_action( 'latepoint_settings_notifications_other_after', [
				\TechXelaLatePointSmsSettingsHelper::class,
				'settingsNotificationsOtherAfter'
			] );

			add_action( 'latepoint_booking_quick_form_after', [
				\TechXelaLatePointSmsBookingHelper::class,
				'bookingQuickFormAfter'
			], 35 );

			add_filter( 'latepoint_roles_get_all_available_actions_list', [
				\TechXelaLatePointSmsRolesHelper::class,
				'getAllAvailableActionsList'
			] );

			add_filter( 'latepoint_roles_action_names', [
				\TechXelaLatePointSmsRolesHelper::class,
				'rolesActionNames'
			], 20, 2 );

			add_filter( 'latepoint_roles_action_descriptions', [
				\TechXelaLatePointSmsRolesHelper::class,
				'rolesActionDescriptions'
			], 20, 2 );

			add_filter( 'latepoint_capabilities_for_controllers', [
				\TechXelaLatePointSmsRolesHelper::class,
				'capabilitiesForControllers'
			] );
		}

		final protected function init() {
			$this->smsAddonInit();
		}

		protected function smsAddonInit() {
		}


		final protected function latePointInit() {
			$this->smsAddonLatePointInit();
		}

		/**
		 * Addon-specific LatePoint initialization
		 */
		protected function smsAddonLatePointInit() {
		}

		private function __loadAdminScriptsAndStyles() {
			wp_enqueue_style(
				'techxela-latepoint-addons-sms-admin',
				$this->frameworkSrcUrl() . '/public/css/sms-admin.css',
				[],
				self::$frameworkVersion
			);
			wp_enqueue_script(
				'techxela-latepoint-addons-sms-admin',
				$this->frameworkSrcUrl() . '/public/js/sms-admin.min.js',
				[ 'jquery', 'latepoint-main-admin' ],
				self::$frameworkVersion
			);

			if ( \TechXelaLatePointSmsSettingsHelper::isOn( 'qsms', 'enabled' ) &&
			     \OsRolesHelper::can_user( 'quick_sms__send' ) ) {
				wp_enqueue_style(
					'techxela-latepoint-addons-quick-sms-admin',
					$this->frameworkSrcUrl() . '/public/css/quick-sms-admin.css',
					[],
					self::$frameworkVersion
				);
				wp_enqueue_script(
					'techxela-latepoint-addons-quick-sms-admin',
					$this->frameworkSrcUrl() . '/public/js/quick-sms-admin.min.js',
					[ 'jquery', 'latepoint-main-admin' ],
					self::$frameworkVersion
				);
			}
		}

		final protected function loadAdminScriptsAndStyles() {
			$this->__loadAdminScriptsAndStyles();
			$this->smsAddonLoadAdminScriptsAndStyles();
		}

		protected function smsAddonLoadAdminScriptsAndStyles() {

		}

		final public function smsProcessors( $smsProcessors, $enabledOnly ) {
			$smsProcessors[ $this->smsProcessorCode ] = [
				'code'      => $this->smsProcessorCode,
				'label'     => $this->smsProcessorName,
				'image_url' => $this->publicImgUrl( $this->smsProcessorLogoImg ),
			];

			return $smsProcessors;
		}

		/**
		 * Render SMS processor-specific setup instructions.
		 *
		 * @return string Raw HTML instructions to be escaped/filtered.
		 */
		protected function renderSmsProcessorInstructions(): string {
			return '';
		}

		/**
		 * Render SMS processor-specific settings fields.
		 *
		 * @return void
		 */
		protected function renderSmsProcessorSettingsFields() {
		}

		/**
		 * Render settings section for this SMS processor.
		 *
		 * @return void
		 */
		final public function addSmsProcessorSettings( $smsProcessorCode ) {
			if ( $smsProcessorCode == $this->smsProcessorCode ) {
				if ( $this->hasSetupInstructions ) { ?>
                    <div class="sub-section-row">
                        <div class="sub-section-label">
                            <h3><?php esc_html_tx_e( 'Instructions', 'latepoint-addons' ); ?></h3>
                        </div>
                        <div class="sub-section-content">
                            <div class="latepoint-message latepoint-message-subtle">
								<?= wp_kses(
									$this->renderSmsProcessorInstructions(),
									$this->setupInstructionsAllowedHtml,
									$this->setupInstructionsAllowedProtocols
								) ?>
                            </div>
                        </div>
                    </div>
				<?php }
				$this->renderSmsProcessorSettingsFields();
			}
		}

		/**
		 * Abstracted logic to determine if SMS can be sent.
		 *
		 * @param $to
		 * @param $content
		 *
		 * @return array
		 */
		private function _canSendSms( $result, $to, $content ): array {
			if ( empty( $to ) ) {
				$result['message']                      = esc_html_tx__( 'No recipient phone number(s) provided.', 'latepoint-addons' );
				$result['extra_data']['missing_msisdn'] = true;
				$result['extra_data']['send_unable']    = true;
			} elseif ( ! \TechXelaLatePointLicenseHelper::isLicenseActive( $this->productId ) ) {
				$result['message']                            = esc_html_tx__( 'Please register your license to perform that action.', 'latepoint-addons' );
				$result['extra_data']['product_unregistered'] = true;
				$result['extra_data']['send_unable']          = true;
			}

			return [ $result, $to, $content ];
		}

		/**
		 * Custom logic to determine if SMS can be sent.
		 * Define <code>$result['extra_data']['send_unable'] = false;</code> to reject sending outright
		 *
		 * @param $result
		 * @param $to
		 * @param $content
		 *
		 * @return array
		 */
		protected function canSendSms( $result, $to, $content ): array {
			return [ $result, $to, $content ];
		}

		/**
		 * Abstracted SMS sending logic.
		 *
		 * @param array $result
		 * @param $to
		 * @param $content
		 *
		 * @return array
		 */
		final public function sendSms( array $result, $to, $content ): array {
			if ( \OsSmsHelper::is_sms_processor_enabled( $this->smsProcessorCode ) ) {
				$result['processor_code'] = $this->smsProcessorCode;
				$result['processor_name'] = $this->smsProcessorName;

				list( $result, $to, $content ) = $this->_canSendSms( $result, $to, $content );

				if ( empty( $result['extra_data']['send_unable'] ) ) {
					try {
						if ( ! is_array( $to ) ) {
							$to = array_map( 'trim', explode( ',', $to ) );
						}

						$result['recipients']      = $to;
						$result['sent_recipients'] = [];

						$result = $this->sendSMSMessage( $result, $to, $content );

						$totalSent = count( $result['sent_recipients'] );
						if ( $totalSent > 0 ) {
							$result['status'] = LATEPOINT_STATUS_SUCCESS;
						}

						$result['message'] = sprintf( esc_html_tx__( 'SMS message sent to %d of %d recipients.', 'whatsapp-latepoint' ), $totalSent, count( $to ) );
					} catch ( \Throwable $exception ) {
						$result['message']                 = $exception->getMessage();
						$result['errors'][]                = $result['message'];
						$result['extra_data']['exception'] = $exception->getTraceAsString();
						\TechXelaLatePointSmsDebugHelper::logSendException( $exception, $this->smsProcessorCode, $this->smsProcessorName );
					}
				} else {
					$result['errors'][] = $result['message'];
				}
			}

			return $result;
		}

		/**
		 * Processor-specific SMS sending logic.
		 *
		 * @param array $result
		 * @param array $to
		 * @param $content
		 *
		 * @return array
		 *
		 */
		protected function sendSMSMessage( array $result, array $to, $content ): array {
			return $result;
		}

		protected function localizedVarsForAdmin( $localizedVars ) {
			if ( \TechXelaLatePointSmsSettingsHelper::isOn( 'qsms', 'enabled' ) &&
			     \OsRolesHelper::can_user( 'quick_sms__send' ) ) {
				$localizedVars['techxela_qsms_quick_sms_btn_html']         = \TechXelaLatePointSmsBookingHelper::quickSmsBtnHtml();
				$localizedVars['techxela_qsms_indicate_sent']              = \TechXelaLatePointSmsSettingsHelper::isOn( 'qsms', 'indicate_sent', 'on' );
				$localizedVars['techxela_qsms_send_count_route']           = \OsRouterHelper::build_route_name( 'tech_xela_quick_sms', 'getSentCount' );
				$localizedVars['techxela_qsms_send_form_route']            = \OsRouterHelper::build_route_name( 'tech_xela_quick_sms', 'sendForm' );
				$localizedVars['techxela_qsms_get_template_content_route'] = \OsRouterHelper::build_route_name( 'tech_xela_quick_sms', 'getTemplateContent' );
			}

			return $localizedVars;
		}

		final public static function settingsMenuChildren( $additionalChildren ): array {
			foreach ( \TechXelaLatePointSmsMenuHelper::menuItems() as $menuItem ) {
				if ( ! in_array( $menuItem, $additionalChildren ) ) {
					$additionalChildren[] = $menuItem;
				}
			}

			return $additionalChildren;
		}

		final public static function getSMSAddons(): array {
			return parent::getChildAddonClasses( self::class );
		}
	}
}