<?php
class UsersModelWsbp extends ModelWsbp {
	public $startRecalcOptionKey = 'wsbp_start_recalc';
	public $recalcUsersLockLimit = 20;

	public function __construct() {
		$this->_setTbl('users');
		$lists = array(
			'status' => array(
				0 => __('Refused', 'wupsales-reward-points'),
				1 => __('Active', 'wupsales-reward-points'),
				2 => __('Blocked', 'wupsales-reward-points'),
			)
		);
		$this->setFieldLists($lists);
	}
	public function getUsersListQuery( $params, $withLimit, $onlyIds = false, $oneQuery = true ) {
		global $wpdb;
		$query = array('select' => 'SELECT' . ( $withLimit ? ' SQL_CALC_FOUND_ROWS' : '' ) . ' u.ID');
		$query['from'] = ' FROM `#__users` as u LEFT JOIN `@__users` wu ON (wu.id=u.ID)';
		$query['join'] = '';
		$query['where'] = ' WHERE 1=1';
		if (!$onlyIds) {
			$query['select'] .= ', u.display_name, wu.status, u.user_email, wu.points, role_meta.meta_value as user_role';
			$query['from'] .= " LEFT JOIN `#__usermeta` as role_meta ON (role_meta.user_id=u.ID and role_meta.meta_key='{$wpdb->prefix}capabilities')";
		}
		
		if (!empty($params['search']) && !empty($params['search']['value'])) {
			$search = $params['search']['value'];
			$isId = is_numeric($search);
			$query['where'] .= ' AND (' . $wpdb->prepare( "%1s %2s '%3s'", 'u.display_name', 'LIKE', '%' . $search . '%' ) . ( $isId ? ' OR u.ID=' . ( (int) $search ) : '' ) . ')';
		}
		
		if (!empty($params['filters']) && !empty($params['filters']['filters'])) {
			$filters = $params['filters'];
			$prefix = $wpdb->prefix;
			$logicAnd = UtilsWsbp::getArrayValue($filters, 'filter_logic', 'and') == 'and';
			$dateFormat = UtilsWsbp::getArrayValue($filters, 'date_format');
			$filters = UtilsWsbp::getArrayValue($filters, 'filters', array(), 2);
			$where = '';
			$andOr = ' ' . ( $logicAnd ? 'AND' : 'OR' ) . ' ';
			$i = UtilsWsbp::getArrayValue($params, 'iterator', 0, 1);
			$dbDateFormat = 'Y-m-d';
			$isHPOS = UtilsWsbp::isHPOS();
			foreach ($filters as $filter) {
				$i++;
				$metaTable = 'wsbp_filter' . $i;
				$typ = UtilsWsbp::getArrayValue($filter, 'type', false);
				switch ($typ) {
					case 'role':
						$roles = UtilsWsbp::getArrayValue($filter, 'roles', array(), 2);
						if (!empty($roles)) {
							$query['join'] .= " LEFT JOIN `#__usermeta` as $metaTable ON u.ID=$metaTable.user_id and $metaTable.meta_key='{$prefix}capabilities'";
							$w = '';
							foreach ($roles as $r) {
								$w .= ( empty($w) ? '' : ' OR ' ) . $wpdb->prepare( "%1s %2s '%3s'", $metaTable . '.meta_value', 'LIKE', '%' . $r . '%' );
							}
							$where .= ( empty($where) ? '' : $andOr ) . '(' . $w . ')';
						}
						break;
					case 'registr':
						$minDate = UtilsWsbp::convertDateFormat(UtilsWsbp::getArrayValue($filter, 'min_date'), $dateFormat);
						$maxDate = UtilsWsbp::convertDateFormat(UtilsWsbp::getArrayValue($filter, 'max_date'), $dateFormat);
						if (!empty($minDate) || !empty($maxDate)) {
							$where .= ( empty($where) ? '' : $andOr ) .
								'(' . ( !empty($minDate) ? "u.user_registered>='" . $minDate . " 00:00:00'" : '' ) .
									( !empty($minDate) && !empty($maxDate) ? ' AND ' : '' ) .
									( !empty($maxDate) ? "u.user_registered<='" . $maxDate . " 23:59:59'" : '' ) . ')';
						}
						break;
					case 'age':
						$minAge = UtilsWsbp::getArrayValue($filter, 'min_age', 0, 1);
						$maxAge = UtilsWsbp::getArrayValue($filter, 'max_age', 0, 1);
						$minAge = empty($minAge) ? false : UtilsWsbp::addDays(0, false, $minAge * ( -1 ));
						$maxAge = empty($maxAge) ? false : UtilsWsbp::addDays(0, false, ( $maxAge + 1 ) * ( -1 ));
						$where .= ( empty($where) ? '' : $andOr ) . '(wu.birthday>' . ( empty($maxAge) ? 0 : $maxAge );
						if (!empty($minAge)) {
							$where .= ' AND wu.birthday<=' . $minAge;
						}
						$where .= ')';
						break;
					case 'birthday':
						$where .= ( empty($where) ? '' : $andOr ) . "(wu.bd='" . UtilsWsbp::getFormatedDateTime(UtilsWsbp::getTimestamp(), 'm-d') . "'";
						if (UtilsWsbp::getArrayValue($filter, 'exclude_change', 0, 1) == 1) {
							$where .= " AND (wu.bd_updated is NULL OR wu.bd_updated<'" . UtilsWsbp::addDays(-5, 'Y-m-d') . "')";
						}
						$where .= ')';
						break;
					case 'amount':
						$minAmount = UtilsWsbp::getArrayValue($filter, 'min_amount', '', 1, false, true);
						$maxAmount = UtilsWsbp::getArrayValue($filter, 'max_amount', '', 1, false, true);
						$isMin = ( '' !== $minAmount );
						$isMax = ( '' !== $maxAmount );
						if ($isMin || $isMax) {
							$where .= ( empty($where) ? '' : $andOr ) .
								'(' . ( $isMin ? 'wu.total_amount>=' . $minAmount : '' ) .
									( $isMin && $isMax ? ' AND ' : '' ) .
									( $isMax ? 'wu.total_amount<=' . $maxAmount : '' ) . ')';
						}
						break;
					case 'count':
						$minCount = UtilsWsbp::getArrayValue($filter, 'min_count', '', 1, false, true);
						$maxCount = UtilsWsbp::getArrayValue($filter, 'max_count', '', 1, false, true);
						$isMin = ( '' !== $minCount );
						$isMax = ( '' !== $maxCount );
						if ($isMin || $isMax) {
							$where .= ( empty($where) ? '' : $andOr ) .
								'(' . ( $isMin ? 'wu.total_count>=' . $minCount : '' ) .
									( $isMin && $isMax ? ' AND ' : '' ) .
									( $isMax ? 'wu.total_count<=' . $maxCount : '' ) . ')';
						}
						break;
					case 'order':
						$minDate = UtilsWsbp::convertDateFormat(UtilsWsbp::getArrayValue($filter, 'min_date'), $dateFormat);
						$maxDate = UtilsWsbp::convertDateFormat(UtilsWsbp::getArrayValue($filter, 'max_date'), $dateFormat);
						$without = UtilsWsbp::getArrayValue($filter, 'without_order', false);
						$w = '';
						if (!empty($minDate) || !empty($maxDate)) {
							$w .= '(' . ( !empty($minDate) ? "wu.last_order>='" . $minDate . " 00:00:00'" : '' ) .
									( !empty($minDate) && !empty($maxDate) ? ' AND ' : '' ) .
									( !empty($maxDate) ? "wu.last_order<='" . $maxDate . " 23:59:59'" : '' ) . ')';
						}
						if ($without) {
							$w .= ( empty($w) ? '' : ' OR ' ) . 'wu.last_order is NULL';
						}
						if (!empty($w)) {
							$where .= ( empty($where) ? '' : $andOr ) . '(' . $w . ')';
						}
						break;
					case 'dead':
						$minDays = UtilsWsbp::getArrayValue($filter, 'min_days', '', 1, false, true);
						$maxDays = UtilsWsbp::getArrayValue($filter, 'max_days', '', 1, false, true);
						$maxDate = ( '' === $minDays || $minDays < 0 ? false : UtilsWsbp::addDays($minDays * ( -1 ), $dbDateFormat) );
						$minDate = ( '' === $maxDays || $minDays < 0 ? false : UtilsWsbp::addDays($maxDays * ( -1 ), $dbDateFormat) );
						$without = UtilsWsbp::getArrayValue($filter, 'without_order', false);
						$w = '';
						if (!empty($minDate) || !empty($maxDate)) {
							$w .= '(' . ( !empty($minDate) ? "wu.last_order>='" . $minDate . " 00:00:00'" : '' ) .
									( !empty($minDate) && !empty($maxDate) ? ' AND ' : '' ) .
									( !empty($maxDate) ? "wu.last_order<='" . $maxDate . " 23:59:59'" : '' ) . ')';
						}
						if ($without) {
							$w .= ( empty($w) ? '' : ' OR ' ) . 'wu.last_order is NULL';
						}
						if (!empty($w)) {
							$where .= ( empty($where) ? '' : $andOr ) . '(' . $w . ')';
						}
						break;
					case 'active':
						$minDate = UtilsWsbp::convertDateFormat(UtilsWsbp::getArrayValue($filter, 'min_date'), $dateFormat);
						$maxDate = UtilsWsbp::convertDateFormat(UtilsWsbp::getArrayValue($filter, 'max_date'), $dateFormat);
						if (!empty($minDate) || !empty($maxDate)) {
							$query['join'] .= " LEFT JOIN `#__usermeta` as $metaTable ON u.ID=$metaTable.user_id and $metaTable.meta_key='wc_last_active'";
							
							$where .= ( empty($where) ? '' : $andOr ) . '(';
							if (!empty($minDate)) {
								$date = new DateTime($minDate);
								$timestamp = $date->getTimestamp();
								$where .= $metaTable . '.meta_value+0>=' . $timestamp;
							}
							$where .= ( !empty($minDate) && !empty($maxDate) ? ' AND ' : '' );
							if (!empty($maxDate)) {
								$date = new DateTime($maxDate);
								$timestamp = $date->getTimestamp();
								$where .= $metaTable . '.meta_value+0<=' . $timestamp;
							}
							$where .= ')';
						}
						break;
					case 'review':
						$where .= ( empty($where) ? '' : $andOr ) . 'EXISTS(' .
							'SELECT 1 FROM `#__comments` as c' .
							" WHERE c.comment_type='review' AND c.user_id=u.ID" .
							' LIMIT 1)';
						break;
					case 'category':
						$categories = UtilsWsbp::getArrayValue($filter, 'category', array(), 2);
						if (!empty($categories)) {
							$categories = UtilsWsbp::controlNumericValues($categories);
							if (!empty($categories)) {
								if (UtilsWsbp::getArrayValue($filter, 'with_child', false)) {
									$cats = array();
									foreach ($categories as $cat) {
										$cats[] = $cat;
										$cats = array_merge($cats, get_term_children($cat, 'product_cat'));
									}
									$categories = $cats;
								}
							
								$where .= ( empty($where) ? '' : $andOr ) . 'EXISTS(';
								if ($isHPOS) {
									$where .= 'SELECT 1 FROM `#__wc_orders` as p' .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON (i.order_id=p.ID)" .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON (im.order_item_id=i.order_item_id AND im.meta_key='_product_id')" .
										" INNER JOIN {$wpdb->term_relationships} AS tr ON (tr.object_id=im.meta_value)" . 
										" INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id=tt.term_taxonomy_id)" .
										" WHERE p.type='shop_order' AND p.status='wc-completed' AND p.customer_id=u.ID AND tt.term_id IN (" . implode(',', $categories) . ')';
								
								} else {
									$where .= 'SELECT 1 FROM `#__posts` as p' .
										" INNER JOIN `#__postmeta` as mu ON (mu.post_id=p.id AND mu.meta_key='_customer_user')" .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON (i.order_id=p.ID)" .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON (im.order_item_id=i.order_item_id AND im.meta_key='_product_id')" .
										" INNER JOIN {$wpdb->term_relationships} AS tr ON (tr.object_id=im.meta_value)" . 
										" INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id=tt.term_taxonomy_id)" .
										" WHERE p.post_type='shop_order' AND p.post_status='wc-completed' AND mu.meta_value=u.ID AND tt.term_id IN (" . implode(',', $categories) . ')';
								}
								$where .= ' LIMIT 1)';
							}
						}
						break;
					case 'attribute':
						$attribute = UtilsWsbp::getArrayValue($filter, 'attribute');
						$values = UtilsWsbp::getArrayValue($filter, 'value', array(), 2);
						if (!empty($attribute)) {
							if (!empty($values)) {
								$values = UtilsWsbp::controlNumericValues($values);
							}
							$labels = array();
							if (!empty($values)) {
								foreach ($values as $termId) {
									$term = get_term($termId);
									if ($term) {
										$labels[] = $term->name;
									}
								}
							}
							$where .= ( empty($where) ? '' : $andOr ) . 'EXISTS(';
							if ($isHPOS) {
								$where .= 'SELECT 1 FROM `#__wc_orders` as p' .
									" INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON (i.order_id=p.ID)" .
									" INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON (im.order_item_id=i.order_item_id AND im.meta_key='_product_id')" .
									" INNER JOIN {$wpdb->term_relationships} AS tr ON (tr.object_id=im.meta_value)" . 
									" INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id=tt.term_taxonomy_id)" .
									" LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS iv ON (iv.order_item_id=i.order_item_id AND iv.meta_key='_variation_id')" .
									" LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS ia ON (ia.order_item_id=i.order_item_id AND ia.meta_key='" . $attribute . "')" .
									" WHERE p.type='shop_order' AND p.status='wc-completed' AND p.customer_id=u.ID AND " .
									( empty($values) ? "tt.taxonomy='" . $attribute . "'" : 
										'IF(iv.meta_value+0=0, tt.term_id IN (' . implode(',', $values) . "), ia.meta_value IN ('" . implode("','", $labels) . "'))" );
							} else {
								$where .= 'SELECT 1 FROM `#__posts` as p' .
									" INNER JOIN `#__postmeta` as mu ON (mu.post_id=p.id AND mu.meta_key='_customer_user')" .
									" INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON (i.order_id=p.ID)" .
									" INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON (im.order_item_id=i.order_item_id AND im.meta_key='_product_id')" .
									" INNER JOIN {$wpdb->term_relationships} AS tr ON (tr.object_id=im.meta_value)" . 
									" INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id=tt.term_taxonomy_id)" .
									" LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS iv ON (iv.order_item_id=i.order_item_id AND iv.meta_key='_variation_id')" .
									" LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS ia ON (ia.order_item_id=i.order_item_id AND ia.meta_key='" . $attribute . "')" .
									" WHERE p.post_type='shop_order' AND p.post_status='wc-completed' AND mu.meta_value=u.ID AND " .
									( empty($values) ? "tt.taxonomy='" . $attribute . "'" : 
										'IF(iv.meta_value+0=0, tt.term_id IN (' . implode(',', $values) . "), ia.meta_value IN ('" . implode("','", $labels) . "'))" );
							}
							$where .= ' LIMIT 1)';
						}
						break;
					case 'tag':
						$tags = UtilsWsbp::getArrayValue($filter, 'tag', array(), 2);
						if (!empty($tags)) {
							$tags = UtilsWsbp::controlNumericValues($tags);
							if (!empty($tags)) {
								$where .= ( empty($where) ? '' : $andOr ) . 'EXISTS(';
								if ($isHPOS) {
									$where .= 'SELECT 1 FROM `#__wc_orders` as p' .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON (i.order_id=p.ID)" .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON (im.order_item_id=i.order_item_id AND im.meta_key='_product_id')" .
										" INNER JOIN {$wpdb->term_relationships} AS tr ON (tr.object_id=im.meta_value)" . 
										" INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id=tt.term_taxonomy_id)" .
										" WHERE p.type='shop_order' AND p.status='wc-completed' AND p.customer_id=u.ID AND tt.term_id IN (" . implode(',', $tags) . ')';
								} else {
									$where .= 'SELECT 1 FROM `#__posts` as p' .
										" INNER JOIN `#__postmeta` as mu ON (mu.post_id=p.id AND mu.meta_key='_customer_user')" .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON (i.order_id=p.ID)" .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON (im.order_item_id=i.order_item_id AND im.meta_key='_product_id')" .
										" INNER JOIN {$wpdb->term_relationships} AS tr ON (tr.object_id=im.meta_value)" . 
										" INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id=tt.term_taxonomy_id)" .
										" WHERE p.post_type='shop_order' AND p.post_status='wc-completed' AND mu.meta_value=u.ID AND tt.term_id IN (" . implode(',', $tags) . ')';
								}
								$where .= ' LIMIT 1)';
							}
						}
						break;
					case 'product':
						$products = UtilsWsbp::getArrayValue($filter, 'product');
						if (!empty($products)) {
							if (!is_array($products)) {
								$products = explode(',', $products);
							}
							$products = UtilsWsbp::controlNumericValues($products);
							if (!empty($products)) {
								$where .= ( empty($where) ? '' : $andOr ) . 'EXISTS(';
								if ($isHPOS) {
									$where .= 'SELECT 1 FROM `#__wc_orders` as p' .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON (i.order_id=p.ID)" .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON (im.order_item_id=i.order_item_id AND im.meta_key='_product_id')" .
										" WHERE p.type='shop_order' AND p.status='wc-completed' AND p.customer_id=u.ID AND im.meta_value IN (" . implode(',', $products) . ')';
								
								} else {
									$where .= 'SELECT 1 FROM `#__posts` as p' .
										" INNER JOIN `#__postmeta` as mu ON (mu.post_id=p.id AND mu.meta_key='_customer_user')" .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON (i.order_id=p.ID)" .
										" INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON (im.order_item_id=i.order_item_id AND im.meta_key='_product_id')" .
										" WHERE p.post_type='shop_order' AND p.post_status='wc-completed' AND mu.meta_value=u.ID AND im.meta_value IN (" . implode(',', $products) . ')';
								}
								$where .= ' LIMIT 1)';
							}
						}
						break;
					default:
						break;
				}
			}
			if (!empty($where)) {
				$query['where'] .= ' AND (' . $where . ')';
			}
		}
		return $oneQuery ? $query['select'] . $query['from'] . $query['join'] . $query['where'] : $query;
	}
		
