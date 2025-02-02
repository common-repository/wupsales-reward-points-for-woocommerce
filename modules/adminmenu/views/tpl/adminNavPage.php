<style type="text/css">
.wupsales-main {
	display: none;
}
.wupsales-plugin-loader {
	width: 100%;
	height: 100px;
	text-align: center;
}
.wupsales-plugin-loader div {
	font-size: 30px;
	position: relation;
	margin-top: 40px;
}

</style>
<div class="wupsales-wrap">
	<div class="wupsales-plugin wupsales-main">
		<section class="wupsales-content">
			<nav class="wupsales-navigation wupsales-sticky <?php DispatcherWsbp::doAction('adminMainNavClassAdd'); ?>">
				<ul>
					<?php foreach ($this->tabs as $tabKey => $t) { ?>
						<?php 
						if (isset($t['hidden']) && $t['hidden']) {
							continue;
						}
						?>
						<li class="wupsales-tab-<?php echo esc_attr($tabKey); ?> <?php echo ( ( $this->activeTab == $tabKey || in_array($tabKey, $this->activeParentTabs) ) ? 'active' : '' ); ?>">
							<a href="<?php echo esc_url($t['url']); ?>" title="<?php echo esc_attr($t['label']); ?>">
								<?php if (isset($t['fa_icon'])) { ?>
									<i class="fa <?php echo esc_attr($t['fa_icon']); ?>"></i>
								<?php } elseif (isset($t['wp_icon'])) { ?>
									<i class="dashicons-before <?php echo esc_attr($t['wp_icon']); ?>"></i>
								<?php } elseif (isset($t['icon'])) { ?>
									<i class="<?php echo esc_attr($t['icon']); ?>"></i>
								<?php } ?>
								<span class="sup-tab-label"><?php echo esc_html($t['label']); ?></span>
							</a>
						</li>
					<?php } ?>
				</ul>
			</nav>
			<div class="wupsales-container wupsales-<?php echo esc_attr($this->activeTab); ?>">
				<?php HtmlWsbp::echoEscapedHtml($this->content); ?>
				<div class="clear"></div>
			</div>
		</section>
		<div id="wsbpAddDialog" class="wupsales-plugin wupsales-hidden" title="<?php echo esc_attr__('Enter product filter name', 'wupsales-reward-points'); ?>">
			<div>
				<form id="tableForm">
					<input id="addDialog_title" class="wupsales-text wupsales-width-full" type="text"/>
					<input type="hidden" id="addDialog_duplicateid" class="wupsales-text wupsales-width-full"/>
				</form>
				<div id="formError" class="wupsales-hidden">
					<p></p>
				</div>
			</div>
		</div>
	</div>
	<div class="wupsales-plugin-loader">
		<div>Loading...<i class="fa fa-spinner fa-spin"></i></div>
	</div>
</div>

