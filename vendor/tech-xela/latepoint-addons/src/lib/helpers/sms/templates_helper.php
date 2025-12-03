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

if ( ! class_exists( 'TechXelaLatePointSmsTemplatesHelper' ) ) :

	final class TechXelaLatePointSmsTemplatesHelper {
		use \TechXela\LatePointAddons\Traits\FormBlock\IsFormBlockHelper;

		public static $settingsHelperClass = TechXelaLatePointSmsSettingsHelper::class;

		public static $formBlocksAreDraggable = false;
		public static $formBlocksDbOptionPrefix = 'qsms';
		public static $formBlocksDbOptionName = 'templates';
		public static $formBlocksFormArrName = 'templates';
		public static $formBlockSettingPrefix = 'tpl';
		public static $formBlocksRouteController = 'tech_xela_quick_sms';

		public static function customFormBlockProperties(): array {
			return [
				'content' => '',
			];
		}

		protected static function getValidationErrors( $formBlock, $errors ): array {
			if ( empty( $formBlock['content'] ) ) {
				$errors[] = esc_html_tx__( 'Content cannot be empty', 'latepoint-addons' );
			}

			return [ $formBlock, $errors ];
		}

		public static function afterFormBlockNameSection( array $formBlock ): void {
			?>
            <div class="sub-section-row">
                <div class="sub-section-label">
                    <h3><?php esc_html_tx_e( 'Content', 'latepoint-addons' ); ?></h3>
                </div>
                <div class="sub-section-content">
					<?php echo OsFormHelper::textarea_field( self::generateSettingName( $formBlock, 'content' ),
						false,
						$formBlock['content'],
						[ 'theme' => 'bordered', 'rows' => 5 ] );
					?>
                </div>
            </div>
			<?php
		}

		public static function getTemplatesOptionsList(): array {
			$templates = [ [ 'value' => '', 'label' => esc_html_tx__( 'Select a template', 'latepoint-addons' ) ] ];

			foreach ( self::getFormBlocksArr() as $template ) {
				if ( $template['enabled'] == 'on' ) {
					$templates[] = [
						'value' => $template['id'],
						'label' => $template['name']
					];
				}
			}

			return $templates;
		}
	}

endif;