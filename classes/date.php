<?php
class DateWsbp {
	public static function _( $time = null ) {
		if (is_null($time)) {
			$time = time();
		}
		return gmdate(WSBP_DATE_FORMAT_HIS, $time);
	}

}
