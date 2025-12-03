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

if ( ! function_exists( 'set_tx_lp_global' ) ) {
	function set_tx_lp_global( $name, $value ) {
		$GLOBALS['_TX_LP_VARS'][ $name ] = $value;
	}

	function get_tx_lp_global( $name, $default = false ) {
		return $GLOBALS['_TX_LP_VARS'][ $name ] ?? $default;
	}

	function isset_tx_lp_global( $name ): bool {
		return isset( $GLOBALS['_TX_LP_VARS'][ $name ] );
	}

	function unset_tx_lp_global( $name ) {
		unset( $GLOBALS['_TX_LP_VARS'][ $name ] );
	}
}

if ( ! function_exists( 'esc_html_tx__' ) ) {
	function esc_html_tx__( $string, $domain ): string {
		return esc_html__( $string, $domain );
	}

	function esc_html_tx_e( $string, $domain ) {
		esc_html_e( $string, $domain );
	}

	function esc_attr_tx__( $string, $domain ): string {
		return esc_attr__( $string, $domain );
	}

	function esc_attr_tx_e( $string, $domain ) {
		esc_attr_e( $string, $domain );
	}
}

if ( ! function_exists( 'techxela_latepoint_addons_maybe_init' ) ) {
	/**
	 * Handle add-on's dependency on LatePoint (and/or other plugins)
	 *
	 * @param string $addonClass
	 * @param array $deps
	 *
	 * @return void
	 */
	function techxela_latepoint_addons_maybe_init( string $addonClass, array $deps = [] ) {
		$deps = array_merge( [
			'latepoint/latepoint.php' => [
				'name'    => 'LatePoint',
				'link'    => 'https://bit.ly/latepoint',
				'version' => '4.9.0'
			]
		], $deps );

		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		$missingDeps = array_filter( $deps, function ( $key ) {
			return is_plugin_inactive( $key );
		}, ARRAY_FILTER_USE_KEY );

		try {
			if ( empty( $missingDeps ) ) {
				// We are addons, so we load late
				add_action( 'plugins_loaded', function () use ( $addonClass ) {
					try {
						$instanceVarName  = lcfirst( $addonClass );
						$addonInstance = new $addonClass();
						if ( ! isset_tx_lp_global( $instanceVarName ) ) {
							set_tx_lp_global( $instanceVarName, $addonInstance );
						}

						techxela_latepoint_addons_init_plugins_page( $addonInstance );
					} catch ( \Throwable $e ) {
					}
				}, 20 );
			} else {
				throw new \Exception();
			}
		} catch ( \Throwable $exception ) {
			try {
				$pluginFileName = ( new \ReflectionClass( $addonClass ) )->getFileName();
				$pluginData     = get_plugin_data( $pluginFileName );

				add_action( 'init', function () use ( $missingDeps, $pluginData ) {
					if ( current_user_can( 'activate_plugins' ) ) {
						add_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', function () use ( $missingDeps, $pluginData ) {
							$requiredNotice = '<ul style="list-style: square; margin-left: 20px;">';
							foreach ( $missingDeps as $missingDepKey => $missingDep ) {
								$requiredNotice .= sprintf( '<li>Please install and activate %s<a href="%s" target="_blank"><strong>%s</strong></a>%s.</li>',
									isset( $missingDep['version'] ) ? '' : 'the latest version of ',
									$missingDep['link'],
									$missingDep['name'],
									isset( $missingDep['version'] ) ? ' version <strong>' . $missingDep['version'] . '</strong> or greater' : ''
								);
							}
							$requiredNotice .= '</ul>';

							printf( '<div class="notice notice-error"><h3>%s</h3><p>The plugin is currently active but will remain <strong>non-functional</strong>, until all required dependencies are installed and activated.</p>%s</div>',
								"{$pluginData['Name']}",
								$requiredNotice
							);
						} );
						unset ( $_GET['activate'], $_GET['activate-multi'] );
					}
				} );
			} catch ( \Throwable $exception ) {
				if ( $exception instanceof ReflectionException ) {
					add_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', function () use ( $addonClass ) {
						printf( '<strong>LatePoint Addons Framework: </strong>' . esc_html_tx__( 'Could not locate a file which declares the %s class', 'latepoint-addons' ), $addonClass );
					} );
					unset ( $_GET['activate'], $_GET['activate-multi'] );
				}
			}
		}
	}
}

