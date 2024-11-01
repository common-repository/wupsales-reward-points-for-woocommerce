<?php
class BonusesWsbp extends ModuleWsbp {
	private static $isActiveBonusProgram = null;
	private static $userParams = array();
	private static $mainOptions = null;
	private static $designOptions = null;
	private static $enableBadge = null;
	private static $badgeOnImage = null;
	private static $badgeAbbreviation = null;
	private static $badgeOptions = null;
	private static $enableWidget = null;
	private static $widgetOptions = null;
	private static $namePlural = null;
	private static $nameOne = null;
	private static $abbreviated = null;
	private static $dateFormat = null;
	private static $currencyRate = null;
	public $bonusCoupon = 'wsbp-coupon';

	public function init() {
		DispatcherWsbp::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		add_action( 'woocommerce_update_product', array( $this, 'recalcProductPoints' ), 100, 1 );

		add_shortcode(WSBP_SHORTCODE, array($this, 'doShortcode'));
		add_action('init', array($this, 'initProductBPFields'), 999);
		add_action('wp_head', array($this, 'addAssetsFront'));
		
		add_action('wsbp_calc_products_points', array($this->getModel('products'), 'recalcProductsPoints'), 10, 1);
		
		add_action('woocommerce_before_shop_loop_item', array($this, 'addBadgeBeforeShopLoopItem'), 10, 0);
		add_action('woocommerce_before_shop_loop_item_title', array($this, 'addBadgeBeforeShopLoopTitle'), 99, 0);
		add_action('woocommerce_before_single_product_summary', array($this, 'addBadgeProductPage'), 10, 0);
		add_action('woocommerce_before_single_product', array($this, 'addBadgeBeforeSingleProduct'), 10, 0);
		add_action('woocommerce_after_cart_table', array($this, 'printCartFormDiscount'), 10, 0);
		add_action('woocommerce_before_calculate_totals', array($this, 'addCartPointsCoupon'), 10, 1);
		add_filter('woocommerce_get_shop_coupon_data', array($this, 'getPointsCouponData'), 10, 3);
		add_action('woocommerce_checkout_order_processed', array($this, 'doDiscountCompleted'), 1, 1 );
		
		add_filter('woocommerce_cart_totals_coupon_html', function( $coupon_html, $coupon, $discount_amount_html ) {
			return $coupon->get_code() == $this->bonusCoupon ? $discount_amount_html : $coupon_html;
		}, 10, 3);
		add_filter('woocommerce_cart_totals_coupon_label', function( $label, $coupon ) {
			return $coupon->get_code() == $this->bonusCoupon ? $this->getNamePlural() : $label;
		}, 10, 3);
		add_filter( 'render_block', array($this, 'renderCartFormDiscount'), 10, 2 );
	}
	

	function renderCartFormDiscount( $blockContent, $block ) {
		if( $block['blockName'] === 'woocommerce/cart-order-summary-coupon-form-block' ) {
			if ($this->isActiveBonusProgram() && $this->isActiveUser()) {
				$blockContent .= $this->getView()->printCartFormDiscount(true);
			}
		}
		return $blockContent;
	}
	public function isActiveBonusProgram() {
		if (is_null(self::$isActiveBonusProgram)) {
			self::$isActiveBonusProgram = FrameWsbp::_()->getModule('options')->getModel()->isActiveBonusProgram();
		}
		return self::$isActiveBonusProgram;
	}
	
	public function getUserParams( $userId = 0 ) {
		if (empty($userId)) {
			$userId = get_current_user_id();
		}
		if ($userId) {
			if (!isset(self::$userParams[$userId])) {
				self::$userParams[$userId] = FrameWsbp::_()->getModule('actions')->getModel('users')->getUserParams($userId);
			}
		} else {
			return false;
		}
		return self::$userParams[$userId];
	}
	
	public function isActiveUser( $userId = 0 ) {
		if (empty($userId)) {
			$userId = get_current_user_id();
		}
		if ($userId) {
			$userParams = $this->getUserParams($userId);
			return $userParams && UtilsWsbp::getArrayValue($userParams, 'status', 0, 1) == 1 && $this->isUserAgePass($userParams);
		}
		return false;
	}
	public function isUserAgePass( $userParams ) {
		if ($this->getMainOptions('age_limit') == 1) {
			$birthday = UtilsWsbp::getArrayValue($userParams, 'birthday', 0, 1);
			return !empty($birthday) && UtilsWsbp::addDays(0, false, ( (int) $this->getMainOptions('min_age_user') ) * ( -1 )) > $birthday;
		}
		return true;
	}
	
