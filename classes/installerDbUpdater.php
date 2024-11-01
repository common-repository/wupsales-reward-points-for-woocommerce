<?php
class InstallerDbUpdaterWsbp {
	public static function runUpdate() {
		if (!DbWsbp::existsTableColumn('@__actions', 'author')) {
			DbWsbp::query( "ALTER TABLE `@__actions` ADD COLUMN `author`  int(11) NOT NULL DEFAULT '0' AFTER `conditions`" );
		}
	}
}
