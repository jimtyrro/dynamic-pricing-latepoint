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

if ( ! trait_exists( 'TechXela\LatePointAddons\Traits\FormBlock\IsFormBlockHelper' ) ) {
	trait IsFormBlockHelper {

		/**
		 * @var \TechXela\LatePointAddons\Traits\IsSettingsHelper $settingsHelperClass
		 *
		 * @var bool $formBlocksAreDraggable
		 *
		 * @var string $formBlocksDbOptionPrefix
		 *
		 * @var string $formBlocksDbOptionName
		 *
		 * @var string $formBlocksFormArrName
		 *
		 * @var string $formBlockConditionSettingPrefix
		 *
		 * @var string $formBlockSettingPrefix
		 */

		/**
		 * @return array
		 */
		public static function customFormBlockProperties(): array {
			return [];
		}

		public static function getFormBlockType( array $formBlock, \OsController $controller = null ): string {
			return '';
		}

		public static function afterFormBlockNameSection( array $formBlock, \OsController $controller = null ): void {
		}

		public static function beforeFormBlockEnabledSection( array $formBlock, \OsController $controller = null ): void {
		}

		public static function hasValidationErrors( $formBlock ): array {
			$errors = [];

			if ( empty( self::getSettingValue( $formBlock, 'name' ) ) ) {
				$errors[] = esc_html__( 'Name cannot be blank', 'latepoint-addons' );
			}

			list( $formBlock, $errors ) = self::getValidationErrors( $formBlock, $errors );

			if ( \TechXelaLatePointUtilHelper::classUsesTrait( self::class, IsConditionalFormBlockHelper::class ) ) {
				foreach ( self::getSettingValue( $formBlock, 'conditions' ) as $conditionId => $conditionData ) {
					$formBlock['conditions'][ $conditionId ]['target_prop_type'] = self::getConditionTargetPropsList( $conditionData['target'] )[ $conditionData['target_prop'] ]['type'];
				}
			}

			if ( \TechXelaLatePointUtilHelper::classUsesTrait( self::class, IsSchedulableFormBlockHelper::class ) ) {
				if ( self::getSettingValue( $formBlock, 'scheduling' ) == 'on' ) {
					if ( empty( self::getSettingValue( $formBlock, 'schedule.from' ) ) || ! \TechXelaLatePointDateTimeHelper::isDateValid( self::getSettingValue( $formBlock, 'schedule.from' ) ) ) {
						$errors[] = esc_html_tx__( 'Schedule From must be a valid date', 'latepoint-addons' );
					}
					if ( empty( self::getSettingValue( $formBlock, 'schedule.to' ) ) || ! \TechXelaLatePointDateTimeHelper::isDateValid( self::getSettingValue( $formBlock, 'schedule.to' ) ) ) {
						$errors[] = esc_html_tx__( 'Schedule To must be a valid date', 'latepoint-addons' );
					}
				}

				if ( \TechXelaLatePointUtilHelper::classUsesTrait( self::class, IsRecurringFormBlockHelper::class ) ) {
					if ( ! empty( self::getSettingValue( $formBlock, 'recurrence.value' ) ) ) {
						$formBlock['recurrence']['value'] = intval( round( floatval( self::getSettingValue( $formBlock, 'recurrence.value' ) ) ) );
						if ( self::getSettingValue( $formBlock, 'recurrence.value' ) < 1 ) {
							$formBlock['recurrence']['value'] = 1;
						}
					}

					if ( ! empty( self::getSettingValue( $formBlock, 'recurrence.unit' ) ) && ! in_array( self::getSettingValue( $formBlock, 'recurrence.unit' ), array_keys( self::getRecurrenceUnits() ) ) ) {
						$formBlock['recurrence']['unit'] = array_key_first( self::getRecurrenceUnits() );
					}
				}
			}

			if ( empty( $errors ) ) {
				return [ $formBlock, false ];
			} else {
				return [ $formBlock, $errors ];
			}
		}

		protected static function getValidationErrors( $formBlock, $errors ): array {
			return [ $formBlock, $errors ];
		}

		public static function isFormBlockSatisfied( array $formBlock, \OsBookingModel $bookingObject = null ): bool {
			if ( ! self::isOn( $formBlock, 'enabled' ) || ! self::isCustomFormBlockSatisfied( $formBlock, $bookingObject ) ) {
				return false;
			}

			if ( \TechXelaLatePointUtilHelper::classUsesTrait( self::class, IsSchedulableFormBlockHelper::class ) ) {
				$scheduled = true;
				if ( self::isOn( $formBlock, 'scheduling' ) && ! empty( self::getSettingValue( $formBlock, 'schedule' ) ) ) {
					$nowDate      = ( new \OsWpDateTime() );
					$nowTimeStamp = $nowDate->getTimestamp();
					if ( ! empty( $scheduleFrom = self::getSettingValue( $formBlock, 'schedule.from' ) ) ) {
						$scheduleFromDate = \TechXelaLatePointDateTimeHelper::createDateFromImplicitFormat( $scheduleFrom, true );
						$scheduled        = $nowTimeStamp >= $scheduleFromDate->getTimestamp();
					}
					if ( ! empty( $scheduleTo = self::getSettingValue( $formBlock, 'schedule.to' ) ) ) {
						$scheduleToDate = \TechXelaLatePointDateTimeHelper::createDateFromImplicitFormat( $scheduleTo );
						$scheduled      = $scheduled && $nowTimeStamp <= $scheduleToDate->getTimestamp();
					}

					if ( \TechXelaLatePointUtilHelper::classUsesTrait( self::class, IsRecurringFormBlockHelper::class ) ) {
						// If `Schedule To` date has passed, enforce next schedule in the recurring series and try again
						if ( ! empty( $scheduleFromDate ) && ! empty( $scheduleToDate ) && $nowDate > $scheduleToDate &&
						     self::isOn( $formBlock, 'recurring' ) && ! empty( self::getSettingValue( $formBlock, 'recurrence' ) ) &&
						     ! empty( $recurrenceValue = self::getSettingValue( $formBlock, 'recurrence.value' ) ) &&
						     ! empty( $recurrenceUnit = self::getSettingValue( $formBlock, 'recurrence.unit' ) ) ) {
							$outFormat = \OsSettingsHelper::get_date_format();

							$formBlock['schedule']['from']['value'] = $scheduleFromDate
								->add( new \DateInterval( "P$recurrenceValue$recurrenceUnit" ) )
								->format( $outFormat );
							$formBlock['schedule']['from']['format'] = $outFormat;

							$formBlock['schedule']['to']['value']   = $scheduleToDate
								->add( new \DateInterval( "P$recurrenceValue$recurrenceUnit" ) )
								->format( $outFormat );
							$formBlock['schedule']['to']['format'] = $outFormat;

							self::save( $formBlock );

							return self::isFormBlockSatisfied( $formBlock, $bookingObject );
						}
					}
				}
				if ( ! $scheduled ) {
					return false;
				}
			}

			$allTrue = true;

			if ( \TechXelaLatePointUtilHelper::classUsesTrait( self::class, IsConditionalFormBlockHelper::class ) ) {
				if ( self::isOn( $formBlock, 'conditional' ) && ! empty( self::getSettingValue( $formBlock, 'conditions' ) ) ) {
					foreach ( self::getSettingValue( $formBlock, 'conditions' ) as $conditionId => $condition ) {
						$allTrue = ( ( $condition['gate'] == 'and' ) ?
							( $allTrue && self::isConditionSatisfied( $condition, $bookingObject ) ) :
							( $allTrue || self::isConditionSatisfied( $condition, $bookingObject ) ) );
					}
				}
			}

			return $allTrue;
		}

		protected static function isCustomFormBlockSatisfied( array $formBlock, \OsBookingModel $bookingObject = null ): bool {
			return true;
		}

		public static function generateFormBlockId(): string {
			return self::$formBlockSettingPrefix . '_' . \OsUtilHelper::random_text( 'alnum', 8 );
		}

		public static function generateSettingName( $formBlock, $param ): string {
			$settingSuffix = '';
			$params        = explode( '.', $param );

			foreach ( $params as $param ) {
				$settingSuffix .= "[$param]";
			}

			return self::$formBlocksFormArrName . "[{$formBlock['id']}]$settingSuffix";
		}

		/**
		 * @param array|string $formBlock FormBlock ID (string) or Instance (array)
		 * @param string $param
		 * @param mixed $default
		 *
		 * @return array|false|mixed|string
		 */
		public static function getSettingValue( $formBlock, string $param, $default = '' ) {
			if ( is_string( $formBlock ) ) {
				$formBlock = self::get( $formBlock );
			}

			if ( is_array( $formBlock ) ) {
				$settingValue = $formBlock;
				$params       = explode( '.', $param );

				foreach ( $params as $param ) {
					if ( $settingValue ) {
						$settingValue = $settingValue[ $param ] ?? false;
					}
				}

				return $settingValue ?: $default;
			}

			return $default;
		}

		public static function isOn( $formBlock, $param ): bool {
			return self::getSettingValue( $formBlock, $param, 'off' ) == 'on';
		}

		public static function getFormBlocksArr() {
			$formBlocks = self::$settingsHelperClass::getSetting( self::$formBlocksDbOptionPrefix ?? '', self::$formBlocksDbOptionName, '[]' );
			if ( $formBlocks ) {
				return json_decode( $formBlocks, true );
			} else {
				return [];
			}
		}

		public static function saveFormBlocksArr( $formBlocksArr ): bool {
			return self::$settingsHelperClass::saveSetting( self::$formBlocksDbOptionPrefix ?? '', self::$formBlocksDbOptionName, json_encode( $formBlocksArr ) );
		}

		public static function save( $formBlock ): bool {
			$formBlocks  = self::getFormBlocksArr();
			$formBlockId = $formBlock['id'] ?? '';

			if ( empty( $formBlockId ) ) {
				$formBlockId = self::generateFormBlockId();

				if ( self::exists( $formBlockId ) ) {
					$formBlock['id'] = '';
					self::save( $formBlock );
				}
			}

			$formBlocks[ $formBlockId ] = $formBlock;

			return self::saveFormBlocksArr( $formBlocks );
		}

		public static function exists( $formBlockId ): bool {
			return isset( self::getFormBlocksArr()[ $formBlockId ] );
		}

		public static function get( $formBlockId ) {
			$formBlocks = self::getFormBlocksArr();

			return $formBlocks[ $formBlockId ] ?? false;
		}

		public static function delete( $formBlockId ): bool {
			if ( ! empty( $formBlockId ) ) {
				$formBlocks = self::getFormBlocksArr();

				if ( isset( $formBlocks[ $formBlockId ] ) ) {
					unset( $formBlocks[ $formBlockId ] );

					return self::saveFormBlocksArr( $formBlocks );
				}

				return true;
			} else {
				return false;
			}
		}
	}
}