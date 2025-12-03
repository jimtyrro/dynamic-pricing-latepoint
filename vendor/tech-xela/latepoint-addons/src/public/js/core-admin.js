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

'use strict';

class TechXelaLatePointCoreAdmin {
    constructor() {
        this.ready();
    }

    ready() {
        jQuery(document).ready(() => {
            this.initSideMenu();
            this.initLateSelects();
            this.initWpEditorTextareas();
            this.initTabs();
            this.initAddonsInfo();
            this.initFormBlocksContainer();
            this.initFormBlockConditions();
            this.initExternalLinks();
        });
    }

    initFormBlocksContainer() {
        let $rulesContainer = jQuery('.tx-form-blocks-w');

        $rulesContainer.on('change', 'select.form-block-condition-select', (event) => {
            let $select = jQuery(event.currentTarget);
            let $condition = $select.closest('.form-block-condition');
            let $conditions = $condition.closest('.form-block-conditions');

            $condition.find('.form-block-condition-select').attr('disabled', 'disabled');
            $condition.find('.form-block-condition-value-w').hide();
            $condition.addClass('os-loading');

            let params = {
                target: $condition.find('.form-block-condition-target').val(),
                target_prop: $condition.find('.form-block-condition-target-prop').val(),
                comparison: $condition.find('.form-block-condition-comparison').val(),
                form_block_id: $select.closest('.os-form-block').data('os-form-block-id'),
                condition_id: $condition.data('condition-id')
            }
            let data = {
                action: latepoint_helper.route_action,
                route_name: $conditions.data('tx-condition-refresh-route'),
                params: params,
                return_format: 'json'
            }

            jQuery.ajax({
                type: 'post',
                dataType: "json",
                url: latepoint_helper.ajaxurl,
                data: data,
                success: (response) => {
                    if (response.status === latepoint_helper.response_status.success) {
                        let $segmentsToRefresh = $select.data('refresh-segments').split(',');

                        $segmentsToRefresh.forEach((segment) => {
                            $condition.find(`.form-block-condition-${segment}-w`).replaceWith(response.message[segment]);
                        });

                        this.initFormBlockConditions();
                    } else {
                        latepoint_add_notification(latepoint_helper.techxela_laf_form_block_condition_refresh_error, 'error');
                    }

                    $condition.find('.form-block-condition-select').removeAttr('disabled');
                    $condition.removeClass('os-loading');
                }
            });
        });

        $rulesContainer.on('click', '.form-block-remove-condition', (event) => {
            if (jQuery(event.currentTarget).closest('.form-block-conditions').find('.form-block-condition').length > 1) {
                jQuery(event.currentTarget).closest('.form-block-condition').remove();
            } else {
                latepoint_add_notification(latepoint_helper.techxela_laf_form_block_condition_deletion_warning, 'error');
            }
            return false;
        });

        $rulesContainer.on('change', '.form-block-scheduling-toggle .os-form-toggler-group input', (event) => {
            let $togglerInput = jQuery(event.currentTarget);
            let $recurringSection = $togglerInput.closest('.os-form-block').find('.form-block-recurring-section');

            if ($togglerInput.val() === 'on') {
                $recurringSection.show();
            } else {
                $recurringSection.hide();
            }
        });
    }

    initNewFormBlock($elem, response) {
        this.initFormBlockConditions();
        $elem.trigger('initNewTxFormBlock');
    }

    initFormBlockConditions() {
        latepoint_init_input_masks();
        this.initLateSelects();
    }

    addFormBlockCondition($btn, response) {
        $btn.closest('.form-block-condition').after(response.message);
        this.initFormBlockConditions();
    }

    formBlockRemoved($elem) {
        $elem.closest('.os-form-block').remove();
    }

    initTabs() {
        jQuery('.tx-latepoint-tab-triggers').on('click', '.tx-latepoint-tab-trigger', function () {
            let $tabsWrapper = jQuery(this).closest('.tx-latepoint-tabs-w')
            $tabsWrapper.find('.tx-latepoint-tab-trigger.active').removeClass('active');
            $tabsWrapper.find('.tx-latepoint-tab-content').removeClass('active');
            jQuery(this).addClass('active');
            $tabsWrapper.find('.tx-latepoint-tab-content' + jQuery(this).data('tab-target')).addClass('active');
            jQuery('.tx-latepoint-tab-triggers').trigger('trigger:pulled', this);
            return false;
        });
    }

