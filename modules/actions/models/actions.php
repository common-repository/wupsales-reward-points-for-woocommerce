<?php
class ActionsModelWsbp extends ModelWsbp {
	public $taxList = array('category' => 'product_cat', 'tag' => 'product_tag', 'brand' => 'pwb-brand');
	
	public function __construct() {
		$this->_setTbl('actions');
		$lists = array(
			'tr_type' => array(
				0 => __('Manual', 'wupsales-reward-points'),
				1 => __('Auto', 'wupsales-reward-points'),
			),
			'status' => array(
				0 => __('Wait', 'wupsales-reward-points'),
				1 => __('Completed', 'wupsales-reward-points'),
				2 => __('Active', 'wupsales-reward-points'),
				8 => __('Stopped', 'wupsales-reward-points'),
				9 => __('Deleted', 'wupsales-reward-points'),
			)
		);
		$this->setFieldLists($lists);
	}
	
	public function getReasonList() {
		$options = FrameWsbp::_()->getModule('bonuses')->getMainOptions();
		$list = array();
		
		if (UtilsWsbp::getArrayValue($options, 'e_reason_list', false)) {
			$data = UtilsWsbp::getArrayValue($options, 'reason_list');
			if (!empty($data)) {
			$ll = explode("\n", $data);
				foreach ($ll as $l) {
					$parts = explode(':', $l);
					foreach ($parts as $i => $v) {
						$parts[$i] = trim($v);
					}
					$list[] = $parts;
				}
			}
		}
		return $list;
	}
	
	public function getPointsList() {
		$options = FrameWsbp::_()->getModule('bonuses')->getMainOptions();
		$list = array();
		
		if (UtilsWsbp::getArrayValue($options, 'e_points_list', false)) {
			$data = UtilsWsbp::getArrayValue($options, 'points_list');
			if (!empty($data)) {
				$parts = explode(',', $data);
				foreach ($parts as $i => $v) {
					$p = (int) trim($v);
					if ($p > 0) {
						$list[$p] = $p;
					}
				}
			}
		}
		return $list;
	}
	
