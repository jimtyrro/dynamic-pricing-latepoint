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

if ( ! class_exists( 'TechXelaLatePointSmsBookingHelper' ) ) :

	final class TechXelaLatePointSmsBookingHelper {
		public static function bookingQuickFormAfter( $booking ) {
			if ( $booking->exists() && \TechXelaLatePointSmsSettingsHelper::isOn( 'qsms', 'enabled' ) &&
                 OsRolesHelper::can_user( 'quick_sms__send' ) ) {
				?>
                <div class="tx-lp-qsms-w">
                    <div class="os-form-sub-header">
                        <h3><?php esc_html_tx_e( 'Quick SMS', 'latepoint-addons' ); ?></h3>
                    </div>
					<?php if ( $sentCount = $booking->get_meta_by_key( TECHXELA_LATEPOINT_ADDONS_SMS_QSMS_COUNT_BOOKING_META_KEY, 0 ) ) { ?>
                        <div class="latepoint-message latepoint-message-info">
                            <div><?php printf( esc_html_tx__( 'Quick SMS messages sent for this booking: %s', 'latepoint-addons' ), $sentCount ); ?></div>
                        </div>
					<?php } ?>
                    <div>
                        <a href="#"
                           data-os-action="<?php echo OsRouterHelper::build_route_name( 'tech_xela_quick_sms', 'sendForm' ) ?>"
                           data-os-params="<?php echo OsUtilHelper::build_os_params( [ 'booking_id' => $booking->id ] ); ?>"
                           data-os-output-target="lightbox"
                           data-os-after-call="techXelaLatePointQuickSmsAdmin.initSendForm"
                           class="latepoint-btn latepoint-btn-md latepoint-btn-block latepoint-btn-outline"><?php esc_html_tx_e( 'Compose Quick SMS', 'latepoint-addons' ); ?></a>
                    </div>
                </div>
				<?php
			}
		}

		public static function quickSmsBtnHtml( $bookingId = false ) {
			$html = '<a href="#" class="latepoint-btn latepoint-btn-sm latepoint-btn-primary tx-qsms-booking-box-send-btn">' . esc_html_tx__( 'Quick SMS', 'latepoint-addons' ) . '</a>';

			if ( $bookingId ) {
				$booking = new OsBookingModel( $bookingId );
				if ( $booking->exists() &&
				     $booking->get_meta_by_key( TECHXELA_LATEPOINT_ADDONS_SMS_QSMS_COUNT_BOOKING_META_KEY, 0 ) > 0 ) {
					$html = str_replace( 'latepoint-btn-primary', 'latepoint-btn-success', $html );
				}
			}

			return $html;
		}

	}

endif;