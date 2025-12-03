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

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'TechXelaLatePointSmsRolesHelper' ) ) :

	final class TechXelaLatePointSmsRolesHelper {
		public static function getAllAvailableActionsList( $actions ): array {
			return array_merge( $actions, [
				'quick_sms__view',
				'quick_sms__create',
				'quick_sms__edit',
				'quick_sms__delete',
				'quick_sms__send',
				'quick_sms__manage',
			] );
		}

		public static function rolesActionNames( $actionNames, $actionCode ) {
			$actionName = '';

			switch ( $actionCode ) {
				case 'quick_sms':
					$actionName = esc_html_tx__( 'Quick SMS', 'latepoint-addons' );
					break;
				case 'send':
					$actionName = esc_html_tx__( 'Send', 'latepoint-addons' );
					break;
			}

			if ( ! empty( $actionName ) ) {
				$actionNames[ $actionCode ] = $actionName;
			}

			return $actionNames;
		}

		public static function rolesActionDescriptions( $actionDescriptions, $actionCode ) {
			$actionDescription = '';

			switch ( $actionCode ) {
				case 'quick_sms':
					$actionDescription = sprintf( __( 'Send free-form or template-based SMS messages directly from the booking form or calendars, using an SMS add-on from %s', 'latepoint-addons' ), '<a href="http://techxela.com" target="_blank">TechXela</a>' );
					break;
			}

			if ( ! empty( $actionDescription ) ) {
				$actionDescriptions[ $actionCode ] = $actionDescription;
			}

			return $actionDescriptions;
		}

		public static function capabilitiesForControllers( $capabilities ) {
			$capabilities['OsTechXelaQuickSmsController'] = [
				'default'    => [ 'quick_sms__manage' ],
				'per_action' => [
					'formBlocksContainer' => [ 'quick_sms__view' ],
					'newForm'             => [ 'quick_sms__create' ],
					'save'                => [ 'quick_sms__edit' ],
					'delete'              => [ 'quick_sms__delete' ],
					'sendForm'            => [ 'quick_sms__send' ],
					'getTemplateContent'  => [ 'quick_sms__send' ],
					'sendSms'             => [ 'quick_sms__send' ],
					'getSentCount'        => [ 'quick_sms__send' ]
				]
			];

			return $capabilities;
		}
	}

endif;