	public function saveUserAction( $id, $params, $conditions ) {
		$isNew = empty($id);
		$actionDt = UtilsWsbp::getArrayValue($params, 'date');
		$isNow = empty($actionDt) || !FrameWsbp::_()->isPro();

		$data = array('tr_type' => 0);
		
		$reason = str_replace(array("'", '\\'), array('"', ''), trim(UtilsWsbp::getArrayValue($params, 'reason')));
		
		$reasonList = $this->getReasonList();
		$controlReason = true;
		if (!empty($reasonList)) {
			$rl = UtilsWsbp::getArrayValue($params, 'reason_list', 0);
			if (isset($reasonList[$rl])) {
				if (!empty($reasonList[$rl][1]) && '+' == $reasonList[$rl][1]) {
					if (empty($reason)) {
						FrameWsbp::_()->pushError(__('The field Aditional description not filled', 'wupsales-reward-points'));
						return false;
					}
					$reason = substr(trim($reasonList[$rl][0]) . ': ' . $reason, 0, 50);
				} else {
					$reason = $reasonList[$rl][0];
					$controlReason = false;
				}
				$controlReason = false;
			}
		} 
		if ($controlReason) {
			if (empty($reason)) {
				FrameWsbp::_()->pushError(__('The field Reason not filled', 'wupsales-reward-points'));
				return false;
			}
			if (strlen($reason) > 50) {
				FrameWsbp::_()->pushError(__('The field Reason must be a maximum of 50 characters', 'wupsales-reward-points'));
				return false;
			}
		}
		$data['reason'] = $reason;
		$operation = UtilsWsbp::getArrayValue($params, 'operation', 'add') == 'add' ? 1 : -1;
		
		$points = UtilsWsbp::getArrayValue($params, 'points', 0, 1);
		if (1 == $operation) {
			$pointsList = $this->getPointsList();
			if (!empty($pointsList)) {
				$pl = UtilsWsbp::getArrayValue($params, 'points_add', 0);
				$points = isset($pointsList[$pl]) ? $pointsList[$pl] : array_keys($pointsList)[0];
			}
		}
		if (empty($points)) {
			FrameWsbp::_()->pushError(__('The field Count not filled', 'wupsales-reward-points'));
			return false;
		}
		$data['points'] = $points * $operation;
		$ts = UtilsWsbp::getTimestamp();
		
		$actionDt = $isNow ? $ts : UtilsWsbp::checkDateTime($actionDt);
		if (empty($actionDt)) {
			FrameWsbp::_()->pushError(__('The field Action date: format error', 'wupsales-reward-points'));
			return false;
		}
		$data['act_date'] = $actionDt;
		$data['end_date'] = null;
		
		if ($operation < 0) {
			$expiryDt = null;
		} else {
		
			$expiryDt = UtilsWsbp::getArrayValue($params, 'expiry');
			if (empty($expiryDt)) {
				$expiryDt = null;
			} else {
				$expiryDt = UtilsWsbp::checkDateTime($expiryDt);
				if (empty($expiryDt)) {
					FrameWsbp::_()->pushError(__('The field Expiry date: format error', 'wupsales-reward-points'));
					return false;
				}
				if ($expiryDt <= $actionDt) {
					FrameWsbp::_()->pushError(__('The Expiry date cannot be less than the Action date', 'wupsales-reward-points'));
					return false;
				}
			}
		}
		$data['exp_date'] = $expiryDt;
		$data['status'] = 0;
		$dateFormat = FrameWsbp::_()->getModule('bonuses')->getDateFormat();
		$params['date_format'] = $dateFormat;
		$data['params'] = UtilsWsbp::jsonEncode($params);
		if ($isNew) {
			$data['status'] = 0;
			$userIds = UtilsWsbp::getArrayValue($conditions, 'ids', array(), 2);
			if (!empty($conditions) && empty($userIds)) {
				$conditions['date_format'] = $dateFormat;
			}
			$data['conditions'] = UtilsWsbp::jsonEncode($conditions);
			$data['author'] = get_current_user_id();
		} else {
			$action = $this->getById($id);
			if ($action && !empty($action['status'])) {
				FrameWsbp::_()->pushError(__('This action already completed or deleted', 'wupsales-reward-points'));
				return false;
			}
		}
		
		if ($isNew) {
			$id = $this->insert($data);
		} else {
			$this->updateById($data, $id);
		}
		
		if ($isNow || $data['act_date'] <= $ts) {
			if (FrameWsbp::_()->getModule('bonuses')->getModel('transactions')->controlExpired(0, true)) {
				return $this->doUserAction($id);
			}
		}
		return true;
	}
	
	public function deleteAction( $id ) {
		$this->updateById(array('status' => 9), $id);
		return true;
	}
	
