"use strict";

class DynamicPricingLatepointAdmin {
    constructor() {
        this.ready();
    }

    ready() {
        jQuery(document).ready(() => {
            let $rulesContainer = jQuery('.tx-form-blocks-w');

            $rulesContainer.on('change', '.form-block-modifier-components select', this.blurModifierAmount);
            $rulesContainer.on('keyup', '.form-block-modifier-components input', this.updateRuleBlockType);
            $rulesContainer.on('blur', '.form-block-modifier-components input', this.updateRuleBlockType);
        });
    }

    blurModifierAmount(event) {
        jQuery(event.currentTarget).closest('.form-block-modifier-components').find('.form-block-modifier-amount')
            .trigger('focus').trigger('keyup').trigger('blur');
    }

    updateRuleBlockType(event) {
        event.preventDefault();

        const $component = jQuery(event.currentTarget);
        const $componentsContainer = $component.closest('.form-block-modifier-components');
        const modifier = $componentsContainer.find('.form-block-modifier').val();
        const $modifierAmountField = $componentsContainer.find('.form-block-modifier-amount');
        let modifierAmount = $modifierAmountField.val();
        const $modifierTypeField = $componentsContainer.find('.form-block-modifier-type');
        if ($modifierTypeField.val() === 'percent') {
            if (!$modifierAmountField.hasClass('os-mask-percent')) {
                $modifierAmountField.removeClass('os-mask-money');
                $modifierAmountField.addClass('os-mask-percent');
                techXelaLatePointCoreAdmin.maskPercent($modifierAmountField, 2);
            }
        } else {
            if (!$modifierAmountField.hasClass('os-mask-money')) {
                $modifierAmountField.removeClass('os-mask-percent');
                $modifierAmountField.addClass('os-mask-money');
                latepoint_mask_money($modifierAmountField);
            }
        }

        $componentsContainer.closest('.os-form-block').find('.os-form-block-type').text(modifier + modifierAmount);
    }
}

window.dynamicPricingLatepointAdmin = new DynamicPricingLatepointAdmin();