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

"use strict";

class TechXelaLatePointQuickSmsAdmin {
    constructor() {
        this.observableCalendarClasses = ['.agents-timelines-w', '.calendar-daily-agent-w', '.calendar-week-agent-w', '.calendar-month-agents-w'];
        this.bookingBoxClassMappings = [
            {
                'container': '.booking-block',
                'subContainer': '.appointment-box-small',
                'parentCalendars': ['.agents-timelines-w']
            },
            {
                'container': '.ch-day-booking',
                'subContainer': '.ch-day-booking-i',
                'parentCalendars': ['.calendar-daily-agent-w', '.calendar-week-agent-w']
            },
            {
                'container': '.ma-day-booking',
                'subContainer': '.appointment-box-small',
                'parentCalendars': ['.calendar-month-agents-w']
            }
        ];
        this.ready();
    }

    ready() {
        jQuery(document).ready(() => {
                let thisClass = this;

                this.bookingBoxClassMappings.forEach((classMapping) => {
                    this.maybeAddSendButtonToBookingBox(classMapping.container, classMapping.subContainer);
                });

                this.observableCalendarClasses.forEach((calendarClass) => {
                    let $target = jQuery('.latepoint-content ' + calendarClass);
                    if ($target.length) {
                        this.setupBookingBoxObserver(calendarClass);
                    }
                });

                jQuery('.latepoint-content-w').on('click', '.tx-qsms-booking-box-send-btn', (e) => {
                    e.stopPropagation();

                    let $btn = jQuery(e.currentTarget);
                    if ($btn.hasClass('os-loading')) {
                        return false;
                    }
                    $btn.addClass('os-loading');

                    let data = {
                        action: 'latepoint_route_call',
                        route_name: latepoint_helper.techxela_qsms_send_form_route,
                        params: $btn.data('tx-params'),
                        return_format: 'json'
                    };

                    techXelaLatePointCoreAdmin.clearAdminNotifs();

                    jQuery.ajax({
                        type: "post",
                        dataType: "json",
                        url: latepoint_helper.ajaxurl,
                        data: data,
                        success: function success(response) {
                            if (response.status === "success") {
                                latepoint_show_data_in_lightbox(response.message);
                                thisClass.initSendForm($btn);
                            } else {
                                latepoint_add_notification(response.message, 'error');
                            }

                            $btn.removeClass('os-loading');
                        }
                    });

                    return false;
                });
            }
        );
    }

    setupBookingBoxObserver(calendarClass) {
        let target = jQuery('.latepoint-content');

        const thisClass = this;

        const config = {childList: true, subtree: true};

        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === "childList") {
                    thisClass.bookingBoxClassMappings.forEach((classMapping) => {
                        if (classMapping.parentCalendars.includes(calendarClass)) {
                            thisClass.maybeAddSendButtonToBookingBox(classMapping.container, classMapping.subContainer);
                        }
                    });
                }
            });
        });

        observer.observe(target[0], config);
    }

    maybeAddSendButtonToBookingBox(containerClass, subContainerClass) {
        for (const boxInfo of jQuery(containerClass)) {
            let $info = jQuery(boxInfo).find(subContainerClass);
            if (!$info.find('.tx-qsms-booking-box-send-btn').length) {
                $info.append(latepoint_helper.techxela_qsms_quick_sms_btn_html);
                let $sendBtn = jQuery(boxInfo).find('.tx-qsms-booking-box-send-btn');

                if ($sendBtn.length) {
                    $sendBtn.attr('data-tx-id', techXelaLatePointCoreAdmin.randNum(1));
                    $sendBtn.attr('data-tx-params', jQuery(boxInfo).data('os-params'));

                    if (latepoint_helper.techxela_qsms_indicate_sent) {
                        $sendBtn.addClass('os-loading');

                        let data = {
                            action: 'latepoint_route_call',
                            route_name: latepoint_helper.techxela_qsms_send_count_route,
                            params: $sendBtn.data('tx-params'),
                            return_format: 'json'
                        };

                        jQuery.ajax({
                            type: "post",
                            dataType: "json",
                            url: latepoint_helper.ajaxurl,
                            data: data,
                            success: function success(response) {
                                if (response.message > 0) {
                                    if ($sendBtn.hasClass('latepoint-btn-primary') && !$sendBtn.hasClass('latepoint-btn-success')) {
                                        $sendBtn.addClass('latepoint-btn-success');
                                        $sendBtn.removeClass('latepoint-btn-primary');
                                    }
                                }

                                $sendBtn.removeClass('os-loading');
                            }
                        });
                    }
                }
            }
        }
    }

    initSendForm($sendBtn) {
        if ($sendBtn !== undefined) {
            jQuery('.tx-qsms-send-form form #booking_id').after(`<input type="hidden" name="tx_from_btn_id" value="${$sendBtn.data('tx-id')}">`);
        }

        jQuery('.tx-qsms-send-form').on('change', '.tx-qsms-sf-template-select', (e) => {
            let templateId = jQuery(e.currentTarget).val();

            if (templateId.length) {
                let data = {
                    action: 'latepoint_route_call',
                    route_name: latepoint_helper.techxela_qsms_get_template_content_route,
                    params: 'id=' + templateId,
                    return_format: 'json'
                };

                techXelaLatePointCoreAdmin.clearAdminNotifs();

                jQuery.ajax({
                    type: "post",
                    dataType: "json",
                    url: latepoint_helper.ajaxurl,
                    data: data,
                    success: function success(response) {
                        if (response.status === "success") {
                            jQuery(e.currentTarget).closest('.tx-qsms-send-form').find('.tx-qsms-sf-message-field').val(response.message);
                        } else {
                            latepoint_add_notification(response.message, 'error');
                        }
                    }
                });
            } else {
                jQuery(e.currentTarget).closest('.tx-qsms-send-form').find('.tx-qsms-sf-message-field').val('');
            }
        });
    }

    checkSentCount(response) {
        let $sendBtn = jQuery(`a[data-tx-id="${response.from_btn_id}"`);
        if ($sendBtn.hasClass('latepoint-btn-primary') && !$sendBtn.hasClass('latepoint-btn-success')) {
            $sendBtn.addClass('os-loading');

            let data = {
                action: 'latepoint_route_call',
                route_name: latepoint_helper.techxela_qsms_send_count_route,
                params: $sendBtn.data('tx-params'),
                return_format: 'json'
            };

            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: latepoint_helper.ajaxurl,
                data: data,
                success: function success(response) {
                    if (response.message > 0) {
                        $sendBtn.addClass('latepoint-btn-success');
                        $sendBtn.removeClass('latepoint-btn-primary');
                    }

                    $sendBtn.removeClass('os-loading');
                    $sendBtn.click();
                }
            });

            return false;
        }

        $sendBtn.click();
        setTimeout(() => {
            latepoint_add_notification(response.message, response.status)
        }, 1500);
    }
}

window.techXelaLatePointQuickSmsAdmin = new TechXelaLatePointQuickSmsAdmin();
window.txQsmsCheckSentCount = techXelaLatePointQuickSmsAdmin.checkSentCount;