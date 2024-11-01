<?php
class BonusesViewWsbp extends ViewWsbp {
	//$badge / $widget:
	//0 - if enabled
	//1 - need
	//2 - not need
	public function addCustomStyles( $badge = 0, $widget = 0, $all = false ) {
		$styles = '';
		$import = '';
		$module = $this->getModule();
		$notStandartFonts = DispatcherWsbp::applyFilters('getFontsList', array(), '');
		if ( 1 == $badge || ( 0 == $badge && $module->isEnableBadge() ) ) {
			$options = $module->getBadgeOptions();
			$eIcon = false;
			if (UtilsWsbp::getArrayValue($options, 'e_icon', false)) {
				$icon = UtilsWsbp::getArrayValue($options, 'icon', FrameWsbp::_()->getModule('options')->getDefaultBadgeDesign('icon'));
				if ($icon) {
					$eIcon = true;
					$styles .= '.wsbp-badge-icon{' . $icon . '}';
				}
			}

			if (UtilsWsbp::getArrayValue($options, 'e_balance', false)) {
				$styles .= '.wsbp-badge-text{';
				$color = UtilsWsbp::getArrayValue($options, 'color', false);
				if ($color) {
					$styles .= 'background-color:' . $color . '!important;';
				}
				$font = UtilsWsbp::getArrayValue($options, 'font_family', false);
				if ($font) {
					if (in_array($font, $notStandartFonts)) {
						$import .= '@import url("//fonts.googleapis.com/css?family=' . str_replace(' ', '+', $font) . '"); ';
					}
					$styles .= 'font-family:' . $font . '!important;';
				}
				$size = UtilsWsbp::getArrayValue($options, 'font_size', false);
				if ($size) {
					$styles .= 'font-size:' . $size . 'px!important;';
				}
				$color = UtilsWsbp::getArrayValue($options, 'font_color', false);
				if ($color) {
					$styles .= 'color:' . $color . '!important;';
				}
				if ($eIcon) {
					$width = UtilsWsbp::getArrayValue($options, 'icon_width', false);
					if ($width) {
						$styles .= 'padding-left:' . ( $width - 5 ) . 'px!important;';
					}
				} else {
					$styles .= 'position:static!important;transform:initial!important;';
				}
				$styles .= '}';
			}
			if ($eIcon) {
				$styles .= '.wsbp-badge-wrapper{min-height:' . UtilsWsbp::getArrayValue($options, 'icon_height', 0, 1) . 'px!important;}';
			}
			if (UtilsWsbp::getArrayValue($options, 'show', 0, 1) == 0) {
				$styles .= '.wsbp-shop-page .wsbp-badge-wrapper{' .
					'left:' . UtilsWsbp::getArrayValue($options, 'shop_left', 0, 1) . 'px!important;' .
					'top:' . UtilsWsbp::getArrayValue($options, 'shop_top', 0, 1) . 'px!important;' .
					'}';
				$styles .= '.wsbp-product-page .wsbp-badge-wrapper{' .
					'left:' . UtilsWsbp::getArrayValue($options, 'product_left', 0, 1) . 'px!important;' .
					'top:' . UtilsWsbp::getArrayValue($options, 'product_top', 0, 1) . 'px!important;' .
					'}';
			}
		}
		
		if ( 1 == $widget || ( 0 == $widget && $module->isEnableWidget() ) ) {
			$options = $module->getWidgetOptions();
			$eIcon = false;
			if (UtilsWsbp::getArrayValue($options, 'e_icon', false)) {
				$icon = UtilsWsbp::getArrayValue($options, 'icon', FrameWsbp::_()->getModule('options')->getDefaultWidgetDesign('icon'));
				if ($icon) {
					$eIcon = true;
					$styles .= '.wsbp-widget-icon{' . $icon . '}';
				}
			}

			$stylesW = '.wsbp-detail-block{';
			$styles .= '.wsbp-widget-text{';
			$styleT = '.wsbp-widget-wrapper .wsbp-toggle{';
			$color = UtilsWsbp::getArrayValue($options, 'color', false);
			if ($color) {
				$styles .= 'background-color:' . $color . '!important;';
				$stylesW .= 'background-color:' . UtilsWsbp::colourBrightness($color, 0.7) . '!important;';
			}
			$font = UtilsWsbp::getArrayValue($options, 'font_family', false);
			if ($font) {
				if (in_array($font, $notStandartFonts)) {
					$import .= '@import url("//fonts.googleapis.com/css?family=' . str_replace(' ', '+', $font) . '"); ';
				}
				$styles .= 'font-family:' . $font . '!important;';
				$stylesW .= 'font-family:' . $font . '!important;';
			}
			$size = UtilsWsbp::getArrayValue($options, 'font_size', false);
			if ($size) {
				$styles .= 'font-size:' . $size . 'px!important;';
				$stylesW .= 'font-size:' . ( $size - 1 ) . 'px!important;';
			}
			$color = UtilsWsbp::getArrayValue($options, 'font_color', false);
			if ($color) {
				$styles .= 'color:' . $color . '!important;';
				$stylesW .= 'color:' . $color . '!important;';
				$styleT .= 'color:' . $color . '!important;';
			}
			if ($eIcon) {
				$width = UtilsWsbp::getArrayValue($options, 'icon_width', false);
				if ($width) {
					$styles .= 'padding-left:' . ( $width - 5 ) . 'px!important;';
				}
			} else {
				$styles .= 'position:static!important;transform:initial!important;';
			}
			$styles .= '}' . $stylesW . '}' . $styleT . '}';

			$styles .= ' .wsbp-widget-wrapper{';
			if (UtilsWsbp::getArrayValue($options, 'show', 0, 1) == 0) {
				$styles .= 'top:' . UtilsWsbp::getArrayValue($options, 'top', 0, 1) . 'px!important;';
			}
			if ($eIcon) {
				$styles .= 'min-height:' . UtilsWsbp::getArrayValue($options, 'icon_height', 0, 1) . 'px!important;';
			}
			$styles .= '} ';
		}
		if ($all) {
			$styles = '.wsbp-widget-wrapper{position: relative;display: inline-block;} ' .
				'.wsbp-widget-icon{position: absolute;top: 0;margin-left: -10px;z-index: 51;} ' .
				'.wsbp-widget-text{position: absolute;top: 50%;transform: translateY(-50%);z-index: 50;padding: 2px 10px;white-space: nowrap;text-align: left;line-height: normal;} ' .
			$styles;
		}

		if (!empty($styles)) {
			$this->assign('styles', ( $all ? '' : $import ) . $styles);
			return parent::getContent('bonusesFrontStyles');
		}
		return '';
	}
	
