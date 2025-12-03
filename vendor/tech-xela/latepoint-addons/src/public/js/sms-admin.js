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

jQuery(document).ready((function ($) {
    "use strict";

    $('.techxela-latepoint-smser-instructions-title').on('click', function (e) {
        $(this).closest('.techxela-latepoint-smser-instructions')
            .find('.techxela-latepoint-smser-instructions-body').slideToggle(200);
        return false;
    });
}));