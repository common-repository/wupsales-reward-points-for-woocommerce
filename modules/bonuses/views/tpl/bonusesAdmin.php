<section class="wupsales-bar wupsales-titlebar">
	<ul class="wupsales-bar-controls">
		<li class="wupsales-title-icon">
			<i class="fa fa-plus"></i>
		</li>
		<li class="wupsales-title-text">
			<?php echo esc_html__('Set points', 'wupsales-reward-points'); ?>
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
				<li class="group-button wupsales-hidden">
					<button id="wsbpBtnSave" class="button button-primary<?php echo $this->is_pro ? '' : ' wupsales-hidden'; ?>">
						<i class="fa fa-floppy-o" aria-hidden="true"></i><span><?php esc_html_e('Save', 'wupsales-reward-points'); ?></span>
					</button>
				</li>
				<li>
					<button id="wsbpBtnRecalc" class="button button-primary">
						<i class="fa fa-refresh" aria-hidden="true"></i><span><?php esc_html_e('Recalc points', 'wupsales-reward-points'); ?></span>
					</button>
				</li>
			</ul>
		</div>
	</div>
	<form id="wsbpBonusesForm">
		<div class="wupsales-item wupsales-panel">
			<div class="wsbp-main-tab-content">
				<?php foreach ($this->main_tabs as $key => $data) { ?>
					<div class="block-tab options-values" id="block-tab-<?php echo esc_attr($key); ?>">
						<?php 
						if ($data['pro']) {
							if ($this->is_pro) {
								DispatcherWsbp::doAction('bonusesIncludeTpl', 'bonusesTab' . strFirstUpWsbp($key), array());
							} else { 
								include 'bonusesProFeature.php';
							}
						} else {
							include_once 'bonusesTab' . strFirstUpWsbp($key) . '.php';
						}
						?>
					</div>
				<?php } ?>
			</div>
		</div>
		<div class="wupsales-clear"></div>
		<?php 
			HtmlWsbp::hidden('mod', array('value' => 'bonuses'));
			HtmlWsbp::hidden('action', array('value' => 'saveProductBonuses'));
		?>
	</form>
</section>

