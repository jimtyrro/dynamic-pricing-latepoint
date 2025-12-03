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

<?php
if ( $addons ) { ?>
	<div class="addons-boxes-w">
		<?php foreach ( $addons as $addon ) {
			$is_activated    = is_plugin_active( $addon->wp_plugin_path );
			$is_installed    = OsAddonsHelper::is_addon_installed( $addon->wp_plugin_path );
			$addon_css_class = '';
			$is_featured     = false;
			if ( $is_activated ) {
				$addon_css_class .= ' status-activated';
			}
			if ( $is_installed ) {
				$addon_css_class   .= ' status-installed';
				$addon_data        = get_plugin_data( OsAddonsHelper::get_addon_plugin_path( $addon->wp_plugin_path ) );
				$installed_version = $addon_data['Version'] ?? '1.0.0';
				if ( version_compare( $addon->version, $installed_version, 'gt' ) ) {
					$addon_css_class .= ' status-update-available';
				}
			} elseif ( $addon->is_featured == 'yes' ) {
				$addon_css_class .= ' status-is-featured';
				$is_featured     = true;
			}
			if ( empty( $addon->purchase_url ) ) {
				$addon->purchase_url = "mailto:sales@techxela.com?subject=$addon->name&body=Hey TechXela, I am interested in purchasing $addon->name";
			}

			$addon_data_html = " data-addon-name=\"$addon->wp_plugin_name\" data-addon-path=\"$addon->wp_plugin_path\" data-addon-title=\"$addon->name\" data-addon-pid=\"$addon->product_id\"";
			?>
			<div class="addon-box <?= $addon_css_class; ?>">
				<?php if ( $is_featured ) {
					echo '<div class="addon-label"><i class="latepoint-icon latepoint-icon-star"></i><span>' . esc_html__( 'Featured', 'latepoint' ) . '</span></div>';
				} ?>
				<div class="addon-media" style="background-image: url(<?= $addon->media_url ?>);"></div>
				<div class="addon-header">
					<h3 class="addon-name">
						<a target="_blank" href="<?= $addon->purchase_url ?>">
							<span><?= $addon->name ?></span>
							<i class="latepoint-icon latepoint-icon-external-link"></i>
						</a>
					</h3>
				</div>
				<div class="addon-body">
					<div class="addon-desc"><?= $addon->description; ?></div>
					<div class="addon-meta">
						<?php
						if ( $is_installed ) {
							if ( version_compare( $addon->version, $installed_version, 'gt' ) ) {
								echo '<div>' . esc_html__( 'Latest', 'latepoint' ) . ': ' . $addon->version . '</div>';
								echo '<div>' . esc_html__( 'Installed', 'latepoint' ) . ': ' . $installed_version . '</div>';
							} else {
								echo '<div>' . esc_html__( 'Installed', 'latepoint' ) . ': ' . $installed_version . '</div>';
							}
						} else {
							echo '<div>' . esc_html__( 'Latest', 'latepoint' ) . ': ' . $addon->version . '</div>';
						} ?>
					</div>
				</div>
				<div class="addon-footer">
					<?php
					if ( version_compare( $addon->required_version, LATEPOINT_VERSION, 'gt' ) ) {
						echo '<a class="os-update-plugin-link" href="' . OsRouterHelper::build_link( [
								'updates',
								'status'
							] ) . '"><span><i class="latepoint-icon latepoint-icon-refresh-cw"></i></span><span>' . esc_html__( 'Requires LatePoint', 'latepoint' ) . ' v' . $addon->required_version . '+</span></a>';
					} elseif ( $is_activated ) {
						// is activated
						if ( version_compare( $addon->version, $installed_version, 'gt' ) ) {
							echo '<a href="#" class="os-install-addon-btn tx-addon-action-btn" data-route-name="' . OsRouterHelper::build_route_name( 'tech_xela_addons', 'installAddon' ) . '" ' . $addon_data_html . '>';
							echo '<span><i class="latepoint-icon latepoint-icon-grid-18"></i></span><span>' . esc_html__( 'Update Now', 'latepoint' ) . '</span>';
							echo '</a>';
						} else {
							echo '<a href="#" class="os-subtle-addon-action-btn tx-addon-action-btn" data-route-name="' . OsRouterHelper::build_route_name( 'tech_xela_addons', 'deactivateAddon' ) . '" ' . $addon_data_html . '>';
							echo esc_html__( 'Deactivate', 'latepoint' );
							echo '</a>';
							echo '<div class="os-addon-activated-label"><span><i class="latepoint-icon latepoint-icon-checkmark"></i></span><span>' . esc_html__( 'Active', 'latepoint' ) . '</span></div>';
						}
					} else {
						// check if its installed
						if ( $is_installed ) {
							// installed but outdated
							if ( version_compare( $addon->version, $installed_version, 'gt' ) ) {
								echo '<a href="#" class="os-install-addon-btn tx-addon-action-btn" data-route-name="' . OsRouterHelper::build_route_name( 'tech_xela_addons', 'installAddon' ) . '" ' . $addon_data_html . '>';
								echo '<span><i class="latepoint-icon latepoint-icon-grid-18"></i></span><span>' . esc_html__( 'Update Now', 'latepoint' ) . '</span>';
								echo '</a>';
							} else {
								echo '<a href="#" class="os-subtle-addon-action-btn tx-addon-action-btn" data-route-name="' . OsRouterHelper::build_route_name( 'tech_xela_addons', 'deleteAddon' ) . '" ' . $addon_data_html . '>';
								echo esc_html__( 'Delete', 'latepoint' );
								echo '</a>';
								// installed but not activated
								echo '<a href="#" class="os-install-addon-btn tx-addon-action-btn" data-route-name="' . OsRouterHelper::build_route_name( 'tech_xela_addons', 'activateAddon' ) . '" ' . $addon_data_html . '>';
								echo '<span><i class="latepoint-icon latepoint-icon-box"></i></span><span>' . esc_html__( 'Activate', 'latepoint' ) . '</span>';
								echo '</a>';
							}
						} // not installed
						elseif ( $addon->purchased === 'yes' ) {
							echo '<a href="#" class="os-install-addon-btn tx-addon-action-btn" data-route-name="' . OsRouterHelper::build_route_name( 'tech_xela_addons', 'installAddon' ) . '" ' . $addon_data_html . '>';
							echo '<span>' . esc_html__( 'Install Now', 'latepoint' ) . '</span>';
							echo '</a>';
						} else {
							if ( $addon->price > 0 ) {
								echo '<span class="addon-price">' . '$' . number_format( $addon->price ) . '</span>';
							}
							echo '<a target="_blank" href="' . $addon->purchase_url . '" class="os-purchase-addon-btn">';
							echo '<span><i class="latepoint-icon latepoint-icon-external-link"></i></span><span>' . esc_html__( 'Learn More', 'latepoint' ) . '</span>';
							echo '</a>';
						}
					} ?>
					</a>
				</div>
			</div>
		<?php } ?>
	</div>
<?php } ?>
