<?php
HtmlWsbp::hidden('', array('value' => UtilsWsbp::jsonEncode($this->settings), 'attrs' => 'id="wsbpLangSettingsJson" class="wupsales-nosave"'));
$countries = WC()->countries->get_countries();
if (empty($countries)) {
	$countries = array();
}
$reasonList = $this->reasonList;
$pointsList = $this->pointsList;
?>
<div id="wsbpUsersWidget" data-new-user="<?php echo ( $this->allowNewUser ? 1 : 0 ); ?>">
	<div id="wsbpUsersWidgetFilter">
		<form>
		<div class="wsbp-form-row">
			<div class="wsbp-form-label">
				<?php esc_html_e('First name', 'wupsales-reward-points'); ?>
			</div>
			<div class="wsbp-form-value">
				<?php HtmlWsbp::text('first_name'); ?>
			</div>
		</div>
		<div class="wsbp-form-row">
			<div class="wsbp-form-label">
				<?php esc_html_e('Last name', 'wupsales-reward-points'); ?>
			</div>
			<div class="wsbp-form-value">
				<?php HtmlWsbp::text('last_name'); ?>
			</div>
		</div>
		<div class="wsbp-form-row">
			<div class="wsbp-form-label">
				<?php esc_html_e('Email', 'wupsales-reward-points'); ?>
			</div>
			<div class="wsbp-form-value">
				<?php HtmlWsbp::text('user_email'); ?>
			</div>
		</div>
