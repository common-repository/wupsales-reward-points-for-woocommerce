<?php
class AdminmenuWsbp extends ModuleWsbp {
	private $_tabs = array();
	protected $_mainSlug = 'wsbp';
	private $_mainCap = 'manage_options';

	public function init() {
		parent::init();
		add_action('admin_menu', array($this, 'initMenu'), 9);
		$plugName = plugin_basename(WSBP_DIR . WSBP_MAIN_FILE);
		add_filter('plugin_action_links_' . $plugName, array($this, 'addSettingsLinkForPlug') );
		//add_action('admin_footer', array($this, 'displayAdminFooter'), 9);
		add_action('admin_notices', array($this, 'checkAdminPromoNotices'));
	}
	public function addSettingsLinkForPlug( $links ) {
		$mainLink = 'https://woobewoo.com/';
		/* translators: %s: plugin name */
		$twitterStatus = sprintf(esc_html__('Cool WordPress plugins from woobewoo.com developers. I tried %s - and this was what I need! #woobewoo.com', 'wupsales-reward-points'), WSBP_WP_PLUGIN_NAME);
		array_unshift($links, '<a href="' . esc_url($this->getMainLink()) . '">' . esc_html__('Settings', 'wupsales-reward-points') . '</a>');
		array_push($links, '<a title="' . esc_attr__('More plugins for your WordPress site here!', 'wupsales-reward-points') . '" href="' . esc_url($mainLink) . '" target="_blank">woobewoo.com</a>');
		return $links;
	}
	public function initMenu() {
		$mainCap = $this->getMainCap();
		$mainSlug = DispatcherWsbp::applyFilters('adminMenuMainSlug', $this->_mainSlug);
		$mainMenuPageOptions = array(
			'page_title' => WSBP_WP_PLUGIN_NAME, 
			'menu_title' => 'Reward Points', 
			'capability' => $mainCap,
			'menu_slug' => $mainSlug,
			'function' => array($this, 'getAdminPage'));
		$mainMenuPageOptions = DispatcherWsbp::applyFilters('adminMenuMainOption', $mainMenuPageOptions);

		add_menu_page($mainMenuPageOptions['page_title'], $mainMenuPageOptions['menu_title'], $mainMenuPageOptions['capability'], $mainMenuPageOptions['menu_slug'], $mainMenuPageOptions['function'], 'dashicons-list-view');
		$tabs = $this->getTabs();
		$subMenus = array();
		foreach ($tabs as $tKey => $tab) {
			if ('main_page' == $tKey) {
				continue;	// Top level menu item - is main page, avoid place it 2 times
			}
			if ( ( isset($tab['hidden']) && $tab['hidden'] )
				|| ( isset($tab['hidden_for_main']) && $tab['hidden_for_main'] )	// Hidden for WP main
				|| ( isset($tab['is_main']) && $tab['is_main'] ) ) {
				continue;
			}
			$slug = 'admin.php?page=' . $mainSlug . '&tab=' . $tKey;
			if ('tables' == $tKey) {
				array_unshift($subMenus, array('title' => esc_html__('All Tables', 'wupsales-reward-points'), 'capability' => $mainCap, 'menu_slug' => $slug, 'function' => ''));
			} else {
				$subMenus[] = array('title' => $tab['label'], 'capability' => $mainCap, 'menu_slug' => $slug, 'function' => '');
			}
		}
		$subMenus = DispatcherWsbp::applyFilters('adminMenuOptions', $subMenus);
		foreach ($subMenus as $opt) {
			add_submenu_page($mainSlug, $opt['title'], $opt['title'], $opt['capability'], $opt['menu_slug'], $opt['function']);
		}
		//remove duplicated WP menu item
		remove_submenu_page($mainSlug, $mainSlug);
	}
	public function getMainLink() {
		return UriWsbp::_(array('baseUrl' => admin_url('admin.php'), 'page' => $this->getMainSlug()));
	}
	public function getMainSlug() {
		return $this->_mainSlug;
	}
	public function getMainCap() {
		return DispatcherWsbp::applyFilters('adminMenuAccessCap', $this->_mainCap);
	}
	public function getPluginLinkPro() {
		return 'https://woobewoo.com/plugins/reward-points-for-woocommerce/' ;
	}
	public function generateMainLink( $params = '' ) {
		$mainLink = $this->getMainLink();
		if (!empty($params)) {
			return $mainLink . ( strpos($mainLink , '?') ? '&' : '?' ) . $params;
		}
		return $mainLink;
	}
	public function getAdminPage() {
		if (!InstallerWsbp::isUsed()) {
			InstallerWsbp::setUsed();
		}
		return $this->getView()->getAdminPage();
	}
	public function checkAdminPromoNotices() {
		return;
		if (!FrameWsbp::_()->isAdminPlugOptsPage()) {
			return;
		}
		$notices = array();
		$moduleOptions = FrameWsbp::_()->getModule('options');
		// Start usage
		$startUsage = (int) $moduleOptions->get('start_usage');
		$currTime = time();
		$day = 24 * 3600;
		if ($startUsage) {	// Already saved
			/* translators: %s: label */
			$rateMsg = '<h3>' . esc_html(sprintf(__('Hey, I noticed you just use %s over a week – that’s awesome!', 'wupsales-reward-points'), WSBP_WP_PLUGIN_NAME)) . '</h3><p>' .
				esc_html__('Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.', 'wupsales-reward-points') . '</p>';
			$rateMsg .= '<p><a href="https://wordpress.org/support/plugin/wupsales-reward-points/reviews/?rate=5#new-post" target="_blank" class="button button-primary" data-statistic-code="done">' .
				esc_html__('Ok, you deserve it', 'wupsales-reward-points') . '</a>
				<a href="#" class="button" data-statistic-code="later">' . esc_html__('Nope, maybe later', 'wupsales-reward-points') . '</a>
				<a href="#" class="button" data-statistic-code="hide">' . esc_html__('I already did', 'wupsales-reward-points') . '</a></p>';
			$notices = array(
				'rate_msg' => array('html' => $rateMsg, 'show_after' => 7 * $day),
			);
			foreach ($notices as $nKey => $n) {
				if ($currTime - $startUsage <= $n['show_after']) {
					unset($notices[ $nKey ]);
					continue;
				}
				$done = (int) $moduleOptions->get('done_' . $nKey);
				if ($done) {
					unset($notices[ $nKey ]);
					continue;
				}
				$hide = (int) $moduleOptions->get('hide_' . $nKey);
				if ($hide) {
					unset($notices[ $nKey ]);
					continue;
				}
				$later = (int) $moduleOptions->get('later_' . $nKey);
				if ( $later && ( $currTime - $later ) <= 2 * $day ) {	// remember each 2 days
					unset($notices[ $nKey ]);
					continue;
				}
			}
		} else {
			$moduleOptions->getModel()->save('start_usage', $currTime);
		}
		if (!empty($notices)) {
			$html = '';
			foreach ($notices as $nKey => $n) {
				$html .= '<div class="updated notice is-dismissible wupsales-admin-notice" data-code="' . $nKey . '">' . $n['html'] . '</div>';
			}
			HtmlWsbp::echoEscapedHtml($html);
		}
	}
	public function displayAdminFooter() {
		if (FrameWsbp::_()->isAdminPlugPage()) {
			$this->getView()->displayAdminFooter();
		}
	}
	
