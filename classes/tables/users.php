<?php
class TableUsersWsbp extends TableWsbp {
	public function __construct() {
		$this->_table = '@__users';
		$this->_id = 'id';
		$this->_alias = 'wsbp_users';
		$this->_addField('id', 'text', 'int')
			->_addField('status', 'text', 'tinyint', 0, esc_html__('Status', 'wupsales-reward-points'))
			->_addField('birthday', 'text', 'int', 0, esc_html__('Birthday', 'wupsales-reward-points'))
			->_addField('bd', 'text', 'text', '', esc_html__('Birthday m-d', 'wupsales-reward-points'))
			->_addField('bd_updated', 'text', 'date', 0, esc_html__('Last update for birthday ', 'wupsales-reward-points'))
			->_addField('points', 'text', 'decimal', 0, esc_html__('Reward Points', 'wupsales-reward-points'))
			->_addField('cart', 'text', 'decimal', 0, esc_html__('Cart coupon', 'wupsales-reward-points'))
			->_addField('total_amount', 'text', 'decimal', 0, esc_html__('Total Amount', 'wupsales-reward-points'))
			->_addField('total_count', 'text', 'decimal', 0, esc_html__('Total Order Count', 'wupsales-reward-points'))
			->_addField('last_order', 'text', 'date', 0, esc_html__('Last Order Date', 'wupsales-reward-points'))
			->_addField('calculated', 'text', 'date', 0, esc_html__('Calculated', 'wupsales-reward-points'));
	}
}
