<?php
class ActionsControllerWsbp extends ControllerWsbp {

	protected $_code = 'actions';

	public function getNoncedMethods() {
		return array('addUsersPoints', 'lockUser', 'recalcUsersBalance', 'saveUserAction', 'deleteAction', 'saveUserBirthday', 'setUserStatus', 'getEmailPreview', 'getPopupPreview', 'getWidgetUsersList', 'addNewUser');
	}
	
	public function getUsersList() {
		$res = new ResponseWsbp();
		$res->ignoreShellData();

		$params = array(
			'isPage' => true,
			'length' => ReqWsbp::getVar('length'),
			'start' => ReqWsbp::getVar('start'),
			'search' => ReqWsbp::getVar('search'),
			'order' => ReqWsbp::getVar('order'),
			//'columns' => ReqWsbp::getVar('columns'),
			'filters' => ReqWsbp::getVar('filters'),

		);
		
		$result = false;
		$data = $this->getModel('users')->getUsersList($params);

		if ($data) {
			$res->data = $data['rows'];
			$res->recordsTotal = $data['total'];
			$res->recordsFiltered = $data['filtered'];
			$res->draw = ReqWsbp::getVar('draw');
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	
	public function getHistoryList() {
		$res = new ResponseWsbp();
		$res->ignoreShellData();

		$params = array(
			'isPage' => true,
			'length' => ReqWsbp::getVar('length'),
			'start' => ReqWsbp::getVar('start'),
			'search' => ReqWsbp::getVar('search'),
			'order' => ReqWsbp::getVar('order'),
			'completed' => ReqWsbp::getVar('completed'),

		);
		
		$result = false;
		$data = $this->getModel()->getHistoryList($params);

		if ($data) {
			$res->data = $data['rows'];
			$res->recordsTotal = $data['total'];
			$res->recordsFiltered = $data['filtered'];
			$res->draw = ReqWsbp::getVar('draw');
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();

	}
	
	public function recalcUsersBalance() {
		$res = new ResponseWsbp();
		
		$inCron = ReqWsbp::getVar('inCron');
		
		if ($inCron) {
			if (!wp_next_scheduled('wsbp_calc_users_balance')) {
				wp_schedule_single_event(time() + 3, 'wsbp_calc_users_balance');
			}
			$result = true;
		} else {
			$result = $this->getModel('users')->recalcUsersParams();
		}
		
		if ($result) {
			$res->addMessage($inCron ? esc_html__('Done', 'wupsales-reward-points') : esc_html__('Users balance recalculated', 'wupsales-reward-points'));
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	
	public function lockUser() {
		$res = new ResponseWsbp();
		$lock = ReqWsbp::getVar('lock');
		if ($this->getModel('users')->lockUser(ReqWsbp::getVar('userId'), $lock)) {
			$res->addMessage('lock' == $lock ? esc_html__('User blocked', 'wupsales-reward-points') : esc_html__('User unlocked', 'wupsales-reward-points'));
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}

	public function saveUserAction() {
		$res = new ResponseWsbp();
		$params = ReqWsbp::getVar('params', 'post', null, array('message'));

		$id = ReqWsbp::getVar('actionId');
		if (ReqWsbp::getVar('actionWidget') == 1) {
			if (!$this->getModule()->isEnableUsersWidget()) {
				$res->pushError(esc_html__('Access error', 'wupsales-reward-points'));
				return $res->ajaxExec();
			}
			$expDays = (int) FrameWsbp::_()->getModule('bonuses')->getMainOptions('expiry_date');
			$params['expiry'] = UtilsWsbp::addDays($expDays);
		}
		
		if ($this->getModel()->saveUserAction($id, $params, ReqWsbp::getVar('conditions'))) {
			$res->addMessage(esc_html__('User\'s action saved', 'wupsales-reward-points'));
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	
	public function deleteAction() {
		$res = new ResponseWsbp();
		if ($this->getModel()->deleteAction(ReqWsbp::getVar('actionId'))) {
			$res->addMessage(esc_html__('Action deleted', 'wupsales-reward-points'));
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function getEmailPreview() {
		$res = new ResponseWsbp();
		$params = ReqWsbp::getVar('params', 'post', array(), array('message'));
		if (isset($params['email']['message'])) {
			$params['email']['message'] = stripslashes(base64_decode($params['email']['message']));
		}
		$res->html = $this->getView()->getEmailPreview($params);
		return $res->ajaxExec();
	}
	public function getPopupPreview() {
		$res = new ResponseWsbp();
		$params = ReqWsbp::getVar('params', 'post', array(), array('message'));
		if (isset($params['popup']['message'])) {
			$params['popup']['message'] = stripslashes(base64_decode($params['popup']['message']));
		}
		$res->html = $this->getView()->renderActionPopup($params);
		return $res->ajaxExec();
	}
	public function getActionPopup() {
		$res = new ResponseWsbp();
		$userId = get_current_user_id();
		$res->html = empty($userId) ? '' : $this->getView()->getActionPopup($userId);
		return $res->ajaxExec();
	}
	public function saveUserBirthday() {
		$res = new ResponseWsbp();
		$userId = get_current_user_id();
		$birthday = ReqWsbp::getVar('birthday');
		if ($this->getModel('users')->saveUserBirthday($userId, $birthday)) {
			$res->addMessage(esc_html__('Done', 'wupsales-reward-points'));
			$userParams = FrameWsbp::_()->getModule('bonuses')->getUserParams($userId);
			$res->is_age_pass = FrameWsbp::_()->getModule('bonuses')->isUserAgePass($userParams) ? 1 : 0;
			$res->user_status = UtilsWsbp::getArrayValue($userParams, 'status', 0, 1);
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function setUserStatus() {
		$res = new ResponseWsbp();
		$userId = get_current_user_id();
		$status = ReqWsbp::getVar('status');
		if ($this->getModel('users')->setUserStatus($userId, $status)) {
			$res->addMessage(esc_html__('Done', 'wupsales-reward-points'));
			$userParams = FrameWsbp::_()->getModule('bonuses')->getUserParams($userId);
			$res->is_age_pass = FrameWsbp::_()->getModule('bonuses')->isUserAgePass($userParams) ? 1 : 0;
			$res->user_status = UtilsWsbp::getArrayValue($userParams, 'status', 0, 1);
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function getWidgetUsersList() {
		$res = new ResponseWsbp();
		$res->ignoreShellData();

		$params = array(
			'isPage' => true,
			'length' => ReqWsbp::getVar('length'),
			'start' => ReqWsbp::getVar('start'),
			'search' => ReqWsbp::getVar('search'),
			'order' => ReqWsbp::getVar('order'),
			//'columns' => ReqWsbp::getVar('columns'),
			'filters' => ReqWsbp::getVar('filters'),
		);
		
		$result = false;
		$data = $this->getModel('users')->getWidgetUsersList($params);

		if ($data) {
			$res->data = $data['rows'];
			$res->recordsTotal = $data['total'];
			$res->recordsFiltered = $data['filtered'];
			$res->draw = ReqWsbp::getVar('draw');
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function addNewUser() {
		$res = new ResponseWsbp();
		$params = ReqWsbp::getVar('params', 'post', null, array('message'));
		if (!$this->getModule()->isEnableUsersWidget()) {
			$res->pushError(esc_html__('Access error', 'wupsales-reward-points'));
			return $res->ajaxExec();
		}
		$userId = ReqWsbp::getVar('userId');

		if ($this->getModel('users')->saveNewUser($userId, $params)) {
			$res->addMessage(empty($userId) ? esc_html__('New User saved', 'wupsales-reward-points') : esc_html__('User data edited', 'wupsales-reward-points'));
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function getUserData() {
		$res = new ResponseWsbp();
		
		$userId = ReqWsbp::getVar('userId');
		$data = $this->getModel('users')->getUserData($userId);
		if ($data) {
			$res->data = $data;
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
}
