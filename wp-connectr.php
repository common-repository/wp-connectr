<?php

use WPConnectr\ContainerService;
use WPConnectr\Plugin;

// If this file is called directly, abort.
defined('ABSPATH') || exit;

// exit if not in wordpress
defined('WPINC') || die;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.reenhanced.com/
 * @since             1.0.0
 * @package           wp-connectr

 * @wordpress-plugin
 *
 * Plugin Name:       WPConnectr: Power Automate integration
 * Plugin URI:        https://www.reenhanced.com/products/wordpress-connector/
 * Description:       CERTIFICATION PENDING WITH MICROSOFT. CANT USE YET -- Deeply integrates WordPress with Microsoft Power Automate. Easily connect your site to Microsoft SharePoint, Dynamics, Teams or over 1000 other services.
 * Version:           1.0.0precertification
 * Author:            Reenhanced LLC
 * License:           GPL-2.0+
 * Author URI:        https://www.reenhanced.com/
 * Text Domain:       wp-connectr
 *
 */
define( 'WP_CONNECTR_VERSION', '1.0.0precertification' );

 /*
Copyright 2024 Reenhanced LLC. All Rights Reserved. (email: support@reenhanced.com  web: https://reenhanced.com/)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('WP_CONNECTR_PLUGIN_NAME', 'WP Connectr');
define('WP_CONNECTR_SLUG', 'wp-connectr');
define('WP_CONNECTR_PLUGIN_BASE', plugin_basename(__FILE__));
define('WP_CONNECTR_PLUGIN_FILE', __FILE__);
define('WP_CONNECTR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_CONNECTR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_CONNECTR_MINIMUM_SUPPORTED_PHP_VERSION', '7.2.0' );

/**
 * Displays a message if PHP version isn't supported.
 *
 * @return void
 */
function wp_connector_incompatible_php_version_admin_notice() {
	$class = 'notice notice-error';
	// Translators: 1: Minimum supported PHP Version. 2: Currently running PHP version.
	$message = __( 'WP Connectr is disabled because it is only compatible with PHP version %1$s or later. Please contact your web host to upgrade from PHP version %2$s to a newer version. We recommend using PHP 7.3 or greater.', 'wp-connectr' );
	$message = sprintf( $message, WP_CONNECTR_MINIMUM_SUPPORTED_PHP_VERSION, PHP_VERSION );
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

/**
 * Check PHP compatibility.
 */
if ( version_compare( PHP_VERSION, WP_CONNECTR_MINIMUM_SUPPORTED_PHP_VERSION, '<=' ) ) {
	add_action( 'admin_notices', 'wp_connector_incompatible_php_version_admin_notice' );
	return;
}

require_once 'autoload.php';

( new ContainerService() )->get(Plugin::class)->register();