	public function showPointAdmin() {
		$frame = FrameWsbp::_();
		$module = $frame->getModule('bonuses');
		$frame->addScript('wsbp-admin-bonuses', $module->getModPath() . 'assets/js/admin.bonuses.js');
		$frame->addStyle('wsbp-admin-bonuses', $module->getModPath() . 'assets/css/admin.bonuses.css');

		$assets = AssetsWsbp::_();
		$assets->loadCoreJs();
		$assets->loadJqueryUi();
		$assets->loadDataTables(array('buttons', 'responsive'));
		$assets->loadAdminEndCss();
		DispatcherWsbp::doAction('addBonusesAssetsContent');
	
		$this->assign('is_pro', $frame->isPro());
		$this->assign('pro_url', $frame->getProUrl());
		$this->assign('main_tabs', $module->getBonusesTabsList());
				
		$settings = array(
			'emptyTable' => esc_html__('You have no products for now.', 'wupsales-reward-points'),
			'lengthMenu' => esc_html__('Show', 'wupsales-reward-points'),
			'info' => esc_html__('Showing', 'wupsales-reward-points'),
			'btn-set' => esc_html__('Set points', 'wupsales-reward-points'),
			'btn-delete' => esc_html__('Clear points', 'wupsales-reward-points'),
			'btn-cancel' => esc_html__('Cancel', 'wupsales-reward-points'),
			'confirm-delete' => esc_html__('Are you sure want to remove product\'s points?', 'wupsales-reward-points'),
			'all-label' => esc_html__('All', 'wupsales-reward-points'),
			'btn-run' => esc_html__('Run', 'wupsales-reward-points'),
			'btn-cancel' => esc_html__('Cancel', 'wupsales-reward-points'),
			'err-set' => esc_html__('Choose Products to set points', 'wupsales-reward-points'),
			'err-clear' => esc_html__('Choose Products to clear points', 'wupsales-reward-points'),
			'err-groups' => esc_html__('Choose Groups to delete', 'wupsales-reward-points'),
		);
		$this->assign('settings', $settings);
		return parent::getContent('bonusesAdmin');
	}
	
	public function showAnalyticsAdmin() {
		$assets = AssetsWsbp::_();
		$assets->loadCoreJs();
		$assets->loadAdminEndCss();
		$this->assign('pro_url', FrameWsbp::_()->getProUrl());
		return parent::getContent('analyticsAdmin');
	}
	
	public function showPointsFront() {
		$frame = FrameWsbp::_();
		$module = $frame->getModule('bonuses');
		AssetsWsbp::_()->loadCoreJs(false);
		//AssetsWsbp::_()->loadJqueryUi();
		AssetsWsbp::_()->loadJqueryPopup();
		$frame->addScript('wsbp-front-bonuses', $module->getModPath() . 'assets/js/wsbp.bonuses.js');
		$frame->addStyle('wsbp-front-bonuses', $module->getModPath() . 'assets/css/wsbp.bonuses.css');
		$jsData = array('wsbpNonce' => wp_create_nonce('wsbp-nonce'));
		if ($module->getMainOptions('age_limit')) {
			$user = $module->getUserParams();
			if (isset($user['status']) && 1 == $user['status'] && empty($user['birthday'])) {
				$jsData['showWidget'] = 1;
			}
		}
		$frame->addJSVar('wsbp-front-bonuses', 'WSBP_FRONT', $jsData);
	}
	
