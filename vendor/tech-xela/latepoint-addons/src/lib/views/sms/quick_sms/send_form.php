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

<div class="os-form-w tx-qsms-send-form">
    <h3><?php echo( esc_html_tx__( 'Quick SMS - Appointment #', 'latepoint-addons' ) . $booking->id ); ?></h3>
    <div class="latepoint-message latepoint-message-subtle">
        <div><?php esc_html_tx_e( 'You can use agent, customer and appointment variables in your message content.', 'latepoint-addons' ); ?><?php echo OsUtilHelper::template_variables_link_html(); ?></div>
    </div>
	<?php if ( $sentCount = $booking->get_meta_by_key( TECHXELA_LATEPOINT_ADDONS_SMS_QSMS_COUNT_BOOKING_META_KEY, 0 ) ) { ?>
        <div class="latepoint-message latepoint-message-info">
            <div><?php printf( esc_html_tx__( 'Quick SMS messages sent for this booking: %s', 'latepoint-addons' ), $sentCount ); ?></div>
        </div>
	<?php } ?>
    <form action=""
          data-os-action="<?php echo OsRouterHelper::build_route_name( 'tech_xela_quick_sms', 'sendSms' ); ?>"
          data-os-after-call="txQsmsCheckSentCount"
          data-os-pass-response="true">
        <div class="os-row">
            <div class="os-col-12">
				<?php echo OsFormHelper::select_field(
					'template_id',
					esc_html_tx__( 'Template', 'latepoint-addons' ),
					TechXelaLatePointSmsTemplatesHelper::getTemplatesOptionsList(),
					'',
					[ 'class' => 'tx-qsms-sf-template-select' ]
				) ?>
            </div>
            <div class="os-col-12">
				<?php echo OsFormHelper::textarea_field(
					'message',
					esc_html_tx__( 'Message', 'latepoint-addons' ),
					'',
					[ 'theme' => 'bordered', 'rows' => 5, 'class' => 'tx-qsms-sf-message-field' ],
				) ?>
            </div>
			<?php if ( \OsRolesHelper::can_user( 'quick_sms__manage' ) && isset( $booking ) ) { ?>
                <div class="os-col-12">
					<?php echo OsFormHelper::select_field(
						'recipient_type',
						esc_html_tx__( 'Send To', 'latepoint-addons' ),
						[
							[ 'value' => 'customer', 'label' => esc_html__( 'Customer', 'latepoint' ) ],
							[ 'value' => 'agent', 'label' => esc_html__( 'Agent', 'latepoint' ) ]
						]
					) ?>
                </div>
			<?php } ?>
        </div>
		<?php echo OsFormHelper::hidden_field( 'booking_id', $booking->id ); ?>
		<?php echo OsFormHelper::button( 'submit', esc_html_tx__( 'Send SMS', 'latepoint-addons' ), 'submit', [ 'class' => 'latepoint-btn latepoint-btn-md latepoint-btn-block latepoint-btn-primary' ] ); ?>
    </form>
</div>