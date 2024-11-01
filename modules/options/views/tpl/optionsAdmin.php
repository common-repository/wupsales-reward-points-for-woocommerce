<section class="wupsales-bar wupsales-titlebar">
	<ul class="wupsales-bar-controls">
		<li class="wupsales-title-icon">
			<i class="fa fa-gear"></i>
		</li>
		<li class="wupsales-title-text">
			<?php echo esc_html__('Plugin Settings', 'wupsales-reward-points'); ?>
		</li>
	</ul>
	<div class="wupsales-clear"></div>
</section>
<section>
	<div class="row wupsales-menu-tabs">
		<div class="col-9">
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
		<div class="wsbp-main-buttons col-3">
			<ul class="wupsales-control-buttons">
				<li>
					<button id="wsbpBtnSave" class="button button-primary">
						<i class="fa fa-floppy-o" aria-hidden="true"></i><span><?php esc_html_e('Save', 'wupsales-reward-points'); ?></span>
					</button>
				</li>
			</ul>
		</div>
	</div>
	<form id="wsbpSettingsForm">
		<div class="wupsales-item wupsales-panel">
			<div class="wsbp-main-tab-content">
				<?php foreach ($this->main_tabs as $key => $data) { ?>
					<div class="block-tab options-values" id="block-tab-<?php echo esc_attr($key); ?>">
						<?php 
						if ($data['pro']) {
							if ($this->is_pro) {
								DispatcherWsbp::doAction('optionsIncludeTpl', 'optionsTab' . strFirstUpWsbp($key), array('options' => $this->options));
							} else { 
								include 'optionsProFeature.php';
							}
						} else {
							include_once 'optionsTab' . strFirstUpWsbp($key) . '.php';
						}
						?>
					</div>
				<?php } ?>
			</div>
		</div>
		<div class="wupsales-clear"></div>
		<?php 
			HtmlWsbp::hidden('mod', array('value' => 'options'));
			HtmlWsbp::hidden('action', array('value' => 'saveOptions'));
			HtmlWsbp::hidden('', array('value' => UtilsWsbp::jsonEncode($this->lang), 'attrs' => 'id="wsbpLangSettingsJson" class="wupsales-nosave"'));
		?>
	</form>
</section>