	public function getDesignOptions() {
		if (is_null(self::$designOptions)) {
			self::$designOptions = FrameWsbp::_()->getModule('options')->getModel()->get('design', '');
		}
		return self::$designOptions;
	}
	
	public function getMainOptions( $key = '' ) {
		if (is_null(self::$mainOptions)) {
			self::$mainOptions = FrameWsbp::_()->getModule('options')->getModel()->get('main', '');
			if (!isset(self::$mainOptions['expiry_date'])) {
				self::$mainOptions = FrameWsbp::_()->getModule('options')->getDefaultMainSettings();
			}
		}
		return empty($key) ? self::$mainOptions : UtilsWsbp::getArrayValue(self::$mainOptions, $key);
	}
	public function getDateFormat() {
		if (is_null(self::$dateFormat)) {
			$options = $this->getMainOptions();
			self::$dateFormat = UtilsWsbp::getArrayValue($options, 'date_format');
			if (empty(self::$dateFormat)) {
				self::$dateFormat = 'Y-m-d';
			}
		}
		return self::$dateFormat;
	}
	
	public function getBadgeOptions() {
		if (is_null(self::$badgeOptions)) {
			$options = $this->getDesignOptions();
			$badge = is_array($options) ? UtilsWsbp::getArrayValue($options, 'badge', false, 2) : false;
			if (false === $badge || !isset($badge['e_icon'])) {
				$badge = FrameWsbp::_()->getModule('options')->getDefaultBadgeDesign();
			}
			self::$badgeOptions = $badge;
		}
		return self::$badgeOptions;
	}
	
	public function isEnableBadge() {
		if (is_null(self::$enableBadge)) {
			$options = $this->getBadgeOptions();
			self::$enableBadge = UtilsWsbp::getArrayValue($options, 'e_icon', false, 1) || UtilsWsbp::getArrayValue($options, 'e_balance', false, 1);
		}
		return self::$enableBadge;
	}
	public function isBadgeOnImage() {
		if (is_null(self::$badgeOnImage)) {
			$options = $this->getBadgeOptions();
			self::$badgeOnImage = UtilsWsbp::getArrayValue($options, 'show', 0, 1) == 0;
		}
		return self::$badgeOnImage;
	}
	public function isBadgeAbbreviation() {
		if (is_null(self::$badgeAbbreviation)) {
			$options = $this->getBadgeOptions();
			self::$badgeAbbreviation = UtilsWsbp::getArrayValue($options, 'e_abbreviation', 0, 1) == 1;
		}
		return self::$badgeAbbreviation;
	}
	
	public function getWidgetOptions() {
		if (is_null(self::$widgetOptions)) {
			$options = $this->getDesignOptions();
			$widget = is_array($options) ? UtilsWsbp::getArrayValue($options, 'widget', false, 2) : false;
			if (false === $widget || !isset($widget['e_icon'])) {
				$widget = FrameWsbp::_()->getModule('options')->getDefaultWidgetDesign();
			}
			self::$widgetOptions = $widget;
		}
		return self::$widgetOptions;
	}
	
	public function isEnableWidget() {
		if (is_null(self::$enableWidget)) {
			$options = $this->getWidgetOptions();
			self::$enableWidget = UtilsWsbp::getArrayValue($options, 'e_icon', false, 1) || UtilsWsbp::getArrayValue($options, 'e_balance', false, 1);
		}
		return self::$enableWidget;
	}
	
	public function getNamePlural() {
		if (is_null(self::$namePlural)) {
			$options = $this->getMainOptions();
			self::$namePlural = UtilsWsbp::getArrayValue($options, 'name_plural');
			if (empty(self::$namePlural)) {
				self::$namePlural = FrameWsbp::_()->getModule('options')->getDefaultMainSettings('name_plural');
			}
		}
		return self::$namePlural;
	}
	public function getNameOne() {
		if (is_null(self::$nameOne)) {
			$options = $this->getMainOptions();
			self::$nameOne = UtilsWsbp::getArrayValue($options, 'name_one_point');
			if (empty(self::$nameOne)) {
				self::$nameOne = FrameWsbp::_()->getModule('options')->getDefaultMainSettings('name_one_point');
			}
		}
		return self::$nameOne;
	}
	public function getAbbreviated() {
		if (is_null(self::$abbreviated)) {
			$options = $this->getMainOptions();
			self::$abbreviated = UtilsWsbp::getArrayValue($options, 'name_abbreviated');
			if (empty(self::$abbreviated)) {
				self::$abbreviated = FrameWsbp::_()->getModule('options')->getDefaultMainSettings('name_abbreviated');
			}
		}
		return self::$abbreviated;
	}
	
