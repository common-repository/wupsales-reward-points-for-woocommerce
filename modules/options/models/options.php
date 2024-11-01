<?php
class OptionsModelWsbp extends ModelWsbp {
	public $rulesPageMetaKey = 'wsbp_rules';
	private $activateMetaKey = 'wsbp_is_active';
	private $activateHMetaKey = 'wsbp_active_history';
	private $_values = array();
	private $rulesPage = null;
	//private $_valuesLoaded = false;
	
	public function get( $gr, $key ) {
		$this->_loadOptValues($gr);
		return empty($key) ? $this->_values[$gr] : ( isset($this->_values[$gr][$key]) ? $this->_values[$gr][$key] : false );
	}
	public function getDef( $gr, $key ) {
		$this->_loadOptValues($gr);
		if (empty($key)) {
			return $this->_values[$gr];
		}
		if (isset($this->_values[$gr][$key])) {
			return isset($this->_values[$gr][$key]);
		}
		if ('main' == $gr) {
			return $this->getModule()->getDefaultMainSettings($key);
		}
		return false;
	}
	public function reset( $gr ) {
		$this->_values[$gr] = array();
	}
	public function isEmpty( $gr, $key ) {
		$value = $this->get($gr, $key);
		return ( false === $value );
	}
	public function save( $gr, $key, $val, $ignoreDbUpdate = false ) {
		$this->_loadOptValues($gr);
		if (!isset($this->_values[$gr][$key]) || $this->_values[$gr][$key] != $val) {
			$this->_values[$gr][$key] = $val;
			if (!$ignoreDbUpdate) {
				$this->_updateOptsInDb($gr);
			}
			return true;
		}
		return false;
	}
	public function getAll() {
		$tabs = $this->getModule()->getOptionsTabsList();
		foreach ($tabs as $gr => $d) {
			$this->_loadOptValues($gr);
		}
		return $this->_values;
	}

	public function saveOptions( $data = array(), $tabs = false ) {
		$leer = true;

		if (is_array($data)) {
			if (false === $tabs) {
				$tabs = $this->getModule()->getOptionsTabsList();
			}
			$needRecalcPoints = false;
			$recalcOptions = $this->getModule()->getRecalcOptions();
			foreach ($data as $gr => $d) {
				if (isset($tabs[$gr]) && is_array($d)) {
					$leer = false;
					$needSave = false;
					if ($tabs[$gr]['remove']) {
						$this->reset($gr);
						$needSave = true;
					}
					foreach ($d as $key => $val) {
						if ($this->save($gr, $key, $val, true)) {
							$needSave = true;
							if (isset($recalcOptions[$gr]) && ( true === $recalcOptions[$gr] || in_array($key, $recalcOptions[$gr]) )) {
								$needRecalcPoints = true;
							}
						}
					}
					if ($needSave) {
						$this->_updateOptsInDb($gr);
					}
				}
			}
			if ($needRecalcPoints) {
				FrameWsbp::_()->getModule('bonuses')->getModel('products')->recalcProductsPoints(0, array('recalcPrPoints' => false));
			}
		}
		if ($leer) {
			$this->pushError(esc_html__('Empty data to save option', 'wupsales-reward-points'));
			return false;
		}
		return true;
	}

	private function _updateOptsInDb( $gr ) {
		update_option(WSBP_CODE . '_options_' . $gr, $this->_values[$gr]);
	}
	private function _loadOptValues( $gr ) {
		if (!isset($this->_values[$gr])) {
			$this->_values[$gr] = get_option(WSBP_CODE . '_options_' . $gr);
			if (empty($this->_values[$gr])) {
				$this->_values[$gr] = array();
			}
		}
	}
	public function getPageRules() {
		if (is_null($this->rulesPage)) {
			$args = array(
				'numberposts' => 1,
				'post_type'  => 'page',
				'meta_query' => array(
					array(
						'key'   => $this->rulesPageMetaKey,
						'value' => '1',
					)
				)
			);
			$page = get_posts($args);
			$this->rulesPage = $page && count($page) == 1 ? $page[0] : false;
		}
		return $this->rulesPage;
	}
	public function isActiveBonusProgram() {
		return get_option($this->activateMetaKey) == 1;
	}
	public function activateBonusProgram( $val ) {
		$val = (int) $val;
		update_option($this->activateMetaKey, $val);
		$history = $this->getActiveHistory();
		$last = empty($history) ? 0 : ( (int) end($history) );
		if ($val != $last) {
			$history[UtilsWsbp::getTimestamp()] = $val;
			update_option($this->activateHMetaKey, $history);
		}
	}
	public function getActiveHistory() {
		$history = get_option($this->activateHMetaKey);
		return empty($history) || !is_array($history) ? array() : $history;
	}
}
