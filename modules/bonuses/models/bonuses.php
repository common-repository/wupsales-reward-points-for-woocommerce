<?php
class BonusesModelWsbp extends ModelWsbp {
	public $productMetaKey = 'wsbp_point';
	public $orderMetaExpiryKey = 'wsbp_expiry';
	
	public function __construct() {
		//$this->_setTbl('products');
	}
	
	public function getProductsList( $params ) {
		$data = array();
		$postTypes = array('product');
		$variations = UtilsWsbp::getArrayValue($params, 'variations', false);
		if ($variations) {
			$postTypes[] = 'product_variation';
		}
		$postStatuses = array('publish', 'private');
		
		$args = array(
			'post_type'           => $postTypes,
			'ignore_sticky_posts' => true,
			'post_status'         => $postStatuses,
			'posts_per_page'      => -1,
			'fields'              => 'ids',
			'tax_query'           => array()
		);

		$loop = new WP_Query($args);
		$data['total'] = $loop->found_posts;
	
		$args['posts_per_page'] = $params['length'];
		$args['offset'] = $params['start'];
		$args['wsbp_query'] = true;

		if (!empty($params['search']) && !empty($params['search']['value'])) {
			$args['wsbp_search'] = $params['search']['value'];
		}
		if (!empty($params['order']) && !empty($params['order']['0']['column']) && !empty($params['order']['0']['dir'])) {
			$args['wsbp_order_by'] = ( 1 == $params['order']['0']['column'] ? 'id' : 'post_title' );
			$args['wsbp_order_dir'] = $params['order']['0']['dir'];
		}
		// || !empty($params['order'])
		add_filter('posts_clauses', array($this, 'setClausesProductsOrder'), 10, 2);
		
		$loop = new WP_Query($args);
		
		remove_filter('posts_clauses', array($this, 'setClausesProductsOrder'), 10, 2);
		
		$productCount = $loop->post_count;
		$rows = array();
		if ($productCount > 0) {
			foreach ($loop->posts as $id) {

				$_product = wc_get_product($id);
				$point = get_post_meta($id, $this->productMetaKey, true);

				$rows[] = array(
					'<input type="checkbox" class="wsbpCheckOne" data-id="' . $id . '">', 
					'<div class="wsbp_product_' . $_product->get_type() . '">' . $id . '</div>', 
					$_product->get_name(),
					( empty($point) ? '-' : $point ),
				);
			}
		}
		$data['filtered'] = $loop->found_posts;
		$data['rows'] = $rows;

		return $data;
	}
	public function setClausesProductsOrder( $clauses, $wp_query ) {
		if (!isset($wp_query->query['wsbp_query'])) {
			return $clauses;
		}
		global $wpdb;

		if (isset($wp_query->query['wsbp_search'])) {
			$search = $wp_query->query['wsbp_search'];
			$isId = is_numeric($search);
			$clauses['where'] .= ' AND ( ' . $wpdb->prepare("%1s %2s '%3s'", "{$wpdb->posts}.post_title", 'LIKE', '%' . $search . '%') . ( $isId ? " OR {$wpdb->posts}.ID=" . ( (int) $search ) : '' ) . ')';

			unset($wp_query->query['wsbp_search']);
		}
		if (isset($wp_query->query['wsbp_order_by'])) {
			$clauses['orderby'] =  "{$wpdb->posts}." . $wp_query->query['wsbp_order_by'] . ' ' . $wp_query->query['wsbp_order_dir'];
			unset($wp_query->query['wsbp_order_by']);
			unset($wp_query->query['wsbp_order_dir']);
		}
		//
		return $clauses;
	}
	
	public function setProductPoints( $ids, &$point ) {
		$point = $this->sanitizeBonusPoint($point);
		if (is_array($ids) && !empty($ids)) {
			foreach ($ids as $id) {
				$this->setProductMetaBP($id, $point);
			}
			$this->getModule()->getModel('products')->recalcProductsPoints(count($ids) == 1 ? $ids[0] : 0);
		}
		return true;
	}
	
	public function createProductBPFields() {
		$args = array(
			'id' => $this->productMetaKey,
			'label' => __('Points per purchase', 'wupsales-reward-points'),
			'class' => 'wsbp-custom-field short',
			'wrapper_class' => 'wsbp-custom-field-wrapper',
		);
		woocommerce_wp_text_input($args);
	}
	
