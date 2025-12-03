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

if ( ! class_exists( 'TechXelaLatePointFormHelper' ) ) :

	class TechXelaLatePointFormHelper {
		/**
		 * @param string $name
		 * @param string $label
		 * @param array|string $value
		 * @param string $format
		 * @param bool $asPeriod
		 * @param array $atts
		 * @param array $wrapperAtts
		 *
		 * @return string
		 */
		public static function datePickerField( string $name, string $label, $value = false, $format = false, bool $asPeriod = false, array $atts = [], array $wrapperAtts = [] ): string {
			$html         = '';
			$extraClasses = '';
			$outFormat    = \OsSettingsHelper::get_date_format();

			if ( empty( $value ) ) {
				$value = \OsTimeHelper::today_date( $outFormat );
			} elseif ( ! is_array( $value ) && ! empty( $format ) ) {
				$value = \OsWpDateTime::os_createFromFormat( $format, $value )->format( $outFormat );
			} else {
				$value = \TechXelaLatePointDateTimeHelper::createDateFromImplicitFormat( $value )->format( $outFormat );
			}

			if ( $asPeriod ) {
				$extraClasses = 'as-period';
			}

			$earliestYear = $atts['earliest_year'] ?? false;
			if ( ! $earliestYear || ! OsWpDateTime::os_createFromFormat( 'Y', $earliestYear ) ) {
				$earliestYear = intval( date( 'Y' ) - 100 );
			}
			$latestYear = $atts['latest_year'] ?? false;
			if ( ! $latestYear || ! OsWpDateTime::os_createFromFormat( 'Y', $latestYear ) ) {
				$latestYear = intval( date( 'Y' ) ) + 100;
			}

			if ( ! isset( $atts['id'] ) && ! isset( $atts['skip_id'] ) ) {
				$atts['id'] = \OsFormHelper::name_to_id( $name, $atts );
			}
			if ( ! empty( $wrapperAtts ) ) {
				$html = '<div ' . \OsFormHelper::atts_string_from_array( $wrapperAtts ) . '>';
			}
			$html .= '<div class="os-form-group os-form-group-bordered tx-date-group tx-date-input-w ' . $extraClasses . '">';
			if ( $label ) {
				$html .= '<label for="' . $atts['id'] . '">' . $label . '</label>';
			}
			$html .= '<input type="text" placeholder="' . $outFormat . '" name="' . $name . '[value]" value="' . $value . '" ' . \OsFormHelper::atts_string_from_array( $atts, [ 'class' => 'os-form-control os-mask-date tx-date-field' ] ) . '>';
			$html .= '<input type="hidden" name="' . $name . '[format]" value="' . $outFormat . '"/>';
			$html .= '<a class="latepoint-btn latepoint-btn-secondary latepoint-btn-just-icon tx-date-field-edit-btn" 
			data-os-after-call="techXelaLatePointCoreAdmin.initDatePickerForm" 
			data-os-lightbox-classes="width-700 latepoint-lightbox-nopad" 
			data-os-output-target="lightbox" 
			data-os-action="' . \OsRouterHelper::build_route_name( 'tech_xela_form', 'datePickerForm' ) . '"
			data-os-params="' . \OsUtilHelper::build_os_params( [
					'date'           => $value,
					'format'         => $outFormat,
					'input_field_id' => $atts['id'],
					'earliest_year'  => $earliestYear,
					'latest_year'    => $latestYear,
				] ) . '">
			<i class="latepoint-icon latepoint-icon-edit-2"></i>
			</a>';
			$html .= '</div>';
			if ( ! empty( $wrapperAtts ) ) {
				$html .= '</div>';
			}

			return $html;
		}

	}

endif;
