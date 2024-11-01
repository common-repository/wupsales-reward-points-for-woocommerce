<?php
abstract class ViewWsbp extends BaseObjectWsbp {
	/*
	 * @deprecated
	 */
	protected $_tpl = WSBP_DEFAULT;
	/*
	 * @var string name of theme to load from templates, if empty - default values will be used
	 */
	protected $_theme = '';
	/*
	 * @var string module code for this view
	 */
	protected $_code = '';

	public function display( $tpl = '' ) {
		$tpl = ( empty($tpl) ) ? $this->_tpl : $tpl;
		$content = $this->getContent($tpl);
		if (false !== $content) {
			HtmlWsbp::echoEscapedHtml($content);
		}
	}
	public function getPath( $tpl ) {
		$path = '';
		$parentModule = FrameWsbp::_()->getModule( $this->_code );
		if (file_exists($parentModule->getModDir() . 'views' . DS . 'tpl' . DS . $tpl . '.php')) { //Then try to find it in module directory
			$path = $parentModule->getModDir() . DS . 'views' . DS . 'tpl' . DS . $tpl . '.php';
		}
		return $path;
	}
	public function getModule() {
		return FrameWsbp::_()->getModule( $this->_code );
	}
	public function getModel( $code = '' ) {
		return FrameWsbp::_()->getModule( $this->_code )->getController()->getModel($code);
	}
	public function getContent( $tpl = '' ) {
		$tpl = ( empty($tpl) ) ? $this->_tpl : $tpl;
		$path = $this->getPath($tpl);
		if ($path) {
			$content = '';
			ob_start();
			require($path);
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		return false;
	}
	public function setTheme( $theme ) {
		$this->_theme = $theme;
	}
	public function getTheme() {
		return $this->_theme;
	}
	public function setTpl( $tpl ) {
		$this->_tpl = $tpl;
	}
	public function getTpl() {
		return $this->_tpl;
	}
	public function init() {

	}
	public function assign( $name, $value ) {
		$this->$name = $value;
	}
	public function setCode( $code ) {
		$this->_code = $code;
	}
	public function getCode() {
		return $this->_code;
	}

	/**
	 * This will display form for our widgets
	 */
	public function displayWidgetForm( $data = array(), $widget = array(), $formTpl = 'form' ) {
		$this->assign('data', $data);
		$this->assign('widget', $widget);
		if (FrameWsbp::_()->isTplEditor()) {
			if ($this->getPath($formTpl . '_ext')) {
				$formTpl .= '_ext';
			}
		}
		self::display($formTpl);
	}
	public function sizeToPxPt( $size ) {
		if (!strpos($size, 'px') && !strpos($size, '%')) {
			$size .= 'px';
		}
		return $size;
	}
	public function getInlineContent( $tpl = '' ) {
		return preg_replace('/\s+/', ' ', $this->getContent($tpl));
	}
	
	public function includeTemplate( $tpl, $params ) {
		foreach ($params as $param => $data) {
			$this->assign($param, $data);
		}
		return self::display($tpl);
	}
}
