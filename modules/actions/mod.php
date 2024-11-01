<?php
class ActionsWsbp extends ModuleWsbp {
	private static $enableUsersWidget = null;

	public function init() {
		DispatcherWsbp::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		add_shortcode(WSBP_SHORTCODE_USERS, array($this, 'doShortcodeUsers'));
		
		add_action('woocommerce_order_status_changed', array($this, 'recalcUserBalance'), 10, 4);
		add_action('wsbp_calc_users_balance', array($this->getModel('users'), 'recalcUsersParams'), 10, 1);
		add_action('wsbp_do_users_actions', array($this, 'doUserActions'), 10, 1);
		//wp_clear_scheduled_hook('wsbp_do_users_actions');
		if (!wp_next_scheduled('wsbp_do_users_actions')) {
			wp_schedule_event(time() + 3, 'hourly', 'wsbp_do_users_actions');
		}
	}
	
	public function doUserActions() {
		if (FrameWsbp::_()->getModule('bonuses')->isActiveBonusProgram()) {
			if (!FrameWsbp::_()->getModule('bonuses')->getModel('transactions')->controlExpired(0, true)) {
				FrameWsbp::_()->saveDebugLogging();
			}
			DispatcherWsbp::doAction('doUserAutoActions');
			if (!$this->getModel()->sendMails()) {
				FrameWsbp::_()->saveDebugLogging();
			}
		}
	}
	
	public function recalcUserBalance( $orderId, $from, $to, $order ) {
		if (FrameWsbp::_()->getModule('bonuses')->isActiveBonusProgram()) {
			$cancelStatuses = array('cancelled', 'refunded', 'failed');
			$recalc = true;
			if ('completed' == $to && !in_array($from, $cancelStatuses)) {
				FrameWsbp::_()->getModule('bonuses')->getModel()->addPointsForPurchase($order);
				$recalc = false;
			}
			if (in_array($to, $cancelStatuses)) {
				FrameWsbp::_()->getModule('bonuses')->getModel()->deletePointsForPurchase($order, 'refunded' == $to && 'completed' == $from);
				$recalc = false;
			}
			if ('completed' == $from || 'completed' == $to) {
				if ($recalc) {
					$this->getModel('users')->recalcUsersParams($order->get_customer_id());
				}
				/*if ('completed' == $to) {
					FrameWsbp::_()->getModule('bonuses')->getModel()->addPointsForPurchase($order);
				} */
			}
			DispatcherWsbp::doAction('orderStatusChanged', $orderId, $from, $to, $order);
		}
	}

	public function addAdminTab( $tabs ) {
		$icon = FrameWsbp::_()->isPro() ? '' : ' wupsales-show-pro';
		$code = $this->getCode();
		$tabs[ $code . '-manual' ] = array(
			'label' => esc_html__('User balance', 'wupsales-reward-points'), 'callback' => array($this, 'showActionsAdmin'), 'fa_icon' => 'fa-users', 'sort_order' => 20, 'add_bread' => $this->getCode(),
		);
		$tabs[$code . '-auto'] = array(
			'label' => esc_html__('AutoActions', 'wupsales-reward-points'), 'callback' => array($this, 'showAutoActionsAdmin'), 'fa_icon' => 'fa-magic' . $icon, 'sort_order' => 40, 'add_bread' => $this->getCode(),
		);
		return $tabs;
	}

	public function showActionsAdmin() {
		return $this->getView()->showActionsAdmin();
	}
	public function showAutoActionsAdmin() {
		return DispatcherWsbp::applyFilters('showAutoActionsAdmin', $this->getView()->showAutoActionsAdmin());
	}
	public function getActionsTabsList( $current = '' ) {
		$tabs = array(
			'users' => array(
				'icon' => 'fa-user',
				'class' => '',
				'pro' => false,
				'label' => __('Users', 'wupsales-reward-points'),
			),
			'history' => array(
				'icon' => 'fa-history',
				'class' => '',
				'pro' => false,
				'label' => __('History', 'wupsales-reward-points'),
			),
		);

		if (empty($current) || !isset($tabs[$current])) {
			reset($tabs);
			$current = key($tabs);
		}
		$tabs[$current]['class'] .= ' current';
		
		return DispatcherWsbp::applyFilters('getActionsTabsList', $tabs);
	}
	
	public function getUsersConditionsTypes() {
		return array(
			'role' => __('Role', 'wupsales-reward-points'),
			'registr' => __('Registration', 'wupsales-reward-points'),
			'age' => __('Age', 'wupsales-reward-points'),
			'amount' => __('Total amount', 'wupsales-reward-points'),
			'count' => __('Count of purchases', 'wupsales-reward-points'),
			'order' => __('Last order', 'wupsales-reward-points'),
			'active' => __('Last active', 'wupsales-reward-points'),
			'category' => __('Bought in categories', 'wupsales-reward-points'),
			'attribute' => __('Bought with attributes', 'wupsales-reward-points'),
			'tag' => __('Bought with tags', 'wupsales-reward-points'),
			'brand' => __('Bought brands', 'wupsales-reward-points'),
			'product' => __('Bought products', 'wupsales-reward-points')
		);
	}
	
	public function getAttributesDisplay() {
		//return wc_get_attribute_taxonomy_labels();
		$productAttr = wc_get_attribute_taxonomies();

		$attrDisplay = array('' => esc_html__('Select attribute', 'wupsales-reward-points'));
		foreach ($productAttr as $attr) {
			$attrDisplay[wc_attribute_taxonomy_name($attr->attribute_name)] = $attr->attribute_label;
		}
		return $attrDisplay;
	}
	
	public function doShortcodeUsers( $params ) {
		return FrameWsbp::_()->getModule('bonuses')->isActiveBonusProgram() ? $this->getView()->renderShortcodeUsers($params) : '';
	}
	
	public function isEnableUsersWidget() {
		if (is_null(self::$enableUsersWidget)) {
			self::$enableUsersWidget = false;
			$options = FrameWsbp::_()->getModule('bonuses')->getMainOptions();
			if (is_user_logged_in() && UtilsWsbp::getArrayValue($options, 'e_user_widget')) {
				$access = UtilsWsbp::getArrayValue($options, 'widget_access_roles', array(), 2);
				$user = wp_get_current_user();
				$roles = ( array ) $user->roles;
				if (!empty($access) && !empty($roles)) {
					foreach ($roles as $role) {
						if (in_array($role, $access)) {
							self::$enableUsersWidget = true;
						}
					}
				}
			}
		}
		return self::$enableUsersWidget;
	}
}
