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

if ( ! class_exists( 'TechXelaLatePointJungleHelper' ) ) :

	final class TechXelaLatePointJungleHelper {

		public static function applyFrontSummaryStylePatches() {
			if ( ! in_array( \OsParamsHelper::get_param( 'current_step' ), [ 'verify', 'confirmation' ] ) ) {
				wp_add_inline_style( 'techxela-latepoint-addons-core-front',
					".latepoint-w .latepoint-summary-w .price-breakdown-w .summary-box-heading{display:block!important;margin-top:10px!important;margin-bottom:10px!important;}" .
					".latepoint-w .latepoint-summary-w .summary-price-item-w.spi-total{margin-top:10px!important;}" .
					".latepoint-w .latepoint-summary-w .price-breakdown-w .pb-heading {text-transform:unset;font-size:19px!important;font-weight:500!important;color:#1f222b!important;}" );
			}
		}

		public static function applyMonkeyPatches() {
			$patchMappings = [
				[
					'banana' => base64_decode( 'T3NNb2RlbA==' ), // OsModel
					'peels'  => [
						[
							'search'   => base64_decode( "bGF0ZXBvaW50X21vZGVsX3dpbGxfYmVfZGVsZXRlZA==" ), // will_be_deleted
							'replaced' => base64_decode( "aWYoJGlkICYmICR0aGlzLT5kYi0+ZGVsZXRl" ),
							'replacer' => base64_decode( "ZG9fYWN0aW9uKCdsYXRlcG9pbnRfbW9kZWxfd2lsbF9iZV9kZWxldGVkJywgJHRoaXMpOwogICAgaWYoJGlkICYmICR0aGlzLT5kYi0+ZGVsZXRl" )
						]
					]
				],
				[
					'banana' => base64_decode( 'T3NEZWJ1Z0NvbnRyb2xsZXI=' ), // Debug Ctrl
					'peels'  => [
						[
							'search'   => base64_decode( "bGF0ZXBvaW50X2RlYnVnX2xpc3Rfb2ZfYWRkb25z" ), // List of add-ons
							'replaced' => base64_decode( "T3NVcGRhdGVzSGVscGVyOjpnZXRfbGlzdF9vZl9hZGRvbnMoKQ==" ),
							'replacer' => base64_decode( "YXBwbHlfZmlsdGVycygnbGF0ZXBvaW50X2RlYnVnX2xpc3Rfb2ZfYWRkb25zJywgT3NVcGRhdGVzSGVscGVyOjpnZXRfbGlzdF9vZl9hZGRvbnMoKSk=" )
						]
					]
				],
			];

			foreach ( $patchMappings as $patchMapping ) {
				self::applyMonkeyPatch( $patchMapping['banana'], $patchMapping['peels'] );
			}
		}

		/**
		 * 🍌🐒
		 *
		 * @param string $banana
		 * @param array $peels
		 *
		 * @return bool
		 */
		public static function applyMonkeyPatch( string $banana, array $peels ): bool {
			if ( ! empty( $banana ) && ! empty( $peels ) ) {
				try {
					if ( file_exists( $banana ) ) {
						$potassium = $banana;
					} elseif ( class_exists( $banana ) ) {
						$potassium = ( new \ReflectionClass( $banana ) )->getFileName();
					}

					if ( isset( $potassium ) && is_writable( $potassium ) ) {
						foreach ( $peels as $peel ) {
							$fibre = file_get_contents( $potassium );

							if ( strpos( $fibre, $peel['search'] ) === false ) {
								file_put_contents( $potassium, str_replace( $peel['replaced'], $peel['replacer'], $fibre ) );
							}
						}

						return true;
					}
				} catch ( \Throwable $exception ) {
					TechXelaLatePointDebugHelper::logException( $exception, 'apply_monkey_patch' );
				}
			}

			return false;
		}
	}

endif;