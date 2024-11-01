<?php 
$bLabel = 'col-3 options-label';
$bValues = 'col-8 options-values';

$conditionsTypes = $this->cond_types;
$conditionsLogic = array('and' => __('AND', 'wupsales-reward-points'), 'or' => __('OR', 'wupsales-reward-points'));

$modActions = FrameWsbp::_()->getModule('actions');
$attributes =  $modActions->getAttributesDisplay();
$attrValues = array();
$modelActions = $modActions->getModel();
$args = array(
	'parent' => 0,
	'hide_empty' => 0,
	'orderby' => 'name',
	'order' => 'asc',
);
foreach ($attributes as $slug => $t) {
	$attrValues[$slug] = $modelActions->getTaxonomyHierarchy($slug, $args);
}
$emptyProducts = empty($this->products);
$reasonList = $modelActions->getReasonList();
$pointsList = $modelActions->getPointsList();

HtmlWsbp::hidden('', array('value' => UtilsWsbp::jsonEncode($this->settings), 'attrs' => 'id="wsbpLangSettingsJson" class="wupsales-nosave"'));
$expDays = (int) FrameWsbp::_()->getModule('bonuses')->getMainOptions('expiry_date');
?>
<div id="wsbpUsersFilter" class="wsbp-list-filter">
	<div class="row row-options-block">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<?php esc_html_e('Filter logic', 'wupsales-reward-points'); ?>
		</div>
		<div class="col-9">
			<div class="options-value">
				<?php HtmlWsbp::selectbox('filter_logic', array('options' => $conditionsLogic, 'attrs' => 'id="wsbpUsersCondLogic"')); ?>
			</div>
			<div class="options-buttons">
				<button id="wsbpBtnFilter" class="button button-small">
					<i class="fa fa-filter" aria-hidden="true"></i><span><?php esc_html_e('Filter', 'wupsales-reward-points'); ?></span>
				</button>
			</div>
		</div>
	</div>
	<div class="row row-options-block wupsales-nosave">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<?php esc_html_e('Filter type', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::selectbox('', array('options' => $conditionsTypes, 'attrs' => 'id="wsbpUsersCondTypes"')); 
					HtmlWsbp::hidden('', array('value' => UtilsWsbp::jsonEncode($attrValues), 'attrs' => 'id="wsbpAttrValuesJson"'));
				?>
			</div>
			<div class="options-value">
				<button class="button button-small button-secondary" id="wsbpAddFilter" data-num="0"><?php esc_html_e('Add filter', 'wupsales-reward-points'); ?></button>
			</div>
		</div>
	</div>
	<div class="wsbp-filters-list">
	</div>
</div>
<div class="wupsales-table-list wupsales-nosave">
	<table id="wsbpUsersList">
		<thead>
			<tr>
				<th><input type="checkbox" class="wsbpCheckAll"></th>
				<th><?php esc_html_e('Id', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Name', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Role', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Email', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Points', 'wupsales-reward-points'); ?></th>
				<th><?php esc_html_e('Actions', 'wupsales-reward-points'); ?></th>
			</tr>
		</thead>
	</table>
</div>
<div class="wupsales-clear"></div>
<div class="wupsales-hidden">
	<div id="wsbpDialogRecalc" title="<?php esc_attr_e('Recacl users balance', 'wupsales-reward-points'); ?>">
		<div class="wsbp-info-desc">
			<?php esc_html_e('For the correct and fast operation of user filters and calculation of the user level, the plugin create the corresponding meta-parameters. These parameters are automatically updated by editing/creating orders. But if you\'ve edited the orders with third-party plugins or methods and/or noticed that the plugin doesn\'t work correctly, then click this button to force a refresh of the user\'s settings. If you have many users, the process may take some time.', 'wupsales-reward-points'); ?>
		</div>
		<div class="wupsales-center options-values">
			<div class="options-value">
				<?php HtmlWsbp::checkboxToggle('in_cron'); ?>
				<div class="options-label">run in background</div>
			</div>
		</div>
	</div>
	<div id="wsbpDialogAddPoint" title="<?php esc_attr_e('Add points', 'wupsales-reward-points'); ?>">
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
		<div class="wsbp-input-row">
			<div class="wsbp-input-group">
				<label><?php esc_html_e('Action date', 'wupsales-reward-points'); ?></label>
				<?php
				if ($this->is_pro) {
					HtmlWsbp::text('date', array('attrs' => 'class="wsbp-field-date wupsales-width100" data-default=""'));
				} else {
					echo '<label>'; 
					HtmlWsbp::proOptionLink(); 
					echo '</label>';
					HtmlWsbp::text('date', array('value' => '', 'attrs' => 'disabled readonly class="wupsales-width100" placeholder="' . __('now', 'wupsales-reward-points') . '"'));
				}
				?>
			</div>
			<div class="wsbp-input-group" data-select="operation" data-select-value="add">
				<label><?php esc_html_e('Expiry date', 'wupsales-reward-points'); ?></label>
				<?php HtmlWsbp::text('expiry', array('attrs' => 'class="wsbp-field-date wupsales-width100" data-default="' . UtilsWsbp::addDays($expDays) . '"')); ?>
			</div>
		</div>
		<div class="wsbp-input-group">
			<label><?php HtmlWsbp::checkboxToggle('e_email', array('value' => 1, 'attrs' => 'data-default=""')); ?></label>
			<label><?php esc_html_e('Send email', 'wupsales-reward-points'); ?></label>
		</div>
		<div class="wsbp-sub-group wupsales-hidden" id="wsbpEmailSettings" data-parent="e_email">
			<div class="wsbp-input-group">
				<label><?php esc_html_e('Subject', 'wupsales-reward-points'); ?></label>
				<?php HtmlWsbp::text('email[subject]', array('value' => '', 'attrs' => 'data-default="' . __('You have received reward points', 'wupsales-reward-points') . '"')); ?>
			</div>
			<div class="wsbp-input-group wsbp-static">
				<label><?php esc_html_e('Message', 'wupsales-reward-points'); ?></label>
				<?php HtmlWsbp::textarea('email[message]', array('attrs' => 'id="wsbpEmailMessage" class="wsbp-field-html" data-default=""')); ?>
			</div>
			<div class="wsbp-input-row">
				<div class="wsbp-input-group">
					<label><?php HtmlWsbp::checkboxToggle('email[bonus_block]', array('value' => 1, 'attrs' => 'data-default="0"')); ?></label>
					<label><?php esc_html_e('Add reward points block', 'wupsales-reward-points'); ?></label>
				</div>
				<div class="wsbp-input-group">
					<label><?php HtmlWsbp::checkboxToggle('email[shop_button]', array('value' => 1, 'attrs' => 'data-default="0"')); ?></label>
					<label><?php esc_html_e('Add shop button', 'wupsales-reward-points'); ?></label>
				</div>
			</div>
			<div class="wsbp-input-group">
				<button id="wsbpEmailPreview" class="button button-small button-dark"><?php esc_html_e('Preview email', 'wupsales-reward-points'); ?></button>
			</div>
		</div>
		<div class="wsbp-input-group">
			<label><?php HtmlWsbp::checkboxToggle('e_popup', array('value' => 1, 'attrs' => 'data-default=""')); ?></label>
			<label><?php esc_html_e('Show popup', 'wupsales-reward-points'); ?></label>
		</div>
		<div class="wsbp-sub-group wupsales-hidden" id="wsbpPopupSettings" data-parent="e_popup">
			<div class="wsbp-input-group">
				<label><?php esc_html_e('Title', 'wupsales-reward-points'); ?></label>
				<?php HtmlWsbp::text('popup[title]', array('value' => '', 'attrs' => 'data-default="' . __('You have received reward points', 'wupsales-reward-points') . '"')); ?>
			</div>
			<div class="wsbp-input-group wsbp-static">
				<label><?php esc_html_e('Message', 'wupsales-reward-points'); ?></label>
				<?php HtmlWsbp::textarea('popup[message]', array('attrs' => 'id="wsbpPopupMessage" class="wsbp-field-html" data-default=""')); ?>
			</div>
			<div class="wsbp-input-row">
				<div class="wsbp-input-group">
					<label><?php HtmlWsbp::checkboxToggle('popup[bonus_block]', array('value' => 1, 'attrs' => 'data-default="0"')); ?></label>
					<label><?php esc_html_e('Add reward points block', 'wupsales-reward-points'); ?></label>
				</div>
				<div class="wsbp-input-group">
					<label><?php HtmlWsbp::checkboxToggle('popup[shop_button]', array('value' => 1, 'attrs' => 'data-default="0"')); ?></label>
					<label><?php esc_html_e('Add shop button', 'wupsales-reward-points'); ?></label>
				</div>
			</div>
			<div class="wsbp-input-group">
				<button id="wsbpPopupPreview" class="button button-small button-dark"><?php esc_html_e('Preview popup', 'wupsales-reward-points'); ?></button>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-role">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a>
			<?php esc_html_e('User roles', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::hidden('filters[N][type]', array('value' => 'role'));
					HtmlWsbp::selectlist('filters[N][roles]', array(
						'options' => FrameWsbp::_()->getModule('options')->getAvailableUserRolesSelect(),
						'attrs' => 'data-placeholder="' . __('Select roles', 'publish-your-table') . '"',
						'class' => 'no-chosen'
						));
					?>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-registr">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a>
			<?php esc_html_e('Registration', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::hidden('filters[N][type]', array('value' => 'registr'));
					HtmlWsbp::text('filters[N][min_date]', array('attrs' => 'class="wsbp-field-date wupsales-width100"'));
				?>
				<div class="options-label">-</div>
				<?php HtmlWsbp::text('filters[N][max_date]', array('attrs' => 'class="wsbp-field-date wupsales-width100"')); ?>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-age">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a>
			<?php esc_html_e('Age', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::hidden('filters[N][type]', array('value' => 'age'));
					HtmlWsbp::number('filters[N][min_age]', array('attrs' => 'min="0" class="wupsales-width80"'));
				?>
				<div class="options-label">-</div>
				<?php HtmlWsbp::number('filters[N][max_age]', array('attrs' => 'min="0" class="wupsales-width80"')); ?>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-amount">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a>
			<?php esc_html_e('Total amount', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::hidden('filters[N][type]', array('value' => 'amount'));
					HtmlWsbp::number('filters[N][min_amount]', array('attrs' => 'min="0" class="wupsales-width80"'));
				?>
				<div class="options-label">-</div>
				<?php HtmlWsbp::number('filters[N][max_amount]', array('attrs' => 'min="0" class="wupsales-width80"')); ?>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-count">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a>
			<?php esc_html_e('Count of purchases', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::hidden('filters[N][type]', array('value' => 'count'));
					HtmlWsbp::number('filters[N][min_count]', array('attrs' => 'min="0" class="wupsales-width80"'));
				?>
				<div class="options-label">-</div>
				<?php HtmlWsbp::number('filters[N][max_count]', array('attrs' => 'min="0" class="wupsales-width80"')); ?>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-order">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a>
			<?php esc_html_e('Last order', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::hidden('filters[N][type]', array('value' => 'order'));
					HtmlWsbp::text('filters[N][min_date]', array('attrs' => 'class="wsbp-field-date wupsales-width100"'));
				?>
				<div class="options-label">-</div>
				<?php HtmlWsbp::text('filters[N][max_date]', array('attrs' => 'class="wsbp-field-date wupsales-width100"')); ?>
			</div>
			<div class="options-value">
				<?php HtmlWsbp::checkboxToggle('filters[N][without_order]', array()); ?>
				<div class="options-label">add without orders</div>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-active">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a>
			<?php esc_html_e('Last active', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::hidden('filters[N][type]', array('value' => 'active'));
					HtmlWsbp::text('filters[N][min_date]', array('attrs' => 'class="wsbp-field-date wupsales-width100"'));
				?>
				<div class="options-label">-</div>
				<?php HtmlWsbp::text('filters[N][max_date]', array('attrs' => 'class="wsbp-field-date wupsales-width100"')); ?>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-category">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a>
			<?php esc_html_e('Categories', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::hidden('filters[N][type]', array('value' => 'category'));
					HtmlWsbp::selectlist('filters[N][category]', array(
						'options' => $modelActions->getTaxonomyHierarchy('product_cat', $args),
						'attrs' => 'data-placeholder="' . __('Select category', 'publish-your-table') . '"',
						'class' => 'no-chosen'
						));
					?>
			</div>
			<div class="options-value">
				<div class="options-label">with children</div>
				<?php HtmlWsbp::checkboxToggle('filters[N][with_child]', array('checked' => false)); ?>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-attribute">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a>
			<?php esc_html_e('Attributes', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::hidden('filters[N][type]', array('value' => 'attribute'));
					HtmlWsbp::selectBox('filters[N][attribute]', array('options' => $attributes, 'attrs' => 'class="wsbp-attribute-slug"')); 
				?>
			</div>
			<div class="options-value wupsales-hidden wsbp-attribute-value">
				<?php 
					HtmlWsbp::selectlist('filters[N][value]', array(
						'options' => array(),
						'attrs' => 'data-placeholder="' . __('Select values', 'publish-your-table') . '"',
						'class' => 'no-chosen'
						));
					?>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-tag">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a> 
			<?php esc_html_e('Tags', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::hidden('filters[N][type]', array('value' => 'tag'));
					HtmlWsbp::selectlist('filters[N][tag]', array(
						'options' => $modelActions->getTaxonomyHierarchy('product_tag', $args),
						'attrs' => 'data-placeholder="' . __('Select tag', 'publish-your-table') . '"',
						'class' => 'no-chosen'
						));
					?>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-brand">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a> 
			<?php esc_html_e('Brands', 'wupsales-reward-points'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlWsbp::hidden('filters[N][type]', array('value' => 'brand'));
					HtmlWsbp::selectlist('filters[N][brand]', array(
						'options' => $modelActions->getTaxonomyHierarchy('pwb-brand', $args),
						'attrs' => 'data-placeholder="' . __('Select brand', 'publish-your-table') . '"',
						'class' => 'no-chosen'
						));
					?>
			</div>
		</div>
	</div>
	<div class="row row-options-block" id="wsbp-filter-product">
		<div class="<?php echo esc_attr($bLabel); ?>">
			<a href="#" class="wupsales-list-actions wsbp-delete-cond"><i class="fa fa-times"></i></a> 
			<?php esc_html_e('Products', 'wupsales-reward-points') . ( $emptyProducts ? ' ids' : '' ); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
				HtmlWsbp::hidden('filters[N][type]', array('value' => 'product'));
				if ($emptyProducts) {
					HtmlWsbp::text('filters[N][product]', array());
					?>
					<div class="options-label"><?php esc_html_e('example input: 1,2,3', 'publish-your-table'); ?></div>
				<?php
				} else {
					HtmlWsbp::selectlist('filters[N][product]', array(
						'options' => $this->products,
						'attrs' => 'data-placeholder="' . __('Select products', 'publish-your-table') . '"',
						'class' => 'no-chosen'
						));
				} 
				?>
			</div>
		</div>
	</div>
</div>
