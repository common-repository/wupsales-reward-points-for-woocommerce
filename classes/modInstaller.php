<?php
class ModInstallerWsbp {
	private static $_current = array();
	/**
	 * Install new ModuleWsbp into plugin
	 *
	 * @param string $module new ModuleWsbp data (@see classes/tables/modules.php)
	 * @param string $path path to the main plugin file from what module is installed
	 * @return bool true - if install success, else - false
	 */
	public static function install( $module, $path ) {
		$exPlugDest = explode('plugins', $path);
		if (!empty($exPlugDest[1])) {
			$module['ex_plug_dir'] = str_replace(DS, '', $exPlugDest[1]);
		}
		$path = $path . DS . $module['code'];
		if (!empty($module) && !empty($path) && is_dir($path)) {
			if (self::isModule($path)) {
				$filesMoved = false;
				if (empty($module['ex_plug_dir'])) {
					$filesMoved = self::moveFiles($module['code'], $path);
				} else {
					$filesMoved = true;     //Those modules doesn't need to move their files
				}
				if ($filesMoved) {
					if (FrameWsbp::_()->getTable('modules')->exists($module['code'], 'code')) {
						FrameWsbp::_()->getTable('modules')->delete(array('code' => $module['code']));
					}
					if ('license' != $module['code']) {
						$module['active'] = 0;
					}
					FrameWsbp::_()->getTable('modules')->insert($module);
					self::_runModuleInstall($module);
					self::_installTables($module);
					return true;
				} else {
					/* translators: %s: module name */
					ErrorsWsbp::push(esc_html(sprintf(__('Move files for %s failed'), $module['code'])), ErrorsWsbp::MOD_INSTALL);
				}
			} else {
				/* translators: %s: module name */
				ErrorsWsbp::push(esc_html(sprintf(__('%s is not plugin module'), $module['code'])), ErrorsWsbp::MOD_INSTALL);
			}
		}
		return false;
	}
	protected static function _runModuleInstall( $module, $action = 'install' ) {
		$moduleLocationDir = WSBP_MODULES_DIR;
		if (!empty($module['ex_plug_dir'])) {
			$moduleLocationDir = UtilsWsbp::getPluginDir( $module['ex_plug_dir'] );
		}
		if (is_dir($moduleLocationDir . $module['code'])) {
			if (!class_exists($module['code'] . strFirstUpWsbp(WSBP_CODE))) {
				importClassWsbp($module['code'] . strFirstUpWsbp(WSBP_CODE), $moduleLocationDir . $module['code'] . DS . 'mod.php');
			}
			$moduleClass = toeGetClassNameWsbp($module['code']);
			$moduleObj = new $moduleClass($module);
			if ($moduleObj) {
				$moduleObj->$action();
			}
		}
	}
	/**
	 * Check whether is or no module in given path
	 *
	 * @param string $path path to the module
	 * @return bool true if it is module, else - false
	 */
	public static function isModule( $path ) {
		return true;
	}
	/**
	 * Move files to plugin modules directory
	 *
	 * @param string $code code for module
	 * @param string $path path from what module will be moved
	 * @return bool is success - true, else - false
	 */
	public static function moveFiles( $code, $path ) {
		if (!is_dir(WSBP_MODULES_DIR . $code)) {
			if (mkdir(WSBP_MODULES_DIR . $code)) {
				UtilsWsbp::copyDirectories($path, WSBP_MODULES_DIR . $code);
				return true;
			} else {
				ErrorsWsbp::push(esc_html__('Cannot create module directory. Try to set permission to ' . WSBP_MODULES_DIR . ' directory 755 or 777', 'wupsales-reward-points'), ErrorsWsbp::MOD_INSTALL);
			}
		} else {
			return true;
		}
		return false;
	}
	private static function _getPluginLocations() {
		$locations = array();
		$plug = ReqWsbp::getVar('plugin');
		if (empty($plug)) {
			$plug = ReqWsbp::getVar('checked');
			$plug = $plug[0];
		}
		$locations['plugPath'] = plugin_basename( trim( $plug ) );
		$locations['plugDir'] = dirname(WP_PLUGIN_DIR . DS . $locations['plugPath']);
		$locations['plugMainFile'] = WP_PLUGIN_DIR . DS . $locations['plugPath'];
		$locations['xmlPath'] = $locations['plugDir'] . DS . 'install.xml';
		return $locations;
	}
	private static function _getModulesFromXml( $xmlPath ) {
		$xml = UtilsWsbp::getXml($xmlPath);
		if ($xml) {
			if (isset($xml->modules) && isset($xml->modules->mod)) {
				$modules = array();
				$xmlMods = $xml->modules->children();
				foreach ($xmlMods->mod as $mod) {
					$modules[] = $mod;
				}
				if (empty($modules)) {
					ErrorsWsbp::push(esc_html__('No modules were found in XML file', 'wupsales-reward-points'), ErrorsWsbp::MOD_INSTALL);
				} else {
					return $modules;
				}
			} else {
				ErrorsWsbp::push(esc_html__('Invalid XML file', 'wupsales-reward-points'), ErrorsWsbp::MOD_INSTALL);
			}
		} else {
			ErrorsWsbp::push(esc_html__('No XML file were found', 'wupsales-reward-points'), ErrorsWsbp::MOD_INSTALL);
		}
		return false;
	}
	/**
	 * Check whether modules is installed or not, if not and must be activated - install it
	 *
	 * @param array $codes array with modules data to store in database
	 * @param string $path path to plugin file where modules is stored (__FILE__ for example)
	 * @return bool true if check ok, else - false
	 */
	public static function check( $extPlugName = '' ) {
		if (WSBP_TEST_MODE) {
			add_action('activated_plugin', array(FrameWsbp::_(), 'savePluginActivationErrors'));
		}
		$locations = self::_getPluginLocations();
		$modules = self::_getModulesFromXml($locations['xmlPath']);
		if ($modules) {
			foreach ($modules as $m) {
				$modDataArr = UtilsWsbp::xmlNodeAttrsToArr($m);
				if (!empty($modDataArr)) {
					//If module Exists - just activate it, we can't check this using FrameWsbp::moduleExists because this will not work for multy-site WP
					if (FrameWsbp::_()->getTable('modules')->exists($modDataArr['code'], 'code')) {
						self::activate($modDataArr);
					} else {                                           //  if not - install it
						if (!self::install($modDataArr, $locations['plugDir'])) {
							/* translators: %s: module name */
							ErrorsWsbp::push(esc_html(sprintf(__('Install %s failed'), $modDataArr['code'])), ErrorsWsbp::MOD_INSTALL);
						}
					}
				}
			}
		} else {
			ErrorsWsbp::push(esc_html__('Error Activate module', 'wupsales-reward-points'), ErrorsWsbp::MOD_INSTALL);
		}
		if (ErrorsWsbp::haveErrors(ErrorsWsbp::MOD_INSTALL)) {
			self::displayErrors(false);
			return false;
		}
		update_option(WSBP_CODE . '_full_installed', 1);
		return true;
	}
	/**
	 * Public alias for _getCheckRegPlugs()
	 * We will run this each time plugin start to check modules activation messages
	 */
	public static function checkActivationMessages() {

	}
	/**
	 * Deactivate module after deactivating external plugin
	 */
	public static function deactivate() {
		$locations = self::_getPluginLocations();
		$modules = self::_getModulesFromXml($locations['xmlPath']);
		if ($modules) {
			foreach ($modules as $m) {
				$modDataArr = UtilsWsbp::xmlNodeAttrsToArr($m);
				if (FrameWsbp::_()->moduleActive($modDataArr['code'])) { //If module is active - then deacivate it
					if (FrameWsbp::_()->getModule('adminmenu')->getModel('modules')->put(array(
						'id' => FrameWsbp::_()->getModule($modDataArr['code'])->getID(),
						'active' => 0,
					))->error) {
						ErrorsWsbp::push(esc_html__('Error Deactivation module', 'wupsales-reward-points'), ErrorsWsbp::MOD_INSTALL);
					}
				}
			}
		}
		if (ErrorsWsbp::haveErrors(ErrorsWsbp::MOD_INSTALL)) {
			self::displayErrors(false);
			return false;
		}
		wp_clear_scheduled_hook('wsbp_do_users_actions');
		return true;
	}
	public static function activate( $modDataArr ) {
		$locations = self::_getPluginLocations();
		$modules = self::_getModulesFromXml($locations['xmlPath']);
		if ($modules) {
			foreach ($modules as $m) {
				$modDataArr = UtilsWsbp::xmlNodeAttrsToArr($m);
				if (!FrameWsbp::_()->moduleActive($modDataArr['code']) && 'license' == $modDataArr['code']) { //If module is not active - then acivate it
					if (FrameWsbp::_()->getModule('adminmenu')->getModel('modules')->put(array(
						'code' => $modDataArr['code'],
						'active' => 1,
					))->error) {
						ErrorsWsbp::push(esc_html__('Error Activating module', 'wupsales-reward-points'), ErrorsWsbp::MOD_INSTALL);
					} else {
						$dbModData = FrameWsbp::_()->getModule('adminmenu')->getModel('modules')->get(array('code' => $modDataArr['code']));
						if (!empty($dbModData) && !empty($dbModData[0])) {
							$modDataArr['ex_plug_dir'] = $dbModData[0]['ex_plug_dir'];
						}
						self::_runModuleInstall($modDataArr, 'activate');
					}
				}
			}
		}
	} 
	/**
	 * Display all errors for module installer, must be used ONLY if You realy need it
	 */
	public static function displayErrors( $exit = true ) {
		$errors = ErrorsWsbp::get(ErrorsWsbp::MOD_INSTALL);
		foreach ($errors as $e) {
			echo '<b class="wupsales-error">' . esc_html($e) . '</b><br />';
		}
		if ($exit) {
			exit();
		}
	}
	public static function uninstall() {
		$locations = self::_getPluginLocations();
		$modules = self::_getModulesFromXml($locations['xmlPath']);
		if ($modules) {
			foreach ($modules as $m) {
				$modDataArr = UtilsWsbp::xmlNodeAttrsToArr($m);
				self::_uninstallTables($modDataArr);
				FrameWsbp::_()->getModule('adminmenu')->getModel('modules')->delete(array('code' => $modDataArr['code']));
				UtilsWsbp::deleteDir(WSBP_MODULES_DIR . $modDataArr['code']);
				if ('license' == $modDataArr['code']) {
					FrameWsbp::_()->getModule('options')->getModel()->save('lic', 'license_save_name', '');
				}
			}
		}
	}
	protected static function _uninstallTables( $module ) {
		if (is_dir(WSBP_MODULES_DIR . $module['code'] . DS . 'tables')) {
			$tableFiles = UtilsWsbp::getFilesList(WSBP_MODULES_DIR . $module['code'] . DS . 'tables');
			if (!empty($tableNames)) {
				foreach ($tableFiles as $file) {
					$tableName = str_replace('.php', '', $file);
					if (FrameWsbp::_()->getTable($tableName)) {
						FrameWsbp::_()->getTable($tableName)->uninstall();
					}
				}
			}
		}
	}
	public static function _installTables( $module, $action = 'install' ) {
		$modDir = empty($module['ex_plug_dir']) ? WSBP_MODULES_DIR . $module['code'] . DS : UtilsWsbp::getPluginDir($module['ex_plug_dir']) . $module['code'] . DS; 
		if (is_dir($modDir . 'tables')) {
			$tableFiles = UtilsWsbp::getFilesList($modDir . 'tables');
			if (!empty($tableFiles)) {
				FrameWsbp::_()->extractTables($modDir . 'tables' . DS);
				foreach ($tableFiles as $file) {
					$tableName = str_replace('.php', '', $file);
					if (FrameWsbp::_()->getTable($tableName)) {
						FrameWsbp::_()->getTable($tableName)->$action();
					}
				}
			}
		}
	}
}
