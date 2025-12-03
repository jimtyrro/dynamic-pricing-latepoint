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

<div class="os-form-w">
    <h3><?= $addon['name'] ?> - <?php esc_html_tx_e( 'Purchase Code Verification', 'latepoint-addons' ); ?></h3>
    <form action=""
          data-os-action="<?php echo OsRouterHelper::build_route_name( 'tech_xela_license', 'activateLicense' ); ?>"
          data-os-success-action="reload">
		<?php if ( ! empty( $license['status_message'] ) ) { ?>
			<?php if ( $license['is_active'] === 'yes' ) { ?>
                <div class="os-form-message-w status-success">
                    <ul>
                        <li><?= $license['status_message']; ?></li>
                    </ul>
                </div>
			<?php } else { ?>
                <div class="os-form-message-w status-error">
                    <ul>
                        <li><?= $license['status_message']; ?></li>
                    </ul>
                </div>
			<?php } ?>
		<?php } else { ?>
            <div class="os-form-message-w">
                <ul>
                    <li><?php esc_html_tx_e( 'Please enter your Envato Purchase Code to receive free addon updates.', 'latepoint-addons' ) ?></li>
                </ul>
            </div>
		<?php } ?>
		<?php echo OsFormHelper::hidden_field( 'product_id', $addon['product_id'] ) ?>
        <div class="os-row">
			<?php if ( empty( $license['client_name'] ) ) { ?>
                <div class="os-col-lg-12 latepoint-message latepoint-message-subtle">
                    <small>
                        <i class="latepoint-icon latepoint-icon-info"></i>
						<?php printf( esc_html_tx__( 'If your license was purchased with guest checkout, enter your full name in the %sYour Envato Username%s field instead.', 'latepoint-addons' ), '<span class="very-bold-text">', '</span>' ); ?>
                    </small>
                </div>
			<?php } ?>
            <div class="os-col-lg-6">
				<?php echo OsFormHelper::text_field( 'license[client_name]', esc_attr__( 'Your Envato Username', 'latepoint-addons' ), $license['client_name'] ?? '', [ 'theme' => 'bordered' ] ); ?>

            </div>
            <div class="os-col-lg-6">
				<?php echo OsFormHelper::text_field( 'license[client_email]', esc_attr__( 'Your Email Address', 'latepoint-addons' ), $license['client_email'] ?? '', [ 'theme' => 'bordered' ] ); ?>
            </div>
        </div>
        <div class="os-row">
            <div class="os-col-12">
				<?php echo OsFormHelper::text_field( 'license[license_code]', esc_attr__( 'Purchase Code', 'latepoint-addons' ), $license['license_code'] ?? '', [ 'theme' => 'bordered' ] ); ?>
            </div>
        </div>
		<?php if ( ! empty( $returnTo ) ) {
			echo OsFormHelper::hidden_field( 'txLpReturnTo', $returnTo );
		} ?>
        <div class="license-buttons-w">
			<?php echo OsFormHelper::button( 'submit', esc_attr_tx__( 'Activate Purchase Code', 'latepoint-addons' ), 'submit', [
				'class'              => 'latepoint-btn latepoint-btn-outline latepoint-btn-small',
				'data-os-after-call' => 'techXelaLatePointCoreAdmin.maybeReturnToRoute',
				'data-os-pass-this'  => 'yes'
			] ); ?>
            <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"
               target="_blank"><?php esc_html_tx_e( 'Where Is My Purchase Code?', 'latepoint-addons' ) ?></a>
        </div>
    </form>
</div>