	public function doUserAction( $id, $recalc = true ) {
		$action = $this->getById($id);
		if (!$action || !is_array($action)) {
			FrameWsbp::_()->pushError(__('Action not found', 'wupsales-reward-points'));
			return false;
		} 
		if (!empty($action['status'])) {
			FrameWsbp::_()->pushError(__('The action already completed or deleted', 'wupsales-reward-points'));
			return false;
		}
		$points = UtilsWsbp::getArrayValue($action, 'points', 0, 1);
		if (empty($points)) {
			FrameWsbp::_()->pushError(__('The action has empty points', 'wupsales-reward-points'));
			return false;
		}
		
		$conditions = UtilsWsbp::jsonDecode($action['conditions']);
		$ids = UtilsWsbp::controlNumericValues(UtilsWsbp::getArrayValue($conditions, 'ids', array(), 2));
		//$filters = UtilsWsbp::getArrayValue($conditions, 'filters', array(), 2);
		$userModel = FrameWsbp::_()->getModule('actions')->getModel('users');
		
		if (empty($ids)) {
			$query = $userModel->getUsersListQuery(array('filters' => $conditions), false, true);
		} else {
			$limit = 300;
			$query = '';
			$cnt = count($ids);
			for ($offset = 0; $offset < $cnt; $offset += $limit) {
				$query .= ( empty($query) ? '' : ' UNION ALL ' ) .
					'SELECT id FROM `#__users` WHERE id IN (' . implode(',', array_slice($ids, $offset, $limit)) . ')';
			}
		}
		$tempTable = DbWsbp::createTemporaryTable('wsbpTempUserAction', $query);
		$cntUsers = DbWsbp::get('SELECT count(*) FROM ' . $tempTable . ' as t', 'one');
		if ($cntUsers > 0) {
			$status = ( $points < 0 ? 5 : 0 );
			$rest = ( $points < 0 ? 0 : $points );
			$aParams = UtilsWsbp::jsonDecode($action['params']);
			$email = ( UtilsWsbp::getArrayValue($aParams, 'e_email', 0, 1) == 1 ? 1 : 0 );
			$popup = ( UtilsWsbp::getArrayValue($aParams, 'e_popup', 0, 1) == 1 ? 1 : 0 );
			$insert = 'INSERT IGNORE INTO `@__transactions` (user_id, tr_type, points, rest, created, exp_date, op_id, email, popup, status)' .
				' SELECT t.id, 0, ' . $points . ', ' . $rest . ', now(), ' . ( is_null($action['exp_date']) ? 'NULL' : $action['exp_date'] ) . ', ' . $id . ', ' . $email . ', ' . $popup . ', ' . $status .
				' FROM ' . $tempTable . ' as t';
			if (!DbWsbp::query($insert)) {
				FrameWsbp::_()->pushError('Error query: ' . $insert);
				FrameWsbp::_()->pushError(DbWsbp::getError());
				return false;
			}
			
			if (5 == $status) {
				if (!FrameWsbp::_()->getModule('bonuses')->getModel('transactions')->completeDebitTransaction()) {
					return false;
				}
			}
		}
		
		$this->updateById(array('status' => 1, 'completed' => UtilsWsbp::getTimestamp(), 'cnt_users' => $cntUsers), $id);
		
		if ($cntUsers > 0 && $recalc) {
			return $userModel->recalcUsersParams(0, $tempTable);
		}

		return true;
	}
	
