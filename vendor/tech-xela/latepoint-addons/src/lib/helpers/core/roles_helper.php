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

if ( ! class_exists( 'TechXelaLatePointRolesHelper' ) ) :

	final class TechXelaLatePointRolesHelper {
		public static function getAllAvailableActionsList( $actions ): array {
			return array_merge( $actions, [
				'tx_updates__manage',
			] );
		}

		public static function rolesActionNames( $actionNames, $actionCode ) {
			$actionName = '';

			switch ( $actionCode ) {
				case 'list':
					$actionName = esc_html_tx__( 'List', 'latepoint-addons' );
					break;
				case 'manage':
					$actionName = esc_html_tx__( 'Manage', 'latepoint-addons' );
					break;
				case 'tx_updates':
					$actionName = join( ' ', [ 'TechXela', esc_html_tx__( 'Updates', 'latepoint-addons' ) ] );
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
				case 'tx_updates':
					$actionDescription = sprintf( __( 'Manage add-ons licenses and updates from %s', 'latepoint-addons' ), '<a href="http://techxela.com" target="_blank">TechXela</a>' );
					break;
			}

			if ( ! empty( $actionDescription ) ) {
				$actionDescriptions[ $actionCode ] = $actionDescription;
			}

			return $actionDescriptions;
		}

		public static function capabilitiesForControllers( $capabilities ): array {
			return array_merge( $capabilities, [
				'OsTechXelaAddonsController'  => [
					'default' => [ 'tx_updates__manage' ]
				],
				'OsTechXelaLicenseController' => [
					'default' => [ 'tx_updates__manage' ]
				]
			] );
		}
	}

endif;