	public function addAssetsFront() {
		if ($this->isActiveBonusProgram()) {
			if ($this->isEnableBadge() || $this->isEnableWidget()) {
				HtmlWsbp::echoEscapedHtml($this->getView()->addCustomStyles());
			}
			$this->getView()->showPointsFront();
		}
	}
	
	
	public function getCurrencyRate() {
		if (is_null(self::$currencyRate)) {
			$price = 1000;
			$newPrice = $this->getCurrencyPrice($price);
			self::$currencyRate = $newPrice / $price;
		}
		
		return self::$currencyRate;
	}
	
	public function getCurrencyPrice( $rawPrice, $dec = 2 ) {
		if (function_exists('alg_wc_currency_switcher_plugin')) {
			$price = alg_wc_currency_switcher_plugin()->core->change_price_by_currency($rawPrice);
		} else {
			/**
			* Do raw_woocommerce_price
			* 
			* @since
			*/
			$price = apply_filters('raw_woocommerce_price', $rawPrice);
			// some plugin uses a different hook, use it if the standard one did not change the price
			if ($price === $rawPrice && function_exists('is_plugin_active') && ( is_plugin_active( 'woocommerce-currency-switcher/index.php') || is_plugin_active('woocommerce-multicurrency/woocommerce-multicurrency.php') )) {
				/**
				* Do woocommerce_product_get_regular_price
				* 
				* @since
				*/
				$price = apply_filters('woocommerce_product_get_regular_price', $rawPrice, null);
			}
		}
		return ( false === $dec ? $price : round($price, $dec) );
	}
	
	public function recalcProductPoints( $productId ) {
		//if ( ! $this->isDisabledAutoindexing() ) {
			$this->getModel('products')->recalcProductsPoints( $productId );
		//}
	}
	
	public function addBadgeBeforeShopLoopItem() {
		if ($this->isActiveBonusProgram() && $this->isEnableBadge() && $this->isBadgeOnImage()) {
			$this->getView()->drawBadge($this->getBadgeOptions(), ' wsbp-shop-page');
		}
	}
	public function addBadgeProductPage() {
		if ($this->isActiveBonusProgram() && $this->isEnableBadge() && $this->isBadgeOnImage()) {
			$this->getView()->drawBadge($this->getBadgeOptions(), ' wsbp-product-page');
		}
	}
	public function addBadgeBeforeShopLoopTitle() {
		if ($this->isActiveBonusProgram() && $this->isEnableBadge() && !$this->isBadgeOnImage()) {
			$this->getView()->drawBadge($this->getBadgeOptions(), ' wsbp-before-title', false);
		}
	}
	public function addBadgeBeforeSingleProduct() {
		if ($this->isActiveBonusProgram() && $this->isEnableBadge() && !$this->isBadgeOnImage()) {
			$this->getView()->drawBadge($this->getBadgeOptions(), ' wsbp-before-title wsbp-before-product', false);
		}
	}
	public function printCartFormDiscount() {
		if ($this->isActiveBonusProgram() && $this->isActiveUser()) {
			$this->getView()->printCartFormDiscount();
		}
	}
	
	public function doDiscountCompleted( $orderId ) {
		if ($this->isActiveBonusProgram() && $this->isActiveUser()) {
			$this->getModel()->doDiscountCompleted($orderId, $this->getUserParams());
		}
	}
	
	public function addCartPointsCoupon( $cart ) {
		$cart->applied_coupons = array_diff($cart->applied_coupons, [$this->bonusCoupon]);
		if ($this->isActiveBonusProgram() && $this->isActiveUser()) {
			$user = $this->getUserParams();
			$coupon = (float) $user['cart'];
			if (!empty($coupon)) {
				$cart->applied_coupons[] = $this->bonusCoupon;
			}
		}
	}
	
