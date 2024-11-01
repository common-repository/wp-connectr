<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.reenhanced.com/
 * @since      1.0.0
 *
 */

namespace WPConnectr;

use WPConnectr\API\API;
use WPConnectr\ContainerService;
use WPConnectr\Logger;
use WPConnectr\ResourceManager;
use WPConnectr\Admin\Menu;

defined('ABSPATH') || exit;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WPConnectr
 * @subpackage WPConnectr/includes
 */
class Plugin {

	/** The minimum WordPress version that this plugin supports. */
	const MINIMUM_SUPPORTED_WORDPRESS_VERSION = '4.7.0';

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	public $plugin_name = WP_CONNECTR_SLUG;

	/**
	 * The Container Service
	 *
	 * @var ContainerService
	 */
	public $container;

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct( ContainerService $container ) {
		$this->container = $container;
		$this->logger    = $container->get(Logger::class);
	}


	public function register() {

		load_plugin_textdomain(
			WP_CONNECTR_SLUG,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	public function enqueue_styles() {
		wp_enqueue_style(
			'wp-connectr-admin',
			WP_CONNECTR_PLUGIN_URL . 'assets/css/wp-connectr.css',
			array(),
			WP_CONNECTR_VERSION,
			'all'
		);
	}

	public function plugins_loaded() {
		if ( version_compare( get_bloginfo('version'), self::MINIMUM_SUPPORTED_WORDPRESS_VERSION, '<' ) ) {
			// WordPress is older than our minimum required version.
			add_action( 'admin_notices', array( $this, 'admin_notice_unsupported_version' ) );
			return;
		}

		if ( ! API::available() ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_webapi_disabled' ) );
			return;
		}

		$this->container->get( Menu::class )->register();
		$this->container->get( API::class )->register();
		$this->container->get( ACFRestApiSupport::class )->register();
		$this->container->get( DynamicSchemaProvider::class )->register();
		$this->container->get( ResourceManager::class )->register();
		$this->container->get( Trigger\DataStore::class )->register();
		$this->container->get( Trigger\Topics::class )->register();
		$this->container->get( Capabilities::class )->register();

		// Call the installer last so other components can register their hooks first
		$this->container->get( Installer::class )->register();
	}

	/**
	 * Global status indicator.
	 * Returns TRUE when system is connected to Power Automate and ready to send flows
	 *
	 * @return bool
	 */
	public function enabled() {
		return true;
	}

	/**
	 * Displays a message if the user isn't using a supported version of WordPress.
	 *
	 * @return void
	 */
	public function admin_notice_unsupported_version() {
		?>
		<div id="message" class="error">
			<p>
				<?php
				echo esc_html(
					sprintf(
						// Translators: %s: WordPress Version.
						__( 'WP Connectr requires WordPress version %s or later.', 'wp-connectr' ),
						self::MINIMUM_SUPPORTED_WORDPRESS_VERSION
					)
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Displays a message if the Web API for WordPress is turned off
	 *
	 * @return void
	 */
	public function admin_notice_webapi_disabled() {
		?>
		<div id="message" class="notice notice-error gf-notice">
			<p>
				<?php
				echo esc_html( __( 'WP Connectr requires WordPress REST API to be enabled. Please enable the WordPress REST API', 'wp-connectr' ) );
				?>
			</p>
		</div>
		<?php
	}
}
