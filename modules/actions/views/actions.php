<?php
class ActionsViewWsbp extends ViewWsbp {
	
	public function showActionsAdmin() {
		$frame = FrameWsbp::_();
		$module = $frame->getModule('actions');
		$frame->addScript('wsbp-admin-actions', $module->getModPath() . 'assets/admin.actions.js');
		$frame->addStyle('wsbp-admin-actions', $module->getModPath() . 'assets/admin.actions.css');

		$assets = AssetsWsbp::_();
		$assets->loadCoreJs();
		$assets->loadJqueryUi();
		$assets->loadDataTables(array('buttons', 'responsive'));
		$assets->loadAdminEndCss();
		$assets->loadChosenSelects();
		$assets->loadDateTimePicker();
		$assets->loadJqueryPopup();
		$frame->addStyle('wsbp-front-bonuses', $frame->getModule('bonuses')->getModPath() . 'assets/css/wsbp.bonuses.css');
		wp_enqueue_editor();
		wp_enqueue_script('media-upload');
		//DispatcherWsbp::doAction('addBonusesAssetsContent');
	
		$this->assign('is_pro', $frame->isPro());
		$this->assign('pro_url', $frame->getProUrl());
		$this->assign('main_tabs', $module->getActionsTabsList());
		$this->assign('cond_types', $module->getUsersConditionsTypes());
		
		$args = array(
			'post_type'           => array('product'),
			'ignore_sticky_posts' => true,
			'posts_per_page'      => -1,
			'fields'              => 'ids',
			'tax_query'           => array()
		);
		$loop = new WP_Query($args);
		$products = array();
		$cnt = $loop->found_posts;
		if ($cnt > 0 && $cnt <= 100) {
			foreach ($loop->posts as $id) {
				$_product = wc_get_product($id);
				$products[$id] = $_product->get_name();
			}
		}
		$this->assign('products', $products);
				
		$settings = array(
			'emptyTable' => esc_html__('You have no actions for now.', 'wupsales-reward-points'),
			'lengthMenu' => esc_html__('Show', 'wupsales-reward-points'),
			'info' => esc_html__('Showing', 'wupsales-reward-points'),
			'btn-run' => esc_html__('Run', 'wupsales-reward-points'),
			'btn-add' => esc_html__('Add points', 'wupsales-reward-points'),
			'btn-delete' => esc_html__('Delete points', 'wupsales-reward-points'),
			'btn-filtered' => esc_html__('For all filtered', 'wupsales-reward-points'),
			'btn-save' => esc_html__('Save', 'wupsales-reward-points'),
			'btn-cancel' => esc_html__('Cancel', 'wupsales-reward-points'),
			//'confirm-delete' => esc_html__('Are you sure want to remove product\'s point?', 'wupsales-reward-points'),
			'all-label' => esc_html__('All', 'wupsales-reward-points'),
			'err-add' => esc_html__('Choose Users to set points', 'wupsales-reward-points'),
			'err-delete' => esc_html__('Choose Users to delete points', 'wupsales-reward-points'),
		);
		$this->assign('settings', $settings);

		return $frame->getModule('bonuses')->getView()->addCustomStyles(2, 1) . parent::getContent('actionsAdmin');
	}
	public function showAutoActionsAdmin() {
		$assets = AssetsWsbp::_();
		$assets->loadCoreJs();
		$assets->loadAdminEndCss();
		$this->assign('pro_url', FrameWsbp::_()->getProUrl());
		return parent::getContent('autoactionsAdmin');
	}
	
