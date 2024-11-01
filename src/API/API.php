<?php

namespace WPConnectr\API;

use WPConnectr\ResourceManager;
use WPConnectr\ContainerService;
use WPConnectr\Controller\Resources;
use WPConnectr\Controller\TriggerTopicsController;
use WPConnectr\Logger;

defined('ABSPATH') || exit;

/**
 * The api-specific functionality of the plugin.
 *
 * @link       https://www.reenhanced.com/
 * @since      1.0.0
 *
 * @package    WPConnectr
 * @subpackage api
 */

/**
 * The api-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for installing API backend
 *
 * @package    WPConnectr
 * @subpackage api
 */
class API {
	const REST_NAMESPACE = 'wp-connector/v1';

	private $container;
	private $resource_manager;
	private $logger;
	private $controllers = array();

	public $errors = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( ContainerService $container, ResourceManager $resource_manager ) {
		$this->container        = $container;
		$this->resource_manager = $resource_manager;
		$this->logger           = $container->get(Logger::class);
	}

	public static function available() {
		return ( class_exists( 'WP_REST_Controller' ) );
	}

	public function register() {
		if ( ! self::available() ) {
			$this->logger->error( 'API not available: WP Connectr cannot be initialized. Ensure WordPress Web API is enabled!' );
			return;
		}

		add_action( 'rest_api_init', array( $this, 'rest_api_init' ), 151);
		add_action( 'wp', array( $this, 'disable_error_reporting' ), -1 );
	}

	/**
	 * Disable error reporting for our requests.
	 * This will prevent PHP errors from being displayed in the response, which causes Power Automate to break.
	 *
	 * You may lose some debugging information, but it's better than breaking the flow.
	 *
	 * If you are debugging, you can comment out the error_reporting(0) line.
	 *
	 * @return void
	 */
	public function disable_error_reporting() {
		if ( $this->is_our_request() ) {
			error_reporting( 0 );
		}
	}

	public function rest_url( $path ) {
		return rest_url(self::REST_NAMESPACE . '/' . $path);
	}

	public function is_our_request() {
		if ( ! isset( $GLOBALS['wp']->query_vars['rest_route'] ) ) {
			return false;
		}

		if ( strpos( $GLOBALS['wp']->query_vars['rest_route'], '/' . self::REST_NAMESPACE ) === 0) {
			return true;
		}

		return false;
	}

	/**
	 * Registers all of our routes
	 */
	public function rest_api_init() {
		// Runs register_rest_route (base WordPress) for all of our routes.

		$controllers = array(
			'resources' => $this->container->get( Resources::class ),
			'trigger_topics' => $this->container->get( TriggerTopicsController::class ),
		);

		// resource-specific controllers.
		foreach ( $this->resource_manager->get_enabled() as $resource ) {
			$resource->register();

			$resource_controllers = $resource->get_controllers();

			foreach ( $resource_controllers as $controller_def ) {
				$key = $controller_def['key'];
				if ( ! isset( $controllers[ $key ] ) ) {
					$controllers[ $key ] = $controller_def['controller'];
				}
			}
		}

		// Alphabetical sort by key order so that schema definitions are in alphabetical order.
		ksort( $controllers );

		foreach ( $controllers as $controller ) {
			$controller->register_routes();
		}
	}
}