<?php foreach ($this->billingFields as $field) { ?>
	<?php if (!empty($field[2]) && '+' == $field[2]) { ?>
		<div class="wsbp-form-row">
			<div class="wsbp-form-label">
				<?php echo esc_html(empty($field[1]) ? $field[0] : $field[1]); ?>
			</div>
			<div class="wsbp-form-value">
				<?php HtmlWsbp::text($field[0]); ?>
			</div>
		</div>
	<?php } ?>
<?php } ?>
		<div class="wsbp-form-row">
			<div class="wsbp-form-label">

			</div>
			<div class="wsbp-form-value">
				<?php HtmlWsbp::button(array('value' => __('Search', 'wupsales-reward-points'), 'attrs' => 'id="wsbpWidgetUserBtnFilter"')); ?>
			</div>
		</div>
		</form>
	</div>
	<table id="wsbpWidgetUsersList">
		<thead>
			<tr>
				<th><?php esc_html_e('Name', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Email', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Points', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Address', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Actions', 'wupsales-reward-points'); ?></th>
			</tr>
		</thead>
	</table>
	<div class="wupsales-clear"></div>
	<div class="wsbp-hidden">
		<div id="wsbpDialogAddPoint">
			<div class="wsbp-label-name">
			</div>
			<div class="wsbp-input-row">
				<div class="wsbp-input-group">
					<label><?php esc_html_e('Operation', 'wupsales-reward-points'); ?></label>	
					<?php 
						HtmlWsbp::selectbox('operation', array(
							'options' => array('add' => __('add', 'wupsales-reward-points'), 'del' => __('delete', 'wupsales-reward-points')),
							'attrs' => 'data-default="add" class="wupsales-width100"',
							));
						?>
				</div>
				<?php if (!empty($pointsList)) { ?>
					<div class="wsbp-input-group" data-select="operation" data-select-value="add">
						<label><?php esc_html_e('Count', 'wupsales-reward-points'); ?></label>
						<?php HtmlWsbp::selectbox('points_add', array('options' => $pointsList, 'attrs' => 'class="wupsales-width100" data-default="' . array_keys($pointsList)[0] . '"')); ?>
					</div>
					<div class="wsbp-input-group" data-select="operation" data-select-value="del">
						<label><?php esc_html_e('Count', 'wupsales-reward-points'); ?></label>
						<?php HtmlWsbp::number('points', array('attrs' => 'min="0" class="wupsales-width100" data-default=""')); ?>
					</div>
				<?php } else { ?>
					<div class="wsbp-input-group">
						<label><?php esc_html_e('Count', 'wupsales-reward-points'); ?></label>
						<?php HtmlWsbp::number('points', array('attrs' => 'min="0" class="wupsales-width100" data-default=""')); ?>
					</div>
				<?php } ?>
			</div>
			<?php if (!empty($reasonList)) { ?>
				<div class="wsbp-input-group">
					<label><?php esc_html_e('Reason', 'wupsales-reward-points'); ?></label>
					<?php 
						$list = array();
						$needAdd = '';
						foreach ($reasonList as $i => $reason) {
							$list[$i] = $reason[0];
							if (!empty($reason[1]) && '+' == $reason[1]) {
								$needAdd .= ' ' . $i;
							}
						}
						HtmlWsbp::selectbox('reason_list', array(
							'options' => $list,
							'attrs' => 'data-default="0"',
							));
						?>
				</div>
				<?php if (!empty($needAdd)) { ?>
					<div class="wsbp-input-group" data-select="reason_list" data-select-value="<?php echo esc_attr($needAdd); ?>">
						<label><?php esc_html_e('Aditional description', 'wupsales-reward-points'); ?></label>
						<?php HtmlWsbp::text('reason', array('attrs' => ' data-default=""')); ?>
					</div>
				<?php } ?>
			<?php } else { ?>
				<div class="wsbp-input-group">
					<label><?php esc_html_e('Reason (max 50 symbols)', 'wupsales-reward-points'); ?></label>
					<?php HtmlWsbp::text('reason', array('attrs' => ' data-default=""')); ?>
				</div>
			<?php } ?>
			<div class="wsbp-input-row wsbp-button-row">
				<div class="wsbp-input-group">
					<button class="button wsbp-save-action"><i class="fa fa-fw fa-save"></i> <?php esc_html_e('Save', 'wupsales-reward-points'); ?></button>
				</div>
				<div class="wsbp-input-group">
					<?php HtmlWsbp::button(array('value' => __('Cancel', 'wupsales-reward-points'), 'attrs' => ' class="button wsbp-cancel-action"')); ?>
				</div>
			</div>
		</div>
		<div id="wsbpDialogAddUser">
			<div class="wsbp-label-name wspb-user-name" data-label-new="<?php esc_html_e('Add new user', 'wupsales-reward-points'); ?>">
				<?php esc_html_e('Add new user', 'wupsales-reward-points'); ?>
			</div>
			<div class="wsbp-input-row">
				<div class="wsbp-input-group">
					<label><?php esc_html_e('First Name', 'wupsales-reward-points'); ?></label>
					<?php HtmlWsbp::input('first_name', array()); ?>
				</div>
				<div class="wsbp-input-group">
					<label><?php esc_html_e('Last Name', 'wupsales-reward-points'); ?></label>
					<?php HtmlWsbp::input('last_name', array()); ?>
				</div>
			</div>
			<div class="wsbp-input-group">
				<label><?php esc_html_e('Email Addess', 'wupsales-reward-points'); ?></label>
				<?php HtmlWsbp::input('user_email', array()); ?>
			</div>
			<div class="wsbp-label-name">
				<?php esc_html_e('Billing fields', 'wupsales-reward-points'); ?>
			</div>
			<?php 
				$num = 0;
				foreach ($this->billingFields as $field) {
					$num++;
					if (1 == $num) {
				?>
				<div class="wsbp-input-row">
				<?php } ?>
					<div class="wsbp-input-group">
						<label><?php echo esc_html(empty($field[1]) ? $field[0] : $field[1]); ?></label>
						<?php 
							if ('billing_country' == $field[0] && !empty($field[3]) && 'select' == $field[3]) {
								$def = DispatcherWsbp::applyFilters('getDefaultUserCountry', '');
								HtmlWsbp::selectbox('billing_country', array(
									'options' => array_merge(array('' => __('Select country', 'wupsales-reward-points')), $countries), 
									'value' => $def,
									'attrs' => 'data-default="' . $def . '"',
								));
							} else {
								HtmlWsbp::input($field[0], array()); 
							}
						?>
					</div>
				<?php if (2 == $num) { ?>
				</div>
				<?php 
					}
					if ($num > 1) {
						$num = 0;
					}
				}
				if (1 == $num) {
				?>
				<div class="wsbp-input-group"></div>
				</div>
				<?php
				}
			?>
			<div class="wsbp-input-row wsbp-button-row">
				<div class="wsbp-input-group">
					<button class="button wsbp-save-action"><i class="fa fa-fw fa-save"></i> <?php esc_html_e('Save', 'wupsales-reward-points'); ?></button>
				</div>
				<div class="wsbp-input-group">
					<?php HtmlWsbp::button(array('value' => __('Cancel', 'wupsales-reward-points'), 'attrs' => ' class="button wsbp-cancel-action"')); ?>
				</div>
			</div>
		</div>
	</div>
</div>