	public function getPointsCouponData( $false, $data, $coupon ) {
		if ($data == $this->bonusCoupon && $this->isActiveBonusProgram() && $this->isActiveUser()) {
			$coupon->set_code($this->bonusCoupon);
			$coupon->set_virtual(true);
			$coupon->set_discount_type('fixed_cart');
			$amount = 0;
			$user = $this->getUserParams();
			
			if (!empty($user['cart'])) {
				$amount = $user['cart'];
			}
			$amount = DispatcherWsbp::applyFilters('getDiscountAmount', $amount);
			$cart = WC()->cart;
			if ($cart) {
				$total = $cart->subtotal;
				if ($total < $amount) {
					$amount = $total;
				}
				$mainOptions = $this->getMainOptions();
				if (UtilsWsbp::getArrayValue($mainOptions, 'e_max_percent_cart', false)) {
					$percent = UtilsWsbp::getArrayValue($mainOptions, 'max_percent_cart', 100, 1);
					if ($percent < 100) {
						$maxPoint = round($total * $percent / 100, 2);
						if ($maxPoint < $amount) {
							$amount = $maxPoint;
						}
					}
				}
			}
			$coupon->set_amount($amount);
			$coupon = DispatcherWsbp::applyFilters('getPointsCouponData', $coupon, $data, $user);
			return $coupon;
		}
		return $false;
	}

	public function addAdminTab( $tabs ) {
		$icon = FrameWsbp::_()->isPro() ? '' : ' wupsales-show-pro';
		$code = $this->getCode();
		$tabs[ $code . '-point' ] = array(
			'label' => esc_html__('Set points', 'wupsales-reward-points'), 'callback' => array($this, 'showPointAdmin'), 'fa_icon' => 'fa-plus', 'sort_order' => 10, 'add_bread' => $this->getCode(),
		);
		$tabs[ 'analytics'] = array(
			'label' => esc_html__('Analytics', 'wupsales-reward-points'), 'callback' => array($this, 'showAnalyticsAdmin'), 'fa_icon' => 'fa-line-chart' . $icon, 'sort_order' => 50, 'add_bread' => $this->getCode(),
		);
		return $tabs;
	}

	public function showPointAdmin() {
		return $this->getView()->showPointAdmin();
	}
	public function showAnalyticsAdmin() {
		return DispatcherWsbp::applyFilters('showAnalyticsAdmin', $this->getView()->showAnalyticsAdmin());
	}
	
	public function getBonusesTabsList( $current = '' ) {
		//$proClass = ( FrameWsbp::_()->isPro() ? '' : ' wupsales-show-pro' );
		$tabs = array(
			'products' => array(
				'icon' => 'fa-object-ungroup',
				'class' => '',
				'pro' => false,
				'label' => __('Products', 'wupsales-reward-points'),
			),
			'groups' => array(
				'icon' => 'fa-object-group',
				'class' => '',
				'pro' => true,
				'label' => __('Groups', 'wupsales-reward-points'),
			),
		);

		if (empty($current) || !isset($tabs[$current])) {
			reset($tabs);
			$current = key($tabs);
		}
		$tabs[$current]['class'] .= ' current';
		
		return DispatcherWsbp::applyFilters('getBonusesTabsList', $tabs);
	}
	
	public function initProductBPFields() {
		$model = $this->getModel();

		add_action('woocommerce_product_options_pricing', array($model, 'createProductBPFields'));
		add_action('woocommerce_variation_options_pricing', array($model, 'createProductBPFieldsVariation'), 10, 3);
		add_action('woocommerce_process_product_meta', array($model, 'saveProductBPFields'));
		add_action('woocommerce_save_product_variation', array($model, 'saveProductBPFieldsVariation'), 10, 2);
	}
	
	public function addWidgetInMenu( $items, $args ) {
		$items .= '<li>' . $this->getView()->renderShortcode() . '</li>';
		return $items;
	}
	
	public function doShortcode( $params ) {
		return $this->isActiveBonusProgram() ? $this->getView()->renderShortcode($params) : '';
	}
	
	public function getPointsWithName( $points, $isBadge = false ) {
		if ($isBadge && $this->isBadgeAbbreviation()) {
			return $points . ' ' . $this->getAbbreviated();
		}
		return $points . ' ' . ( 1 == $points ? $this->getNameOne() : $this->getNamePlural() );
	}
}
