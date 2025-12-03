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

if ( ! class_exists( 'OsTechXelaQuickSmsController' ) ) :

	final class OsTechXelaQuickSmsController extends \OsController {
		use \TechXela\LatePointAddons\Traits\FormBlock\IsFormBlockController;

		public function __construct() {
			parent::__construct();

			$this->formBlockHelper            = TechXelaLatePointSmsTemplatesHelper::class;
			$this->logTagPrefix               = 'quick_sms_templates';
			$this->newFormBlockName           = esc_html_tx__( 'New Template', 'latepoint-addons' );
			$this->formBlockObjectName        = esc_html_tx__( 'Template', 'latepoint-addons' );
			$this->formBlockObjectNamePlural  = esc_html_tx__( 'Templates', 'latepoint-addons' );
			$this->formBlocksContainerClasses = ' tx-qsms-templates-w';
			$this->formBlocksAddBoxLabel      = esc_html_tx__( 'Add Quick SMS Template', 'latepoint-addons' );
		}

		public function sendForm() {
			$bookingId = $this->params['id'] ?? $this->params['booking_id'];
			$booking   = new OsBookingModel( $bookingId );

			if ( $booking->exists() ) {
				$this->vars['booking'] = $booking;
				$this->views_folder    = TECHXELA_LATEPOINT_ADDONS_SMS_VIEWS_ABSPATH;
				$this->set_layout( 'none' );
				$message = $this->format_render_return( 'quick_sms/send_form' );
				$status  = LATEPOINT_STATUS_SUCCESS;
			} else {
				$message = esc_html_tx__( 'Booking not found', 'latepoint-addons' );
				$status  = LATEPOINT_STATUS_ERROR;
			}

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( [ 'status' => $status, 'message' => $message ] );
			}
		}

		public function getTemplateContent() {
			$templateId = $this->params['id'];
			$template   = TechXelaLatePointSmsTemplatesHelper::get( $templateId );

			if ( $template ) {
				$message = $template['content'];
				$status  = LATEPOINT_STATUS_SUCCESS;
			} else {
				$message = esc_html_tx__( 'Template not found', 'latepoint-addons' );
				$status  = LATEPOINT_STATUS_ERROR;
			}

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( [ 'status' => $status, 'message' => $message ] );
			}
		}

		public function sendSms() {
			$booking = new OsBookingModel( $this->params['booking_id'] );

			if ( OsAuthHelper::get_highest_current_user_type() == LATEPOINT_USER_TYPE_AGENT && $booking->agent_id != OsAuthHelper::get_logged_in_agent_id() ) {
				$status  = LATEPOINT_STATUS_ERROR;
				$message = esc_html_tx__( 'You are not authorized to perform that action', 'latepoint-addons' );
			} else {
				$agent      = $booking->agent;
				$smsMessage = OsReplacerHelper::replace_all_vars( $this->params['message'], [
					'customer' => $booking->customer,
					'agent'    => $agent,
					'booking'  => $booking
				] );
				$recipients = [];

				if ( OsRolesHelper::can_user( 'quick_sms__manage' ) && isset( $this->params['recipient_type'] ) && $this->params['recipient_type'] == 'agent' ) {
					$recipients[] = $agent->phone;
					if ( ! empty( $agent->extra_phones ) ) {
						$extra_phones_arr = explode( ',', $agent->extra_phones );
						if ( ! empty( $extra_phones_arr ) ) {
							foreach ( $extra_phones_arr as $extra_phone ) {
								$recipients[] = $extra_phone;
							}
						}
					}
				} else {
					$recipients[] = $booking->customer->phone;
				}

				$result = OsSmsHelper::send_sms( implode( ',', $recipients ), $smsMessage, [
					'booking_id'  => $booking->id,
					'agent_id'    => $booking->agent_id,
					'service_id'  => $booking->service_id,
					'customer_id' => $booking->customer_id
				] );

				$sentCount = count( $result['sent_recipients'] );

				if ( $sentCount > 0 ) {
					$existingSentCount = intval( $booking->get_meta_by_key( TECHXELA_LATEPOINT_ADDONS_SMS_QSMS_COUNT_BOOKING_META_KEY, 0 ) );
					$booking->save_meta_by_key( TECHXELA_LATEPOINT_ADDONS_SMS_QSMS_COUNT_BOOKING_META_KEY, ( $existingSentCount + $sentCount ) );
				}

				$status  = $result['status'];
				$message = $result['message'];
			}

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( [
					'status'      => $status,
					'message'     => $message,
					'from_btn_id' => $this->params['tx_from_btn_id']
				] );
			}
		}

		public function getSentCount() {
			$bookingId = $this->params['id'] ?? $this->params['booking_id'];
			$booking   = new OsBookingModel( $bookingId );
			$status    = LATEPOINT_STATUS_SUCCESS;
			$message   = 0;

			if ( $booking->exists() ) {
				$message = intval( $booking->get_meta_by_key( TECHXELA_LATEPOINT_ADDONS_SMS_QSMS_COUNT_BOOKING_META_KEY, 0 ) );
			}

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( [ 'status' => $status, 'message' => $message ] );
			}
		}
	}

endif;
