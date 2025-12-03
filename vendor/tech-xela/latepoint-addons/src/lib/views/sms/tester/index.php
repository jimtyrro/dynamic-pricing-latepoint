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
?>

<div class="latepoint-settings-w os-form-w">
    <form action=""
          data-os-action="<?php echo OsRouterHelper::build_route_name( 'tech_xela_sms', 'sendTestSms' ); ?>">
        <div class="white-box">
            <div class="white-box-header">
                <div class="os-form-sub-header">
                    <h3><?php esc_html_tx_e( 'Compose New SMS', 'latepoint-addons' ); ?></h3></div>
            </div>
            <div class="white-box-content">
                <div class="os-row">
                    <div class="os-col">
						<?php echo OsFormHelper::phone_number_field(
							'to',
							esc_html_tx__( 'Recipient', 'latepoint-addons' )
						); ?>
                    </div>
                </div>
                <div class="os-row">
                    <div class="os-col">
						<?php echo OsFormHelper::textarea_field(
							'message',
							esc_html_tx__( 'Message', 'latepoint-addons' ),
							'',
							[ 'theme' => 'bordered', 'rows' => 5 ]
						); ?>
                    </div>
                </div>
            </div>
        </div>
		<?php echo OsFormHelper::button( 'submit', esc_html_tx__( 'Send SMS', 'latepoint-addons' ), 'submit', [ 'class' => 'latepoint-btn latepoint-btn-lg latepoint-btn-block' ] ); ?>
    </form>
</div>