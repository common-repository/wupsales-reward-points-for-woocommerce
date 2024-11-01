<?php
class Bonuses_WidgetWsbp extends ModuleWsbp {
	public function init() {
		parent::init();
		add_action('widgets_init', array($this, 'registerWidget'));
	}
	public function registerWidget() {
		return register_widget('WsbpBonusesWidget');
	}
}
/**
 * Maps widget class
 */
class WsbpBonusesWidget extends WP_Widget {
	public function __construct() {
		$widgetOps = array(
			'classname' => 'WsbpBonusesWidget',
			'description' => esc_html__('Displays User Rewards', 'wupsales-reward-points')
		);
		parent::__construct( 'WsbpBonusesWidget', WSBP_WP_PLUGIN_NAME, $widgetOps );
	}
	public function widget( $args, $instance ) {
		extract($args);
		extract($instance);
		FrameWsbp::_()->getModule('bonuses_widget')->getView()->displayWidget($instance, $args);
	}
	public function form( $instance ) {
		extract($instance);
		FrameWsbp::_()->getModule('bonuses_widget')->getView()->displayForm($instance, $this);
	}
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
}
