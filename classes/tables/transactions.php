<?php
class TableTransactionsWsbp extends TableWsbp {
	public function __construct() {
		$this->_table = '@__transactions';
		$this->_id = 'id';
		$this->_alias = 'wsbp_transactions';
		$this->_addField('id', 'text', 'int')
			->_addField('user_id', 'text', 'int', '', esc_html__('User Id', 'wupsales-reward-points'))
			->_addField('tr_type', 'text', 'tinyint', 0, esc_html__('Type', 'wupsales-reward-points'))
			->_addField('points', 'text', 'decimal', 0, esc_html__('Poins', 'wupsales-reward-points'))
			->_addField('rest', 'text', 'decimal', 0, esc_html__('Rest', 'wupsales-reward-points'))
			->_addField('created', 'text', 'datetime', 0, esc_html__('Transaction DateTime', 'wupsales-reward-points'))
			->_addField('exp_date', 'text', 'int', 0, esc_html__('Expired Date', 'wupsales-reward-points'))
			->_addField('op_id', 'text', 'int', 0, esc_html__('Action/Order Id', 'wupsales-reward-points'))
			->_addField('uniq', 'text', 'int', 0, esc_html__('Unique number', 'wupsales-reward-points'))
			->_addField('email', 'text', 'int', 0, esc_html__('Send email', 'wupsales-reward-points'))
			->_addField('popup', 'text', 'int', 0, esc_html__('Show popup', 'wupsales-reward-points'))
			->_addField('status', 'text', 'tinyint', 0, esc_html__('Status', 'wupsales-reward-points'));
	}
}
