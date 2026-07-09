<?php
/**
 * Product Table by WBW - Date class.
 *
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

class DateWtbp {
	public static function _( $time = null ) {
		if (is_null($time)) {
			$time = time();
		}
		return gmdate(WTBP_DATE_FORMAT_HIS, $time);
	}
}
