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

if ( ! class_exists( 'TechXelaLatePointSmsActivitiesHelper' ) ) :

	final class TechXelaLatePointSmsActivitiesHelper {
		public static function activityViewVars( $vars, $activity ) {
			if ( $activity->code == 'sms_sent' ) {
				$activityData = json_decode( $activity->description, true );

				$vars['status_html'] .= '<div class="status-item">' .
				                        esc_html__( 'Processor', 'latepoint' ) . ': <strong>' .
				                        $activityData['processor_name'] . '</strong></div>';


				if ( ! empty( $activityData['errors'] ) || ! empty( $activityData['extra_data'] ) ) {
					$vars['content_html'] .= '</div>';

					if ( ! empty( $activityData['errors'] ) ) {
						$vars['content_html'] .= '<div class="os-section-header">' . esc_html_tx__( 'Errors', 'latepoint-addons' ) .
						                         '</div><pre class="format-json">' . json_encode( $activityData['errors'], JSON_PRETTY_PRINT ) . '</pre>';
					}

					if ( ! empty( $activityData['extra_data'] ) ) {
						$vars['content_html'] .= '<div class="os-section-header">' . esc_html_tx__( 'Extra Data', 'latepoint-addons' ) .
						                         '</div><pre class="format-json">' . json_encode( $activityData['extra_data'], JSON_PRETTY_PRINT ) . '</pre>';
					}
				}
			}

			return $vars;
		}
	}

endif;
