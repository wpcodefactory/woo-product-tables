<?php
/**
 * Product Table by WBW - Admin_NavController class.
 *
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

class Admin_NavControllerWtbp extends ControllerWtbp {
	public function getPermissions() {
		return array(
			WTBP_USERLEVELS => array(
				WTBP_ADMIN => array()
			),
		);
	}
}
