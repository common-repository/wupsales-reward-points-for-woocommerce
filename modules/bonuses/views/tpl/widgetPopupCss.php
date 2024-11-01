<style type="text/css" id="wspb-widget-css">
.wsbp-menu-tabs {
	display: flex;
	-ms-flex-wrap: wrap;
	flex-wrap: wrap;
	padding: 0 20px;
}
.wsbp-menu-tabs>ul {
	padding: 0;
	margin: 0;
	width: 100%;
}
.wsbp-widget-tab {
	display: inline-block;
	margin: 0;
	padding: 0;
	white-space: nowrap;
	margin: 1px -2px;
	border: none;
	padding: 5px 20px;
	border-bottom: solid #000 1px;
}
.wsbp-widget-tab-content {
	overflow: auto;
	max-height: 350px;
	padding: 0 20px;
}
.wsbp-widget-tab.current {
	border-color: red;
	color: red;
}
.wsbp-widget-tab a {
	color: inherit !important;
}
.wsbp-widget-popup a {
	outline: none !important;
	text-decoration: none;
	box-shadow: none;
	font-size: 16px;
}
.wsbp-widget-popup:not(.wsbp-info-popup) .wsbp-widget-text {
	width: 100%;
	padding-right: 30px;
}
.wsbp-widget-popup .wsbp-widget-wrapper {
	display: block;
	cursor: initial;
}
.wsbp-text-inner {
	overflow: hidden;
}
.wsbp-widget-popup .wsbp-toggle {
	position: absolute;
	right: 5px;
	font-size: 13px;
	top: 50%;
	transform: translateY(-50%);
	z-index: 102;
	color: black;
}
.wsbp-detail-block {
	padding: 5px 10px;
	background-color: #99e799;
}
.wsbp-balance-row {
	display: flex;
}
.wsbp-balance-row>div {
	padding: 0 5px;
	line-height: normal;
}
.wsbp-balance-point {
	width: 60px;
	text-align: right;
}
.wsbp-balance-info {
	padding-left: 65px;
	font-size: 10px;
	font-weight: bold;
	color: #1d2327;
}
.wsbp-block-tab {
	display: none;
	font-size: 14px;
}
.wsbp-block-tab.active {
	display: block;
}
.wsbp-trans-filter, .wsbp-settings-row {
	margin-top: 10px;
	display: flex;
}
.wsbp-trans-filter input, .wsbp-settings-row input {
	width: 100px;
	background-color: #ffffff;
	color: #000000;
	border: 1px solid #c3c3c3;
	height: 28px;
	min-height: 20px !important;
	font-size: 13px !important;
	outline: none;
	box-shadow: none;
}
.wsbp-mid-label, .wsbp-widget-label {
	margin: 0 5px;
	line-height: 25px;
}
.wsbp-important {
	color: red;
	font-style: italic;
	font-size: 11px;
}
.wsbp-widget-popup button {
	font-size: 13px;
	border-radius: 0;
	box-shadow: none;
	background: #32CD32;
	text-shadow: none;
	color: #000000;
	background-color: #ffffff;
	border: 1px solid #C4C4C4;
	height: 28px;
	min-height: 28px;
	line-height: normal;
	margin: 0 5px;
	padding: 0 10px;
}
.wsbp-widget-trans {
	width: 100%;
	border-spacing: 0 10px;
}
.wsbp-widget-trans th {
	background-color: transparent !important;
	padding: 5px;
	font-weight: bold;
	font-size: 14px;
	border-bottom: solid 1px #c1c1c1;
	cursor: pointer;
	text-align: left;
}
.wsbp-widget-trans th .ui-icon {
	width: 16px;
	height: 16px;
	display: inline-block;
	vertical-align: text-bottom;
}
.wsbp-widget-trans th .wsbp-sort-desc {
	background-position: -64px -48px;
}
.wsbp-widget-trans th .wsbp-sort-asc {
	background-position: 1px -48px;
}

