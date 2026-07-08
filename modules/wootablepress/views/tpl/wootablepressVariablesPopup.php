<?php
/**
 * Product Table by WBW - Wootablepress Variables Popup
 *
 * @version 2.3.0
 */
?>
<div id="wtbpVariablesModal" class="wtbpModal" style="display: none;">
	<div class="wtbpModalContent wtbpModalContentForVariations">
		<span class="wtbpCloseModal">×</span>
		<div class="wtbpModalContentPlaceholder">
			<div class="wtbpModalVariationImages"></div>
			<div class="wtbpModalVariationName"></div>
			<div class="wtbpModalVariationDescription"></div>
			<div class="wtbpModalVariationAttributes"></div>
			<?php if ($withNote) { ?>
			<div class="wtbpPropuctNoteContent">
				<label><?php esc_html_e('Product note (optional)', 'woo-product-tables'); ?></label>
				<textarea class="wtbpProductNote" placeholder="<?php esc_html_e('Add your note here, please…', 'woo-product-tables'); ?>"></textarea>
			</div>
			<?php 
			} 
			// translators:
			$cartText = sprintf('%s ', $variationToCart);
			?>
			<div class="wtbpModalVariationBtns" data-add-to-cart-text="<?php echo esc_attr($cartText); ?>"></div>
		</div>
	</div>
</div>
