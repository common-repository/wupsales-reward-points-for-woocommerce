<?php
class AssetsWsbp {
	protected $_styles = array();
	private $_cdnUrl = '';

	public function init() {
		$this->getCdnUrl();
		if (is_admin()) {
			$isAdminPlugOptsPage = FrameWsbp::_()->isAdminPlugOptsPage();
			if ($isAdminPlugOptsPage) {
				$this->loadAdminCoreJs();
				$this->loadCoreCss();
				$this->loadBootstrap();
				$this->loadFontAwesome();
				$this->loadJqueryUi();
				FrameWsbp::_()->addScript('wsbp-admin-options', WSBP_JS_PATH . 'admin.options.js', array(), false, true);
				add_action('admin_enqueue_scripts', array($this, 'loadMediaScripts'));
				add_action('init', array($this, 'connectAdditionalAdminAssets'));
				// Some common styles - that need to be on all admin pages - be careful with them
				FrameWsbp::_()->addStyle('wupsales-for-all-admin-' . WSBP_CODE, WSBP_CSS_PATH . 'wupsales-for-all-admin.css');
			}
		}
	}
	public static function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new AssetsWsbp();
		}
		return $instance;
	}
	public static function _() {
		return self::getInstance();
	}
	public function getCdnUrl() {
		if (empty($this->_cdnUrl)) {
			if ((int) FrameWsbp::_()->getModule('options')->get('use_local_cdn')) {
				$uploadsDir = wp_upload_dir( null, false );
				$this->_cdnUrl = $uploadsDir['baseurl'] . '/' . WSBP_CODE . '/';
				if (UriWsbp::isHttps()) {
					$this->_cdnUrl = str_replace('http://', 'https://', $this->_cdnUrl);
				}
			} else {
				$this->_cdnUrl = ( UriWsbp::isHttps() ? 'https' : 'http' ) . '://wupsales-14700.kxcdn.com/';
			}
		}
		return $this->_cdnUrl;
	}

	public function connectAdditionalAdminAssets() {
		if (is_rtl()) {
			FrameWsbp::_()->addStyle('wsbp-style-rtl', WSBP_CSS_PATH . 'style-rtl.css');
		}
	}
	public function loadMediaScripts() {
		if (function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
	}
	public function loadAdminCoreJs() {
		FrameWsbp::_()->addScript('jquery-ui-dialog');
		FrameWsbp::_()->addScript('jquery-ui-slider');
	}
	public function loadCoreJs( $nonce = true) {
		static $loaded = false;
		if (!$loaded) {
			FrameWsbp::_()->addScript('jquery');
			FrameWsbp::_()->addScript('wsbp-core', WSBP_JS_PATH . 'core.js');
			FrameWsbp::_()->addScript('wsbp-notify-js', WSBP_JS_PATH . 'notify.js', array(), false, true);

			$ajaxurl = admin_url('admin-ajax.php');
			$jsData = array(
				'siteUrl' => WSBP_SITE_URL,
				'imgPath' => WSBP_IMG_PATH,
				'cssPath' => WSBP_CSS_PATH,
				'loader' => WSBP_LOADER_IMG,
				'close'	=> WSBP_IMG_PATH . 'cross.gif',
				'ajaxurl' => $ajaxurl,
				'WSBP_CODE' => WSBP_CODE,
				'jsPath' => WSBP_JS_PATH,
				'libPath' => WSBP_LIB_PATH,
				//'dateFormat' => 'yy-mm-dd',
				//'timeFormat' => 'HH:mm'
				'dateFormat' => UtilsWsbp::getJSDateFormat(),
				'timeFormat' => UtilsWsbp::getJSTimeFormat(),
				'isPro' => FrameWsbp::_()->isPro()
			);
			if ($nonce) {
				$jsData['wsbpNonce'] = wp_create_nonce('wsbp-nonce');
			}
			$jsData = DispatcherWsbp::applyFilters('jsInitVariables', $jsData);
			FrameWsbp::_()->addJSVar('wsbp-core', 'WSBP_DATA', $jsData);
			$this->loadTooltipster();
			$loaded = true;
		}
	}
	public function loadTooltipster() {
		$path = WSBP_LIB_PATH . 'tooltipster/';
		FrameWsbp::_()->addScript('tooltipster', $path . 'jquery.tooltipster.min.js');
		FrameWsbp::_()->addStyle('tooltipster', $path . 'tooltipster.css');
	}
	public function loadSlimscroll() {
		FrameWsbp::_()->addScript('jquery.slimscroll', WSBP_JS_PATH . 'slimscroll.min.js');
	}
	public function loadLoaders() {
		FrameWsbp::_()->addStyle('wsbp-loaders', WSBP_CSS_PATH . 'loaders.css');
	}
	public function loadCoreCss() {
		$this->_styles = array(
			'wsbp-style'			=> array('path' => WSBP_CSS_PATH . 'style.css', 'for' => 'admin'),
			'wsbp-wupsales-ui'	=> array('path' => WSBP_CSS_PATH . 'wupsales-ui.css', 'for' => 'admin'),
			'dashicons'			=> array('for' => 'admin'),
			'bootstrap-alerts'	=> array('path' => WSBP_CSS_PATH . 'bootstrap-alerts.css', 'for' => 'admin'),
		);
		foreach ($this->_styles as $s => $sInfo) {
			if (!empty($sInfo['path'])) {
				FrameWsbp::_()->addStyle($s, $sInfo['path']);
			} else {
				FrameWsbp::_()->addStyle($s);
			}
		}
		$this->loadFontAwesome();
	}
	public function loadAdminEndCss() {
		FrameWsbp::_()->addStyle('wsbp-admin-options', WSBP_CSS_PATH . 'admin.options.css');
	}
	public function loadColorPicker() {
		$path = WSBP_LIB_PATH . 'colorpicker/';
		FrameWsbp::_()->addScript('wsbp-colorpicker', $path . 'colorpicker.js');
		FrameWsbp::_()->addStyle('wsbp-colorpicker', $path . 'colorpicker.css');
	}
	public function loadJqueryUi() {
		static $loaded = false;
		if (!$loaded) {
			//Includes: widget.js, position.js, data.js, disable-selection.js, effect.js, effects/effect-blind.js, effects/effect-bounce.js, effects/effect-clip.js, effects/effect-drop.js, effects/effect-explode.js, effects/effect-fade.js, effects/effect-fold.js, effects/effect-highlight.js, effects/effect-puff.js, effects/effect-pulsate.js, effects/effect-scale.js, effects/effect-shake.js, effects/effect-size.js, effects/effect-slide.js, effects/effect-transfer.js, focusable.js, form-reset-mixin.js, jquery-1-7.js, keycode.js, labels.js, scroll-parent.js, tabbable.js, unique-id.js, widgets/accordion.js, widgets/autocomplete.js, widgets/button.js, widgets/checkboxradio.js, widgets/controlgroup.js, widgets/datepicker.js, widgets/dialog.js, widgets/draggable.js, widgets/droppable.js, widgets/menu.js, widgets/mouse.js, widgets/progressbar.js, widgets/resizable.js, widgets/selectable.js, widgets/selectmenu.js, widgets/slider.js, widgets/sortable.js, widgets/spinner.js, widgets/tabs.js, widgets/tooltip.js
			//FrameWsbp::_()->addScript('jquery-ui', WSBP_JS_PATH . 'jquery-ui.min.js');
			$this->loadDatePicker();
			FrameWsbp::_()->addScript('jquery-ui');
			FrameWsbp::_()->addStyle('jquery-ui', WSBP_CSS_PATH . 'jquery-ui.min.css');
			$loaded = true;
		}
	}
	public function loadJqueryPopup() {
		FrameWsbp::_()->addScript('wsbp-bpopup', WSBP_JS_PATH . 'bPopup.js');
	}
	public function loadDataTables( $extensions = array(), $jqueryui = false ) {
		$frame = FrameWsbp::_();
		$path = WSBP_LIB_PATH . 'datatables/';
		$frame->addScript('wsbp-dt-js', $path . 'js/jquery.dataTables.min.js');
		if ($jqueryui) {
			$frame->addScript('wsbp-dt-jq-js', $path . 'js/dataTables.jqueryui.min.js');
			$frame->addStyle('wsbp-dt-css', $path . 'css/dataTables.jqueryui.min.css');
		} else {
			$frame->addStyle('wsbp-dt-css', $path . 'css/jquery.dataTables.min.css');
		}
		foreach ($extensions as $ext) {
			switch ($ext) {
				case 'print':
					$frame->addScript('wsbp-dt-print', $path . 'js/buttons.print.min.js');
					break;
				case 'html5':
					$frame->addScript('wsbp-dt-jszip', WSBP_LIB_PATH . 'jszip/jszip.min.js');
					$frame->addScript('wsbp-dt-pdfmake', WSBP_LIB_PATH . 'pdfmake/pdfmake.min.js');
					$frame->addScript('wsbp-dt-vfs_fonts', WSBP_LIB_PATH . 'pdfmake/vfs_fonts.js');
					$frame->addScript('wsbp-dt-html', $path . 'js/buttons.html5.min.js');
					break;
				
				default:
					$frame->addScript('wsbp-dt-' . $ext, $path . 'js/dataTables.' . $ext . '.min.js');
					if ($jqueryui) {
						$frame->addScript('wsbp-dt-jq-' . $ext, $path . 'js/' . $ext . '.jqueryui.min.js');
					}				
					if ($jqueryui) {
						$frame->addStyle('wsbp-dt-' . $ext, $path . 'css/' . $ext . '.jqueryui.min.css');
					} else {
						$frame->addStyle('wsbp-dt-' . $ext, $path . 'css/' . $ext . '.dataTables.min.css');
					}
					break;
			}
		}
	}
	public function loadFontAwesome() {
		FrameWsbp::_()->addStyle('wsbp-font-awesome', WSBP_CSS_PATH . 'font-awesome.min.css');
	}
	public function loadChosenSelects() {
		$path = WSBP_LIB_PATH . 'chosen/';
		FrameWsbp::_()->addStyle('wsbp-jquery-chosen', $path . 'chosen.min.css');
		FrameWsbp::_()->addScript('wsbp-jquery-chosen', $path . 'chosen.jquery.min.js');
	}
	public function loadDateTimePicker() {
		$path = WSBP_LIB_PATH . 'datetimepicker/';
		FrameWsbp::_()->addScript('jquery-ui-datepicker');
		FrameWsbp::_()->addStyle('wsbp-jquery-datetime', $path . 'jquery-ui-timepicker-addon.css');
		FrameWsbp::_()->addScript('wsbp-jquery-datetime', $path . 'jquery-ui-timepicker-addon.js');
	}
	public function loadDatePicker() {
		FrameWsbp::_()->addScript('jquery-ui-datepicker');
	}
	public function loadSortable() {
		static $loaded = false;
		if (!$loaded) {
			FrameWsbp::_()->addScript('jquery-ui-core');
			FrameWsbp::_()->addScript('jquery-ui-widget');
			FrameWsbp::_()->addScript('jquery-ui-mouse');

			FrameWsbp::_()->addScript('jquery-ui-draggable');
			FrameWsbp::_()->addScript('jquery-ui-sortable');
			$loaded = true;
		}
	}
	public function loadBootstrap() {
		static $loaded = false;
		if (!$loaded) {
			FrameWsbp::_()->addStyle('bootstrap.min', WSBP_CSS_PATH . 'bootstrap.min.css');
			$loaded = true;
		}
	}
}