table.wsbp-widget-trans tbody tr.wsbp-widget-tran td {
	font-size: 13px;
	background-color: #cff2a5;
	padding: 2px 5px;
	max-width: 120px;
}
table.wsbp-widget-trans tbody tr.wsbp-empty-trans td {
	font-size: 13px;
	background-color: #f7f7d9;
	padding: 2px 5px;
}
td.wsbp-tran-points {
	text-align: right;
}
.wsbp-widget-tran {
	padding: 0 5px;
}
.wsbp-det-order, .wsbp-det-empty, .wsbp-det-author {
	font-weight: bold;
}
.wsbp-det-points, .wsbp-det-source {
	font-size: 10px;
}
.wsbp-det-order, .wsbp-det-source {
	padding-left: 10px;
}
.wsbp-widget-buttons {
	justify-content: center;
}
.wsbp-confirm {
	background-color: green !important;
	color: #ffffff !important;
}
.wsbp-refuse {
	background-color: red !important;
	color: #ffffff !important;
}
.wsbp-user-status {
	margin: 20px 30px;
	color: #228B22;
	font-size: 16px;
	font-weight: 600;
	text-align: center;
	line-height: normal;
}
.wsbp-user-blocked, .wsbp-user-refused, .wsbp-user-agelimit {
	color: red;
}

