<?php
class TransactionsModelWsbp extends ModelWsbp {
	
	//Manual/Auto:
	//	+ Active/Spent/Expired/Deleted
	//	- Reserved/Completed/Deleted
	//Purchase:
	//	Active/Spent/Expired/Refund
	//Discount:
	//	Reserved/Completed/Canceled/Refund
	
	public function __construct() {
		$this->_setTbl('transactions');
		$lists = array(
			'tr_type' => array(
				0 => __('Manual', 'wupsales-reward-points'),
				1 => __('Auto', 'wupsales-reward-points'),
				2 => __('Purchase', 'wupsales-reward-points'),
				3 => __('Discount', 'wupsales-reward-points'),
				4 => __('Fixes', 'wupsales-reward-points'),
			),
			'status' => array(
				0 => __('Active', 'wupsales-reward-points'),
				1 => __('Spent', 'wupsales-reward-points'),
				2 => __('Expired', 'wupsales-reward-points'),
				5 => __('Reserved', 'wupsales-reward-points'), //Technical wait for debit
				6 => __('Completed', 'wupsales-reward-points'),
				7 => __('Refund', 'wupsales-reward-points'),
				8 => __('Сanceled', 'wupsales-reward-points'),
				9 => __('Deleted', 'wupsales-reward-points'),
			)
		);
		$this->setFieldLists($lists);
	}
	
	public function controlExpired( $userId = 0, $recalc = false ) {
		$userId = (int) $userId;
		$where =  ' WHERE status=0 AND exp_date<=' . UtilsWsbp::getTimestamp();
		if (empty($userId)) {
			$tempTable = DbWsbp::createTemporaryTable('wsbpTempExpired', 'SELECT DISTINCT user_id as id FROM `@__transactions`' . $where);
			$cnt = DbWsbp::get('select count(*) from ' . $tempTable . ' as t', 'one');
			if ($cnt) {
				$update = 'UPDATE `@__transactions` r' .
					' INNER JOIN ' . $tempTable . ' t ON (t.id=r.user_id)' .
					' SET status=2';
				if (!DbWsbp::query($update)) {
					FrameWsbp::_()->pushError('Error query: ' . $update);
					FrameWsbp::_()->pushError(DbWsbp::getError());
					return false;
				}
				if ($recalc) {
					return FrameWsbp::_()->getModule('actions')->getModel('users')->doRecalcUsersParams(0, $tempTable);
				}
			}
		} else {
			$update = 'UPDATE `@__transactions` SET status=2' . $where . ( empty($userId) ? '' : ' AND user_id=' . $userId );
			if (DbWsbp::query($update, true) && $recalc) {
				return FrameWsbp::_()->getModule('actions')->getModel('users')->doRecalcUsersParams($userId, ''); 
			}
		}
		return true;
	}
	
	public function insertTransaction( $data, $details = array() ) {
		$userId = $data['user_id'];
		$tran = $this->setWhere(array('user_id' => $userId, 'op_id' => $data['op_id'], 'tr_type' => $data['tr_type']) )->getFromTbl();
		if (!empty($tran)) {
			return false;
		}

		$points = $data['points'];
		$data['created'] = UtilsWsbp::getFormatedDateTime(UtilsWsbp::getTimestamp(), 'Y-m-d H:i:s');
		if ($points < 0) {
			$data['status'] = 5;
		
			$trId = $this->insert($data);
			if ($trId) {
				return $this->completeDebitTransaction($trId);
			}
		} else {
			$data['rest'] = $data['points'];
			$trId = $this->insert($data);
			if ($trId && $this->getModule()->getModel('details')->insertDetails($trId, $details)) {
				return FrameWsbp::_()->getModule('actions')->getModel('users')->doRecalcUsersParams($userId, '');
			}
		}
		return true;
	}
	
