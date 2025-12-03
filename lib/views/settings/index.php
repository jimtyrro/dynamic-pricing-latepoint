<?php
/*
 * Dynamic Pricing for LatePoint
 * Copyright (c) 2023 TechXela (https://codecanyon.net/user/tech-xela). All Rights Reserved.
 *
 * LICENSE
 * -------
 * This software is furnished under license(s) and may be used and copied only in accordance with the terms of such
 * license(s) along with the inclusion of the above copyright notice. If you purchased this software through CodeCanyon,
 * please read the full license(s) at: https://codecanyon.net/licenses/standard
 *
 */
?>

<div class="os-section-header">
    <h3><?php esc_html_e( 'Pricing Rules', 'dynamic-pricing-latepoint' ); ?></h3>
</div>
<div class="latepoint-message latepoint-message-subtle">
	<?php esc_html_tx_e( "Define the rules that will apply to customers' booking or service price.", 'dynamic-pricing-latepoint' ); ?>
</div>
<?php ( new OsTechXelaDynamicPricingRulesController() )->formBlocksContainer(); ?>