	public function drawBadge( $options, $addClass = '', $absolute = true ) {
		$productId = get_the_ID();
		$module = $this->getModule();
		$points = $module->getModel('products')->getProductPoints($productId);
		if (empty($points)) {
			return;
		}
		echo '<div class="wsbp-product-wrapper' . esc_attr($addClass) . '"><div class="wsbp-badge-wrapper"' . ( $absolute ? ' style="position:absolute;"' : '' ) . '>';
		if (UtilsWsbp::getArrayValue($options, 'e_icon', false)) {
			echo '<div class="wsbp-badge-icon" style="position:absolute;"></div>';
		}
		if (UtilsWsbp::getArrayValue($options, 'e_balance', false)) {
			echo '<div class="wsbp-badge-text">' . esc_html($module->getPointsWithName($points, true)) . '</div>';
		}
		echo '</div></div>';
	}

	public function printCartFormDiscount( $ret = false ) {
		if ($ret) {
			return parent::getContent('bonusesCartDiscount');
		}
		HtmlWsbp::echoEscapedHtml(parent::getContent('bonusesCartDiscount'));
	}
	
	public function renderShortcode( $attributes = array() ) {
		$module = $this->getModule();
		if (is_user_logged_in() && $module->isEnableWidget()) {
			wp_enqueue_script('jquery-ui-datepicker', '', array('jquery'), WSBP_VERSION);
			$html = '';
			$options = $module->getWidgetOptions();
			$user = $module->getUserParams();
			$icon = UtilsWsbp::getArrayValue($options, 'e_icon', false);
			$balance = UtilsWsbp::getArrayValue($options, 'e_balance', false);
			$points = $module->getPointsWithName($module->getCurrencyPrice($user['points']));
			return $this->renderUserPointsBlock($icon, $balance, $points);
		}
		return '';
	}
	public function renderUserPointsBlock( $icon, $balance, $points ) {
		return '<div class="wsbp-widget-wrapper" data-currency="' . get_woocommerce_currency() . '">' . 
			( $icon ? '<div class="wsbp-widget-icon" style="position:absolute;"></div>' : '' ) .
			( $balance ? '<div class="wsbp-widget-text">' . esc_html($points) . '</div>' : '' ) . 
			'</div>';
	}
	public function renderShopButton() {
		$html = '<div style="display:block;text-align:center;">' . 
			'<a href="' . get_permalink(wc_get_page_id('shop')) . '" style="' .
			'outline:none;box-shadow:none;background-color:#32CD32;color:#ffffff;border:1px solid #32CD32;font-size:14px;letter-spacing:0.06em;margin:0;padding:5px 20px;display:inline-block;text-decoration:none;' .
			'">' . __('Shop', 'wupsales-reward-points') . '</a></div>';
		return $html;
	}
		
	public function renderWidgetPopup( $userId = 0, $front = true ) {
		AssetsWsbp::_()->loadJqueryUi();
		$module = $this->getModule();
				
		$options = $module->getWidgetOptions();
		$user = $module->getUserParams($userId);
		$userId = $user['id'];
		$bonuses = $module->getModel('transactions')->getUserActiveBonuses($userId);
		$dateFormat = 'Y-m-d';
		$transFrom = UtilsWsbp::addDays(-6, $dateFormat);
		$transTo = UtilsWsbp::getFormatedDateTime(UtilsWsbp::getTimestamp(), $dateFormat);
		$trans = $module->getModel('transactions')->getUserTransactions($userId, array('from' => $transFrom, 'to' => $transTo, 'date_format' => $dateFormat));
		$this->assign('is_pro', FrameWsbp::_()->isPro());
		$this->assign('user', $user);
		$this->assign('userId', $userId);
		$this->assign('options', $options);
		$this->assign('bonuses', $bonuses);
		$dateFormat = $module->getDateFormat();
		$this->assign('transFrom', UtilsWsbp::addDays(-6, $dateFormat));
		$this->assign('transTo', UtilsWsbp::getFormatedDateTime(UtilsWsbp::getTimestamp(), $dateFormat));
		$this->assign('trans', $trans);
		$this->assign('rules_view', FrameWsbp::_()->getModule('options')->getPageRulesUrl());
		$this->assign('is_front', $front);
		
		$tabs = array(
			'balance' => __('Account Details', 'wupsales-reward-points'),
			'trans' => __('Transactions', 'wupsales-reward-points')
		);
		$currentTab = 'balance';
		if ($front) {
			$tabs['settings'] = __('Settings', 'wupsales-reward-points');
			if (!$module->isActiveUser($userId)) {
				$currentTab = 'settings';
			}
		
			$this->assign('age_limit', $module->getMainOptions('age_limit') == 1);
			$this->assign('is_age_pass', $module->isUserAgePass($user));
		}
		$this->assign('tabs', $tabs);
		$this->assign('current_tab', $currentTab);
		
		return array('html' => parent::getContent('widgetPopup'), 'css' => parent::getContent('widgetPopupCss'));
	}
	public function renderUserTransactions( $userId, $params ) {
		$trans = $this->getModule()->getModel('transactions')->getUserTransactions($userId, $params);
		$this->assign('trans', $trans);
		$this->assign('is_front', !empty($params['front']));
		return parent::getContent('widgetPopupTransactions');
	}
}
