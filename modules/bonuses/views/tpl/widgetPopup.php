<?php 
$options = $this->options;
$user = $this->user;
$points = $user['points'];
$module = $this->getModule();
$namePlural = $module->getNamePlural();
$bonuses = $this->bonuses;

?>
<div class="wsbp-menu-tabs">
	<ul class="wsbp-widget-tabs">
		<?php foreach ($this->tabs as $key => $label) { ?>
			<li class="wsbp-widget-tab<?php echo $key == $this->current_tab ? ' current' : ''; ?>" data-tab=".block-tab-<?php echo esc_attr($key); ?>">
				<a href="#">
					<?php echo esc_html($label); ?>
				</a>
			</li>
		<?php } ?>
	</ul>
</div>
<div class="wsbp-widget-tab-content">
	<div class="wsbp-block-tab block-tab-balance">
		<div class="wsbp-widget-wrapper">
			<div class="wsbp-widget-icon"></div>
			<div class="wsbp-widget-text">
				<div class="wsbp-text-inner"><?php echo esc_html($module->getPointsWithName($module->getCurrencyPrice($points)) . ' ' . __('available for payments', 'wupsales-reward-points')); ?></div>
			</div>
			<a href="#" class="wsbp-toggle"><i class="fa fa-chevron-down"></i></a>
		</div>
		<div class="wsbp-balance-detail wsbp-hidden">
		<?php foreach ($bonuses as $bonus) { ?>
			<div class="wsbp-detail-block">
				<div class="wsbp-balance-row">
					<div class="wsbp-balance-point"><?php echo esc_html($module->getCurrencyPrice($bonus['rest'])); ?></div>
					<div class="wsbp-balance-reason">
					<?php 
						echo esc_html($namePlural) . ' ' . 
							esc_html( 2 == $bonus['tr_type'] ? __('for purchases', 'wupsales-reward-points') : __('for the reason', 'wupsales-reward-points') . ' "' . __($bonus['reason'], 'wupsales-reward-points') . '"' );
					?>
					</div>
				</div>
				<div class="wsbp-balance-info"><?php echo esc_html__('Valid until', 'wupsales-reward-points') . ' ' . ( is_null($bonus['exp_date']) ? '-' : esc_html(UtilsWsbp::getFormatedDateTime($bonus['exp_date'])) ); ?></div>
			</div>
		<?php } ?>
		</div>
		<?php
		if ($this->is_pro) {
			DispatcherWsbp::doAction('bonusesIncludeTpl', 'widgetPopupAccount', array('user' => $user, 'options' => $options));
		}
		?>
	</div>
	<div class="wsbp-block-tab block-tab-trans">
		<div class="wsbp-trans-filter">
			<?php HtmlWsbp::text('from', array('attrs' => 'class="wsbp-field-date wupsales-width100" data-default=""', 'value' => $this->transFrom)); ?>
			<div class="wsbp-mid-label"> - </div>
			<?php HtmlWsbp::text('to', array('attrs' => 'class="wsbp-field-date wupsales-width100" data-default=""', 'value' => $this->transTo)); ?>
			<button class="button button-small wsbp-trans-show"><i class="fa fa-refresh" aria-hidden="true"></i> <?php esc_html_e('Show', 'wupsales-reward-points'); ?></button>
			<input type="hidden" name="sort" value="created">
			<input type="hidden" name="dir" value="desc">
			<input type="hidden" name="front" value="<?php echo $this->is_front ? 1 : 0; ?>">
			<input type="hidden" name="userId" value="<?php echo esc_attr($user['id']); ?>">
		</div>
		<table class="wsbp-widget-trans">
			<thead>
				<tr>
					<th data-field="created"><span class="ui-icon wsbp-sort-desc"></span><?php esc_html_e('Date', 'wupsales-reward-points'); ?></th>
					<th data-field="status"><?php esc_html_e('Status', 'wupsales-reward-points'); ?></th>
					<th data-field="tr_type"><?php esc_html_e('Operation', 'wupsales-reward-points'); ?></th>
					<th data-field="points"><?php echo esc_html($namePlural); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php include_once 'widgetPopupTransactions.php'; ?>
			</tbody>
		</table>
	</div>
	<?php if ($this->is_front) { ?>
		<div class="wsbp-block-tab block-tab-settings">
			<div class="wsbp-settings-row">
				<div class="wsbp-widget-label"><?php esc_html_e('Date of birth', 'wupsales-reward-points'); ?></div>
				<?php HtmlWsbp::text('birthday', array('attrs' => 'class="wsbp-field-date wupsales-width100" data-default=""', 'value' => empty( $user['birthday']) ? '' : UtilsWsbp::getFormatedDateTime($user['birthday'], FrameWsbp::_()->getModule('bonuses')->getDateFormat() ))); ?>
				<button class="button button-small wsbp-birthday-save">
					<i class="fa fa-floppy-o" aria-hidden="true"></i> <?php esc_html_e('Save', 'wupsales-reward-points'); ?>
				</button>
			</div>
			<div class="wsbp-widget-label wsbp-rules-link">
				<a href="<?php echo esc_url($this->rules_view); ?>" target="_blank"><?php echo esc_html__('Rules for the use of reward points', 'wupsales-reward-points'); ?></a>
			</div>
			<?php if (2 == $this->user['status']) { ?>
				<div class="wsbp-widget-label wsbp-user-status wsbp-user-blocked"><?php esc_html_e('You have been blocked by the administrator, possibly for violating the rules of the reward program.', 'wupsales-reward-points'); ?></div>
			<?php } else { ?>
				<div class="wsbp-widget-label wsbp-user-status wsbp-user-active<?php echo 1 == $this->user['status'] && $this->is_age_pass ? '' : ' wsbp-hidden'; ?>"><?php esc_html_e('Congratulations! You are reward program participant.', 'wupsales-reward-points'); ?></div>
				<div class="wsbp-widget-label wsbp-user-status wsbp-user-refused<?php echo 0 == $this->user['status'] ? '' : ' wsbp-hidden'; ?>"><?php esc_html_e('You refused to participate.', 'wupsales-reward-points'); ?></div>
				<div class="wsbp-widget-label wsbp-user-status wsbp-user-agelimit<?php echo ( 1 == $this->user['status'] && !$this->is_age_pass ? '' : ' wsbp-hidden' ); ?>">
					<?php 
					/* translators: %s: min_age_user */
					echo sprintf(esc_html__('Members of the reward program can only be persons over %s years old', 'wupsales-reward-points'), esc_html($this->getModule()->getMainOptions('min_age_user')));
					?>
				</div>
				
				<div class="wsbp-settings-row wsbp-widget-buttons">
					<button class="button button-small wsbp-confirm<?php echo 0 == $this->user['status'] ? '' : ' wsbp-hidden'; ?>">
						<i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e('Join reward program', 'wupsales-reward-points'); ?>
					</button>
					<button class="button button-small wsbp-refuse<?php echo 1 == $this->user['status'] ? '' : ' wsbp-hidden'; ?>">
						<i class="fa fa-times" aria-hidden="true"></i> <?php esc_html_e('Refuse to participate', 'wupsales-reward-points'); ?>
					</button>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
</div>
