<?php
/**
 * Plugin Name: WupSales - Reward Points for WooCommerce
 * Plugin URI: https://woobewoo.com/plugins/reward-points-for-woocommerce/
 * Description: Organization of the loyalty program of the bonus system for Woocommerce stores
 * Version: 1.2.2
 * Author: wupsales
 * Text Domain: wupsales-reward-points
 * Domain Path: /languages
 **/
/**
 * Base config constants and functions
 */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.php');
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
/**
 * Connect all required core classes
 */
importClassWsbp('DbWsbp');
importClassWsbp('InstallerWsbp');
importClassWsbp('BaseObjectWsbp');
importClassWsbp('ModuleWsbp');
importClassWsbp('ModelWsbp');
importClassWsbp('ViewWsbp');
importClassWsbp('ControllerWsbp');
importClassWsbp('HelperWsbp');
importClassWsbp('DispatcherWsbp');
importClassWsbp('FieldWsbp');
importClassWsbp('TableWsbp');
importClassWsbp('FrameWsbp');

importClassWsbp('ReqWsbp');
importClassWsbp('UriWsbp');
importClassWsbp('HtmlWsbp');
importClassWsbp('ResponseWsbp');
importClassWsbp('FieldAdapterWsbp');
importClassWsbp('ValidatorWsbp');
importClassWsbp('ErrorsWsbp');
importClassWsbp('UtilsWsbp');
importClassWsbp('ModInstallerWsbp');
importClassWsbp('InstallerDbUpdaterWsbp');
importClassWsbp('DateWsbp');
importClassWsbp('AssetsWsbp');
importClassWsbp('CacheWsbp');
importClassWsbp('UserWsbp');
/**
 * Check plugin version - maybe we need to update database, and check global errors in request
 */
InstallerWsbp::update();
ErrorsWsbp::init();
/**
 * Start application
 */
FrameWsbp::_()->parseRoute();
FrameWsbp::_()->init();
FrameWsbp::_()->exec();
