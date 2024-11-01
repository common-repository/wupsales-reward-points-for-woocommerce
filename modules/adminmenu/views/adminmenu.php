<?php
class AdminmenuViewWsbp extends ViewWsbp {
	public function getAdminPage() {
		$tabs = $this->getModule()->getTabs();
		$activeTab = $this->getModule()->getActiveTab();
		$content = 'No tab content found - ERROR';
		if (isset($tabs[ $activeTab ]) && isset($tabs[ $activeTab ]['callback'])) {
			$content = call_user_func($tabs[ $activeTab ]['callback']);
		}
		$activeParentTabs = array();
		foreach ($tabs as $tabKey => $tab) {
			if ($tabKey == $activeTab && isset($tab['child_of'])) {
				$activeTab = $tab['child_of'];
			}
		}
		FrameWsbp::_()->addJSVar('adminOptionsWsbp', 'wsbpActiveTab', $activeTab);
		$this->assign('tabs', $tabs);
		$this->assign('activeTab', $activeTab);
		$this->assign('content', $content);
		$this->assign('mainUrl', $this->getModule()->getTabUrl());
		$this->assign('activeParentTabs', $activeParentTabs);

		FrameWsbp::_()->addJSVar('adminCreateTableWsbp', 'url', admin_url('admin-ajax.php'));

		parent::display('adminNavPage');
	}

	public function displayAdminFooter() {
		parent::display('adminFooter');
	}
}
