<?php
/**
 * Product Table by WBW - FavoritesModel class.
 *
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

class FavoritesModelWtbp extends ModelWtbp {
	public function __construct() {
		$this->_setTbl('favorites');
	}
}