	public function getHistoryList( $params ) {
		global $wpdb;
		$data = array();

		$len = UtilsWsbp::getArrayValue($params, 'length', 10, 1);
		$start = UtilsWsbp::getArrayValue($params, 'start', 0, 1);
		$search = UtilsWsbp::getArrayValue(UtilsWsbp::getArrayValue($params, 'search', array(), 2), 'value');
		
		$withLimit = ( $len > 0 );
		
		$query = 'SELECT' . ( $withLimit ? ' SQL_CALC_FOUND_ROWS' : '' ) . ' IFNULL(completed, act_date) as real_date, a.*  FROM `@__actions` a';
		$where = ' WHERE tr_type=0';
		
		$totalCount = DbWsbp::get('SELECT COUNT(*) FROM `@__actions`' . $where, 'one');
		
		if (!empty($params['search']) && !empty($params['search']['value'])) {
			$search = $params['search']['value'];
			$isId = is_numeric($search);
			$where .= ' AND (' . $wpdb->prepare( "%1s %2s '%3s'", 'reason', 'LIKE', '%' . $search . '%' ) . ( $isId ? ' OR a.id=' . ( (int) $search ) : '' ) . ')';
		}
		if (UtilsWsbp::getArrayValue($params, 'completed', false)) {
			$where .= ' AND status=1';
		}
		
		$order = ' ORDER BY ';
		if (!empty($params['order']) && !empty($params['order']['0']['dir'])) {
			switch ($params['order']['0']['column']) {
				case 1: 
					$order .= 'real_date';
					break;
				case 3: 
					$order .= 'reason';
					break;
				case 4: 
					$order .= 'points';
					break;
				case 6: 
					$order .= 'cnt_users';
					break;
				default: 
					$order .= 'id';
					break;
			}
			$order .= ' ' . $params['order']['0']['dir'];
		}
		$query .= $where . $order;
		
		if ($len > 0) {
			if ($start >= $totalCount) {
				$start = 0;
			}
			$query .= ' LIMIT ' . $start . ',' . $len;
		}
		$actions = DbWsbp::get($query);
		$filtered = 0;
		$rows = array();
		if ($actions && !empty($actions)) {
			$filtered = DbWsbp::get('SELECT FOUND_ROWS()', 'one');
			$btnDelete = __('Are you sure to delete this action?', 'wupsales-reward-points') . '<div class="buttons"><button>' . __('Cancel', 'wupsales-reward-points') . '</button><button class="wsbp-delete">' . __('Confirm', 'wupsales-reward-points') . '</button></div>';

			foreach ($actions as $act) {
				$id = $act['id'];

				$status = $act['status'];
				
				$conditions = UtilsWsbp::jsonDecode($act['conditions']);
				$ids = UtilsWsbp::getArrayValue($conditions, 'ids', array(), 2);
				$filters = UtilsWsbp::getArrayValue($conditions, 'filters', array(), 2);
				$users = '';
				if (!empty($ids)) {
					$users = __('User list', 'wupsales-reward-points');
				} else if (!empty($filters)) {
					//$filters = UtilsWsbp::getArrayValue($filters, 'filters', array(), 2);
					$types = FrameWsbp::_()->getModule('actions')->getUsersConditionsTypes();
					$attributes = FrameWsbp::_()->getModule('actions')->getAttributesDisplay();
					$cnt = 0;
					foreach ($filters as $filter) {
						$cnt++;
						$typ = UtilsWsbp::getArrayValue($filter, 'type', false);
						$users .= ( empty($users) ? '' : '; ' ) . UtilsWsbp::getArrayValue($types, $typ, '???') . ': ';
						switch ($typ) {
							case 'role':
								$users .= implode(',', UtilsWsbp::getArrayValue($filter, 'roles', array(), 2));
								break;
							case 'registr':
							case 'order':
							case 'active':
								$minDate = UtilsWsbp::getArrayValue($filter, 'min_date');
								$maxDate = UtilsWsbp::getArrayValue($filter, 'max_date');
								if (!empty($minDate)) {
									$users .= ( empty($maxDate) ? '>=' : '' ) . $minDate;
								}
								if (!empty($maxDate)) {
									$users .= ( empty($minDate) ? '<=' : ' - ' ) . $maxDate;
								}
								if (UtilsWsbp::getArrayValue($filter, 'without_order', false)) {
									$users .= ', ' . __('without orders', 'wupsales-reward-points');
								}
								break;
							case 'amount':
							case 'count':
							case 'age':
								$minAmount = UtilsWsbp::getArrayValue($filter, 'min_' . $typ, '', 1, false, true);
								$maxAmount = UtilsWsbp::getArrayValue($filter, 'max_' . $typ, '', 1, false, true);
								if (!empty($minAmount)) {
									$users .= ( empty($maxAmount) ? '>=' : '' ) . $minAmount;
								}
								if (!empty($maxAmount)) {
									$users .= ( empty($minAmount) ? '<=' : ' - ' ) . $maxAmount;
								}
								break;
							case 'category':
							case 'tag':
							case 'attribute':
							case 'brand':
								$taxonomy = UtilsWsbp::getArrayValue($filter, 'attribute');
								if (empty($taxonomy)) {
									$taxonomy = UtilsWsbp::getArrayValue($this->taxList, $typ);
								} else {
									$users .= UtilsWsbp::getArrayValue($attributes, $taxonomy, '???') . ' - ';
								}
								if (!empty($taxonomy)) {
									$ids = UtilsWsbp::getArrayValue($filter, $typ, array(), 2);
									$i = 0;
									foreach ($ids as $termId) {
										if ($i > 10) {
											$users .= '...';
											break;
										}
										$term = get_term($termId, $taxonomy);
										if ($term) {
											$users .= ( empty($i) ? '' : ', ' ) . $term->name;
										}
										$i++;
									}
								}
								break;
							case 'product':
								$products = UtilsWsbp::getArrayValue($filter, 'product');
								$users .= ( empty($products) ? '-' : implode(',', $products) );
								break;
							default:
								break;

						}
					}
					if ($cnt > 1) {
						$users .= '; ' . __('Logic', 'wupsales-reward-points') . ': ' . UtilsWsbp::getArrayValue($conditions, 'filter_logic');
					}
				}
				
				$params = UtilsWsbp::jsonDecode($act['params']);
				if (isset($params['email']['message'])) {
					$params['email']['message'] = stripslashes(base64_decode($params['email']['message']));
				}
				if (isset($params['popup']['message'])) {
					$params['popup']['message'] = stripslashes(base64_decode($params['popup']['message']));
				};
				
				$rows[] = array(
					$id,
					UtilsWsbp::getFormatedDateTime($act['real_date']),
					$this->getFieldLists('status', $status),
					$act['reason'],
					$act['points'],
					( empty($users) ? __('All users', 'wupsales-reward-points') : $users ),
					$act['cnt_users'],
					//'<div class="wupsales-list-actions" data-id="' . $id . '" data-params="' . htmlentities($act['params']) . '">'.
					'<div class="wupsales-list-actions" data-id="' . $id . '"><input type="hidden" name="params" value="' . htmlentities(UtilsWsbp::jsonEncode($params)) . '">' .
					
					( 0 == $status ?
						'<i class="fa fa-fw fa-pencil wsbp-edit wupsales-tooltip" title="' . esc_attr__('Edit action', 'wupsales-reward-points') . '"></i>' .
						'<i class="fa fa-fw fa-trash wupsales-tooltip wsbp-delete" title="' . esc_attr($btnDelete . '<div class="wupsales-hidden">' . $id . '</div>') . '"></i>'
						: '<i class="fa fa-fw fa-eye wsbp-view wupsales-tooltip" title="' . esc_attr__('View action', 'wupsales-reward-points') . '"></i>' ) .
					'</div>',
				);
			}
		}

		$data['total'] = $totalCount;
		$data['filtered'] = $filtered;
		$data['rows'] = $rows;

		return $data;
	}
	
