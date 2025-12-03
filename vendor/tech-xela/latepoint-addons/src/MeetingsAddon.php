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

namespace TechXela\LatePointAddons;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\TechXela\LatePointAddons\MeetingsAddon' ) ) {

	abstract class MeetingsAddon extends CoreAddon {
		/**
		 * Base Meetings Addon information.
		 */
		public $meetingSystemCode;
		public $meetingSystemName;
		public $meetingSystemLogoImg;
		public $meetingSystemIconImg;

		final protected function includes() {
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/meetings/meeting_helper_iface.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/meetings/settings_helper.php';
			$this->meetingsAddonIncludes();
		}

		/**
		 * Payments addon-specific includes
		 */
		protected function meetingsAddonIncludes() {
		}

		final protected function initHooks() {
			add_filter( 'latepoint_list_of_external_meeting_systems', [
				$this,
				'listOfExternalMeetingSystems'
			], 1, 2 );

			add_action( 'latepoint_external_meeting_system_settings', [
				$this,
				'externalMeetingSystemSettings'
			] );

			add_action( 'latepoint_service_form_after', [ $this, 'serviceFormAfter' ], 9 );

			add_action( 'latepoint_service_saved', [ $this, 'serviceSaved' ], 10, 3 );

			add_action( 'latepoint_booking_created', [ $this, 'bookingCreated' ], 11 );

			add_action( 'latepoint_booking_updated', [ $this, 'bookingUpdated' ], 11, 2 );

			add_action( 'latepoint_booking_will_be_deleted', [ $this, 'bookingWillBeDeleted' ], 9 );

			add_action( 'latepoint_booking_quick_form_after', [ $this, 'bookingQuickFormAfter' ], 9 );

			add_action( 'latepoint_customer_dashboard_after_booking_info_tile', [
				$this,
				'customerDashboardAfterBookingInfoTile'
			], 9 );

			add_action( 'latepoint_available_vars_booking', [ $this, 'availableVarsBooking' ] );

			add_filter( 'latepoint_replace_booking_vars', [ $this, 'replaceBookingVars' ], 10, 5 );

			$this->meetingsAddonInitHooks();
		}

		final protected function latePointInit() {
			$this->meetingsAddonLatePointInit();
		}

		protected function meetingsAddonLatePointInit() {
		}

		protected function meetingsAddonInitHooks() {
		}

		final public function listOfExternalMeetingSystems( $externalMeetingSystems, $enabledOnly ) {
			$externalMeetingSystems[ $this->meetingSystemCode ] = [
				'code'      => $this->meetingSystemCode,
				'name'      => $this->meetingSystemName,
				'image_url' => \TechXelaLatePointMeetingsSettingsHelper::getMeetingSystemLogoUrl( get_class( $this ) ),
			];

			return $externalMeetingSystems;
		}

		final public function externalMeetingSystemSettings( $meetingSystemCode ) {
			if ( $meetingSystemCode === $this->meetingSystemCode ) {
				$this->meetingsAddonSystemSettings();
			}
		}

		protected function meetingsAddonSystemSettings() {
		}

		final public function serviceFormAfter( $service ) {
			$this->meetingsAddonServiceFormAfter( $service );
		}

		protected function meetingsAddonServiceFormAfter( $service ) {
		}

		final public function serviceSaved( \OsServiceModel $service, $isNewRecord, $serviceParams ) {
			$this->meetingsAddonServiceSaved( $service, $isNewRecord, $serviceParams );
		}

		protected function meetingsAddonServiceSaved( \OsServiceModel $service, $isNewRecord, $serviceParams ) {
		}

		final public function bookingCreated( $booking ) {
			$this->meetingsAddonBookingCreated( $booking );
		}

		protected function meetingsAddonBookingCreated( $booking ) {
		}

		final public function bookingUpdated( $booking, $oldBooking ) {
			$this->meetingsAddonBookingUpdated( $booking, $oldBooking );
		}

		protected function meetingsAddonBookingUpdated( $booking, $oldBooking ) {
		}

		final public function bookingWillBeDeleted( $bookingId ) {
			$this->meetingsAddonBookingWillBeDeleted( $bookingId );
		}

		protected function meetingsAddonBookingWillBeDeleted( $bookingId ) {
		}

		final public function bookingQuickFormAfter( $booking ) {
			$this->meetingsAddonBookingQuickFormAfter( $booking );
		}

		protected function meetingsAddonBookingQuickFormAfter( $booking ) {
		}

		final public function availableVarsBooking() {
			$this->meetingsAddonAvailableVarsBooking();
		}

		protected function meetingsAddonAvailableVarsBooking() {
		}

		final public function replaceBookingVars( $text, $booking, $originalText, $needles, $replacements ) {
			return $this->meetingsAddonReplaceBookingVars( $text, $booking, $originalText, $needles, $replacements );
		}

		protected function meetingsAddonReplaceBookingVars( $text, $booking, $originalText, $needles, $replacements ) {
			return $text;
		}

		final public function customerDashboardAfterBookingInfoTile( $booking ) {
			$this->meetingsAddonCustomerDashboardAfterBookingInfoTile( $booking );
		}

		protected function meetingsAddonCustomerDashboardAfterBookingInfoTile( $booking ) {
		}

		private function __loadAdminScriptsAndStyles() {
			wp_enqueue_style(
				'techxela-latepoint-addons-meetings-admin',
				$this->frameworkSrcUrl() . '/public/css/meetings-admin.css',
				[],
				self::$frameworkVersion
			);
		}

		protected function loadAdminScriptsAndStyles() {
			$this->__loadAdminScriptsAndStyles();
			$this->loadMeetingsAddonAdminScriptsAndStyles();
		}

		protected function loadMeetingsAddonAdminScriptsAndStyles() {
		}

		private function __loadFrontScriptsAndStyles() {
			wp_enqueue_style(
				'techxela-latepoint-addons-meetings-front',
				$this->frameworkSrcUrl() . '/public/css/meetings-front.css',
				[],
				self::$frameworkVersion
			);
		}

		protected function loadFrontScriptsAndStyles() {
			$this->__loadFrontScriptsAndStyles();
			$this->loadMeetingsAddonFrontScriptsAndStyles();
		}

		protected function loadMeetingsAddonFrontScriptsAndStyles() {
		}

		final public static function getMeetingAddons(): array {
			return parent::getChildAddonClasses( self::class );
		}
	}

}