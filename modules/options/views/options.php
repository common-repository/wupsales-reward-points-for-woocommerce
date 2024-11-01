<?php
class OptionsViewWsbp extends ViewWsbp {
	//private $_news = array();
	
	public function getSettingsTabContent() {
		$frame = FrameWsbp::_();
		$module = $frame->getModule('options');
		$frame->addScript('wsbp-admin-settings', $module->getModPath() . 'assets/js/admin.settings.js');
		$frame->addStyle('wsbp-admin-settings', $module->getModPath() . 'assets/css/admin.settings.css');

		$assets = AssetsWsbp::_();
		$assets->loadCoreJs();
		$assets->loadJqueryUi();
		$assets->loadAdminEndCss();
		$assets->loadChosenSelects();
		DispatcherWsbp::doAction('addOptionsAssetsContent');
	
		//$this->assign('options', $frame->getModule('options')->getAll());
		$this->assign('is_active', $module->isActiveBonusProgram());
		$this->assign('is_pro', $frame->isPro());
		$this->assign('pro_url', $frame->getProUrl());
		$this->assign('options', $module->getModel()->getAll());
		$this->assign('main_tabs', $module->getOptionsTabsList());
		$this->assign('rules_view', $module->getPageRulesUrl());
		$this->assign('rules_edit', $module->getPageRulesEditLink());
		
		$lang = array('confirm-rules' => esc_html__('Are you sure want to reset rules page?', 'wupsales-reward-points'),);
		$this->assign('lang', DispatcherWsbp::applyFilters('addLangSettings', $lang));
		return parent::getContent('optionsAdmin');
	}

	public function getHtmlOptions( $optKey, $opt ) {
		$htmlOpts = array('attrs' => 'data-optkey="' . $optKey . '"');
		$htmlType = $opt['html'];
		if (in_array($htmlType, array('selectbox', 'selectlist')) && isset($opt['options'])) {
			if (is_callable($opt['options'])) {
				$htmlOpts['options'] = call_user_func( $opt['options'] );
			} elseif (is_array($opt['options'])) {
				$htmlOpts['options'] = $opt['options'];
			}
		}
		if (in_array($htmlType, array('checkbox', 'checkboxToggle'))) {
			$htmlOpts['value'] = 1;
			$htmlOpts['checked'] = $opt['value'];
		} else {
			$htmlOpts['value'] = $opt['value'];
		}
		if (!empty($opt['classes'])) {
			$htmlOpts['attrs'] .= ' class="' . $opt['classes'] . '"';
		}
		return $htmlOpts;
	}
	public function renderPageRules( $reset = false ) {
		$page = $this->getModel()->getPageRules();
		if ($page && !$reset) {
			return true;
		}
		$args = array(
			'post_title' => 'Reward points rules',
			'post_content' => parent::getContent('rulesPageContent'),
			'post_type' => 'page',
			'post_status' => 'publish',
			'post_author' => 1,
		);
		if ($reset && $page) {
			$args['ID'] = $page->ID;
		} 
		$result = wp_insert_post($args, true);
		if (is_wp_error($result)) {
			FrameWsbp::_()->pushError($result->get_error_message());
			return false;
		}
		update_post_meta($result, $this->getModel()->rulesPageMetaKey, 1);
		return true;
	}
}
