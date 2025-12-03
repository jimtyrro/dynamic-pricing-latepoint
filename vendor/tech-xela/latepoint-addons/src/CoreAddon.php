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

if ( ! class_exists( '\TechXela\LatePointAddons\CoreAddon' ) ) {
	abstract class CoreAddon {

		/**
		 * Core Addon information.
		 *
		 */
		public $productId;
		public $version;
		public $dbVersion;
		public static $frameworkVersion = '16.13.0';
		public $addonSlug;
		public $addonName;
		public $settingsRouteName;

		/**
		 * Core Addon Constructor.
		 */
		public function __construct() {
			$this->_defineConstants();
			$this->_includes();
			$this->_initHooks();

			if ( class_exists( '\OsDatabaseHelper' ) ) {
				\OsDatabaseHelper::check_db_version_for_addons();
			}

			if ( class_exists( '\TechXelaLatePointLicenseHelper' ) ) {
				\TechXelaLatePointLicenseHelper::checkForUpdate( $this->productId, $this->version );
			}
		}


		final protected function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Define core constants.
		 */
		private function _defineConstants() {
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH', dirname( __FILE__ ) . '/' );
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_CORE_VIEWS_ABSPATH', dirname( __FILE__ ) . '/lib/views/core/' );
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU', 1 );
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_TARGET_MENU_TYPE_MENU_SECTION', 2 );
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_BEFORE', 1 );
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_AFTER', 2 );
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_INTO', 3 );
			$this->define( 'TECHXELA_LATEPOINT_ADDONS_MENU_INSERT_MODE_REPLACE', 4 );
			$this->defineConstants();
		}

		/**
		 * Define addon-specific constants.
		 *
		 * Hint: use $this->define($name, $value)
		 */
		protected function defineConstants() {
		}

		private function __includes() {
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/addons_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/booking_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/booking_intent_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/date_time_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/debug_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/form_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/jungle_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/license_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/menu_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/model_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/processes_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/roles_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/service_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/settings_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/updates_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/util_helper.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/helpers/core/wp_helper.php';

			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/models/core/model.php';

			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/controllers/core/addons_controller.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/controllers/core/form_controller.php';
			include_once TECHXELA_LATEPOINT_ADDONS_CORE_ABSPATH . 'lib/controllers/core/license_controller.php';
		}

		/**
		 * Include required files used in admin and on the frontend.
		 */
		final public function _includes() {
			$this->__includes();
			$this->includes();
		}

		/**
		 * Include required, addon-specific files used in admin and on the frontend.
		 */
		protected function includes() {
		}

		final public function _initHooks() {
			add_action( 'latepoint_init', [ $this, '_latePointInit' ] );

			add_action( 'latepoint_includes', [ $this, '_includes' ] );

			add_filter( 'latepoint_installed_addons', [ $this, 'registerAddon' ], 0, 1 );

			add_action( 'latepoint_check_plugin_version', [ $this, 'checkPluginVersion' ] );

			add_action( 'latepoint_check_if_addons_update_available', [
				\TechXelaLatePointAddonsHelper::class,
				'checkAddonVersions'
			] );

			add_filter( 'latepoint_debug_list_of_addons', [
				\TechXelaLatePointAddonsHelper::class,
				'addTxAddonsToOsListOfAddons'
			] );

			add_filter( 'pre_set_site_transient_update_plugins', [
				\TechXelaLatePointUpdatesHelper::class,
				'checkPluginsLatestVersion'
			] );

			add_filter( 'latepoint_encrypted_settings', [ $this, '_encryptedSettings' ] );

			add_action( 'latepoint_admin_enqueue_scripts', [ $this, '_loadAdminScriptsAndStyles' ] );

			add_filter( 'latepoint_localized_vars_admin', [ $this, '_localizedVarsForAdmin' ] );

			add_action( 'latepoint_wp_enqueue_scripts', [ $this, '_loadFrontScriptsAndStyles' ] );

			add_filter( 'latepoint_localized_vars_front', [ $this, '_localizedVarsForFront' ] );

			add_filter( 'latepoint_side_menu', [ \TechXelaLatePointMenuHelper::class, 'sideMenu' ] );

			add_filter( 'admin_url', [ \TechXelaLatePointWPHelper::class, 'adminUrl' ], 10 );

			add_action( 'latepoint_model_save', [ \TechXelaLatePointModelHelper::class, 'modelSave' ], 9 );

			add_action( 'latepoint_model_will_be_deleted', [
				\TechXelaLatePointModelHelper::class,
				'modelWillBeDeleted'
			], 9 );

			add_action( 'latepoint_model_deleted', [
				\TechXelaLatePointModelHelper::class,
				'modelDeleted'
			], 9, 2 );

			add_filter( 'latepoint_model_options_for_multi_select', [
				\TechXelaLatePointModelHelper::class,
				'modelOptionsForMultiSelect'
			], 99, 2 );

			add_filter( 'latepoint_roles_get_all_available_actions_list', [
				\TechXelaLatePointRolesHelper::class,
				'getAllAvailableActionsList'
			], 20 );

			add_filter( 'latepoint_roles_action_names', [
				\TechXelaLatePointRolesHelper::class,
				'rolesActionNames'
			], 20, 2 );

			add_filter( 'latepoint_roles_action_descriptions', [
				\TechXelaLatePointRolesHelper::class,
				'rolesActionDescriptions'
			], 20, 2 );

			add_filter( 'latepoint_capabilities_for_controllers', [
				\TechXelaLatePointRolesHelper::class,
				'capabilitiesForControllers'
			], 20 );

			add_filter( 'latepoint_process_action_types', [
				\TechXelaLatePointProcessesHelper::class,
				'processActionTypes'
			], 8 );

			add_filter( 'latepoint_process_action_names', [
				\TechXelaLatePointProcessesHelper::class,
				'processActionNames'
			], 8 );

			add_filter( 'latepoint_process_action_settings_fields_html_after', [
				\TechXelaLatePointProcessesHelper::class,
				'processActionSettingsFieldsHtmlAfter'
			], 8, 2 );

			// addon specific pre-init hooks
			$this->initHooks();

			add_action( 'init', [ $this, '_init' ], 0 );

			register_activation_hook( __FILE__, [ $this, 'onActivate' ] );
			register_deactivation_hook( __FILE__, [ $this, 'onDeactivate' ] );
		}


		/**
		 * Core plugin initialization.
		 */
		private function __init() {
			$this->loadPluginTextDomain();
		}

		/**
		 * Plugin initialization.
		 */
		final public function _init() {
			$this->__init();
			$this->init();
		}

		/**
		 * Addon-specific plugin initialization.
		 */
		protected function init() {
		}

		/**
		 * Addon-specific hooks initialization
		 *
		 * @return void
		 */
		protected function initHooks() {
		}

		/**
		 * Set up localisation.
		 *
		 * @return void
		 */
		private function loadPluginTextDomain() {
			// Load framework strings
			if ( ! is_textdomain_loaded( 'latepoint-addons' ) ) {
				load_plugin_textdomain( 'latepoint-addons',
					true,
					"{$this->frameworkSrcRelPath()}/languages" );
			}

			// Load addon strings
			load_plugin_textdomain( $this->addonName,
				false,
				"$this->addonName/languages" );
		}

		public function onActivate() {
			if ( class_exists( '\TechXelaLatePointLicenseHelper' ) ) {
				\TechXelaLatePointLicenseHelper::checkForUpdate( $this->productId, $this->version );
			}
		}

		public function onDeactivate() {
			if ( class_exists( '\OsAddonsHelper' ) ) {
				\OsAddonsHelper::delete_addon_info( $this->addonName, $this->version );
			}
		}


		final public function registerAddon( $installedAddons ) {
			$installedAddons[] = [
				'name'       => $this->addonName,
				'db_version' => $this->dbVersion,
				'version'    => $this->version
			];

			return $installedAddons;
		}

		final public function checkPluginVersion() {
			\TechXelaLatePointLicenseHelper::verifyLicense( $this->productId, $this->version );
		}

		private function __encryptedSettings( $encryptedSettings ) {
			$encryptedSettings[] = \TechXelaLatePointSettingsHelper::prefixSetting(
				\TechXelaLatePointLicenseHelper::prefixLicenseSetting( $this->productId, 'license' )
			);

			return $encryptedSettings;
		}

		/**
		 * Define global options to be encrypted at rest.
		 *
		 * @param $encryptedSettings
		 *
		 * @return array
		 */
		final public function _encryptedSettings( $encryptedSettings ): array {
			$encryptedSettings = $this->__encryptedSettings( $encryptedSettings );

			return $this->encryptedSettings( $encryptedSettings );
		}

		/**
		 * Define addon-specific options to be encrypted at rest.
		 *
		 * @param $encryptedSettings
		 *
		 * @return array
		 */
		protected function encryptedSettings( $encryptedSettings ): array {
			return $encryptedSettings;
		}

		private function __latePointInit() {
			\TechXelaLatePointJungleHelper::applyMonkeyPatches();
		}

		/**
		 * LatePoint initialization
		 */
		final public function _latePointInit() {
			$this->__latePointInit();
			$this->latePointInit();
		}

		/**
		 * Addon-specific LatePoint initialization
		 */
		protected function latePointInit() {
		}

		private function __loadAdminScriptsAndStyles() {
			wp_enqueue_style(
				'techxela-latepoint-addons-core-admin',
				$this->frameworkSrcUrl() . '/public/css/core-admin.css',
				[ 'latepoint-main-admin' ],
				self::$frameworkVersion
			);
			wp_enqueue_script(
				'techxela-latepoint-addons-core-admin',
				$this->frameworkSrcUrl() . '/public/js/core-admin.min.js',
				[ 'jquery', 'latepoint-main-admin' ],
				self::$frameworkVersion
			);
			wp_enqueue_editor();
		}

		/**
		 * Enqueue scripts and styles for the admin panel.
		 */
		final public function _loadAdminScriptsAndStyles() {
			$this->__loadAdminScriptsAndStyles();
			$this->loadAdminScriptsAndStyles();
		}

		/**
		 * Enqueue addon-specific scripts and styles for the admin panel.
		 */
		protected function loadAdminScriptsAndStyles() {
		}

		private function __localizedVarsForAdmin( $localizedVars ) {
			$localizedVars['techxela_laf_default_titled_toggle_form_name']       = esc_html_tx__( 'Name', 'latepoint-addons' );
			$localizedVars['techxela_laf_form_block_condition_refresh_error']    = esc_html_tx__( 'Error encountered while refreshing condition. Please contact support.', 'dynamic-pricing-latepoint' );
			$localizedVars['techxela_laf_form_block_condition_deletion_warning'] = esc_html_tx__( 'You need to have at least one condition if conditional is enabled.', 'dynamic-pricing-latepoint' );

			return $localizedVars;
		}

		/**
		 * Define localized JS variables for the admin panel.
		 */
		final public function _localizedVarsForAdmin( $localizedVars ): array {
			$localizedVars = $this->__localizedVarsForAdmin( $localizedVars );

			return $this->localizedVarsForAdmin( $localizedVars );
		}

		/**
		 * Define addon-specific localized JS variables for the admin panel.
		 */
		protected function localizedVarsForAdmin( $localizedVars ) {
			return $localizedVars;
		}


		private function __loadFrontScriptsAndStyles() {
			wp_enqueue_style(
				'techxela-latepoint-addons-core-front',
				$this->frameworkSrcUrl() . '/public/css/core-front.css',
				[ 'latepoint-main-front' ],
				self::$frameworkVersion
			);
			\TechXelaLatePointJungleHelper::applyFrontSummaryStylePatches();
			wp_enqueue_script(
				'techxela-latepoint-addons-core-front',
				$this->frameworkSrcUrl() . '/public/js/core-front.min.js',
				[ 'jquery', 'latepoint-main-front' ],
				self::$frameworkVersion
			);
		}

		/**
		 * Enqueue scripts and styles for the frontend.
		 */
		final public function _loadFrontScriptsAndStyles() {
			$this->__loadFrontScriptsAndStyles();
			$this->loadFrontScriptsAndStyles();
		}

		/**
		 * Enqueue addon-specific scripts and styles for the frontend.
		 */
		protected function loadFrontScriptsAndStyles() {
		}

		private function __localizedVarsForFront( $localizedVars ) {
			return $localizedVars;
		}

		/**
		 * Define localized JS variables for the frontend.
		 */
		final public function _localizedVarsForFront( $localizedVars ): array {
			$localizedVars = $this->__localizedVarsForFront( $localizedVars );

			return $this->localizedVarsForFront( $localizedVars );
		}

		/**
		 * Define addon-specific localized JS variables for the frontend.
		 */
		protected function localizedVarsForFront( $localizedVars ) {
			return $localizedVars;
		}

		final public function pluginFilePath(): string {
			return join( "/", [ "$this->addonName", "$this->addonName.php" ] );
		}

		final public function frameworkSrcRelPath(): string {
			return $this->addonName . '/vendor/tech-xela/latepoint-addons/src';
		}

		final public function frameworkSrcUrl(): string {
			return plugins_url( '/vendor/tech-xela/latepoint-addons/src', $this->pluginFilePath() );
		}

		final public function pluginUrl( $path = '' ): string {
			return plugins_url( $path, $this->pluginFilePath() );
		}

		final public function publicCssUrl( $path = '' ): string {
			return $this->pluginUrl() . "/public/css/$path";
		}

		final public function publicJsUrl( $path = '' ): string {
			return $this->pluginUrl() . "/public/js/$path";
		}

		final public function publicImgUrl( $path = '' ): string {
			return $this->pluginUrl() . "/public/img/$path";
		}

		final public function enqueueStyle( $handleSuffix, $srcFilePath, $deps = [], $media = 'all' ) {
			wp_enqueue_style( "techxela-$this->addonName-$handleSuffix", $this->publicCssUrl( $srcFilePath ), $deps, $this->version, $media );
		}

		final public function enqueueAdminStyle( $handleSuffix = '', $srcFilePath = '', $deps = [], $media = 'all' ) {
			if ( empty( $handleSuffix ) ) {
				$handleSuffix = 'admin';
			}
			if ( empty( $srcFilePath ) ) {
				$srcFilePath = "$this->addonName-$handleSuffix.css";
			}
			$this->enqueueStyle( $handleSuffix, $srcFilePath, array_merge( [ 'techxela-latepoint-addons-core-admin' ], $deps ), $media );
		}

		final public function enqueueFrontStyle( $handleSuffix = '', $srcFilePath = '', $deps = [], $media = 'all' ) {
			if ( empty( $handleSuffix ) ) {
				$handleSuffix = 'front';
			}
			if ( empty( $srcFilePath ) ) {
				$srcFilePath = "$this->addonName-$handleSuffix.css";
			}
			$this->enqueueStyle( $handleSuffix, $srcFilePath, array_merge( [ 'techxela-latepoint-addons-core-front' ], $deps ), $media );
		}

		final public function enqueueScript( $handleSuffix, $srcFilePath, $deps = [], $inFooter = false ) {
			wp_enqueue_script( "techxela-$this->addonName-$handleSuffix", $this->publicJsUrl( $srcFilePath ), $deps, $this->version, $inFooter );
		}

		final public function enqueueAdminScript( $handleSuffix = '', $srcFilePath = '', $deps = [], $inFooter = false ) {
			if ( empty( $handleSuffix ) ) {
				$handleSuffix = 'admin';
			}
			if ( empty( $srcFilePath ) ) {
				$srcFilePath = "$this->addonName-$handleSuffix.min.js";
			}
			$this->enqueueScript( $handleSuffix, $srcFilePath, array_merge( [ 'techxela-latepoint-addons-core-admin' ], $deps ), $inFooter );
		}

		final public function enqueueFrontScript( $handleSuffix = '', $srcFilePath = '', $deps = [], $inFooter = false ) {
			if ( empty( $handleSuffix ) ) {
				$handleSuffix = 'front';
			}
			if ( empty( $srcFilePath ) ) {
				$srcFilePath = "$this->addonName-$handleSuffix.min.js";
			}
			$this->enqueueScript( $handleSuffix, $srcFilePath, array_merge( [ 'techxela-latepoint-addons-core-front' ], $deps ), $inFooter );
		}

		final public static function getChildAddonClasses( $parent = self::class ): array {
			$result = array();
			foreach ( get_declared_classes() as $class ) {
				if ( is_subclass_of( $class, $parent ) && ! ( new \ReflectionClass( $class ) )->isAbstract() ) {
					$result[] = $class;
				}
			}

			return $result;
		}
	}
}

$helperFile = dirname( __FILE__ ) . '/latepoint-addons-framework-helper.php';

if ( file_exists( $helperFile ) ) {
	require_once( $helperFile );
}