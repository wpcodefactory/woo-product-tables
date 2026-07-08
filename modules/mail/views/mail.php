<?php
/**
 * Product Table by WBW - MailView class.
 *
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

class MailViewWtbp extends ViewWtbp {
	public function getTabContent() {
		FrameWtbp::_()->getModule('templates')->loadJqueryUi();
		FrameWtbp::_()->addScript('admin.' . $this->getCode(), $this->getModule()->getModPath() . 'js/admin.' . $this->getCode() . '.js');
		
		$this->assign('options', FrameWtbp::_()->getModule('options')->getCatOpts( $this->getCode() ));
		$this->assign('testEmail', FrameWtbp::_()->getModule('options')->get('notify_email'));
		return parent::getContent('mailAdmin');
	}
}