	public function createProductBPFieldsVariation( $loop, $variation_data, $variation ) {
		$field = $this->productMetaKey;
		$args = array(
			'id' => $field . '[' . $variation->ID . ']',
			'label' => __('Points per purchase', 'wupsales-reward-points'),
			'class' => 'wsbp-custom-field',
			'wrapper_class' => 'wsbp-custom-field-wrapper form-row',
			'value' => get_post_meta($variation->ID, $field, true),
		);
		woocommerce_wp_text_input($args);
	}

	public function saveProductBPFields( $postId ) {
		$points = ReqWsbp::getVar($this->productMetaKey, 'post', '');
		if (!is_array($points)) {
			$this->setProductMetaBP($postId, $this->sanitizeBonusPoint($points));
		}
	}
	
	public function saveProductBPFieldsVariation( $postId ) {
		$points = ReqWsbp::getVar($this->productMetaKey, 'post', array());
		if (is_array($points) && isset($points[$postId])) {
			$this->setProductMetaBP($postId, $this->sanitizeBonusPoint($points[$postId]));
		}
	}
	
	public function sanitizeBonusPoint( $point ) {
		if (empty($point) || is_array($point)) {
			return '';
		}

		$withPercent = strpos($point, '%') !== false;
		if ($withPercent) {
			$point = str_replace('%', '', $point);
		}
		$point = floatval(str_replace(',', '.', $point));
		return empty($point) ? '' : sanitize_text_field($point . ( $withPercent ? '%' : '' ));
	}
	
	public function setProductMetaBP( $id, $point ) {
		//$point = $this->sanitizeBonusPoint($point);
		if ('' == $point) {
			delete_post_meta($id, $this->productMetaKey);
		} else {
			update_post_meta($id, $this->productMetaKey, $point);
		}
	}
	
	public function doDiscountCompleted( $orderId, $user ) {
		$order = wc_get_order( $orderId );
		$coupons = $order->get_items('coupon');
		$module = $this->getModule();
		foreach ($coupons as $coupon) {
			if ($coupon->get_code() == $module->bonusCoupon) {
				$amount = DispatcherWsbp::applyFilters('getDiscountPointsAmount', $coupon->get_discount());
				if ($user['points'] < $amount) {
					$amount = $user['points'];
				}
				//$amount = DispatcherWsbp::applyFilters('getDiscountPointsAmount', $amount) * ( -1 );
				$amount = $amount * ( -1 );
				if ($amount < 0) {
					$userId = $user['id'];
					$transactions = $module->getModel('transactions');
					$data = array('user_id' => $userId, 'op_id' => $orderId, 'tr_type' => 3);
					//$tran = $transactions->setWhere($data)->getFromTbl();
					if (!empty($data)) {
						$data['points'] = $amount;
						$transactions->insertTransaction($data);
					}
				}
				break;
			}
		}
		FrameWsbp::_()->getModule('actions')->getModel('users')->updateById(array('cart' => 0), $user['id']);
	}
	
