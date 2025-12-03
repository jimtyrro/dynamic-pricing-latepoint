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

/**
 * @param {string} message Message to notify
 * @param {string} message_type Type of notification to show
 * @param {boolean} persist Force notification to be displayed indefinitely
 * @param {boolean} close Force close notification
 * @param {int} timeout Time (in seconds) for which notification should be displayed
 */
function tx_latepoint_notif(message, message_type = 'success', persist = false, close = false, timeout = 10) {
    if (!close) {
        latepoint_add_notification(message, message_type);
        if (!persist) {
            setTimeout(() => jQuery('.os-notifications').remove(), timeout * 1000)
        }
    } else {
        jQuery('.os-notifications').remove();
    }
}

class TechXelaLatePointCoreFront {
    constructor() {
        this.ready();
    }

    ready() {
        jQuery(document).ready(() => {
            this.initCustomerDashboardTabs();
        });
    }

    initCustomerDashboardTabs() {
        let params = new URLSearchParams(location.search);
        if (params.has('active_customer_tab')) {
            const targetParam = params.get('active_customer_tab');
            const tabTarget = `.tab-content-customer-${targetParam}`;

            if (jQuery(tabTarget).length > 0) {
                jQuery('.latepoint-tab-content').removeClass('active');
                jQuery(tabTarget).addClass('active');
                jQuery('.latepoint-tab-trigger').removeClass('active');
                jQuery(`a[data-tab-target="${tabTarget}"]`).addClass('active');
            }

            jQuery('.customer-dashboard-tabs')
                .closest('.latepoint-tabs-w').trigger('txLatePoint::initCustomerDashboardTabs', {
                targetParam: targetParam,
                tabTarget: tabTarget
            })
        }

        jQuery('.customer-dashboard-tabs').find('.latepoint-tab-trigger').on('click', function (event) {
            const $tabTrigger = jQuery(event.currentTarget);

            const tabTarget = $tabTrigger.data('tab-target');
            if (tabTarget && tabTarget.length) {
                let targetParam = tabTarget.replace('.tab-content-customer-', '');
                let params = new URLSearchParams(location.search);
                if (!params.has('active_customer_tab') || params.get('active_customer_tab') !== targetParam) {
                    params.set('active_customer_tab', targetParam);
                    history.replaceState(null, '', '?' + params + location.hash);
                }
            }

            $tabTrigger.closest('.customer-dashboard-tabs').trigger('txLatePoint::customerDashboardTabTriggerClicked', {
                tabTrigger: $tabTrigger[0]
            })
        });

        const $customerDashboardTabs = jQuery('.customer-dashboard-tabs');
        if ($customerDashboardTabs.length) {
            jQuery([document.documentElement, document.body]).animate({
                scrollTop: $customerDashboardTabs.closest('.latepoint-tabs-w').offset().top - 30
            }, 200);
        }
    }
}

window.techXelaLatePointCoreFront = new TechXelaLatePointCoreFront();