	public function getEmailPreview( $params ) {
		$email = UtilsWsbp::getArrayValue($params, 'email', array(), 2);
		if (empty($email)) {
			return '';
		}
		$subject = UtilsWsbp::getArrayValue($email, 'subject');
		$message = stripslashes(UtilsWsbp::getArrayValue($email, 'message'));
		$bonusesMod = FrameWsbp::_()->getModule('bonuses');
		$bonusesView = $bonusesMod->getView();
		if (UtilsWsbp::getArrayValue($email, 'bonus_block', false, 1)) {
			$message .= $bonusesView->addCustomStyles(2, 1, true);
			$options = $bonusesMod->getWidgetOptions();
			$icon = UtilsWsbp::getArrayValue($options, 'e_icon', false);
			$message .= $bonusesView->renderUserPointsBlock($icon, true, '100 ' . $bonusesMod->getNamePlural());
		}
		if (UtilsWsbp::getArrayValue($email, 'shop_button', false, 1)) {
			$message .= $bonusesView->renderShopButton();
		}
		return $message;
	}
	public function renderActionPopup( $params, $points = 100 ) {
		$popup = UtilsWsbp::getArrayValue($params, 'popup', array(), 2);
		if (empty($popup)) {
			return '';
		}
		$title = UtilsWsbp::getArrayValue($popup, 'title');
		$message = stripslashes(UtilsWsbp::getArrayValue($popup, 'message'));
		if (!empty($title)) {
			$message = '<div class="wsbp-popup-title">' . $title . '</div>' . $message;
		}
		$bonusesMod = FrameWsbp::_()->getModule('bonuses');
		$bonusesView = $bonusesMod->getView();
		if (UtilsWsbp::getArrayValue($popup, 'bonus_block', false, 1)) {
			$message .= $bonusesView->addCustomStyles(2, 1, true);
			$options = $bonusesMod->getWidgetOptions();
			$icon = UtilsWsbp::getArrayValue($options, 'e_icon', false);
			$message .= $bonusesView->renderUserPointsBlock($icon, true, $points . ' ' . $bonusesMod->getNamePlural());
		}
		if (UtilsWsbp::getArrayValue($popup, 'shop_button', false, 1)) {
			$message .= $bonusesView->renderShopButton();
		}
		return $message;
	}
	public function getActionPopup( $userId, $actionId = 0 ) {
		$action = $this->getModule()->getModel()->getUserPopupActions($userId, $actionId, 1);
		$html = '';
		if ($action && count($action) == 1) {
			$action = $action[0];
			$params = UtilsWsbp::jsonDecode(stripslashes($action['params']));
			$popup = UtilsWsbp::getArrayValue($params, 'popup', array(), 2);
			$popup['message'] = stripslashes(base64_decode(UtilsWsbp::getArrayValue($popup, 'message')));
			$html = $this->renderActionPopup(array('popup' => $popup), $action['user_points']);
			$html = DispatcherWsbp::applyFilters('changePopupHtml', $html, $action, $popup);
			FrameWsbp::_()->getModule('bonuses')->getModel('transactions')->updateById(array('popup' => 0), $action['id']);
		}
		return $html;
	}
	
	public function renderShortcodeUsers( $attributes = array() ) {
		$module = $this->getModule();
		if (!$module->isEnableUsersWidget()) {
			return '';
		}
		$frame = FrameWsbp::_();
		$assets = AssetsWsbp::_();
		$assets->loadCoreJs(false);
		$assets->loadDataTables(array('buttons', 'responsive'));
		//$assets->loadJqueryUi();
		$assets->loadJqueryPopup();
		wp_enqueue_script('jquery-ui-datepicker', '', array('jquery'), WSBP_VERSION);
		$frame->addScript('wsbp-front-users', $module->getModPath() . 'assets/wsbp.users.js');
		$frame->addStyle('wsbp-front-bonuses', $frame->getModule('bonuses')->getModPath() . 'assets/css/wsbp.bonuses.css');
		$frame->addStyle('wsbp-front-users', $module->getModPath() . 'assets/wsbp.users.css');
		$jsData = array('wsbpNonce' => wp_create_nonce('wsbp-nonce'));
		$frame->addJSVar('wsbp-front-users', 'WSBP_FRONT', $jsData);
		$settings = array(
			'emptyTable' => esc_html__('You have no users for now.', 'wupsales-reward-points'),
			'lengthMenu' => esc_html__('Show', 'wupsales-reward-points'),
			'info' => esc_html__('Showing', 'wupsales-reward-points'),
			'btn-add' => esc_html__('Add user', 'wupsales-reward-points'),
			'btn-save' => esc_html__('Save', 'wupsales-reward-points'),
			'btn-cancel' => esc_html__('Cancel', 'wupsales-reward-points'),
			'all-label' => esc_html__('All', 'wupsales-reward-points'),
		);
		$this->assign('settings', $settings);
		
		$options = FrameWsbp::_()->getModule('bonuses')->getMainOptions();
		
		$this->assign('reasonList', $module->getModel()->getReasonList());
		$this->assign('pointsList', $module->getModel()->getPointsList());
		$this->assign('billingFields', $module->getModel('users')->getBillingFields(0));
		$this->assign('allowNewUser', UtilsWsbp::getArrayValue($options, 'e_widget_new_user', false) ? true : false);
		$this->assign('allowEditUser', UtilsWsbp::getArrayValue($options, 'e_widget_edit_user', false) ? true : false);
		return parent::getContent('widgetUsers');

	}
}
