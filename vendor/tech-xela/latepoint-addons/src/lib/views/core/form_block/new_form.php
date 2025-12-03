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

/** @var array $formBlock */
/** @var string $formBlockObjectName */
/** @var string $newFormBlockName */
/** @var \TechXela\LatePointAddons\Traits\FormBlock\IsFormBlockHelper|\TechXela\LatePointAddons\Traits\FormBlock\IsConditionalFormBlockHelper|\TechXela\LatePointAddons\Traits\FormBlock\IsSchedulableFormBlockHelper|\TechXela\LatePointAddons\Traits\FormBlock\IsRecurringFormBlockHelper $formBlockHelper */
?>

<div data-os-form-block-id="<?= $formBlockHelper::getSettingValue( $formBlock, 'id' ) ?>"
     class="os-form-block tx-form-block<?= empty( $formBlockHelper::getSettingValue( $formBlock, 'name' ) ) ? ' os-is-editing' : '' ?>
<?= ! empty( $formBlockHelper::getFormBlockType( $formBlock, $this ) ) ? ( ' os-form-block-type-' . $formBlockHelper::getFormBlockType( $formBlock, $this ) ) : '' ?>
<?= ( $formBlockHelper::getSettingValue( $formBlock, 'enabled' ) === 'on' ) ? ' status-active' : ' status-disabled' ?>">
	<div class="os-form-block-i">
		<div class="os-form-block-header">
			<?= $formBlockHelper::$formBlocksAreDraggable ? '<div class="os-form-block-drag"></div>' : '' ?>
			<div
				class="os-form-block-name"><?= $formBlockHelper::getSettingValue( $formBlock, 'name', $newFormBlockName ) ?></div>
			<div class="os-form-block-type"><?= $formBlockHelper::getFormBlockType( $formBlock, $this ) ?></div>
			<div class="os-form-block-edit-btn"><i class="latepoint-icon latepoint-icon-edit-3"></i></div>
		</div>
		<div class="os-form-block-params os-form-w">
			<div class="sub-section-row">
				<div class="sub-section-label">
					<h3><?php esc_html_e( 'Name', 'latepoint' ) ?></h3>
				</div>
				<div class="sub-section-content">
					<?= OsFormHelper::text_field( $formBlockHelper::generateSettingName( $formBlock, 'name' ),
						false,
						$formBlockHelper::getSettingValue( $formBlock, 'name' ),
						[ 'class' => 'os-form-block-name-input', 'theme' => 'bordered' ] );
					?>
				</div>
			</div>
			<?php $formBlockHelper::afterFormBlockNameSection( $formBlock, $this ); ?>
			<?php if ( \TechXelaLatePointUtilHelper::classUsesTrait( $formBlockHelper, \TechXela\LatePointAddons\Traits\FormBlock\IsConditionalFormBlockHelper::class ) ) { ?>
				<div class="sub-section-row">
					<div class="sub-section-label">
						<h3><?php esc_html_tx_e( 'Conditional', 'latepoint-addons' ) ?></h3>
					</div>
					<div class="sub-section-content">
						<?= OsFormHelper::toggler_field( $formBlockHelper::generateSettingName( $formBlock, 'conditional' ), esc_html_tx__( 'Apply only when conditions are met', 'latepoint-addons' ), ( $formBlockHelper::getSettingValue( $formBlock, 'conditional' ) == 'on' ), 'form-block-conditions-for-' . esc_attr( $formBlockHelper::getSettingValue( $formBlock, 'id' ) ) ); ?>
						<div class="form-block-conditions pe-conditions"
						     data-tx-condition-refresh-route="<?= OsRouterHelper::build_route_name( $formBlockHelper::$formBlocksRouteController, 'refreshCondition' ) ?>"
						     id="form-block-conditions-for-<?= esc_attr( $formBlockHelper::getSettingValue( $formBlock, 'id' ) ) ?>"
						     style="<?= ( $formBlockHelper::getSettingValue( $formBlock, 'conditional' ) == 'on' ) ? 'display:  block;' : ''; ?>">
							<div class="pe-conditions-heading"><?php esc_html_tx_e( 'Apply if', 'latepoint-addons' ); ?>
								:
							</div>
							<?php
							if ( $formBlockHelper::getSettingValue( $formBlock, 'conditions' ) ) {
								foreach ( $formBlockHelper::getSettingValue( $formBlock, 'conditions' ) as $conditionId => $condition ) {
									echo $formBlockHelper::generateConditionForm( $formBlockHelper::getSettingValue( $formBlock, 'id' ), $conditionId, $condition );
								}
							} else {
								echo $formBlockHelper::generateConditionForm( $formBlockHelper::getSettingValue( $formBlock, 'id' ) );
							}
							?>
						</div>
					</div>
				</div>
			<?php } ?>
			<?php if ( \TechXelaLatePointUtilHelper::classUsesTrait( $formBlockHelper, \TechXela\LatePointAddons\Traits\FormBlock\IsSchedulableFormBlockHelper::class ) ) { ?>
				<div class="sub-section-row form-block-scheduling-section">
					<div class="sub-section-label">
						<h3><?php esc_html_tx_e( 'Scheduling', 'latepoint-addons' ) ?></h3>
					</div>
					<div class="sub-section-content">
						<div class="form-block-scheduling-toggle">
							<?php echo
							OsFormHelper::toggler_field(
								$formBlockHelper::generateSettingName( $formBlock, 'scheduling' ),
								esc_html_tx__( 'Apply only during a specific date range', 'latepoint-addons' ),
								( $formBlockHelper::getSettingValue( $formBlock, 'scheduling' ) == 'on' ),
								'form-block-schedule-for-' . esc_attr( $formBlockHelper::getSettingValue( $formBlock, 'id' ) )
							); ?>
						</div>
						<div
							id="form-block-schedule-for-<?= esc_attr( $formBlockHelper::getSettingValue( $formBlock, 'id' ) ); ?>"
							class="ws-period mt-15"
							style="display: <?= ( $formBlockHelper::getSettingValue( $formBlock, 'scheduling' ) == 'on' ) ? 'block' : 'none'; ?>;">
							<?php
							echo \TechXelaLatePointFormHelper::datePickerField(
								$formBlockHelper::generateSettingName( $formBlock, 'schedule.from' ),
								esc_html_tx__( 'From', 'latepoint-addons' ),
								$formBlockHelper::getSettingValue( $formBlock, 'schedule.from' ),
								false,
								true
							);
							echo \TechXelaLatePointFormHelper::datePickerField(
								$formBlockHelper::generateSettingName( $formBlock, 'schedule.to' ),
								esc_html_tx__( 'To', 'latepoint-addons' ),
								$formBlockHelper::getSettingValue( $formBlock, 'schedule.to' ),
								false,
								true
							);
							?>
						</div>
					</div>
				</div>
				<?php if ( \TechXelaLatePointUtilHelper::classUsesTrait( $formBlockHelper, \TechXela\LatePointAddons\Traits\FormBlock\IsRecurringFormBlockHelper::class ) ) { ?>
					<div class="sub-section-row form-block-recurring-section"
					     style="display: <?= ( $formBlockHelper::getSettingValue( $formBlock, 'scheduling' ) == 'on' ) ? 'flex' : 'none'; ?>;">
						<div class="sub-section-label">
							<h3><?php esc_html_tx_e( 'Recurring', 'latepoint-addons' ); ?></h3>
						</div>
						<div class="sub-section-content">
							<?php echo
							OsFormHelper::toggler_field(
								$formBlockHelper::generateSettingName( $formBlock, 'recurring' ),
								esc_html_tx__( 'Repeat the above schedule', 'latepoint-addons' ),
								( $formBlockHelper::getSettingValue( $formBlock, 'recurring' ) == 'on' ),
								'form-block-recurrence-for-' . esc_attr( $formBlockHelper::getSettingValue( $formBlock, 'id' ) )
							); ?>
							<div
								id="form-block-recurrence-for-<?= esc_attr( $formBlockHelper::getSettingValue( $formBlock, 'id' ) ); ?>"
								class="pe-conditions"
								style="display: <?= ( $formBlockHelper::getSettingValue( $formBlock, 'recurring' ) == 'on' ) ? 'block' : 'none'; ?>;">
								<div
									class="pe-conditions-heading"><?php esc_html_tx_e( 'Repeat every', 'latepoint-addons' ); ?>
									:
								</div>
								<div class="pe-condition">
									<div class="os-col-lg-6">
										<?= OsFormHelper::text_field( $formBlockHelper::generateSettingName( $formBlock, 'recurrence.value' ),
											false,
											$formBlockHelper::getSettingValue( $formBlock, 'recurrence.value' ),
											[
												'theme' => 'bordered',
												'class' => 'form-block-recurrence-value'
											] ); ?>
									</div>
									<div class="os-col-lg-6">
										<?= OsFormHelper::select_field( $formBlockHelper::generateSettingName( $formBlock, 'recurrence.unit' ),
											false,
											$formBlockHelper::getRecurrenceUnits(),
											$formBlockHelper::getSettingValue( $formBlock, 'recurrence.unit' ),
											[ 'class' => 'form-block-recurrence-unit' ] ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php }
			} ?>
			<?php $formBlockHelper::beforeFormBlockEnabledSection( $formBlock, $this ); ?>
			<div class="sub-section-row form-block-enabled-section">
				<div class="sub-section-label">
					<h3><?php esc_html_tx_e( 'Active', 'latepoint-addons' ) ?></h3>
				</div>
				<div class="sub-section-content">
					<?= OsFormHelper::toggler_field( $formBlockHelper::generateSettingName( $formBlock, 'enabled' ),
						false,
						$formBlockHelper::getSettingValue( $formBlock, 'enabled' ) === 'on',
						false,
						'large'
					) ?>
				</div>
			</div>

			<div class="os-form-block-buttons">
				<a href="#" class="latepoint-btn latepoint-btn-danger pull-left tx-remove-form-block-btn"
				   data-os-prompt="<?php printf( esc_html_tx__( 'Are you sure you want to delete this %s?', 'latepoint-addons' ), strtolower( $formBlockObjectName ) ); ?>"
				   data-os-after-call="techXelaLatePointCoreAdmin.formBlockRemoved" data-os-pass-this="yes"
				   data-os-action="<?= OsRouterHelper::build_route_name( $formBlockHelper::$formBlocksRouteController, 'delete' ); ?>"
				   data-os-params="<?= OsUtilHelper::build_os_params( [ 'id' => $formBlockHelper::getSettingValue( $formBlock, 'id' ) ] ) ?>"><?php esc_html_e( 'Delete', 'latepoint' ); ?></a>
				<a href="#" class="os-form-block-save-btn tx-form-block-save-btn latepoint-btn latepoint-btn-primary"
				   data-os-action="<?= OsRouterHelper::build_route_name( $formBlockHelper::$formBlocksRouteController, 'save' ); ?>"
				   data-os-source-of-params=".os-form-block-params">
					<span><?php printf( esc_html_tx__( 'Save %s', 'latepoint-addons' ), $formBlockObjectName ); ?></span>
				</a>
			</div>
			<?= OsFormHelper::hidden_field( $formBlockHelper::generateSettingName( $formBlock, 'id' ), $formBlockHelper::getSettingValue( $formBlock, 'id' ), [ 'class' => 'os-form-block-id' ] ); ?>
		</div>
	</div>
    <a href="#"
       data-os-prompt="<?php printf( esc_html_tx__( 'Are you sure you want to delete this %s?', 'latepoint-addons' ), strtolower( $formBlockObjectName ) ); ?>"
       data-os-after-call="techXelaLatePointCoreAdmin.formBlockRemoved"
       data-os-pass-this="yes"
       data-os-action="<?= OsRouterHelper::build_route_name( $formBlockHelper::$formBlocksRouteController, 'delete' ); ?>"
       data-os-params="<?= OsUtilHelper::build_os_params( [ 'id' => $formBlockHelper::getSettingValue( $formBlock, 'id' ) ] ) ?>" class="os-remove-form-block"><i class="latepoint-icon latepoint-icon-cross"></i></a>
</div>