if ( ! function_exists( 'techxela_latepoint_addons_init_plugins_page' ) ) {
	/**
	 * Initialize plugins page on admin panel
	 *
	 * @param \TechXela\LatePointAddons\CoreAddon $addonInstance
	 *
	 * @return void
	 */
	function techxela_latepoint_addons_init_plugins_page( \TechXela\LatePointAddons\CoreAddon $addonInstance ) {
		$pluginFileName  = ( new \ReflectionClass( $addonInstance ) )->getFileName();
		$pluginBaseName  = plugin_basename( $pluginFileName );
		$pluginData      = get_plugin_data( $pluginFileName );
		$isSmsAddon      = is_subclass_of( $addonInstance, \TechXela\LatePointAddons\SMSAddon::class );
		$isPaymentsAddon = is_subclass_of( $addonInstance, \TechXela\LatePointAddons\PaymentsAddon::class );
		$isMeetingAddon  = is_subclass_of( $addonInstance, \TechXela\LatePointAddons\MeetingsAddon::class );

		// Action Links
		$actionLinks = [];

		if ( $isSmsAddon ) {
			$settingsActionRouteName = 'settings__notifications';
		} elseif ( $isPaymentsAddon ) {
			$settingsActionRouteName = 'settings__payments';
		} elseif ( $isMeetingAddon ) {
			$settingsActionRouteName = 'integrations__external_meeting_systems';
		} else {
			$settingsActionRouteName = $addonInstance->settingsRouteName ?: false;
		}

		$addonsL10n  = esc_html__( 'Add-ons', 'latepoint' );
		$licenseL10n = esc_html_tx__( 'Licenses', 'latepoint-addons' );

		if ( ! empty( $settingsActionRouteName ) ) {
			$settingsL10n  = esc_html__( 'Settings', 'latepoint' );
			$actionLinks[] = "<a href='admin.php?page=latepoint&route_name=$settingsActionRouteName' target='_blank'>$settingsL10n</a>";
		}

		$actionLinks = array_merge( $actionLinks, [
			"<a href='admin.php?page=latepoint&route_name=tech_xela_addons__index' target='_blank'>$addonsL10n</a>",
			"<a href='admin.php?page=latepoint&route_name=tech_xela_license__index' target='_blank'>$licenseL10n</a>",
		] );

		add_filter( "plugin_action_links_$pluginBaseName", function ( $links ) use ( $actionLinks ) {
			return array_merge( $links, $actionLinks );
		} );

		add_filter( "network_admin_plugin_action_links_$pluginBaseName", function ( $links ) use ( $actionLinks ) {
			return array_merge( $links, $actionLinks );
		} );

		// License Notice

		if ( OsRolesHelper::can_user( 'tx_updates__manage' ) ) {
			$licenseNotice = '';
			if ( ! \TechXelaLatePointLicenseHelper::isLicenseActive( $addonInstance->productId ) ) {
				$licenseMessage = sprintf( esc_html_tx__( 'License is missing or invalid. Activate your purchase code to receive free %s compatibility and security updates.%s Activate now%s', 'latepoint-addons' ),
					'<strong>' . $pluginData['Name'] . '</strong>',
					'<a href="admin.php?page=latepoint&route_name=tech_xela_license__index" target="_blank">',
					'</a>'
				);
				$licenseNotice  = '<tr class="plugin-update plugin-update-tr installer-plugin-update-tr js-otgs-plugin-tr active">
							<td colspan="4">
								<div class="update-message notice inline notice-error notice-alt">
									<p>
										' . $licenseMessage . '
									</p>
								</div>
							</td>
						</tr>';
			}

			// Update Notice

			$updateNotice = '';
			$current      = get_site_transient( 'update_plugins' );
			if ( isset( $current->response[ $addonInstance->pluginFilePath() ] ) ) {
				$response = $current->response[ $addonInstance->pluginFilePath() ];
				if ( isset( $response->new_version ) && ! empty( $response->new_version ) ) {
					$updateMessage = sprintf( esc_html_tx__( 'There is a new version of %s available. %sView version %s details%s or %supdate now%s.', 'latepoint-addons' ),
						'<strong>' . $pluginData['Name'] . '</strong>',
						'<a href="' . $pluginData['PluginURI'] . '" target="_blank">',
						$response->new_version,
						'</a>',
						'<a href="admin.php?page=latepoint&route_name=tech_xela_addons__index" target="_blank">',
						'</a>'
					);
					$updateNotice  = '<tr class="plugin-update plugin-update-tr installer-plugin-update-tr js-otgs-plugin-tr active">
							<td colspan="4">
								<div class="update-message notice inline notice-warning notice-alt">
									<p>
										' . $updateMessage . '
									</p>
								</div>
							</td>
						</tr>';
				}
			}

			add_action( "after_plugin_row_$pluginBaseName", function () use ( $pluginBaseName, $licenseNotice, $updateNotice ) {
				// Prevent default
				remove_action( "after_plugin_row_$pluginBaseName", 'wp_plugin_update_row' );

				echo( $licenseNotice );
				echo( $updateNotice );
			}, 8, 3 );
		}
	}
}


