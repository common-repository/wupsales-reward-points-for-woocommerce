<?php
class TableActionsWsbp extends TableWsbp {
	public function __construct() {
		$this->_table = '@__actions';
		$this->_id = 'id';
		$this->_alias = 'wsbp_actions';
		$this->_addField('id', 'text', 'int')
			->_addField('reason', 'text', 'varchar', '', esc_html__('Title', 'wupsales-reward-points'), 100)
			->_addField('tr_type', 'text', 'tinyint', 0, esc_html__('Type', 'wupsales-reward-points'))
			->_addField('points', 'text', 'decimal', 0, esc_html__('Poins', 'wupsales-reward-points'))
			->_addField('act_date', 'text', 'int', '', esc_html__('Action Date', 'wupsales-reward-points'))
			->_addField('end_date', 'text', 'int', '', esc_html__('End Action Date', 'wupsales-reward-points'))
			->_addField('exp_date', 'text', 'int', '', esc_html__('Expired Date', 'wupsales-reward-points'))
			->_addField('completed', 'text', 'int', '', esc_html__('Expired Date', 'wupsales-reward-points'))
			->_addField('cnt_users', 'text', 'int', '', esc_html__('Count users', 'wupsales-reward-points'))
			->_addField('status', 'text', 'tinyint', 0, esc_html__('Status', 'wupsales-reward-points'))
			->_addField('triger', 'text', 'varchar', '', esc_html__('Trigger', 'wupsales-reward-points'), 10)
			->_addField('params', 'text', 'text', '', esc_html__('Parameters', 'wupsales-reward-points'))
			->_addField('conditions', 'text', 'text', '', esc_html__('Conditions', 'wupsales-reward-points'))
			->_addField('author', 'text', 'int', '', esc_html__('Author', 'wupsales-reward-points'));
	}
}
