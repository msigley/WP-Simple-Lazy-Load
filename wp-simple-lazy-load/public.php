<?php
class WPSimpleLazyLoadPublic {
	private $main = null;

	public function __construct() {
		$this->main = WPSimpleLazyLoad::object();

		// Only buffer the output in between wp_head and wp_footer to avoid parsing inline JS and CSS
		add_action( 'wp_head', array( $this, 'ob_start' ), 999 );
		add_action( 'wp_footer', array( $this, 'ob_end_flush' ), 1 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css_js' ), 1 );
	}

	public function ob_start() {
		ob_start( array( $this, 'add_lazy_loading_attributes' ) );
	}

	public function ob_end_flush() {
		ob_end_flush();
	}

	public function add_lazy_loading_attributes( $content ) {
		$DOM = new DomDocument();
		$html_wrapper = '<!DOCTYPE html><html><head><meta charset="' . ini_get( 'default_charset' ) . '" /></head><body>%s</body></html>';
		return preg_replace_callback( '/<img ((?!(?:[^<]*)loading=)[^<]*)>/u', function( $matches ) use ( $DOM, $html_wrapper ) {
			@$DOM->loadHTML( sprintf( $html_wrapper, $matches[0] ) );
			$imgDOM = $DOM->getElementsByTagName( 'img' );
			if( empty( $imgDOM[0] ) )
				return $matches[0];

			$imgDOM = $imgDOM[0];
			if( substr( $imgDOM->getAttribute( 'src' ), 0, 5 ) == 'data:' ) // Don't bother lazy loading inline images
				return $matches[0];

			$imgDOM->setAttribute( 'loading', 'lazy' );
			if( empty( $imgDOM->getAttribute( 'style' ) ) && empty( $imgDOM->getAttribute( 'onload' ) ) ) {
				$imgDOM->setAttribute( 'style', "background:rgba(153,153,153,0.2);" );
				$imgDOM->setAttribute( 'onload', "this.style.background='transparent';" );
			}
			
			$output = @$DOM->saveHTML( $imgDOM );
			if( empty( $output ) )
				return $matches[0];
			return $output;
		}, $content );
	}

	public function enqueue_css_js() {
		wp_enqueue_script( 'wp-simple-lazy-load-helper', plugins_url( '/js/lazy-load-helper.js', __FILE__ ), array( 'jquery' ), $this->main->get_version(), true );
	}

	public function enqueue_shortcode_css_js( $content ) {
		if( $this->enqueued_shortcode_css_js )
			return;

		$this->enqueued_shortcode_css_js = true;

		wp_enqueue_style( 'example-css', plugins_url( '/css/file.css', __FILE__ ), $this->main->get_version(), true );
		wp_enqueue_script( 'example-js', plugins_url( '/js/file.js', __FILE__ ), $this->main->get_version(), true );
	}
}