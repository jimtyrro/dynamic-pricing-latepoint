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

<?php if ( ! empty( $flashMsg ) ) { ?>
    <div class="os-row">
        <div class="os-form-message-w status-error">
            <ul>
                <li><?= esc_html( $flashMsg ) ?></li>
            </ul>
        </div>
    </div>
<?php } ?>

<div class="os-row">
	<?php if ( ! empty( $addons ) ) {
		foreach ( $addons as $addon ) {
			$license = $addon['license']
			?>
            <div class="os-col-lg-6 techxela-license-card">
				<?php if ( $addon['license']['is_active'] === 'yes' ) { ?>
                    <div class="active-license-info is-active">
                        <div class="version-check-icon"></div>
                        <h3><?= $addon['name'] . ' - v' . $addon['version'] ?></h3>
                        <h4 class="techxela-license-card-sub-header"><?php esc_html_tx_e( 'Your license is active! Enjoy free, lifetime addon updates.', 'latepoint-addons' ) ?></h4>
                        <div class="license-info-buttons-w">
                            <a href="#" class="latepoint-show-license-details">
                                <i class="latepoint-icon latepoint-icon-file-text"></i>
                                <span><?php esc_html_tx_e( 'License Info', 'latepoint-addons' ) ?></span>
                            </a>
                            <a href="#"
                               data-os-action="<?= OsRouterHelper::build_route_name( 'tech_xela_license', 'deactivateLicense' ); ?>"
                               data-os-prompt="<?php esc_attr_tx_e( 'Are you sure you want to deactivate your license?', 'latepoint-addons' ) ?>"
                               data-os-success-action="reload"
                               data-os-params="<?= OsUtilHelper::build_os_params( [ 'product_id' => $addon['product_id'] ] ) ?>"
                               class="os-deactivate-license-btn">
                                <i class="latepoint-icon latepoint-icon-slash"></i>
                                <span><?php esc_html_tx_e( 'Deactivate License', 'latepoint-addons' ) ?></span>
                            </a>
                        </div>
                        <div class="license-info-w" style="display: none;">
                            <ul>
                                <li><span><?= esc_html_tx__( 'Your Envato Username', 'latepoint-addons' ) ?></span><strong><?= $license['client_name']; ?></strong></li>
                                <li><span><?= esc_html_tx__( 'Your Email Address', 'latepoint-addons' ) ?></span><strong><?= $license['client_email']; ?></strong></li>
                                <li><span><?= esc_html_tx__( 'Purchase Code', 'latepoint-addons' ) ?></span><strong><?= $license['license_code']; ?></strong></li>
                            </ul>
                        </div>                        
                    </div>
				<?php } else { ?>
                    <div class="active-license-info">
                        <div class="version-warn-icon"></div>
                        <h3><?= $addon['name'] . ' - v' . $addon['version'] ?></h3>
                        <h4 class="techxela-license-card-sub-header"><?php esc_html_tx_e( 'Register your license to receive free addon updates.', 'latepoint-addons' ) ?></h4>
                        <div class="license-info-w">
							<?php include( '_license_form.php' ); ?>
                        </div>
                    </div>
				<?php } ?>
            </div>
		<?php }
	}
	?>
</div>