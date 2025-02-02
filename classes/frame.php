<?php
class FrameWsbp extends BaseObjectWsbp {
	private $_modules = array();
	private $_tables = array();
	private $_allModules = array();
	/**
	 * Uses to know if we are on one of the plugin pages
	 */
	private $_inPlugin = false;
	/**
	 * Array to hold all scripts and add them in one time in addScripts method
	 */
	private $_scripts = array();
	private $_scriptsInitialized = false;
	private $_styles = array();
	private $_stylesInitialized = false;
	private $_useFootAssets = false;

	private $_scriptsVars = array();
	private $_mod = '';
	private $_action = '';
	/**
	 * Object with result of executing non-ajax module request
	 */
	private $_res = null;

	public function __construct() {
		$this->_res = toeCreateObjWsbp('response', array());

	}
	public static function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new FrameWsbp();
		}
		return $instance;
	}
	public static function _() {
		return self::getInstance();
	}
	public function parseRoute() {
		// Check plugin
		$pl = ReqWsbp::getVar('pl');
		if (WSBP_CODE == $pl) {
			$mod = ReqWsbp::getMode();
			if ($mod) {
				$this->_mod = $mod;
			}
			$action = ReqWsbp::getVar('action');
			if ($action) {
				$this->_action = $action;
			}
		}
	}
	public function setMod( $mod ) {
		$this->_mod = $mod;
	}
	public function getMod() {
		return $this->_mod;
	}
	public function setAction( $action ) {
		$this->_action = $action;
	}
	public function getAction() {
		return $this->_action;
	}
	protected function _extractModules() {
		$activeModules = $this->getTable('modules')->get($this->getTable('modules')->alias() . '.*');
		if ($activeModules) {
			foreach ($activeModules as $m) {
				$code = $m['code'];
				$moduleLocationDir = WSBP_MODULES_DIR;
				if (!empty($m['ex_plug_dir'])) {
					$moduleLocationDir = UtilsWsbp::getExtModDir( $m['ex_plug_dir'] );
				}
				if (is_dir($moduleLocationDir . $code)) {
					$this->_allModules[$m['code']] = 1;
					if ((bool) $m['active']) {
						importClassWsbp($code . strFirstUpWsbp(WSBP_CODE), $moduleLocationDir . $code . DS . 'mod.php');
						$moduleClass = toeGetClassNameWsbp($code);
						if (class_exists($moduleClass)) {
							$this->_modules[$code] = new $moduleClass($m);
							if (is_dir($moduleLocationDir . $code . DS . 'tables')) {
								$this->_extractTables($moduleLocationDir . $code . DS . 'tables' . DS);
							}
						}
					}
				}
			}
		}
	}
	protected function _initModules() {
		if (!empty($this->_modules)) {
			foreach ($this->_modules as $mod) {
				 $mod->init();
			}
		}
	}
	public function init() {
		ReqWsbp::init();
		CacheWsbp::_()->init();
		
		$this->_extractTables();

		$this->_extractModules();

		$this->_initModules();

		DispatcherWsbp::doAction('afterModulesInit');

		ModInstallerWsbp::checkActivationMessages();

		$this->_execModules();
		if ($this->isSuccessInit()) {
			AssetsWsbp::_()->init();
		}

		$addAssetsAction = $this->usePackAssets() && !is_admin() ? 'wp_footer' : 'init';

		add_action($addAssetsAction, array($this, 'addScripts'));
		add_action($addAssetsAction, array($this, 'addStyles'));
		//add_action('wp_enqueue_scripts', array($this, 'addStyles'));
		global $langOK;
		register_activation_hook(WSBP_DIR . DS . WSBP_MAIN_FILE, array('UtilsWsbp', 'activatePlugin')); //See classes/install.php file
		register_uninstall_hook(WSBP_DIR . DS . WSBP_MAIN_FILE, array('UtilsWsbp', 'deletePlugin'));
		register_deactivation_hook(WSBP_DIR . DS . WSBP_MAIN_FILE, array( 'UtilsWsbp', 'deactivatePlugin' ) );

		add_action('init', array($this, 'connectLang'));
		//UtilsWsbp::setTimeZone();
	}
	public function isSuccessInit() {
		return !empty($this->_modules) && $this->getModule('options') && $this->getModule('adminmenu');
	}
	public function connectLang() {
		global $langOK;
		$langOK = load_plugin_textdomain('wupsales-reward-points', false, WSBP_PLUG_NAME . '/languages/');
	}
	/**
	 * Check permissions for action in controller by $code and made corresponding action
	 *
	 * @param string $code Code of controller that need to be checked
	 * @param string $action Action that need to be checked
	 * @return bool true if ok, else - should exit from application
	 */
	public function checkPermissions( $code, $action ) {
		//return true;
		if ($this->havePermissions($code, $action)) {
			return true;
		} else {
			exit(esc_html_e('You have no permissions to view this page', 'wupsales-reward-points'));
		}
	}
	/**
	 * Check permissions for action in controller by $code
	 *
	 * @param string $code Code of controller that need to be checked
	 * @param string $action Action that need to be checked
	 * @return bool true if ok, else - false
	 */
	public function havePermissions( $code, $action ) {
		$res = true;
		$mod = $this->getModule($code);
		$action = strtolower($action);
		if ($mod) {
			$permissions = $mod->getController()->getPermissions();
			if (!empty($permissions)) {  // Special permissions
				$user = new UserWsbp();
				if (isset($permissions[WSBP_METHODS]) && !empty($permissions[WSBP_METHODS])) {
					foreach ($permissions[WSBP_METHODS] as $method => $permissions) {   // Make case-insensitive
						$permissions[WSBP_METHODS][strtolower($method)] = $permissions;
					}
					if (array_key_exists($action, $permissions[WSBP_METHODS])) {        // Permission for this method exists
						$currentUserPosition = $user->getCurrentUserPosition();
						if ( ( is_array($permissions[ WSBP_METHODS ][ $action ] ) && !in_array($currentUserPosition, $permissions[ WSBP_METHODS ][ $action ]) )
							|| ( !is_array($permissions[ WSBP_METHODS ][ $action ]) && $permissions[WSBP_METHODS][$action] != $currentUserPosition )
						) {
							$res = false;
						}
					}
				}
				if (isset($permissions[WSBP_USERLEVELS])	&& !empty($permissions[WSBP_USERLEVELS])) {
					$currentUserPosition = $user->getCurrentUserPosition();
					// For multi-sites network admin role is undefined, let's do this here
					if (is_multisite() && is_admin() && is_super_admin()) {
						$currentUserPosition = WSBP_ADMIN;
					}
					foreach ($permissions[WSBP_USERLEVELS] as $userlevel => $methods) {
						if (is_array($methods)) {
							$lowerMethods = array_map('strtolower', $methods);          // Make case-insensitive
							if (in_array($action, $lowerMethods)) {                      // Permission for this method exists
								if ($currentUserPosition != $userlevel) {
									$res = false;
								}
								break;
							}
						} else {
							$lowerMethod = strtolower($methods);            // Make case-insensitive
							if ($lowerMethod == $action) {                   // Permission for this method exists
								if ($currentUserPosition != $userlevel) {
									$res = false;
								}
								break;
							}
						}
					}
				}
			}
			if ($res) {	// Additional check for nonces
				$noncedMethods = $mod->getController()->getNoncedMethods();
				if (!empty($noncedMethods)) {
					$noncedMethods = array_map('strtolower', $noncedMethods);
					if (in_array($action, $noncedMethods)) {
						check_ajax_referer('wsbp-nonce', 'wsbpNonce');
					}
				}
			}
		}
		return $res;
	}
	public function getRes() {
		return $this->_res;
	}
	public function execAfterWpInit() {
		$this->_doExec();
	}
	/**
	 * Check if method for module require some special permission. We can detect users permissions only after wp init action was done.
	 */
	protected function _execOnlyAfterWpInit() {
		$res = false;
		$mod = $this->getModule( $this->_mod );
		$action = strtolower( $this->_action );
		if ($mod) {
			$permissions = $mod->getController()->getPermissions();
			if (!empty($permissions)) {  // Special permissions
				if (isset($permissions[WSBP_METHODS]) && !empty($permissions[WSBP_METHODS])) {
					foreach ($permissions[WSBP_METHODS] as $method => $permissions) {   // Make case-insensitive
						$permissions[WSBP_METHODS][strtolower($method)] = $permissions;
					}
					if (array_key_exists($action, $permissions[WSBP_METHODS])) {        // Permission for this method exists
						$res = true;
					}
				}
				if (isset($permissions[WSBP_USERLEVELS])	&& !empty($permissions[WSBP_USERLEVELS])) {
					$res = true;
				}
			}
			if (!$res) {
				$noncedMethods = $mod->getController()->getNoncedMethods();
				if (!empty($noncedMethods)) {
					$noncedMethods = array_map('strtolower', $noncedMethods);
					if (in_array($action, $noncedMethods)) {
						$res = true;
					}
				}
			}
		}
		return $res;
	}
	protected function _execModules() {
		if ($this->_mod) {
			// If module exist and is active
			$mod = $this->getModule($this->_mod);
			if ($mod && !empty($this->_action)) {
				//add_action('init', array($this, 'execAfterWpInit'));
				if ($this->_execOnlyAfterWpInit()) {
					add_action('init', array($this, 'execAfterWpInit'));
				} else {
					$this->_doExec();
				}
			}
		}
	}
	protected function _doExec() {
		$mod = $this->getModule($this->_mod);
		if ($mod && $this->checkPermissions($this->_mod, $this->_action)) {
			switch (ReqWsbp::getVar('reqType')) {
				case 'ajax':
					add_action('wp_ajax_' . $this->_action, array($mod->getController(), $this->_action));
					add_action('wp_ajax_nopriv_' . $this->_action, array($mod->getController(), $this->_action));
					break;
				default:
					$this->_res = $mod->exec($this->_action);
					break;
			}
		}
	}
	protected function _extractTables( $tablesDir = WSBP_TABLES_DIR ) {
		$mDirHandle = opendir($tablesDir);
		while ( ( $file = readdir($mDirHandle) ) !== false ) {
			if ( is_file($tablesDir . $file) && ( '.' != $file ) && ( '..' != $file ) && strpos($file, '.php') ) {
				$this->_extractTable( str_replace('.php', '', $file), $tablesDir );
			}
		}
	}
	protected function _extractTable( $tableName, $tablesDir = WSBP_TABLES_DIR ) {
		importClassWsbp('noClassNameHere', $tablesDir . $tableName . '.php');
		$this->_tables[$tableName] = TableWsbp::_($tableName);
	}
	/**
	 * Public alias for _extractTables method
	 *
	 * @see _extractTables
	 */
	public function extractTables( $tablesDir ) {
		if (!empty($tablesDir)) {
			$this->_extractTables($tablesDir);
		}
	}
	public function exec() {
		//deprecated
	}
	public function getTables () {
		return $this->_tables;
	}
	/**
	 * Return table by name
	 *
	 * @param string $tableName table name in database
	 * @return object table
	 * @example FrameWsbp::_()->getTable('products')->getAll()
	 */
	public function getTable( $tableName ) {
		if (empty($this->_tables[$tableName])) {
			$this->_extractTable($tableName);
		}
		return $this->_tables[$tableName];
	}
	public function getModules( $filter = array() ) {
		$res = array();
		if (empty($filter)) {
			$res = $this->_modules;
		} else {
			foreach ($this->_modules as $code => $mod) {
				if (isset($filter['type'])) {
					if (is_numeric($filter['type']) && $filter['type'] == $mod->getTypeID()) {
						$res[$code] = $mod;
					} elseif ($filter['type'] == $mod->getType()) {
						$res[$code] = $mod;
					}
				}
			}
		}
		return $res;
	}

	public function getModule( $code ) {
		return ( isset($this->_modules[$code]) ? $this->_modules[$code] : null );
	}
	public function inPlugin() {
		return $this->_inPlugin;
	}
	public function usePackAssets() {
		if (!$this->_useFootAssets && $this->getModule('options') && $this->getModule('options')->get('foot_assets')) {
			$this->_useFootAssets = true;
		}
		return $this->_useFootAssets;
	}
	/**
	 * Push data to script array to use it all in addScripts method
	 *
	 * @see wp_enqueue_script definition
	 */
	public function addScript( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false, $vars = array() ) {
		$src = empty($src) ? $src : UriWsbp::_($src);
		if (!$ver) {
			$ver = WSBP_VERSION;
		}
		if ($this->_scriptsInitialized) {
			wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
		} else {
			$this->_scripts[] = array(
				'handle' => $handle,
				'src' => $src,
				'deps' => $deps,
				'ver' => $ver,
				'in_footer' => $in_footer,
				'vars' => $vars
			);
		}
	}
	/**
	 * Add all scripts from _scripts array to worwpfess
	 */
	public function addScripts() {
		if (!empty($this->_scripts)) {
			foreach ($this->_scripts as $s) {
				wp_enqueue_script($s['handle'], $s['src'], $s['deps'], $s['ver'], $s['in_footer']);

				if ($s['vars'] || isset($this->_scriptsVars[$s['handle']])) {
					$vars = array();
					if ($s['vars']) {
						$vars = $s['vars'];
					}
					if ($this->_scriptsVars[$s['handle']]) {
						$vars = array_merge($vars, $this->_scriptsVars[$s['handle']]);
					}
					if ($vars) {
						foreach ($vars as $k => $v) {
							wp_localize_script($s['handle'], $k, is_array($v) ? $v : array($v));
						}
					}
				}
			}
		}
		$this->_scriptsInitialized = true;
	}
	public function addJSVar( $script, $name, $val ) {
		if ($this->_scriptsInitialized) {
			wp_localize_script($script, $name, is_array($val) ? $val : array($val));
		} else {
			$this->_scriptsVars[$script][$name] = $val;
		}
	}

	public function addStyle( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
		$src = empty($src) ? $src : UriWsbp::_($src);
		if (!$ver) {
			$ver = WSBP_VERSION;
		}
		if ($this->_stylesInitialized) {
			wp_enqueue_style($handle, $src, $deps, $ver, $media);
		} else {
			$this->_styles[] = array(
				'handle' => $handle,
				'src' => $src,
				'deps' => $deps,
				'ver' => $ver,
				'media' => $media
			);
		}
	}
	public function addStyles() {
		if (!empty($this->_styles)) {
			foreach ($this->_styles as $s) {
				wp_enqueue_style($s['handle'], $s['src'], $s['deps'], $s['ver'], $s['media']);
			}
		}
		$this->_stylesInitialized = true;
	}
	//Very interesting thing going here.............
	public function loadPlugins() {
		require_once(ABSPATH . 'wp-includes/pluggable.php');
	}
	public function loadWPSettings() {
		require_once(ABSPATH . 'wp-settings.php');
	}
	public function loadLocale() {
		require_once(ABSPATH . 'wp-includes/locale.php');
	}
	public function moduleActive( $code ) {
		return isset($this->_modules[$code]);
	}
	public function moduleExists( $code ) {
		if ($this->moduleActive($code)) {
			return true;
		}
		return isset($this->_allModules[$code]);
	}
	public function isTplEditor() {
		$tplEditor = ReqWsbp::getVar('tplEditor');
		return (bool) $tplEditor;
	}
	/**
	 * This is custom method for each plugin and should be modified if you create copy from this instance.
	 */
	public function isAdminPlugOptsPage() {
		$page = ReqWsbp::getVar('page');
		if (is_admin() && !empty($page) && strpos($page, self::_()->getModule('adminmenu')->getMainSlug()) !== false) {
			return true;
		}
		return false;
	}
	public function isAdminPlugPage() {
		if ($this->isAdminPlugOptsPage()) {
			return true;
		}
		return false;
	}
	public function licenseDeactivated() {
		return ( !$this->getModule('license') && $this->moduleExists('license') );
	}
	public function savePluginActivationErrors() {
		update_option(WSBP_CODE . '_plugin_activation_errors', ob_get_contents());
	}
	public function getActivationErrors() {
		return get_option(WSBP_CODE . '_plugin_activation_errors');
	}
	public function isPro() {
		return $this->moduleExists('license') && $this->getModule('license') && $this->moduleExists('bonusespro') && $this->getModule('bonusespro');
	}
	public function getProUrl() {
		return 'https://woobewoo.com';
	}
	public function saveDebugLogging() {
		if ($this->haveErrors() && $this->getModule('bonuses')->getMainOptions('logging') == 1) {
			$logger = wc_get_logger();
			if ($logger) {
				$logger->debug(wc_print_r($this->getErrors(), true), array('source' => 'wsbp-debug-logging'));
			} 
		}
	}
}