.ui-datepicker {width: 220px !important;padding: .2em .2em 0 !important;display: none}
.ui-datepicker .ui-datepicker-header {position: relative;padding: .2em 0 !important}
.ui-datepicker .ui-datepicker-prev,.ui-datepicker .ui-datepicker-next {position: absolute;top: 2px !important;width: 1.8em !important;height: 1.8em !important}
.ui-datepicker .ui-datepicker-prev-hover,.ui-datepicker .ui-datepicker-next-hover {top: 1px !important}
.ui-datepicker .ui-datepicker-prev {left: 2px !important}
.ui-datepicker .ui-datepicker-next {right: 2px !important}
.ui-datepicker .ui-datepicker-prev-hover {left: 1px !important}
.ui-datepicker .ui-datepicker-next-hover {right: 1px !important}
.ui-datepicker .ui-datepicker-prev span,.ui-datepicker .ui-datepicker-next span {display: block;position: absolute;left: 50% !important;margin-left: -8px !important;top: 50% !important;margin-top: -8px !important}
.ui-datepicker .ui-datepicker-title {margin: 0 2em !important;line-height: 1.8em !important;text-align: center !important}
.ui-datepicker .ui-datepicker-title select {font-size: 1em !important;margin: 1px 0 !important}
.ui-datepicker select.ui-datepicker-month,.ui-datepicker select.ui-datepicker-year {width: 45% !important;min-height: 25px !important;font-weight: normal !important;font-size: 14px !important;border-color: #c5c5c5 !important;}
.ui-datepicker table {width: 100% !important;font-size: 11px !important;border-collapse: collapse !important;margin: 0 0 .4em !important}
.ui-datepicker th {padding: .7em .3em !important;text-align: center !important;font-weight: bold !important;border: 0 !important}
.ui-datepicker td {border: 0 !important;padding: 1px !important}
.ui-datepicker td span,.ui-datepicker td a {display: block;padding: .2em !important;text-align: center !important;text-decoration: none !important}
.ui-datepicker .ui-datepicker-buttonpane {background-image: none !important;margin: .7em 0 0 0 !important;padding: 0 .2em !important;border-left: 0 !important;border-right: 0 !important;border-bottom: 0 !important}
.ui-datepicker .ui-datepicker-buttonpane button {float: right;margin: .5em .2em .4em !important;cursor: pointer;padding: .2em .6em .3em .6em !important;width: auto;overflow: visible}
.ui-datepicker .ui-datepicker-buttonpane button.ui-datepicker-current {float: left}
.ui-datepicker.ui-widget-content tr {height:auto !important}
.ui-datepicker.ui-datepicker-multi {width: auto}
.ui-datepicker-multi .ui-datepicker-group {float: left}
.ui-datepicker-multi .ui-datepicker-group table {width: 95%;margin: 0 auto .4em}
.ui-datepicker-multi-2 .ui-datepicker-group {width: 50%}
.ui-datepicker-multi-3 .ui-datepicker-group {width: 33.3%}
.ui-datepicker-multi-4 .ui-datepicker-group {width: 25%}
.ui-datepicker-multi .ui-datepicker-group-last .ui-datepicker-header,.ui-datepicker-multi .ui-datepicker-group-middle .ui-datepicker-header {border-left-width: 0}
.ui-datepicker-multi .ui-datepicker-buttonpane {clear: left}
.ui-datepicker-row-break {clear: both;width: 100%;font-size: 0}
.ui-datepicker-rtl {direction: rtl}
.ui-datepicker-rtl .ui-datepicker-prev {right: 2px;left: auto}
.ui-datepicker-rtl .ui-datepicker-next {left: 2px;right: auto}
.ui-datepicker-rtl .ui-datepicker-prev:hover {right: 1px;left: auto}
.ui-datepicker-rtl .ui-datepicker-next:hover {left: 1px;right: auto}
.ui-datepicker-rtl .ui-datepicker-buttonpane {clear: right}
.ui-datepicker-rtl .ui-datepicker-buttonpane button {float: left}
.ui-datepicker-rtl .ui-datepicker-buttonpane button.ui-datepicker-current,.ui-datepicker-rtl .ui-datepicker-group {float: right}
.ui-datepicker-rtl .ui-datepicker-group-last .ui-datepicker-header,.ui-datepicker-rtl .ui-datepicker-group-middle .ui-datepicker-header {border-right-width: 0;border-left-width: 1px}
.ui-datepicker .ui-icon {display: block;text-indent: -99999px;overflow: hidden;background-repeat: no-repeat;left: .5em;top: .3em}
.ui-datepicker .ui-widget {font-family: Arial,Helvetica,sans-serif !important;font-size: 1em !important;}
.ui-datepicker .ui-widget .ui-widget {font-size: 1em !important;}
.ui-datepicker .ui-widget input,.ui-widget select,.ui-widget textarea,.ui-widget button {font-family: Arial,Helvetica,sans-serif;font-size: 1em}
.ui-datepicker.ui-widget-content {border: 1px solid #ddd !important;background: #fff !important;color: #333 !important;}
.ui-datepicker .ui-widget-header {border: 1px solid #ddd !important;background: #e9e9e9 !important;color: #333 !important;font-weight: bold !important;}
.ui-datepicker .ui-state-default,
.ui-datepicker .ui-button,
.ui-datepicker .ui-button.ui-state-disabled:hover,
.ui-datepicker .ui-button.ui-state-disabled:active {border: 1px solid #c5c5c5 !important;background: #f6f6f6 !important;font-weight: normal !important;color: #454545 !important;}
.ui-datepicker .ui-state-default a,
.ui-datepicker .ui-state-default a:link,
.ui-datepicker .ui-state-default a:visited,
.ui-datepicker a.ui-button,a:link.ui-button,
.ui-datepicker a:visited.ui-button,.ui-button {color: #454545 !important;text-decoration: none !important;}
.ui-datepicker .ui-state-hover,
.ui-datepicker .ui-state-focus,
.ui-datepicker .ui-button:hover,.ui-datepicker .ui-button:focus {border: 1px solid #ccc !important;background: #ededed !important;font-weight: normal !important;color: #2b2b2b !important;}
.ui-datepicker .ui-state-hover a,
.ui-datepicker .ui-state-hover a:hover,
.ui-datepicker .ui-state-hover a:link,
.ui-datepicker .ui-state-hover a:visited,
.ui-datepicker .ui-state-focus a,
.ui-datepicker .ui-state-focus a:hover,
.ui-datepicker .ui-state-focus a:link,
.ui-datepicker .ui-state-focus a:visited,
.ui-datepicker a.ui-button:hover,
.ui-datepicker a.ui-button:focus {color: #2b2b2b !important;text-decoration: none !important;}
.ui-datepicker .ui-state-active,
.ui-datepicker a.ui-button:active,
.ui-datepicker .ui-button:active,
.ui-datepicker .ui-button.ui-state-active:hover {border: 1px solid #003eff !important;background: #007fff !important;font-weight: normal !important;color: #fff !important;}
.ui-datepicker .ui-icon-background {border: #003eff !important;background-color: #fff !important;}
.ui-datepicker .ui-state-highlight {border: 1px solid #dad55e !important;background: #fffa90;color: #777620 !important;}
.ui-datepicker .ui-icon {width: 16px;height: 16px !important;}
.wsbp-dp.ui-datepicker .ui-icon-circle-triangle-e{background-position:-48px -192px !important;}
.ui-datepicker .ui-icon-circle-triangle-w {background-position: -80px -192px !important;}
.ui-datepicker .ui-corner-all{border-radius: 3px; !important;}
<?php
if ($this->is_pro) {
	DispatcherWsbp::doAction('bonusesIncludeTpl', 'widgetPopupCss', array('user' => $this->user, 'options' => $this->options));
}
?>
</style>
