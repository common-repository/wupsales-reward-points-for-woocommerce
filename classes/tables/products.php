<?php
class TableProductsWsbp extends TableWsbp {
	public function __construct() {
		$this->_table = '@__products';
		$this->_id = 'id';
		$this->_alias = 'wsbp_products';
		$this->_addField('id', 'text', 'int')
			->_addField('parent', 'text', 'int', 0, esc_html__('Main product', 'wupsales-reward-points'))
			->_addField('point', 'text', 'text', 0, esc_html__('Product point', 'wupsales-reward-points'))
			->_addField('price', 'text', 'decimal', 0, esc_html__('Product price', 'wupsales-reward-points'))
			->_addField('pr_points', 'text', 'decimal', 0, esc_html__('Product Reward Points', 'wupsales-reward-points'))
			->_addField('gr_num', 'text', 'int', 0, esc_html__('Number of the group', 'wupsales-reward-points'))
			->_addField('gr_points', 'text', 'decimal', 0, esc_html__('Group Reward Points', 'wupsales-reward-points'))
			->_addField('points', 'text', 'decimal', 0, esc_html__('Reward Points', 'wupsales-reward-points'))
			->_addField('calculated', 'text', 'date', 0, esc_html__('Calculated', 'wupsales-reward-points'));
	}
}
