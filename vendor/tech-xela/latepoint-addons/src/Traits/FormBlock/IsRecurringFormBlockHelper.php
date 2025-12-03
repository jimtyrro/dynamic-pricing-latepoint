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

namespace TechXela\LatePointAddons\Traits\FormBlock;

if ( ! trait_exists( 'TechXela\LatePointAddons\Traits\FormBlock\IsRecurringFormBlockHelper' ) ) {
	trait IsRecurringFormBlockHelper {
		use IsSchedulableFormBlockHelper;

		public static function getRecurrenceUnits(): array {
			return apply_filters( 'tx_latepoint_form_block_recurrence_units', [
				'H' => esc_html_tx__( 'Hours', 'latepoint-addons' ),
				'D' => esc_html_tx__( 'Days', 'latepoint-addons' ),
				'W' => esc_html_tx__( 'Weeks', 'latepoint-addons' ),
				'M' => esc_html_tx__( 'Months', 'latepoint-addons' ),
				'Y' => esc_html_tx__( 'Years', 'latepoint-addons' )
			] );
		}
	}
}