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

if ( ! class_exists( 'TechXelaLatePointSmsDatabaseHelper' ) ) :

	final class TechXelaLatePointSmsDatabaseHelper {
		public static function addonsSqls( $sqls ) {
			if ( version_compare( LATEPOINT_VERSION, '4.7.5', 'ge' ) ) {
				foreach ( \TechXelaLatePointSmsTemplatesHelper::getFormBlocksArr() as $templateId => $template ) {
					if ( isset( $template['title'] ) ) {
						$template['name'] = $template['title'];
						unset( $template['title'] );
						$template['content'] = OsUtilHelper::replace_single_curly_with_double( $template['content'] );
						\TechXelaLatePointSmsTemplatesHelper::save( $template );
					}
				}

				if ( \TechXelaLatePointSmsSettingsHelper::getSetting( 'qsms', 'enabled', false ) === false ) {
					\TechXelaLatePointSmsSettingsHelper::saveSetting( 'qsms', 'enabled', 'on' );
				}
				\TechXelaLatePointSmsSettingsHelper::deleteSetting( 'qsms', 'allow_agents' );

				try {
					global $wpdb;
					$legacyLogsTable = $wpdb->prefix . 'latepoint_techxela_sms_logs';
					if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $legacyLogsTable ) ) === $legacyLogsTable ) {
						$wpdb->query( "DROP TABLE {$legacyLogsTable};" );
					}
				} catch ( \Throwable $exception ) {
				}
			}

			return $sqls;
		}
	}

endif;
