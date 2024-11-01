<div class="wsbpAdminFooterShell wsbpHidden">
	<div class="wsbpAdminFooterCell">
		<?php echo esc_html(WSBP_WP_PLUGIN_NAME); ?>
		<?php esc_html_e('Version', 'wupsales-reward-points'); ?>:
		<a target="_blank" href="http://wordpress.org/plugins/wupsales-reward-points/changelog/"><?php echo esc_html(WSBP_VERSION); ?></a>
	</div>
	<div class="wsbpAdminFooterCell">|</div>
	<?php if (!FrameWsbp::_()->getModule(implode('', array('l', 'ic', 'e', 'ns', 'e')))) { ?>
	<div class="wsbpAdminFooterCell">
		<?php esc_html_e('Go', 'wupsales-reward-points'); ?>&nbsp;<a target="_blank" href="<?php echo esc_url($this->getModule()->getMainLink()); ?>"><?php esc_html_e('PRO', 'wupsales-reward-points'); ?></a>
	</div>
	<div class="wsbpAdminFooterCell">|</div>
	<?php } ?>
	<div class="wsbpAdminFooterCell">
		<a target="_blank" href="https://wordpress.org/support/plugin/wupsales-reward-points"><?php esc_html_e('Support', 'wupsales-reward-points'); ?></a>
	</div>
	<div class="wsbpAdminFooterCell">|</div>
	<div class="wsbpAdminFooterCell">
		Add your <a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/wupsales-reward-points?filter=5#postform">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on wordpress.org.
	</div>
</div>
