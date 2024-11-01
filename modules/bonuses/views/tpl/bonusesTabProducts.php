<?php 
$isPercentPoint = FrameWsbp::_()->getModule('bonuses')->getMainOptions('is_percent_point');
HtmlWsbp::hidden('', array('value' => UtilsWsbp::jsonEncode($this->settings), 'attrs' => 'id="wsbpLangSettingsJson" class="wupsales-nosave"'));

?>
<div class="row row-options-block wupsales-nosave">
	<div class="col-12 wsbp-group-label wsbp-group-first">
		<span class="wupsales-tooltip" title="<?php esc_attr_e('If the product is variable, you can set a reward for each variation separately. The value set by the variation takes precedence over the value set by the variable product', 'wupsales-reward-points'); ?>"><?php esc_html_e('Show variations', 'wupsales-reward-points'); ?></span>
		<div><?php HtmlWsbp::checkboxToggle('', array('id' => 'wsbpShowVariations')); ?></div>
	</div>
</div>
<div class="wupsales-table-list wupsales-nosave">
	<table id="wsbpProductsList">
		<thead>
			<tr>
				<th><input type="checkbox" class="wsbpCheckAll"></th>
				<th><?php esc_html_e('Id', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Name', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Points', 'wupsales-reward-points'); ?></th>
			</tr>
		</thead>
	</table>
</div>
<div class="wupsales-clear"></div>
<div class="wupsales-hidden">
	<div id="wsbpDialogRecalc" title="<?php esc_attr_e('Recacl products points', 'wupsales-reward-points'); ?>">
		<div class="wsbp-info-desc">
			<?php esc_html_e('For the correct and fast operation of product filters and calculation user rewards, the plugin create the corresponding meta-parameters. These parameters are automatically updated by editing/creating products and plugin settings. But if you\'ve edited the products with third-party plugins or methods and/or noticed that the plugin doesn\'t work correctly, then click button Run to force a refresh of the products settings. If you have many products, the process may take some time.', 'wupsales-reward-points'); ?>
		</div>
		<div class="wupsales-center options-values">
			<div class="options-value">
				<?php HtmlWsbp::checkboxToggle('in_cron'); ?>
				<div class="options-label">run in background</div>
			</div>
		</div>
	</div>
	<div id="wsbpDialogSetPoint" title="<?php esc_attr_e('Set points', 'wupsales-reward-points'); ?>">
		<div class="wupsales-center">
			<div class="wupsales-input-group">
				<input type="number" min="0" class="wupsales-width80 wsbp-input-point">
				<select class="wsbp-select-point" data-default="<?php echo $isPercentPoint ? '%' : ''; ?>"><option value="%">%</option><option value=""><?php esc_attr_e('points', 'wupsales-reward-points'); ?></option></select>
			</div>
		</div>
	</div>
</div>




