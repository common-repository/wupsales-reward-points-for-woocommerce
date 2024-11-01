<?php
	$module = $this->getModule();
	$userParams = $module->getUserParams();
	$cartDiscount = (float) $userParams['cart'];
	$withDiscount = !empty($cartDiscount);

?>
<div class="wsbp-cart-wrapper wsbp-plugin">
	<div class="wsbp-cart-title"><?php esc_html_e('Enter discount', 'wupsales-reward-points'); ?> :</div>
	<div class="wsbp-cart-enabled">
		<?php 
			echo esc_html__('Use', 'wupsales-reward-points') . ' ' . esc_html($module->getNamePlural()); 
			HtmlWsbp::checkbox('wsbp_enabled', array('value' => '1', 'checked' => $withDiscount)); 
		?>
		<div class="wsbp-cart-balance"><?php esc_html_e('Your points balance', 'wupsales-reward-points'); ?> = <?php echo esc_html($module->getCurrencyPrice($userParams['points'])); ?></div>
	</div>
	<div class="wsbp-cart-form<?php echo $withDiscount ? '' : ' wsbp-hidden'; ?>">
		<?php 
			HtmlWsbp::number('wsbp_points', array('value' => $withDiscount ? $module->getCurrencyPrice($cartDiscount) : '', 'attrs' => 'step="any" min="0" max="' . $module->getCurrencyPrice($userParams['points']) . '"')); 
			HtmlWsbp::hidden('wsbp_nonce', array('value' => wp_create_nonce('wsbp-nonce')));
			HtmlWsbp::hidden('wsbp_rate', array('value' => $module->getCurrencyRate())); 
		?>
		<button class="button wsbp-cart-add-discount">
			<i class="fa fa-check" aria-hidden="true"></i>
			<?php esc_html_e('Apply', 'wupsales-reward-points'); ?>
		</button>
	</div>
</div>