	public function getUsersList( $params ) {
		$data = array();
		
		$userCount = count_users();
		$totalCount = $userCount['total_users'];
		
		$len = UtilsWsbp::getArrayValue($params, 'length', -1, 1);
		$start = UtilsWsbp::getArrayValue($params, 'start', 0, 1);
		
		$withLimit = ( $len > 0 );
		
		$query = $this->getUsersListQuery($params, $withLimit);
		
		$order = ' ORDER BY ';
		if (!empty($params['order']) && !empty($params['order']['0']['column']) && !empty($params['order']['0']['dir'])) {
			switch ($params['order']['0']['column']) {
				case 2: 
					$order .= 'u.display_name';
					break;
				case 3: 
					$order .= 'user_role';
					break;
				case 4: 
					$order .= 'user_email';
					break;
				case 5: 
					$order .= ' wu.points';
					break;
				default: 
					$order .= ' u.ID';
					break;
			}
			$order .= ' ' . $params['order']['0']['dir'];
		}
		$query .= $order;
		
		if ($len > 0) {
			if ($start >= $totalCount) {
				$start = 0;
			}
			$query .= ' LIMIT ' . $start . ',' . $len;
		}
		$users = DbWsbp::get($query);
		$filtered = 0;
		$rows = array();
		if ($users && !empty($users)) {
			$filtered = DbWsbp::get('SELECT FOUND_ROWS()', 'one');
			$btnLock = __('Are you sure to block this user?', 'wupsales-reward-points') . '<div class="buttons"><button>' . __('Cancel', 'wupsales-reward-points') . '</button><button class="wsbp-lock">' . __('Confirm', 'wupsales-reward-points') . '</button></div>';
			$btnUnlock = __('Are you sure to unlock this user?', 'wupsales-reward-points') . '<div class="buttons"><button>' . __('Cancel', 'wupsales-reward-points') . '</button><button class="wsbp-unlock">' . __('Confirm', 'wupsales-reward-points') . '</button></div>';
			
			foreach ($users as $user) {
				$id = $user['ID'];

				$point = $user['points'];
				$lock = ( 2 == $user['status'] );
				$refused = ( 0 == $user['status'] );
				$roles = unserialize($user['user_role']);
				$r = '';
				if (is_array($roles)) {
					foreach ($roles as $role => $f) {
						if ($f && strpos($role, 'wpml_') === false) {
							$r .= $role . ', ';
						}
					}
					$r = substr($r, 0, -2);
				} else {
					$r = $roles;
				}

				$rows[] = array(
					'<input type="checkbox" class="wsbpCheckOne" data-id="' . $id . '">', 
					$id, 
					$user['display_name'] . 
					( $refused ? '<div class="wsbp-user-refused">' . esc_html__('Refused', 'wupsales-reward-points') . '</div>' : ( $lock ? '<div class="wsbp-user-blocked">' . esc_html__('Blocked', 'wupsales-reward-points') . '</div>' : '' ) ),
					$r,
					$user['user_email'],
					( empty($point) || '0.00' == $point ? '-' : $point ),
					'<div class="wupsales-list-actions" data-id="' . $id . '"><i class="fa fa-fw fa-plus-circle wsbp-add wupsales-tooltip" title="' . esc_attr__('Add point', 'wupsales-reward-points') .
					'"></i><i class="fa fa-fw fa-minus-circle wsbp-delete wupsales-tooltip" title="' . esc_attr__('Delete point', 'wupsales-reward-points') .
					'"></i><i class="fa fa-fw fa-info wsbp-info wupsales-tooltip" title="' . esc_attr__('More info', 'wupsales-reward-points') .
					( $refused ? '' 
						: ( $lock
							? '"></i><i class="fa fa-fw fa-unlock wupsales-tooltip wsbp-unlock" title="' . esc_attr($btnUnlock . '<div class="wupsales-hidden">' . $id . '</div>')
							: '"></i><i class="fa fa-fw fa-lock wupsales-tooltip wsbp-lock" title="' . esc_attr($btnLock . '<div class="wupsales-hidden">' . $id . '</div>') ) ) .
					'"></i></div>',
				);
			}
		}

		$data['total'] = $totalCount;
		$data['filtered'] = $filtered;
		$data['rows'] = $rows;

		return $data;
	}
	
	
	public function lockUser( $id, $lock ) {
		$id = (int) $id;
		if (empty($id)) {
			return false;
		}
		$user = $this->getUserParams($id);
		if ($user) {
			$this->updateById(array('status' => ( 'lock' == $lock ? 2 : 1 ) ), $id);
		}
		return true;
	}
	
