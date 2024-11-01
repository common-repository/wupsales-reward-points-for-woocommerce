<?php
class OptionsWsbp extends ModuleWsbp {
	private $_options = array();
	private $_optionsToCategoires = array();	// For faster search

	public function init() {
		DispatcherWsbp::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		add_action('init', array($this, 'createPageRules'), 999);
	}
	public function initAllOptValues() {
		// Just to make sure - that we loaded all default options values
		$this->getAll();
	}
	/**
	 * This method provides fast access to options model method get
	 *
	 * @see optionsModel::get($d)
	 */
	public function get( $gr, $key = '' ) {
		if (empty($key)) {
			$key = $gr;
			$gr = 'main';
		}
		return $this->getModel()->get($gr, $key);
	}
	/**
	 * This method provides fast access to options model method get
	 *
	 * @see optionsModel::get($d)
	 */
	public function isEmpty( $gr, $key = '' ) {
		if (empty($key)) {
			$key = $gr;
			$gr = 'main';
		}
		return $this->getModel()->isEmpty($gr, $key);
	}

	public function addAdminTab( $tabs ) {
		$tabs['settings'] = array(
			'label' => esc_html__('Settings', 'wupsales-reward-points'), 'callback' => array($this, 'getSettingsTabContent'), 'fa_icon' => 'fa-cog', 'sort_order' => 30,
		);
		return $tabs;
	}
	public function getSettingsTabContent() {
		return $this->getView()->getSettingsTabContent();
	}
	
	public function getRolesList() {
		if (!function_exists('get_editable_roles')) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );
		}
		return get_editable_roles();
	}
	public function getAvailableUserRolesSelect() {
		$rolesList = $this->getRolesList();
		$rolesListForSelect = array();
		foreach ($rolesList as $rKey => $rData) {
			$rolesListForSelect[ $rKey ] = $rData['name'];
		}
		return $rolesListForSelect;
	}
	public function getOptionsTabsList( $current = '' ) {
		$tabs = array(
			'main' => array(
				'icon' => 'fa-wrench',
				'class' => '',
				'pro' => false,
				'label' => __('Main', 'wupsales-reward-points'),
				'remove' => false
			),
			'design' => array(
				'icon' => 'fa-paint-brush',
				'class' => '',
				'pro' => true,
				'label' => __('Design', 'wupsales-reward-points'),
				'remove' => false
			),
			'cart' => array(
				'icon' => 'fa-shopping-cart',
				'class' => '',
				'pro' => true,
				'label' => __('Cart', 'wupsales-reward-points'),
				'remove' => true
			),
			'levels' => array(
				'icon' => 'fa-level-up',
				'class' => '',
				'pro' => true,
				'label' => __('User levels', 'wupsales-reward-points'),
				'remove' => true
			),
		);

		if (empty($current) || !isset($tabs[$current])) {
			reset($tabs);
			$current = key($tabs);
		}
		$tabs[$current]['class'] .= ' current';
		
		return DispatcherWsbp::applyFilters('getOptionsTabsList', $tabs);
	}
	
	public function getDefaultMainSettings( $key = '', $def = '' ) {
		$main = array(
			'age_limit' => 0,
			'min_age_user' => 18,
			'name_one_point' => __('Point', 'wupsales-reward-points'),
			'name_plural' => __('Points', 'wupsales-reward-points'),
			'name_abbreviated' => __('RP', 'wupsales-reward-points'),
			'max_emails' => 30,
			'logging' => 1,
			'widget_access_roles' => '',
			'widget_new_user_role' => 'customer',
			'widget_user_fields' => '',
			'expiry_date' => 180,
			'logic_expiry' => 0,
			'max_percent_cart' => 80,
			'refund_type' => 0,
			'date_format' => 'Y-m-d',
			'round_percent_decimals' => 2,
			
		);
		$main = DispatcherWsbp::applyFilters('getDefaultMainSettings', $main);
		return empty($key) ? $main : ( isset($main[$key]) ? $main[$key] : $def );
	}
	public function getDefaultWidgetDesign( $key = '', $def = ''  ) {
		$design = array(
			'e_icon' => 1,
			'icon' => 'background-image:url(' . WSBP_IMG_PATH . 'giftbox.png);width:32px;height:32px;',
			'icon_height' => 32,
			'icon_width' => 32,
			'e_balance' => 1,
			'color' => '#32CD32',
			'font_family' => '',
			'font_size' => 16,
			'font_color' => '#000000'
		);
		$design = DispatcherWsbp::applyFilters('getDefaultWidgetDesign', $design);
		return empty($key) ? $design : ( isset($design[$key]) ? $design[$key] : $def );
	}
	public function getDefaultBadgeDesign( $key = '', $def = '' ) {
		$design = array(
			'e_icon' => 1,
			'icon' => 'background-image:url(' . WSBP_IMG_PATH . 'giftbox.png);width:32px;height:32px;',
			'icon_height' => 32,
			'icon_width' => 32,
			'e_balance' => 1,
			'e_abbreviation' => 0,
			'color' => '#32CD32',
			'font_family' => '',
			'font_size' => 16,
			'font_color' => '#000000',
			'show' => 0,
			'shop_left' => 0,
			'shop_top' => 0,
			'product_left' => 0,
			'product_top' => 0
		);
		$design = DispatcherWsbp::applyFilters('getDefaultBadgeDesign', $design);
		return empty($key) ? $design : ( isset($design[$key]) ? $design[$key] : $def );
	}
	public function getRecalcOptions() {
		$options = array('main' => array('round_percent_point', 'round_percent_decimals'));
		return DispatcherWsbp::applyFilters('getRecalcOptions', $options);
	}
	public function createPageRules( $reset = false ) {
		if (!$this->getView()->renderPageRules($reset)) {
			FrameWsbp::_()->saveDebugLogging();
		}
	}
	public function getPageRulesUrl() {
		$page = $this->getModel()->getPageRules();
		return $page ? get_permalink($page) : false;
	}
	public function getPageRulesEditLink() {
		$page = $this->getModel()->getPageRules();
		return $page ? get_edit_post_link($page) : false;
	}
	public function isActiveBonusProgram() {
		return $this->getModel()->isActiveBonusProgram();
	}
}
