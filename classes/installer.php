<?php
class InstallerWsbp {
	public static $update_to_version_method = '';
	private static $_firstTimeActivated = false;
	public static function init( $isUpdate = false ) {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Versiom */
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$current_version = get_option($wpPrefix . WSBP_DB_PREF . 'db_version', 0);
		if (!$current_version) {
			self::$_firstTimeActivated = true;
		}
		/**
		 * Table modules 
		 */
		if (!DbWsbp::exist('@__modules')) {
			dbDelta(DbWsbp::prepareQuery("CREATE TABLE IF NOT EXISTS `@__modules` (
			  `id` smallint(3) NOT NULL AUTO_INCREMENT,
			  `code` varchar(32) NOT NULL,
			  `active` tinyint(1) NOT NULL DEFAULT '0',
			  `type_id` tinyint(1) NOT NULL DEFAULT '0',
			  `label` varchar(64) DEFAULT NULL,
			  `ex_plug_dir` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `code` (`code`)
			) DEFAULT CHARSET=utf8;"));
			DbWsbp::query("INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES
				(NULL, 'adminmenu',1,1,'Admin Menu'),
				(NULL, 'options',1,1,'Options'),
				(NULL, 'bonuses',1,1,'Bonuses'),
				(NULL, 'bonuses_widget',1,1,'Widget'),
				(NULL, 'actions',1,1,'Actions');");
		}
		
		/**
		 * Table products
		 */
		if (!DbWsbp::exist('@__products')) {
			dbDelta(DbWsbp::prepareQuery("CREATE TABLE IF NOT EXISTS `@__products` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`point` varchar(32) DEFAULT NULL,
				`parent` int(11) NOT NULL DEFAULT '0',
				`price` decimal(10,2) NOT NULL DEFAULT '0',
				`pr_points` decimal(10,2) DEFAULT NULL,
				`gr_num` int(11) DEFAULT NULL,
				`gr_points` decimal(10,2) DEFAULT NULL,
				`points` decimal(10,2) DEFAULT NULL,
				`calculated` datetime NOT NULL,
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;"));
		}
		/**
		 * Table users
		 */
		if (!DbWsbp::exist('@__users')) {
			dbDelta(DbWsbp::prepareQuery("CREATE TABLE IF NOT EXISTS `@__users` (
				`id` int(11) NOT NULL,
				`status` tinyint(1) NOT NULL DEFAULT '1',
				`birthday` int(11) NOT NULL DEFAULT '0',
				`bd` varchar (5) NOT NULL DEFAULT '',
				`bd_updated` datetime DEFAULT NULL,
				`points` decimal(10,2) NOT NULL DEFAULT '0',
				`cart` decimal(10,2) NOT NULL DEFAULT '0',
				`total_amount` decimal(10,2) NOT NULL DEFAULT '0',
				`total_count` int(11) NOT NULL DEFAULT '0',
				`last_order` datetime,
				`calculated` datetime NOT NULL,
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;"));
		}
		/**
		 * Table actions
		 */
		if (!DbWsbp::exist('@__actions')) {
			dbDelta(DbWsbp::prepareQuery("CREATE TABLE IF NOT EXISTS `@__actions` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`reason` varchar(100) NULL DEFAULT NULL,
				`tr_type` tinyint(1) NOT NULL DEFAULT '0',
				`points` decimal(10,2) NOT NULL DEFAULT '0',
				`act_date` int(11) NOT NULL,
				`end_date` int(11) NULL DEFAULT NULL,
				`exp_date` int(11) NULL DEFAULT NULL,
				`completed` int(11) NULL DEFAULT NULL,
				`cnt_users` int(11) NOT NULL DEFAULT '0',
				`status` tinyint(1) NOT NULL DEFAULT '0',
				`triger` varchar(10) NULL DEFAULT NULL,
				`params` text,
				`conditions` text,
				PRIMARY KEY (`id`),
				INDEX (`tr_type`, `status`, `triger`)
			) DEFAULT CHARSET=utf8;"));
		}
		/**
		 * Table transations
		 */
		if (!DbWsbp::exist('@__transactions')) {
			dbDelta(DbWsbp::prepareQuery("CREATE TABLE IF NOT EXISTS `@__transactions` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`user_id` int(11) NOT NULL DEFAULT '0',
				`tr_type` tinyint(1) NOT NULL DEFAULT '0',
				`points` decimal(10,2) NOT NULL DEFAULT '0',
				`rest` decimal(10,2) NOT NULL DEFAULT '0',
				`created` datetime NOT NULL,
				`exp_date` int(11) DEFAULT NULL,
				`op_id` int(11) NOT NULL DEFAULT '0',
				`uniq` int(11) NOT NULL DEFAULT '0',
				`email` tinyint(1) NOT NULL DEFAULT '0',
				`popup` tinyint(1) NOT NULL DEFAULT '0',
				`status` tinyint(1) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`),
				UNIQUE INDEX `user_op` (`user_id`, `op_id`, `tr_type`, `uniq`)
			) DEFAULT CHARSET=utf8;"));
		}
		/**
		 * Table details
		 */
		if (!DbWsbp::exist('@__details')) {
			dbDelta(DbWsbp::prepareQuery("CREATE TABLE IF NOT EXISTS `@__details` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`tr_id` int(11) NOT NULL DEFAULT '0',
				`source` tinyint(1) NOT NULL DEFAULT '0',
				`source_id` int(11) NOT NULL DEFAULT '0',
				`pur_sum` decimal(10,2) NOT NULL DEFAULT '0',
				`pur_cur` varchar(3) NOT NULL DEFAULT '',
				`pur_cnt` int(11) NOT NULL DEFAULT '0',
				`points` decimal(10,2) NOT NULL DEFAULT '0',
				`conditions` text,
				PRIMARY KEY (`id`),
				INDEX (`tr_id`)
			) DEFAULT CHARSET=utf8;"));
		}
		InstallerDbUpdaterWsbp::runUpdate();
		if ($current_version && !self::$_firstTimeActivated) {
			self::setUsed();
		}
		update_option($wpPrefix . WSBP_DB_PREF . 'db_version', WSBP_VERSION);
		add_option($wpPrefix . WSBP_DB_PREF . 'db_installed', 1);
		if ( !wp_next_scheduled('wsbp_calc_products_points')) {
			wp_schedule_single_event( time() + 3, 'wsbp_calc_products_points' );
		}
		if ( !wp_next_scheduled('wsbp_calc_users_balance')) {
			wp_schedule_single_event( time() + 5, 'wsbp_calc_users_balance' );
		}
	}
	public static function setUsed() {
		update_option(WSBP_DB_PREF . 'plug_was_used', 1);
	}
	public static function isUsed() {
		return (int) get_option(WSBP_DB_PREF . 'plug_was_used');
	}
	public static function delete() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;
		$wpdb->query('DROP TABLE IF EXISTS `' . $wpdb->prefix . esc_sql(WSBP_DB_PREF) . 'modules`');
		delete_option($wpPrefix . WSBP_DB_PREF . 'db_version');
		delete_option($wpPrefix . WSBP_DB_PREF . 'db_installed');
	}
	public static function deactivate() {
		wp_clear_scheduled_hook('wsbp_calc_products_points');
		wp_clear_scheduled_hook('wsbp_calc_users_balance');
		wp_clear_scheduled_hook('wsbp_do_users_actions');
		FrameWsbp::_()->getModule('options')->getModel()->activateBonusProgram(0);
	}
	public static function update() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Version */
		$currentVersion = get_option($wpPrefix . WSBP_DB_PREF . 'db_version', 0);
		if (!$currentVersion || version_compare(WSBP_VERSION, $currentVersion, '>')) {
			self::init( true );
			update_option($wpPrefix . WSBP_DB_PREF . 'db_version', WSBP_VERSION);
		}
	}
}
