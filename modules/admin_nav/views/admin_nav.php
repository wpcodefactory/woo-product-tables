<?php
/**
 * Product Table by WBW - Admin_NavView class.
 *
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

class Admin_NavViewWtbp extends ViewWtbp {
	public function getBreadcrumbs() {
		$this->assign('breadcrumbsList', DispatcherWtbp::applyFilters('mainBreadcrumbs', $this->getModule()->getBreadcrumbsList()));
		return parent::getContent('adminNavBreadcrumbs');
	}
}
