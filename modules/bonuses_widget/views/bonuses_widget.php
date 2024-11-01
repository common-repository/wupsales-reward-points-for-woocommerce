<?php
class Bonuses_WidgetViewWsbp extends ViewWsbp {
	public function displayWidget( $instance, $args ) {
		$widget = do_shortcode( '[' . WSBP_SHORTCODE . ' mode="widget"]' );
		if ( '' !== $widget ) {
			HtmlWsbp::echoEscapedHtml( $args['before_widget'] . $widget . $args['after_widget'] );
		}
	}
	public function displayForm( $data, $widget ) {

	}
}
