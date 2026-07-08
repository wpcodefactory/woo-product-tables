<?php
/**
 * Product Table for WooCommerce by WBW - Show Admin Notice
 *
 * @version 2.3.0
 *
 * @author woobewoo
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="error notice">
	<p><?php HtmlWtbp::echoEscapedHtml($this->errorMsg); ?></p>
</div>