add_action( 'init', function () {
	if ( ! isset_tx_lp_global( 'laf_is_filtering_sms_processors' ) ) {
		set_tx_lp_global( 'laf_is_filtering_sms_processors', true );

		add_filter( 'latepoint_sms_processors', function ( $smsProcessors, $enabledOnly ) {
			$smsProcessors['tx_sms_addons'] = [
				'code'      => 'tx_sms_addons',
				'label'     => '',
				'image_url' => 'https://assets.techxela.com/techxela-logo.svg',
			];

			return $smsProcessors;
		}, 24, 2 );

		add_action( 'latepoint_sms_processor_settings', function ( $processorCode ) {
			if ( $processorCode == 'tx_sms_addons' ) {
				echo '<div class="tx_sms_addons_processor_settings">';
				echo \TechXelaLatePointAddonsHelper::generateMissingAddonLink(
					esc_html_tx__( 'Install Additional SMS Add-Ons', 'latepoint-addons' ),
					'https://1.envato.market/tx-lp-addons'
				);
				echo '</div>';
			}
		} );

		add_action( 'latepoint_admin_enqueue_scripts', function () {
			wp_add_inline_style( 'latepoint-main-admin', '#notificationProcessorToggler_tx_sms_addons .os-toggler-w{display: none!important} #toggleNotificationSettings_tx_sms_addons{display: block!important}' );
		} );

		add_filter( 'latepoint_process_action_settings_fields_html_after', function ( $html, $action ) {
			if ( $action->type == 'send_sms' ) {
				$smsProcessors = \OsSmsHelper::get_sms_processors();

				if ( empty( $smsProcessors ) ||
				     ( count( $smsProcessors ) == 1 && isset( $smsProcessors['tx_sms_addons'] ) ) ) {
					$html = \TechXelaLatePointAddonsHelper::generateMissingAddonLink(
						esc_html_tx__( 'Install Additional SMS Add-Ons', 'latepoint-addons' ),
						'https://1.envato.market/tx-lp-addons'
					);
				}
			}

			return $html;
		}, 25, 2 );
	}

	if ( ! isset_tx_lp_global( 'laf_is_filtering_external_meeting_systems' ) ) {
		set_tx_lp_global( 'laf_is_filtering_external_meeting_systems', true );

		add_filter( 'latepoint_list_of_external_meeting_systems', function ( $externalMeetingSystems, $enabledOnly ) {
			$externalMeetingSystems['tx_meeting_addons'] = [
				'code'      => 'tx_meeting_addons',
				'name'      => '',
				'label'     => '',
				'image_url' => 'https://assets.techxela.com/techxela-logo.svg',
			];

			return $externalMeetingSystems;
		}, 24, 2 );

		add_action( 'latepoint_external_meeting_system_settings', function ( $meetingSystemCode ) {
			if ( $meetingSystemCode == 'tx_meeting_addons' ) {
				echo \TechXelaLatePointAddonsHelper::generateMissingAddonLink(
					esc_html_tx__( 'Install Additional Meeting Add-Ons', 'latepoint-addons' ),
					'https://1.envato.market/tx-lp-addons'
				);
			}
		} );

		add_action( 'latepoint_admin_enqueue_scripts', function () {
			wp_add_inline_style( 'latepoint-main-admin', '.os-toggler-w:has(input[id="settings_enable_tx_meeting_addons"]){display:none!important;}#toggleMeetingSystemSettings_tx_meeting_addons{display:block!important;}' );
		} );
	}

	if ( ! isset_tx_lp_global( 'laf_is_filtering_external_marketing_systems' ) ) {
		set_tx_lp_global( 'laf_is_filtering_external_marketing_systems', true );

		add_filter( 'latepoint_list_of_external_marketing_systems', function ( $externalMarketingSystems, $enabledOnly ) {
			$externalMarketingSystems['tx_marketing_addons'] = [
				'code'      => 'tx_marketing_addons',
				'name'      => '',
				'label'     => '',
				'image_url' => 'https://assets.techxela.com/techxela-logo.svg',
			];

			return $externalMarketingSystems;
		}, 24, 2 );

		add_action( 'latepoint_external_marketing_system_settings', function ( $marketingSystemCode ) {
			if ( $marketingSystemCode == 'tx_marketing_addons' ) {
				echo \TechXelaLatePointAddonsHelper::generateMissingAddonLink(
					esc_html_tx__( 'Install Additional Marketing Add-Ons', 'latepoint-addons' ),
					'https://1.envato.market/tx-lp-addons'
				);
			}
		} );

		add_action( 'latepoint_admin_enqueue_scripts', function () {
			wp_add_inline_style( 'latepoint-main-admin', '.os-toggler-w:has(input[id="settings_enable_tx_marketing_addons"]){display:none!important;}#toggleMarketingSystemSettings_tx_marketing_addons{display:block!important;}' );
		} );
	}

	if ( ! isset_tx_lp_global( 'laf_is_filtering_external_calendars' ) ) {
		set_tx_lp_global( 'laf_is_filtering_external_calendars', true );

		add_filter( 'latepoint_list_of_external_calendars', function ( $externalCalendars, $enabledOnly ) {
			$externalCalendars['tx_calendar_addons'] = [
				'code'      => 'tx_calendar_addons',
				'name'      => '',
				'label'     => '',
				'image_url' => 'https://assets.techxela.com/techxela-logo.svg',
			];

			return $externalCalendars;
		}, 24, 2 );

		add_action( 'latepoint_external_calendar_settings', function ( $calendarCode ) {
			if ( $calendarCode == 'tx_calendar_addons' ) {
				echo \TechXelaLatePointAddonsHelper::generateMissingAddonLink(
					esc_html_tx__( 'Install Additional Calendar Add-Ons', 'latepoint-addons' ),
					'https://1.envato.market/tx-lp-addons'
				);
			}
		} );

		add_action( 'latepoint_admin_enqueue_scripts', function () {
			wp_add_inline_style( 'latepoint-main-admin', '.os-toggler-w:has(input[id="settings_enable_tx_calendar_addons"]){display:none!important;}#toggleCalendarSettings_tx_calendar_addons{display:block!important;}' );
		} );
	}
} );