<?php
namespace WPConnectr\PowerResource;

defined( 'ABSPATH' ) || exit;

/**
 * Interface for PowerResource Definitions
 *
 * @since 2.0.0
 */
interface Definition {
	/**
	 * Whether or not this Resource Type is enabled/available.
	 *
	 * @return bool
	 */
	public function is_enabled();

	/**
	 * Get the trigger definitions that this resource supports.
	 *
	 * @return TriggerDefinition[]
	 */
	public function get_triggers();

	/**
	 * Get the fully qualified class name of the REST API Controller for this resource.
	 *
	 * This class name must be extend a WP_REST_Controller
	 *
	 * @return array{
	 *   array{
	 *     key: string,
	 *     name: string,
	 *     controller: WP_REST_Controller,
	 *   }
	 * }
	 */
	public function get_controllers();
}
