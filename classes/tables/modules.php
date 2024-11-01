<?php
class TableModulesWsbp extends TableWsbp {
	public function __construct() {
		$this->_table = '@__modules';
		$this->_id = 'id';     /*Let's associate it with posts*/
		$this->_alias = 'sup_m';
		$this->_addField('label', 'text', 'varchar', 0, esc_html__('Label', 'wupsales-reward-points'), 128)
				->_addField('type_id', 'selectbox', 'smallint', 0, esc_html__('Type', 'wupsales-reward-points'))
				->_addField('active', 'checkbox', 'tinyint', 0, esc_html__('Active', 'wupsales-reward-points'))
				->_addField('params', 'textarea', 'text', 0, esc_html__('Params', 'wupsales-reward-points'))
				->_addField('code', 'hidden', 'varchar', '', esc_html__('Code', 'wupsales-reward-points'), 64)
				->_addField('ex_plug_dir', 'hidden', 'varchar', '', esc_html__('External plugin directory', 'wupsales-reward-points'), 255);
	}
}
