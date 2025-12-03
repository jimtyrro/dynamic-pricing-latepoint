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

if ( ! trait_exists( 'TechXela\LatePointAddons\Traits\FormBlock\IsConditionalFormBlockHelper' ) ) {
	trait IsConditionalFormBlockHelper {
		use IsFormBlockHelper;

		/**
		 * @var string $formBlocksRouteController
		 */

		/**
		 * @param string $modelClass
		 * @param array $query
		 * @param array $labelFields
		 * @param bool $includeBlank
		 * @param string $blankLabel
		 * @param string $valueField
		 *
		 * @return array
		 */
		public static function getModelListForSelect( string $modelClass, array $query, array $labelFields, bool $includeBlank = false, string $blankLabel = '', string $valueField = 'id' ): array {
			$modelList = [];

			if ( $includeBlank ) {
				if ( empty( $blankLabel ) ) {
					$blankLabel = esc_html__( 'Nothing selected', 'latepoint-addons' );
				}
				$modelList[] = [
					'label' => $blankLabel,
					'value' => '__any__'
				];
			}

			if ( class_exists( $modelClass ) ) {
				$queryResults = ( new $modelClass() )->where( $query )->get_results_as_models();
				if ( ! empty( $queryResults ) ) {
					foreach ( $queryResults as $modelObject ) {
						$label = '';
						foreach ( $labelFields as $labelField ) {
							if ( ! empty( $labelField ) ) {
								if ( property_exists( $modelObject, $labelField ) ||
								     method_exists( $modelObject, "get_$labelField" ) ||
								     isset( $modelObject->$labelField ) ) {
									$modelFieldValue = $modelObject->$labelField;
									if ( ! empty( $modelFieldValue ) ) {
										$label .= ( empty( $label ) ? '' : ' ' ) . $modelFieldValue;
									}
								} else {
									$label .= $labelField;
								}
							}
						}
						$modelList[] = [
							'label' => $label,
							'value' => $modelObject->$valueField,
						];
					}
				}
			}

			$modelList = self::getCustomModelListForSelect( $modelList, $modelClass, $query, $labelFields, $includeBlank, $blankLabel, $valueField );

			return apply_filters( 'tx_latepoint_form_block_model_list_for_select', $modelList, $modelClass, $query, $labelFields, $includeBlank, $blankLabel, $valueField );
		}

		protected static function getCustomModelListForSelect( array $selectList, string $modelClass, array $query, array $labelFields, bool $includeBlank = false, string $blankLabel = '', string $valueField = 'id' ): array {
			return $selectList;
		}

		public static function getConditionTargets(): array {
			$conditionTargets = array_merge( [ 'booking' => esc_html__( 'Booking', 'latepoint' ), ],
				\TechXelaLatePointAddonsHelper::isOsAddonActive( 'custom-fields' ) ?
					[ 'booking_custom_field' => esc_html_tx__( 'Booking Custom Field', 'latepoint-addons' ) ] : [],
				[
					'service_category' => esc_html__( 'Service Category', 'latepoint' ),
					'service'          => esc_html__( 'Service', 'latepoint' ),
					'customer'         => esc_html__( 'Customer', 'latepoint' ),
				],
				\TechXelaLatePointAddonsHelper::isOsAddonActive( 'custom-fields' ) ?
					[ 'customer_custom_field' => esc_html_tx__( 'Customer Custom Field', 'latepoint-addons' ) ] : [],
				\TechXelaLatePointAddonsHelper::isTxAddonActive( 'mca' ) ?
					[ 'customer_address' => esc_html__( 'Customer Address', 'mca-latepoint' ) ] : [],
				[
					'agent'             => esc_html__( 'Agent', 'latepoint' ),
					'location_category' => esc_html_tx__( 'Location Category', 'latepoint-addons' ),
					'location'          => esc_html__( 'Location', 'latepoint' ),
				],
				\TechXelaLatePointAddonsHelper::isOsAddonActive( 'coupons' ) ?
					[ 'coupon' => esc_html_tx__( 'Coupon', 'latepoint-addons' ) ] : []
			);

			$conditionTargets = self::getCustomConditionTargets( $conditionTargets );

			return apply_filters( 'tx_latepoint_form_block_condition_targets', $conditionTargets );
		}

		protected static function getCustomConditionTargets( array $conditionTargets ): array {
			return $conditionTargets;
		}

		public static function getConditionTargetPropsList( string $target ): array {
			$propsList = [];

			switch ( $target ) {
				case 'booking':
				case \OsBookingModel::class:
					$propsList = [
						'start_date'      => [
							'label' => esc_html__( 'Start Date', 'latepoint' ),
							'type'  => 'date'
						],
						'end_date'        => [ 'label' => esc_html__( 'End Date', 'latepoint' ), 'type' => 'date' ],
						'start_time'      => [
							'label' => esc_html__( 'Start Time', 'latepoint' ),
							'type'  => 'time'
						],
						'end_time'        => [ 'label' => esc_html__( 'End Time', 'latepoint' ), 'type' => 'time' ],
						'payment_method'  => [
							'label'   => esc_html__( 'Payment Method', 'latepoint' ),
							'type'    => 'multi_select',
							'options' => 'OsPaymentsHelper::get_all_payment_methods_for_select'
						],
						'payment_portion' => [
							'label'   => esc_html__( 'Payment Portion', 'latepoint' ),
							'type'    => 'select',
							'options' => 'OsPaymentsHelper::get_payment_portions_list'
						],
						'duration'        => [
							'label' => esc_html__( 'Duration (minutes)', 'latepoint' ),
							'type'  => 'numeric'
						],
						'total_attendies' => [
							'label' => esc_html_tx__( 'Total Attendees', 'latepoint-addons' ),
							'type'  => 'numeric'
						]
					];
					if ( \TechXelaLatePointAddonsHelper::isOsAddonActive( 'service-extras' ) ) {
						$propsList = array_merge( $propsList, [
							'service_extras'       => [
								'label'   => esc_html__( 'Service Extras', 'latepoint-service-extras' ),
								'type'    => 'multi_select',
								'options' => \TechXela\LatePointAddons\Traits\FormBlock\IsConditionalFormBlockHelper::getServiceExtrasForSelect( true )
							],
							'service_extras_count' => [
								'label' => esc_html_tx__( 'Service Extras Count', 'latepoint-addons' ),
								'type'  => 'numeric'
							],
							'service_extras_cost'  => [
								'label' => esc_html_tx__( 'Service Extras Cost', 'latepoint-addons' ),
								'type'  => 'numeric'
							]
						] );
					}
					break;
				case 'service_category':
				case 'location_category':
				case \OsServiceCategoryModel::class:
				case \OsLocationCategoryModel::class:
					$propsList = [
						'name'              => [ 'label' => esc_html__( 'Name', 'latepoint' ), 'type' => 'string' ],
						'short_description' => [
							'label' => esc_html__( 'Short Description', 'latepoint' ),
							'type'  => 'text'
						],
						'parent_id'         => [
							'label' => esc_html_tx__( 'Parent Category', 'latepoint-addons' ),
							'type'  => 'model'
						]
					];
					break;
				case 'service':
				case \OsServiceModel::class:
					$propsList = [
						'name'              => [ 'label' => esc_html__( 'Name', 'latepoint' ), 'type' => 'string' ],
						'short_description' => [
							'label' => esc_html__( 'Short Description', 'latepoint' ),
							'type'  => 'text'
						],
						'charge_amount'     => [
							'label' => esc_html__( 'Charge Amount', 'latepoint' ),
							'type'  => 'numeric'
						],
						'deposit_amount'    => [
							'label' => esc_html__( 'Deposit Amount', 'latepoint' ),
							'type'  => 'numeric'
						],
						'buffer_before'     => [
							'label' => esc_html__( 'Buffer Before', 'latepoint' ),
							'type'  => 'numeric'
						],
						'buffer_after'      => [
							'label' => esc_html__( 'Buffer After', 'latepoint' ),
							'type'  => 'numeric'
						],
						'capacity_min'      => [
							'label' => esc_html_tx__( 'Minimum Capacity', 'latepoint-addons' ),
							'type'  => 'numeric'
						],
						'capacity_max'      => [
							'label' => esc_html_tx__( 'Maximum Capacity', 'latepoint-addons' ),
							'type'  => 'numeric'
						]
					];
					break;
				case 'customer':
				case \OsCustomerModel::class:
					$propsList = [
						'first_name'               => [
							'label' => esc_html__( 'First Name', 'latepoint' ),
							'type'  => 'string'
						],
						'last_name'                => [
							'label' => esc_html__( 'Last Name', 'latepoint' ),
							'type'  => 'string'
						],
						'full_name'                => [
							'label' => esc_html__( 'Full Name', 'latepoint' ),
							'type'  => 'string'
						],
						'email'                    => [
							'label' => esc_html__( 'Email Address', 'latepoint' ),
							'type'  => 'string'
						],
						'phone'                    => [
							'label' => esc_html__( 'Phone', 'latepoint' ),
							'type'  => 'string'
						],
						'notes'                    => [
							'label' => esc_html__( 'Notes', 'latepoint' ),
							'type'  => 'text'
						],
						'admin_notes'              => [
							'label' => esc_html_tx__( 'Admin Notes', 'latepoint-addons' ),
							'type'  => 'text'
						],
						'selected_timezone_name'   => [
							'label' => esc_html_tx__( 'Timezone', 'latepoint-addons' ),
							'type'  => 'string'
						],
						'past_bookings_count'      => [
							'label' => esc_html__( 'Past Appointments', 'latepoint' ),
							'type'  => 'numeric'
						],
						'cancelled_bookings_count' => [
							'label' => esc_html__( 'Cancelled Appointments', 'latepoint' ),
							'type'  => 'numeric'
						],
						'future_bookings_count'    => [
							'label' => esc_html__( 'Upcoming Appointments', 'latepoint' ),
							'type'  => 'numeric'
						],
						'total_bookings_count'     => [
							'label' => esc_html__( 'Total Appointments', 'latepoint' ),
							'type'  => 'numeric'
						]
					];
					break;
				case 'customer_address':
				case 'TechXelaLatePointMCAAddressModel':
					if ( \TechXelaLatePointAddonsHelper::isTxAddonActive( 'mca' ) ) {
						$propsList = [
							'contact_name'             => [
								'label' => esc_html__( 'Contact Name', 'mca-latepoint' ),
								'type'  => 'string'
							],
							'street_1'                 => [
								'label' => esc_html__( 'Address Line #1', 'mca-latepoint' ),
								'type'  => 'string'
							],
							'street_2'                 => [
								'label' => esc_html__( 'Address Line #2', 'mca-latepoint' ),
								'type'  => 'string'
							],
							'city'                     => [
								'label' => esc_html__( 'City', 'mca-latepoint' ),
								'type'  => 'string'
							],
							'state_name'               => [
								'label' => esc_html__( 'State', 'mca-latepoint' ),
								'type'  => 'string'
							],
							'country'                  => [
								'label'   => esc_html__( 'Country', 'mca-latepoint' ),
								'type'    => 'multi_select',
								'options' => 'TechXelaLatePointMCAGeoHelper::getCountriesList'
							],
							'zip_code'                 => [
								'label' => esc_html__( 'ZIP Code', 'mca-latepoint' ),
								'type'  => 'string'
							],
							'phone_number'             => [
								'label' => esc_html__( 'Phone Number', 'mca-latepoint' ),
								'type'  => 'string'
							],
							'full_address_single_line' => [
								'label' => esc_html__( 'Full Address', 'mca-latepoint' ),
								'type'  => 'string'
							],
						];
					}
					break;
				case 'agent':
				case \OsAgentModel::class:
					$propsList = [
						'first_name'   => [ 'label' => esc_html__( 'First Name', 'latepoint' ), 'type' => 'string' ],
						'last_name'    => [ 'label' => esc_html__( 'Last Name', 'latepoint' ), 'type' => 'string' ],
						'full_name'    => [ 'label' => esc_html__( 'Full Name', 'latepoint' ), 'type' => 'string' ],
						'display_name' => [ 'label' => esc_html__( 'Display Name', 'latepoint' ), 'type' => 'string' ],
						'title'        => [ 'label' => esc_html__( 'Title', 'latepoint' ), 'type' => 'string' ],
						'email'        => [
							'label' => esc_html__( 'Email Address', 'latepoint' ),
							'type'  => 'string'
						],
						'phone'        => [ 'label' => esc_html__( 'Phone', 'latepoint' ), 'type' => 'string' ],
						'extra_emails' => [ 'label' => esc_html__( 'Extra Emails', 'latepoint' ), 'type' => 'string' ],
						'extra_phones' => [ 'label' => esc_html__( 'Extra Phones', 'latepoint' ), 'type' => 'string' ],
						'bio'          => [ 'label' => esc_html__( 'Bio', 'latepoint' ), 'type' => 'text' ]
					];
					break;
				case 'location':
				case \OsLocationModel::class:
					$propsList = [
						'name'         => [ 'label' => esc_html__( 'Name', 'latepoint' ), 'type' => 'string' ],
						'full_address' => [
							'label' => esc_html_tx__( 'Full Address', 'latepoint-addons' ),
							'type'  => 'string'
						]
					];
					break;
				case 'coupon':
				case 'OsCouponModel':
					$propsList = [
						'code'           => [
							'label' => esc_html_tx__( 'Code', 'latepoint-addons' ),
							'type'  => 'string'
						],
						'name'           => [ 'label' => esc_html__( 'Name', 'latepoint' ), 'type' => 'string' ],
						'description'    => [ 'label' => esc_html__( 'Description', 'latepoint' ), 'type' => 'text' ],
						'discount_type'  => [
							'label'   => esc_html__( 'Type', 'latepoint' ),
							'type'    => 'select',
							'options' => '\TechXela\LatePointAddons\Traits\FormBlock\IsConditionalFormBlockHelper::getCouponDiscountTypesForSelect'
						],
						'discount_value' => [ 'label' => esc_html__( 'Value', 'latepoint' ), 'type' => 'numeric' ],
					];
					break;
				case 'booking_custom_field':
				case 'customer_custom_field':
					if ( \TechXelaLatePointAddonsHelper::isOsAddonActive( 'custom-fields' ) ) {
						$fieldsFor    = str_replace( '_custom_field', '', $target );
						$customFields = \OsCustomFieldsHelper::get_custom_fields_arr( $fieldsFor, 'all' );
						foreach ( $customFields as $customField ) {
							$propType = self::convertCfTypeToPropType( $customField['type'], $customField );
							if ( $propType ) {
								$prop = [
									'label' => $customField['label'],
									'type'  => $propType
								];

								if ( $customField['type'] == 'select' ) {
									$prop['options'] = \OsFormHelper::generate_select_options_from_custom_field( $customField['options'] );
									$prop['options'] = array_combine( $prop['options'], $prop['options'] );
								} elseif ( $customField['type'] == 'checkbox' ) {
									$prop['options'] = [
										'on'  => esc_html_tx__( 'On', 'latepoint-addons' ),
										'off' => esc_html_tx__( 'Off', 'latepoint-addons' )
									];
								}

								$propsList[ $customField['id'] ] = $prop;
							}
						}
					}
					break;
			}

			if ( ! in_array( $target, apply_filters( 'tx_latepoint_form_block_non_model_targets', [
				'booking',
				\OsBookingModel::class,
				'booking_custom_field',
				'customer_custom_field'
			] ) ) ) {
				$propsList = array_merge(
					[
						'id' => [
							'label' => esc_html_tx__( 'Object', 'latepoint-addons' ),
							'type'  => 'model'
						],
					],
					$propsList,
					[
						'created_at' => [
							'label' => esc_html_tx__( 'Created Date', 'latepoint-addons' ),
							'type'  => 'date'
						],
						'updated_at' => [
							'label' => esc_html_tx__( 'Updated Date', 'latepoint-addons' ),
							'type'  => 'date'
						]
					]
				);
			}

			$propsList = self::getCustomConditionTargetPropsList( $propsList, $target );

			return apply_filters( 'tx_latepoint_form_block_props_list', $propsList, $target );
		}

		protected static function getCustomConditionTargetPropsList( array $propsList, string $target ): array {
			return $propsList;
		}

		public static function getConditionTargetPropsListForSelect( string $target ): array {
			$selectList = [];

			$propsList = self::getConditionTargetPropsList( $target );

			foreach ( $propsList as $propKey => $propInfo ) {
				$selectList[] = [
					'label' => $propInfo['label'],
					'value' => $propKey
				];
			}

			return $selectList;
		}

		public static function getComparisonOptions( string $targetPropType ): array {
			$comparisonOptions = [];

			switch ( $targetPropType ) {
				case 'model':
				case 'multi_select':
					$comparisonOptions = [
						'includes' => esc_html_tx__( 'is included in', 'latepoint-addons' ),
						'excludes' => esc_html_tx__( 'is excluded from', 'latepoint-addons' )
					];
					break;
				case 'numeric':
					$comparisonOptions = [
						'lt'   => esc_html_tx__( 'less than', 'latepoint-addons' ),
						'lte'  => esc_html_tx__( 'less than or equal to', 'latepoint-addons' ),
						'eq'   => esc_html_tx__( 'equal to', 'latepoint-addons' ),
						'neq'  => esc_html_tx__( 'not equal to', 'latepoint-addons' ),
						'gt'   => esc_html_tx__( 'greater than', 'latepoint-addons' ),
						'gte'  => esc_html_tx__( 'greater than or equal to', 'latepoint-addons' ),
						'mod'  => esc_html_tx__( 'divisible by', 'latepoint-addons' ),
						'mul'  => esc_html_tx__( 'multiple of', 'latepoint-addons' ),
						'even' => esc_html_tx__( 'is even', 'latepoint-addons' ),
						'odd'  => esc_html_tx__( 'is odd', 'latepoint-addons' ),
					];
					break;
				case 'string':
				case 'text':
					$comparisonOptions = [
						'is'              => esc_html_tx__( 'is', 'latepoint-addons' ),
						'not'             => esc_html_tx__( 'is not', 'latepoint-addons' ),
						'starts_with'     => esc_html_tx__( 'starts with', 'latepoint-addons' ),
						'not_starts_with' => esc_html_tx__( 'does not start with', 'latepoint-addons' ),
						'ends_with'       => esc_html_tx__( 'ends with', 'latepoint-addons' ),
						'not_ends_with'   => esc_html_tx__( 'does not end with', 'latepoint-addons' ),
						'contains'        => esc_html_tx__( 'contains', 'latepoint-addons' ),
						'not_contains'    => esc_html_tx__( 'does not contain', 'latepoint-addons' ),
					];
					break;
				case 'date':
					$comparisonOptions = [
						'is'        => esc_html_tx__( 'is', 'latepoint-addons' ),
						'not'       => esc_html_tx__( 'is not', 'latepoint-addons' ),
						'on_a'      => esc_html_tx__( 'is on a', 'latepoint-addons' ),
						'not_on_a'  => esc_html_tx__( 'is not on a', 'latepoint-addons' ),
						'before'    => esc_html_tx__( 'is before', 'latepoint-addons' ),
						'on_before' => esc_html_tx__( 'is on or before', 'latepoint-addons' ),
						'after'     => esc_html_tx__( 'is after', 'latepoint-addons' ),
						'on_after'  => esc_html_tx__( 'is on or after', 'latepoint-addons' ),
						'between'   => esc_html_tx__( 'is between', 'latepoint-addons' ),
					];
					break;
				case 'time':
					$comparisonOptions = [
						'is'        => esc_html_tx__( 'is', 'latepoint-addons' ),
						'not'       => esc_html_tx__( 'is not', 'latepoint-addons' ),
						'before'    => esc_html_tx__( 'is before', 'latepoint-addons' ),
						'at_before' => esc_html_tx__( 'is at or before', 'latepoint-addons' ),
						'after'     => esc_html_tx__( 'is after', 'latepoint-addons' ),
						'at_after'  => esc_html_tx__( 'is at or after', 'latepoint-addons' ),
						'between'   => esc_html_tx__( 'is between', 'latepoint-addons' ),
					];
					break;
				case 'select':
					$comparisonOptions = [
						'is'  => esc_html_tx__( 'is', 'latepoint-addons' ),
						'not' => esc_html_tx__( 'is not', 'latepoint-addons' )
					];

			}

			$comparisonOptions = self::getCustomComparisonOptions( $comparisonOptions, $targetPropType );

			return apply_filters( 'tx_latepoint_form_block_comparison_options', $comparisonOptions, $targetPropType );
		}

		protected static function getCustomComparisonOptions( array $options, string $targetPropType ): array {
			return $options;
		}

		public static function generateConditionForm( string $formBlockId, string $conditionId = '', array $conditionData = [] ): string {
			if ( empty( $conditionId ) ) {
				$conditionId   = self::generateFormBlockConditionId();
				$conditionData = [
					'target'           => 'booking',
					'target_prop'      => 'start_date',
					'target_prop_type' => 'date',
					'comparison'       => 'is',
					'value'            => false,
					'gate'             => 'and'
				];
			}

			$conditionSegments = self::generateConditionFormSegments( $formBlockId, $conditionId, $conditionData );

			return '<div class="pe-condition form-block-condition" data-condition-id="' . $conditionId . '">' .
			       '<button class="pe-remove-condition form-block-remove-condition"><i class="latepoint-icon latepoint-icon-cross"></i></button>' .
			       \OsFormHelper::select_field(
				       self::generateFormBlockConditionName( $formBlockId, $conditionId, 'gate' ),
				       false,
				       [
					       'and' => esc_html_tx__( 'AND', 'latepoint-addons' ),
					       'or'  => esc_html_tx__( 'OR', 'latepoint-addons' )
				       ],
				       $conditionData['gate'],
				       [],
				       [ 'class' => 'form-block-condition-gate-w' ]
			       ) .

			       $conditionSegments['targetSegment'] .

			       $conditionSegments['targetPropSegment'] .

			       $conditionSegments['comparisonSegment'] .

			       $conditionSegments['valueSegment'] .

			       '<div class="form-block-condition-add-w" 
                      data-os-action="' . \OsRouterHelper::build_route_name( self::$formBlocksRouteController, 'newCondition' ) . '" 
                      data-os-params="' . \OsUtilHelper::build_os_params( [ 'form_block_id' => $formBlockId ] ) . '" 
                      data-os-pass-response="yes" 
                      data-os-pass-this="yes" 
                      data-os-before-after="none" 
                      data-os-after-call="techXelaLatePointCoreAdmin.addFormBlockCondition"><button class="latepoint-btn-outline latepoint-btn"><i class="latepoint-icon latepoint-icon-plus2"></i></button></div>' .
			       '</div>';
		}

		public static function generateConditionFormSegments( string $formBlockId, string $conditionId, array $conditionData, bool $newCondition = true ): array {
			$targetProps = self::getConditionTargetPropsListForSelect( $conditionData['target'] );
			if ( ! $newCondition ) {
				if ( ! in_array( $conditionData['target_prop'], wp_list_pluck( $targetProps, 'value' ) ) ) {
					$conditionData['target_prop'] = $targetProps[0]['value'];
				}
				$targetPropType = self::getConditionTargetPropsList( $conditionData['target'] )[ $conditionData['target_prop'] ]['type'];
				if ( $targetPropType != $conditionData['target_prop_type'] ) {
					$conditionData['target_prop_type'] = $targetPropType;
				}
				$conditionData['value'] = false;
			}
			$comparisonOptions = self::getComparisonOptions( $conditionData['target_prop_type'] );

			$targetSegment = \OsFormHelper::select_field(
				self::generateFormBlockConditionName( $formBlockId, $conditionId, 'target' ),
				false,
				self::getConditionTargets(),
				$conditionData['target'],
				[
					'class'                 => 'form-block-condition-select form-block-condition-target',
					'data-refresh-segments' => 'target-prop,comparison,value'
				],
				[ 'class' => 'form-block-condition-target-w' ] );

			$targetPropSegment = \OsFormHelper::select_field(
				self::generateFormBlockConditionName( $formBlockId, $conditionId, 'target_prop' ),
				false,
				$targetProps,
				$conditionData['target_prop'],
				[
					'class'                 => 'form-block-condition-select form-block-condition-target-prop',
					'data-refresh-segments' => 'comparison,value'
				],
				[ 'class' => 'form-block-condition-target-prop-w' ] );

			$comparisonSegment = \OsFormHelper::select_field(
				self::generateFormBlockConditionName( $formBlockId, $conditionId, 'comparison' ),
				false,
				$comparisonOptions,
				$conditionData['comparison'],
				[
					'class'                 => 'form-block-condition-select form-block-condition-comparison',
					'data-refresh-segments' => 'value'
				],
				[ 'class' => 'form-block-condition-comparison-w' ] );

			$valueSegment = self::generateConditionFormValueSegment( $formBlockId, $conditionId, $conditionData, $newCondition );

			$formSegments = self::generateCustomConditionFormSegments(
				compact( 'targetSegment', 'targetPropSegment', 'comparisonSegment', 'valueSegment' ),
				$formBlockId, $conditionId, $conditionData, $newCondition
			);

			return apply_filters( 'tx_latepoint_form_block_condition_form_segments', $formSegments, $formBlockId, $conditionId, $conditionData, $newCondition );
		}

		protected static function generateCustomConditionFormSegments( array $formSegments, string $formBlockId, string $conditionId, array $conditionData, bool $newCondition = true ): array {
			return $formSegments;
		}

		private static function generateConditionFormValueSegment( $formBlockId, $conditionId, $conditionData, $newCondition = true ): string {
			$valueSegment   = '<span class="form-block-condition-value-w"></span>';
			$fieldName      = self::generateFormBlockConditionName( $formBlockId, $conditionId, 'value' );
			$atts           = [ 'class' => 'form-block-condition-value' ];
			$wrapperAtts    = [ 'class' => 'form-block-condition-value-w' ];
			$targetPropType = $conditionData['target_prop_type'];

			switch ( $targetPropType ) {
				case 'model':
					$modelListParams = self::conditionTargetToModelListParams( $conditionData );
					$modelList       = self::getModelListForSelect(
						$modelListParams['modelClass'],
						$modelListParams['query'],
						$modelListParams['labelFields'],
						$modelListParams['includeBlank'],
						$modelListParams['blankLabel'],
						$modelListParams['valueField'],
					);

					$valueSegment = \OsFormHelper::multi_select_field(
						$fieldName,
						false,
						$modelList,
						$conditionData['value'] ? explode( ',', $conditionData['value'] ) : [],
						$atts,
						$wrapperAtts
					);
					break;
				case 'numeric':
					if ( ! in_array( $conditionData['comparison'], [
						'even',
						'odd'
					] ) ) {
						$atts['theme'] = 'bordered';
						$valueSegment  = \OsFormHelper::number_field( $fieldName, false, $conditionData['value'], null, null, $atts, $wrapperAtts );
					} else {
						$valueSegment = '<span class="form-block-condition-value-w display-none"></span>';
					}
					break;
				case 'string':
					$atts['theme'] = 'bordered';
					$valueSegment  = \OsFormHelper::text_field( $fieldName, false, $conditionData['value'], $atts, $wrapperAtts );
					break;
				case 'text':
					$atts['theme'] = 'bordered';
					$atts['rows']  = 1;
					$valueSegment  = \OsFormHelper::textarea_field( $fieldName, false, $conditionData['value'], $atts, $wrapperAtts );
					break;
				case 'select':
				case 'multi_select':
					$propOptions = self::getConditionTargetPropsList( $conditionData['target'] )[ $conditionData['target_prop'] ]['options'] ?? [];
					if ( is_callable( $propOptions ) ) {
						$options = $propOptions();
					} elseif ( is_array( $propOptions ) ) {
						$options = $propOptions;
					} else {
						$options = [];
					}

					if ( $targetPropType == 'select' ) {
						$valueSegment = \OsFormHelper::select_field( $fieldName, false, $options, $conditionData['value'], $atts, $wrapperAtts );
					} else {
						$valueSegment = \OsFormHelper::multi_select_field(
							$fieldName,
							false,
							$options,
							( $conditionData['value'] ? explode( ',', $conditionData['value'] ) : [] ),
							$atts,
							$wrapperAtts
						);
					}
					break;
				case 'date':
					$valueSegment = "<div class='{$wrapperAtts['class']}'>";
					if ( $conditionData['comparison'] == 'between' ) {
						$wrapperAtts['class'] .= ' ws-period';
						$valueSegment         = "<div class='{$wrapperAtts['class']}'>";

						$valueSegment .= \TechXelaLatePointFormHelper::datePickerField(
							"{$fieldName}[start]",
							esc_html__( 'Start', 'latepoint' ),
							$conditionData['value']['start'] ?? false,
							false,
							true,
							$atts
						);
						$valueSegment .= \TechXelaLatePointFormHelper::datePickerField(
							"{$fieldName}[end]",
							esc_html_tx__( 'End', 'latepoint-addons' ),
							$conditionData['value']['end'] ?? false,
							false,
							true,
							$atts
						);
					} elseif ( in_array( $conditionData['comparison'], [ 'on_a', 'not_on_a' ] ) ) {
						$valueSegment .= \OsFormHelper::multi_select_field( $fieldName,
							false,
							self::getWeekdayNamesForSelect(),
							explode( ',', $conditionData['value'] ),
							$atts,
							$wrapperAtts
						);
					} else {
						$valueSegment .= \TechXelaLatePointFormHelper::datePickerField( $fieldName,
							false,
							$conditionData['value'] ?: false,
							false,
							true,
							$atts
						);
					}
					$valueSegment .= '</div>';
					break;
				case 'time':
					$valueSegment = "<div class='{$wrapperAtts['class']}'>";

					if ( $conditionData['comparison'] == 'between' ) {
						$wrapperAtts['class'] .= ' ws-period';
						$valueSegment         = "<div class='{$wrapperAtts['class']}'>";

						$startTime    = $conditionData['value']['start']['formatted_value'] ?? '09:00';
						$startAmPm    = $conditionData['value']['start']['ampm'] ?? false;
						$startMinutes = \OsTimeHelper::convert_time_to_minutes( $startTime, $startAmPm );

						$endTime    = $conditionData['value']['end']['formatted_value'] ?? '17:00';
						$endAmPm    = $conditionData['value']['end']['ampm'] ?? false;
						$endMinutes = \OsTimeHelper::convert_time_to_minutes( $endTime, $endAmPm );

						$valueSegment .= \OsFormHelper::time_field( "{$fieldName}[start]", esc_html__( 'Start', 'latepoint' ), $startMinutes, true );
						$valueSegment .= \OsFormHelper::time_field( "{$fieldName}[end]", esc_html_tx__( 'End', 'latepoint-addons' ), $endMinutes, true );
					} else {
						$fieldTime    = $conditionData['value']['formatted_value'] ?? '09:00';
						$fieldAmPm    = $conditionData['value']['ampm'] ?? false;
						$fieldMinutes = \OsTimeHelper::convert_time_to_minutes( $fieldTime, $fieldAmPm );

						$valueSegment .= \OsFormHelper::time_field( $fieldName, false, $fieldMinutes, true );
					}
					$valueSegment .= '</div>';
			}

			return $valueSegment;
		}

		public static function conditionTargetToModelListParams( array $condition ): array {
			$target       = $condition['target'];
			$modelClass   = \OsModel::class;
			$query        = [];
			$labelFields  = [ 'name' ];
			$includeBlank = false;
			$blankLabel   = esc_html_tx__( 'Nothing selected', 'latepoint-addons' );
			$valueField   = 'id';

			switch ( $target ) {
				case 'service_category':
					$modelClass   = \OsServiceCategoryModel::class;
					$includeBlank = true;
					$blankLabel   = esc_html_tx__( 'Any Service Category', 'latepoint-addons' );
					break;
				case 'service':
					$modelClass   = \OsServiceModel::class;
					$includeBlank = true;
					$blankLabel   = esc_html_tx__( 'Any Service', 'latepoint-addons' );
					break;
				case 'customer':
					$modelClass   = \OsCustomerModel::class;
					$labelFields  = [ 'first_name', 'last_name' ];
					$includeBlank = true;
					$blankLabel   = esc_html_tx__( 'Any Customer', 'latepoint-addons' );
					break;
				case 'customer_address':
					$modelClass   = 'TechXelaLatePointMCAAddressModel';
					$labelFields  = [ 'full_address_single_line' ];
					$includeBlank = true;
					$blankLabel   = esc_html_tx__( 'Any Address', 'latepoint-addons' );
					break;
				case 'agent':
					$modelClass   = \OsAgentModel::class;
					$labelFields  = [ 'first_name', 'last_name' ];
					$includeBlank = true;
					$blankLabel   = esc_html_tx__( 'Any Agent', 'latepoint-addons' );
					break;
				case 'location_category':
					$modelClass   = \OsLocationCategoryModel::class;
					$includeBlank = true;
					$blankLabel   = esc_html_tx__( 'Any Location Category', 'latepoint-addons' );
					break;
				case 'location':
					$modelClass   = \OsLocationModel::class;
					$includeBlank = true;
					$blankLabel   = esc_html_tx__( 'Any Location', 'latepoint-addons' );
					break;
				case 'coupon':
					$modelClass   = 'OsCouponModel';
					$labelFields  = [ 'code', ' - ', 'name' ];
					$includeBlank = true;
					$blankLabel   = esc_html_tx__( 'Any Coupon', 'latepoint-addons' );
			}

			$modelListParams = self::customConditionTargetToModelListParams(
				compact( 'modelClass', 'query', 'labelFields', 'includeBlank', 'blankLabel', 'valueField' ),
				$condition
			);

			return apply_filters( 'tx_latepoint_form_block_condition_model_list_params', $modelListParams, $condition );
		}

		protected static function customConditionTargetToModelListParams( array $modelListParams, array $condition ): array {
			return $modelListParams;
		}

		public static function isConditionSatisfied( array $condition, \OsBookingModel $bookingObject ): bool {
			$targetProp         = $condition['target_prop'];
			$targetPropType     = $condition['target_prop_type'];
			$comparison         = $condition['comparison'];
			$conditionValue     = $condition['value'];
			$targetPropValue    = self::getTargetPropValue( $condition, $bookingObject );
			$conditionSatisfied = false;

			if ( ! is_null( $targetPropValue ) ) {
				switch ( $targetPropType ) {
					case 'model':
					case 'multi_select':
						$conditionValue = explode( ',', $conditionValue );
						if ( empty( $conditionValue ) ) {
							$conditionSatisfied = true;
						} elseif ( $targetProp == 'service_extras' ) {
							foreach ( $targetPropValue as $serviceExtraId => $serviceExtraQty ) {
								if ( $comparison == 'includes' ) {
									$conditionPassed = in_array( '__any__', $conditionValue ) || in_array( $serviceExtraId, $conditionValue );
								} else {
									$conditionPassed = ! in_array( '__any__', $conditionValue ) && ! in_array( $serviceExtraId, $conditionValue );
								}

								if ( $conditionPassed ) {
									$conditionSatisfied = true;
									break;
								}
							}
						} elseif ( $comparison == 'includes' ) {
							$conditionSatisfied = in_array( '__any__', $conditionValue ) || in_array( $targetPropValue, $conditionValue );
						} else {
							$conditionSatisfied = ! in_array( '__any__', $conditionValue ) && ! in_array( $targetPropValue, $conditionValue );
						}
						break;
					case 'select':
						switch ( $comparison ) {
							case 'is':
								$conditionSatisfied = ( $targetPropValue == $conditionValue );
								break;
							case 'not':
								$conditionSatisfied = ( $targetPropValue != $conditionValue );
						}
						break;
					case 'numeric':
						$targetPropValue = floatval( $targetPropValue );
						$conditionValue  = floatval( $conditionValue );

						switch ( $comparison ) {
							case 'lt':
								$conditionSatisfied = $targetPropValue < $conditionValue;
								break;
							case 'lte':
								$conditionSatisfied = $targetPropValue <= $conditionValue;
								break;
							case 'eq':
								$conditionSatisfied = $targetPropValue == $conditionValue;
								break;
							case 'neq':
								$conditionSatisfied = $targetPropValue != $conditionValue;
								break;
							case 'gt':
								$conditionSatisfied = $targetPropValue > $conditionValue;
								break;
							case 'gte':
								$conditionSatisfied = $targetPropValue >= $conditionValue;
								break;
							case 'mod':
							case 'mul':
							case 'even':
							case 'odd':
								$targetPropValue = round( $targetPropValue );
								$conditionValue  = round( $conditionValue );

								switch ( $comparison ) {
									case 'mod':
										$conditionSatisfied = ( $targetPropValue % $conditionValue ) == 0;
										break;
									case 'mul':
										$conditionSatisfied = ( $targetPropValue / $conditionValue ) == 0;
										break;
									case 'even':
										$conditionSatisfied = ( $targetPropValue / 2 ) == 0;
										break;
									case 'odd':
										$conditionSatisfied = ( $targetPropValue / 2 ) != 0;
								}
						}

						break;
					case 'string':
					case 'text':
						switch ( $comparison ) {
							case 'is':
								$conditionSatisfied = $targetPropValue == $conditionValue;
								break;
							case 'not':
								$conditionSatisfied = $targetPropValue != $conditionValue;
								break;
							case 'starts_with':
							case 'not_starts_with':
							case 'ends_with':
							case 'not_ends_with':
							case 'contains':
							case 'not_contains':
								$negated = substr( $comparison, 0, 4 ) == 'not_';
								$func    = $negated ? substr( $comparison, 4 ) : $comparison;
								$func    = "str_$func";

								$conditionSatisfied = is_callable( $func ) && ( $negated ? ! $func( $targetPropValue, $conditionValue ) :
										$func( $targetPropValue, $conditionValue ) );
						}
						break;
					case 'date':
						if ( \TechXelaLatePointDateTimeHelper::isDateValid( $targetPropValue ) ) {
							$targetPropValue = \TechXelaLatePointDateTimeHelper::createDateFromImplicitFormat( $targetPropValue, true );

							if ( $comparison == 'between' ) {
								if ( ( ! empty( $betweenStartDate = $conditionValue['start'] ) && \TechXelaLatePointDateTimeHelper::isDateValid( $betweenStartDate ) )
								     && ( ! empty( $betweenEndDate = $conditionValue['end'] ) && \TechXelaLatePointDateTimeHelper::isDateValid( $betweenEndDate ) ) ) {
									$betweenStartDate = \TechXelaLatePointDateTimeHelper::createDateFromImplicitFormat( $betweenStartDate );
									$betweenEndDate   = \TechXelaLatePointDateTimeHelper::createDateFromImplicitFormat( $betweenEndDate );

									$conditionSatisfied = ( $targetPropValue >= $betweenStartDate ) && ( $targetPropValue <= $betweenEndDate );
								}
							} elseif ( in_array( $comparison, [ 'on_a', 'not_on_a' ] ) ) {
								$conditionValue  = explode( ',', $conditionValue );
								$targetPropValue = $targetPropValue->format( 'l' );

								$conditionSatisfied = ! empty( $conditionValue ) &&
								                      ( $comparison == 'on_a' ?
									                      in_array( $targetPropValue, $conditionValue ) :
									                      ! in_array( $targetPropValue, $conditionValue )
								                      );
							} else {
								$conditionValue = \TechXelaLatePointDateTimeHelper::createDateFromImplicitFormat( $conditionValue );

								switch ( $comparison ) {
									case 'is':
										$conditionSatisfied = $targetPropValue == $conditionValue;
										break;
									case 'not':
										$conditionSatisfied = $targetPropValue != $conditionValue;
										break;
									case 'before':
										$conditionSatisfied = $targetPropValue < $conditionValue;
										break;
									case 'on_before':
										$conditionSatisfied = $targetPropValue <= $conditionValue;
										break;
									case 'after':
										$conditionSatisfied = $targetPropValue > $conditionValue;
										break;
									case 'on_after':
										$conditionSatisfied = $targetPropValue >= $conditionValue;
								}
							}
						}
						break;
					case 'time':
						if ( $comparison == 'between' ) {
							$startTime = $conditionValue['start']['formatted_value'] ?? false;
							$startAmPm = $conditionValue['start']['ampm'] ?? false;

							$endTime = $conditionValue['end']['formatted_value'] ?? false;
							$endAmPm = $conditionValue['end']['ampm'] ?? false;

							if ( $startTime && $endTime ) {
								$startMinutes       = \OsTimeHelper::convert_time_to_minutes( $startTime, $startAmPm );
								$endMinutes         = \OsTimeHelper::convert_time_to_minutes( $endTime, $endAmPm );
								$conditionSatisfied = ( $targetPropValue >= $startMinutes ) && ( $targetPropValue <= $endMinutes );
							}
						} else {
							$conditionTime = $conditionValue['formatted_value'] ?? false;
							$conditionAmPm = $conditionValue['ampm'] ?? false;

							if ( $conditionTime ) {
								$conditionValue = \OsTimeHelper::convert_time_to_minutes( $conditionTime, $conditionAmPm );

								switch ( $comparison ) {
									case 'is':
										$conditionSatisfied = $targetPropValue == $conditionValue;
										break;
									case 'not':
										$conditionSatisfied = $targetPropValue != $conditionValue;
										break;
									case 'before':
										$conditionSatisfied = $targetPropValue < $conditionValue;
										break;
									case 'at_before':
										$conditionSatisfied = $targetPropValue <= $conditionValue;
										break;
									case 'after':
										$conditionSatisfied = $targetPropValue > $conditionValue;
										break;
									case 'at_after':
										$conditionSatisfied = $targetPropValue >= $conditionValue;
								}
							}
						}
						break;
				}
			}

			$conditionSatisfied = self::isCustomConditionSatisfied( $conditionSatisfied, $condition, $bookingObject );

			return apply_filters( 'tx_latepoint_form_block_is_condition_satisfied', $conditionSatisfied, $condition, $bookingObject );
		}

		protected static function isCustomConditionSatisfied( bool $conditionSatisfied, array $condition, \OsBookingModel $bookingObject ): bool {
			return $conditionSatisfied;
		}

		public static function getTargetPropValue( array $condition, \OsBookingModel $bookingObject ) {
			$target          = $condition['target'];
			$targetProp      = $condition['target_prop'];
			$targetPropValue = null;

			switch ( $target ) {
				case 'booking':
					if ( str_starts_with( $targetProp, 'service_extras' ) &&
					     \TechXelaLatePointAddonsHelper::isOsAddonActive( 'service-extras' ) ) {
						if ( $targetProp == 'service_extras' ) {
							$targetPropValue = \OsServiceExtrasHelper::get_service_extras_for_booking( $bookingObject );
						} elseif ( $targetProp == 'service_extras_count' ) {
							$targetPropValue      = 0;
							$bookingServiceExtras = \OsServiceExtrasHelper::get_service_extras_for_booking( $bookingObject );
							foreach ( $bookingServiceExtras as $id => $quantity ) {
								$targetPropValue += ( $quantity * 1 );
							}
						} elseif ( $targetProp == 'service_extras_cost' ) {
							$targetPropValue = \OsServiceExtrasHelper::calculate_service_extras_prices(
								0, $bookingObject, false, false );
						}
					} elseif ( isset( $bookingObject->$targetProp ) || method_exists( $bookingObject, "get_$targetProp" ) ) {
						// Fix for end_time being 60 minutes (1:00 AM), even before datepicker step
						if ( in_array( $targetProp, [
								'start_time',
								'end_time'
							] ) && ( is_null( $bookingObject->start_time ) || $bookingObject->start_time == '' ) ) {
							break;
						} else {
							$targetPropValue = $bookingObject->$targetProp;
						}
						break;
					}
					break;
				case 'service':
				case 'customer':
				case 'agent':
				case 'location':
					if ( isset( $bookingObject->$target->$targetProp ) || method_exists( $bookingObject->$target, "get_$targetProp" ) ) {
						$targetPropValue = $bookingObject->$target->$targetProp;
					} elseif ( $target == 'customer' ) {
						if ( in_array( $targetProp, [ 'past_bookings_count', 'cancelled_bookings_count' ] ) ) {
							$prop            = str_replace( '_count', '', $targetProp );
							$targetPropValue = count( $bookingObject->$target->$prop );
						}
					}
					break;
				case 'service_category':
				case 'location_category':
					if ( $target == 'service_category' ) {
						$targetPropValue = ( new \OsServiceCategoryModel( $bookingObject->service->category_id ) )->$targetProp;
					} else {
						$targetPropValue = ( new \OsLocationCategoryModel( $bookingObject->location->category_id ) )->$targetProp;
					}
					break;
				case 'booking_custom_field':
				case 'customer_custom_field':
					if ( \TechXelaLatePointAddonsHelper::isOsAddonActive( 'custom-fields' ) ) {
						if ( $target == 'booking_custom_field' ) {
							$targetPropValue = $bookingObject->custom_fields[ $targetProp ] ?? '';
						} else {
							$targetPropValue = $bookingObject->customer->custom_fields[ $targetProp ] ?? '';
						}
					}
					break;
				case 'customer_address':
					if ( ! empty( $bookingObject->tx_mca_address_id ) &&
					     \TechXelaLatePointAddonsHelper::isTxAddonActive( 'mca' ) ) {
						$bookingObject   = \TechXelaLatePointMCABookingHelper::cloneFromOsToTx( $bookingObject );
						$targetPropValue = $bookingObject->address->$targetProp ?? '';
					}
					break;
				case 'coupon':
					if ( ! empty( $bookingObject->coupon_code ) &&
					     \TechXelaLatePointAddonsHelper::isOsAddonActive( 'coupons' ) ) {
						$couponObject = \OsCouponHelper::get_coupon_by_code( $bookingObject->coupon_code );
						if ( $couponObject->exists() ) {
							$targetPropValue = $couponObject->$targetProp ?? '';
						}
					}
			}

			$targetPropValue = self::getCustomTargetPropValue( $targetPropValue, $condition, $bookingObject );

			return apply_filters( 'tx_latepoint_form_block_target_prop_value', $targetPropValue, $condition, $bookingObject );
		}

		protected static function getCustomTargetPropValue( $targetPropValue, array $condition, \OsBookingModel $bookingObject ) {
			return $targetPropValue;
		}

		public static function generateFormBlockConditionId(): string {
			return ( self::$formBlockConditionSettingPrefix ?? 'cond' ) . '_' . \OsUtilHelper::random_text( 'alnum', 8 );
		}

		public static function generateFormBlockConditionName( $formBlockId, $conditionId, $name ): string {
			return self::$formBlocksFormArrName . "[$formBlockId][conditions][$conditionId][$name]";
		}

		public static function getServiceExtrasForSelect( $multi = false ): array {
			$serviceExtrasList = [];
			foreach ( ( new \OsServiceExtraModel() )->get_results_as_models() as $serviceExtra ) {
				if ( $multi ) {
					$serviceExtrasList[] = [ 'value' => $serviceExtra->id, 'label' => $serviceExtra->name ];
				} else {
					$serviceExtrasList[ $serviceExtra->id ] = $serviceExtra->name;
				}
			}

			return $serviceExtrasList;
		}

		public static function convertCfTypeToPropType( string $cfType, array $customField = [] ): string {
			switch ( $cfType ) {
				case 'text':
				case 'phone_number':
				case 'hidden':
				default:
					$propType = 'string';
					break;
				case 'number':
					$propType = 'numeric';
					break;
				case 'textarea':
					$propType = 'text';
					break;
				case 'select':
					$propType = 'multi_select';
					break;
				case 'checkbox':
					$propType = 'select';
			}

			return apply_filters( 'tx_latepoint_form_block_cf_type_to_prop_type', $propType, $cfType, $customField );
		}

		public static function getWeekdayNamesForSelect(): array {
			return [
				'Monday'    => esc_html__( 'Monday', 'latepoint' ),
				'Tuesday'   => esc_html__( 'Tuesday', 'latepoint' ),
				'Wednesday' => esc_html__( 'Wednesday', 'latepoint' ),
				'Thursday'  => esc_html__( 'Thursday', 'latepoint' ),
				'Friday'    => esc_html__( 'Friday', 'latepoint' ),
				'Saturday'  => esc_html__( 'Saturday', 'latepoint' ),
				'Sunday'    => esc_html__( 'Sunday', 'latepoint' )
			];
		}

		public static function getCouponDiscountTypesForSelect(): array {
			return [
				'percent' => esc_html__( 'Percent', 'latepoint-coupons' ),
				'fixed'   => esc_html__( 'Fixed Value', 'latepoint-coupons' )
			];
		}
	}
}