	public function addUserCartPointsDiscount( $user, $points ) {
		if (!is_numeric($points)) {
			$points = 0;
		}
		$id = $user['id'];
		if ($user['points'] < $points) {
			$points = $user['points'];
		}
				
		$this->updateById(array('cart' => $points), $id);

		return true;
	}
	
	public function recalcUsersParams( $userId = 0, $tempTable = false ) {
		if (FrameWsbp::_()->getModule('bonuses')->getModel('transactions')->controlExpired($userId)) {
			$result = $this->doRecalcUsersParams($userId, $tempTable);
		}
		if (!$result) {
			FrameWsbp::_()->saveDebugLogging();
		}
		return $result;
	}
	
	public function doRecalcUsersParams( $userId, $usersTable ) {
		if (!empty($userId) && !is_numeric($userId)) {
			return false;
		}
		$isAllUsers = empty($userId);
		$isOneUser = !$isAllUsers && is_numeric($userId);
		
		$fullRecalc = $isAllUsers && empty($usersTable);
		$startRecalc = get_option($this->startRecalcOptionKey);

		if ($fullRecalc && !empty($startRecalc)) {
			if (microtime(true) - $startRecalc <= $this->recalcUsersLockLimit * 60) {
				FrameWsbp::_()->pushError('Wait. The calculation is already running ...');
				return false;
			}
		}
		if ($fullRecalc) {
			update_option($this->startRecalcOptionKey, UtilsWsbp::getTimestamp());
		}

		$defParams = array('last_order' => null, 'total_count' => 0, 'total_amount' => 0);
		$isHPOS = UtilsWsbp::isHPOS();
		if ($isHPOS) {
			$query = 'SELECT' . ( $isOneUser ? '' : ' p.customer_id as id,' ) . 
				' max(p.date_created_gmt) as last_order, count(DISTINCT p.id) as total_count, sum(p.total_amount) as total_amount' .
				' FROM `#__wc_orders` as p' .
				( $usersTable && !$isOneUser ? ' INNER JOIN ' . $usersTable . ' as t ON (t.id=p.customer_id)' : '' ) .
				" WHERE p.type='shop_order' AND p.status='wc-completed'";
		} else {
			$query = 'SELECT' . ( $isOneUser ? '' : ' mu.meta_value+0 as id,' ) . 
				' max(p.post_date) as last_order, count(DISTINCT p.id) as total_count, sum(mo.meta_value) as total_amount' .
				' FROM `#__posts` as p' .
				" INNER JOIN `#__postmeta` as mu ON (mu.post_id=p.id AND mu.meta_key='_customer_user')" .
				( $usersTable && !$isOneUser ? ' INNER JOIN ' . $usersTable . ' as t ON (t.id=mu.meta_value)' : '' ) .
				" INNER JOIN `#__postmeta` as mo ON (mo.post_id=p.id AND mo.meta_key='_order_total')" .
				" WHERE p.post_type='shop_order' AND p.post_status='wc-completed'";
		}
					
		if ($isOneUser) {
			$userId = (int) $userId;
			$query .= ' AND ' . ( $isHPOS ? 'p.customer_id' : 'mu.meta_value' ) . '=' . $userId;
			$data = DbWsbp::get($query, 'row');
			$user = $this->getUserParams($userId);
			if ($user) {
				if (empty($data) || is_null($data)) {
					$data = $defParams;
				}
				$update = array();
				foreach ($data as $field => $value) {
					if (isset($user[$field]) && $user[$field] != $value) {
						$update[$field] = $value;
					}
				}
				$queryPoints = 'SELECT sum(rest) FROM `@__transactions` WHERE status=0 AND user_id=' . $userId;
				$points = DbWsbp::get($queryPoints, 'one');
				$update['points'] = $points ? $points : 0;
				$update['calculated'] = $points ? $points : 0;
				if (!empty($update)) {
					$this->updateById($update, $userId);
				}
			}
		} else {
			if (!$isAllUsers) {
				$userList = implode(',', UtilsWsbp::controlNumericValues($userId));
				$query .= ' AND ' . ( $isHPOS ? 'p.customer_id' : 'mu.meta_value' ) . ' IN (' . $userList . ')';
			}
			$query .= ' GROUP BY ' . ( $isHPOS ? 'p.customer_id' : 'mu.meta_value' );
			
			$tempTable = DbWsbp::createTemporaryTable('wsbpTempCalc', $query);
			$cnt = DbWsbp::get('select count(*) from ' . $tempTable . ' as t', 'one');
			
			$queryPoints = 'SELECT u.id, sum(t.rest) as points' .
				' FROM ' . ( $usersTable ? $usersTable : '`#__users`' ) . ' as u' .
				' LEFT JOIN `@__transactions` t ON (t.user_id=u.id AND t.status=0)' .
				( $isAllUsers ? '' : ' AND u.id IN (' . $userList . ')' ) .
				' GROUP BY u.id';
			$tempTablePoints = DbWsbp::createTemporaryTable('wsbpTempCalcPoints', $queryPoints);
			$cntPoints = DbWsbp::get('select count(*) from ' . $tempTablePoints . ' as p', 'one');
			
			$update = 'UPDATE `@__users` u' .
				' INNER JOIN ' . $tempTablePoints . ' as p ON (p.id=u.id)' .
				' LEFT JOIN ' . $tempTable . ' as t ON (t.id=u.id)' .
				' SET u.points=IFNULL(p.points, 0),';
			foreach ($defParams as $field => $value) {
				$update .= 'u.' . $field . '=IFNULL(t.' . $field . ',' . ( is_null($value) ? 'NULL' : $value ) . '),';
			}
			$update .= 'calculated=NOW()';
			if (!DbWsbp::query($update)) {
				FrameWsbp::_()->pushError('Error query: ' . $update);
				FrameWsbp::_()->pushError(DbWsbp::getError());
				return false;
			}

			if ($cnt > 0 || $cntPoints > 0) {
				$list = implode(',', array_keys($defParams));
				$insert = 'INSERT IGNORE INTO `@__users` (id,' . $list . ', points)' .
				' SELECT p.id, ' . $list . ', IFNULL(p.points, 0) FROM ' . $tempTablePoints . ' as p' .
				' LEFT JOIN ' . $tempTable . ' as t ON (t.id=p.id)';
				if (!DbWsbp::query($insert)) {
					FrameWsbp::_()->pushError('Error query: ' . $insert);
					FrameWsbp::_()->pushError(DbWsbp::getError());
					return false;
				}
			}
		}

		if ($fullRecalc) {
			update_option($this->startRecalcOptionKey, '');
		}
		return true;
	}
	public function getUserParams( $id ) {
		FrameWsbp::_()->getModule('bonuses')->getModel('transactions')->controlExpired($id, true);
		$user = $this->getById($id);
		if (!$user) {
			if (get_user_by('ID', $id)) {
				$this->insert( array('id' => $id) );
				$user = $this->getById($id);
			}
		}
		return $user ? $user : false;
	}
	public function saveUserBirthday( $id, $birthday ) {
		$id = (int) $id;
		if (empty($id)) {
			return false;
		}
		$birthday = UtilsWsbp::checkDateTime($birthday, FrameWsbp::_()->getModule('bonuses')->getDateFormat());
		if (empty($birthday)) {
			FrameWsbp::_()->pushError(__('The field Date of birth: format error', 'wupsales-reward-points'));
			return false;
		}
		$user = $this->getUserParams($id);
		if ($user && ( null == $user['bd_updated'] || $user['bd_updated'] < UtilsWsbp::addDays(0, 'Y-m-d', -1) )) {
			$update = 'UPDATE `@__users` SET birthday=' . $birthday . ", bd='" . UtilsWsbp::getFormatedDateTime($birthday, 'm-d') . "'" .
				( empty($user['birthday']) ? '' : ',bd_updated=NOW()' ) . 
				' WHERE id=' . $id;
			if (!DbWsbp::query($update)) {
				FrameWsbp::_()->pushError('Error query: ' . $update);
				FrameWsbp::_()->pushError(DbWsbp::getError());
				return false;
			}
		} else {
			FrameWsbp::_()->pushError(__('You can change your age only once/year.', 'wupsales-reward-points'));
			return false;
		}
		return true;
	}
	public function setUserStatus( $id, $status ) {
		$id = (int) $id;
		if (empty($id)) {
			return false;
		}
		$user = $this->getUserParams($id);
		if ($user && 2 != $user['status']) {
			$this->updateById(array('status' => ( 1 == $status ? 1 : 0 ) ), $id);
		}
		return true;
	}
	
