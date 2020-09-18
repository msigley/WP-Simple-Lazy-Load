<?php
/*
Plugin Name: WP Simple Lazy Load
Plugin URI: https://github.com/msigley
Description: Implements lazying loading for image and iframe content.
Version: 1.0.3
Requires at least: 4.8.0
Author: Matthew Sigley
License: GPL2
*/

//TODO: Add custom theme template support
class WPSimpleLazyLoad {
	private static $version = '1.0.0';
	private static $object = null;
	private static $contruct_args = array( 'DEFINE_NAME' => 'arg1_name', 'arg2_name' );
	private static $flipped_construct_args = false;

	private $path = '';

	private $public = null;
	private $admin = null;

	static function &object( $args=array() ) {
		if ( ! self::$object instanceof WPSimpleLazyLoad ) {
			if( ! self::$flipped_construct_args ) {
				self::$contruct_args = array_flip( self::$contruct_args );
				self::$flipped_construct_args = true;
			}
			self::$object = new WPSimpleLazyLoad( $args );
		}
		return self::$object;
	}

	private function __construct( $args ) {
		foreach( $args as $arg_name => $arg_value ) {
			if( empty($arg_value) || !isset( self::$contruct_args[$arg_name] ) )
				continue;

			if( !empty( self::$contruct_args[$arg_name] ) && defined( self::$contruct_args[$arg_name] ) )
				$this->$arg_name = constant( self::$contruct_args[$arg_name] );
			else
				$this->$arg_name = $arg_value;
		}
	}

	public function init() {
		//Plugin path
		$this->path = plugin_dir_path( __FILE__ );

		//Plugin activation/deactivation
		register_activation_hook( __FILE__, array($this, 'activation') );
		register_deactivation_hook( __FILE__, array($this, 'deactivation') );

		if( !is_admin() ) {
			require_once $this->path . 'public.php';
			$this->public = new WPSimpleLazyLoadPublic();
		}
	}

	public function activation() {
	}

	public function deactivation() {
		wp_cache_flush();
	}

	public function get_dominant_image_color_hex( $image_filepath ) {
		$image = false;
		$fileext = strtolower( substr( -4 ) );
		switch( $fileext ) {
			case '.jpg':
				$image = @imagecreatefromjpeg("image.jpg");
				break;
			case '.png':
				$image = @imagecreatefrompng("image.png");
				break;
			case '.gif':
				$image = @imagecreatefromgif("image.gif");
				break;
		}
		if( empty( $image ) )
			return false;
		
		$total = 0;
		for( $x = 0; $x < imagesx( $image ); $x++ ) {
			for( $y = 0; $y < imagesy( $image ); $y++ ) {
				$rgb = imagecolorat( $image, $x, $y );
				$r = ( $rgb >> 16 ) & 0xFF;
				$g = ( $rgb >> 8 ) & 0xFF;
				$b = $rgb & 0xFF;
				
				$rTotal += $r;
				$gTotal += $g;
				$bTotal += $b;
				$total++;
			}
		}
		
		$rAverage = round( $rTotal / $total );
		$gAverage = round( $gTotal / $total );
		$bAverage = round( $bTotal / $total );

		return sprintf( "#%02x%02x%02x", $rAverage, $gAverage, $bAverage );
	}

	/*
	 * Helper functions
	 */
	public function get_version() {
		return self::$version;
	}

	public function get_path() {
		return $this->path;
	}
}

$plugin_obj = WPSimpleLazyLoad::object();
if( !empty( $plugin_obj ) )
	$plugin_obj->init();