<?php

namespace WPConnectr\PowerResource;

use WPConnectr\ContainerService;
use WPConnectr\Trigger\Trigger;
use WPConnectr\WPConnectrError;

defined( 'ABSPATH' ) || exit;

/**
 * HOW TO ADD ADDITIONAL RESOURCES:
 *
 * - Create PowerResource and Controller
 * - Add PowerResource and Controller to ContainerService
 * - Add PowerResource to Resource Manager
 *
 * HOW TO ADD ADDITIONAL RESOURCE FROM EXTERNAL PLUGIN:
 * - Create PowerResource and Controller
 * - ????
 */


/**
 * Base Resource Type definition.
 *
 * NOTE: Resource is a reserved word in PHP, so we use PowerResource as the formal name.
 *       Whenever we use "resource" it always refers to a PowerResource
 *
 * @since 2.0.0
 */
abstract class Base implements Definition {
	/**
	 * Resource key.
	 *
	 * Must be a-z lowercase characters only, and in singular (non plural) form.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * List of Triggers that this resource has.
	 *
	 * @var Trigger[]
	 */
	protected $triggers;

	/**
	 * Container Service.
	 *
	 * @var ContainerService
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @throws WPConnectrError If id or name aren't set.
	 */
	public function __construct() {
		if ( $this->is_enabled() ) {
			$this->get_triggers();
		}

		$this->container = ContainerService::get_instance();
	}

	public function register() {
		// Implement your own custom registration logic here in child classes.
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_controllers() {
		$controllers = array();

		$default_controller_name = substr( get_class( $this ), 0, (int) strrpos( get_class( $this ), '\\' ) ) . '\\Controller';

		$controllers[ $default_controller_name ] = $this->container->get( $default_controller_name );

		return $controllers;
	}

	/**
	 * Gets a list of extra triggers supported by this resource
	 *
	 * @return Trigger[] An array of triggers
	 */
	public function get_triggers() {
		return array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled() {
		return true;
	}
}
