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

if ( ! class_exists( 'OsTechXelaFormController' ) ) {

	class OsTechXelaFormController extends OsController {
		public function __construct() {
			parent::__construct();

			$this->views_folder            = TECHXELA_LATEPOINT_ADDONS_CORE_VIEWS_ABSPATH;
			$this->action_access['public'] = array_merge( $this->action_access['public'], [ 'datePickerForm' ] );
		}

		public function datePickerForm() {
			$this->set_layout( 'none' );
			$this->vars['date_is_preselected'] = isset( $this->params['date']['value'] );
			$this->vars['date']                = \TechXelaLatePointDateTimeHelper::createDateFromImplicitFormat( $this->params['date'] ?? false, true );
			$this->vars['earliest_year']       = $this->params['earliest_year'];
			$this->vars['latest_year']         = $this->params['latest_year'];
			$this->vars['input_field_id']      = $this->params['input_field_id'];
			$this->format_render( 'form_fields/date_picker' );
		}
	}

}