	public function completeDebitTransaction( $trId = 0 ) {
		$where = 'status=5' . ( empty($trId) ? '' : ' AND id=' . $trId );
		$trans = $this->setWhere($where)->getFromTbl();
		if (!empty($trans)) {
			$detModel = $this->getModule()->getModel('details');
			$curTime = UtilsWsbp::getTimestamp();
			foreach ($trans as $tran) {
				$trId = $tran['id'];
				$amount = abs($tran['points']);
				$details = array();
				$userId = $tran['user_id'];
				$credits = $this->setWhere(array('user_id' => $userId, 'status' => 0, 'additionalCondition' => 'rest>0'))
					->setOrderBy('exp_date')->getFromTbl();
				foreach ($credits as $credit) {
					$rest = $credit['rest'];
					$crId = $credit['id'];
					if (!is_null($credit['exp_date']) && $credit['exp_date'] <= $curTime) {
						continue;
					}
					if ($rest > $amount) {
						$rest -= $amount;
						$this->updateById(array('rest' => $rest), $crId);
						$details[] = array('source_id' => $crId, 'points' => $amount * ( -1 ), 'conditions' => $credit);
						break;
					} else {
						$this->updateById(array('rest' => 0, 'status' => 1), $crId);
						$amount -= $rest;
						//$ids[$crId] = $rest;
						$details[] = array('source_id' => $crId, 'points' => $rest * ( -1 ), 'conditions' => $credit);
						if (empty($amount)) {
							break;
						}
					}
				}
				if (!$detModel->insertDetails($trId, $details)) {
					return false;
				}
				$this->updateById(array('status' => 6), $trId);
				if (!FrameWsbp::_()->getModule('actions')->getModel('users')->doRecalcUsersParams($userId, '')) {
					return false;
				}
			}
		}
		return true;
	}
	public function getUserActiveBonuses( $userId ) {
		$query = 'SELECT t.tr_type, t.exp_date, a.reason, sum(t.rest) as rest FROM `@__transactions` t' .
			' LEFT JOIN `@__actions` a ON (a.id=t.op_id)' .
			' WHERE t.status=0 AND t.rest>0 AND t.user_id=' . ( (int) $userId ) .
			' GROUP BY tr_type, exp_date, reason';
		$trans = DbWsbp::get($query);
		return empty($trans) ? array() : $trans;
	}
	public function getUserTransactions( $userId, $params ) {
		$where = '';
		$order = '';
		$dateFormat = UtilsWsbp::getArrayValue($params, 'date_format', $this->getModule()->getDateFormat());
		foreach ($params as $key => $value) {
			switch ($key) {
				case 'from':
					$where .= " AND t.created>='" . UtilsWsbp::convertDateFormat($value, $dateFormat) . " 00:00:00'";
					break;
				case 'to':
					$where .= " AND t.created<='" . UtilsWsbp::convertDateFormat($value, $dateFormat) . " 23:59:59'";
					break;
				case 'sort':
					$order = $value;
					if (isset($params['dir'])) {
						$order .= ' ' . $params['dir'];
					}
					break;
				default:
					break;
			}
		}
		
		$query = 'SELECT t.id, t.tr_type, t.created, a.reason, t.points, t.status, t.op_id, a.author FROM `@__transactions` t' .
			' LEFT JOIN `@__actions` a ON (a.id=t.op_id)' .
			' WHERE t.user_id=' . ( (int) $userId ) . $where .
			' ORDER BY ' . ( empty($order) ? 'created DESC' : $order );
		$trans = DbWsbp::get($query);
		return empty($trans) ? array() : $trans;
	}
	public function getOrderTransactions( $userId, $opId ) {
		return $this->setWhere(array('user_id' => $userId, 'op_id' => $opId, 'additionalCondition' => 'status<7'))->getFromTbl();
	}
	public function returnPoints( $trId, $points, $expDate = false ) {
		$points = ( (float) $points );
		if ($points > 0) {
			$update = 'UPDATE `@__transactions` SET rest=IF(rest+' . $points . '>points, points, rest+' . $points .
				'),status=IF(status=1,0,status)' . ( $expDate ? ', exp_date=' . ( (int) $expDate ) : '' ) .
				' WHERE id=' . ( (int) $trId );
			return DbWsbp::query($update) ? $points : 0;
		}
		return 0;
	}
	public function getExpiryDate( $userId ) {
		$query = 'SELECT min(exp_date) FROM `@__transactions`' .
			' WHERE tr_type=2 AND exp_date>' . UtilsWsbp::getTimestamp() . ' AND user_id=' . ( (int) $userId );
		return DbWsbp::get($query, 'one');
	}
	public function addExpiryDates( $userId, $newDate ) {
		$newDate = ( (float) $newDate );
		if ($newDate > 0) {
			$update = 'UPDATE `@__transactions` SET exp_date=' . $newDate .
				' WHERE tr_type=2 AND exp_date>' . UtilsWsbp::getTimestamp() . ' AND user_id=' . ( (int) $userId );
			return DbWsbp::query($update);
		}
		return true;
	}
	public function returnExpiryDates( $userId, $newDate, $created ) {
		$newDate = ( (float) $newDate );
		if ($newDate > 0) {
			$update = 'UPDATE `@__transactions` SET exp_date=' . $newDate .
				' WHERE tr_type=2 AND exp_date>' . UtilsWsbp::getTimestamp() .
				" AND created<='" . $created . "'" .
				' AND user_id=' . ( (int) $userId );
			return DbWsbp::query($update);
		}
		return true;
	}
}
