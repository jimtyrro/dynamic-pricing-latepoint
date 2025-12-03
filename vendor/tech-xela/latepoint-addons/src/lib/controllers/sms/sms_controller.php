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

if ( ! class_exists( 'OsTechXelaSmsController' ) ) :

	class OsTechXelaSmsController extends \OsController {

		public function __construct() {
			parent::__construct();

			$this->views_folder        = TECHXELA_LATEPOINT_ADDONS_SMS_VIEWS_ABSPATH;
			$this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id( 'techxela_settings_menu' );
		}

		public function tester() {
			$this->format_render( 'tester/index' );
		}

		public function sendTestSms() {
			$to      = $this->params['to'];
			$content = $this->params['message'];

			$result = OsSmsHelper::send_sms( $to, $content );

			$this->send_json( [ 'status' => $result['status'], 'message' => $result['message'] ] );
		}
	}

endif;