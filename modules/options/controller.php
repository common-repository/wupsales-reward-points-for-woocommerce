<?php
class OptionsControllerWsbp extends ControllerWsbp {
	public function saveOptions() {
		$res = new ResponseWsbp();
		if ($this->getModel()->saveOptions(ReqWsbp::get('post'))) {
			$res->addMessage(esc_html__('Done', 'wupsales-reward-points'));
		} else {
			$res->pushError ($this->getModel('options')->getErrors());
		}
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			WSBP_USERLEVELS => array(
				WSBP_ADMIN => array('saveGroup', 'resetRulesPage', 'activateBonusProgram')
			),
		);
	}
	public function resetRulesPage() {
		$res = new ResponseWsbp();
		if ($this->getView()->renderPageRules(true)) {
			$res->addMessage(esc_html__('Done', 'wupsales-reward-points'));
		} else {
			$res->pushError (FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function activateBonusProgram() {
		$res = new ResponseWsbp();
		if ($this->getModel()->activateBonusProgram(ReqWsbp::getVar('activate'))) {
			$res->addMessage(esc_html__('Done', 'wupsales-reward-points'));
		} else {
			$res->pushError (FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
}