	public function getTabs() {
		if (empty($this->_tabs)) {
			$this->_tabs = DispatcherWsbp::applyFilters('mainAdminTabs', array(
				// example: 'main_page' => array('label' => esc_html__('Main Page', 'wupsales-reward-points'), 'callback' => array($this, 'getTabContent'), 'wp_icon' => 'dashicons-admin-home', 'sort_order' => 0),
			));
			foreach ($this->_tabs as $tabKey => $tab) {
				if (!isset($this->_tabs[ $tabKey ]['url'])) {
					$this->_tabs[ $tabKey ]['url'] = $this->getTabUrl( $tabKey );
				}
			}
			uasort($this->_tabs, array($this, 'sortTabsClb'));
		}
		return $this->_tabs;
	}
	public function sortTabsClb( $a, $b ) {
		if (isset($a['sort_order']) && isset($b['sort_order'])) {
			if ($a['sort_order'] > $b['sort_order']) {
				return 1;
			}
			if ($a['sort_order'] < $b['sort_order']) {
				return -1;
			}
		}
		return 0;
	}
	public function getTab( $tabKey ) {
		$this->getTabs();
		return isset($this->_tabs[ $tabKey ]) ? $this->_tabs[ $tabKey ] : false;
	}
	public function getTabContent() {
		return $this->getView()->getTabContent();
	}
	public function getActiveTab() {
		$reqTab = sanitize_text_field(ReqWsbp::getVar('tab'));
		return empty($reqTab) ? 'tables' : $reqTab;
	}
	public function getTabUrl( $tab = '' ) {
		static $mainUrl;
		if (empty($mainUrl)) {
			$mainUrl = FrameWsbp::_()->getModule('adminmenu')->getMainLink();
		}
		return empty($tab) ? $mainUrl : $mainUrl . '&tab=' . $tab;
	}

	public function getEditLink( $id, $code ) {
		$link = $this->getTabUrl( $code . '-edit' ) . '&id=' . $id;
		return $link;
	}

}