	/*
	 * $type = 0 (all), 1 (search)
	*/
	public function getBillingFields( $type = 0 ) {
		$options = FrameWsbp::_()->getModule('bonuses')->getMainOptions();
		
		$fields = UtilsWsbp::getArrayValue($options, 'widget_user_fields');
		$billingFields = array();
		if (!empty($fields)) {
			$ff = explode("\n", $fields);
			foreach ($ff as $f) {
				$parts = explode(':', $f);
				foreach ($parts as $i => $v) {
					$parts[$i] = trim($v);
				}
				$billingFields[] = $parts;
			}
			if (1 == $type) {
				foreach ($billingFields as $i => $data) {
					if (empty($data[2]) || '+' != $data[2]) {
						unset($billingFields[$i]);
					}
				}
			}
		}
		return $billingFields;
	}
	
	public function getWidgetUsersList( $params ) {
		$data = array();
		
		$uRole = FrameWsbp::_()->getModule('bonuses')->getMainOptions('widget_new_user_role');
		
		$len = UtilsWsbp::getArrayValue($params, 'length', -1, 1);
		$start = UtilsWsbp::getArrayValue($params, 'start', 0, 1);
		
		$withLimit = ( $len > 0 );
		$billingFields = $this->getBillingFields(1);
		
		global $wpdb;
		$select = 'SELECT' . ( $withLimit ? ' SQL_CALC_FOUND_ROWS' : '' ) . 
			' u.ID, u.user_login, wu.status, u.user_email, wu.points, f_meta.meta_value as f_name, l_meta.meta_value as l_name';
			
		$from = ' FROM `#__users` as u ' .
			" INNER JOIN `#__usermeta` as role_meta ON (role_meta.user_id=u.ID and role_meta.meta_key='{$wpdb->prefix}capabilities')" .
			' LEFT JOIN `@__users` wu ON (wu.id=u.ID)' .
			" LEFT JOIN `#__usermeta` as f_meta ON (f_meta.user_id=u.ID and f_meta.meta_key='first_name')" .
			" LEFT JOIN `#__usermeta` as l_meta ON (l_meta.user_id=u.ID and l_meta.meta_key='last_name')";
		
		$i = 0;
		foreach ($billingFields as $i => $field) {
			$from .= ' LEFT JOIN `#__usermeta` as meta' . $i . ' ON (meta' . $i . '.user_id=u.ID AND meta' . $i . '.meta_key="' . esc_sql($field[0]) . '")';
			$select .= ', meta' . $i . '.meta_value as addr' . $i;
		}
		$query = $select . $from . 
			' WHERE 1=1 AND ' . $wpdb->prepare( "%1s %2s '%3s'", 'role_meta.meta_value', 'LIKE', '%' . $uRole . '%' );
		if (!empty($params['filters'])) {
			$filters = $params['filters'];
			foreach ($filters as $typ => $value) {
				if (!empty($value)) {
					switch ($typ) {
						case 'user_email': 
							$query .= ' AND ' . $wpdb->prepare( "%1s %2s '%3s'", 'u.user_email', 'LIKE', '%' . $value . '%' );
							break;
						case 'first_name': 
							$query .= ' AND ' . $wpdb->prepare( "%1s %2s '%3s'", 'f_meta.meta_value', 'LIKE', '%' . $value . '%' );
							break;
						case 'last_name': 
							$query .= ' AND ' . $wpdb->prepare( "%1s %2s '%3s'", 'l_meta.meta_value', 'LIKE', '%' . $value . '%' );
							break;
						default:
							foreach ($billingFields as $i => $field) {
								if ($field[0] == $typ) {
									$query .= ' AND ' . $wpdb->prepare( "%1s %2s '%3s'", 'meta' . $i . '.meta_value', 'LIKE', '%' . $value . '%' );
									break;
								}
							}
					}
				}
			}
		}
		
		$order = ' ORDER BY ';
		if (!empty($params['order']) && isset($params['order']['0']['column']) && isset($params['order']['0']['dir'])) {
			switch ($params['order']['0']['column']) {
				case 0: 
					$order .= 'f_name ' . $params['order']['0']['dir'] . ',' . 'l_name';
					break;
				case 1: 
					$order .= 'user_email';
					break;
				case 2: 
					$order .= ' wu.points';
					break;
				default: 
					$order .= ' u.ID';
					break;
			}
			$order .= ' ' . $params['order']['0']['dir'] . ',';
		} 
		$query .= $order . 'u.ID';
		
		if ($len > 0) {
			$query .= ' LIMIT ' . $start . ',' . $len;
		}
		$users = DbWsbp::get($query);
		$filtered = 0;
		$rows = array();
		if ($users && !empty($users)) {
			$filtered = DbWsbp::get('SELECT FOUND_ROWS()', 'one');
			$allowEdit = FrameWsbp::_()->getModule('bonuses')->getMainOptions('e_widget_edit_user') == 1;

			foreach ($users as $user) {
				$id = $user['ID'];

				$point = $user['points'];
				$active = ( 1 == $user['status'] );
				$lock = ( 2 == $user['status'] );
				$refused = ( 0 == $user['status'] );

				$name = $user['f_name'];
				if (empty($name)) {
					$name = '';
				}
				$name .= ( empty($name) ? '' : ' ' ) . ( empty($user['l_name']) ? '' : $user['l_name'] );
				
				$address = array();
				foreach ($billingFields as $i => $field) {
					if (!empty($user['addr' . $i])) {
						$address[] = $user['addr' . $i];
					}
				}
				$rows[] = array(
					'<div class="wsbp-user-name">' . $name . ( ' (' . $user['user_login'] . ') ') . '</div>' .
					( $refused ? '<div class="wsbp-user-refused">' . esc_html__('Refused', 'wupsales-reward-points') . '</div>' : ( $lock ? '<div class="wsbp-user-blocked">' . esc_html__('Blocked', 'wupsales-reward-points') . '</div>' : '' ) ),
					$user['user_email'],
					( empty($point) || '0.00' == $point ? '-' : round($point) ),
					implode (', ' , $address),
					'<div class="wsbp-list-actions wsbp-user-' . ( $active ? 'active' : 'noactive' ) . '" data-id="' . $user['ID'] . '"><i class="fa fa-fw fa-plus-circle wsbp-add" title="' . esc_attr__('Add point', 'wupsales-reward-points') .
					( $allowEdit ? '"></i><i class="fa fa-fw fa-pencil wsbp-edit" title="' . esc_attr__('Edit user', 'wupsales-reward-points') : '' ) .
					'"></i><i class="fa fa-fw fa-info wsbp-info" title="' . esc_attr__('More info', 'wupsales-reward-points') .
					'"></i></div>',
				);

			}
		}

		$data['total'] = $filtered;
		$data['filtered'] = $filtered;
		$data['rows'] = $rows;

		return $data;
		
		
	}

