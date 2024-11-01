<section class="wupsales-bar wupsales-titlebar">
	<ul class="wupsales-bar-controls">
		<li class="wupsales-title-icon">
			<i class="fa fa-users"></i>
		</li>
		<li class="wupsales-title-text">
			<?php echo esc_html__('User balance', 'wupsales-reward-points'); ?>
		</li>
	</ul>
	<div class="wupsales-clear"></div>
</section>
<section>
	<div class="row wupsales-menu-tabs">
		<div class="col-5">
			<ul class="wupsales-grbtn wsbp-main-tabs">
				<?php foreach ($this->main_tabs as $key => $data) { ?>
					<li>
						<a href="#block-tab-<?php echo esc_attr($key); ?>" data-model="<?php echo esc_attr($key); ?>" class="button <?php echo ( !$data['pro'] || $this->is_pro ? '' : 'wupsales-show-pro ' ) . ( empty($data['class']) ? '' : esc_attr($data['class']) ); ?>">
							<i class="fa fa-fw <?php echo esc_attr($data['icon']); ?>"></i><?php echo esc_html($data['label']); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
		<div class="wsbp-main-buttons col-7">
			<ul class="wupsales-control-buttons">
				<li>
					<button id="wsbpBtnRecalc" class="button button-primary">
						<i class="fa fa-refresh" aria-hidden="true"></i><span><?php esc_html_e('Recalc balance', 'wupsales-reward-points'); ?></span>
					</button>
				</li>
			</ul>
		</div>
	</div>
	<div class="wupsales-item wupsales-panel">
		<div class="wsbp-main-tab-content">
			<?php foreach ($this->main_tabs as $key => $data) { ?>
				<div class="block-tab options-values" id="block-tab-<?php echo esc_attr($key); ?>">
					<?php 
					if ($data['pro']) {
						if ($this->is_pro) {
							DispatcherWsbp::doAction('actionsIncludeTpl', 'avtionsTab' . strFirstUpWsbp($key), array('options' => $this->options));
						} else { 
							include 'actionsProFeature.php';
						}
					} else {
						include_once 'actionsTab' . strFirstUpWsbp($key) . '.php';
					}
					?>
				</div>
			<?php } ?>
		</div>
	</div>
	<div class="wupsales-clear"></div>
</section>