    initAddonsInfo() {
        jQuery('.addons-info-holder').on('click', '.tx-addon-action-btn', (function (event) {
            event.preventDefault();

            let actionBtn = jQuery(this);
            actionBtn.addClass('os-loading');

            let params = {
                addon_name: actionBtn.data('addon-name'),
                addon_path: actionBtn.data('addon-path'),
                addon_title: actionBtn.data('addon-title'),
                addon_pid: actionBtn.data('addon-pid'),
            };
            let data = {
                action: 'latepoint_route_call',
                route_name: actionBtn.data('route-name'),
                params: params,
                layout: 'none',
                return_format: 'json'
            };

            jQuery.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: latepoint_helper.ajaxurl,
                    data: data,
                    success: (response) => {
                        actionBtn.removeClass('os-loading');
                        if (response.status === 'success') {
                            latepoint_add_notification(response.message);
                            latepoint_load_addons_info();
                        } else if (response.code === '404') {
                            latepoint_show_data_in_lightbox(response.message);
                        } else {
                            latepoint_add_notification(response.message, 'error')
                        }
                    }
                }
            );
        }));
    }

    maybeReturnToRoute($elem) {
        let returnTo = $elem.closest('.os-form-w').find('input[name="txLpReturnTo"]');
        if (returnTo.length > 0 && returnTo.val().length > 0) {
            window.location = atob(returnTo.val());
        }
    }

    initDatePickerForm() {
        latepoint_init_custom_day_schedule();
        jQuery('.date-picker-form-w').on('click', '.tx-date-picker-save-btn', (event) => {
            event.preventDefault();

            let $btn = jQuery(event.target);
            let $datePickerForm = $btn.closest('.date-picker-form-w');
            let inputFieldId = $datePickerForm.find('input[name="input_field_id"]').val();
            let $inputField = jQuery(`#${inputFieldId}`);
            let $editBtn = $inputField.closest('.tx-date-group').find('.tx-date-field-edit-btn');
            let btnParams = new URLSearchParams(decodeURIComponent($editBtn.data('os-params')));
            
            const newDate = $datePickerForm.find('input[name="date"]').val();
            const formattedNewDate = this.formatDate(newDate);

            btnParams.set('date', formattedNewDate);
            btnParams.set('format', latepoint_helper.date_format);
            btnParams.set('input_field_id', inputFieldId);
            const btnParamsString = btnParams.toString();

            $inputField.attr('value', formattedNewDate).val(formattedNewDate);
            $editBtn.attr('data-os-params', btnParamsString).data('os-params', btnParamsString);

            latepoint_lightbox_close();
        });
    }

    formatDate(sourceDate) {
        let date = new Date(sourceDate).toISOString().slice(0, 10).split('-');
        let year = date[0];
        let month = date[1];
        let day = date[2];

        return latepoint_helper.date_format.replace('Y', year).replace('m', month).replace('d', day);
    }

    maskPercent($elem, precision = 4) {
        if (jQuery().inputmask) {
            $elem.inputmask({
                'alias': 'decimal',
                'radixPoint': latepoint_helper.decimal_separator,
                'digits': precision,
                'digitsOptional': false,
                'suffix': '%',
                'placeholder': '0.00',
                'rightAlign': false
            });
        }
    }

    initSideMenu() {
        jQuery('.latepoint-side-menu-w ul.side-menu > li > a').on('click', function (event) {
            let $menuItem = jQuery(event.currentTarget);
            let $menuItemIcon = $menuItem.find('i');

            if ($menuItemIcon.length && $menuItemIcon.hasClass('techxela-latepoint-icons')) {
                if ($menuItemIcon.hasClass('target-blank')) {
                    event.preventDefault();
                    event.stopPropagation();

                    window.open($menuItem.attr('href'), '_blank');
                }

                $menuItem.trigger('txLPSideMenuItem:clicked');
            }

            $menuItem.trigger('latepoint:sideMenuItem:clicked');
        });
    }

    initLateSelects() {
        jQuery('.os-late-select').lateSelect();
    }

    initWpEditorTextareas() {
        jQuery('textarea.tx-wp-editor-textarea').each(function (idx, elem) {
            latepoint_init_tiny_mce(jQuery(this).attr('id'));
        });
    }

    initExternalLinks() {
        jQuery('.tx-external-link, a.step-message').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            let $externalLinkElement = jQuery(event.currentTarget);
            window.open($externalLinkElement.attr('href'), $externalLinkElement.attr('target'));
        });
    }

    clearAdminNotifs() {
        jQuery('.os-notifications').remove();
    }

    randNum(length) {
        const array = new Uint32Array(length);
        self.crypto.getRandomValues(array);
        let randNum = '';

        for (const num of array) {
            randNum = randNum + `${num}`;
        }

        return randNum;
    }

    resolveObjProp(path, obj) {
        return path.split('.').reduce(function (prev, curr) {
            return prev ? prev[curr] : null
        }, obj || self);
    }
}

window.techXelaLatePointCoreAdmin = new TechXelaLatePointCoreAdmin();