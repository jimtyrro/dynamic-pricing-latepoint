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

if ( ! trait_exists( 'TechXela\LatePointAddons\Traits\FormBlock\IsFormBlockController' ) ) {
	trait IsFormBlockController {

		/**
		 * @var string $newFormBlockName
		 */
		public $newFormBlockName;

		/**
		 * @var string $formBlockObjectName
		 */
		public $formBlockObjectName;

		/**
		 * @var string $formBlockObjectNamePlural
		 */
		public $formBlockObjectNamePlural;

		/**
		 * @var \TechXela\LatePointAddons\Traits\FormBlock\IsFormBlockHelper|\TechXela\LatePointAddons\Traits\FormBlock\IsConditionalFormBlockHelper $formBlockHelper
		 */
		public $formBlockHelper;

		/**
		 * @var string $logTagPrefix
		 */
		public $logTagPrefix;

		/**
		 * @var string $formBlocksContainerClasses
		 */
		public $formBlocksContainerClasses = '';

		/**
		 * @var string $formBlocksFieldsForAttrVal
		 */
		public $formBlocksFieldsForAttrVal = 'none';

		/**
		 * @var string $formBlocksAddBoxLabel
		 */
		public $formBlocksAddBoxLabel = '';

		public function formBlocksContainer() {
			$this->vars['formBlockHelper'] = $this->formBlockHelper;
			if ( $this->formBlockHelper::$formBlocksAreDraggable ) {
				$this->formBlocksContainerClasses .= ' os-draggable-form-blocks';
			}
			$this->vars['formBlocksContainerClasses'] = $this->formBlocksContainerClasses;
			$this->vars['formBlocksFieldsForAttrVal'] = $this->formBlocksFieldsForAttrVal;
			$this->vars['formBlocksAddBoxLabel']      = $this->formBlocksAddBoxLabel;

			$this->vars = $this->varsForFormBlocksContainer( $this->vars );
			$this->set_layout( 'none' );
			$this->views_folder = TECHXELA_LATEPOINT_ADDONS_CORE_VIEWS_ABSPATH . 'form_block/';
			$this->format_render( 'form_blocks_container' );
		}

		protected function varsForFormBlocksContainer( array $vars ): array {
			return $vars;
		}

		public function newForm() {
			$this->vars['formBlockHelper']           = $this->formBlockHelper;
			$this->vars['newFormBlockName']          = $this->newFormBlockName;
			$this->vars['formBlockObjectName']       = $this->formBlockObjectName;
			$this->vars['formBlockObjectNamePlural'] = $this->formBlockObjectNamePlural;

			if ( empty( $this->vars['formBlock'] ) ) {
				$formBlock = array_merge( [
					'id'      => $this->formBlockHelper::generateFormBlockId(),
					'name'    => '',
					'enabled' => 'on'
				], $this->formBlockHelper::customFormBlockProperties() );

				if ( \TechXelaLatePointUtilHelper::classUsesTrait( $this->formBlockHelper, IsConditionalFormBlockHelper::class ) ) {
					$formBlock = array_merge( $formBlock, [
						'conditional' => 'off',
						'conditions'  => [],
					] );
				}

				if ( \TechXelaLatePointUtilHelper::classUsesTrait( $this->formBlockHelper, IsSchedulableFormBlockHelper::class ) ) {
					$formBlock = array_merge( $formBlock, [
						'scheduling' => 'off',
						'schedule'   => [
							'from' => '',
							'to'   => ''
						],
					] );
					if ( \TechXelaLatePointUtilHelper::classUsesTrait( $this->formBlockHelper, IsRecurringFormBlockHelper::class ) ) {
						$formBlock = array_merge( $formBlock, [
							'recurring'  => 'off',
							'recurrence' => [
								'value' => 1,
								'unit'  => 'D'
							],
						] );
					}
				}

				$this->vars['formBlock'] = $formBlock;
			}

			$this->vars = $this->varsForNewForm( $this->vars );
			$this->set_layout( 'none' );
			$this->views_folder = TECHXELA_LATEPOINT_ADDONS_CORE_VIEWS_ABSPATH . 'form_block/';
			$this->format_render( 'new_form' );
		}

		protected function varsForNewForm( array $vars ): array {
			return $vars;
		}

		public function newCondition() {
			try {
				$formBlockId   = $this->params['form_block_id'];
				$conditionHtml = $this->formBlockHelper::generateConditionForm( $formBlockId );

				if ( $this->get_return_format() == 'json' ) {
					$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => $conditionHtml ) );
				}
			} catch ( \Throwable $exception ) {
				\TechXelaLatePointDebugHelper::logException( $exception, $this->logTagPrefix . '_' . __FUNCTION__ );
			}
		}

		public function refreshCondition() {
			try {
				$target      = $this->params['target'];
				$targetProp  = $this->params['target_prop'];
				$comparison  = $this->params['comparison'];
				$formBlockId = $this->params['form_block_id'];
				$conditionId = $this->params['condition_id'];

				$conditionSegments = $this->formBlockHelper::generateConditionFormSegments(
					$formBlockId,
					$conditionId,
					[
						'target'      => $target,
						'target_prop' => $targetProp,
						'comparison'  => $comparison,
						'value'       => false,
					],
					false
				);

				if ( $this->get_return_format() == 'json' ) {
					$this->send_json( [
						'status'  => LATEPOINT_STATUS_SUCCESS,
						'message' => [
							'target'      => $conditionSegments['targetSegment'],
							'target-prop' => $conditionSegments['targetPropSegment'],
							'comparison'  => $conditionSegments['comparisonSegment'],
							'value'       => $conditionSegments['valueSegment'],
						]
					] );
				}
			} catch ( \Throwable $exception ) {
				\TechXelaLatePointDebugHelper::logException( $exception, $this->logTagPrefix . '_' . __FUNCTION__ );
			}
		}

		public function save() {
			try {
				if ( ! empty( $this->params[ $this->formBlockHelper::$formBlocksFormArrName ] ) ) {
					foreach ( $this->params[ $this->formBlockHelper::$formBlocksFormArrName ] as $formBlock ) {
						list( $formBlock, $validationErrors ) = $this->formBlockHelper::hasValidationErrors( $formBlock );

						if ( is_array( $validationErrors ) ) {
							$status       = LATEPOINT_STATUS_ERROR;
							$responseHtml = implode( ', ', $validationErrors );
						} elseif ( $this->formBlockHelper::save( $formBlock ) ) {
							do_action( 'tx_latepoint_form_block_saved', $formBlock, $this );
							$status       = LATEPOINT_STATUS_SUCCESS;
							$responseHtml = sprintf( esc_html_tx__( '%s Saved', 'latepoint-addons' ), $this->formBlockObjectName );
						} else {
							$status       = LATEPOINT_STATUS_ERROR;
							$responseHtml = sprintf( esc_html_tx__( 'Error Saving %s', 'latepoint-addons' ), $this->formBlockObjectName );
						}
					}
				} else {
					$status       = LATEPOINT_STATUS_ERROR;
					$responseHtml = esc_html__( 'Invalid params', 'latepoint' );
				}

				if ( $this->get_return_format() == 'json' ) {
					$this->send_json( array( 'status' => $status, 'message' => $responseHtml ) );
				}
			} catch ( \Throwable $exception ) {
				\TechXelaLatePointDebugHelper::logException( $exception, $this->logTagPrefix . '_' . __FUNCTION__ );
			}
		}

		public function updateOrder() {
			try {
				$orderedFormBlocks    = $this->params['ordered_fields'];
				$formBlocksArr        = $this->formBlockHelper::getFormBlocksArr();
				$orderedFormBlocksArr = [];
				foreach ( $orderedFormBlocks as $formBlockId => $formBlockOrder ) {
					if ( isset( $formBlocksArr[ $formBlockId ] ) ) {
						$orderedFormBlocksArr[ $formBlockId ] = $formBlocksArr[ $formBlockId ];
					}
				}
				if ( ! empty( $orderedFormBlocksArr ) && $this->formBlockHelper::saveFormBlocksArr( $orderedFormBlocksArr ) ) {
					$status       = LATEPOINT_STATUS_SUCCESS;
					$responseHtml = sprintf( esc_html_tx__( 'Order of %s Updated', 'latepoint-addons' ), $this->formBlockObjectNamePlural );
				} else {
					$status       = LATEPOINT_STATUS_ERROR;
					$responseHtml = sprintf( esc_html_tx__( 'Error Updating Order of %s', 'latepoint-addons' ), $this->formBlockObjectNamePlural );
				}
				if ( $this->get_return_format() == 'json' ) {
					$this->send_json( array( 'status' => $status, 'message' => $responseHtml ) );
				}
			} catch ( \Throwable $exception ) {
				\TechXelaLatePointDebugHelper::logException( $exception, $this->logTagPrefix . '_' . __FUNCTION__ );
			}
		}

		public function delete() {
			try {
				if ( isset( $this->params['id'] ) && ! empty( $this->params['id'] ) ) {
					$formBlockId = $this->params['id'];
					$formBlock   = $this->formBlockHelper::get( $formBlockId );

					if ( $this->formBlockHelper::delete( $formBlockId ) ) {
						do_action( 'tx_latepoint_form_block_deleted', $formBlock, $this );
						$status       = LATEPOINT_STATUS_SUCCESS;
						$responseHtml = sprintf( esc_html_tx__( '%s Removed', 'latepoint-addons' ), $this->formBlockObjectName );
					} else {
						$status       = LATEPOINT_STATUS_ERROR;
						$responseHtml = sprintf( esc_html_tx__( 'Error Removing %s', 'latepoint-addons' ), $this->formBlockObjectName );
					}
				} else {
					$status       = LATEPOINT_STATUS_ERROR;
					$responseHtml = sprintf( esc_html_tx__( 'Invalid %s ID', 'latepoint-addons' ), $this->formBlockObjectName );
				}
				if ( $this->get_return_format() == 'json' ) {
					$this->send_json( array( 'status' => $status, 'message' => $responseHtml ) );
				}
			} catch ( \Throwable $exception ) {
				\TechXelaLatePointDebugHelper::logException( $exception, $this->logTagPrefix . '_' . __FUNCTION__ );
			}
		}
	}
}