	public function saveNewUser( $userId, $params ) {
		$userId = (int) $userId;
		$fName = UtilsWsbp::getArrayValue($params, 'first_name');
		$lName = UtilsWsbp::getArrayValue($params, 'last_name');
		$email = UtilsWsbp::getArrayValue($params, 'user_email');
		$isNew = empty($userId);
		
		if (empty($fName)) {
			FrameWsbp::_()->pushError(__('The field First Name not filled', 'wupsales-reward-points'));
			return false;
		}
		if (empty($lName)) {
			FrameWsbp::_()->pushError(__('The field Last Name not filled', 'wupsales-reward-points'));
			return false;
		}
		if (empty($email)) {
			FrameWsbp::_()->pushError(__('The field Email Address not filled', 'wupsales-reward-points'));
			return false;
		}
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			FrameWsbp::_()->pushError(__('Email Address is not valid', 'wupsales-reward-points'));
			return false;
		}
		
		if ($isNew) {
			if (email_exists($email)) {
				FrameWsbp::_()->pushError(__('A user with this Email Address already exists', 'wupsales-reward-points'));
				return false;
			}
			
			$parts = explode('@', $email);
			if (count($parts) != 2) {
				FrameWsbp::_()->pushError(__('Email Address is not valid.', 'wupsales-reward-points'));
				return false;
			}
			$username = $parts[0];
			
			$step = 0;
			$found = false;
			while (username_exists($username) != false) {
				$step++;
				if ($step >= 10) {
					$found = true;
					break;
				}
				$username = $parts[0] . '_' . $step;
			}
			if ($found) {
				FrameWsbp::_()->pushError(__('A user with this login already exists', 'wupsales-reward-points'));
				return false;
			}
			
			$password = wp_generate_password();
			$userId = wp_create_user( $username, $password, $email );
			if( is_wp_error($userId) ) {
				FrameWsbp::_()->pushError(__('Error', 'wupsales-reward-points') . ':' . $userId->get_error_message());
				return false;
			}
		} else {
			$foundEmail = email_exists($email);
			if (!empty($foundEmail) && $foundEmail != $userId) {
				FrameWsbp::_()->pushError(__('A user with this Email Address already exists.', 'wupsales-reward-points'));
				return false;
			}
		}
		$user = get_user_by( 'id', $userId );
		if ($isNew) {
			$user->remove_role( 'subscriber' );
			$user->set_role(FrameWsbp::_()->getModule('bonuses')->getMainOptions('widget_new_user_role'));
		}
		
