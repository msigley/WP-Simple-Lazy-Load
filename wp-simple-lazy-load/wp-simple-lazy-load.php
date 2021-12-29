<?php
/*
Plugin Name: WP Simple Lazy Load
Plugin URI: https://github.com/msigley
Description: Implements lazying loading for image and iframe content.
Version: 1.1.0
Requires at least: 4.8.0
Author: Matthew Sigley
License: GPL2
*/

//TODO: Add custom theme template support
class WPSimpleLazyLoad {
	private static $version = '1.0.5';
	private static $object = null;

	private $path = '';

	private $public = null;
	private $admin = null;

	static function &object( $args=array() ) {
		if ( ! self::$object instanceof WPSimpleLazyLoad )
			self::$object = new WPSimpleLazyLoad();
		return self::$object;
	}

	private function __construct() { }

	public function init() {
		//Plugin path
		$this->path = plugin_dir_path( __FILE__ );

		//Plugin activation/deactivation
		register_deactivation_hook( __FILE__, array($this, 'deactivation') );

		if( !is_admin() ) {
			require_once $this->path . 'public.php';
			$this->public = new WPSimpleLazyLoadPublic();
		}
	}

	public function deactivation() {
		wp_cache_flush();
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