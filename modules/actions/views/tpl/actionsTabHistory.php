<?php 
$isPercentPoint = FrameWsbp::_()->getModule('bonuses')->getMainOptions('is_percent_point');

?>
<div class="row row-options-block wupsales-nosave">
	<div class="col-12 wsbp-group-label wsbp-group-first">
		<span class="wupsales-tooltip" title="<?php esc_attr_e('Only those accruals that have already taken place will be shown', 'wupsales-reward-points'); ?>"><?php esc_html_e('Show completed', 'wupsales-reward-points'); ?></span>
		<div><?php HtmlWsbp::checkboxToggle('', array('id' => 'wsbpShowCompleted')); ?></div>
	</div>
</div>
<div class="wupsales-table-list wupsales-nosave">
	<table id="wsbpHistoryList">
		<thead>
			<tr>
				<th><?php esc_html_e('Id', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Date', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Status', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Reason', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Points', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Users', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Сredited', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Actions', 'wupsales-reward-points'); ?></th>
			</tr>
		</thead>
	</table>
</div>
<div class="wupsales-clear"></div>





