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

<div class="tx-form-blocks-sw">
    <div class="tx-form-blocks-w<?= ! empty( $formBlocksContainerClasses ) ? " $formBlocksContainerClasses" : '' ?>"
		<?= $formBlockHelper::$formBlocksAreDraggable ? ( 'data-order-update-route="' .
		                                                  OsRouterHelper::build_route_name( $formBlockHelper::$formBlocksRouteController, 'updateOrder' ) .
		                                                  '" data-fields-for="' . $formBlocksFieldsForAttrVal . '"' ) : '' ?>>
		<?php
		foreach ( $formBlockHelper::getFormBlocksArr() as $formBlock ) {
			$this->vars['formBlock'] = $formBlock;
			$this->newForm();
		}
		?>
    </div>
    <div class="os-add-box tx-add-form-block-box"
         data-os-after-call="techXelaLatePointCoreAdmin.initNewFormBlock"
         data-os-pass-this="yes"
         data-os-pass-response="yes"
         data-os-action="<?php echo OsRouterHelper::build_route_name( $formBlockHelper::$formBlocksRouteController, 'newForm' ); ?>"
         data-os-output-target-do="append"
         data-os-output-target=".tx-form-blocks-w<?= ! empty( $formBlocksContainerClasses ) ? str_replace( ' ', '.', $formBlocksContainerClasses ) : '' ?>">
        <div class="add-box-graphic-w">
            <div class="add-box-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div>
        </div>
        <div class="add-box-label"><?= $formBlocksAddBoxLabel ?></div>
    </div>
</div>