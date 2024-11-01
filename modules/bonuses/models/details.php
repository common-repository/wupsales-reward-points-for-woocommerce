<?php
class DetailsModelWsbp extends ModelWsbp {

	public function __construct() {
		$this->_setTbl('details');
		$lists = array(
			'source' => array(
				0 => __('Transaction', 'wupsales-reward-points'),
				1 => __('Product', 'wupsales-reward-points'),
				2 => __('Cart reward', 'wupsales-reward-points'),
				3 => __('Level reward', 'wupsales-reward-points'),
				4 => __('Cart fix', 'wupsales-reward-points'),
			)
		);
		$this->setFieldLists($lists);
	}
	
	public function insertDetails( $trId, $details ) {
		if (empty($details)) {
			return true;
		}
		$insert = 'INSERT INTO `@__details` (tr_id';
		$first = $details[0];
		$keys = array();
		$fields = $this->getTable()->getFields();
		foreach ($first as $key => $v) {
			if (isset($fields[$key])) {
				$keys[$key] = $fields[$key]->default;
				$insert .= ',' . $key;
			}
		}
		$insert .= ') VALUES ';
		foreach ($details as $detail) {
			$insert .= '(' . $trId;
			foreach ($keys as $key => $default) {
				$insert .= ",'" . ( isset($detail[$key]) ? ( is_array($detail[$key]) ? UtilsWsbp::jsonEncode($detail[$key]) : $detail[$key] ) : $default ) . "'";
			}
			$insert .= '),';
		}
		$insert = substr($insert, 0, -1);
		if (!DbWsbp::query($insert)) {
			FrameWsbp::_()->pushError('Error query: ' . $insert);
			FrameWsbp::_()->pushError(DbWsbp::getError());
			return false;
		}
		return true;
	}
	public function getTransactionDetails( $trId ) {
		return $this->setWhere(array('tr_id' => $trId))->setOrderBy('source')->getFromTbl();
	}
}
