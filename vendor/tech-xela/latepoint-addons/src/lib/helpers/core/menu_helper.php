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

if ( ! class_exists( 'TechXelaLatePointMenuHelper' ) ) :
	final class TechXelaLatePointMenuHelper {

		public static function sideMenu( $menus ): array {
			$updatesAvailable = \TechXelaLatePointUpdatesHelper::isUpdateAvailableForAddons();
			$children         = [];
			$canManageUpdates = OsRolesHelper::can_user( 'tx_updates__manage' );

			if ( $canManageUpdates ) {
				$children[] = [
					'id'               => 'techxela_addons_menu',
					'is_techxela_menu' => true,
					'label'            => esc_html__( 'Add-ons', 'latepoint' ),
					'show_notice'      => $updatesAvailable,
					'icon'             => '',
					'link'             => \OsRouterHelper::build_link( [ 'tech_xela_addons', 'index' ] )
				];
			}

			$additionalChildren = array();
			$additionalChildren = apply_filters( 'techxela_latepoint_settings_menu_children', $additionalChildren );
			$children           = array_merge( $children, $additionalChildren );

			if ( $canManageUpdates ) {
				$children[] = [
					'id'               => 'techxela_licenses_menu',
					'is_techxela_menu' => true,
					'label'            => esc_html_tx__( 'Licenses', 'latepoint-addons' ),
					'icon'             => '',
					'link'             => \OsRouterHelper::build_link( [ 'tech_xela_license', 'index' ] )
				];
			}

			$txSettingsMenu = [
				'id'          => 'techxela_settings_menu',
				'label'       => 'TechXela',
				'show_notice' => $updatesAvailable,
				'icon'        => 'techxela-latepoint-icons techxela-icon',
				'link'        => ! empty( $children ) ? $children[0]['link'] : '#',
				'children'    => $children
			];

			if ( $canManageUpdates ||
			     apply_filters( 'techxela_latepoint_can_show_tx_menu', false ) ) {
				$menus = \TechXelaLatePointMenuHelper::insertMenuItems( $menus, [ $txSettingsMenu ] );
			}

			if ( $canManageUpdates && ! TechXelaLatePointAddonsHelper::isTxAddonActive( 'whatsapp' ) ) {
				$whatsAppSettingsMenu = [
					'id'               => 'techxela_whatsapp_menu',
					'is_techxela_menu' => true,
					'label'            => 'WhatsApp',
					'icon'             => 'techxela-latepoint-icons whatsapp-icon target-blank',
					'link'             => 'https://1.envato.market/whatsapp-latepoint'
				];

				$menus = TechXelaLatePointMenuHelper::insertMenuItems(
					$menus,
					[ $whatsAppSettingsMenu ],
					'processes',
					TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU,
					TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_BEFORE
				);
			}

			if ( $canManageUpdates && ! TechXelaLatePointAddonsHelper::isTxAddonActive( 'push-notifications' ) ) {
				$pushNotifsSettingsMenu = [
					'id'               => 'techxela_push_notifs_menu',
					'is_techxela_menu' => true,
					'label'            => 'Push Notifications',
					'icon'             => 'techxela-latepoint-icons push-notifs-icon target-blank',
					'link'             => 'https://1.envato.market/push-notifications-latepoint'
				];

				$menus = TechXelaLatePointMenuHelper::insertMenuItems(
					$menus,
					[ $pushNotifsSettingsMenu ],
					'processes',
					TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU,
					TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_BEFORE
				);
			}

			return $menus;
		}

		/**
		 * Insert side menu items. Default behaviour is to insert item(s) into top-level menu or before menu section
		 *
		 * @param array $menus Array of existing menus
		 *
		 * @param array $itemsToInsert Array of menu items to insert
		 *
		 * @param string $targetMenuId ID of the top level menu or menu section to use as reference for the insertion
		 *
		 * @param int $targetMenuType The type of menu element to query for. Valid types are:
		 *
		 *                               <b>TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU</b> - Top-level menu
		 *
		 *                               <b>TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU_SECTION</b> - Menu section
		 *
		 * @param int $mode The mode/method of insertion. Valid modes are:
		 *
		 *                  <b>TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_BEFORE</b> - Insert $itemsToInsert <b>before</b> target menu element
		 *
		 *                  <b>TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_AFTER</b> - Insert $itemsToInsert <b>after</b> target menu element
		 *
		 *                  <b>TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_INTO</b> - Insert $itemsToInsert <b>into</b> target menu element. Implies <i>$targetMenuType</i> == <i>TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU</i>
		 *
		 *                  <b>TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_REPLACE</b> - Insert $itemsToInsert <b>before</b> target menu element
		 *
		 * @param int $childIndex Index at which to insert <i>$itemsToInsert</i> into menu's `children` array.
		 *                             Implies <i>$targetMenuType</i> == <i>TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU</i> and <i>$mode</i> == <i>TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_INTO</i>
		 *
		 * @return array
		 */
		public static function insertMenuItems( array $menus, array $itemsToInsert = [], string $targetMenuId = '', int $targetMenuType = TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU, int $mode = TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_INTO, int $childIndex = 0 ): array {
			if ( empty( $itemsToInsert ) ) {
				return $menus;
			}

			if ( empty( $targetMenuType ) ) {
				$targetMenuType = TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU;
			}

			if ( empty( $mode ) ) {
				$mode = TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_INTO;
			}

			foreach ( $itemsToInsert as $itemToInsert ) {
				$menuExist = false;

				if ( isset( $itemToInsert['id'] ) ) {
					foreach ( $menus as $menu ) {
						if ( array_key_exists( 'id', $menu ) && $menu['id'] === $itemToInsert['id'] ) {
							$menuExist = true;
							break;
						}
						if ( isset( $menu['children'] ) && is_array( $menu['children'] ) ) {
							foreach ( $menu['children'] as $childMenu ) {
								if ( array_key_exists( 'id', $childMenu ) && $childMenu['id'] === $itemToInsert['id'] ) {
									$menuExist = true;
									break 2;
								}
							}
						}
					}

					if ( ! $menuExist ) {
						if ( ! empty( $targetMenuId ) ) {
							for ( $idx = 0; $idx < count( $menus ); $idx ++ ) {
								$menu = $menus[ $idx ];
								if ( ( $targetMenuType === TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU && array_key_exists( 'id', $menu ) && $menu['id'] === $targetMenuId ) ||
								     ( $targetMenuType === TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU_SECTION && array_key_exists( 'menu_section', $menu ) && $menu['menu_section'] === $targetMenuId ) ) {
									switch ( $mode ) {
										case TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_BEFORE:
										default:
											break;
										case TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_AFTER:
											$idx += 1;
											break;
										case TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_INTO:
											if ( $targetMenuType === TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU ) {
												if ( $childIndex && ! empty( $menu['children'] ) ) {
													array_splice( $menu['children'], $childIndex, 0, [ $itemToInsert ] );
												} else {
													$menu['children'][] = $itemToInsert;
												}
												$itemToInsert = $menu;
												unset( $menus[ $idx ] );
											}
											break;
										case TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_REPLACE:
											if ( $targetMenuType === TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU ||
											     ( $targetMenuType === TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU_SECTION &&
											       isset( $itemToInsert['label'], $itemToInsert['small_label'], $itemToInsert['menu_section'] ) ) ) {
												unset( $menus[ $idx ] );
											}
											break;
									}

									array_splice( $menus, $idx, 0, [ $itemToInsert ] );
									break;
								}
							}
						} else {
							$menus = array_merge( $menus, $itemsToInsert );
						}
					}
				}
			}

			return $menus;
		}
	}
endif;
