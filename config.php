<?php
global $wpdb;
if (!defined('WPLANG') || WPLANG == '') {
	define('WSBP_WPLANG', 'en_GB');
} else {
	define('WSBP_WPLANG', WPLANG);
}
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

define('WSBP_PLUG_NAME', basename(dirname(__FILE__)));
define('WSBP_DIR', WP_PLUGIN_DIR . DS . WSBP_PLUG_NAME . DS);
define('WSBP_CLASSES_DIR', WSBP_DIR . 'classes' . DS);
define('WSBP_TABLES_DIR', WSBP_CLASSES_DIR . 'tables' . DS);
define('WSBP_HELPERS_DIR', WSBP_CLASSES_DIR . 'helpers' . DS);
define('WSBP_LANG_DIR', WSBP_DIR . 'languages' . DS);
define('WSBP_ASSETS_DIR', WSBP_DIR . 'common' . DS);
define('WSBP_IMG_DIR', WSBP_ASSETS_DIR . 'img' . DS);
define('WSBP_JS_DIR', WSBP_ASSETS_DIR . 'js' . DS);
define('WSBP_LIB_DIR', WSBP_ASSETS_DIR . 'lib' . DS);
define('WSBP_MODULES_DIR', WSBP_DIR . 'modules' . DS);
define('WSBP_ADMIN_DIR', ABSPATH . 'wp-admin' . DS);

define('WSBP_PLUGINS_URL', plugins_url());
define('WSBP_SITE_URL', get_bloginfo('wpurl') . '/');
define('WSBP_LIB_PATH', WSBP_PLUGINS_URL . '/' . WSBP_PLUG_NAME . '/common/lib/');
define('WSBP_JS_PATH', WSBP_PLUGINS_URL . '/' . WSBP_PLUG_NAME . '/common/js/');
define('WSBP_CSS_PATH', WSBP_PLUGINS_URL . '/' . WSBP_PLUG_NAME . '/common/css/');
define('WSBP_IMG_PATH', WSBP_PLUGINS_URL . '/' . WSBP_PLUG_NAME . '/common/img/');
define('WSBP_MODULES_PATH', WSBP_PLUGINS_URL . '/' . WSBP_PLUG_NAME . '/modules/');

define('WSBP_URL', WSBP_SITE_URL);

define('WSBP_LOADER_IMG', WSBP_IMG_PATH . 'loading.gif');
define('WSBP_TIME_FORMAT', 'H:i:s');
define('WSBP_DATE_DL', '/');
define('WSBP_DATE_FORMAT', 'm/d/Y');
define('WSBP_DATE_FORMAT_HIS', 'm/d/Y (' . WSBP_TIME_FORMAT . ')');
//define('WSBP_DATE_FORMAT', 'YY-MM-DD');
//define('WSBP_DATE_FORMAT_HIS', 'YY-MM-DD (' . WSBP_TIME_FORMAT . ')');
define('WSBP_DB_PREF', 'wsbp_');
define('WSBP_MAIN_FILE', 'wupsales-reward-points.php');

define('WSBP_DEFAULT', 'default');

define('WSBP_VERSION', '1.2.2');

define('WSBP_CLASS_PREFIX', 'wsbpc');
define('WSBP_TEST_MODE', true);

define('WSBP_ADMIN', 'admin');
define('WSBP_LOGGED', 'logged');
define('WSBP_GUEST', 'guest');

define('WSBP_METHODS', 'methods');
define('WSBP_USERLEVELS', 'userlevels');
/**
 * Framework instance code
 */
define('WSBP_CODE', 'wsbp');
/**
 * Plugin name
 */
define('WSBP_WP_PLUGIN_NAME', 'WupSales - Reward Points for WooCommerce');
/**
 * Custom defined for plugin
 */
define('WSBP_SHORTCODE', 'wsbp-widget');
define('WSBP_SHORTCODE_USERS', 'wsbp-widget-users');
