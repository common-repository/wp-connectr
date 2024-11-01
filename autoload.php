<?php

defined( 'ABSPATH' ) || exit;

/**
 * Autoload the WPConnectr namespaced classes.
 */
spl_autoload_register(
	function ( $class ) {
		$prefix   = 'WPConnectr\\';
		$base_dir = __DIR__ . '/src/';
		$len      = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			// No, move to the next registered autoloader.
			return;
		}
		$relative_class = substr( $class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

require_once 'vendor/autoload.php';
require_once 'vendor_prefixed/autoload.php';