	public function addPointsForPurchase( $order ) {
		$userId = $order->get_customer_id();
		$module = $this->getModule();
		if (!$module->isActiveUser($userId)) {
			return;
		}
		$userParams = $module->getUserParams($userId);
		if (!$userParams || UtilsWsbp::getArrayValue($userParams, 'status', 0, 1) != 1) {
			return;
		}
		
		$productModel = $module->getModel('products');
		$mainOptions =  $module->getMainOptions();
		$products = $order->get_items();
		$pointTotal = 0;
		$details = array();
		$purCur = $order->get_currency();
		$wsbpCur = UtilsWsbp::getArrayValue($mainOptions, 'currency', $purCur);
		$expiryDate = UtilsWsbp::getArrayValue($mainOptions, 'expiry_date', 0, 1);
		$expDate = UtilsWsbp::addDays((int) $expiryDate, false);
		$logicExpiry = UtilsWsbp::getArrayValue($mainOptions, 'logic_expiry', 0, 1);
		foreach ($products as $product) {
			$productId = $product->get_product_id();
			$variationId = $product->get_variation_id();
			$quantity = $product->get_quantity();
			$subtotal = $product->get_subtotal();
			$prId = empty($variationId) ? $productId : $variationId;
			$prPoints = $productModel->getById($prId); //getProductPoints($productId, $variationId);
			if ($prPoints) {
				$point = $prPoints['points'];
				$points = $point ? $point * $quantity : 0;
				if ($points) {
					$pointTotal += $points;
					$details[] = array('source' => 1, 'source_id' => $prId, 'pur_sum' => $subtotal, 'pur_cur' => $purCur, 'pur_cnt' => $quantity, 'points' => $points, 'conditions' => array('product' => $prPoints, 'options' => $mainOptions));
				}
			}
		}
		$details = DispatcherWsbp::applyFilters('addPointsForPurchase', $details, $pointTotal, $order);
		if (count($details) > 0) {
			$pointTotal = 0;
			foreach ($details as $det) {
				$pointTotal += $det['points'];
			}
			if ($pointTotal < 0) {
				$pointTotal = 0;
			}
			
			$orderId = $order->get_id();
			$data = array('user_id' => $userId, 'op_id' => $orderId, 'tr_type' => 2, 'points' => $pointTotal, 'status' => 0, 'exp_date' => $expDate);
			$module->getModel('transactions')->insertTransaction($data, $details);
			if (empty($logicExpiry)) {
				$curExpDate = $module->getModel('transactions')->getExpiryDate($userId);
				$module->getModel('transactions')->addExpiryDates($userId, $expDate);
				if (!empty($curExpDate) && !is_null($curExpDate)) {  
					$order->update_meta_data($this->orderMetaExpiryKey, $curExpDate);
					$order->save();
					//update_post_meta($orderId, $this->orderMetaExpiryKey, $curExpDate);
				}
			}
		}
	}
	
	public function deletePointsForPurchase( $order, $isRefund ) {
		$module = $this->getModule();
		$mainOptions =  $module->getMainOptions();
		$refundType = UtilsWsbp::getArrayValue($mainOptions, 'refund_type', 0, 1);
		if ($isRefund && 2 == $refundType) {
			return;
		}
		$userId = $order->get_customer_id();
		
		$userParams = $module->getUserParams($userId);
		if (!$userParams) {
			return;
		}
		
		$orderId = $order->get_id();
		$tranModel = $module->getModel('transactions');
		$detModel = $module->getModel('details');
		$trans = $tranModel->getOrderTransactions($userId, $orderId);
		if (empty($trans)) {
			return;
		}
		$discount = 0;
		$points = 0;
		$created = false;
		foreach ($trans as $tran) {
			$typ = $tran['tr_type'];
			$trId = $tran['id'];
			$status = $tran['status'];
			if (3 == $typ) {
				//return discount
				if (6 == $status) { //if completed
					$details = $detModel->getTransactionDetails($trId);
					if ($details) {
						foreach ($details as $det) {
							$p = $det['points'];
							if ($p < 0 && 0 == $det['source']) {
								$credit = is_null($det['conditions']) ? false : UtilsWsbp::jsonDecode($det['conditions']);
								$expDate = $credit ? UtilsWsbp::getArrayValue($credit, 'exp_date', false, 1) : false;
								$discount += $tranModel->returnPoints($det['source_id'], abs($p), $expDate);
							}
						}
					}
					$tranModel->updateById(array('status' => $isRefund ? 7 : 8), $trId); //set refund/canceled
				}
			} else if (2 == $typ) {
				//deleted points
				$points += $tran['points'] - $tran['rest'];
				$tranModel->updateById(array('status' => 7), $trId);
				$created = $tran['created'];
			}
		}
		if ($created) {
			//$expDate = get_post_meta($orderId, $this->orderMetaExpiryKey, true);
			$expDate = $order->get_meta($this->orderMetaExpiryKey, true);
			if ($expDate && is_numeric($expDate)) {
				$module->getModel('transactions')->returnExpiryDates($userId, $expDate, $created);
			}
		}
		
		if ($points > 0) {
			// write off rest points
			$data = array('user_id' => $userId, 'op_id' => $orderId, 'tr_type' => 4, 'points' => ( $points * ( -1 ) ), 'status' => 0);
			$module->getModel('transactions')->insertTransaction($data);
		} else {
			FrameWsbp::_()->getModule('actions')->getModel('users')->doRecalcUsersParams($userId, '');
		}
		
		return;
	}
}
