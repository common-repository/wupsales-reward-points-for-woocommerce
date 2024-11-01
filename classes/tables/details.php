<?php
class TableDetailsWsbp extends TableWsbp {
	public function __construct() {
		$this->_table = '@__details';
		$this->_id = 'id';
		$this->_alias = 'wsbp_details';
		$this->_addField('id', 'text', 'int')
			->_addField('tr_id', 'text', 'int', 0, esc_html__('Transaction Id', 'wupsales-reward-points'), 128)
			->_addField('source', 'text', 'int', 0, esc_html__('Discount/Product/Cart', 'wupsales-reward-points'))
			->_addField('source_id', 'text', 'int', 0, esc_html__('Product Id / Transaction Id', 'wupsales-reward-points'))
			->_addField('pur_sum', 'text', 'decimal', 0, esc_html__('Purchase sum', 'wupsales-reward-points'))
			->_addField('pur_cur', 'text', 'text', '', esc_html__('Purchase currency', 'wupsales-reward-points'))
			->_addField('pur_cnt', 'text', 'int', 0, esc_html__('Purchase quantity', 'wupsales-reward-points'))
			->_addField('points', 'text', 'decimal', 0, esc_html__('Points', 'wupsales-reward-points'))
			->_addField('conditions', 'text', 'text', '', esc_html__('Current options', 'wupsales-reward-points'));
	}
}
