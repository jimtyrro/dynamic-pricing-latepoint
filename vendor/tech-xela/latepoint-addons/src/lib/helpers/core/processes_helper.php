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

if ( ! class_exists( 'TechXelaLatePointProcessesHelper' ) ) :

	class TechXelaLatePointProcessesHelper {
		public static function processActionTypes( $actionTypes ): array {
			return array_merge( [ 'tx_send_whatsapp', 'tx_send_push_notif' ], $actionTypes );
		}

		public static function processActionNames( $names ): array {
			$names['tx_send_whatsapp']   = esc_html__( 'Send WhatsApp Message', 'whatsapp-latepoint' );
			$names['tx_send_push_notif'] = esc_html__( 'Send Push Notification', 'push-notifications-latepoint' );

			return $names;
		}

		public static function processActionSettingsFieldsHtmlAfter( $html, $action ): string {
			switch ( $action->type ) {
				case 'tx_send_whatsapp':
					$html = TechXelaLatePointAddonsHelper::generateMissingAddonLink(
						esc_html_tx__( 'Install WhatsApp Add-on', 'latepoint-addons' ),
						'https://1.envato.market/whatsapp-latepoint'
					);
					break;
				case 'tx_send_push_notif':
					$html = TechXelaLatePointAddonsHelper::generateMissingAddonLink(
						esc_html_tx__( 'Install Push Notifications Add-on', 'latepoint-addons' ),
						'https://1.envato.market/push-notifications-latepoint'
					);
					break;
			}

			return $html;
		}
	}

endif;
