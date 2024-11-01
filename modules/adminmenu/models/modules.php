<?php
class ModulesModelWsbp extends ModelWsbp {
	public function __construct() {
		$this->_setTbl('modules');
		$this->setFieldLists(array('type_id' => array(1 => 'system', 6 => 'addons')));
	}

	public function get( $d = array() ) {
		if (isset($d['id']) && $d['id'] && is_numeric($d['id'])) {
			$fields = FrameWsbp::_()->getTable('modules')->fillFromDB($d['id'])->getFields();
			$fields['types'] = array();
			$types = $this->getFieldLists('type_id');
			foreach ($types as $t => $l) {
				$fields['types'][$t] = $l;
			}
			return $fields;
		} elseif (!empty($d)) {
			$data = FrameWsbp::_()->getTable('modules')->get('*', $d);
			return $data;
		} else {
			return FrameWsbp::_()->getTable('modules')->getAll();
		}
	}
	public function put( $d = array() ) {
		$res = new ResponseWsbp();
		$id = $this->_getIDFromReq($d);
		$d = prepareParamsWsbp($d);
		if (is_numeric($id) && $id) {
			if (isset($d['active'])) {
				$d['active'] = ( ( is_string($d['active']) && 'true' == $d['active'] ) || 1 == $d['active'] ) ? 1 : 0;
			}
			if (FrameWsbp::_()->getTable('modules')->update($d, array('id' => $id))) {
				$res->messages[] = esc_html__('Module Updated', 'wupsales-reward-points');
				$mod = FrameWsbp::_()->getTable('modules')->getById($id);
				$res->data = array(
					'id' => $id, 
					'label' => $mod['label'], 
					'code' => $mod['code'], 
					'active' => $mod['active'], 
				);
			} else {
				$tableErrors = FrameWsbp::_()->getTable('modules')->getErrors();
				if ($tableErrors) {
					$res->errors = array_merge($res->errors, $tableErrors);
				} else {
					$res->errors[] = esc_html__('Module Update Failed', 'wupsales-reward-points');
				}
			}
		} else {
			$res->errors[] = esc_html__('Error module ID', 'wupsales-reward-points');
		}
		return $res;
	}
	protected function _getIDFromReq( $d = array() ) {
		$id = 0;
		if (isset($d['id'])) {
			$id = $d['id'];
		} elseif (isset($d['code'])) {
			$fromDB = $this->get(array('code' => $d['code']));
			if (isset($fromDB[0]) && $fromDB[0]['id']) {
				$id = $fromDB[0]['id'];
			}
		}
		return $id;
	}
}
