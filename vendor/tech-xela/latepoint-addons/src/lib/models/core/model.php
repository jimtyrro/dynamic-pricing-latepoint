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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'TechXelaLatePointModel' ) ) {

	class TechXelaLatePointModel extends OsModel {
		public $id, $created_at, $updated_at, $table_name;

		public function delete_all(): bool {
			if ( $this->db->query( "TRUNCATE TABLE `$this->table_name`" ) ) {
				return true;
			}

			return false;
		}

		public function delete_where( $where = false, $where_format = null ): bool {
			if ( empty( $where ) ) {
				return $this->delete_all();
			}

			return parent::delete_where( $where, $where_format );
		}
	}

}