	public function getTaxonomyHierarchy( $taxonomy, $argsIn, $parent = true, $r = 0 ) {
		$taxonomy = is_array( $taxonomy ) ? array_shift( $taxonomy ) : $taxonomy;
		$args = array(
			'hide_empty' => $argsIn['hide_empty'],
		);
		if (isset($argsIn['order'])) {
			$args['orderby'] = !empty($argsIn['orderby']) ? $argsIn['orderby'] : 'name';
			$args['order']   = $argsIn['order'];
		}

		if ( !empty($argsIn['parent']) && 0 !== $argsIn['parent'] ) {
			$args['parent'] = $argsIn['parent'];
		} else {
			$args['parent'] = 0;
		}

		if ('' === $taxonomy) {
			return false;
		}

		if ( 'product_cat' === $taxonomy && $parent ) {
			$args['parent'] = 0;
		}

		$terms = get_terms( $taxonomy, $args );
		$children = array();
		if (!is_wp_error($terms)) {
			foreach ( $terms as $term ) {
				if (empty($argsIn['only_parent'])) {
					if (!empty($term->term_id)) {
						$args = array(
							'hide_empty' => $argsIn['hide_empty'],
							'parent' => $term->term_id,
						);
						if (isset($argsIn['order'])) {
							$args['order']   = $argsIn['order'];
							$args['orderby'] = !empty($argsIn['orderby']) ? $argsIn['orderby'] : 'name';
						}
						$term->children = $this->getTaxonomyHierarchy( $taxonomy, $args, false, $r + 1 );
					}
				}
				//$children[ $term->term_id ] = $term;
				$children[ $term->term_id ] = str_repeat('—', $r) . $term->name;
				foreach ($term->children as $k => $t) {
					$children[ $k ] = str_repeat('—', $r) . $t;
				}
			}
		}
		return $children;
	}
	
	
	public function sendMails() {
		$bonusesMod = FrameWsbp::_()->getModule('bonuses');
		$mainOptions = $bonusesMod->getMainOptions();
		$limit = UtilsWsbp::getArrayValue($mainOptions, 'max_emails', 0, 1);
		
		$query = 'SELECT t.id, t.op_id, t.points, u.user_email, uu.points as user_points' . 
			' FROM `@__transactions` t ' . 
			' INNER JOIN `#__users` as u ON (u.ID=t.user_id)' .
			' LEFT JOIN `@__users` as uu ON (uu.id=t.user_id)' .
			' WHERE u.user_email is not NULL AND t.email=1' .
			( empty($limit) ? '' : ' LIMIT ' . $limit );
		$emails = DbWsbp::get($query);
		
		if (empty($emails)) {
			return true;
		}
		$bonusesMod = FrameWsbp::_()->getModule('bonuses');
		$bonusesView = $bonusesMod->getView();
		$transactions = $bonusesMod->getModel('transactions');
		$headers = array(
			'Content-type: text/html; charset=utf-8',
			'Content-Transfer-Encoding: 8bit',
			'From: ' . get_option( 'woocommerce_email_from_name' ) . ' <' . get_option( 'woocommerce_email_from_address' ) . '>'
			);
			
		$actions = array();
		$pointsStyles = '';
		$icon = false;
		$points = '';
		$shopButton = '';
		foreach ($emails as $e) {
			$actionId = $e['op_id'];
			if (isset($actions[$actionId])) {
				$action = $actions[$actionId];
			} else {
				$action = $this->getById($actionId);
				if ($action) {
					$params = UtilsWsbp::jsonDecode(stripslashes($action['params']));
					if (UtilsWsbp::getArrayValue($params, 'e_email') == 1) {
						$action['eEmail'] = true;
						$email = UtilsWsbp::getArrayValue($params, 'email', array(), 2);
						$action['subject'] = UtilsWsbp::getArrayValue($email, 'subject');
						$action['message'] = stripslashes(base64_decode(UtilsWsbp::getArrayValue($email, 'message')));
						$action['withBP'] = UtilsWsbp::getArrayValue($email, 'bonus_block', false, 1) == 1;
						if (UtilsWsbp::getArrayValue($email, 'shop_button', false, 1)) {
							if (empty($shopButton)) {
								$shopButton = $bonusesView->renderShopButton();
							}
							$action['shopButton'] = $shopButton;
						} else {
							$action['shopButton'] = '';
						}
					}
				}
			}
			if ($action && !empty($action['eEmail'])) {
				$message = $action['message'];
				if ($action['withBP']) {
					if (empty($pointsStyles)) {
						$pointsStyles = $bonusesView->addCustomStyles(2, 1, true);
						$widgetOptions = $bonusesMod->getWidgetOptions();
						$icon = UtilsWsbp::getArrayValue($widgetOptions, 'e_icon', false);
						$points = ' ' . $bonusesMod->getNamePlural();
					}
					$message .= $pointsStyles . $bonusesView->renderUserPointsBlock($icon, true, $e['points'] . $points);
				}
				
				$subject = DispatcherWsbp::applyFilters('changeEmailSubject', $action['subject'], $action, $email);
				$message = DispatcherWsbp::applyFilters('changeEmailМessage', $message . $shopButton, $action, $email);

				if (!wp_mail($e['user_email'], $subject, $message, $headers)) {
					FrameWsbp::_()->pushError(__('Error by sending email', 'wupsales-reward-points'));
					return false;
				}
			}
			$transactions->update(array('email' => 0), $e['id']);
		}
		return true;
	}
	
	public function getUserPopupActions( $userId, $actionId = 0, $limit = 1 ) {
		$query = 'SELECT t.id, t.op_id, t.points, u.points as user_points, a.params' . 
			' FROM `@__transactions` t ' . 
			' INNER JOIN `@__users` as u ON (u.id=t.user_id)' .
			' INNER JOIN `@__actions` as a ON (a.id=t.op_id)' .
			' WHERE t.popup=1 AND t.user_id=' . ( (int) $userId ) .
			( empty($actionId) ? '' : ' AND t.op_id=' . ( (int) $actionId ) ) .
			( empty($limit) ? '' : ' LIMIT ' . $limit );
		return DbWsbp::get($query);
	}
	
}