		wp_update_user([
			'ID' => $userId,
			'first_name' => $fName,
			'last_name' => $lName,
			'user_email' => $email
		]);
		
		$billingFields = $this->getBillingFields(0);
		foreach ($billingFields as $data) {
			$field = $data[0];
			$value = UtilsWsbp::getArrayValue($params, $field, null);
			if (!is_null($value)) {
				update_metadata('user', $userId, $field, $value);
			}
		}

		if ($isNew) {
			wp_send_new_user_notifications( $userId );
			$this->recalcUsersParams($userId);
		}

	
		return true;
	}
	public function getUserData( $userId ) {
		$userId = (int) $userId;
		$data = array('id' => $userId);
		$user = get_user_by( 'id', $userId );
		
		if ($user) {
			$data['user_email'] = $user->user_email;
			$meta = get_user_meta($userId);
			if (!empty($meta) && is_array($meta)) {
				$data['first_name'] = empty($meta['first_name']) ? '' : $meta['first_name'][0];
				$data['last_name'] = empty($meta['last_name']) ? '' : $meta['last_name'][0];
				$billingFields = $this->getBillingFields(0);
				foreach ($billingFields as $field) {
					$f = $field[0];
					$data[$f] = empty($meta[$f]) ? '' : $meta[$f][0];
				}
			}
			return $data;
		}
		FrameWsbp::_()->pushError(__('User not found', 'wupsales-reward-points'));
		return false;
	}
}
