<?php 
$options = UtilsWsbp::getArrayValue($this->options, 'main', array(), 2);
$bLabel = HtmlWsbp::blockClasses('label');
$bValues = HtmlWsbp::blockClasses('values');
$bFull = HtmlWsbp::blockClasses('full');
$proClass = $this->is_pro ? '' : ' wupsales-show-pro';
$mainDefault = $this->getModule()->getDefaultMainSettings();
$isActive = $this->is_active;
?>
<div class="row row-options-block wupsales-nosave wsbp-active-program<?php echo $isActive ? '' : ' wupsales-hidden'; ?>" data-active="1">
	<div class="col-12 wsbp-group-label wsbp-group-first">
		<?php esc_html_e('Reward program is active', 'wupsales-reward-points'); ?>
		<div>
			<button class="button button-alert button-mini" id="wsbpStopBonusProgram"><i class="fa fa-stop" aria-hidden="true"></i> <?php esc_html_e('Stop', 'wupsales-reward-points'); ?></button>
		</div>
	</div>
</div>
<div class="row row-options-block wupsales-nosave wsbp-active-program<?php echo $isActive ? ' wupsales-hidden' : ''; ?>" data-active="0">
	<div class="col-12 wsbp-group-label wsbp-group-first">
		<?php esc_html_e('Reward program is not active', 'wupsales-reward-points'); ?>
		<div>
			<button class="button button-secondary button-mini" id="wsbpRunBonusProgram"><i class="fa fa-play" aria-hidden="true"></i> <?php esc_html_e('Run', 'wupsales-reward-points'); ?></button>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Place the widget of the Balance (Personal Account of the member of the Reward system) by adding this shortcode anywhere on your site or via standard WordPress widgets (Appearance->Widget)', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Widget shortcode', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php
				$value = '[' . WSBP_SHORTCODE . ']';
				HtmlWsbp::text('', array(
					'value' => $value,
					'attrs' => 'readonly class="wupsales-shortcode wupsales-width-full"',
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('The rules for participation in the Reward system are generated automatically based on the set settings, but you can also edit them or, in which case, restore the default value', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Rules page', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<button class="button button-alert button-mini" id="wsbpResetRulesPage"><i class="fa fa-refresh" aria-hidden="true"></i> <?php esc_html_e('Reset to default', 'wupsales-reward-points'); ?></button>
			<div class="options-label">
				<a href="<?php echo esc_url($this->rules_view); ?>" target="_blank"><?php esc_html_e('View', 'wupsales-reward-points'); ?></a>
			</div>
			<div class="options-label">
				<a href="<?php echo esc_url($this->rules_edit); ?>" target="_blank"><?php esc_html_e('Edit', 'wupsales-reward-points'); ?></a>
			</div>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bFull); ?> wsbp-group-label">
		<?php esc_html_e('General', 'wupsales-reward-points'); ?>
	</div>
</div>
<?php
$enabled = UtilsWsbp::getArrayValue($options, 'age_limit', false);
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('If the option is enabled, a field will be added to all program participants in their personal balance account, in the Settings section, asking them to enter their age. This will give you the option to limit company participation by age and will also help you use the Birthday trigger and Age condition for auto-action company planning in the future', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Set an age limit', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::checkboxToggle('main[age_limit]', array(
					'checked' => UtilsWsbp::getArrayValue($options, 'age_limit', false )
				));
				?>
		</div>
		<div class="options-value<?php echo $enabled ? '' : ' wupsales-hidden'; ?>" data-parent="main[age_limit]">
			<div class="options-label"><?php esc_html_e('Min age', 'wupsales-reward-points'); ?></div>
			<?php
				HtmlWsbp::number('main[min_age_user]', array(
					'value' => UtilsWsbp::getArrayValue($options, 'min_age_user', $mainDefault['min_age_user'], 1),
					'attrs' => 'min="0" class="wupsales-width80"'
				));
				?>
		</div>
	</div>
</div>

<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('This name will be used everywhere on the front of your store where the points name is involved', 'wupsales-reward-points'); ?>">
		<?php 
			esc_html_e('Name one point', 'wupsales-reward-points'); 
			if (!$this->is_pro) {
				HtmlWsbp::proOptionLink(); 
			}
		?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::text('main[name_one_point]', array(
					'value' => UtilsWsbp::getArrayValue($options, 'name_one_point', $mainDefault['name_one_point']),
					'attrs' => $this->is_pro ? '' : ' disabled'
				));
				?>
		</div>
		<div class="options-value">
			<div class="options-label"><?php esc_html_e('Plural name', 'wupsales-reward-points'); ?></div>
			<?php 
				HtmlWsbp::text('main[name_plural]', array(
					'value' => UtilsWsbp::getArrayValue($options, 'name_plural', $mainDefault['name_plural']),
					'attrs' => $this->is_pro ? '' : ' disabled'
				));
				?>
		</div>
		<div class="options-value">
			<div class="options-label"><?php esc_html_e('Abbreviated', 'wupsales-reward-points'); ?></div>
			<?php 
				HtmlWsbp::text('main[name_abbreviated]', array(
					'value' => UtilsWsbp::getArrayValue($options, 'name_abbreviated', $mainDefault['name_abbreviated']),
					'attrs' => 'class="wupsales-width80"' . ( $this->is_pro ? '' : ' disabled' )
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Defines the preferred way to enter points for assigning a reward: the exact amount, or a percentage of the cost of the product', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Percent reward points', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::checkboxToggle('main[is_percent_point]', array(
					'checked' => UtilsWsbp::getArrayValue($options, 'is_percent_point', false )
				));
				?>
		</div>
	</div>
</div>
<?php
$enabled = UtilsWsbp::getArrayValue($options, 'round_percent_point', false);
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Round up percentage points', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Round up percentage points', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::checkboxToggle('main[round_percent_point]', array(
					'checked' => $enabled
				));
				?>
		</div>
		<div class="options-value<?php echo $enabled ? '' : ' wupsales-hidden'; ?>" data-parent="main[round_percent_point]">
			<div class="options-label"><?php esc_html_e('decimals', 'wupsales-reward-points'); ?></div>
			<?php 
				HtmlWsbp::number('main[round_percent_decimals]', array(
					'value' => UtilsWsbp::getArrayValue($options, 'round_percent_decimals', $mainDefault['round_percent_decimals'], 1),
					'attrs' => 'min="0" max="2" class="wupsales-width80"'
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('All scheduled emails are sent once per hour. Determine how many emails to send at one time', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Еmails to send per session', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::number('main[max_emails]', array(
					'value' => UtilsWsbp::getArrayValue($options, 'max_emails', $mainDefault['max_emails'], 1),
					'attrs' => 'min="0" class="wupsales-width80"'
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Save debug messages to the WooCommerce SystemStatus Log', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Logging', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::checkboxToggle('main[logging]', array(
					'checked' => UtilsWsbp::getArrayValue($options, 'logging', false )
				));
				?>
		</div>
	</div>
</div>
<?php 
if ($this->is_pro) {
	DispatcherWsbp::doAction('optionsIncludeTpl', 'optionsTabMainGeneral', array('options' => $this->options, 'mainDefault' => $mainDefault));
} else { 
	?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('If a product has a certain number of points, and a group that will include the same product has a different value, then you can determine which value to use for such cases', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Priority groups', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<?php HtmlWsbp::proOptionLink(); ?>
	</div>
</div>
<?php 
} 
$enabled = UtilsWsbp::getArrayValue($options, 'e_points_list', false);
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Use fixed values for points by manual adding. Specify a list of fixed values separated by commas.', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Fixed values for points', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::checkboxToggle('main[e_points_list]', array(
					'checked' => UtilsWsbp::getArrayValue($options, 'e_points_list', false)
				));
				?>
		</div>
		<div class="options-value<?php echo $enabled ? '' : ' wupsales-hidden'; ?>" data-parent="main[e_points_list]">
			<?php 
				HtmlWsbp::text('main[points_list]', array(
					'value' => UtilsWsbp::getArrayValue($options, 'points_list', ''),
					'placeholder' => '10,50,100',
					));
				?>
		</div>
	</div>
</div>
<?php 
$enabled = UtilsWsbp::getArrayValue($options, 'e_reason_list', false);
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Use preset reason values ​​by adding points.', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Use preset reasons', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::checkboxToggle('main[e_reason_list]', array(
					'checked' => UtilsWsbp::getArrayValue($options, 'e_reason_list', false)
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block row-options-block-sub<?php echo $enabled ? '' : ' wupsales-hidden'; ?>" data-parent="main[e_reason_list]">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php echo esc_attr('Specify a list of reasons for adding points.', 'wupsales-reward-points') . '<br>' . esc_attr('Enter each reason on a new line. You can add a colon to indicate that additional description is required.', 'wupsales-reward-points') . '<br><br>' . esc_attr('See the field placeholder for an example.', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Preset reasons list', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				$roles = FrameWsbp::_()->getModule('options')->getAvailableUserRolesSelect();
				HtmlWsbp::textarea('main[reason_list]', array(
					'value' => UtilsWsbp::getArrayValue($options, 'reason_list', ''),
					'placeholder' => 'buy product from shop' . PHP_EOL . 'buy merchandise from shop' . PHP_EOL . 'extra discount points:+' ,
					));
				?>
		</div>
	</div>
</div>
<?php 
$enabled = UtilsWsbp::getArrayValue($options, 'e_user_widget', false);
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Place the widget of Users (Form for viewing and editing user bonuses) by adding this shortcode anywhere on your site', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Use widget of Users', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::checkboxToggle('main[e_user_widget]', array(
					'checked' => UtilsWsbp::getArrayValue($options, 'e_user_widget', false)
				));
				?>
		</div>
		<div class="options-value<?php echo $enabled ? '' : ' wupsales-hidden'; ?>" data-parent="main[e_user_widget]">
			<?php
				$value = '[' . WSBP_SHORTCODE_USERS . ']';
				HtmlWsbp::text('', array(
					'value' => $value,
					'attrs' => 'readonly class="wupsales-shortcode wupsales-width-full"',
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block row-options-block-sub<?php echo $enabled ? '' : ' wupsales-hidden'; ?>" data-parent="main[e_user_widget]">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Select the roles that will have access to the widget.', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Access roles', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				$roles = FrameWsbp::_()->getModule('options')->getAvailableUserRolesSelect();
				HtmlWsbp::selectlist('main[widget_access_roles]', array(
					'options' => $roles,
					'value' => UtilsWsbp::getArrayValue($options, 'widget_access_roles', $mainDefault['widget_access_roles']),
					'attrs' => 'data-placeholder="' . __('Select roles', 'publish-your-table') . '"',
					));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block row-options-block-sub<?php echo $enabled ? '' : ' wupsales-hidden'; ?>" data-parent="main[e_user_widget]">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Specify the user role, which can be viewed and edited via the widget. If necessary, you can allow not only viewing and editing user balances, but also adding new users through the widget of Users.', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('User role', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::selectbox('main[widget_new_user_role]', array(
					'options' => $roles,
					'value' => UtilsWsbp::getArrayValue($options, 'widget_new_user_role', $mainDefault['widget_new_user_role']),
					'attrs' => '',
					));
				?>
		</div>
		<div class="options-value">
			<div class="options-label"><?php esc_html_e('enable creating users', 'wupsales-reward-points'); ?></div>
			<?php
				HtmlWsbp::checkboxToggle('main[e_widget_new_user]', array(
					'checked' => UtilsWsbp::getArrayValue($options, 'e_widget_new_user', false)
				));
				?>
		</div>
		<div class="options-value">
			<div class="options-label"><?php esc_html_e('enable editing users', 'wupsales-reward-points'); ?></div>
			<?php
				HtmlWsbp::checkboxToggle('main[e_widget_edit_user]', array(
					'checked' => UtilsWsbp::getArrayValue($options, 'e_widget_edit_user', false)
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block row-options-block-sub<?php echo $enabled ? '' : ' wupsales-hidden'; ?>" data-parent="main[e_user_widget]">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php echo esc_attr('Specify a list of billing meta-fields for editing and searching for users.', 'wupsales-reward-points') . '<br>' . esc_attr('Enter each meta-field on a new line. Separated by a colon, you can add a label and a sign that the search field is being used.', 'wupsales-reward-points') . '<br><br>' . esc_attr('See the field placeholder for an example.', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('User billing fields', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::textarea('main[widget_user_fields]', array(
					'value' => UtilsWsbp::getArrayValue($options, 'widget_user_fields', $mainDefault['widget_user_fields']),
					'placeholder' => 'billing_phone:Phone number' . PHP_EOL . 'billing_street_name:Street:+' . PHP_EOL . 'billing_house_number:House number' ,
					));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bFull); ?> wsbp-group-label">
		<?php esc_html_e('Expiry date', 'wupsales-reward-points'); ?>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Set the expiration date for the points that customers receive for purchases. For already received points, the setting will not be applied', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Reward expiry date', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::number('main[expiry_date]', array(
					'value' => UtilsWsbp::getArrayValue($options, 'expiry_date', $mainDefault['expiry_date'], 1),
					'attrs' => 'min="1" class="wupsales-width80"'
				));
				?>
			<div class="options-label"><?php esc_html_e('days per purchase', 'wupsales-reward-points'); ?></div>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('If “Update when adding new ones” is selected, then each new purchase made by the user will reset the expiration timer to the value specified in the "Reward expiry date" option. If "Each point has its own expiration date" is selected, each point accrual will have its own expiration date, regardless of purchases made', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Logic expiry date', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::selectBox('main[logic_expiry]', array(
					'options' => array(
						0 => __('Update when adding new ones', 'wupsales-reward-points'),
						1 => __('Each point has its own expiration date', 'wupsales-reward-points')
						),
					'value' => UtilsWsbp::getArrayValue($options, 'logic_expiry', $mainDefault['logic_expiry'], 1),
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('This option controls the date format in the entire Reward system interface on the front.  For example, for a counter that is located in the users balance widget and shows when the points expire, or to enter the users age, etc', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Date format', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::selectBox('main[date_format]', array(
					'options' => array(
						'Y-m-d' => '2022-05-22',
						'd/m/Y' => '22/05/2022',
						'd.m.Y' => '22.05.2022',
						),
					'value' => UtilsWsbp::getArrayValue($options, 'date_format', $mainDefault['date_format'], 1),
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bFull); ?> wsbp-group-label">
		<?php esc_html_e('Pay Settings', 'wupsales-reward-points'); ?>
	</div>
</div>
<?php
$enabled = UtilsWsbp::getArrayValue($options, 'e_max_percent_cart', false);
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Set the maximum percentage of the cart amount that can be paid with Reward points', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Max to pay by points', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::checkboxToggle('main[e_max_percent_cart]', array(
					'checked' => $enabled
				));
				?>
		</div>
		<div class="options-value<?php echo $enabled ? '' : ' wupsales-hidden'; ?>" data-parent="main[e_max_percent_cart]">
			<?php 
				HtmlWsbp::number('main[max_percent_cart]', array(
					'value' => UtilsWsbp::getArrayValue($options, 'max_percent_cart', $mainDefault['max_percent_cart'], 1),
					'attrs' => 'min="0" max="100" class="wupsales-width80"'
				));
				?>
			<div class="options-label">%</div>
		</div>
	</div>
</div>
<?php 
if ($this->is_pro) {
	DispatcherWsbp::doAction('optionsIncludeTpl', 'optionsTabMainPay', array('options' => $this->options, 'mainDefault' => $mainDefault));
} else { 
	?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Set the maximum amount of points that can be used for one cart', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Max value BP for 1 cart', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<?php HtmlWsbp::proOptionLink(); ?>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('Set the minimum amount of the cart to be able to use the points', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Min amount cart', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<?php HtmlWsbp::proOptionLink(); ?>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('If this option is enabled, discounted items will be excluded from the cart amount calculation', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Exclude sales items', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<?php HtmlWsbp::proOptionLink(); ?>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('By default, the discount form is displayed only in the shopping cart. Here you can enable additional display of the discount form on the checkout page.', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('Discounts form at checkout', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<?php HtmlWsbp::proOptionLink(); ?>
	</div>
</div>
<?php 
$currency = get_option('woocommerce_currency');
$symbols = get_woocommerce_currency_symbols();
$currencySymbol = ( isset($symbols[$currency]) ? $symbols[$currency] : $currency );
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php echo esc_attr(__('By default, for every 1 bonus point a customer can get', 'wupsales-reward-points') . ' 1' . $currencySymbol . ' ' . __('off. This option allows you to set other rules.', 'wupsales-reward-points')); ?>">
		<?php esc_html_e('Use custom rule', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<?php HtmlWsbp::proOptionLink(); ?>
	</div>
</div>
<?php } ?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bFull); ?> wsbp-group-label">
		<?php esc_html_e('Refund Settings', 'wupsales-reward-points'); ?>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> wupsales-tooltip" title="<?php esc_attr_e('If at the time of returning the goods, the client does not have enough Bonuses on the Bonus account to write off the amount previously accrued for the purchase and spent by the client, then: If "Write off the rest" is selected, the maximum possible number of bonuses will be written off; If "Do not take bonuses (and not return bonus discount)" is selected, then the bonuses accrued for the returned goods will not be deducted under any circumstances when returning the goods', 'wupsales-reward-points'); ?>">
		<?php esc_html_e('When rewards are spent', 'wupsales-reward-points'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlWsbp::selectBox('main[refund_type]', array(
					'options' => array(
						0 => __('Write off the rest', 'wupsales-reward-points'),
						//1 => __('Withhold from refund amount', 'wupsales-reward-points'),
						2 => __('Do not take rewards (and not return reward discount)', 'wupsales-reward-points')
						),
					'value' => UtilsWsbp::getArrayValue($options, 'refund_type', 0, 1),
				));
				?>
		</div>
	</div>
</div>



