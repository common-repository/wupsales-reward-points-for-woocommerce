<?php
class BonusesControllerWsbp extends ControllerWsbp {

	protected $_code = 'bonuses';

	public function getNoncedMethods() {
		return array('setProductPoints', 'saveProductBonuses', 'recalcProductsPoints', 'addUserCartPointsDiscount');
	}
	
	public function getProductsList() {
		$res = new ResponseWsbp();
		$res->ignoreShellData();

		$params = array(
			'isPage' => true,
			'length' => ReqWsbp::getVar('length'),
			'start' => ReqWsbp::getVar('start'),
			'search' => ReqWsbp::getVar('search'),
			'order' => ReqWsbp::getVar('order'),
			'variations' => ReqWsbp::getVar('variations')
		);
		
		$result = false;
		$data = $this->getModel()->getProductsList($params);

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

	public function setProductPoints() {
		$res = new ResponseWsbp();
		$point = ReqWsbp::getVar('point');
		if ($this->getModel()->setProductPoints(ReqWsbp::getVar('ids'), $point)) {
			$res->addMessage(esc_html__('Product\'s point saved', 'wupsales-reward-points'));
			$res->point = $point;
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	
	public function saveProductBonuses() {
		$res = new ResponseWsbp();
		$res->addMessage(esc_html__('Reward points saved', 'wupsales-reward-points'));
		$res = DispatcherWsbp::applyFilters('saveProductBonuses', $res);

		return $res->ajaxExec();
	}
	
	public function recalcProductsPoints() {
		$res = new ResponseWsbp();
		$inCron = ReqWsbp::getVar('inCron');
		
		if ($inCron) {
			if ( !wp_next_scheduled( 'wsbp_calc_products_points' ) ) {
				wp_schedule_single_event( time() + 3, 'wsbp_calc_products_points' );
			}
			$result = true;
		} else {
			$result = $this->getModel('products')->recalcProductsPoints();
		}
		
		if ($result) {
			$res->addMessage($inCron ? esc_html__('Done', 'wupsales-reward-points') : esc_html__('Products points recalculated', 'wupsales-reward-points'));
		} else {
			$res->pushError(FrameWsbp::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function addUserCartPointsDiscount() {
		$res = new ResponseWsbp();
		$point = DispatcherWsbp::applyFilters('getPointsForDiscount', ReqWsbp::getVar('points'));
		$res->data = array('points' => empty($point) ? '' : $point);
		if (false === $point) {
			$res->pushError(FrameWsbp::_()->getErrors());
			return $res->ajaxExec();
		}
		$rate = ReqWsbp::getVar('rate');
		if (!empty($rate)) {
			$point = round($point / $rate, 2);
		}
		$module = $this->getModule();
		$userParams = $module->getUserParams();
		if ($module->isActiveUser()) {
			if (FrameWsbp::_()->getModule('actions')->getModel('users')->addUserCartPointsDiscount($userParams, $point)) {
				$res->addMessage(esc_html__('Reward points has been activated', 'wupsales-reward-points'));
			} else {
				$res->pushError(FrameWsbp::_()->getErrors());
			}
		} else {
			if ($userParams) {
				if (2 == $userParams['status']) {
					$res->pushError(__('You are blocked', 'wupsales-reward-points'));
				} else {
					$res->pushError(__('You are refused', 'wupsales-reward-points'));
				}
			} else {
				$res->pushError(__('You are not logged in', 'wupsales-reward-points'));
			}
		}
		return $res->ajaxExec();
	}
	public function getWidgetPopup() {
		$res = new ResponseWsbp();
		$userId = ReqWsbp::getVar('userId', 'all', 0);
		$res->data = $this->getModule()->getView()->renderWidgetPopup($userId, ReqWsbp::getVar('front', 'all', false));
		return $res->ajaxExec();
	}
	public function getUserTransactions() {
		$res = new ResponseWsbp();
		$userId = ReqWsbp::getVar('userId');
		$params = ReqWsbp::getVar('params');
		$res->html = $this->getModule()->getView()->renderUserTransactions($userId, $params);
		return $res->ajaxExec();
	}
}
