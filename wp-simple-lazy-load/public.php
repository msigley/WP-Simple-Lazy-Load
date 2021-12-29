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
		libxml_use_internal_errors( true );
		ob_end_flush();
		libxml_use_internal_errors( false );
	}

	public function add_lazy_loading_attributes( $content ) {
		$DOM = new DomDocument();
		return preg_replace_callback( '/<i(?:mg|frame) (?:(?!(?:[^<]*)loading=)[^<]*)>/u', function( $matches ) use ( &$DOM ) {
			@$DOM->loadHTML( $matches[0], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT | LIBXML_NOBLANKS );
			libxml_clear_errors();
			$elementDOM = $DOM->getElementsByTagName( 'img' );
			if( $elementDOM->length < 1 )
				$elementDOM = $DOM->getElementsByTagName( 'iframe' );
			if( $elementDOM->length < 1 )
				return $matches[0];

			$elementDOM = &$elementDOM[0];
			$src = $elementDOM->getAttribute( 'src' );
			if( '' === $src || substr( $src, 0, 5 ) == 'data:' ) // Don't data urls. They are embeded in the source.
				return $matches[0];

			if( !$elementDOM->hasAttribute( 'style' ) && !$elementDOM->hasAttribute( 'onload' ) ) {
				$elementDOM->setAttribute( 'style', "background:rgba(153,153,153,0.2);" );
				$elementDOM->setAttribute( 'onload', "this.style.background='transparent';" );
			}
			$elementDOM->setAttribute( 'loading', 'lazy' );
			
			$output = @$DOM->saveHTML();
			if( false === $output )
				return $matches[0];
			return $output;
		}, $content );
	}

	public function enqueue_css_js() {
		wp_enqueue_script( 'wp-simple-lazy-load-helper', plugins_url( '/js/lazy-load-helper.js', __FILE__ ), array( 'jquery' ), $this->main->get_version(), true );
	}
}