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

if ( ! class_exists( 'TechXelaLatePointSmsSettingsHelper' ) ) :

	class TechXelaLatePointSmsSettingsHelper {
		use \TechXela\LatePointAddons\Traits\IsSettingsHelper;

		const scopedOptionPrefix = 'notifications_sms';

		public static function isSmsProcessorEnabled( string $smsProcessorCode ): bool {
			return OsSmsHelper::is_sms_processor_enabled( $smsProcessorCode );
		}

		public static function settingsNotificationsOtherAfter() {
			if ( OsRolesHelper::can_user( 'quick_sms__manage' ) ) {
				?>
                <div class="white-box" id="stickySectionQuickSMS">
                    <div class="white-box-header">
                        <div class="os-form-sub-header">
                            <h3><?= esc_html_tx__( 'Quick SMS', 'latepoint-addons' ) ?></h3>
                        </div>
                    </div>
                    <div class="white-box-content no-padding">
                        <div class="sub-section-row">
                            <div class="sub-section-label">
                                <h3><?= esc_html__( 'Settings', 'latepoint' ) ?></h3>
                            </div>
                            <div class="sub-section-content">
								<?= OsFormHelper::toggler_field(
									\TechXelaLatePointSmsSettingsHelper::settingName( 'qsms', 'enabled' ),
									esc_html_tx__( 'Enable Quick SMS', 'latepoint-addons' ),
									\TechXelaLatePointSmsSettingsHelper::isOn( 'qsms', 'enabled' ),
									'quickSmsSettings'
								) ?>
                            </div>
                        </div>
                        <div id="quickSmsSettings">
                            <div class="sub-section-row"
                                 style="display: <?= \TechXelaLatePointSmsSettingsHelper::isOn( 'qsms', 'enabled' ) ? 'flex' : 'none' ?>;">
                                <div class="sub-section-label">
                                    <h3><?= esc_html_tx__( 'Templates', 'latepoint-addons' ) ?></h3>
                                </div>
                                <div class="sub-section-content">
                                    <div class="latepoint-message latepoint-message-subtle">
                                        <div><?php esc_html_tx_e( 'You can use variables in your template content. Just click on the variable with {} brackets and it will automatically copy to your buffer and you can simply paste it where you want to use it. It will be converted into a value for the agent/customer or appointment.', 'latepoint-addons' ); ?><?php echo OsUtilHelper::template_variables_link_html(); ?></div>
                                    </div>
									<?php ( new OsTechXelaQuickSmsController() )->formBlocksContainer(); ?>
                                </div>
                            </div>
                            <div class="sub-section-row">
                                <div class="sub-section-label">
                                    <h3><?php esc_html_tx_e( 'Other Settings', 'latepoint-addons' ); ?></h3>
                                </div>
                                <div class="sub-section-content">
									<?= OsFormHelper::toggler_field(
										\TechXelaLatePointSmsSettingsHelper::settingName( 'qsms', 'indicate_sent' ),
										esc_html_tx__( 'Indicate sent quick SMS', 'latepoint-addons' ),
										\TechXelaLatePointSmsSettingsHelper::isOn( 'qsms', 'indicate_sent', 'on' ),
										false, false,
										[ 'sub_label' => esc_html_tx__( 'Change the Quick SMS button color to green (on the calendar), indicating that quick sms message(s) have been sent.', 'latepoint-addons' ) ]
									) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
		}
	}

endif;